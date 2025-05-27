<?php
// admin/manage_admins.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_admin'])) {
        $username = sanitize_input($_POST['username']);
        $password = sanitize_input($_POST['password']);
        $hashed = hash_password($password);

        try {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
            $stmt_check->execute([$username]);
            if ($stmt_check->fetchColumn() > 0) {
                $error = "Admin with this username already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO admins (username, password, is_active) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed, TRUE]);
                $message = "Admin added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_admin'])) {
        $id = (int)$_POST['id'];
        try {
            // Note: ON DELETE CASCADE in votes table will handle votes from this user.
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Admin deleted successfully.";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>
<div class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Manage Admins</h1>

    <?php if ($message): ?><div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form action="manage_admins.php" method="POST" class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50 space-y-4">
        <h2 class="text-xl font-semibold text-gray-700">Add New Admin</h2>
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
            <input type="text" name="username" id="username" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
            <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        <div>
            <button type="submit" name="add_admin" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">Add Admin</button>
        </div>
    </form>

    <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Admins</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Is Active</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt_admins = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
                while ($row = $stmt_admins->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['username']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?php $row['is_active'] == TRUE
                            ? '<span class="text-green-600 hover:text-green-900"><i class="bx bx-circle"></i></span>'
                            : '<span class="text-red-600 hover:text-red-900"><i class="bx bx-circle"></i></span>'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form action="manage_admins.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this admin? This will also delete all their votes.');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_admin" class="text-red-600 hover:text-red-900"><i class="bx bx-trash-x"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
// ?>