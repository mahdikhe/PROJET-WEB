<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store the token in session to redirect back after login
    if (isset($_GET['token'])) {
        $_SESSION['pending_invitation_token'] = $_GET['token'];
    }
    header('Location: login.php');
    exit;
}

// Get token from URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (!$token) {
    die('<div style="padding: 20px; background: #ffebee; color: #c62828; border-radius: 5px;">Lien d\'invitation invalide.</div>');
}

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get invitation details
    $stmt = $pdo->prepare("
        SELECT i.*, t.title as task_title 
        FROM invitations i 
        JOIN tasks t ON i.task_id = t.id 
        WHERE i.token = ? AND i.status = 'pending' AND i.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invitation) {
        die('<div style="padding: 20px; background: #ffebee; color: #c62828; border-radius: 5px;">Lien expiré ou invalide.</div>');
    }

    // Check if user already has access to this task
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_assignments WHERE task_id = ? AND user_id = ?");
    $stmt->execute([$invitation['task_id'], $_SESSION['user_id']]);
    $hasAccess = $stmt->fetchColumn() > 0;

    if (!$hasAccess) {
        // Add user to task assignments
        $stmt = $pdo->prepare("INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)");
        $stmt->execute([$invitation['task_id'], $_SESSION['user_id']]);
    }

    // Update invitation status
    $stmt = $pdo->prepare("UPDATE invitations SET status = 'accepted' WHERE token = ?");
    $stmt->execute([$token]);

    // Redirect to the task page with success message
    $_SESSION['success_message'] = "Vous avez rejoint la tâche avec succès!";
    header('Location: tasks.php');
    exit;

} catch (Exception $e) {
    die('<div style="padding: 20px; background: #ffebee; color: #c62828; border-radius: 5px;">Une erreur s\'est produite : ' . $e->getMessage() . '</div>');
}