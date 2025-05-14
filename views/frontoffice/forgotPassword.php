<?php
session_start();
require  'C:\xampp\htdocs\projet web fr\config\database.php'; // Include your database connection
require 'C:\xampp\htdocs\projet web fr\vendor\autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['reset_error'] = "Email not found in our system.";
            header("Location: forgotPassword.php");
            exit();
        }

        // Generate 6-digit code
        $resetCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = date('Y-m-d H:i:s', time() + 600); // 10 minutes expiry

        // Store code in database
        $stmt = $pdo->prepare("UPDATE users SET reset_code = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->execute([$resetCode, $expiry, $email]);

        // Send email
        $mail = new PHPMailer(true);
        
        // SMTP Configuration (Gmail example)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'khelilmahdi60@gmail.com'; // Your Gmail
        $mail->Password   = 'nlyh thqf tbuq wbbx';    // App Password (NOT Gmail password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Email content
        $mail->setFrom('khelilmahdi60@gmail.com', 'projetweb');
        $mail->addAddress($email); // User's email
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body = "Your password reset code is: <strong>$resetCode</strong><br>Valid for 10 minutes.";

        $mail->send();
        
        $_SESSION['mail_success'] = true;
        $_SESSION['reset_email'] = $email;
        header("Location: resetCode.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['reset_error'] = "Failed to send reset code. Please try again.";
        $_SESSION['mail_debug'] = $mail->ErrorInfo;
        header("Location: forgotPassword.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/forgotpasswordstyle.css">
    <link href="../../assets/css/transitions.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .auth-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<script src="/path/to/transitions.js"></script>
    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4">Forgot Password</h2>
            
            <!-- Status Alerts -->
            <?php if (isset($_SESSION['mail_success'])): ?>
                <div class="alert alert-success">
                    <div class="small text-muted mt-1">
                        Debug: <?= htmlspecialchars($_SESSION['mail_debug'] ?? '') ?>
                    </div>
                </div>
                <?php unset($_SESSION['mail_success'], $_SESSION['mail_debug']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['reset_error'])): ?>
                <div class="alert alert-danger">
                    ✗ <?= htmlspecialchars($_SESSION['reset_error']) ?>
                    <?php if (isset($_SESSION['mail_debug'])): ?>
                        <div class="small text-muted mt-1">
                            <?= htmlspecialchars($_SESSION['mail_debug']) ?>
                        </div>
                        <?php unset($_SESSION['mail_debug']); ?>
                    <?php endif; ?>
                </div>
                <?php unset($_SESSION['reset_error']); ?>
            <?php endif; ?>

            <!-- Email Form -->
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Reset Code</button>
            </form>

            <div class="mt-3 text-center">
                <a href="login.php" class="text-decoration-none">← Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>