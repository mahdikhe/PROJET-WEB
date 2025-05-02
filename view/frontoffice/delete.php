<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';

$postController = new PostController();
$error = null;
$success = null;

// Check if we're deleting a specific post by ID
if (isset($_GET['id'])) {
    try {
        // First get the post to confirm it exists
        $post = $postController->getPost($_GET['id']);
        
        if (!$post) {
            $error = "Post not found.";
        } else {
            // First delete all comments associated with this post
            $comments = $postController->getComments($_GET['id']);
            foreach ($comments as $comment) {
                $postController->deleteComment($comment['id']);
            }
            
            // Then delete the post
            if ($postController->deletePost($_GET['id'])) {
                $success = "Post and its comments deleted successfully.";
                // Redirect after a short delay
                header("refresh:2;url=cont.php");
            } else {
                $error = "Failed to delete post.";
            }
        }
    } catch (Exception $e) {
        error_log("Error deleting post: " . $e->getMessage());
        $error = "Error deleting post: " . $e->getMessage();
    }
} else {
    $error = "No post ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../style1.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="cont.php" class="logo">
                <img src="../../assets/logo.png" alt="CityPulse Logo" style="height: 35px; margin-right: 10px;">
                CityPulse
            </a>
            <nav class="main-nav">
                <a href="cont.php">Posts</a>
                <a href="event.html">Events</a>
                <a href="forums.html">Forums</a>
            </nav>
        </div>
    </header>

    <main class="container" style="margin-top: 40px;">
        <?php if (isset($error)): ?>
            <div class="error-message" style="color: red; background-color: #ffebee; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success-message" style="color: green; background-color: #e8f5e9; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?= htmlspecialchars($success) ?>
                <p>Redirecting to posts page...</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title">Delete Post</h2>
            <?php if (!isset($success)): ?>
                <p style="color: var(--text-medium);">You will be redirected to the posts page.</p>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="window.location.href='cont.php'">Back to Posts</button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer style="background-color: var(--text-dark); color: white; padding: 48px 0; margin-top: 48px;">
        <div class="container">
            <div style="text-align: center;">
                <p>&copy; 2025 CityPulse. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 