<?php
// Direct database fix script
require_once 'config.php';

echo '<html><head><title>Database Fix</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 4px; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 4px; }
    a { display: inline-block; margin-top: 15px; background: #6944ff; color: white; 
        padding: 8px 15px; text-decoration: none; border-radius: 4px; }
</style>
</head><body>';

echo '<h1>Database Structure Fix</h1>';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Direct and simple approach: add the column
    $sql = "ALTER TABLE entretiens ADD COLUMN status VARCHAR(20) DEFAULT 'pending'";
    
    if ($conn->query($sql) === TRUE) {
        echo '<div class="success">Success! The status column has been added to the entretiens table.</div>';
        
        // Update existing records
        $update = "UPDATE entretiens SET status = 'pending' WHERE status IS NULL";
        if ($conn->query($update) === TRUE) {
            echo '<div class="success">All existing records have been updated with a default status of "pending".</div>';
        }
    } else {
        // If error is about duplicate column, that's actually fine
        if (strpos($conn->error, 'Duplicate column') !== false) {
            echo '<div class="success">The status column already exists in the table.</div>';
        } else {
            throw new Exception($conn->error);
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo '<div class="error">Error: ' . $e->getMessage() . '</div>';
}

echo '<a href="../view/entretiens.php">Return to Applications Management</a>';
echo '</body></html>';
?>