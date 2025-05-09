<?php
// Include database connection
require_once 'config.php';

// Function to generate a unique ID
function generateUniqueID($conn) {
    // Generate a random ID starting with a "#" followed by 4 digits
    $randomID = "#" . rand(1000, 9999);
    
    // Check if this ID already exists in the database
    $stmt = $conn->prepare("SELECT id FROM offres WHERE id = ?");
    $stmt->execute([$randomID]);
    
    // If ID exists, retry until we get a unique one
    while ($stmt->rowCount() > 0) {
        $randomID = "#" . rand(1000, 9999);
        $stmt->execute([$randomID]);
    }
    
    return $randomID;
}

// Function to validate form data
function validateFormData($data) {
    $errors = [];
    
    // Validate titre (Title) - letters only
    if (empty($data['titre'])) {
        $errors[] = "Le titre est requis";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s\-]+$/u", $data['titre'])) {
        $errors[] = "Le titre ne doit contenir que des lettres et des espaces";
    }
    
    // Validate entreprise (Company) - letters only
    if (empty($data['entreprise'])) {
        $errors[] = "Le nom de l'entreprise est requis";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s\-&.]+$/u", $data['entreprise'])) {
        $errors[] = "Le nom de l'entreprise ne doit contenir que des lettres, espaces et certains caractères spéciaux";
    }
    
    // Validate emplacement (Location)
    if (empty($data['emplacement'])) {
        $errors[] = "L'emplacement est requis";
    }
    
    // Validate description - between 5 and 255 words
    if (empty($data['description'])) {
        $errors[] = "La description est requise";
    } else {
        $wordCount = str_word_count($data['description']);
        if ($wordCount < 5) {
            $errors[] = "La description doit contenir au moins 5 mots";
        } elseif ($wordCount > 255) {
            $errors[] = "La description ne doit pas dépasser 255 mots";
        }
    }
    
    // Validate date
    if (empty($data['date'])) {
        $errors[] = "La date est requise";
    } elseif (!strtotime($data['date'])) {
        $errors[] = "Format de date invalide";
    }
    
    // Validate type
    if (empty($data['type'])) {
        $errors[] = "Le type est requis";
    } elseif (!in_array($data['type'], ['en ligne', 'hybride', 'sur place'])) {
        $errors[] = "Le type doit être 'en ligne', 'hybride' ou 'sur place'";
    }
    
    return $errors;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Server-side sanitize and validate input data
    $titre = trim(htmlspecialchars($_POST["titre"]));
    $entreprise = trim(htmlspecialchars($_POST["entreprise"]));
    $emplacement = trim(htmlspecialchars($_POST["emplacement"]));
    $description = trim(htmlspecialchars($_POST["description"]));
    $date = trim($_POST["date"]);
    $type = isset($_POST["type"]) ? trim($_POST["type"]) : "";
    
    // Validate the form data
    $formData = [
        'titre' => $titre,
        'entreprise' => $entreprise,
        'emplacement' => $emplacement,
        'description' => $description,
        'date' => $date,
        'type' => $type
    ];
    
    $errors = validateFormData($formData);
    
    if (empty($errors)) {
        try {
            // Generate a unique ID for the new offer
            $id = generateUniqueID($conn);
            
            // Prepare and execute SQL statement using PDO
            $stmt = $conn->prepare("INSERT INTO offres (id, titre, entreprise, emplacement, description, date, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $titre, $entreprise, $emplacement, $description, $date, $type]);
            
            // Redirect back to the offers page with success message
            header("Location: ../view/offres.php?success=1");
            exit();
        } catch(PDOException $e) {
            // Redirect back with error
            header("Location: ../view/offres.php?error=db&message=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // Redirect back with validation errors
        $errorString = implode(", ", $errors);
        header("Location: ../view/offres.php?error=validation&message=" . urlencode($errorString));
        exit();
    }
}
?>