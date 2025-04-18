<?php
require_once 'C:\xampp\htdocs\blog\config.php';

class PostController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // Add a new post
    public function addPost($title, $content, $author) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO post (title, content, author, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $author]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Update an existing post
    public function updatePost($id, $title, $content, $author) {
        try {
            $stmt = $this->pdo->prepare("UPDATE post SET title = ?, content = ?, author = ? WHERE id = ?");
            $stmt->execute([$title, $content, $author, $id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete a post
    public function deletePost($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM post WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Get a post by ID
    public function getPostById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM post WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Get a post by author and title
    public function getPostByAuthorAndTitle($author, $title) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM post WHERE author = ? AND title = ?");
            $stmt->execute([$author, $title]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Get all posts
    public function getAllPosts() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM post ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postController = new PostController();

    // Add Post
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];

        if ($postController->addPost($title, $content, $author)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to add post";
        }
    }

    // Update Post
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author = $_POST['author'];

        if ($postController->updatePost($id, $title, $content, $author)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to update post";
        }
    }

    // Delete Post
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];

        if ($postController->deletePost($id)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to delete post";
        }
    }
}
?>
