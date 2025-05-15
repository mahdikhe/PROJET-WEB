<?php

class ContributionModel {
    private $db;
    private $uploadDir;

    public function __construct($db) {
        $this->db = $db;
        $this->uploadDir = __DIR__ . '/../views/frontoffice/uploads/';
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function getAllContributions() {
        $sql = "SELECT c.*, p.projectName, u.username 
                FROM contributions c 
                LEFT JOIN projects p ON c.project_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContributionById($id) {
        $sql = "SELECT c.*, p.projectName, u.username 
                FROM contributions c 
                LEFT JOIN projects p ON c.project_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createContribution($data) {
        $sql = "INSERT INTO contributions (project_id, user_id, contribution_type, description, amount, status, created_at)
                VALUES (:project_id, :user_id, :contribution_type, :description, :amount, :status, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':project_id' => $data['project_id'],
            ':user_id' => $data['user_id'],
            ':contribution_type' => $data['contribution_type'],
            ':description' => $data['description'],
            ':amount' => $data['amount'] ?? 0.00,
            ':status' => $data['status']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updateContribution($id, $data) {
        $sql = "UPDATE contributions SET 
                status = :status,
                admin_notes = :admin_notes,
                updated_at = NOW()
                WHERE id = :id";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':status' => $data['status'],
            ':admin_notes' => $data['admin_notes']
        ]);
    }

    public function deleteContribution($id) {
        $sql = "DELETE FROM contributions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Legacy method kept for backward compatibility
    public function saveContribution($data) {
        $contributionData = [
            'project_id' => $data['project_id'],
            'user_id' => $_SESSION['user_id'] ?? null,
            'contribution_type' => $data['type'],
            'description' => $data['message'],
            'status' => 'pending',
            'amount' => 0.00
        ];
        return $this->createContribution($contributionData);
    }

    public function validateContribution($data) {
        $errors = [];
        
        if (empty($data['project_id'])) $errors[] = "Project ID is required";
        if (empty($data['user_id'])) $errors[] = "User ID is required";
        if (empty($data['contribution_type'])) $errors[] = "Contribution type is required";
        if (empty($data['description'])) $errors[] = "Description is required";
        
        return $errors;
    }

    public function saveContributorDetails($data) {
        try {
            $sql = "INSERT INTO contributors (
                first_name, last_name, email, city, phone, 
                age_group, preferred_project, location_availability, 
                contribution_type, message, file_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['city'],
                $data['phone'],
                $data['age'],
                $data['projectId'],
                $data['availability'],
                $data['type'],
                $data['message'],
                $data['filePath'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Error saving contributor: " . $e->getMessage());
            throw new Exception("Failed to save contribution");
        }
    }

    public function handleFileUpload($file) {
        if (empty($file['name'])) {
            return "";
        }

        $filename = time() . "_" . basename($file['name']);
        $targetPath = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload file");
        }

        return $targetPath;
    }

    public function validateContributorInput($data) {
        $errors = [];
        
        if (empty($data['firstName'])) $errors[] = "First name is required";
        if (empty($data['lastName'])) $errors[] = "Last name is required";
        if (empty($data['email'])) $errors[] = "Email is required";
        if (empty($data['projectId'])) $errors[] = "Project ID is required";
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        return $errors;
    }
}