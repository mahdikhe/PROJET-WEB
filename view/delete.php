<?php
require 'C:\xampp\htdocs\blog\config.php';

// Function to handle post deletion
function deletePost($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM post WHERE id = ?");
        $stmt->execute([$id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['author']) && isset($_POST['title'])) {
    $author = $_POST['author'];
    $title = $_POST['title'];
    
    try {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM post WHERE author = ? AND title = ?");
        $stmt->execute([$author, $title]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            if (deletePost($pdo, $post['id'])) {
                header("Location: cont.php");
                exit();
            } else {
                $error = "Failed to delete post";
            }
        } else {
            $error = "No post found with the given author and title";
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
    <title>Delete Post - CityPulse</title>
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
                <div class="form-actions" style="margin-top: 20px;">
                    <button class="btn btn-primary" onclick="window.location.href='delete.html'">Try Again</button>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <h2 class="card-title">Delete Post</h2>
                <p style="color: var(--text-medium);">Please go back to the search page to find a post to delete.</p>
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="window.location.href='delete.html'">Go to Search</button>
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
