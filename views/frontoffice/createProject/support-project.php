<?php
require_once(__DIR__ . '/../../../config/Database.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    // Validate user authentication
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        exit;
    }

    try {
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate project ID
        $projectId = filter_var($data['projectId'] ?? null, FILTER_VALIDATE_INT);
        if (!$projectId) {
            throw new Exception('Invalid project ID');
        }

        $userId = (int)$_SESSION['user_id'];

        // Initialize database connection
        $db = Database::getInstance()->getConnection();
        
        // Start transaction
        $db->beginTransaction();

        // Check if user already supports the project
        $stmt = $db->prepare("SELECT 1 FROM project_supporters WHERE project_id = ? AND supporter_id = ?");
        $stmt->execute([$projectId, $userId]);
        $exists = $stmt->fetch(PDO::FETCH_COLUMN);

        if ($exists) {
            // Remove support
            $stmt = $db->prepare("DELETE FROM project_supporters WHERE project_id = ? AND supporter_id = ?");
            $stmt->execute([$projectId, $userId]);
            $action = 'unsupported';
        } else {
            // Add support
            $stmt = $db->prepare("INSERT INTO project_supporters (project_id, supporter_id) VALUES (?, ?)");
            $stmt->execute([$projectId, $userId]);
            $action = 'supported';
        }

        // Get updated supporter count
        $stmt = $db->prepare("SELECT COUNT(*) FROM project_supporters WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $supportersCount = $stmt->fetch(PDO::FETCH_COLUMN);

        // Update supporters count in projects table
        $stmt = $db->prepare("UPDATE projects SET supporters_count = ? WHERE id = ?");
        $stmt->execute([$supportersCount, $projectId]);

        // Commit transaction
        $db->commit();

        echo json_encode([
            'success' => true,
            'action' => $action,
            'supportersCount' => $supportersCount,
            'message' => "Project successfully " . $action
        ]);

    } catch (Exception $e) {
        // Rollback transaction if active
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        
        error_log("Error in support-project.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}