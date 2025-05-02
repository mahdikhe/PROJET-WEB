<?php
namespace Models;

use PDO;
use PDOException;

class TaskModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getTaskById($id) {
        try {
            $sql = "SELECT * FROM tasks WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error fetching task: " . $e->getMessage());
        }
    }

    public function getTasks($projectId = null) {
        try {
            $sql = "SELECT * FROM tasks";
            if ($projectId) {
                $sql .= " WHERE project_id = :project_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':project_id', $projectId);
            } else {
                $stmt = $this->db->query($sql);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Error fetching tasks: " . $e->getMessage());
        }
    }

    public function updateTaskStatus($taskId, $newStatus) {
        try {
            $sql = "UPDATE tasks SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $taskId);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error updating task status: " . $e->getMessage());
        }
    }

    public function deleteTask($taskId) {
        try {
            $sql = "DELETE FROM tasks WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $taskId);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new \Exception("Error deleting task: " . $e->getMessage());
        }
    }

    public function updateTask($taskId, $data) {
        try {
            $updateFields = [];
            $params = [':id' => $taskId];
            
            $allowedFields = ['title', 'description', 'status', 'priority', 'assigned_to', 'due_date', 'estimated_hours'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $sql = "UPDATE tasks SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new \Exception("Error updating task: " . $e->getMessage());
        }
    }

    public function createTask($data) {
        try {
            $sql = "INSERT INTO tasks (project_id, title, description, status, priority, assigned_to, created_by, estimated_hours, due_date)
                    VALUES (:project_id, :title, :description, :status, :priority, :assigned_to, :created_by, :estimated_hours, :due_date)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':project_id' => $data['project_id'],
                ':title' => $data['title'],
                ':description' => $data['description'] ?? '',
                ':status' => $data['status'] ?? 'To Do',
                ':priority' => $data['priority'] ?? 'Medium',
                ':assigned_to' => $data['assigned_to'] ?? '',
                ':created_by' => $data['created_by'] ?? 'Current User',
                ':estimated_hours' => $data['estimated_hours'] ?? null,
                ':due_date' => $data['due_date'] ?? null
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Error creating task: " . $e->getMessage());
        }
    }
}