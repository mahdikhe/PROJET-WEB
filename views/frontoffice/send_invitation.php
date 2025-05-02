<?php
require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/mailer.php');

header('Content-Type: application/json');

try {
    // Validate input data
    if (!isset($_POST['task_id']) || !isset($_POST['email'])) {
        throw new Exception('Données manquantes : task_id et email sont requis');
    }

    $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($taskId === false) {
        throw new Exception('ID de tâche invalide');
    }

    if ($email === false) {
        throw new Exception('Adresse email invalide');
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check if task exists
    $stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    if (!$stmt->fetch()) {
        throw new Exception('La tâche spécifiée n\'existe pas');
    }

    // Check for duplicate invitation
    $stmt = $conn->prepare("SELECT id FROM task_invitations WHERE task_id = ? AND invitee_email = ? AND status = 'pending'");
    $stmt->execute([$taskId, $email]);
    if ($stmt->fetch()) {
        throw new Exception('Une invitation est déjà en attente pour cet email');
    }

    // Generate invitation token
    $token = bin2hex(random_bytes(32));

    // Start transaction
    $conn->beginTransaction();

    try {
        // Save invitation without inviter_id
        $stmt = $conn->prepare("INSERT INTO task_invitations (task_id, invitee_email, message, token, status) 
                               VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$taskId, $email, $message, $token]);

        // Create invitation link
        $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "/platforme/views/frontoffice/accept_invitation.php?token=" . $token;
        
        // Prepare email
        $subject = "Invitation à collaborer sur une tâche";
        $emailBody = "<html><body>";
        $emailBody .= "<h2>Invitation à collaborer</h2>";
        $emailBody .= "<p>Vous avez reçu une invitation à collaborer sur une tâche.</p>";
        if (!empty($message)) {
            $emailBody .= "<p><strong>Message :</strong> " . htmlspecialchars($message) . "</p>";
        }
        $emailBody .= "<p><a href='" . $inviteLink . "'>Cliquez ici pour accepter l'invitation</a></p>";
        $emailBody .= "</body></html>";

        // Send email
        if (!sendEmail($email, $subject, $emailBody)) {
            throw new Exception('Erreur lors de l\'envoi de l\'email');
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Invitation envoyée avec succès'
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue : ' . $e->getMessage()
    ]);
}