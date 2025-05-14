<?php
// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session at the beginning
session_start();

// Include required files
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../models/User.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Initialize error array
        $errors = [];

        // Validate and sanitize email
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        if (empty($email)) {
            $errors['email'] = "Email field is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address.";
        } elseif (strlen($email) > 255) {
            $errors['email'] = "Email address is too long.";
        }

        // Validate password
        $password = $_POST['password'];
        if (empty($password)) {
            $errors['password'] = "Password field is required.";
        } elseif (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters long.";
        } elseif (strlen($password) > 72) {
            $errors['password'] = "Password is too long.";
        }

        // If there are validation errors, redirect back with errors
        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['old_email'] = $email; // Preserve the email for the form
            header("Location: ../views/frontoffice/login.php");
            exit();
        }

        // Process login
        $user = new User();
        $loggedInUser = $user->verifyUser($email, $password);

        if ($loggedInUser) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Clear any previous errors
            unset($_SESSION['login_errors']);
            unset($_SESSION['old_email']);
            
            // Set session variables
            $_SESSION['user_id'] = $loggedInUser['id'];
            $_SESSION['username'] = $loggedInUser['username'];
            $_SESSION['email'] = $loggedInUser['email'];
            $_SESSION['is_admin'] = $loggedInUser['is_admin'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();

            // Update last login
            $user->updateLastLogin($loggedInUser['id']);
            
            // Set welcome message
            $_SESSION['login_success'] = "Welcome back, " . htmlspecialchars($loggedInUser['username']) . "!";

            // Redirect to appropriate dashboard based on admin status
            if ($loggedInUser['is_admin'] == 1) {
                header("Location: ../views/backoffice/dashboardadmin.php");
            } else {
                header("Location: ../views/frontoffice/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['login_errors']['credentials'] = "Invalid email or password.";
            $_SESSION['old_email'] = $email; // Preserve the email for the form
            header("Location: ../views/frontoffice/login.php");
            exit();
        }

    } catch (PDOException $e) {
        // Log the error (in production)
        error_log($e->getMessage());
        
        $_SESSION['login_errors']['database'] = "A database error occurred. Please try again later.";
        header("Location: ../views/frontoffice/login.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['login_errors']['general'] = "An unexpected error occurred. Please try again.";
        header("Location: ../views/frontoffice/login.php");
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header("Location: ../views/frontoffice/login.php");
    exit();
}
?>