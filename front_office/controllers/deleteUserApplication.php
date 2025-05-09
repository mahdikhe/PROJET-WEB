<?php
// Start session
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit;
}

// Include database configuration
require_once '../../back_office/model/config.php';

// Check if the request includes an application ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Location: ../view/mes-candidatures.php?error=' . urlencode('ID de candidature manquant'));
    exit;
}

$id = $_POST['id'];

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if this application belongs to the current user
    $checkStmt = $pdo->prepare("SELECT id FROM entretiens WHERE id = :id AND id_user = :user_id");
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $checkStmt->execute();
    
    if (!$checkStmt->fetch()) {
        // Either the application doesn't exist or doesn't belong to the user
        header('Location: ../view/mes-candidatures.php?error=' . urlencode('Vous ne pouvez pas supprimer cette candidature'));
        exit;
    }
    
    // Delete the application
    $stmt = $pdo->prepare("DELETE FROM entretiens WHERE id = :id AND id_user = :user_id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    // Check if the deletion was successful
    if ($stmt->rowCount() > 0) {
        // Redirect back to applications page with success message
        header('Location: ../view/mes-candidatures.php?success=' . urlencode('Votre candidature a été supprimée avec succès'));
    } else {
        // Redirect back with error message
        header('Location: ../view/mes-candidatures.php?error=' . urlencode('Impossible de supprimer cette candidature'));
    }
    exit;
    
} catch (PDOException $e) {
    // Log error or handle exception
    error_log("Database error in deleteUserApplication: " . $e->getMessage());
    header('Location: ../view/mes-candidatures.php?error=' . urlencode('Une erreur est survenue lors de la suppression de votre candidature'));
    exit;
}
?>