<?php
// admin/results.php
// This is an optional page. For true anonymity and fairness, results should typically
// only be available after an election has concluded and possibly only to administrators.
// This is a very basic example.

require_once '../config/database.php';
require_once '../includes/functions.php';
require_admin_login();

require_once '../includes/header.php';

$election_id_filter = isset($_GET['election_id']) ? (int)$_GET['election_id'] : null;
?>

<div class="bg-white p-8 rounded-lg shadow-lg max-w-8xl mx-auto">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Election Results</h1>

    <form method="GET" action="results.php" class="mb-6 flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-grow">
            <label for="election_id_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Election:</label>
            <select name="election_id" id="election_id_filter" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Elections</option>
                <?php
                $stmt_elections = $pdo->query("SELECT id, title FROM elections ORDER BY title");
                while ($election_row = $stmt_elections->fetch()) {
                    $selected = ($election_id_filter == $election_row['id']) ? 'selected' : '';
                    echo "<option value=\"{$election_row['id']}\" {$selected}>" . htmlspecialchars($election_row['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Filter</button>
    </form>

    <?php
    try {
        $sql = "SELECT e.title AS election_title, c.name AS candidate_name, COUNT(v.id) AS vote_count
                FROM votes v
                JOIN elections e ON v.election_id = e.id
                JOIN candidates c ON v.candidate_id = c.id";
        
        $params = [];
        if ($election_id_filter) {
            $sql .= " WHERE e.id = ?";
            $params[] = $election_id_filter;
        }
        
        $sql .= " GROUP BY e.id, c.id ORDER BY e.title, vote_count DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        if (count($results) > 0) {
            $current_election_title = null;
            foreach ($results as $result) {
                if ($result['election_title'] !== $current_election_title) {
                    if ($current_election_title !== null) {
                        echo '</ul></div>'; // Close previous election's list and container
                    }
                    $current_election_title = $result['election_title'];
                    echo '<div class="mb-8 p-6 border border-gray-200 rounded-lg">';
                    echo '<h2 class="text-2xl font-semibold text-blue-700 mb-4">' . htmlspecialchars($current_election_title) . '</h2>';
                    echo '<ul class="space-y-2">';
                }
                echo '<li class="flex justify-between items-center p-3 bg-gray-50 rounded-md">';
                echo '<span class="text-gray-800 font-medium">' . htmlspecialchars($result['candidate_name']) . '</span>';
                echo '<span class="text-lg font-bold text-blue-600">' . $result['vote_count'] . ' votes</span>';
                echo '</li>';
            }
            if ($current_election_title !== null) {
                echo '</ul></div>'; // Close the last election's list and container
            }
        } else {
            echo '<p class="text-center text-gray-500">No results to display for the selected filter.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="text-red-500 text-center">Error fetching results: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
</div>

<?php require_once '../includes/footer.php'; ?>
