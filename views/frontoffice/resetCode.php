<?php
session_start();
require __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgotPassword.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $userCode = $_POST['code'];
    $email = $_SESSION['reset_email'];

    try {
        // Get the stored code and expiry time
        $stmt = $pdo->prepare("SELECT reset_code, reset_token_expiry FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !$user['reset_code']) {
            $error = "No reset code found. Please request a new one.";
        } else {
            // Compare current time with expiry time
            $currentTime = time();
            $expiryTime = strtotime($user['reset_token_expiry']);
            
            if ($currentTime > $expiryTime) {
                $error = "Reset code has expired. Please request a new one.";
            } elseif ($userCode !== $user['reset_code']) {
                $error = "Invalid reset code. Please try again.";
            } else {
                // Code is valid - proceed to password reset
                $_SESSION['code_verified'] = true;
                header("Location: resetPassword.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again.";
        $_SESSION['mail_debug'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Code Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/resetcodestyle.css">
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
        .code-input {
            letter-spacing: 5px;
            font-size: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
<script src="/path/to/transitions.js"></script>
    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4">Verify Reset Code</h2>
            
            <!-- Success Message -->
            <?php if (isset($_GET['sent']) && isset($_SESSION['mail_success'])): ?>
                <div class="alert alert-success">
                    ✓ Reset code sent! Check your email.
                </div>
                <?php unset($_SESSION['mail_success']); ?>
            <?php endif; ?>

            <!-- Error Messages -->
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

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">✗ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Verification Form -->
            <form method="POST">
                <div class="mb-3">
                    <label for="code" class="form-label">Enter 6-digit Code</label>
                    <input type="text" class="form-control code-input" id="code" name="code" 
                           required maxlength="6" pattern="\d{6}" inputmode="numeric">
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify Code</button>
            </form>

            <div class="mt-3 text-center">
                <p>Didn't receive a code? <a href="forgotPassword.php" class="text-decoration-none">Resend Code</a></p>
                <a href="login.php" class="text-decoration-none">← Back to Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit if 6 digits entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>