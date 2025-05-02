<?php
require_once(__DIR__ . '/../../config/Database.php');
session_start();

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Si l'utilisateur n'est pas connecté, sauvegarder le token et rediriger vers la connexion
if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_invitation_token'] = $token;
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Vérifier si le token est valide
    $stmt = $conn->prepare("SELECT * FROM task_invitations WHERE token = ? AND status = 'pending'");
    $stmt->execute([$token]);
    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invitation) {
        $error = "Invitation invalide ou déjà utilisée";
    } else {
        // Mettre à jour le statut de l'invitation
        $stmt = $conn->prepare("UPDATE task_invitations SET status = 'accepted' WHERE token = ?");
        $stmt->execute([$token]);

        // Ajouter l'utilisateur comme collaborateur de la tâche
        $stmt = $conn->prepare("INSERT INTO task_collaborators (task_id, user_id) VALUES (?, ?)");
        $stmt->execute([$invitation['task_id'], $_SESSION['user_id']]);

        $success = "Vous avez accepté l'invitation avec succès !";
    }
} catch (PDOException $e) {
    $error = "Une erreur est survenue: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accepter l'invitation</title>
    <link rel="stylesheet" href="../../assets/css/style1.css">
</head>
<body>
    <div class="container" style="margin-top: 50px; text-align: center;">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
                <p>Vous allez être redirigé vers la tâche dans quelques secondes...</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'tasks.php';
                }, 3000);
            </script>
        <?php endif; ?>
    </div>
</body>
</html>