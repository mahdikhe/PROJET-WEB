<?php
require_once('db.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Create database connection using PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Validate required fields
        $required = ['projectName', 'projectDescription', 'startDate', 'endDate', 
                    'projectLocation', 'projectCategory', 'projectTags', 'teamSize', 
                    'projectVisibility', 'terms'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Sanitize input data
        $projectName = htmlspecialchars($_POST['projectName']);
        $projectDescription = htmlspecialchars($_POST['projectDescription']);
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $projectLocation = htmlspecialchars($_POST['projectLocation']);
        $projectCategory = $_POST['projectCategory'];
        $projectTags = htmlspecialchars($_POST['projectTags']);
        $teamSize = $_POST['teamSize'];
        
        // Handle image upload
        $imageDestination = handleImageUpload($_FILES['projectImage'] ?? null, $uploadDir);
        
        // Insert into database
       $sql = "INSERT INTO projects (projectName, projectDescription, startDate, endDate, 
                projectLocation, projectCategory, projectTags, teamSize, projectImage, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            $projectName,
            $projectDescription,
            $startDate,
            $endDate,
            $projectLocation,
            $projectCategory,
            $projectTags,
            $teamSize,
            $imageDestination
        ]);

        if ($success) {
            $lastInsertId = $conn->lastInsertId();
            
            // Redirect to success page instead of sending JSON
            header("Location: project_success.php?id=" . $lastInsertId);
            exit();
        } else {
            throw new Exception("Failed to insert project into database");
        }
    }
} catch (Exception $e) {
    sendJsonResponse(false, $e->getMessage());
}

function handleImageUpload($file, $uploadDir) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Image upload is required");
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mime, $allowedTypes)) {
        throw new Exception("Only JPG, PNG, and GIF images are allowed");
    }

    if ($file['size'] > $maxSize) {
        throw new Exception("Image file is too large (max 5MB)");
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $imageName = uniqid('project_', true) . '.' . $ext;
    $imageDestination = 'uploads/' . $imageName;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $imageName)) {
        throw new Exception("Failed to move uploaded image");
    }

    return $imageDestination;
}

function sendJsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data) {
        $response['project'] = $data;
    }
    
    echo json_encode($response);
    exit();
}
?>