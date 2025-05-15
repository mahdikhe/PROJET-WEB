<?php
require_once(__DIR__ . '/../../config/Database.php'); // Fix the path

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Verify no output has been sent
if (headers_sent($file, $line)) {
    error_log("Headers already sent in $file:$line");
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to vote']);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate parameters
$taskId = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
$voteType = filter_input(INPUT_POST, 'vote_type', FILTER_SANITIZE_STRING);
$userId = $_SESSION['user_id'];

if (!$taskId || !$voteType || !in_array($voteType, ['upvote', 'downvote'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $conn->beginTransaction();
    
    // Remove existing vote
    $stmt = $conn->prepare("DELETE FROM task_votes WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$taskId, $userId]);
    
    // Add new vote
    $stmt = $conn->prepare("INSERT INTO task_votes (task_id, user_id, vote_type) VALUES (?, ?, ?)");
    $stmt->execute([$taskId, $userId, $voteType]);
    
    // Get updated counts
    $stmt = $conn->prepare("SELECT vote_type, COUNT(*) as count FROM task_votes WHERE task_id = ? GROUP BY vote_type");
    $stmt->execute([$taskId]);
    $votes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'upvotes' => (int)($votes['upvote'] ?? 0),
        'downvotes' => (int)($votes['downvote'] ?? 0),
        'userVote' => $voteType
    ]);
    
} catch (Exception $e) {
    if ($conn) {
        $conn->rollBack();
    }
    error_log("Vote error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}