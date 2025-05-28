<?php
// admin/index.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_admin_login(); // Ensure admin is logged in

require_once '../includes/header.php'; // Admin header will adjust paths
?>

<div class="bg-white p-8 rounded-lg shadow-lg max-w-8xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Admin Dashboard</h1>
    <p class="text-lg text-gray-700 mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
    <p class="text-gray-600 mb-6">From here you can manage elections, candidates, and users.</p>

    <div class="grid md:grid-cols-4 gap-6">
        <a href="manage_elections.php" class="block p-6 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 transition-colors">
            <h2 class="text-xl font-semibold mb-2">Manage Elections</h2>
            <p>Create, edit, and activate/deactivate elections.</p>
        </a>
        <a href="manage_candidates.php" class="block p-6 bg-green-500 text-white rounded-lg shadow hover:bg-green-600 transition-colors">
            <h2 class="text-xl font-semibold mb-2">Manage Candidates</h2>
            <p>Add and edit candidates for each election.</p>
        </a>
        <a href="manage_users.php" class="block p-6 bg-yellow-500 text-white rounded-lg shadow hover:bg-yellow-600 transition-colors">
            <h2 class="text-xl font-semibold mb-2"><i class='bx bx-user bx-lg'></i>  Manage Voters</h2>
            <p>View and manage the list of eligible voters.</p>
        </a>
        <a href="manage_admins.php" class="block p-6 bg-purple-500 text-white rounded-lg shadow hover:bg-pueple-600 transition-colors">
            <h2 class="text-xl font-semibold mb-2">Manage Admins</h2>
            <p>View and manage the list of administrators.</p>
        </a>
    </div>
    <div class="mt-8 p-6 border border-gray-200 rounded-lg bg-gray-50">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">Quick Stats</h3>
        <?php
        try {
            $total_voters = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $total_elections = $pdo->query("SELECT COUNT(*) FROM elections")->fetchColumn();
            $active_elections = $pdo->query("SELECT COUNT(*) FROM elections WHERE is_active = TRUE AND NOW() BETWEEN start_date AND end_date")->fetchColumn();
            $total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
            $total_admins = $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = TRUE")->fetchColumn();
        ?>
        <ul class="space-y-2 text-gray-600">
            <li>Total Registered Voters: <span class="font-bold text-blue-600"><?php echo $total_voters; ?></span></li>
            <li>Total Elections Created: <span class="font-bold text-blue-600"><?php echo $total_elections; ?></span></li>
            <li>Currently Active Elections: <span class="font-bold text-green-600"><?php echo $active_elections; ?></span></li>
            <li>Total Votes Cast (All Time): <span class="font-bold text-blue-600"><?php echo $total_votes; ?></span></li>
            <li>Total Adminsitrators: <span class="font-bold text-purple-600"><?php echo $total_admins; ?></span></li>
        </ul>
        <?php
        } catch (PDOException $e) {
            echo "<p class='text-red-500'>Could not fetch stats: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
    <div class="mt-8 p-6 border border-gray-200 rounded-lg bg-gray-50">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">Results</h3>
        <a href="results.php"><i class="bx bx-newspaper"></i> Check the Election Results here</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
