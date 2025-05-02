<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';

$postController = new PostController();
$error = null;
$success = null;

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$post_id) {
    header("Location: cont.php");
    exit();
}

// Get post details
$post = $postController->getPost($post_id);
if (!$post) {
    header("Location: cont.php");
    exit();
}

// Get comments for this post
$comments = $postController->getComments($post_id);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add_comment' || $_POST['action'] === 'edit_comment') {
                if (empty($_POST['comment'])) {
                    throw new Exception("Comment cannot be empty");
                }

                // Sanitize and validate comment
                $comment = filter_var(trim($_POST['comment']), FILTER_SANITIZE_STRING);

                // Validate comment (letters, numbers, spaces only)
                if (!preg_match('/^[a-zA-Z0-9\s]+$/', $comment)) {
                    throw new Exception("Comment can only contain letters, numbers and spaces");
                }

                if ($_POST['action'] === 'add_comment') {
                    if ($postController->addComment($post_id, $comment)) {
                        $success = "Comment added successfully";
                    } else {
                        throw new Exception("Failed to add comment");
                    }
                } else {
                    if ($postController->updateComment($_POST['comment_id'], $comment)) {
                        $success = "Comment updated successfully";
                    } else {
                        throw new Exception("Failed to update comment");
                    }
                }
            } elseif ($_POST['action'] === 'delete_comment') {
                if ($postController->deleteComment($_POST['comment_id'])) {
                    $success = "Comment deleted successfully";
                } else {
                    throw new Exception("Failed to delete comment");
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle edit comment
$editing_comment = null;
if (isset($_GET['edit'])) {
    $editing_comment = $postController->getComment($_GET['edit']);
    if (!$editing_comment) {
        header("Location: commentaire.php?id=" . $post_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - <?php echo htmlspecialchars($post['title']); ?> - CityPulse</title>
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
        <div class="card">
            <h2 class="card-title">Comments for: <?php echo htmlspecialchars($post['title']); ?></h2>
            
            <?php if ($error): ?>
                <div class="error-message" style="color: red; background-color: #ffebee; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message" style="color: green; background-color: #e8f5e9; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Comment Form -->
            <div class="form-section">
                <h3><?php echo $editing_comment ? 'Edit Comment' : 'Add a Comment'; ?></h3>
                <form action="commentaire.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $editing_comment ? 'edit_comment' : 'add_comment'; ?>">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <?php if ($editing_comment): ?>
                        <input type="hidden" name="comment_id" value="<?php echo $editing_comment['id']; ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <textarea name="comment" class="form-control" rows="3" required><?php echo $editing_comment ? htmlspecialchars($editing_comment['comment']) : ''; ?></textarea>
                    </div>
                    <div class="form-actions">
                        <?php if ($editing_comment): ?>
                            <a href="commentaire.php?id=<?php echo $post_id; ?>" class="btn btn-outline">Cancel</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editing_comment ? 'Update Comment' : 'Submit Comment'; ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Comments List -->
            <div class="comments-section" style="margin-top: 30px;">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                <?php if (empty($comments)): ?>
                    <p style="color: var(--text-light); text-align: center;">No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="card" style="margin-bottom: 15px;">
                            <div class="card-body">
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                <div class="post-actions">
                                    <a href="commentaire.php?id=<?php echo $post_id; ?>&edit=<?php echo $comment['id']; ?>" 
                                       class="btn btn-outline" style="font-size: 12px; padding: 4px 8px;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="commentaire.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_comment">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                        <button type="submit" class="btn btn-outline" 
                                                style="font-size: 12px; padding: 4px 8px; color: #ff4757; border-color: #ff4757;"
                                                onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-actions" style="margin-top: 20px;">
                <a href="cont.php" class="btn btn-outline">Back to Posts</a>
            </div>
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