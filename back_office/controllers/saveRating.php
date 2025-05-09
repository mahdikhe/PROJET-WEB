<?php
// Controller for candidate rating operations

require_once '../model/config.php';
require_once '../model/CandidateRating.php';

// Check if request has application ID
if (isset($_POST['entretien_id']) && !empty($_POST['entretien_id'])) {
    $entretienId = intval($_POST['entretien_id']);
    
    // For now, we'll hardcode rater_id to 1 (admin)
    // In a real implementation, you'd get this from the session
    $raterId = 1;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create a CandidateRating instance
        $ratingModel = new CandidateRating($pdo);
        
        // Prepare rating data
        $ratingData = [
            'technical_skills' => isset($_POST['technical_skills']) ? intval($_POST['technical_skills']) : null,
            'communication' => isset($_POST['communication']) ? intval($_POST['communication']) : null,
            'experience' => isset($_POST['experience']) ? intval($_POST['experience']) : null,
            'cultural_fit' => isset($_POST['cultural_fit']) ? intval($_POST['cultural_fit']) : null,
            'overall_rating' => isset($_POST['overall_rating']) ? intval($_POST['overall_rating']) : null,
            'strengths' => isset($_POST['strengths']) ? $_POST['strengths'] : null,
            'weaknesses' => isset($_POST['weaknesses']) ? $_POST['weaknesses'] : null,
            'general_feedback' => isset($_POST['general_feedback']) ? $_POST['general_feedback'] : null,
            'interview_notes' => isset($_POST['interview_notes']) ? $_POST['interview_notes'] : null,
            'recommendation' => isset($_POST['recommendation']) ? $_POST['recommendation'] : null,
        ];
        
        // Save the rating
        $result = $ratingModel->saveRating($entretienId, $raterId, $ratingData);
        
        if ($result) {
            // Return success response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Évaluation enregistrée avec succès',
                'rating_id' => $result
            ]);
            exit;
        } else {
            // Return error response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'évaluation'
            ]);
            exit;
        }
        
    } catch (PDOException $e) {
        // Return error response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de base de données: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    // Return error response for missing ID
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'ID de candidature manquant'
    ]);
    exit;
}
?>