<?php
// Include database configuration
require_once '../model/config.php';
// Include email notification utility
require_once '../model/EmailNotification.php';

// Check if the request includes an application ID
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get application details including user and offer details
        $stmt = $pdo->prepare("SELECT e.*, u.nom, u.prenom, u.email, o.titre, o.entreprise, o.emplacement, o.type
                              FROM entretiens e
                              JOIN users u ON e.id_user = u.id
                              JOIN offres o ON e.id_offre = o.id
                              WHERE e.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application) {
            // Return error if application not found
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Candidature non trouvée']);
            exit;
        }
        
        // Update the status to 'accepted'
        $updateStmt = $pdo->prepare("UPDATE entretiens SET status = 'accepted' WHERE id = :id");
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();
        
        // Send acceptance email notification
        $emailSender = new EmailNotification();
        $recipientEmail = $application['email'];
        $recipientName = $application['prenom'] . ' ' . $application['nom'];
        $jobDetails = [
            'titre' => $application['titre'],
            'entreprise' => $application['entreprise'],
            'emplacement' => $application['emplacement'],
            'type' => $application['type'],
        ];
        
        $emailSent = $emailSender->sendAcceptanceEmail($recipientEmail, $recipientName, $jobDetails);
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Candidature acceptée avec succès',
            'emailSent' => $emailSent
        ]);
        exit;
        
    } catch (PDOException $e) {
        // Return error response
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
        exit;
    }
} else {
    // Return error response for missing ID
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de candidature manquant']);
    exit;
}
?>