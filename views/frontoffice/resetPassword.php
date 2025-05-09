<?php
session_start();
require __DIR__ . '/../../config/database.php';

// Verify the user has completed the reset process
if (!isset($_SESSION['code_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgotPassword.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['confirm_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    try {
        // Validate passwords
        if (strlen($newPassword) < 8) {
            $_SESSION['reset_error'] = "Password must be at least 8 characters long";
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['reset_error'] = "Passwords do not match";
        } else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password and clear reset token
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_token_expiry = NULL WHERE email = ?");
            $stmt->execute([$hashedPassword, $email]);

            if ($stmt->rowCount() > 0) {
                // Clear session and redirect
                unset($_SESSION['reset_email'], $_SESSION['code_verified']);
                $_SESSION['password_reset_success'] = "Password updated successfully!";
                header("Location: login.php");
                exit();
            } else {
                throw new Exception("Failed to update password");
            }
        }
    } catch (Exception $e) {
        $_SESSION['reset_error'] = "Error updating password. Please try again.";
        $_SESSION['mail_debug'] = $e->getMessage();
    }
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="refresh" content="0;url='.$redirectUrl.'">
    </head>
    <body>
        <div class="page-transition"></div>
    </body>
    </html>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/resetpasswordstyle.css">
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
            <h2 class="text-center mb-4">Reset Password</h2>
            
            <?php if (isset($_SESSION['reset_error'])): ?>
                <div class="alert alert-danger">
                    ✗ <?= htmlspecialchars($_SESSION['reset_error']) ?>
                    <?php if (isset($_SESSION['mail_debug'])): ?>
                        <div class="small text-muted mt-1">
                            <?= htmlspecialchars($_SESSION['mail_debug']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php unset($_SESSION['reset_error'], $_SESSION['mail_debug']); ?>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    <div class="form-text">Minimum 8 characters</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side password matching validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const pass1 = document.getElementById('new_password').value;
            const pass2 = document.getElementById('confirm_password').value;
            
            if (pass1 !== pass2) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>