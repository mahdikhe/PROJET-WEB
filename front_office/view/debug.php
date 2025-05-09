<?php
// Include database configuration
require_once '../../back_office/model/config.php';

// Start session
session_start();

// Function to dump data in readable format
function debug_dump($data, $title = '') {
    echo "<div style='background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    if ($title) {
        echo "<h3 style='margin-top: 0;'>$title</h3>";
    }
    echo "<pre style='margin: 0;'>";
    print_r($data);
    echo "</pre></div>";
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<h1>Error: User not logged in</h1>";
    exit;
}

// Show user session data
debug_dump($_SESSION, 'User Session Data');

// Connect to database
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Display entretiens table structure
    $stmt = $pdo->query("DESCRIBE entretiens");
    $tableStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
    debug_dump($tableStructure, 'Entretiens Table Structure');

    // Count all applications in the entretiens table
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM entretiens");
    $totalApplications = $stmt->fetch(PDO::FETCH_ASSOC);
    debug_dump($totalApplications, 'Total Applications in Database');

    // Get entretiens for current user
    $stmt = $pdo->prepare("SELECT * FROM entretiens WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    debug_dump($userApplications, 'User Applications Found');

    // Get joined data with offer details
    $query = "SELECT e.*, o.titre, o.entreprise, o.emplacement, o.type, o.date as date_offre,
                     IFNULL(e.created_at, e.date_entretien) as date 
              FROM entretiens e
              JOIN offres o ON e.id_offre = o.id
              WHERE e.id_user = ?
              ORDER BY e.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $joinedData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    debug_dump($joinedData, 'Joined Data with Offer Details');

    // If no applications are found, check if the user has any records in the database
    if (empty($userApplications)) {
        $stmt = $pdo->query("SELECT id, id_user FROM entretiens LIMIT 10");
        $sampleApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        debug_dump($sampleApplications, 'Sample Applications (First 10)');
    }

} catch (PDOException $e) {
    echo "<h1>Database Error:</h1>";
    echo "<p>{$e->getMessage()}</p>";
}
?>