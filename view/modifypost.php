<?php
require 'C:\xampp\htdocs\blog\config.php';

// Function to handle post update
function updatePost($pdo, $id, $title, $content, $author) {
    try {
        $stmt = $pdo->prepare("UPDATE post SET title = ?, content = ?, author = ? WHERE id = ?");
        $stmt->execute([$title, $content, $author, $id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Check if form is submitted for search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['author']) && isset($_POST['title'])) {
    $author = $_POST['author'];
    $title = $_POST['title'];
    
    try {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM post WHERE author = ? AND title = ?");
        $stmt->execute([$author, $title]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Check if form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $newTitle = $_POST['new_title'];
    $newContent = $_POST['new_content'];
    $newAuthor = $_POST['new_author'];
    
    try {
        $pdo = config::getConnexion();
        if (updatePost($pdo, $id, $newTitle, $newContent, $newAuthor)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to update post";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Post - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="front/landingPage.html" class="logo">
                <img src="assets/logo.png" alt="CityPulse Logo" style="height: 35px; margin-right: 10px;">
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
            <div class="card" style="background-color: #ffebee; border-color: #ffcdd2;">
                <p style="color: #c62828;"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($post) && $post): ?>
            <div class="card">
                <h2 class="card-title">Modify Post</h2>
                <form action="modifypost.php" method="POST" class="form-section">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    
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
                        <button type="button" class="btn btn-outline" onclick="window.location.href='modify.html'">Cancel</button>
                        <button type="submit" name="update" class="btn btn-primary">Update Post</button>
                    </div>
                </form>
            </div>
        <?php elseif (isset($post) && !$post): ?>
            <div class="card">
                <h2 class="card-title">Post Not Found</h2>
                <p style="color: var(--text-medium);">No post found with the given author and title.</p>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="window.location.href='modify.html'">Try Again</button>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2 class="card-title">Search for Post</h2>
                <p style="color: var(--text-medium);">Please go back to the search page to find a post to modify.</p>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="window.location.href='modify.html'">Go to Search</button>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer style="background-color: var(--text-dark); color: white; padding: 48px 0; margin-top: 48px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 32px;">
                <div>
                    <h3>CityPulse</h3>
                    <p>Connect with urban planners, architects, and citizens to collaborate on innovative projects.</p>
                </div>
                <div>
                    <h4>Resources</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#" style="color: white; text-decoration: none;">Urban Planning Guide</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Sustainable City Toolkit</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Community Engagement</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Company</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#" style="color: white; text-decoration: none;">About Us</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Blog</a></li>
                        <li><a href="#" style="color: white; text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Connect</h4>
                    <div style="display: flex; gap: 16px; margin-top: 8px;">
                        <a href="#" style="color: white;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: white;"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 32px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1);">
                <p>&copy; 2025 CityPulse. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
