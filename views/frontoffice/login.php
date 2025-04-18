<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Your App</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/loginstyle.css">
</head>
<body>
    <div class="auth-container">
        <h1>Welcome Back</h1>
        
        <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
            <div class="alert success">Registration successful! Please login.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
            <div class="alert error">Invalid email or password.</div>
        <?php endif; ?>

        <form action="/projet web fr/controllers/loginController.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn">Continue to Your Account</button>
        </form>
        
        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Create one now</a></p>
        </div>
        <div class="auth-footer2">
            <a href="forgotPassword.php">Mot de passe oublié ?</a>
        </div>
    </div>
</body>
</html>