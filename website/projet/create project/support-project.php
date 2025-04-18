<?php
include('db.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();
    $projectId = intval($_POST['projectId']);
    $userId = $_SESSION['user_id'] ?? 0; // Make sure you have user authentication

    try {
        // Check if already supported
        $stmt = $conn->prepare("SELECT * FROM project_supporters WHERE project_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $projectId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Unsupport
            $stmt = $conn->prepare("DELETE FROM project_supporters WHERE project_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $projectId, $userId);
            $stmt->execute();
            $action = 'unsupported';
        } else {
            // Support
            $stmt = $conn->prepare("INSERT INTO project_supporters (project_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $projectId, $userId);
            $stmt->execute();
            $action = 'supported';
        }

        // Update supporters count
        $stmt = $conn->prepare("UPDATE projects SET 
            supporters_count = (SELECT COUNT(*) FROM project_supporters WHERE project_id = ?)
            WHERE id = ?");
        $stmt->bind_param("ii", $projectId, $projectId);
        $stmt->execute();

        // Check if current user supports this project
        $stmt = $conn->prepare("SELECT 1 FROM project_supporters WHERE project_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $projectId, $userId);
        $stmt->execute();
        $isSupported = $stmt->get_result()->num_rows > 0;

        echo json_encode([
            'status' => 'success', 
            'action' => $action,
            'is_supported' => $isSupported,
            'supporters_count' => getSupportersCount($conn, $projectId)
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

function getSupportersCount($conn, $projectId) {
    $stmt = $conn->prepare("SELECT supporters_count FROM projects WHERE id = ?");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['supporters_count'] ?? 0;
}