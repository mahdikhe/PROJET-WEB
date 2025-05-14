<?php
session_start();

// Check if user is logged in before processing logout
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    require_once '../models/User.php';
    $userModel = new User();

    // Calculate session duration if login time exists
    if (isset($_SESSION['login_time'])) {
        $loginTime = strtotime($_SESSION['login_time']);
        $sessionDuration = time() - $loginTime;
        
        // Update user's total time spent and session count
        $userModel->updateUser($_SESSION['user_id'], [
            'total_time_spent' => new PDOExpr("total_time_spent + $sessionDuration"),
            'session_count' => new PDOExpr("session_count + 1")
        ]);
    }
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with success message
header("Location: ../views/frontoffice/login.php?logout=success");
exit();
?>