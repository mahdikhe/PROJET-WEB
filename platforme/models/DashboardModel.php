<?php
class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getProjectStats() {
        try {
            $stats = [
                'total_projects' => $this->getTotalProjects(),
                'active_projects' => $this->getActiveProjects(),
                'completed_projects' => $this->getCompletedProjects(),
                'total_supporters' => $this->getTotalSupporters()
            ];
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting project stats: " . $e->getMessage());
            throw new Exception("Failed to retrieve project statistics");
        }
    }

    public function getRecentProjects($limit = 5) {
        try {
            $query = "SELECT p.*, COUNT(ps.id) as supporters_count 
                     FROM projects p 
                     LEFT JOIN project_supporters ps ON p.id = ps.project_id 
                     GROUP BY p.id 
                     ORDER BY p.createdAt DESC 
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent projects: " . $e->getMessage());
            throw new Exception("Failed to retrieve recent projects");
        }
    }

    public function getUserActivities($userId = null, $limit = 10) {
        try {
            $query = "SELECT 'support' as type, p.projectName, ps.supported_at as activity_date 
                     FROM project_supporters ps 
                     JOIN projects p ON ps.project_id = p.id 
                     WHERE (:userId IS NULL OR ps.supporter_id = :userId)
                     UNION ALL 
                     SELECT 'create' as type, projectName, createdAt as activity_date 
                     FROM projects 
                     WHERE (:userId IS NULL OR creator_id = :userId)
                     ORDER BY activity_date DESC 
                     LIMIT :limit";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user activities: " . $e->getMessage());
            throw new Exception("Failed to retrieve user activities");
        }
    }

    public function getUserStats($userId = null) {
        try {
            $stats = [
                'projects_created' => $this->getUserProjectsCount($userId),
                'projects_supported' => $this->getUserSupportsCount($userId),
                'active_participations' => $this->getUserActiveParticipations($userId)
            ];
            return $stats;
        } catch (PDOException $e) {
            error_log("Error getting user stats: " . $e->getMessage());
            throw new Exception("Failed to retrieve user statistics");
        }
    }

    public function getProjectMetrics($timeframe = 'week') {
        try {
            $interval = $this->getTimeframeInterval($timeframe);
            
            $query = "SELECT DATE(createdAt) as date, COUNT(*) as count 
                     FROM projects 
                     WHERE createdAt >= DATE_SUB(NOW(), INTERVAL :interval)
                     GROUP BY DATE(createdAt) 
                     ORDER BY date";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':interval', $interval);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting project metrics: " . $e->getMessage());
            throw new Exception("Failed to retrieve project metrics");
        }
    }

    public function updateUserSettings($settings) {
        try {
            $allowedSettings = ['notification_preferences', 'display_preferences', 'privacy_settings'];
            $filteredSettings = array_intersect_key($settings, array_flip($allowedSettings));
            
            if (empty($filteredSettings)) {
                throw new Exception("No valid settings provided");
            }

            $query = "UPDATE user_settings SET ";
            $params = [];
            
            foreach ($filteredSettings as $key => $value) {
                $query .= "$key = :$key, ";
                $params[":$key"] = $value;
            }
            
            $query = rtrim($query, ", ");
            $query .= " WHERE user_id = :userId";
            $params[':userId'] = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating user settings: " . $e->getMessage());
            throw new Exception("Failed to update user settings");
        }
    }

    private function getTotalProjects() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM projects");
        return $stmt->fetchColumn();
    }

    private function getActiveProjects() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM projects WHERE status = 'active'");
        return $stmt->fetchColumn();
    }

    private function getCompletedProjects() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM projects WHERE status = 'completed'");
        return $stmt->fetchColumn();
    }

    private function getTotalSupporters() {
        $stmt = $this->db->query("SELECT COUNT(DISTINCT supporter_id) FROM project_supporters");
        return $stmt->fetchColumn();
    }

    private function getUserProjectsCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM projects WHERE creator_id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getUserSupportsCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM project_supporters WHERE supporter_id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getUserActiveParticipations($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM project_supporters ps 
            JOIN projects p ON ps.project_id = p.id 
            WHERE ps.supporter_id = :userId AND p.status = 'active'
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getTimeframeInterval($timeframe) {
        switch ($timeframe) {
            case 'day':
                return '1 DAY';
            case 'week':
                return '1 WEEK';
            case 'month':
                return '1 MONTH';
            case 'year':
                return '1 YEAR';
            default:
                return '1 WEEK';
        }
    }
}
