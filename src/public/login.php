<?php
// public/login.php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = sanitize_input($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    // Simple login, no password check as per "known list of emails"
                    header('Location: index.php');
                    exit;
                } else {
                    $error_message = "Email address not found in the voter list.";
                }
            } catch (PDOException $e) {
                $error_message = "Database error. Please try again later.";
                // Log error: error_log($e->getMessage());
            }
        }
    } elseif (isset($_POST['admin_username']) && isset($_POST['admin_password'])) {
        // Basic Admin Login (replace with a more secure mechanism for production)
        $admin_username = sanitize_input($_POST['admin_username']);
        $admin_password = $_POST['admin_password']; // In real app, hash and compare

        // IMPORTANT: This is a placeholder for admin authentication.
        // For a real system, use a dedicated admin users table with hashed passwords.
        if ($admin_username === 'admin' && $admin_password === 'password123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin_username;
            header('Location: ../admin/index.php');
            exit;
        } else {
            $error_message = "Invalid admin credentials.";
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
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Voter Login</h1>
        
        <?php if ($error_message): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address:</label>
                <input type="email" name="email" id="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="your.email@example.com">
            </div>
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">Login as Voter</button>
            </div>
        </form>
        
        <hr class="my-8 border-gray-300">

        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Admin Login</h2>
         <form action="login.php" method="POST" class="space-y-6">
            <div>
                <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">Admin Username:</label>
                <input type="text" name="admin_username" id="admin_username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="admin">
            </div>
            <div>
                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Admin Password:</label>
                <input type="password" name="admin_password" id="admin_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150" placeholder="password">
            </div>
            <div>
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">Login as Admin</button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-4 text-center">Admin login is for system administrators only.</p>
    </div>
</body>
</html>