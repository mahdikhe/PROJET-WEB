<?php
require_once(__DIR__ . '/../../../config/Database.php');
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get the project ID from POST data
$projectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$projectId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid project ID'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // First, check if the project exists
    $checkProject = $db->prepare("SELECT id FROM projects WHERE id = ?");
    $checkProject->execute([$projectId]);
    
    if (!$checkProject->fetch()) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Project not found'
        ]);
        exit;
    }
    
    // Check if user already supports this project
    $checkSupport = $db->prepare("SELECT id FROM project_supporters WHERE project_id = ? AND user_id = ?");
    $checkSupport->execute([$projectId, $userId]);
    
    $db->beginTransaction();
    
    if ($checkSupport->fetch()) {
        // User already supports - remove support
        $stmt = $db->prepare("DELETE FROM project_supporters WHERE project_id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        $is_supported = false;
    } else {
        // User doesn't support yet - add support
        $stmt = $db->prepare("INSERT INTO project_supporters (project_id, user_id, supported_at) VALUES (?, ?, NOW())");
        $stmt->execute([$projectId, $userId]);
        $is_supported = true;
    }
    
    // Get updated supporter count
    $countStmt = $db->prepare("SELECT COUNT(*) as count FROM project_supporters WHERE project_id = ?");
    $countStmt->execute([$projectId]);
    $supportersCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $db->commit();
    
    echo json_encode([
        'status' => 'success',
        'is_supported' => $is_supported,
        'supporters_count' => $supportersCount,
        'message' => $is_supported ? 'Project supported successfully' : 'Support removed successfully'
    ]);
    
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}