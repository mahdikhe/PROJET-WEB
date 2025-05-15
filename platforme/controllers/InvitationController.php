<?php
require_once(__DIR__ . '/../config/Database.php');

// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get database connection
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Get form data
        $taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : null;
        $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Validate inputs
        if (!$taskId || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid input data');
        }
        
        // Check if task exists
        $taskStmt = $conn->prepare("SELECT id FROM tasks WHERE id = ?");
        $taskStmt->execute([$taskId]);
        if (!$taskStmt->fetch()) {
            throw new Exception('Task not found');
        }
        
        // Check if invitation already exists
        $checkStmt = $conn->prepare("SELECT id FROM invitations WHERE task_id = ? AND email = ? AND status = 'pending'");
        $checkStmt->execute([$taskId, $email]);
        if ($checkStmt->fetch()) {
            throw new Exception('An invitation is already pending for this email');
        }
        
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Set expiration date (48 hours from now)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));
        
        // Insert invitation
        $stmt = $conn->prepare("INSERT INTO invitations (task_id, email, message, token, expires_at) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$taskId, $email, $message, $token, $expiresAt])) {
            // Send email (you'll need to implement your email sending logic here)
            // For now, we'll just return success
            $response['success'] = true;
            $response['message'] = 'Invitation sent successfully';
        } else {
            throw new Exception('Failed to create invitation');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Send response
echo json_encode($response);
