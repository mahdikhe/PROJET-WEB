<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: offre.php');
    exit;
}

// Handle errors from login attempt
$errors = [];
if (isset($_SESSION['login_errors'])) {
    $errors = $_SESSION['login_errors'];
    unset($_SESSION['login_errors']);
}

// Remove the problematic redirect - let the form action handle this
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Connexion</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
        }
        .header {
            background-color: #6944ff;
            color: white;
        }
        .login-container {
            max-width: 400px;
            margin: 60px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d2d6dc;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
        }
        .form-control:focus {
            border-color: #6944ff;
            box-shadow: 0 0 0 3px rgba(105, 68, 255, 0.1);
        }
        .btn-primary {
            background-color: #6944ff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #5a38e6;
        }
        .error-message {
            color: #e53e3e;
            font-size: 14px;
            margin-top: 5px;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #edf2f7;
        }
        .divider span {
            padding: 0 10px;
            color: #a0aec0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header class="header py-4">
        <div class="container mx-auto flex justify-between items-center px-4">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-md bg-white bg-opacity-20 flex items-center justify-center text-white mr-2">
                    <i class="fas fa-city"></i>
                </div>
                <h1 class="text-xl font-bold">CityPulse</h1>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="offre.php" class="text-white hover:text-purple-200">Offres d'emploi</a></li>
                    <li><a href="login.php" class="text-white font-medium hover:text-purple-200">Connexion</a></li>
                    <li><a href="register.php" class="bg-white text-purple-700 px-4 py-2 rounded-md font-medium hover:bg-purple-100">Inscription</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container mx-auto">
        <div class="login-container">
            <h2 class="text-2xl font-bold mb-6 text-center">Connexion</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form id="login-form" method="POST" action="../controllers/loginUser.php">
                <div class="form-group">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="block text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-purple-600">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="text-sm text-purple-600 hover:text-purple-800">Mot de passe oublié?</a>
                </div>
                
                <button type="submit" class="btn-primary">Se connecter</button>
            </form>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <p class="text-center text-gray-600 text-sm">
                Vous n'avez pas de compte? <a href="register.php" class="text-purple-600 hover:text-purple-800">Inscrivez-vous</a>
            </p>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-between">
                <div class="w-full md:w-1/4 mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-4">CityPulse</h3>
                    <p class="text-gray-400">Votre plateforme pour trouver les meilleures opportunités professionnelles.</p>
                </div>
                <div class="w-full md:w-1/4 mb-6 md:mb-0">
                    <h4 class="text-lg font-semibold mb-4">Liens utiles</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">À propos</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Offres d'emploi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Entreprises</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="w-full md:w-1/4 mb-6 md:mb-0">
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Avenue Example, Paris</li>
                        <li><i class="fas fa-phone mr-2"></i> +33 1 23 45 67 89</li>
                        <li><i class="fas fa-envelope mr-2"></i> contact@citypulse.fr</li>
                    </ul>
                </div>
                <div class="w-full md:w-1/4">
                    <h4 class="text-lg font-semibold mb-4">Suivez-nous</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 CityPulse. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>