<?php
/**
 * Class PostManager
 * Handles all post-related operations including create, read, update, and delete
 */
class PostManager {
    private $pdo;
    private $lastError;
    private $lastInsertId;
    private $postData;
    
    /**
     * Constructor - initializes database connection and creates table if needed
     */
    public function __construct() {
        $this->pdo = Config::getConnexion();
        $this->lastError = null;
        $this->lastInsertId = null;
        $this->postData = [];
        $this->createTableIfNotExists();
    }

    /**
     * Creates the posts table if it doesn't exist
     */
    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS posts (
            post_id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            author VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )";
        
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating posts table: " . $e->getMessage());
        }
    }
    
    /**
     * Set post data from form or other source
     * 
     * @param array $data Array of post data
     * @return PostManager Instance for method chaining
     */
    public function setPostData($data) {
        $this->postData = $data;
        return $this;
    }
    
    /**
     * Get current post data
     * 
     * @return array Current post data
     */
    public function getPostData() {
        return $this->postData;
    }
    
    /**
     * Set a specific field in post data
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @return PostManager Instance for method chaining
     */
    public function setField($field, $value) {
        $this->postData[$field] = $value;
        return $this;
    }
    
    /**
     * Get a specific field from post data
     * 
     * @param string $field Field name
     * @return mixed Field value or null if not set
     */
    public function getField($field) {
        return $this->postData[$field] ?? null;
    }
    
    /**
     * Get the last error message
     * 
     * @return string|null Last error message or null if no error
     */
    public function getLastError() {
        return $this->lastError;
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return int|null Last inserted ID or null if no insert
     */
    public function getLastInsertId() {
        return $this->lastInsertId;
    }
}

/**
 * Class Post
 * Represents a blog post
 */
class Post {
    private ?int $post_id;
    private string $title;
    private string $content;
    private string $author;
    private string $created_at;
    private string $updated_at;

    public function __construct(
        string $title,
        string $content,
        string $author,
        string $created_at = '',
        string $updated_at = '',
        ?int $post_id = null
    ) {
        $this->post_id = $post_id;
        $this->title = $title;
        $this->content = $content;
        $this->author = $author;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
        $this->updated_at = $updated_at ?: date('Y-m-d H:i:s');
    }

    // Getters
    public function getPostId(): ?int { return $this->post_id; }
    public function getTitle(): string { return $this->title; }
    public function getContent(): string { return $this->content; }
    public function getAuthor(): string { return $this->author; }
    public function getCreatedAt(): string { return $this->created_at; }
    public function getUpdatedAt(): string { return $this->updated_at; }

    // Setters
    public function setPostId(?int $post_id): self { 
        $this->post_id = $post_id; 
        return $this; 
    }
    public function setTitle(string $title): self { 
        $this->title = $title; 
        return $this; 
    }
    public function setContent(string $content): self { 
        $this->content = $content; 
        return $this; 
    }
    public function setAuthor(string $author): self { 
        $this->author = $author; 
        return $this; 
    }
    public function setCreatedAt(string $created_at): self { 
        $this->created_at = $created_at; 
        return $this; 
    }
    public function setUpdatedAt(string $updated_at): self { 
        $this->updated_at = $updated_at; 
        return $this; 
    }

    /**
     * Validate the post data
     * @throws Exception if validation fails
     */
    public function validate(): void {
        if (empty($this->title)) {
            throw new Exception("Title is required");
        }
        if (empty($this->content)) {
            throw new Exception("Content is required");
        }
        if (empty($this->author)) {
            throw new Exception("Author is required");
        }
        if (strlen($this->title) > 255) {
            throw new Exception("Title is too long");
        }
        if (strlen($this->author) > 100) {
            throw new Exception("Author name is too long");
        }
    }
}

/**
 * Class Comment
 * Represents a blog post comment
 */
class Comment {
    private ?int $id;
    private string $comment;
    private int $post_id;

    public function __construct(
        string $comment,
        int $post_id,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->comment = $comment;
        $this->post_id = $post_id;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getComment(): string { return $this->comment; }
    public function getPostId(): int { return $this->post_id; }

    // Setters
    public function setId(?int $id): self { 
        $this->id = $id; 
        return $this; 
    }
    public function setComment(string $comment): self { 
        $this->comment = $comment; 
        return $this; 
    }
    public function setPostId(int $post_id): self { 
        $this->post_id = $post_id; 
        return $this; 
    }

    /**
     * Validate the comment data
     * @throws Exception if validation fails
     */
    public function validate(): void {
        if (empty($this->comment)) {
            throw new Exception("Comment is required");
        }
        if (empty($this->post_id)) {
            throw new Exception("Post ID is required");
        }
    }
}
?>
