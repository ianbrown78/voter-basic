<?php
// admin/login.php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: /admin/index.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = sanitize_input($_POST['username']);
        $password = sanitize_input($_POST['password']);
        echo "<script>console.log('Debug Password: " . $password . "' );</script>";

        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = TRUE");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                echo "<script>console.log('Admin is set' );</script>";
                echo "<script>console.log('Admin ID: " . $admin['id'] . "' );</script>";
                echo "<script>console.log('Admin ID: " . $admin['username'] . "' );</script>";
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                
                header('Location: /admin/index.php');
                exit;
            } else {
                echo "<script>console.log('Admin is NOT set' );</script>";
                $error_message = "Username and/or password incorrect";
            }
        } catch (PDOException $e) {
            $error_message = "Database error. Please try again later.";
            // Log error: error_log($e->getMessage());
        }
    }
}

// No header include here, login page is standalone or has its own simple header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full">
        
        <?php if ($error_message): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Admin Login</h2>
         <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username:</label>
                <input type="text" name="username" id="username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="admin">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password:</label>
                <input type="password" name="password" id="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="password">
            </div>
            <div>
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">Login as Admin</button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-4 text-center">Admin login is for system administrators only.</p>
    </div>
</body>
</html>