<?php
// Email notification utility for job application status updates

require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailNotification {
    private $mail;
    private $senderEmail = 'fatmakhelifi96207@gmail.com';
    private $senderName = 'CityPulse Recrutement';
    private $appPassword = 'evyt fudx zxkm rfbx';

    public function __construct() {
        // Initialize PHPMailer
        $this->mail = new PHPMailer(true);
        
        // Configure SMTP settings
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->senderEmail;
        $this->mail->Password = $this->appPassword;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        
        // Set default sender
        $this->mail->setFrom($this->senderEmail, $this->senderName);
        
        // Set email format to HTML
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
    }
    
    /**
     * Send application acceptance email
     * 
     * @param string $recipientEmail The recipient's email address
     * @param string $recipientName The recipient's name
     * @param array $jobDetails Array containing job details (title, company, etc.)
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendAcceptanceEmail($recipientEmail, $recipientName, $jobDetails) {
        try {
            // Set recipient
            $this->mail->clearAddresses();
            $this->mail->addAddress($recipientEmail, $recipientName);
            
            // Set email subject
            $this->mail->Subject = 'Félicitations ! Votre candidature a été acceptée';
            
            // Create email body
            $emailBody = $this->getAcceptanceEmailTemplate($recipientName, $jobDetails);
            $this->mail->Body = $emailBody;
            
            // Send email
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Log error
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send application rejection email
     * 
     * @param string $recipientEmail The recipient's email address
     * @param string $recipientName The recipient's name
     * @param array $jobDetails Array containing job details (title, company, etc.)
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendRejectionEmail($recipientEmail, $recipientName, $jobDetails) {
        try {
            // Set recipient
            $this->mail->clearAddresses();
            $this->mail->addAddress($recipientEmail, $recipientName);
            
            // Set email subject
            $this->mail->Subject = 'Information concernant votre candidature';
            
            // Create email body
            $emailBody = $this->getRejectionEmailTemplate($recipientName, $jobDetails);
            $this->mail->Body = $emailBody;
            
            // Send email
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Log error
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Get HTML template for acceptance emails
     */
    private function getAcceptanceEmailTemplate($recipientName, $jobDetails) {
        $jobTitle = $jobDetails['titre'];
        $companyName = $jobDetails['entreprise'];
        $location = $jobDetails['emplacement'];
        $jobType = $jobDetails['type'];
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #6944ff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #718096;
        }
        .button {
            display: inline-block;
            background-color: #6944ff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .highlight {
            font-weight: bold;
            color: #6944ff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Félicitations !</h1>
    </div>
    <div class="content">
        <p>Bonjour <span class="highlight">$recipientName</span>,</p>
        
        <p>Nous sommes ravis de vous informer que votre candidature pour le poste de <span class="highlight">$jobTitle</span> chez <span class="highlight">$companyName</span> a été acceptée !</p>
        
        <p>Détails du poste :</p>
        <ul>
            <li><strong>Poste :</strong> $jobTitle</li>
            <li><strong>Entreprise :</strong> $companyName</li>
            <li><strong>Localisation :</strong> $location</li>
            <li><strong>Type :</strong> $jobType</li>
        </ul>
        
        <p>Un recruteur vous contactera très prochainement pour discuter des prochaines étapes du processus de recrutement. En attendant, n'hésitez pas à nous contacter si vous avez des questions.</p>
        
        <p>Encore félicitations pour cette réussite !</p>
        
        <p>Cordialement,<br>
        L'équipe de recrutement de $companyName</p>
        
        <a href="http://localhost/projet_youssef/front_office/view/mes-candidatures.php" class="button">Voir mes candidatures</a>
    </div>
    <div class="footer">
        <p>CityPulse © 2025 | Ceci est un email automatique, merci de ne pas y répondre directement</p>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get HTML template for rejection emails
     */
    private function getRejectionEmailTemplate($recipientName, $jobDetails) {
        $jobTitle = $jobDetails['titre'];
        $companyName = $jobDetails['entreprise'];
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background-color: #4a5568;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #718096;
        }
        .button {
            display: inline-block;
            background-color: #4a5568;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .highlight {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Réponse à votre candidature</h1>
    </div>
    <div class="content">
        <p>Bonjour <span class="highlight">$recipientName</span>,</p>
        
        <p>Nous vous remercions pour l'intérêt que vous avez porté à notre entreprise et pour votre candidature au poste de <span class="highlight">$jobTitle</span> chez <span class="highlight">$companyName</span>.</p>
        
        <p>Après examen attentif de votre dossier, nous sommes au regret de vous informer que nous ne poursuivrons pas votre candidature pour ce poste.</p>
        
        <p>Cette décision ne reflète en aucun cas vos compétences professionnelles. Nous avons reçu de nombreuses candidatures de qualité et nous avons dû faire des choix difficiles.</p>
        
        <p>Nous vous encourageons à consulter régulièrement notre site pour d'autres opportunités qui pourraient correspondre à votre profil.</p>
        
        <p>Nous vous souhaitons beaucoup de succès dans vos recherches professionnelles et dans votre carrière.</p>
        
        <p>Cordialement,<br>
        L'équipe de recrutement de $companyName</p>
        
        <a href="http://localhost/projet_youssef/front_office/view/offre.php" class="button">Voir d'autres offres</a>
    </div>
    <div class="footer">
        <p>CityPulse © 2025 | Ceci est un email automatique, merci de ne pas y répondre directement</p>
    </div>
</body>
</html>
HTML;
    }
}
?>