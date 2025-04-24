<!-- filepath: c:\xampp\htdocs\cursor\website\projet\fetchData.php -->
<?php
// Include the database connection
require_once '../projet/create project/db.php';

try {
    // Example query to fetch data
    $stmt = $conn->prepare("SELECT * FROM your_table_name"); // Replace 'your_table_name' with your actual table name
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return data as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $e->getMessage()
    ]);
}
?>