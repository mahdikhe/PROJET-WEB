<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';

$postController = new PostController();
$error = null;
$post = null;

// Check if we're editing a specific post by ID
if (isset($_GET['id'])) {
    try {
        $post = $postController->getPost($_GET['id']);
        if (!$post) {
            $error = "Post not found.";
        }
    } catch (Exception $e) {
        error_log("Error fetching post: " . $e->getMessage());
        $error = "Error loading post. Please try again.";
    }
}

// Check if form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    try {
        // Validate input
        if (empty($_POST['new_title']) || empty($_POST['new_content']) || empty($_POST['new_author'])) {
            throw new Exception("All fields are required");
        }

        // Sanitize and validate input
        $title = filter_var(trim($_POST['new_title']), FILTER_SANITIZE_STRING);
        $content = filter_var(trim($_POST['new_content']), FILTER_SANITIZE_STRING);
        $author = filter_var(trim($_POST['new_author']), FILTER_SANITIZE_STRING);

        // Validate title (letters, numbers, spaces only)
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $title)) {
            throw new Exception("Title can only contain letters, numbers and spaces");
        }

        // Validate author (letters, numbers, spaces only)
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $author)) {
            throw new Exception("Author name can only contain letters, numbers and spaces");
        }

        // Validate content (letters, numbers, spaces only)
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $content)) {
            throw new Exception("Content can only contain letters, numbers and spaces");
        }

        $post = new Post($title, $content, $author);
        $post->setPostId($_POST['id']);
        
        if ($postController->updatePost($post)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to update post";
        }
    } catch (Exception $e) {
        error_log("Error updating post: " . $e->getMessage());
        $error = "Error updating post: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - CityPulse</title>
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

        <?php if (isset($post) && $post): ?>
            <div class="card">
                <h2 class="card-title">Edit Post</h2>
                <form action="edit.php" method="POST" class="form-section">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($post['post_id']) ?>">
                    
                    <div class="form-group">
                        <label for="new_author">Author Name</label>
                        <input type="text" id="new_author" name="new_author" class="form-control" 
                               value="<?= htmlspecialchars($post['author']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_title">Post Title</label>
                        <input type="text" id="new_title" name="new_title" class="form-control" 
                               value="<?= htmlspecialchars($post['title']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="new_content">Post Content</label>
                        <textarea id="new_content" name="new_content" class="form-control" 
                                  rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="window.location.href='cont.php'">Cancel</button>
                        <button type="submit" name="update" class="btn btn-primary">Update Post</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="card">
                <h2 class="card-title">Post Not Found</h2>
                <p style="color: var(--text-medium);">The requested post could not be found.</p>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="window.location.href='cont.php'">Back to Posts</button>
                </div>
            </div>
        <?php endif; ?>
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