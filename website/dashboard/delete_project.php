<?php
// Include database connection
include 'C:/Users/Abderrahmen/Desktop/2A40/cursor/website/projet/create project/db.php';

// Check if the project ID is provided
if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
    $projectId = intval($_POST['project_id']);
    
    try {
        // Prepare and execute the delete statement
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        
        // Check if the deletion was successful
        if ($stmt->rowCount() > 0) {
            // Redirect with success message
            header("Location: all_projects.php?message=deleted");
            exit;
        } else {
            // Redirect with error message
            header("Location: all_projects.php?error=not_found");
            exit;
        }
    } catch (PDOException $e) {
        // Redirect with error message
        header("Location: all_projects.php?error=database&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Redirect with error message
    header("Location: all_projects.php?error=invalid_id");
    exit;
}
?> 