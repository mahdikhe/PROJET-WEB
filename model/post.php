<?php 

class post {
    private ?int $id = null;
    private string $title;
    private string $content;
    private ?string $author = null;
    private ?string $created_at = null;

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getAuthor(): ?string {
        return $this->author;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setAuthor(?string $author): void {
        $this->author = $author;
    }

    public function setCreatedAt(?string $created_at): void {
        $this->created_at = $created_at;
    }
}






























?>