<?php
$host = 'localhost';
$dbname = 'project_creation';  
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
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