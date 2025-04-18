<?php
require 'C:\xampp\htdocs\blog\config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author = $_POST['author'];
    $created_at = $_POST['created_at'];

    try {
        // Get database connection
        $conn = config::getConnexion();
        
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO post (id, title, content, author, created_at) 
                              VALUES (NULL , :title, :content, :author, :created_at)");
        
        // Bind parameters
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':created_at', $created_at);
        
        // Execute the statement
        $stmt->execute();
        
        // Redirect to success page or back to index
        header("Location: cont.php?success=1");
        exit();
        
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Close connection
$conn = null;
?>
