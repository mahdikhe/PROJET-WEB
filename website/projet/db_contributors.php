<?php
$host = 'localhost';
$dbname = 'projet_contribution';  // Database for contributors table
$username = 'root';
$password = '';

try {
    $contrib_conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $contrib_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query to verify connection
    $testQuery = $contrib_conn->query("SELECT 1");
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