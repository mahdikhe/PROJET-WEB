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

// Function to geocode location using Nominatim (OpenStreetMap)
function geocodeLocation($address) {
    $address = urlencode($address);
    $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "CityPulse-App"); // Required by Nominatim usage policy
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (!empty($data)) {
        return [
            'latitude' => $data[0]['lat'],
            'longitude' => $data[0]['lon']
        ];
    }
    return null;
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

    // Geocode the project location
    $coordinates = geocodeLocation($_POST['projectLocation']);
    $latitude = $coordinates ? $coordinates['latitude'] : null;
    $longitude = $coordinates ? $coordinates['longitude'] : null;

    // Initialize database connection
    $db = Database::getInstance()->getConnection();
    
    // Insert project into database with contributor_count defaulting to 0
    // Added latitude and longitude fields to the query
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
        created_at,
        contributor_count,
        latitude,
        longitude
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0, ?, ?)";

    $stmt = $db->prepare($sql);
    $success = $stmt->execute([
        htmlspecialchars($_POST['projectName']),
        htmlspecialchars($_POST['projectDescription']),
        $_POST['startDate'],
        $_POST['endDate'],
        htmlspecialchars($_POST['projectLocation']),
        $_POST['projectCategory'],
        htmlspecialchars($_POST['projectTags']),
        (float)$_POST['projectBudget'],
        (float)$_POST['fundingGoal'],
        $_POST['teamSize'],
        $imageDestination,
        (int)$_POST['isPaid'],
        (float)($_POST['isPaid'] ? $_POST['ticketPrice'] : 0),
        $_POST['projectVisibility'],
        $latitude,
        $longitude
    ]);

    if (!$success) {
        throw new Exception('Failed to save project to database');
    }

    // After successful project creation, get the last insert ID
    $lastInsertId = $db->lastInsertId();
    
    // Get the newly created project with contributor count (will be 0)
    $projectSql = "SELECT p.*, 
                  (SELECT COUNT(*) FROM contributors WHERE project_id = p.id) AS contributor_count
                  FROM projects p
                  WHERE p.id = ?";
    $projectStmt = $db->prepare($projectSql);
    $projectStmt->execute([$lastInsertId]);
    $project = $projectStmt->fetch(PDO::FETCH_ASSOC);
    
    // Send success response with project data including contributor count
    sendJsonResponse(true, 'Project created successfully', [
        'redirect' => "project_success.php?id=" . $lastInsertId,
        'project' => $project
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