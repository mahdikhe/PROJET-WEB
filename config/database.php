<?php
$host = 'localhost';
$dbname = 'user';
$username = 'root';
$password = '';

try {
    // First connect without specifying database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>