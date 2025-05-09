<?php
// Controller for getting candidate ratings

require_once '../model/config.php';
require_once '../model/CandidateRating.php';

// Check if request has application ID
if (isset($_GET['entretien_id']) && !empty($_GET['entretien_id'])) {
    $entretienId = intval($_GET['entretien_id']);
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create a CandidateRating instance
        $ratingModel = new CandidateRating($pdo);
        
        // Get rating data
        $ratings = $ratingModel->getAllRatingsForApplication($entretienId);
        $statistics = $ratingModel->getRatingStatistics($entretienId);
        
        // Return the ratings data
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'ratings' => $ratings,
            'statistics' => $statistics
        ]);
        exit;
        
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