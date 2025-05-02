<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once(__DIR__ . '/../../config/Database.php');

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get database connection
try {
    $database = Database::getInstance();
    $pdo = $database->getConnection();
    // Set the database to use
    $pdo->exec("USE project_creation");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    header("Location: contribute.html?error=database_connection_failed");
    exit;
}

// Get form fields
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$city = trim($_POST['city'] ?? '');
$phone = trim($_POST['phone-number'] ?? '');
$age = $_POST['age-group'] ?? '';
$projectId = filter_var($_POST['project_id'] ?? null, FILTER_VALIDATE_INT);
$availability = $_POST['location-availability'] ?? '';
$type = $_POST['contributionType'] ?? '';
$message = trim($_POST['message'] ?? '');

// Validate required fields
$errors = [];
if (empty($firstName)) $errors[] = "First name is required";
if (empty($lastName)) $errors[] = "Last name is required";
if (empty($email)) $errors[] = "Email is required";
if (!isValidEmail($email)) $errors[] = "Invalid email format";
if (!$projectId) $errors[] = "Project ID is required";

if (!empty($errors)) {
    error_log("Validation errors: " . implode(", ", $errors));
    header("Location: contribute.html?error=" . urlencode(implode(", ", $errors)));
    exit;
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
    } else {
        error_log("File upload failed for: " . $_FILES['fileUpload']['name']);
    }
}

try {
    // Use prepared statement for insertion
    $sql = "INSERT INTO project_creation.contributors (
        first_name, last_name, email, city, phone, 
        age_group, preferred_project, location_availability, 
        contribution_type, message, file_path
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $firstName, $lastName, $email, $city, $phone,
        $age, $projectId, $availability, $type, $message, $filePath
    ]);
    
    if ($result) {
        error_log("Contribution saved successfully for: $firstName $lastName");
        header("Location: contribution-success.html");
        exit;
    } else {
        throw new Exception("Failed to save contribution");
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: contribute.html?error=database_error");
    exit;
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    header("Location: contribute.html?error=general_error");
    exit;
}
