<?php
// public/index.php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require_once '../includes/header.php';

$userId = $_SESSION['user_id'];
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Available Elections</h1>

    <?php if ($message): ?>
        <div class="mb-4 p-3 rounded-lg <?php echo strpos(strtolower($message), 'success') !== false || strpos(strtolower($message), 'thank you') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php
    try {
        $stmt = $pdo->prepare("SELECT e.*, (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id AND v.user_id = ?) AS has_voted FROM elections e WHERE e.is_active = TRUE AND NOW() BETWEEN e.start_date AND e.end_date ORDER BY e.end_date ASC");
        $stmt->execute([$userId]);
        $elections = $stmt->fetchAll();

        if (count($elections) > 0) {
            echo '<ul class="space-y-6">';
            foreach ($elections as $election) {
                echo '<li class="p-6 border border-gray-200 rounded-lg hover:shadow-xl transition-shadow duration-300">';
                echo '<h2 class="text-2xl font-semibold text-blue-700 mb-2">' . htmlspecialchars($election['title']) . '</h2>';
                echo '<p class="text-gray-600 mb-3">' . nl2br(htmlspecialchars($election['description'])) . '</p>';
                echo '<p class="text-sm text-gray-500 mb-1">Voting Starts: ' . date('F j, Y, g:i a', strtotime($election['start_date'])) . '</p>';
                echo '<p class="text-sm text-gray-500 mb-4">Voting Ends: ' . date('F j, Y, g:i a', strtotime($election['end_date'])) . '</p>';
                if ($election['has_voted']) {
                    echo '<p class="text-green-600 font-semibold"><span class="inline-block bg-green-200 text-green-800 text-xs px-2 py-1 rounded-full uppercase font-semibold tracking-wide">Voted</span> You have already voted in this election.</p>';
                } else {
                    echo '<a href="election.php?id=' . $election['id'] . '" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-300">View Candidates & Vote</a>';
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="text-center text-gray-500 text-lg">There are no active elections at the moment. Please check back later.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-red-500 text-center">Error fetching elections: ' . htmlspecialchars($e->getMessage()) . '</p>'; // More user-friendly in prod
    }
    ?>
</div>

<?php require_once '../includes/footer.php'; ?>
