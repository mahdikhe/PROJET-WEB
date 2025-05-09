<?php
require_once dirname(__DIR__, 2) . '/controller/Controller.php';
require_once dirname(__DIR__, 2) . '/model/model.php';

$postController = new PostController();
$user_id = 'anonymous'; // In a real application, this would be the logged-in user's ID
$saved_posts = $postController->getSavedPosts($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Posts - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
                <a href="saved.php" class="active">Saved</a>
                <a href="event.html">Events</a>
                <a href="forums.html">Forums</a>
            </nav>
            <div class="auth-buttons">
                <a href="login.html" style="text-decoration: none; color: var(--text-medium); margin-right: 10px;">Log In</a>
                <button class="btn btn-primary">Sign Up</button>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="row" style="display: flex; gap: 20px; margin-top: 20px;">
            <!-- Main Content -->
            <div style="width: 100%;">
                <div class="card">
                    <h2>Saved Posts</h2>
                    <?php if (empty($saved_posts)): ?>
                        <p style="text-align: center; color: var(--text-light);">No saved posts yet. Start saving posts you want to read later!</p>
                    <?php else: ?>
                        <?php foreach ($saved_posts as $post): ?>
                            <div class="post">
                                <div class="post-header">
                                    <div class="post-avatar"></div>
                                    <div class="post-meta">
                                        <h4 class="post-author"><?= htmlspecialchars($post['author']) ?></h4>
                                        <p class="post-time"><?= date('F j, Y g:i a', strtotime($post['created_at'])) ?></p>
                                    </div>
                                    <div class="post-actions" style="margin-left: auto; display: flex; gap: 10px;">
                                        <a href="edit.php?id=<?= htmlspecialchars($post['post_id']) ?>" 
                                           class="btn btn-outline" style="font-size: 12px; padding: 4px 8px;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete.php?id=<?= htmlspecialchars($post['post_id']) ?>" 
                                           class="btn btn-outline" style="font-size: 12px; padding: 4px 8px; color: #ff4757; border-color: #ff4757;"
                                           onclick="return confirm('Are you sure you want to delete this post?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                                <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                                <p class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                                <?php if (!empty($post['image_path'])): ?>
                                    <div class="post-image" style="margin: 15px 0;">
                                        <img src="<?= htmlspecialchars('../../' . $post['image_path']) ?>" 
                                             alt="Post image" 
                                             style="max-width: 100%; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    </div>
                                <?php endif; ?>
                                <div class="post-actions">
                                    <div class="action-buttons">
                                        <?php
                                        $reactions = $postController->getPostReactions($post['post_id']);
                                        $userReaction = $postController->getUserReaction($post['post_id'], $user_id);
                                        $isSaved = $postController->isPostSaved($post['post_id'], $user_id);
                                        ?>
                                        <form method="POST" action="cont.php" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
                                            <button type="submit" name="action" value="like" 
                                                    class="action-button <?= $userReaction === 'like' ? 'active' : '' ?>"
                                                    style="color: <?= $userReaction === 'like' ? 'var(--primary)' : 'var(--text-light)' ?>">
                                                <i class="fas fa-thumbs-up"></i> 
                                                <span><?= $reactions['likes'] ?? 0 ?></span>
                                            </button>
                                        </form>
                                        <form method="POST" action="cont.php" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
                                            <button type="submit" name="action" value="dislike"
                                                    class="action-button <?= $userReaction === 'dislike' ? 'active' : '' ?>"
                                                    style="color: <?= $userReaction === 'dislike' ? '#ff4757' : 'var(--text-light)' ?>">
                                                <i class="fas fa-thumbs-down"></i>
                                                <span><?= $reactions['dislikes'] ?? 0 ?></span>
                                            </button>
                                        </form>
                                        <a href="commentaire.php?id=<?= htmlspecialchars($post['post_id']) ?>" class="action-button">
                                            <i class="far fa-comment"></i> 
                                            <?php 
                                                $comments = $postController->getComments($post['post_id']);
                                                echo count($comments);
                                            ?>
                                        </a>
                                        <form method="POST" action="cont.php" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
                                            <button type="submit" name="action" value="save" 
                                                    class="action-button <?= $isSaved ? 'active' : '' ?>"
                                                    style="color: <?= $isSaved ? 'var(--primary)' : 'var(--text-light)' ?>">
                                                <i class="far fa-bookmark"></i>
                                            </button>
                                        </form>
                                        <button class="action-button">Share</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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