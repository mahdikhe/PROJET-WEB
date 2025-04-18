<?php
include('db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    // Validate user authentication
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
        exit;
    }

    // Validate and sanitize input
    $projectId = filter_input(INPUT_POST, 'projectId', FILTER_VALIDATE_INT);
    $userId = intval($_SESSION['user_id']);

    if (!$projectId || $projectId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid project ID']);
        exit;
    }

    try {
        // Debugging: Log the projectId and userId
        error_log("Processing support for projectId: $projectId, userId: $userId");

        // Check if the user already supports the project
        $stmt = $conn->prepare("SELECT 1 FROM project_supporters WHERE project_id = ? AND user_id = ?");
        $stmt->bindParam(1, $projectId, PDO::PARAM_INT);
        $stmt->bindParam(2, $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // If already supported, remove the support
            $stmt = $conn->prepare("DELETE FROM project_supporters WHERE project_id = ? AND user_id = ?");
            $stmt->bindParam(1, $projectId, PDO::PARAM_INT);
            $stmt->bindParam(2, $userId, PDO::PARAM_INT);
            $stmt->execute();
            $action = 'unsupported';
        } else {
            // If not supported, add the support
            $stmt = $conn->prepare("INSERT INTO project_supporters (project_id, user_id) VALUES (?, ?)");
            $stmt->bindParam(1, $projectId, PDO::PARAM_INT);
            $stmt->bindParam(2, $userId, PDO::PARAM_INT);
            $stmt->execute();
            $action = 'supported';
        }

        // Update the supporters count in the projects table
        $stmt = $conn->prepare("
            UPDATE projects 
            SET supporters_count = (SELECT COUNT(*) FROM project_supporters WHERE project_id = ?)
            WHERE id = ?
        ");
        $stmt->bindParam(1, $projectId, PDO::PARAM_INT);
        $stmt->bindParam(2, $projectId, PDO::PARAM_INT);
        $stmt->execute();

        // Get the updated supporters count
        $supportersCount = getSupportersCount($conn, $projectId);

        // Return the response
        echo json_encode([
            'status' => 'success',
            'action' => $action,
            'is_supported' => ($action === 'supported'),
            'supporters_count' => $supportersCount
        ]);
    } catch (Exception $e) {
        // Debugging: Log the error message
        error_log("Error in support-project.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Get the total number of supporters for a project.
 *
 * @param PDO $conn
 * @param int $projectId
 * @return int
 */
function getSupportersCount($conn, $projectId) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM project_supporters WHERE project_id = ?");
    $stmt->bindParam(1, $projectId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ?? 0;
}