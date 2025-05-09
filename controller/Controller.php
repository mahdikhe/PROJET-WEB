<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/model.php';

class PostController {
    private $pdo;

    public function __construct() {
        error_log("Initializing PostController...");
        try {
            $this->pdo = Config::getConnexion();
            $this->createTables();
        } catch (PDOException $e) {
            error_log("Error connecting to database: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    private function createTables() {
        try {
            // Create posts table
            $sql = "CREATE TABLE IF NOT EXISTS posts (
                post_id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                author VARCHAR(100) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                image_path VARCHAR(255)
            )";
            $this->pdo->exec($sql);

            // Check if image_path column exists, if not add it
            $checkColumn = "SHOW COLUMNS FROM posts LIKE 'image_path'";
            $columnExists = $this->pdo->query($checkColumn)->rowCount() > 0;
            
            if (!$columnExists) {
                error_log("Adding image_path column to posts table...");
                $alterSql = "ALTER TABLE posts ADD COLUMN image_path VARCHAR(255)";
                $this->pdo->exec($alterSql);
                error_log("image_path column added successfully");
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

            // Create likes table
            $checkLikesTable = "SHOW TABLES LIKE 'post_likes'";
            $likesTableExists = $this->pdo->query($checkLikesTable)->rowCount() > 0;
            
            if (!$likesTableExists) {
                error_log("Likes table does not exist. Creating it...");
                $sql = "CREATE TABLE IF NOT EXISTS post_likes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    user_id VARCHAR(100) NOT NULL,
                    type ENUM('like', 'dislike') NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
                    UNIQUE KEY unique_user_post (post_id, user_id)
                )";
                $this->pdo->exec($sql);
                error_log("Likes table created successfully");
            }

            // Create saved posts table
            $checkSavedTable = "SHOW TABLES LIKE 'saved_posts'";
            $savedTableExists = $this->pdo->query($checkSavedTable)->rowCount() > 0;
            
            if (!$savedTableExists) {
                error_log("Saved posts table does not exist. Creating it...");
                $sql = "CREATE TABLE IF NOT EXISTS saved_posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    user_id VARCHAR(100) NOT NULL,
                    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
                    UNIQUE KEY unique_user_post (post_id, user_id)
                )";
                $this->pdo->exec($sql);
                error_log("Saved posts table created successfully");
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
                title, content, author, created_at, updated_at, image_path
            ) VALUES (
                :title, :content, :author, :created_at, :updated_at, :image_path
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
            error_log("Image path: " . $post->getImagePath());

            $params = [
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'author' => $post->getAuthor(),
                'created_at' => $post->getCreatedAt(),
                'updated_at' => $post->getUpdatedAt(),
                'image_path' => $post->getImagePath()
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
            updated_at = :updated_at,
            image_path = :image_path
        WHERE post_id = :post_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'post_id' => $post->getPostId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'author' => $post->getAuthor(),
                'updated_at' => date('Y-m-d H:i:s'),
                'image_path' => $post->getImagePath()
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

    // Like/Dislike methods
    public function toggleLike($post_id, $user_id, $type) {
        try {
            // First check if user already has a reaction
            $sql = "SELECT type FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['type'] === $type) {
                    // If same type, remove the reaction
                    $sql = "DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
                    $stmt = $this->pdo->prepare($sql);
                    return $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
                } else {
                    // If different type, update the reaction
                    $sql = "UPDATE post_likes SET type = :type WHERE post_id = :post_id AND user_id = :user_id";
                    $stmt = $this->pdo->prepare($sql);
                    return $stmt->execute(['type' => $type, 'post_id' => $post_id, 'user_id' => $user_id]);
                }
            } else {
                // If no existing reaction, add new one
                $sql = "INSERT INTO post_likes (post_id, user_id, type) VALUES (:post_id, :user_id, :type)";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id, 'type' => $type]);
            }
        } catch (PDOException $e) {
            error_log("Error toggling like: " . $e->getMessage());
            return false;
        }
    }

    public function getPostReactions($post_id) {
        try {
            $sql = "SELECT 
                    SUM(CASE WHEN type = 'like' THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN type = 'dislike' THEN 1 ELSE 0 END) as dislikes
                    FROM post_likes 
                    WHERE post_id = :post_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting post reactions: " . $e->getMessage());
            return ['likes' => 0, 'dislikes' => 0];
        }
    }

    public function getUserReaction($post_id, $user_id) {
        try {
            $sql = "SELECT type FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['type'] : null;
        } catch (PDOException $e) {
            error_log("Error getting user reaction: " . $e->getMessage());
            return null;
        }
    }

    // Save post methods
    public function toggleSavePost($post_id, $user_id) {
        try {
            // Check if post is already saved
            $sql = "SELECT id FROM saved_posts WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // If already saved, unsave it
                $sql = "DELETE FROM saved_posts WHERE post_id = :post_id AND user_id = :user_id";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            } else {
                // If not saved, save it
                $sql = "INSERT INTO saved_posts (post_id, user_id) VALUES (:post_id, :user_id)";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            }
        } catch (PDOException $e) {
            error_log("Error toggling save post: " . $e->getMessage());
            return false;
        }
    }

    public function isPostSaved($post_id, $user_id) {
        try {
            $sql = "SELECT id FROM saved_posts WHERE post_id = :post_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log("Error checking if post is saved: " . $e->getMessage());
            return false;
        }
    }

    public function getSavedPosts($user_id) {
        try {
            $sql = "SELECT p.* FROM posts p 
                    INNER JOIN saved_posts sp ON p.post_id = sp.post_id 
                    WHERE sp.user_id = :user_id 
                    ORDER BY sp.saved_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting saved posts: " . $e->getMessage());
            return [];
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