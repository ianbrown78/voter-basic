<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Determine base path for CSS and links
$basePath = '';
// Check if the current script is in the admin directory
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/public/') !== false) {
    $basePath = '../'; // Go up one level for admin pages
}

$active_page = explode('/', $_SERVER['SERVER_NAME']);
$active_page = $active_page[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <link href='https://cdn.boxicons.com/fonts/transformations.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen">
    <nav class="bg-blue-600 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="<?php echo $basePath; ?>public/index.php" class="text-xl font-bold hover:text-blue-200">Voting System</a>
            <div>
                <?php if (is_logged_in()): ?>
                    <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['user_email']); ?>!</span>
                    <a href="<?php echo $basePath; ?>public/logout.php" class="button-icon-align bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        <i class="bx bx-door-open"></i> Logout
                    </a>
                <?php elseif (is_admin_logged_in() && strpos($_SERVER['PHP_SELF'], '/admin/') !== false ): ?>
                    <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</span>
                    <a href="index.php" class="<?php echo $active_page == 'manage_admins.php' ? 'hover:text-blue-200 active:text-blue-300 px-2 active' : 'hover:text-blue-200 px-2'?>">Dashboard</a>
                    <a href="manage_admins.php" class="<?php echo $active_page == 'manage_admins.php' ? 'hover:text-blue-200 active:text-blue-300 px-2 active' : 'hover:text-blue-200 px-2 active:text-blue-300'?>">Admins</a>
                    <a href="manage_elections.php" class="<?php echo $active_page == 'manage_admins.php' ? 'hover:text-blue-200 active:text-blue-300 px-2 active' : 'hover:text-blue-200 px-2 active:text-blue-300'?>">Elections</a>
                    <a href="manage_candidates.php" class="<?php echo $active_page == 'manage_admins.php' ? 'hover:text-blue-200 active:text-blue-300 px-2 active' : 'hover:text-blue-200 px-2 active:text-blue-300'?>">Candidates</a>
                    <a href="manage_users.php" class="<?php echo $active_page == 'manage_admins.php' ? 'hover:text-blue-200 active:text-blue-300 px-2 active' : 'hover:text-blue-200 px-2 active:text-blue-300'?>">Voters</a>
                    <a href="<?php echo $basePath; ?>admin/logout.php" class="button-icon-align bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        <i class="bx bx-door-open"></i> Logout
                    </a>
                <?php elseif (strpos($_SERVER['PHP_SELF'], '/admin/') === false): // Show login only if not admin and not logged in ?>
                    <a href="<?php echo $basePath; ?>login.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mx-auto p-4 mt-6">
