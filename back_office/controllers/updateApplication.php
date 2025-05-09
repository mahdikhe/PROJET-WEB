<?php
// Include database configuration
require_once '../model/config.php';

// Check if the request includes an application ID and required fields
if (isset($_POST['id']) && !empty($_POST['id'])) {
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
    
    // If there are validation errors, return error response
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => implode(", ", $errors)]);
        exit;
    }
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Process competences into JSON format
        $competencesArray = explode(',', $competences);
        $competencesArray = array_map('trim', $competencesArray);
        $competencesJson = json_encode([
            'langages' => implode(', ', $competencesArray),
            'softskills' => ''
        ], JSON_UNESCAPED_UNICODE);
        
        // Update the application
        $stmt = $pdo->prepare("UPDATE entretiens SET 
            competences = :competences,
            presentation = :presentation,
            motivation = :motivation,
            pourquoi_lui = :pourquoi_lui
            WHERE id = :id");
            
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':competences', $competencesJson, PDO::PARAM_STR);
        $stmt->bindParam(':presentation', $presentation, PDO::PARAM_STR);
        $stmt->bindParam(':motivation', $motivation, PDO::PARAM_STR);
        $stmt->bindParam(':pourquoi_lui', $pourquoi_lui, PDO::PARAM_STR);
        $stmt->execute();
        
        // Check if the update was successful
        if ($stmt->rowCount() > 0) {
            // Return success response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Candidature modifiée avec succès']);
        } else {
            // Return info response if no changes were made
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Aucune modification n\'a été apportée']);
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