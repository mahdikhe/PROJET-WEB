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

// Check if the request includes required fields
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Location: ../view/mes-candidatures.php?error=' . urlencode('ID de candidature manquant'));
    exit;
}

$id = $_POST['id'];
$competences = isset($_POST['competences']) ? $_POST['competences'] : '';
$presentation = isset($_POST['presentation']) ? $_POST['presentation'] : '';
$motivation = isset($_POST['motivation']) ? $_POST['motivation'] : '';
$pourquoi_lui = isset($_POST['pourquoi_lui']) ? $_POST['pourquoi_lui'] : '';

// Server-side validation
$errors = [];

if (empty($competences)) {
    $errors[] = "Les compétences sont requises";
}

if (empty($presentation)) {
    $errors[] = "La présentation est requise";
} elseif (str_word_count($presentation) < 5) {
    $errors[] = "La présentation doit contenir au moins 5 mots";
} elseif (str_word_count($presentation) > 255) {
    $errors[] = "La présentation ne doit pas dépasser 255 mots";
}

if (empty($motivation)) {
    $errors[] = "La motivation est requise";
} elseif (str_word_count($motivation) < 5) {
    $errors[] = "La motivation doit contenir au moins 5 mots";
} elseif (str_word_count($motivation) > 255) {
    $errors[] = "La motivation ne doit pas dépasser 255 mots";
}

if (empty($pourquoi_lui)) {
    $errors[] = "Le champ 'Pourquoi vous ?' est requis";
} elseif (str_word_count($pourquoi_lui) < 5) {
    $errors[] = "Le champ 'Pourquoi vous ?' doit contenir au moins 5 mots";
} elseif (str_word_count($pourquoi_lui) > 255) {
    $errors[] = "Le champ 'Pourquoi vous ?' ne doit pas dépasser 255 mots";
}

// If there are validation errors, redirect back with error message
if (!empty($errors)) {
    header('Location: ../view/mes-candidatures.php?error=' . urlencode(implode(", ", $errors)));
    exit;
}

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
        header('Location: ../view/mes-candidatures.php?error=' . urlencode('Vous ne pouvez pas modifier cette candidature'));
        exit;
    }
    
    // Process competences into JSON format
    $competencesArray = explode(',', $competences);
    $competencesArray = array_map('trim', $competencesArray);
    $competencesJson = json_encode([
        'langages' => implode(', ', $competencesArray),
        'softskills' => ''
    ], JSON_UNESCAPED_UNICODE);
    
    // Update the application in the database
    $stmt = $pdo->prepare("UPDATE entretiens SET 
        competences = :competences,
        presentation = :presentation,
        motivation = :motivation,
        pourquoi_lui = :pourquoi_lui
        WHERE id = :id AND id_user = :user_id");
        
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':competences', $competencesJson, PDO::PARAM_STR);
    $stmt->bindParam(':presentation', $presentation, PDO::PARAM_STR);
    $stmt->bindParam(':motivation', $motivation, PDO::PARAM_STR);
    $stmt->bindParam(':pourquoi_lui', $pourquoi_lui, PDO::PARAM_STR);
    $stmt->execute();
    
    // Redirect back to applications page with success message
    header('Location: ../view/mes-candidatures.php?success=' . urlencode('Votre candidature a été mise à jour avec succès'));
    exit;
    
} catch (PDOException $e) {
    // Log error or handle exception
    error_log("Database error in updateUserApplication: " . $e->getMessage());
    header('Location: ../view/mes-candidatures.php?error=' . urlencode('Une erreur est survenue lors de la mise à jour de votre candidature'));
    exit;
}
?>