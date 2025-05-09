<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    require_once __DIR__ . '/../../config/Database.php';
} catch (Exception $e) {
    error_log('Failed to include Database.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database configuration error']);
    exit;
}

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$taskId = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

// Validate data
if (!$taskId || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

try {
    // Debug output
    error_log('Attempting to get database connection');
    
    $pdo = Database::getInstance()->getConnection();
    
    if (!$pdo) {
        throw new Exception('Failed to get database connection');    
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if task exists
    $stmt = $pdo->prepare("SELECT title FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        throw new Exception('Task not found');
    }

    // Generate unique token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

    // Check if invitations table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'invitations'");
    if ($stmt->rowCount() === 0) {
        // Create invitations table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS invitations (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            task_id INT(11) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT,
            token VARCHAR(255) NOT NULL,
            status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
        )");
    }

    // Insert invitation
    $stmt = $pdo->prepare("INSERT INTO invitations (task_id, email, message, token, expires_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$taskId, $email, $message, $token, $expiresAt]);

    // Prepare invitation URL
    $inviteUrl = "http://{$_SERVER['HTTP_HOST']}/platforme/accept_invitation.php?token=" . urlencode($token);

    // Build email content
    $emailContent = "
        <h2>Vous avez ete invite a rejoindre une tache!</h2>
        <p>Un ami vous a invite a participer a la tache: <strong>{$task['title']}</strong></p>
    ";

    if ($message) {
        $emailContent .= "<p>Message de votre ami:</p><blockquote>{$message}</blockquote>";
    }

    $emailContent .= "
        <p>Pour acceder a la tache, cliquez sur le lien ci-dessous:</p>
     <p><a href='http://{$_SERVER['HTTP_HOST']}/views/frontoffice/manage_task.php?task_id={$taskId}'>Accéder à la tâche</a></p>
        <p><small>Ce lien expirera dans 7 jours.</small></p>
    ";

    // Load PHPMailer
    require __DIR__ . '/../../../vendor/autoload.php'; // Absolute path from project root

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';         // Gmail SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'abderrahmen.mehdi@esprit.tn';   // Your Gmail address
        $mail->Password   = 'nvus prvh kndu zlpv';      // App password from Google
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom('abderrahmen.mehdi@esprit.tn', 'Task Manager App');
        $mail->addReplyTo('abderrahmen.mehdi@esprit.tn', 'Task Manager App');
        $mail->addAddress($email);  // Add recipient's email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Invitation à rejoindre une tâche';
        $mail->Body    = $emailContent;

        $mail->send();

        echo json_encode([
            'success' => true, 
            'message' => 'Invitation envoyée avec succès',
            'debug' => [
                'task_id' => $taskId,
                'email' => $email,
                'token' => $token
            ]
        ]);

    } catch (Exception $e) {
        error_log('Email error: ' . $mail->ErrorInfo);
        throw new Exception("Failed to send email: " . $mail->ErrorInfo);
    }

} catch (Exception $e) {
    error_log('Error in send_invitation.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while processing your request',
        'debug_message' => $e->getMessage()
    ]);
}

$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

// Set UTF-8 encoding
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

// Set email content
$mail->setFrom('abderrahmen.mehdi@esprit.tn', 'Task Manager');
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = '=?UTF-8?B?'.base64_encode('Invitation à rejoindre une tâche').'?=';
$mail->Body = $emailContent;