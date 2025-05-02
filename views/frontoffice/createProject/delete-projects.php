<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session before any output
session_start();

require_once(__DIR__ . '/../../../config/Database.php');

// Ensure no whitespace or output before JSON response
ob_clean();
header('Content-Type: application/json');

// Get JSON data from request
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Check if projectIds is set and not empty
if (!isset($data['projectIds']) || empty($data['projectIds'])) {
    echo json_encode(['success' => false, 'message' => 'No projects selected']);
    exit;
}

$projectIds = array_map('intval', $data['projectIds']);

try {
    // Initialize database connection
    $db = Database::getInstance()->getConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';
    
    // Delete supporters first (foreign key relationship)
    $deleteSupportersQuery = "DELETE FROM project_supporters WHERE project_id IN ($placeholders)";
    $stmt = $db->prepare($deleteSupportersQuery);
    $stmt->execute($projectIds);
    
    // Delete the projects
    $deleteProjectsQuery = "DELETE FROM projects WHERE id IN ($placeholders)";
    $stmt = $db->prepare($deleteProjectsQuery);
    $stmt->execute($projectIds);
    
    // Commit transaction
    $db->commit();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Projects deleted successfully'
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log("Database error during project deletion: " . $e->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while deleting projects'
    ]);
} catch (Exception $e) {
    // Handle any other exceptions
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log("Error during project deletion: " . $e->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting projects'
    ]);
}