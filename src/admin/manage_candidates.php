<?php
// admin/manage_candidates.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_candidate'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $election_id = (int)$_POST['election_id'];
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);

        if (empty($name) || empty($election_id)) {
            $error = "Election and Candidate Name are required.";
        } else {
            try {
                if ($id) { // Edit
                    $stmt = $pdo->prepare("UPDATE candidates SET election_id = ?, name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$election_id, $name, $description, $id]);
                    $message = "Candidate updated successfully.";
                } else { // Add
                    $stmt = $pdo->prepare("INSERT INTO candidates (election_id, name, description) VALUES (?, ?, ?)");
                    $stmt->execute([$election_id, $name, $description]);
                    $message = "Candidate added successfully.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_candidate'])) {
        $id = (int)$_POST['id'];
        try {
            // Note: ON DELETE CASCADE in votes table will handle votes for this candidate.
            $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Candidate deleted successfully.";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch candidate to edit
$candidate_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$edit_id]);
    $candidate_to_edit = $stmt->fetch();
}

// Fetch elections for dropdown
$elections_stmt = $pdo->query("SELECT id, title FROM elections ORDER BY title ASC");
$elections = $elections_stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Manage Candidates</h1>

    <?php if ($message): ?><div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form action="manage_candidates.php" method="POST" class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50 space-y-4">
        <h2 class="text-xl font-semibold text-gray-700"><?php echo $candidate_to_edit ? 'Edit' : 'Add New'; ?> Candidate</h2>
        <input type="hidden" name="id" value="<?php echo $candidate_to_edit['id'] ?? ''; ?>">
        <div>
            <label for="election_id" class="block text-sm font-medium text-gray-700">Election:</label>
            <select name="election_id" id="election_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Select Election</option>
                <?php foreach ($elections as $election): ?>
                    <option value="<?php echo $election['id']; ?>" <?php echo ($candidate_to_edit && $candidate_to_edit['election_id'] == $election['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($election['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Candidate Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($candidate_to_edit['name'] ?? ''); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional):</label>
            <textarea name="description" id="description" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($candidate_to_edit['description'] ?? ''); ?></textarea>
        </div>
        <div>
            <button type="submit" name="save_candidate" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                <?php echo $candidate_to_edit ? 'Update' : 'Add'; ?> Candidate
            </button>
             <?php if ($candidate_to_edit): ?>
                <a href="manage_candidates.php" class="ml-2 text-gray-600 hover:text-gray-800">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>

    <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Candidates</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Election</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt_cand_list = $pdo->query("SELECT c.*, e.title AS election_title FROM candidates c JOIN elections e ON c.election_id = e.id ORDER BY e.title, c.name ASC");
                while ($row = $stmt_cand_list->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['election_title']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="manage_candidates.php?edit_id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                        <form action="manage_candidates.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_candidate" class="text-red-600 hover:text-red-900"><i class="bx bx-trash-x"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
