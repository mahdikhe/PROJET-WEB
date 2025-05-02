<?php
// includes/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

function sendEmail($to, $subject, $body) {
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'abderrahmen.mehdi@esprit.tn'; // Replace with your email
        $mail->Password = 'atxa kvzt eiei aarq'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('abderrahmen.mehdi@esprit.tn', 'Platforme'); // Replace with your email
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        error_log("[SUCCESS] Email envoyé avec succès à : " . $to);
        return true;

    } catch (Exception $e) {
        error_log("[ERROR] Erreur d'envoi d'email à {$to}: " . $e->getMessage());
        throw new Exception("Erreur d'envoi de l'email. Détails: " . $e->getMessage());
    }
}