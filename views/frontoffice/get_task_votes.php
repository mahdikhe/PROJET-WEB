<?php
require_once(__DIR__ . '/../../config/Database.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$taskIds = json_decode($_POST['task_ids'] ?? '[]', true);

if (empty($taskIds)) {
    echo json_encode(['success' => false, 'message' => 'No task IDs provided']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get vote counts for all tasks
    $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
    $stmt = $conn->prepare("
        SELECT task_id, vote_type, COUNT(*) as count 
        FROM task_votes 
        WHERE task_id IN ($placeholders)
        GROUP BY task_id, vote_type
    ");
    $stmt->execute($taskIds);
    $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user's votes
    $stmt = $conn->prepare("
        SELECT task_id, vote_type 
        FROM task_votes 
        WHERE user_id = ? AND task_id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$_SESSION['user_id']], $taskIds));
    $userVotes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Format response
    $result = [];
    foreach ($taskIds as $taskId) {
        $upvotes = 0;
        $downvotes = 0;
        
        foreach ($votes as $vote) {
            if ($vote['task_id'] == $taskId) {
                if ($vote['vote_type'] == 'upvote') {
                    $upvotes = (int)$vote['count'];
                } else {
                    $downvotes = (int)$vote['count'];
                }
            }
        }
        
        $result[] = [
            'task_id' => $taskId,
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
            'user_vote' => $userVotes[$taskId] ?? null
        ];
    }

    echo json_encode(['success' => true, 'votes' => $result]);
    
} catch (Exception $e) {
    error_log("Error getting votes: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>