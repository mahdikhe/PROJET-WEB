<?php
require_once 'config/database.php';

try {
    $pdo = Database::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create invitations table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS invitations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        task_id INT(11) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT,
        token VARCHAR(255) NOT NULL,
        status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Invitations table created successfully";

} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}

Database::disconnect();
