<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

class PasswordResetController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Show forgot password form
    public function showForgotPassword() {
        $error = $_SESSION['reset_error'] ?? null;
        unset($_SESSION['reset_error']);
        require_once __DIR__ . '/../views/frontoffice/forgotPassword.php';
    }
    
    // Process email submission and send reset code
    public function sendResetCode() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['reset_error'] = "Please enter a valid email address";
                header('Location: /forgotPassword');
                exit();
            }
            
            // Check if user exists
            $user = $this->userModel->getUserByEmail($email);
            if (!$user) {
                $_SESSION['reset_error'] = "No account found with that email";
                header('Location: /forgotPassword');
                exit();
            }
            
            // Generate reset code (6 digits)
            $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $resetToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Save code to database
            $this->userModel->setResetToken($user['id'], $resetCode, $resetToken, $expiry);
            
            // Send email with reset code
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // Debugging (remove in production)
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    file_put_contents(__DIR__.'/../logs/mail_debug.log', "$level: $str\n", FILE_APPEND);
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
    
                // Bypass SSL verification (local only)
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
    
                // Recipients
                $mail->setFrom('khelilmahdi60@gmail.com', 'Password Reset');
                $mail->addAddress($email);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Code';
                $mail->Body    = "Your password reset code is: <b>$resetCode</b><br><br>This code is valid for 15 minutes.";
                $mail->AltBody = "Your password reset code is: $resetCode\n\nThis code is valid for 15 minutes.";
    
                if ($mail->send()) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_attempts'] = 0;
                    $_SESSION['mail_success'] = true;
                    $_SESSION['mail_debug'] = "Email sent to $email";
                    header('Location: /resetCode');
                    exit();
                }
            } catch (Exception $e) {
                error_log("Mailer Error: " . $e->getMessage());
                $_SESSION['reset_error'] = "Failed to send reset code. Please try again later.";
                $_SESSION['mail_debug'] = $e->getMessage();
                header('Location: /forgotPassword');
                exit();
            }
        }
    }
    
    // Show reset code form
    public function showResetCode() {
        if (!isset($_SESSION['reset_email'])) {
            header('Location: /forgotPassword');
            exit();
        }
        
        $error = $_SESSION['reset_error'] ?? null;
        unset($_SESSION['reset_error']);
        require_once __DIR__ . '/../views/frontoffice/resetCode.php';
    }
    
    // Verify reset code
    public function verifyResetCode() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['code']);
            
            if (!isset($_SESSION['reset_email']) || empty($code)) {
                $_SESSION['reset_error'] = "Invalid request";
                header('Location: /forgotPassword');
                exit();
            }
            
            // Track attempts to prevent brute force
            $_SESSION['reset_attempts'] = ($_SESSION['reset_attempts'] ?? 0) + 1;
            if ($_SESSION['reset_attempts'] > 5) {
                $_SESSION['reset_error'] = "Too many attempts. Please start over.";
                unset($_SESSION['reset_email']);
                header('Location: /forgotPassword');
                exit();
            }
            
            $email = $_SESSION['reset_email'];
            $user = $this->userModel->getUserByEmail($email);
            
            // Verify code and token expiry
            if ($user && $user['reset_code'] === $code && strtotime($user['reset_token_expiry']) > time()) {
                $_SESSION['reset_token'] = $user['reset_token'];
                unset($_SESSION['reset_attempts']);
                header('Location: /newPassword');
                exit();
            } else {
                $_SESSION['reset_error'] = "Invalid or expired reset code";
                header('Location: /resetCode');
                exit();
            }
        }
    }
    
    // Show new password form
    public function showNewPassword() {
        if (!isset($_SESSION['reset_token'])) {
            header('Location: /forgotPassword');
            exit();
        }
        
        $error = $_SESSION['reset_error'] ?? null;
        unset($_SESSION['reset_error']);
        require_once __DIR__ . '/../views/frontoffice/newPassword.php';
    }
    
    // Update password
    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['reset_token'])) {
                header('Location: /forgotPassword');
                exit();
            }
            
            $password = trim($_POST['password']);
            $confirmPassword = trim($_POST['confirm_password']);
            
            // Validate passwords
            if (empty($password) || empty($confirmPassword)) {
                $_SESSION['reset_error'] = "Please fill in all fields";
                header('Location: /newPassword');
                exit();
            }
            
            if ($password !== $confirmPassword) {
                $_SESSION['reset_error'] = "Passwords do not match";
                header('Location: /newPassword');
                exit();
            }
            
            if (strlen($password) < 8) {
                $_SESSION['reset_error'] = "Password must be at least 8 characters long";
                header('Location: /newPassword');
                exit();
            }
            
            // Get user by reset token
            $user = $this->userModel->getUserByResetToken($_SESSION['reset_token']);
            
            if ($user && strtotime($user['reset_token_expiry']) > time()) {
                // Hash new password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Update password and clear reset token
                $this->userModel->updatePassword($user['id'], $hashedPassword);
                $this->userModel->clearResetToken($user['id']);
                
                // Clean up session
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_token']);
                
                $_SESSION['success'] = "Your password has been updated successfully";
                header('Location: /login');
                exit();
            } else {
                $_SESSION['reset_error'] = "Invalid or expired reset token";
                header('Location: /newPassword');
                exit();
            }
        }
    }
}