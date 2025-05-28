<?php
// admin/manage_users.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $email = sanitize_input($_POST['email']);
        $name = sanitize_input($_POST['name']); // Optional

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            try {
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt_check->execute([$email]);
                if ($stmt_check->fetchColumn() > 0) {
                    $error = "User with this email already exists.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO users (email, name) VALUES (?, ?)");
                    $stmt->execute([$email, $name]);
                    $message = "User added successfully.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['add_bulk_users'])) {
        if (isset($_FILES['csv'])) {
            if ($_FILES["csv"]["error"] > 0) {
                echo "<script>console.log('Debug File Error: " . $_FILES['csv']['error'] . "' );</script>";
                $error = $_FILES["csv"]["error"];
            } else {
                try {
                    // Get all the voters so we can check for dupes
                    $voters = $pdo->prepare("SELECT email FROM users")->execute();

                    $csv = fopen($_FILES['csv']['tmp_name'], 'r');
                    
                    // // Cycle through each line of the sheet and insert the voters into the table
                    // $stmt = $pdo->prepare("INSERT INTO users (email, name) VALUES (?, ?)");
                    // $stmt->bind_param($email, $name);
                    
                    // while(($getData = fgetcsv($csv, 100000, ",")) !== FALSE) {
                    //     if (count($getData) != 2) {
                    //         $error = "Invalid data structure."
                    //         exit;
                    //     }

                    //     if (in_array($getData[0], voters)) {
                    //         $error = "Voter with email " . $getData[0] . "already exists";
                    //         continue;
                    //     }

                    //     // $email = $getData[0];
                    //     // $name = $getData[1];

                    //     // $stmt->execute([$email, $name]);
                    // }

                    fclose($csv);
                    // Tell our users we are good.
                    $message = "Successfully inserted bulk users list";
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = (int)$_POST['id'];
        try {
            // Note: ON DELETE CASCADE in votes table will handle votes from this user.
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = "User deleted successfully.";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>
<div class="bg-white p-8 rounded-lg shadow-lg max-w-8xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Manage Users (Voters)</h1>

    <?php if ($message): ?><div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="columns-2">
        <form action="manage_users.php" method="POST" class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50 space-y-4">
            <h2 class="text-xl font-semibold text-gray-700">Add New User</h2>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name (Optional):</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" name="add_user" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">Add User</button>
            </div>
        </form>

        <form action="manage_users.php" method="POST" enctype="multipart/form-data" class="mb-8 p-6 border border-gray-200 rounded-lg bg-gray-50 space-y-4">
            <h2 class="text-xl font-semibold text-gray-700">Bulk Add Users</h2>
            <div>
                <label for="csv" class="block text-sm font-medium text-gray-700">CSV File:</label>
                <input type="file" name="csv" id="csv" accept=".csv" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <button type="submit" name="add_bulk_users" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">Add Bulk Users</button>
            </div>
        </form>
    </div>

    <h2 class="text-xl font-semibold text-gray-700 mb-4">Existing Users</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                while ($row = $stmt_users->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <form action="manage_users.php" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their votes.');">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900"><i class="bx bx-trash-x"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
