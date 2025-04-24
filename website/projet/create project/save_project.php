<?php
require_once('db.php');



session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = bin2hex(random_bytes(32));
    if (!isset($_SESSION['form_token']) || $_SESSION['form_token'] !== ($_POST['form_token'] ?? '')) {
        $_SESSION['form_token'] = $token;
        // Traitement normal
    } else {
        // Formulaire déjà soumis
        header("Location: project_success.php?id=" . $lastInsertId);
        exit();
    }
}
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
        // Check if this is a duplicate submission
        if (isset($_SESSION['last_project_submission']) && 
            time() - $_SESSION['last_project_submission'] < 5) {
            // This is likely a duplicate submission, redirect to the projects page
            header("Location: projects.php");
            exit();
        }
        
        // Record this submission time
        $_SESSION['last_project_submission'] = time();
        
        // Create database connection using PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Validate required fields
        $required = ['projectName', 'projectDescription', 'startDate', 'endDate', 
                    'projectLocation', 'projectCategory', 'projectTags', 'teamSize', 
                    'projectVisibility', 'terms', 'isPaid'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field]) && $field !== 'isPaid') { // isPaid can be 0 (which is empty in PHP)
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Validate ticket price if project is paid
        $isPaid = (int)$_POST['isPaid'];
        $ticketPrice = 0.00;
        
        if ($isPaid === 1) {
            if (empty($_POST['ticketPrice'])) {
                throw new Exception("Ticket price is required for paid projects");
            }
            $ticketPrice = (float)$_POST['ticketPrice'];
            if ($ticketPrice <= 0) {
                throw new Exception("Ticket price must be greater than 0");
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
        $projectVisibility = $_POST['projectVisibility'];
        
        // Handle image upload
        $imageDestination = handleImageUpload($_FILES['projectImage'] ?? null, $uploadDir);
        
        // Insert into database with new fields
        $sql = "INSERT INTO projects (projectName, projectDescription, startDate, endDate, 
                projectLocation, projectCategory, projectTags, teamSize, projectImage, 
                is_paid, ticket_price, projectVisibility, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

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
            $imageDestination,
            $isPaid,
            $ticketPrice,
            $projectVisibility
        ]);

        if ($success) {
            $lastInsertId = $conn->lastInsertId();
            
            // Always return JSON response for consistency
            sendJsonResponse(true, "Project created successfully", [
                'id' => $lastInsertId,
                'name' => $projectName
            ]);
        } else {
            throw new Exception("Failed to insert project into database");
        }
    }
} catch (Exception $e) {
    // Always return JSON response for consistency
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