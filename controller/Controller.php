<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/model.php';

class PostController {
    private $pdo;

    public function __construct() {
        error_log("Initializing PostController...");
        $this->pdo = Config::getConnexion();
        error_log("Database connection obtained in PostController");
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        try {
            error_log("Checking if posts table exists...");
            $checkTable = "SHOW TABLES LIKE 'posts'";
            $tableExists = $this->pdo->query($checkTable)->rowCount() > 0;
            
            if (!$tableExists) {
                error_log("Posts table does not exist. Creating it...");
                $sql = "CREATE TABLE IF NOT EXISTS posts (
                    post_id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    content TEXT NOT NULL,
                    author VARCHAR(100) NOT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL
                )";
                $this->pdo->exec($sql);
                error_log("Posts table created successfully");
            } else {
                error_log("Posts table already exists");
            }

            // Create comments table
            $checkCommentsTable = "SHOW TABLES LIKE 'commentaire'";
            $commentsTableExists = $this->pdo->query($checkCommentsTable)->rowCount() > 0;
            
            if (!$commentsTableExists) {
                error_log("Comments table does not exist. Creating it...");
                $sql = "CREATE TABLE IF NOT EXISTS commentaire (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    comment TEXT NOT NULL,
                    post_id INT,
                    FOREIGN KEY (post_id) REFERENCES posts(post_id)
                )";
                $this->pdo->exec($sql);
                error_log("Comments table created successfully");
            }
        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function getTotalPosts() {
        $sql = "SELECT COUNT(*) as count FROM posts";
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting total posts: " . $e->getMessage());
            return 0;
        }
    }

    public function getRecentPosts($limit = 5) {
        $sql = "SELECT COUNT(*) as count FROM posts 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        try {
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error getting recent posts: " . $e->getMessage());
            return 0;
        }
    }

    public function listPosts() {
        $sql = "SELECT * FROM posts ORDER BY created_at DESC";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listing posts: " . $e->getMessage());
            return [];
        }
    }

    public function getPost($post_id) {
        $sql = "SELECT * FROM posts WHERE post_id = :post_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting post: " . $e->getMessage());
            return null;
        }
    }

    public function addPost(Post $post) {
        try {
            error_log("Starting addPost method...");
            
            // Validate the post data
            error_log("Validating post data...");
            $post->validate();
            error_log("Post data validation successful");

            $sql = "INSERT INTO posts (
                title, content, author, created_at, updated_at
            ) VALUES (
                :title, :content, :author, :created_at, :updated_at
            )";

            error_log("Preparing SQL statement: " . $sql);
            $stmt = $this->pdo->prepare($sql);
            
            // Debug the values being inserted
            error_log("Post data to be inserted:");
            error_log("Title: " . $post->getTitle());
            error_log("Content: " . $post->getContent());
            error_log("Author: " . $post->getAuthor());
            error_log("Created at: " . $post->getCreatedAt());
            error_log("Updated at: " . $post->getUpdatedAt());

            $params = [
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'author' => $post->getAuthor(),
                'created_at' => $post->getCreatedAt(),
                'updated_at' => $post->getUpdatedAt()
            ];
            
            error_log("Executing SQL statement with parameters: " . print_r($params, true));
            $result = $stmt->execute($params);
            
            if ($result) {
                $lastId = $this->pdo->lastInsertId();
                error_log("Post added successfully. ID: " . $lastId);
                return $lastId;
            } else {
                $error = $stmt->errorInfo();
                error_log("Failed to add post. SQL Error: " . print_r($error, true));
                throw new Exception("Database error: " . $error[2]);
            }
        } catch (PDOException $e) {
            error_log("PDO Error in addPost: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("General Error in addPost: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function updatePost(Post $post) {
        $sql = "UPDATE posts SET 
            title = :title,
            content = :content,
            author = :author,
            updated_at = :updated_at
        WHERE post_id = :post_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'post_id' => $post->getPostId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'author' => $post->getAuthor(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            error_log("Error updating post: " . $e->getMessage());
            return false;
        }
    }

    public function deletePost($post_id) {
        $sql = "DELETE FROM posts WHERE post_id = :post_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['post_id' => $post_id]);
        } catch (PDOException $e) {
            error_log("Error deleting post: " . $e->getMessage());
            return false;
        }
    }

    public function sortPosts($order = 'DESC') {
        $sql = "SELECT * FROM posts ORDER BY title $order";
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error sorting posts: " . $e->getMessage());
            return [];
        }
    }

    public function getPostsByAuthor($author) {
        $sql = "SELECT * FROM posts WHERE author = :author ORDER BY created_at DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['author' => $author]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting posts by author: " . $e->getMessage());
            return [];
        }
    }

    public function searchPosts($keyword) {
        $sql = "SELECT * FROM posts 
                WHERE title LIKE :keyword 
                OR content LIKE :keyword 
                OR author LIKE :keyword 
                ORDER BY created_at DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $keyword = "%$keyword%";
            $stmt->execute(['keyword' => $keyword]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching posts: " . $e->getMessage());
            return [];
        }
    }

    public function getPostsByDateRange($start_date, $end_date) {
        $sql = "SELECT * FROM posts 
                WHERE created_at BETWEEN :start_date AND :end_date 
                ORDER BY created_at DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting posts by date range: " . $e->getMessage());
            return [];
        }
    }

    // Comment-related methods
    public function addComment($post_id, $comment) {
        $sql = "INSERT INTO commentaire (comment, post_id) VALUES (:comment, :post_id)";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'comment' => $comment,
                'post_id' => $post_id
            ]);
        } catch (PDOException $e) {
            error_log("Error adding comment: " . $e->getMessage());
            return false;
        }
    }

    public function getComment($comment_id) {
        $sql = "SELECT * FROM commentaire WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $comment_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting comment: " . $e->getMessage());
            return null;
        }
    }

    public function editComment($comment_id, $comment) {
        $sql = "UPDATE commentaire SET comment = :comment WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'comment' => $comment,
                'id' => $comment_id
            ]);
        } catch (PDOException $e) {
            error_log("Error editing comment: " . $e->getMessage());
            return false;
        }
    }

    public function getComments($post_id) {
        $sql = "SELECT * FROM commentaire WHERE post_id = :post_id ORDER BY id DESC";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting comments: " . $e->getMessage());
            return [];
        }
    }

    public function deleteComment($comment_id) {
        $sql = "DELETE FROM commentaire WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $comment_id]);
        } catch (PDOException $e) {
            error_log("Error deleting comment: " . $e->getMessage());
            return false;
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postController = new PostController();

    // Add Post
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $post = new Post(
            $_POST['title'],
            $_POST['content'],
            $_POST['author']
        );

        if ($postController->addPost($post)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to add post";
        }
    }

    // Update Post
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $post = new Post(
            $_POST['title'],
            $_POST['content'],
            $_POST['author']
        );
        $post->setPostId($_POST['id']);

        if ($postController->updatePost($post)) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to update post";
        }
    }

    // Delete Post
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if ($postController->deletePost($_POST['id'])) {
            header("Location: cont.php");
            exit();
        } else {
            $error = "Failed to delete post";
        }
    }

    // Add Comment
    if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
        if ($postController->addComment($_POST['post_id'], $_POST['comment'])) {
            header("Location: cont.php?id=" . $_POST['post_id']);
            exit();
        } else {
            $error = "Failed to add comment";
        }
    }

    // Delete Comment
    if (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
        if ($postController->deleteComment($_POST['comment_id'])) {
            header("Location: cont.php?id=" . $_POST['post_id']);
            exit();
        } else {
            $error = "Failed to delete comment";
        }
    }

    // Edit Comment
    if (isset($_POST['action']) && $_POST['action'] === 'edit_comment') {
        if ($postController->editComment($_POST['comment_id'], $_POST['comment'])) {
            header("Location: commentaire.php?id=" . $_POST['post_id']);
            exit();
        } else {
            $error = "Failed to edit comment";
        }
    }
}
?> 