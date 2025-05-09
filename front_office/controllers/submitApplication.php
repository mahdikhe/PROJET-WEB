<?php
// Include database configuration
require_once '../../back_office/model/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: ../view/login.php?redirect=offre-detail.php?id=' . (isset($_POST['id_offre']) ? $_POST['id_offre'] : ''));
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data - keep id_offre as string since it's VARCHAR in database
    $id_offre = isset($_POST['id_offre']) ? $_POST['id_offre'] : '';
    $competences = isset($_POST['competences']) ? $_POST['competences'] : '';
    $presentation = isset($_POST['presentation']) ? $_POST['presentation'] : '';
    $motivation = isset($_POST['motivation']) ? $_POST['motivation'] : '';
    $pourquoi_lui = isset($_POST['pourquoi_lui']) ? $_POST['pourquoi_lui'] : '';
    
    // Get user ID from session
    $userId = $_SESSION['user_id'];
    
    // Server-side validation
    $errors = [];
    
    // Validate id_offre
    if (empty($id_offre)) {
        $errors[] = "L'identifiant de l'offre est manquant.";
    }
    
    // Validate competences
    if (empty($competences) || strlen($competences) < 3) {
        $errors[] = "Les compétences sont requises.";
    }
    
    // Validate presentation, motivation, pourquoi_lui - must have 5-255 words
    function countWords($text) {
        return count(preg_split('/\s+/', trim($text)));
    }
    
    if (empty($presentation)) {
        $errors[] = "La présentation est requise.";
    } else {
        $wordCount = countWords($presentation);
        if ($wordCount < 5) {
            $errors[] = "La présentation doit contenir au moins 5 mots.";
        } else if ($wordCount > 255) {
            $errors[] = "La présentation ne doit pas dépasser 255 mots.";
        }
    }
    
    if (empty($motivation)) {
        $errors[] = "La motivation est requise.";
    } else {
        $wordCount = countWords($motivation);
        if ($wordCount < 5) {
            $errors[] = "La motivation doit contenir au moins 5 mots.";
        } else if ($wordCount > 255) {
            $errors[] = "La motivation ne doit pas dépasser 255 mots.";
        }
    }
    
    if (empty($pourquoi_lui)) {
        $errors[] = "Le champ 'Pourquoi nous ?' est requis.";
    } else {
        $wordCount = countWords($pourquoi_lui);
        if ($wordCount < 5) {
            $errors[] = "Le champ 'Pourquoi nous ?' doit contenir au moins 5 mots.";
        } else if ($wordCount > 255) {
            $errors[] = "Le champ 'Pourquoi nous ?' ne doit pas dépasser 255 mots.";
        }
    }
    
    // If there are no errors, proceed with database operations
    if (empty($errors)) {
        try {
            global $servername, $username, $password, $dbname;
            
            // Create a PDO connection
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            
            // Set the PDO error mode to exception
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Process competences into JSON format
            $competencesArray = array_map('trim', explode(',', $competences));
            $competencesJson = json_encode([
                'langages' => implode(', ', $competencesArray),
                'softskills' => ''
            ], JSON_UNESCAPED_UNICODE);
            
            // Check if this user has already applied for this job
            $checkStmt = $pdo->prepare("SELECT id FROM entretiens WHERE id_offre = :id_offre AND id_user = :id_user LIMIT 1");
            $checkStmt->bindParam(':id_offre', $id_offre, PDO::PARAM_STR); // Changed to PARAM_STR since id_offre is VARCHAR
            $checkStmt->bindParam(':id_user', $userId, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                // User has already applied, update their application
                $entretienStmt = $pdo->prepare("UPDATE entretiens SET 
                    competences = :competences,
                    presentation = :presentation,
                    motivation = :motivation,
                    pourquoi_lui = :pourquoi_lui,
                    status = 'pending'
                    WHERE id_offre = :id_offre AND id_user = :id_user");
            } else {
                // New application
                $entretienStmt = $pdo->prepare("INSERT INTO entretiens (id_offre, id_user, competences, presentation, motivation, pourquoi_lui, status) 
                    VALUES (:id_offre, :id_user, :competences, :presentation, :motivation, :pourquoi_lui, 'pending')");
            }
            
            $entretienStmt->bindParam(':id_offre', $id_offre, PDO::PARAM_STR); // Changed to PARAM_STR
            $entretienStmt->bindParam(':id_user', $userId, PDO::PARAM_INT);
            $entretienStmt->bindParam(':competences', $competencesJson, PDO::PARAM_STR);
            $entretienStmt->bindParam(':presentation', $presentation, PDO::PARAM_STR);
            $entretienStmt->bindParam(':motivation', $motivation, PDO::PARAM_STR);
            $entretienStmt->bindParam(':pourquoi_lui', $pourquoi_lui, PDO::PARAM_STR);
            $entretienStmt->execute();
            
            // Redirect to success page
            header('Location: ../view/offre-detail.php?id=' . $id_offre . '&success=1');
            exit;
            
        } catch (PDOException $e) {
            // Log the error (in a production environment)
            error_log("Database error in submitApplication: " . $e->getMessage());
            
            // Redirect to error page
            header('Location: ../view/offre-detail.php?id=' . $id_offre . '&error=db');
            exit;
        }
    } else {
        // Validation errors, redirect back to the form with error
        $errorStr = implode('|', $errors);
        header('Location: ../view/offre-detail.php?id=' . $id_offre . '&error=' . urlencode($errorStr));
        exit;
    }
} else {
    // Not a POST request, redirect to job offers page
    header('Location: ../view/offre.php');
    exit;
}
?>