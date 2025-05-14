<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../models/User.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $username = trim(htmlspecialchars($_POST['username'] ?? ''));
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $country_code = trim(htmlspecialchars($_POST['country'] ?? ''));

        // Validate inputs
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        
        // Enhanced email validation
        if (empty($email)) {
            $errors[] = "Email is required.";
        } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        } 
        elseif (strpos($email, '@') === false) {
            $errors[] = "Email must contain '@' symbol.";
        } 
        elseif (strpos($email, '.') === false) {
            $errors[] = "Email must contain a domain (e.g., example.com).";
        }
        
        if (empty($password) || strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters.";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($country_code)) {
            $errors[] = "Please select your country.";
        }

        // If validation errors exist
        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            header("Location: ../views/frontoffice/register.php");
            exit();
        }

        // Process registration
        $user = new User();
        
        // Check if email exists
        if ($user->getUserByEmail($email)) {
            $_SESSION['register_error'] = "Email already registered.";
            header("Location: ../views/frontoffice/register.php");
            exit();
        }

        // Create new user with country
        if ($user->createUser($username, $email, $password, $country_code)) {
            $_SESSION['register_success'] = "Registration successful! Please login.";
            header("Location: ../views/frontoffice/login.php");
            exit();
        } else {
            throw new Exception("Registration failed. Please try again.");
        }

    } catch (PDOException $e) {
        // Log the error (in production)
        error_log($e->getMessage());
        
        $_SESSION['register_error'] = "Database error occurred. Please try again.";
        header("Location: ../views/frontoffice/register.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['register_error'] = $e->getMessage();
        header("Location: ../views/frontoffice/register.php");
        exit();
    }
} else {
    // If not POST request, redirect to registration page
    header("Location: ../views/frontoffice/register.php");
    exit();
}
?>