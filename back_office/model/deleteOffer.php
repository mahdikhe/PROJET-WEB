<?php
// Ensure no errors or warnings are output to the response
error_reporting(0);

// Include database configuration
require_once 'config.php';

// Check if the ID parameter is set
if (isset($_POST['id'])) {
    // Get the offer ID from the POST request
    $id = $_POST['id'];
    
    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start a transaction
        $pdo->beginTransaction();
        
        // Prepare SQL statement to delete the offer with the given ID
        $stmt = $pdo->prepare("DELETE FROM offres WHERE id = :id");
        
        // Bind parameter
        $stmt->bindParam(':id', $id);
        
        // Execute the statement
        $stmt->execute();
        
        // Commit the transaction
        $pdo->commit();
        
        // Check if a row was affected
        if ($stmt->rowCount() > 0) {
            // Return success response
            echo json_encode(['success' => true, 'message' => 'Offre supprimée avec succès']);
        } else {
            // No offer with the given ID was found
            echo json_encode(['success' => false, 'message' => 'Aucune offre avec cet ID trouvée']);
        }
    } catch (PDOException $e) {
        // Roll back the transaction if something failed
        $pdo->rollBack();
        
        // Return error response
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    }
} else {
    // Return error if no ID was provided
    echo json_encode(['success' => false, 'message' => 'ID de l\'offre manquant']);
}
exit; // Ensure no additional content is sent
?>