<?php
class ProjectModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createProject($data) {
        try {
            $query = "INSERT INTO projects (
                projectName, projectDescription, startDate, endDate,
                projectLocation, projectCategory, projectTags, teamSize,
                projectBudget, fundingGoal, skillsNeeded, projectVisibility,
                projectImage, projectWebsite, is_paid, ticket_price, created_at
            ) VALUES (
                :name, :description, :startDate, :endDate,
                :location, :category, :tags, :teamSize,
                :budget, :fundingGoal, :skills, :visibility,
                :image, :website, :isPaid, :ticketPrice, NOW()
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':name' => $data['projectName'],
                ':description' => $data['projectDescription'],
                ':startDate' => $data['startDate'],
                ':endDate' => $data['endDate'],
                ':location' => $data['projectLocation'],
                ':category' => $data['projectCategory'],
                ':tags' => $data['projectTags'],
                ':teamSize' => $data['teamSize'],
                ':budget' => $data['projectBudget'],
                ':fundingGoal' => $data['fundingGoal'],
                ':skills' => $data['skillsNeeded'],
                ':visibility' => $data['projectVisibility'],
                ':image' => $data['projectImage'],
                ':website' => $data['projectWebsite'] ?? null,
                ':isPaid' => $data['isPaid'] ?? 0,
                ':ticketPrice' => $data['ticketPrice'] ?? 0.00
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating project: " . $e->getMessage());
            throw new Exception("Failed to create project");
        }
    }

    public function getProjectById($id) {
        try {
            $query = "SELECT p.*, COUNT(ps.id) as supporters_count,
                     EXISTS(SELECT 1 FROM project_supporters ps WHERE ps.project_id = p.id AND ps.supporter_id = :userId) as is_supported
                     FROM projects p 
                     LEFT JOIN project_supporters ps ON p.id = ps.project_id 
                     WHERE p.id = :id 
                     GROUP BY p.id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $_SESSION['user_id'] ?? 0, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting project by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getAllProjects($filters = []) {
        try {
            $query = "SELECT p.*, COUNT(ps.id) as supporters_count,
                     EXISTS(SELECT 1 FROM project_supporters ps WHERE ps.project_id = p.id AND ps.supporter_id = :userId) as is_supported,
                     DATEDIFF(COALESCE(p.endDate, CURRENT_DATE), p.startDate) as project_duration
                     FROM projects p 
                     LEFT JOIN project_supporters ps ON p.id = ps.project_id";
            
            $whereConditions = [];
            $params = [':userId' => $_SESSION['user_id'] ?? 0];

            if (!empty($filters['category'])) {
                $whereConditions[] = "p.projectCategory = :category";
                $params[':category'] = $filters['category'];
            }

            if (!empty($filters['search'])) {
                $whereConditions[] = "(p.projectName LIKE :search OR p.projectDescription LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(" AND ", $whereConditions);
            }

            $query .= " GROUP BY p.id";

            if (!empty($filters['sort'])) {
                $sortField = $filters['sort'];
                $sortOrder = $filters['order'] ?? 'ASC';
                if ($sortField === 'teamSize') {
                    $query .= " ORDER BY CAST(p.teamSize AS SIGNED) " . $sortOrder;
                } else if ($sortField === 'supporters_count') {
                    $query .= " ORDER BY supporters_count " . $sortOrder;
                } else if ($sortField === 'project_duration') {
                    $query .= " ORDER BY project_duration " . $sortOrder;
                } else {
                    $query .= " ORDER BY p." . $sortField . " " . $sortOrder;
                }
            } else {
                $query .= " ORDER BY p.created_at DESC";
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all projects: " . $e->getMessage());
            return [];
        }
    }

    public function updateProject($id, $data) {
        try {
            $query = "UPDATE projects SET 
                projectName = :name,
                projectDescription = :description,
                startDate = :startDate,
                endDate = :endDate,
                projectLocation = :location,
                projectCategory = :category,
                projectTags = :tags,
                teamSize = :teamSize,
                projectBudget = :budget,
                fundingGoal = :fundingGoal,
                skillsNeeded = :skills,
                projectVisibility = :visibility,
                projectWebsite = :website
                " . (!empty($data['projectImage']) ? ", projectImage = :image" : "") . "
                WHERE id = :id";

            $params = [
                ':id' => $id,
                ':name' => $data['projectName'],
                ':description' => $data['projectDescription'],
                ':startDate' => $data['startDate'],
                ':endDate' => $data['endDate'],
                ':location' => $data['projectLocation'],
                ':category' => $data['projectCategory'],
                ':tags' => $data['projectTags'],
                ':teamSize' => $data['teamSize'],
                ':budget' => $data['projectBudget'],
                ':fundingGoal' => $data['fundingGoal'],
                ':skills' => $data['skillsNeeded'],
                ':visibility' => $data['projectVisibility'],
                ':website' => $data['projectWebsite']
            ];

            if (!empty($data['projectImage'])) {
                $params[':image'] = $data['projectImage'];
            }

            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating project: " . $e->getMessage());
            throw new Exception("Failed to update project");
        }
    }

    public function deleteProject($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete related records in project_supporters
            $stmt = $this->db->prepare("DELETE FROM project_supporters WHERE project_id = ?");
            $stmt->execute([$id]);
            
            // Delete the project
            $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error deleting project: " . $e->getMessage());
            throw new Exception("Failed to delete project");
        }
    }

    public function addProjectSupport($projectId, $supporterId) {
        try {
            // Check if support already exists
            $stmt = $this->db->prepare("SELECT id FROM project_supporters WHERE project_id = ? AND supporter_id = ?");
            $stmt->execute([$projectId, $supporterId]);
            if ($stmt->fetch()) {
                throw new Exception("Already supporting this project");
            }

            $stmt = $this->db->prepare("INSERT INTO project_supporters (project_id, supporter_id, supported_at) VALUES (?, ?, NOW())");
            return $stmt->execute([$projectId, $supporterId]);
        } catch (PDOException $e) {
            error_log("Error adding project support: " . $e->getMessage());
            throw new Exception("Failed to add support");
        }
    }

    public function removeProjectSupport($projectId, $supporterId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM project_supporters WHERE project_id = ? AND supporter_id = ?");
            return $stmt->execute([$projectId, $supporterId]);
        } catch (PDOException $e) {
            error_log("Error removing project support: " . $e->getMessage());
            throw new Exception("Failed to remove support");
        }
    }
}


