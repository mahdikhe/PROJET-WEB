<?php
require_once(__DIR__ . '/../../config/Database.php');

header('Content-Type: application/json');

$response = ['success' => false, 'collaborators' => []];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $taskId = $_POST['task_id'] ?? null;
    if (!$taskId) {
        $response['message'] = 'Task ID is required';
        echo json_encode($response);
        exit;
    }

    // Récupérer les collaborateurs acceptés
    $stmt = $conn->prepare("
        SELECT u.email 
        FROM task_collaborators tc
        JOIN users u ON tc.user_id = u.id
        WHERE tc.task_id = ? AND tc.status = 'accepted'
    ");
    $stmt->execute([$taskId]);
    $collaborators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['collaborators'] = $collaborators;
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>