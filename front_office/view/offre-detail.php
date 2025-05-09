<?php
// Include the PHP functions to get job offer data
require_once '../model/getOffers.php';
session_start();

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to offers page if no ID provided
    header('Location: offre.php');
    exit;
}

// Get ID from URL (keep as string since IDs in database are VARCHAR)
$offerId = $_GET['id'];

// Create a function to get a single offer by ID
function getOfferDetail($id) {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Debug info - Log the ID we're searching for
        error_log("Searching for offer with ID: " . $id);
        
        // Prepare the query
        $stmt = $pdo->prepare("SELECT * FROM offres WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_STR); // Changed to PARAM_STR because ID is VARCHAR in database
        $stmt->execute();
        
        // Fetch the offer
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug info - Log whether we found an offer
        if ($offer) {
            error_log("Offer found: " . $offer['titre']);
        } else {
            error_log("No offer found with ID: " . $id);
            
            // Additional diagnostics - print all offers to log
            $allOffersStmt = $pdo->prepare("SELECT id, titre FROM offres LIMIT 10");
            $allOffersStmt->execute();
            $allOffers = $allOffersStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Available offers in database: " . print_r($allOffers, true));
        }
        
        return $offer;
    } catch (PDOException $e) {
        error_log("Database error in getOfferDetail: " . $e->getMessage());
        return null;
    }
}

// Get offer details
$offer = getOfferDetail($offerId);

// If offer not found, redirect
if (!$offer) {
    header('Location: offre.php');
    exit;
}

// Function to generate company logo placeholder from company name
function generateLogoPlaceholder($companyName) {
    $words = explode(' ', $companyName);
    $initials = '';
    
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) {
                break;
            }
        }
    }
    
    return $initials;
}

// Check if user is already applied for this job
$alreadyApplied = false;
if (isset($_SESSION['user_id'])) {
    global $servername, $username, $password, $dbname;
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT id FROM entretiens WHERE id_offre = :id_offre AND id_user = :id_user LIMIT 1");
        $stmt->bindParam(':id_offre', $offerId, PDO::PARAM_STR); // Changed to PARAM_STR for consistency
        $stmt->bindParam(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            $alreadyApplied = true;
        }
    } catch (PDOException $e) {
        error_log("Error checking application status: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($offer['titre']); ?> | CityPulse</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.0/css/boxicons.min.css">
    <style>
        :root {
            --primary-color: #6857E8;
            --secondary-color: #F782C2;
            --gradient-bg: linear-gradient(135deg, #6857E8 0%, #9969FF 100%);
            --card-gradient: linear-gradient(135deg, #f5f7ff 0%, #f9f0ff 100%);
            --shadow: 0 10px 15px -3px rgba(104, 87, 232, 0.1), 0 4px 6px -2px rgba(104, 87, 232, 0.05);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: #333;
        }
        
        .gradient-bg {
            background: var(--gradient-bg);
        }
        
        .primary-btn {
            background: var(--gradient-bg);
            color: white;
            transition: all 0.3s ease;
        }
        
        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(104, 87, 232, 0.2);
        }
        
        .secondary-btn {
            background: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .secondary-btn:hover {
            background: rgba(104, 87, 232, 0.05);
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            background-color: #f5f7ff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-weight: bold;
            font-size: 24px;
            color: var(--primary-color);
        }
        
        .active-nav-link {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .header-nav {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .job-detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        
        .job-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .form-input {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 16px;
            transition: border-color 0.2s;
            outline: none;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(104, 87, 232, 0.1);
        }
        
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #4a5568;
        }
        
        .form-textarea {
            width: 100%;
            min-height: 120px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 16px;
            transition: border-color 0.2s;
            outline: none;
            resize: vertical;
        }
        
        .form-textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(104, 87, 232, 0.1);
        }
        
        .error-text {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: -14px;
            margin-bottom: 10px;
            display: none;
        }
        
        /* Form validation styles */
        .form-input.error, .form-textarea.error {
            border-color: #e53e3e;
        }
        
        .form-input.valid, .form-textarea.valid {
            border-color: #48bb78;
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <header class="header-nav bg-white py-3 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="text-2xl font-bold text-gray-800">
                        <span class="text-transparent bg-clip-text" style="background-image: var(--gradient-bg);">City</span>Pulse
                    </div>
                </div>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="index.html" class="py-2 px-1 text-gray-600 hover:text-gray-900">Accueil</a>
                    <a href="offre.php" class="py-2 px-1 active-nav-link">Offres</a>
                    <a href="#" class="py-2 px-1 text-gray-600 hover:text-gray-900">Utilisateurs</a>
                    <a href="#" class="py-2 px-1 text-gray-600 hover:text-gray-900">Transport</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="text-gray-700">
                            Bonjour, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?>
                        </div>
                        <a href="../controllers/logout.php" class="secondary-btn rounded-full py-2 px-6">Déconnexion</a>
                    <?php else: ?>
                        <a href="login.php" class="primary-btn rounded-full py-2 px-6">Connexion</a>
                        <a href="register.php" class="secondary-btn rounded-full py-2 px-6">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="text-sm text-gray-500 mb-6">
            <a href="offre.php" class="hover:text-primary-600">Offres d'emploi</a>
            <span class="mx-2">></span>
            <span class="text-gray-800"><?php echo htmlspecialchars($offer['titre']); ?></span>
        </div>
        
        <!-- Job Details Card -->
        <div class="job-detail-card p-8 mb-10">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start mb-8">
                <div class="flex flex-col md:flex-row md:items-center mb-6 md:mb-0">
                    <div class="company-logo mr-6 mb-4 md:mb-0">
                        <?php echo generateLogoPlaceholder($offer['entreprise']); ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-3"><?php echo htmlspecialchars($offer['titre']); ?></h1>
                        <p class="text-xl text-gray-600 mb-3"><?php echo htmlspecialchars($offer['entreprise']); ?></p>
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="job-info-item">
                                <i class="bx bx-map text-lg mr-2 text-gray-500"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($offer['emplacement']); ?></span>
                            </div>
                            <div class="job-info-item">
                                <i class="bx bx-calendar text-lg mr-2 text-gray-500"></i>
                                <span class="text-gray-700">Publié le <?php echo formatOfferDate($offer['date']); ?></span>
                            </div>
                            <div class="job-info-item">
                                <i class="bx bx-briefcase text-lg mr-2 text-gray-500"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($offer['type']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    <button class="primary-btn rounded-full py-2 px-6 flex items-center">
                        <i class="bx bx-bookmark mr-2"></i> Sauvegarder
                    </button>
                    <button class="secondary-btn rounded-full py-2 px-6 flex items-center">
                        <i class="bx bx-share-alt mr-2"></i> Partager
                    </button>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-8">
                <h2 class="text-2xl font-semibold mb-6">Description du poste</h2>
                <div class="text-gray-700 mb-8 whitespace-pre-line">
                    <?php echo nl2br(htmlspecialchars($offer['description'])); ?>
                </div>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="bg-blue-50 p-6 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold text-blue-700 mb-2">Connexion requise</h3>
                        <p class="text-blue-600 mb-4">Vous devez vous connecter pour postuler à cette offre d'emploi.</p>
                        <a href="login.php?redirect=offre-detail.php?id=<?php echo urlencode($offerId); ?>" class="primary-btn rounded-lg py-3 px-8 inline-block">Se connecter</a>
                        <a href="register.php?redirect=offre-detail.php?id=<?php echo urlencode($offerId); ?>" class="secondary-btn rounded-lg py-3 px-8 inline-block ml-4">S'inscrire</a>
                    </div>
                <?php elseif ($alreadyApplied): ?>
                    <div class="bg-green-50 p-6 rounded-lg mb-6">
                        <h3 class="text-lg font-semibold text-green-700 mb-2">Candidature déjà envoyée</h3>
                        <p class="text-green-600 mb-4">Vous avez déjà postulé à cette offre. Vous pouvez consulter votre candidature dans votre espace personnel.</p>
                        <a href="mes-candidatures.php" class="primary-btn rounded-lg py-3 px-8 inline-block">Voir mes candidatures</a>
                    </div>
                <?php else: ?>
                    <a href="#apply-section" class="primary-btn rounded-lg py-3 px-8 inline-block">Postuler maintenant</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Apply Section -->
        <?php if (isset($_SESSION['user_id']) && !$alreadyApplied): ?>
            <div id="apply-section" class="job-detail-card p-8 mb-10">
                <h2 class="text-2xl font-semibold mb-6">Postuler pour: <?php echo htmlspecialchars($offer['titre']); ?></h2>
                <p class="text-gray-600 mb-8">Veuillez remplir le formulaire ci-dessous pour postuler à cette offre d'emploi.</p>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-50 p-4 rounded-lg mb-6">
                        <p class="text-red-600"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
                    <div class="bg-green-50 p-4 rounded-lg mb-6">
                        <p class="text-green-600">Votre candidature a été soumise avec succès !</p>
                    </div>
                <?php endif; ?>
                
                <form id="job-application-form" method="post" action="../controllers/submitApplication.php">
                    <input type="hidden" name="id_offre" value="<?php echo htmlspecialchars($offer['id']); ?>">
                    
                    <div class="mb-6">
                        <label for="competences" class="form-label">Compétences (séparées par des virgules)</label>
                        <input type="text" id="competences" name="competences" class="form-input" required>
                        <p class="error-text" id="competences-error">Veuillez entrer au moins une compétence</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="presentation" class="form-label">Présentation</label>
                        <textarea id="presentation" name="presentation" class="form-textarea" required></textarea>
                        <p class="error-text" id="presentation-error">Veuillez vous présenter (minimum 50 caractères)</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="motivation" class="form-label">Motivation</label>
                        <textarea id="motivation" name="motivation" class="form-textarea" required></textarea>
                        <p class="error-text" id="motivation-error">Veuillez expliquer votre motivation (minimum 50 caractères)</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="pourquoi_lui" class="form-label">Pourquoi vous ?</label>
                        <textarea id="pourquoi_lui" name="pourquoi_lui" class="form-textarea" required></textarea>
                        <p class="error-text" id="pourquoi_lui-error">Veuillez expliquer pourquoi l'entreprise devrait vous choisir (minimum 50 caractères)</p>
                    </div>
                    
                    <button type="submit" class="primary-btn rounded-lg py-3 px-8 w-full md:w-auto">Envoyer ma candidature</button>
                </form>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-100 py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">CityPulse</h3>
                    <p class="text-gray-600">La plateforme qui connecte les talents aux opportunités dans notre ville intelligente.</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Pages</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Accueil</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Offres</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Utilisateurs</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Transport</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Ressources</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Guide de la ville</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Blog</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">Événements</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary-600">FAQ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Nous contacter</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-600">
                            <i class="bx bx-envelope mr-2"></i>
                            <span>contact@citypulse.com</span>
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="bx bx-phone mr-2"></i>
                            <span>+33 1 23 45 67 89</span>
                        </li>
                        <li class="flex items-center text-gray-600">
                            <i class="bx bx-map mr-2"></i>
                            <span>10 Place de l'Innovation, Ville</span>
                        </li>
                    </ul>
                    
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-600 hover:text-primary-600">
                            <i class="bx bxl-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-primary-600">
                            <i class="bx bxl-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-primary-600">
                            <i class="bx bxl-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-600 hover:text-primary-600">
                            <i class="bx bxl-instagram text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 mt-10 pt-6 text-center text-gray-500">
                <p>© 2023 CityPulse - Tous droits réservés</p>
            </div>
        </div>
    </footer>
    
    <!-- Form Validation JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('job-application-form');
            if (!form) return; // Form might not be present if user is not logged in
            
            // Get all form inputs
            const competences = document.getElementById('competences');
            const presentation = document.getElementById('presentation');
            const motivation = document.getElementById('motivation');
            const pourquoi_lui = document.getElementById('pourquoi_lui');
            
            // Error messages
            const competencesError = document.getElementById('competences-error');
            const presentationError = document.getElementById('presentation-error');
            const motivationError = document.getElementById('motivation-error');
            const pourquoiLuiError = document.getElementById('pourquoi_lui-error');
            
            // Validation functions
            function validateCompetences(competences) {
                return competences.trim().length >= 3;
            }
            
            function validateTextarea(text) {
                // Check if text has at least 5 words and at most 255 words
                const words = text.trim().split(/\s+/);
                return words.length >= 5 && words.length <= 255;
            }
            
            // Show error message
            function showError(input, errorElement, valid, customMessage = null) {
                if (!valid) {
                    input.classList.add('error');
                    input.classList.remove('valid');
                    errorElement.style.display = 'block';
                    if (customMessage) {
                        errorElement.textContent = customMessage;
                    }
                } else {
                    input.classList.remove('error');
                    input.classList.add('valid');
                    errorElement.style.display = 'none';
                }
                return valid;
            }
            
            // Add event listeners for real-time validation
            competences.addEventListener('input', () => {
                showError(competences, competencesError, validateCompetences(competences.value));
            });
            
            presentation.addEventListener('input', () => {
                const valid = validateTextarea(presentation.value);
                const words = presentation.value.trim().split(/\s+/);
                let message = "Veuillez vous présenter";
                
                if (words.length < 5) {
                    message = "Votre présentation doit contenir au moins 5 mots";
                } else if (words.length > 255) {
                    message = "Votre présentation ne doit pas dépasser 255 mots";
                }
                
                showError(presentation, presentationError, valid, message);
            });
            
            motivation.addEventListener('input', () => {
                const valid = validateTextarea(motivation.value);
                const words = motivation.value.trim().split(/\s+/);
                let message = "Veuillez expliquer votre motivation";
                
                if (words.length < 5) {
                    message = "Votre motivation doit contenir au moins 5 mots";
                } else if (words.length > 255) {
                    message = "Votre motivation ne doit pas dépasser 255 mots";
                }
                
                showError(motivation, motivationError, valid, message);
            });
            
            pourquoi_lui.addEventListener('input', () => {
                const valid = validateTextarea(pourquoi_lui.value);
                const words = pourquoi_lui.value.trim().split(/\s+/);
                let message = "Veuillez expliquer pourquoi nous devrions vous choisir";
                
                if (words.length < 5) {
                    message = "Votre explication doit contenir au moins 5 mots";
                } else if (words.length > 255) {
                    message = "Votre explication ne doit pas dépasser 255 mots";
                }
                
                showError(pourquoi_lui, pourquoiLuiError, valid, message);
            });
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate all fields
                if (!showError(competences, competencesError, validateCompetences(competences.value))) isValid = false;
                
                const presentationValid = validateTextarea(presentation.value);
                const presentationWords = presentation.value.trim().split(/\s+/);
                let presentationMessage = "Veuillez vous présenter";
                if (presentationWords.length < 5) {
                    presentationMessage = "Votre présentation doit contenir au moins 5 mots";
                } else if (presentationWords.length > 255) {
                    presentationMessage = "Votre présentation ne doit pas dépasser 255 mots";
                }
                if (!showError(presentation, presentationError, presentationValid, presentationMessage)) isValid = false;
                
                const motivationValid = validateTextarea(motivation.value);
                const motivationWords = motivation.value.trim().split(/\s+/);
                let motivationMessage = "Veuillez expliquer votre motivation";
                if (motivationWords.length < 5) {
                    motivationMessage = "Votre motivation doit contenir au moins 5 mots";
                } else if (motivationWords.length > 255) {
                    motivationMessage = "Votre motivation ne doit pas dépasser 255 mots";
                }
                if (!showError(motivation, motivationError, motivationValid, motivationMessage)) isValid = false;
                
                const pourquoiLuiValid = validateTextarea(pourquoi_lui.value);
                const pourquoiLuiWords = pourquoi_lui.value.trim().split(/\s+/);
                let pourquoiLuiMessage = "Veuillez expliquer pourquoi nous devrions vous choisir";
                if (pourquoiLuiWords.length < 5) {
                    pourquoiLuiMessage = "Votre explication doit contenir au moins 5 mots";
                } else if (pourquoiLuiWords.length > 255) {
                    pourquoiLuiMessage = "Votre explication ne doit pas dépasser 255 mots";
                }
                if (!showError(pourquoi_lui, pourquoiLuiError, pourquoiLuiValid, pourquoiLuiMessage)) isValid = false;
                
                // Prevent form submission if not valid
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to the first error
                    const firstError = document.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        });
    </script>
</body>
</html>