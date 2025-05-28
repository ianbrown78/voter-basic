<?php
// config/database.php (Example)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_NAME', 'voter_basic');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// function importCSV($filename) {
//     // Open the CSV file
//     $file = fopen($filename, "r");
    
//     // Skip the header row (if it exists)
//     fgetcsv($file, 10000, ",");  // Adjust delimiter if needed
    
//     // Prepare the SQL query
//     $sql = "INSERT INTO users (email, name) VALUES (?, ?)";
//     $stmt = $conn->prepare($sql);
    
//     if ($stmt === false) {
//         die("Error preparing statement: " . $conn->error); // Handle prepare error
//     }
    
//     // Bind parameters
//     $stmt->bind_param($email, $name);

//     // Get all the voters so we can check for dupes
//     $voters = $pdo->prepare("SELECT email FROM users")->execute();
//     var_dump($voters);
    
//     while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
//         // Validate data (example validation)
//         if (count($getData) != 2) {
//             error_log("Invalid CSV row: " . implode(",", $getData));
//             continue; // Skip to the next row
//         }
//         // if (in_array($getData[0], voters)) {
//         //     $error = "Voter with email " . $getData[0] . "already exists";
//         //     continue;
//         // }
        
//         // Assign values
//         $email = $getData[0];
//         $name = $getData[1];

//         $stmt->
        
//         // Execute the query
//         // if (!$stmt->execute()) {
//         //     error_log("Error inserting row: " . $stmt->error);
//         //     return false;
//         // }
//     }
    
//     // Close the statement and connection
//     $stmt->close();
//     fclose($file);

//     return true
// }
?>