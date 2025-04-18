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
        // Validate and sanitize input
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $password = $_POST['password'];

        // Validate inputs
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = "Please enter a valid email address.";
            header("Location: ../views/frontoffice/login.php");
            exit();
        }

        if (empty($password)) {
            $_SESSION['login_error'] = "Please enter your password.";
            header("Location: ../views/frontoffice/login.php");
            exit();
        }

        // Process login
        $user = new User();
        $loggedInUser = $user->verifyUser($email, $password);
        

        if ($loggedInUser) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $loggedInUser['id'];
            $_SESSION['username'] = $loggedInUser['username'];
            $_SESSION['email'] = $loggedInUser['email'];
            $_SESSION['is_admin'] = $loggedInUser['is_admin'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();

            $user->updateLastLogin($loggedInUser['id']);
            
            // Set welcome message
            $_SESSION['login_success'] = "Welcome back, " . htmlspecialchars($loggedInUser['username']) . "!";

            // After successful login
            $user->updateUser($userId, [
            'last_login' => date('Y-m-d H:i:s')
            ]);
            // Redirect to appropriate dashboard based on admin status
            if ($loggedInUser['is_admin'] == 1) {
                header("Location: ../views/backoffice/dashboardadmin.php");
            } else {
                header("Location: ../views/frontoffice/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
            header("Location: ../views/frontoffice/login.php");
            exit();
        }

    } catch (PDOException $e) {
        // Log the error (in production)
        error_log($e->getMessage());
        
        $_SESSION['login_error'] = "Database error occurred. Please try again.";
        header("Location: ../views/frontoffice/login.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        header("Location: ../views/frontoffice/login.php");
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header("Location: ../views/frontoffice/login.php");
    exit();
}
?>