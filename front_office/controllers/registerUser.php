<?php
// Register user controller
require_once '../../back_office/model/config.php';

// Initialize error
$error = '';

// Get form data
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$adresse = isset($_POST['adresse']) ? trim($_POST['adresse']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
$terms = isset($_POST['terms']) ? true : false;

// Validate form data
if (empty($nom)) {
    $error = "Le nom est requis.";
} elseif (empty($prenom)) {
    $error = "Le prénom est requis.";
} elseif (empty($email)) {
    $error = "L'email est requis.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Format d'email invalide.";
} elseif (empty($adresse)) {
    $error = "L'adresse est requise.";
} elseif (empty($password)) {
    $error = "Le mot de passe est requis.";
} elseif (strlen($password) < 8) {
    $error = "Le mot de passe doit contenir au moins 8 caractères.";
} elseif ($password !== $confirm_password) {
    $error = "Les mots de passe ne correspondent pas.";
} elseif (!$terms) {
    $error = "Vous devez accepter les conditions d'utilisation.";
} else {
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password_db);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $error = "Cet email est déjà utilisé. Veuillez vous connecter ou utiliser un autre email.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, adresse) VALUES (:nom, :prenom, :email, :password, :adresse)");
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
            $stmt->execute();
            
            // Get new user ID
            $user_id = $pdo->lastInsertId();
            
            // Start session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $prenom . ' ' . $nom;
            $_SESSION['user_email'] = $email;
            
            // Redirect to job listings
            header('Location: offre.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de l'inscription: " . $e->getMessage();
    }
}
?>