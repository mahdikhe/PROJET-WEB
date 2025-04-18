<?php

class comment {
    private ?int $id = null;
    private int $post_id;
    private string $commenter_name;
    private string $comment;
    private ?string $created_at = null;

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getPostId(): int {
        return $this->post_id;
    }

    public function getCommenterName(): string {
        return $this->commenter_name;
    }

    public function getComment(): string {
        return $this->comment;
    }

    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setPostId(int $post_id): void {
        $this->post_id = $post_id;
    }

    public function setCommenterName(string $commenter_name): void {
        $this->commenter_name = $commenter_name;
    }

    public function setComment(string $comment): void {
        $this->comment = $comment;
    }

    public function setCreatedAt(?string $created_at): void {
        $this->created_at = $created_at;
    }
}



















?>