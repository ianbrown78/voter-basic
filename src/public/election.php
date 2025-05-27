<?php
// public/election.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_login(); // Ensure user is logged in

$election_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

if ($election_id <= 0) {
    $_SESSION['message'] = "Error: Invalid election ID.";
    header('Location: index.php');
    exit;
}

// Fetch election details
try {
    $stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ? AND is_active = TRUE AND NOW() BETWEEN start_date AND end_date");
    $stmt->execute([$election_id]);
    $election = $stmt->fetch();

    if (!$election) {
        $_SESSION['message'] = "Error: Election not found or not currently active.";
        header('Location: index.php');
        exit;
    }

    // Check if user has already voted
    $stmt_check_vote = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ? AND election_id = ?");
    $stmt_check_vote->execute([$user_id, $election_id]);
    $has_voted = $stmt_check_vote->fetchColumn() > 0;

    if ($has_voted) {
         $_SESSION['message'] = "You have already voted in this election: " . htmlspecialchars($election['title']);
         header('Location: index.php');
         exit;
    }

} catch (PDOException $e) {
    // Log error
    $_SESSION['message'] = "Database error fetching election details.";
    header('Location: index.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['candidate_id']) && !$has_voted) {
        $candidate_id = (int)$_POST['candidate_id'];

        // Verify candidate belongs to this election
        $stmt_verify_candidate = $pdo->prepare("SELECT COUNT(*) FROM candidates WHERE id = ? AND election_id = ?");
        $stmt_verify_candidate->execute([$candidate_id, $election_id]);
        if ($stmt_verify_candidate->fetchColumn() == 0) {
            $error_message = "Invalid candidate selected for this election.";
        } else {
            try {
                // Re-check voting status just before inserting to prevent race conditions (though less likely with page reload)
                $stmt_check_vote_again = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE user_id = ? AND election_id = ?");
                $stmt_check_vote_again->execute([$user_id, $election_id]);
                if ($stmt_check_vote_again->fetchColumn() > 0) {
                    $_SESSION['message'] = "It seems you've just voted in this election: " . htmlspecialchars($election['title']);
                    header('Location: index.php');
                    exit;
                }

                $stmt_insert_vote = $pdo->prepare("INSERT INTO votes (user_id, election_id, candidate_id) VALUES (?, ?, ?)");
                $stmt_insert_vote->execute([$user_id, $election_id, $candidate_id]);
                $_SESSION['message'] = "Success! Thank you for your vote in '" . htmlspecialchars($election['title']) . "'.";
                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Code for duplicate entry
                    $_SESSION['message'] = "You have already cast your vote for this election.";
                    header('Location: index.php'); // Redirect to prevent re-submission
                    exit;
                } else {
                    $error_message = "Error submitting your vote. Please try again. " . $e->getMessage();
                    // Log error: error_log("Vote submission error: " . $e->getMessage());
                }
            }
        }
    } elseif ($has_voted) {
        $_SESSION['message'] = "You have already voted in this election.";
        header('Location: index.php');
        exit;
    } else {
        $error_message = "Please select a candidate to vote.";
    }
}

// Fetch candidates for this election
try {
    $stmt_candidates = $pdo->prepare("SELECT * FROM candidates WHERE election_id = ? ORDER BY name ASC");
    $stmt_candidates->execute([$election_id]);
    $candidates = $stmt_candidates->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching candidates.";
    $candidates = [];
    // Log error
}

require_once '../includes/header.php';
?>

<div class="bg-white p-8 rounded-lg shadow-lg max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-2 text-center text-blue-600"><?php echo htmlspecialchars($election['title']); ?></h1>
    <p class="text-gray-600 mb-6 text-center"><?php echo nl2br(htmlspecialchars($election['description'])); ?></p>

    <?php if ($error_message): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (!$has_voted && count($candidates) > 0): ?>
        <form action="election.php?id=<?php echo $election_id; ?>" method="POST" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-700">Select a Candidate:</h2>
            <?php foreach ($candidates as $candidate): ?>
                <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="radio" name="candidate_id" value="<?php echo $candidate['id']; ?>" required class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500">
                        <span class="text-lg font-medium text-gray-800"><?php echo htmlspecialchars($candidate['name']); ?></span>
                    </label>
                    <?php if (!empty($candidate['description'])): ?>
                        <p class="text-sm text-gray-500 ml-8 mt-1"><?php echo nl2br(htmlspecialchars($candidate['description'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div>
                <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">Submit Vote</button>
            </div>
        </form>
    <?php elseif (count($candidates) == 0 && !$has_voted): ?>
        <p class="text-center text-gray-500">No candidates are currently listed for this election.</p>
    <?php endif; ?>
     <div class="mt-6 text-center">
        <a href="index.php" class="text-blue-600 hover:text-blue-800 hover:underline">&laquo; Back to Elections List</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
