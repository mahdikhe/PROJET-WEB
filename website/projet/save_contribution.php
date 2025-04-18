<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion à la base
$host = "localhost";
$dbname = "projet_contribution";  // Changed to the contributors database
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// Récupérer les champs du formulaire
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$city = $_POST['city'] ?? '';
$phone = $_POST['phone-number'] ?? '';
$age = $_POST['age-group'] ?? '';
$projectId = $_POST['project_id'] ?? null; // Get the project ID from the form
$availability = $_POST['location-availability'] ?? '';
$type = $_POST['contributionType'] ?? '';
$message = $_POST['message'] ?? '';

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($projectId)) {
    // Redirect with error message
    header("Location: contribute.html?error=missing_fields");
    exit;
}

// Gérer le fichier
$filePath = "";
if (!empty($_FILES['fileUpload']['name'])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filename = time() . "_" . basename($_FILES['fileUpload']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $targetPath)) {
        $filePath = $targetPath;
    }
}

// Insertion SQL
$sql = "INSERT INTO contributors (first_name, last_name, email, city, phone, age_group, preferred_project, location_availability, contribution_type, message, file_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$firstName, $lastName, $email, $city, $phone, $age, $projectId, $availability, $type, $message, $filePath]);
    
    // Log success for debugging
    error_log("Contribution saved successfully for project ID: " . $projectId);
    
    // Redirection
    header("Location: contribution-success.html");
    exit;
} catch (PDOException $e) {
    // Log error
    error_log("Error saving contribution: " . $e->getMessage());
    header("Location: contribute.html?error=database_error");
    exit;
}
?>
