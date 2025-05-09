<?php
// Update entretiens table to add status field
require_once 'config.php';

// HTML header for browser display
echo '<!DOCTYPE html>
<html>
<head>
    <title>Database Update</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; line-height: 1.6; }
        .success { color: green; background: #e8f5e9; padding: 15px; border-radius: 5px; }
        .error { color: red; background: #ffebee; padding: 15px; border-radius: 5px; }
        .info { color: #0288d1; background: #e1f5fe; padding: 15px; border-radius: 5px; }
        a { display: inline-block; margin-top: 20px; background: #6944ff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Database Structure Update</h1>';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if status column already exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM entretiens LIKE 'status'");
    $stmt->execute();
    $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If column doesn't exist, add it
    if (!$columnExists) {
        $alterQuery = "ALTER TABLE entretiens ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
        $pdo->exec($alterQuery);
        echo '<div class="success">Success: Status column added successfully to the entretiens table!</div>';
        
        // Also update existing records
        $updateQuery = "UPDATE entretiens SET status = 'pending' WHERE status IS NULL";
        $pdo->exec($updateQuery);
        echo '<div class="info">All existing records have been updated with status = \'pending\'.</div>';
    } else {
        echo '<div class="info">Status column already exists in the entretiens table.</div>';
    }
    
} catch(PDOException $e) {
    echo '<div class="error">Error: ' . $e->getMessage() . '</div>';
}

echo '<a href="../view/entretiens.php">Return to Applications Management</a>
</body>
</html>';
?>