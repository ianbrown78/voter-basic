<?php
// admin/manage_elections.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle form submissions for add/edit/delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add or Edit Election
    if (isset($_POST['save_election'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title) || empty($start_date) || empty($end_date)) {
            $error = "Title, Start Date, and End Date are required.";
        } elseif (strtotime($start_date) >= strtotime($end_date)) {
            $error = "End Date must be after Start Date.";
        } else {
            try {
                if ($id) { // Edit
                    $stmt = $pdo->prepare("UPDATE elections SET title = ?, description = ?, start_date = ?, end_date = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $start_date, $end_date, $is_active, $id]);
                    $message = "Election updated successfully.";
                } else { // Add
                    $stmt = $pdo->prepare("INSERT INTO elections (title, description, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $start_date, $end_date, $is_active]);
                    $message = "Election added successfully.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
    // Delete Election
    elseif (isset($_POST['delete_election'])) {
        $id = (int)$_POST['id'];
        try {
            // Note: ON DELETE CASCADE will handle candidates and votes for this election.
            $stmt = $pdo->prepare("DELETE FROM elections WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Election deleted successfully (along with its candidates and votes).";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch election to edit if ID is provided
$election_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$edit_id]);
    $election_to_edit = $stmt->fetch();
}


require_once '../includes/header.php';
?>
<div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Manage Elections</h1>

    <?php if ($message): ?><div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form action="manage_elections.php" method="POST" class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50 space-y-4">
        <h2 class="text-xl font-semibold text-gray-700"><?php echo $election_to_edit ? 'Edit' : 'Add New'; ?> Election</h2>
        <input type="hidden" name="id" value="<?php echo $election_to_edit['id'] ?? ''; ?>">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title:</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($election_to_edit['title'] ?? ''); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description:</label>
            <textarea name="description" id="description" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($election_to_edit['description'] ?? ''); ?></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time:</label>
                <input type="datetime-local" name="start_date" id="start_date" value="<?php echo $election_to_edit ? date('Y-m-d\TH:i', strtotime($election_to_edit['start_date'])) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date & Time:</label>
                <input type="datetime-local" name="end_date" id="end_date" value="<?php echo $election_to_edit ? date('Y-m-d\TH:i', strtotime($election_to_edit['end_date'])) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>
        <div class="flex items-center">
            <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo ($election_to_edit && $election_to_edit['is_active']) || !$election_to_edit ? 'checked' : ''; ?> class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-900">Is Active?</label>
        </div>
        <div>
            <button type="submit" name="save_election" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                <?php echo $election_to_edit ? 'Update' : 'Add'; ?> Election
            </button>
            <?php if ($election_to_edit): ?>
                <a href="manage_elections.php" class="ml-2 text-gray-600 hover:text-gray-800">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>

    <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Elections</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Starts</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ends</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt_list = $pdo->query("SELECT * FROM elections ORDER BY start_date DESC");
                while ($row = $stmt_list->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($row['start_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($row['end_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['is_active'] ? '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>' : '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>'; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="manage_elections.php?edit_id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                        <form action="manage_elections.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this election? This will also delete all associated candidates and votes.');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_election" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
