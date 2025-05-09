<?php
// CandidateRating model for handling candidate rating and feedback operations

class CandidateRating {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Add or update a candidate rating
     * 
     * @param int $entretienId The application ID
     * @param int $raterId The ID of the user doing the rating
     * @param array $ratingData The rating data (technical_skills, communication, etc.)
     * @return bool|int Returns the rating ID on success, false on failure
     */
    public function saveRating($entretienId, $raterId, $ratingData) {
        try {
            // Check if rating already exists
            $checkStmt = $this->pdo->prepare("SELECT id FROM candidate_ratings WHERE entretien_id = :entretien_id AND rater_id = :rater_id");
            $checkStmt->bindParam(':entretien_id', $entretienId, PDO::PARAM_INT);
            $checkStmt->bindParam(':rater_id', $raterId, PDO::PARAM_INT);
            $checkStmt->execute();
            $existingRating = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingRating) {
                // Update existing rating
                $sql = "UPDATE candidate_ratings SET 
                        technical_skills = :technical_skills,
                        communication = :communication,
                        experience = :experience,
                        cultural_fit = :cultural_fit,
                        overall_rating = :overall_rating,
                        strengths = :strengths,
                        weaknesses = :weaknesses,
                        general_feedback = :general_feedback,
                        interview_notes = :interview_notes,
                        recommendation = :recommendation,
                        updated_at = NOW()
                        WHERE id = :id";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':id', $existingRating['id'], PDO::PARAM_INT);
            } else {
                // Insert new rating
                $sql = "INSERT INTO candidate_ratings (
                        entretien_id, rater_id, technical_skills, communication, 
                        experience, cultural_fit, overall_rating, strengths, 
                        weaknesses, general_feedback, interview_notes, recommendation
                    ) VALUES (
                        :entretien_id, :rater_id, :technical_skills, :communication, 
                        :experience, :cultural_fit, :overall_rating, :strengths, 
                        :weaknesses, :general_feedback, :interview_notes, :recommendation
                    )";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':entretien_id', $entretienId, PDO::PARAM_INT);
                $stmt->bindParam(':rater_id', $raterId, PDO::PARAM_INT);
            }
            
            // Bind common parameters
            $stmt->bindParam(':technical_skills', $ratingData['technical_skills'], PDO::PARAM_INT);
            $stmt->bindParam(':communication', $ratingData['communication'], PDO::PARAM_INT);
            $stmt->bindParam(':experience', $ratingData['experience'], PDO::PARAM_INT);
            $stmt->bindParam(':cultural_fit', $ratingData['cultural_fit'], PDO::PARAM_INT);
            $stmt->bindParam(':overall_rating', $ratingData['overall_rating'], PDO::PARAM_INT);
            $stmt->bindParam(':strengths', $ratingData['strengths'], PDO::PARAM_STR);
            $stmt->bindParam(':weaknesses', $ratingData['weaknesses'], PDO::PARAM_STR);
            $stmt->bindParam(':general_feedback', $ratingData['general_feedback'], PDO::PARAM_STR);
            $stmt->bindParam(':interview_notes', $ratingData['interview_notes'], PDO::PARAM_STR);
            $stmt->bindParam(':recommendation', $ratingData['recommendation'], PDO::PARAM_STR);
            
            $stmt->execute();
            
            // Update average rating in entretiens table
            $this->updateAverageRating($entretienId);
            
            if ($existingRating) {
                return $existingRating['id'];
            } else {
                return $this->pdo->lastInsertId();
            }
            
        } catch (PDOException $e) {
            error_log("Error saving rating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a candidate rating by application ID and rater ID
     * 
     * @param int $entretienId The application ID
     * @param int $raterId The ID of the user who did the rating
     * @return array|false Returns the rating data or false if not found
     */
    public function getRatingByRater($entretienId, $raterId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM candidate_ratings 
                                       WHERE entretien_id = :entretien_id 
                                       AND rater_id = :rater_id");
            $stmt->bindParam(':entretien_id', $entretienId, PDO::PARAM_INT);
            $stmt->bindParam(':rater_id', $raterId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting rating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all ratings for a candidate application
     * 
     * @param int $entretienId The application ID
     * @return array|false Returns an array of ratings or false on error
     */
    public function getAllRatingsForApplication($entretienId) {
        try {
            $stmt = $this->pdo->prepare("SELECT cr.*, u.nom as rater_nom, u.prenom as rater_prenom
                                       FROM candidate_ratings cr
                                       LEFT JOIN users u ON cr.rater_id = u.id
                                       WHERE cr.entretien_id = :entretien_id
                                       ORDER BY cr.created_at DESC");
            $stmt->bindParam(':entretien_id', $entretienId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting ratings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update the average rating for a candidate application
     * 
     * @param int $entretienId The application ID
     * @return bool Returns true on success, false on failure
     */
    public function updateAverageRating($entretienId) {
        try {
            // Calculate average of overall_rating for all ratings of this application
            $stmt = $this->pdo->prepare("SELECT AVG(overall_rating) as avg_rating 
                                       FROM candidate_ratings 
                                       WHERE entretien_id = :entretien_id");
            $stmt->bindParam(':entretien_id', $entretienId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['avg_rating'])) {
                // Update the average rating in the entretiens table
                $updateStmt = $this->pdo->prepare("UPDATE entretiens 
                                                SET avg_rating = :avg_rating 
                                                WHERE id = :id");
                $updateStmt->bindParam(':avg_rating', $result['avg_rating'], PDO::PARAM_STR);
                $updateStmt->bindParam(':id', $entretienId, PDO::PARAM_INT);
                $updateStmt->execute();
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error updating average rating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a rating
     * 
     * @param int $ratingId The rating ID to delete
     * @return bool Returns true on success, false on failure
     */
    public function deleteRating($ratingId) {
        try {
            // Get entretien_id before deleting
            $getStmt = $this->pdo->prepare("SELECT entretien_id FROM candidate_ratings WHERE id = :id");
            $getStmt->bindParam(':id', $ratingId, PDO::PARAM_INT);
            $getStmt->execute();
            $result = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false;
            }
            
            $entretienId = $result['entretien_id'];
            
            // Delete the rating
            $stmt = $this->pdo->prepare("DELETE FROM candidate_ratings WHERE id = :id");
            $stmt->bindParam(':id', $ratingId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Update average rating
            $this->updateAverageRating($entretienId);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error deleting rating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get rating statistics for a specific application
     * 
     * @param int $entretienId The application ID
     * @return array|false Returns rating statistics or false on error
     */
    public function getRatingStatistics($entretienId) {
        try {
            $stmt = $this->pdo->prepare("SELECT 
                                           COUNT(*) as total_ratings,
                                           AVG(technical_skills) as avg_technical,
                                           AVG(communication) as avg_communication,
                                           AVG(experience) as avg_experience,
                                           AVG(cultural_fit) as avg_cultural_fit,
                                           AVG(overall_rating) as avg_overall,
                                           COUNT(CASE WHEN recommendation = 'Hire' THEN 1 END) as hire_count,
                                           COUNT(CASE WHEN recommendation = 'Consider' THEN 1 END) as consider_count,
                                           COUNT(CASE WHEN recommendation = 'Reject' THEN 1 END) as reject_count
                                       FROM candidate_ratings
                                       WHERE entretien_id = :entretien_id");
            $stmt->bindParam(':entretien_id', $entretienId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting rating statistics: " . $e->getMessage());
            return false;
        }
    }
}
?>