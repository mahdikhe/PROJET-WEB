<?php
// Early headers and content type setting
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once(__DIR__ . '/../../../config/Database.php');

session_start();

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Only POST method is allowed');
    }

    // Validate required fields
    $required = ['projectName', 'projectDescription', 'startDate', 'endDate', 
                'projectLocation', 'projectCategory', 'projectTags','projectBudget','fundingGoal', 'teamSize', 
                'projectVisibility', 'terms', 'isPaid'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field]) && $field !== 'isPaid') {
            throw new Exception("Required field '$field' is missing");
        }
    }

    // Handle image upload
    if (!isset($_FILES['projectImage']) || $_FILES['projectImage']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Project image is required');
    }

    $imageDestination = handleImageUpload($_FILES['projectImage'], $uploadDir);

    // Initialize database connection
    $db = Database::getInstance()->getConnection();
    
    // Insert project into database
    // First fix the SQL query - number of placeholders must match values
$sql = "INSERT INTO projects (
    projectName, 
    projectDescription, 
    startDate, 
    endDate, 
    projectLocation, 
    projectCategory, 
    projectTags,
    projectBudget,
    fundingGoal, 
    teamSize, 
    projectImage, 
    is_paid, 
    ticket_price, 
    projectVisibility, 
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $db->prepare($sql);
$success = $stmt->execute([
    htmlspecialchars($_POST['projectName']),
    htmlspecialchars($_POST['projectDescription']),
    $_POST['startDate'],
    $_POST['endDate'],
    htmlspecialchars($_POST['projectLocation']),
    $_POST['projectCategory'],
    htmlspecialchars($_POST['projectTags']),
    (float)$_POST['projectBudget'],    // Add budget
    (float)$_POST['fundingGoal'],      // Add funding goal
    $_POST['teamSize'],
    $imageDestination,
    (int)$_POST['isPaid'],
    (float)($_POST['isPaid'] ? $_POST['ticketPrice'] : 0),
    $_POST['projectVisibility']
]);

$stmt = $db->prepare($sql);
$success = $stmt->execute([
    htmlspecialchars($_POST['projectName']),
    htmlspecialchars($_POST['projectDescription']),
    $_POST['startDate'],
    $_POST['endDate'],
    htmlspecialchars($_POST['projectLocation']),
    $_POST['projectCategory'],
    htmlspecialchars($_POST['projectTags']),
    (float)$_POST['projectBudget'],    // Add budget
    (float)$_POST['fundingGoal'],      // Add funding goal
    $_POST['teamSize'],
    $imageDestination,
    (int)$_POST['isPaid'],
    (float)($_POST['isPaid'] ? $_POST['ticketPrice'] : 0),
    $_POST['projectVisibility']
]);

    if (!$success) {
        throw new Exception('Failed to save project to database');
    }

    // After successful project creation, get the last insert ID
    $lastInsertId = $db->lastInsertId();
    
    // Send success response with redirect URL
    sendJsonResponse(true, 'Project created successfully', [
        'redirect' => "project_success.php?id=" . $lastInsertId
    ]);

} catch (Exception $e) {
    sendJsonResponse(false, $e->getMessage());
}

function handleImageUpload($file, $uploadDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mime, $allowedTypes)) {
        throw new Exception("Only JPG, PNG, and GIF images are allowed");
    }

    if ($file['size'] > $maxSize) {
        throw new Exception("File size exceeds the limit of 5MB");
    }

    $filename = uniqid('project_', true) . '_' . basename($file['name']);
    $destination = 'uploads/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new Exception("Failed to upload image");
    }

    return $destination;
}