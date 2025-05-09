<?php
// Include database configuration
require_once '../model/config.php';

// Check if the request includes an application ID
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Delete the application
        $stmt = $pdo->prepare("DELETE FROM entretiens WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Check if the deletion was successful
        if ($stmt->rowCount() > 0) {
            // Return success response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Candidature supprimée avec succès']);
        } else {
            // Return error response if no rows were affected
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Candidature non trouvée']);
        }
        exit;
        
    } catch (PDOException $e) {
        // Return error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
        exit;
    }
} else {
    // Return error response for missing ID
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de candidature manquant']);
    exit;
}
?>