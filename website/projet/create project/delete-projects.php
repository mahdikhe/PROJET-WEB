<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session before any output
session_start();

// Log session information
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));
error_log("Cookie data: " . print_r($_COOKIE, true));

// Include database connection
include('db.php');

// Log the request data
$rawData = file_get_contents('php://input');
error_log("Received data: " . $rawData);

// Get JSON data from request
$data = json_decode($rawData, true);

// Log the decoded data
error_log("Decoded data: " . print_r($data, true));

// Check if projectIds is set
if (!isset($data['projectIds']) || empty($data['projectIds'])) {
    echo json_encode(['success' => false, 'message' => 'No projects selected']);
    exit;
}

$projectIds = $data['projectIds'];
$userId = $_SESSION['user_id'] ?? 0; // Use fallback value of 0 if not set
$success = true;
$message = '';

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Since there's no user_id field in the projects table,
    // we'll skip the permission check for now
    // In a real application, you would want to implement proper permission checks
    
    // First, delete related records in project_supporters table
    $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';
    $deleteSupportersQuery = "DELETE FROM project_supporters WHERE project_id IN ($placeholders)";
    $deleteSupportersStmt = $conn->prepare($deleteSupportersQuery);
    
    // Log the query and parameters
    error_log("Query (supporters): " . $deleteSupportersQuery);
    error_log("Parameters (supporters): " . print_r($projectIds, true));
    
    $deleteSupportersStmt->execute($projectIds);
    
    // Check if there are any other related tables with foreign key constraints
    // For example, if there's a project_tasks table:
    try {
        $deleteTasksQuery = "DELETE FROM project_tasks WHERE project_id IN ($placeholders)";
        $deleteTasksStmt = $conn->prepare($deleteTasksQuery);
        error_log("Query (tasks): " . $deleteTasksQuery);
        $deleteTasksStmt->execute($projectIds);
    } catch (PDOException $e) {
        // If the table doesn't exist, just log the error and continue
        error_log("Note: project_tasks table might not exist: " . $e->getMessage());
    }
    
    // Now delete the projects
    $deleteProjectsQuery = "DELETE FROM projects WHERE id IN ($placeholders)";
    $deleteProjectsStmt = $conn->prepare($deleteProjectsQuery);
    
    // Log the query and parameters
    error_log("Query (projects): " . $deleteProjectsQuery);
    error_log("Parameters (projects): " . print_r($projectIds, true));
    
    $deleteProjectsStmt->execute($projectIds);
    
    // Commit transaction
    $conn->commit();
    
    $message = 'Projects deleted successfully';
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    $success = false;
    $message = 'Database error: ' . $e->getMessage();
    error_log("Database error: " . $e->getMessage());
}

// Return JSON response
$response = [
    'success' => $success,
    'message' => $message
];
error_log("Sending response: " . json_encode($response));
echo json_encode($response); 