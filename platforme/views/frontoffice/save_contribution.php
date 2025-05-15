<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../../config/Database.php');

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

try {
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    $pdo->exec("USE project_creation");
    
    // Test connection
    $test = $pdo->query("SELECT 1");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection error: " . $e->getMessage());
}

// Get form fields
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$city = trim($_POST['city'] ?? '');
$phone = trim($_POST['phone-number'] ?? '');
$age = $_POST['age-group'] ?? '';
$projectId = (int)($_POST['project_id'] ?? 0);
$preferredProject = $_POST['preferred-projects'] ?? '';
$availability = $_POST['location-availability'] ?? '';
$type = $_POST['contributionType'] ?? '';
$message = trim($_POST['message'] ?? '');
$latitude = null;
$longitude = null;

// Validate required fields
$errors = [];
if (empty($firstName)) $errors[] = "First name is required";
if (empty($lastName)) $errors[] = "Last name is required";
if (empty($email)) $errors[] = "Email is required";
if (!isValidEmail($email)) $errors[] = "Invalid email format";
if ($projectId <= 0) $errors[] = "Valid Project ID is required";

if (!empty($errors)) {
    die(implode("<br>", $errors));
}

// Check if user already contributed to this project
$checkSql = "SELECT id FROM contributors WHERE email = ? AND project_id = ?";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$email, $projectId]);

if ($checkStmt->fetch()) {
    die("You have already contributed to this project. Each user can only contribute once per project.");
}

// Handle file upload
$filePath = "";
if (!empty($_FILES['fileUpload']['name'])) {
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = time() . "_" . basename($_FILES['fileUpload']['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $targetPath)) {
        $filePath = "uploads/" . $filename;
    }
}

try {
    // Insert contributor with project relationship
    $sql = "INSERT INTO contributors (
        first_name, last_name, email, city, phone, 
        age_group, preferred_project, location_availability, 
        contribution_type, message, file_path, latitude, longitude,
        project_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $firstName, $lastName, $email, $city, $phone,
        $age, $preferredProject, $availability, $type, 
        $message, $filePath, $latitude, $longitude,
        $projectId
    ]);
    
    if ($result) {
        // Update the contributor count for this project
        $updateSql = "UPDATE projects 
                     SET contributor_count = contributor_count + 1 
                     WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$projectId]);
        
        header("Location: contribution-success.php");
        exit;
    }
    
} catch (PDOException $e) {
    // Handle unique constraint violation specifically
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        die("You have already contributed to this project.");
    }
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}