<?php
// Ensure no errors or warnings are output to the response
error_reporting(0);

// Include database configuration
require_once 'config.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $id = $_POST['id'];
    $titre = $_POST['titre'];
    $entreprise = $_POST['entreprise'];
    $emplacement = $_POST['emplacement'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    
    // Simple validation
    if (empty($id) || empty($titre) || empty($entreprise) || empty($emplacement) || 
        empty($description) || empty($date) || empty($type)) {
        header("Location: ../view/offres.php?error=validation&message=Tous les champs sont obligatoires");
        exit();
    }

    try {
        // Create a new PDO instance
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start a transaction
        $pdo->beginTransaction();
        
        // Prepare SQL statement to update the offer
        $stmt = $pdo->prepare("UPDATE offres SET 
                               titre = :titre, 
                               entreprise = :entreprise, 
                               emplacement = :emplacement, 
                               description = :description, 
                               date = :date, 
                               type = :type 
                               WHERE id = :id");
        
        // Bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':titre', $titre);
        $stmt->bindParam(':entreprise', $entreprise);
        $stmt->bindParam(':emplacement', $emplacement);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':type', $type);
        
        // Execute the statement
        $stmt->execute();
        
        // Commit the transaction
        $pdo->commit();
        
        // Redirect with success message
        header("Location: ../view/offres.php?success=update&message=Offre modifiée avec succès");
        exit();
    } catch (PDOException $e) {
        // Roll back the transaction if something failed
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Redirect with error message
        header("Location: ../view/offres.php?error=db&message=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // If not a POST request, redirect to the offers page
    header("Location: ../view/offres.php");
    exit();
}
?>