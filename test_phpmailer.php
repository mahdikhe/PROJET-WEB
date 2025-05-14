<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    // Debugging
    $mail->SMTPDebug = 4;
    $mail->Debugoutput = function($str, $level) {
        file_put_contents('smtp_debug.log', "$level: $str\n", FILE_APPEND);
    };

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'khelilmahdi60@gmail.com';
    $mail->Password = 'nlyh thqf tbuq wbbx';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Bypass SSL verification (local development only)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    // Recipients
    $mail->setFrom('khelilmahdi60@gmail.com', 'Your Name');
    $mail->addAddress('rasiyouja7@gmail.com', 'Recipient Name');
    
    // Content
    $mail->isHTML(false); // Set to true if sending HTML
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a plain-text test email';
    $mail->AltBody = 'This is a plain-text version';

    $mail->send();
    echo "Email sent! Check spam folder if not received.";
} catch (Exception $e) {
    echo "Failed: " . $e->getMessage();
    file_put_contents('error.log', $e->getMessage(), FILE_APPEND);
}