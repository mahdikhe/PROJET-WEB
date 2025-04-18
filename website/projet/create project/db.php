<?php
$host = 'localhost';
$dbname = 'project_creation';  // Changed to the correct database name
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query to verify connection
    $testQuery = $conn->query("SELECT 1");
    if (!$testQuery) {
        throw new Exception("Database connection test failed");
    }
    
} catch(PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}
?>