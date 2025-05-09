<?php
// Start session
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=mes-candidatures.php');
    exit;
}

// Include database configuration
require_once '../../back_office/model/config.php';

/**
 * Get all applications for a specific user
 */
function getUserApplications($userId) {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get all columns from entretiens table
        $columnsQuery = "SHOW COLUMNS FROM entretiens";
        $columnsStmt = $pdo->query($columnsQuery);
        $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Determine which date column to use
        $dateColumn = in_array('created_at', $columns) ? 'created_at' : 
                     (in_array('date_entretien', $columns) ? 'date_entretien' : 
                     (in_array('date', $columns) ? 'date' : null));
        
        // If no date column found, we'll use current date as fallback
        $dateSelect = $dateColumn ? "e.$dateColumn as date" : "CURRENT_DATE() as date";
        
        // Prepare the query to get all user applications with offer details
        $query = "SELECT e.*, o.titre, o.entreprise, o.emplacement, o.type, o.date as date_offre, 
                         $dateSelect
                  FROM entretiens e
                  JOIN offres o ON e.id_offre = o.id
                  WHERE e.id_user = :user_id
                  ORDER BY e.id DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Fetch all applications
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Log results for debugging
        error_log("Found " . count($applications) . " applications for user ID: $userId");
        
        return $applications;
    } catch (PDOException $e) {
        // Log error or handle exception
        error_log("Database error in getUserApplications: " . $e->getMessage());
        return [];
    }
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

// Format date for display
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d/m/Y');
}

// Get user applications
$applications = getUserApplications($_SESSION['user_id']);

// For debugging
echo "<!-- Debug info: User ID = " . $_SESSION['user_id'] . " -->";
echo "<!-- Debug info: Applications found = " . count($applications) . " -->";

// Status translations
$statusTranslations = [
    'pending' => 'En attente',
    'accepted' => 'Accepté',
    'rejected' => 'Rejeté'
];

// Status classes for styling
$statusClasses = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'accepted' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800'
];

// Handle notifications
$notification = null;
if (isset($_GET['success'])) {
    $notification = [
        'type' => 'success',
        'message' => urldecode($_GET['success'])
    ];
} elseif (isset($_GET['error'])) {
    $notification = [
        'type' => 'error',
        'message' => urldecode($_GET['error'])
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Candidatures | CityPulse</title>
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
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(104, 87, 232, 0.1), 0 10px 10px -5px rgba(104, 87, 232, 0.04);
        }
        
        .company-logo {
            width: 50px;
            height: 50px;
            background-color: #f5f7ff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-weight: bold;
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
        
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
                    <a href="index.html" class="text-2xl font-bold text-gray-800">
                        <span class="text-transparent bg-clip-text" style="background-image: var(--gradient-bg);">City</span>Pulse
                    </a>
                </div>
                
                <nav class="hidden md:flex space-x-8">
                    <a href="index.html" class="py-2 px-1 text-gray-600 hover:text-gray-900">Accueil</a>
                    <a href="offre.php" class="py-2 px-1 text-gray-600 hover:text-gray-900">Offres</a>
                    <a href="mes-candidatures.php" class="py-2 px-1 active-nav-link">Mes Candidatures</a>
                    <a href="#" class="py-2 px-1 text-gray-600 hover:text-gray-900">Transport</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <div class="text-gray-700">
                        Bonjour, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?>
                    </div>
                    <a href="../controllers/logout.php" class="secondary-btn rounded-full py-2 px-6">Déconnexion</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Mes Candidatures</h1>
            <p class="text-gray-600">Consultez, modifiez ou supprimez vos candidatures.</p>
        </div>
        
        <!-- Notification -->
        <?php if ($notification): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $notification['type'] === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'; ?>">
                <?php echo htmlspecialchars($notification['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Applications List -->
        <div class="grid gap-6">
            <?php if (empty($applications)): ?>
                <div class="card p-8 text-center">
                    <p class="text-gray-600 mb-4">Vous n'avez encore postulé à aucune offre d'emploi.</p>
                    <a href="offre.php" class="primary-btn rounded-lg py-3 px-6 inline-block">Découvrir les offres</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $application): ?>
                    <div class="card p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="flex items-start mb-4 md:mb-0">
                                <div class="company-logo mr-4">
                                    <?php echo generateLogoPlaceholder($application['entreprise']); ?>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold"><?php echo htmlspecialchars($application['titre']); ?></h2>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($application['entreprise']); ?></p>
                                    <div class="flex flex-wrap items-center gap-4 mt-2">
                                        <div class="flex items-center text-gray-500">
                                            <i class="bx bx-map text-lg mr-2"></i>
                                            <span><?php echo htmlspecialchars($application['emplacement']); ?></span>
                                        </div>
                                        <div class="flex items-center text-gray-500">
                                            <i class="bx bx-calendar text-lg mr-2"></i>
                                            <span>Postulé le <?php echo formatDate($application['date']); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="px-3 py-1 rounded-full text-sm <?php echo $statusClasses[$application['status']]; ?>">
                                                <?php echo $statusTranslations[$application['status']]; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="offre-detail.php?id=<?php echo urlencode($application['id_offre']); ?>" class="secondary-btn py-2 px-4 rounded-lg text-sm flex items-center">
                                    <i class="bx bx-show mr-2"></i> Voir l'offre
                                </a>
                                <button data-id="<?php echo $application['id']; ?>" class="view-btn primary-btn py-2 px-4 rounded-lg text-sm flex items-center">
                                    <i class="bx bx-detail mr-2"></i> Voir détails
                                </button>
                                <?php
                                // Allow editing and deletion for all applications, or customize based on status
                                // Remove the status check or modify it to include accepted applications if needed
                                // if ($application['status'] === 'pending'): 
                                ?>
                                    <button data-id="<?php echo $application['id']; ?>" class="edit-btn secondary-btn py-2 px-4 rounded-lg text-sm flex items-center">
                                        <i class="bx bx-edit mr-2"></i> Modifier
                                    </button>
                                    <button data-id="<?php echo $application['id']; ?>" class="delete-btn bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg text-sm flex items-center">
                                        <i class="bx bx-trash mr-2"></i> Supprimer
                                    </button>
                                <?php //endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- View Application Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Détails de ma candidature</h2>
                <button class="close-modal text-gray-500 hover:text-gray-700">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-2">Offre</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p id="view-job-title" class="font-bold"></p>
                    <p id="view-job-company" class="text-gray-600"></p>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-2">Compétences</h3>
                <div id="view-competences" class="bg-gray-50 p-4 rounded-md"></div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-2">Présentation</h3>
                <div id="view-presentation" class="bg-gray-50 p-4 rounded-md"></div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-2">Motivation</h3>
                <div id="view-motivation" class="bg-gray-50 p-4 rounded-md"></div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium mb-2">Pourquoi vous ?</h3>
                <div id="view-pourquoi" class="bg-gray-50 p-4 rounded-md"></div>
            </div>
            
            <div class="flex justify-end">
                <button class="close-modal secondary-btn py-2 px-4 rounded-lg">Fermer</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Application Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Modifier ma candidature</h2>
                <button class="close-modal text-gray-500 hover:text-gray-700">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <form id="edit-form" action="../controllers/updateUserApplication.php" method="post">
                <input type="hidden" id="edit-id" name="id">
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-2">Offre</h3>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p id="edit-job-title" class="font-bold"></p>
                        <p id="edit-job-company" class="text-gray-600"></p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="edit-competences" class="form-label">Compétences (séparées par des virgules)</label>
                    <input type="text" id="edit-competences" name="competences" class="form-input" required>
                    <p class="error-text" id="edit-competences-error">Veuillez entrer au moins une compétence</p>
                </div>
                
                <div class="mb-6">
                    <label for="edit-presentation" class="form-label">Présentation</label>
                    <textarea id="edit-presentation" name="presentation" class="form-textarea" required></textarea>
                    <p class="error-text" id="edit-presentation-error">Veuillez vous présenter (minimum 5 mots)</p>
                </div>
                
                <div class="mb-6">
                    <label for="edit-motivation" class="form-label">Motivation</label>
                    <textarea id="edit-motivation" name="motivation" class="form-textarea" required></textarea>
                    <p class="error-text" id="edit-motivation-error">Veuillez expliquer votre motivation (minimum 5 mots)</p>
                </div>
                
                <div class="mb-6">
                    <label for="edit-pourquoi" class="form-label">Pourquoi vous ?</label>
                    <textarea id="edit-pourquoi" name="pourquoi_lui" class="form-textarea" required></textarea>
                    <p class="error-text" id="edit-pourquoi-error">Veuillez expliquer pourquoi l'entreprise devrait vous choisir (minimum 5 mots)</p>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" class="close-modal secondary-btn py-2 px-4 rounded-lg">Annuler</button>
                    <button type="submit" class="primary-btn py-2 px-4 rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Confirmer la suppression</h2>
                <button class="close-modal text-gray-500 hover:text-gray-700">
                    <i class="bx bx-x text-2xl"></i>
                </button>
            </div>
            
            <p class="mb-6">Êtes-vous sûr de vouloir supprimer cette candidature ? Cette action est irréversible.</p>
            
            <form id="delete-form" action="../controllers/deleteUserApplication.php" method="post">
                <input type="hidden" id="delete-id" name="id">
                
                <div class="flex justify-end gap-2">
                    <button type="button" class="close-modal secondary-btn py-2 px-4 rounded-lg">Annuler</button>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-gray-100 py-12 mt-12">
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all applications data
            const applications = <?php echo json_encode($applications); ?>;
            
            // Modal elements
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            // View application details
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const applicationId = this.getAttribute('data-id');
                    // Find application by matching the ID as either string or number
                    const application = applications.find(app => app.id == applicationId);
                    
                    if (application) {
                        try {
                            // Parse competences from JSON
                            let competencesText = '';
                            if (application.competences) {
                                const competences = typeof application.competences === 'string' ? 
                                    JSON.parse(application.competences) : application.competences;
                                competencesText = competences.langages || '';
                            }
                            
                            // Fill in the view modal
                            document.getElementById('view-job-title').textContent = application.titre;
                            document.getElementById('view-job-company').textContent = application.entreprise;
                            document.getElementById('view-competences').textContent = competencesText;
                            document.getElementById('view-presentation').textContent = application.presentation;
                            document.getElementById('view-motivation').textContent = application.motivation;
                            document.getElementById('view-pourquoi').textContent = application.pourquoi_lui;
                            
                            // Show the modal
                            viewModal.style.display = 'block';
                        } catch (error) {
                            console.error("Error processing application data:", error);
                            alert("Une erreur s'est produite lors du chargement des détails de la candidature.");
                        }
                    } else {
                        console.error("Application not found for ID:", applicationId);
                    }
                });
            });
            
            // Edit application details
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const applicationId = this.getAttribute('data-id');
                    // Find application by matching the ID as either string or number
                    const application = applications.find(app => app.id == applicationId);
                    
                    if (application) {
                        try {
                            // Parse competences from JSON
                            let competencesText = '';
                            if (application.competences) {
                                const competences = typeof application.competences === 'string' ? 
                                    JSON.parse(application.competences) : application.competences;
                                competencesText = competences.langages || '';
                            }
                            
                            // Fill in the edit form
                            document.getElementById('edit-id').value = application.id;
                            document.getElementById('edit-job-title').textContent = application.titre;
                            document.getElementById('edit-job-company').textContent = application.entreprise;
                            document.getElementById('edit-competences').value = competencesText;
                            document.getElementById('edit-presentation').value = application.presentation || '';
                            document.getElementById('edit-motivation').value = application.motivation || '';
                            document.getElementById('edit-pourquoi').value = application.pourquoi_lui || '';
                            
                            // Hide all error messages
                            document.querySelectorAll('.error-text').forEach(el => {
                                el.style.display = 'none';
                            });
                            
                            // Show the modal
                            editModal.style.display = 'block';
                        } catch (error) {
                            console.error("Error processing application data for edit:", error);
                            alert("Une erreur s'est produite lors du chargement des données de la candidature.");
                        }
                    } else {
                        console.error("Application not found for edit with ID:", applicationId);
                    }
                });
            });
            
            // Delete application
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const applicationId = this.getAttribute('data-id');
                    document.getElementById('delete-id').value = applicationId;
                    deleteModal.style.display = 'block';
                });
            });
            
            // Close modals
            document.querySelectorAll('.close-modal').forEach(button => {
                button.addEventListener('click', function() {
                    viewModal.style.display = 'none';
                    editModal.style.display = 'none';
                    deleteModal.style.display = 'none';
                });
            });
            
            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === viewModal) viewModal.style.display = 'none';
                if (event.target === editModal) editModal.style.display = 'none';
                if (event.target === deleteModal) deleteModal.style.display = 'none';
            });
            
            // Form validation
            const editForm = document.getElementById('edit-form');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Validate competences
                    const competences = document.getElementById('edit-competences');
                    const competencesError = document.getElementById('edit-competences-error');
                    if (competences.value.trim().length < 3) {
                        competencesError.style.display = 'block';
                        competences.classList.add('error');
                        isValid = false;
                    } else {
                        competencesError.style.display = 'none';
                        competences.classList.remove('error');
                    }
                    
                    // Validate presentation
                    const presentation = document.getElementById('edit-presentation');
                    const presentationError = document.getElementById('edit-presentation-error');
                    const presentationWords = presentation.value.trim().split(/\s+/).filter(w => w.length > 0);
                    if (presentationWords.length < 5) {
                        presentationError.style.display = 'block';
                        presentationError.textContent = 'Votre présentation doit contenir au moins 5 mots';
                        presentation.classList.add('error');
                        isValid = false;
                    } else if (presentationWords.length > 255) {
                        presentationError.style.display = 'block';
                        presentationError.textContent = 'Votre présentation ne doit pas dépasser 255 mots';
                        presentation.classList.add('error');
                        isValid = false;
                    } else {
                        presentationError.style.display = 'none';
                        presentation.classList.remove('error');
                    }
                    
                    // Validate motivation
                    const motivation = document.getElementById('edit-motivation');
                    const motivationError = document.getElementById('edit-motivation-error');
                    const motivationWords = motivation.value.trim().split(/\s+/).filter(w => w.length > 0);
                    if (motivationWords.length < 5) {
                        motivationError.style.display = 'block';
                        motivationError.textContent = 'Votre motivation doit contenir au moins 5 mots';
                        motivation.classList.add('error');
                        isValid = false;
                    } else if (motivationWords.length > 255) {
                        motivationError.style.display = 'block';
                        motivationError.textContent = 'Votre motivation ne doit pas dépasser 255 mots';
                        motivation.classList.add('error');
                        isValid = false;
                    } else {
                        motivationError.style.display = 'none';
                        motivation.classList.remove('error');
                    }
                    
                    // Validate pourquoi
                    const pourquoi = document.getElementById('edit-pourquoi');
                    const pourquoiError = document.getElementById('edit-pourquoi-error');
                    const pourquoiWords = pourquoi.value.trim().split(/\s+/).filter(w => w.length > 0);
                    if (pourquoiWords.length < 5) {
                        pourquoiError.style.display = 'block';
                        pourquoiError.textContent = 'Ce champ doit contenir au moins 5 mots';
                        pourquoi.classList.add('error');
                        isValid = false;
                    } else if (pourquoiWords.length > 255) {
                        pourquoiError.style.display = 'block';
                        pourquoiError.textContent = 'Ce champ ne doit pas dépasser 255 mots';
                        pourquoi.classList.add('error');
                        isValid = false;
                    } else {
                        pourquoiError.style.display = 'none';
                        pourquoi.classList.remove('error');
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                    }
                });
                
                // Real-time validation
                document.getElementById('edit-competences').addEventListener('input', function() {
                    const error = document.getElementById('edit-competences-error');
                    if (this.value.trim().length < 3) {
                        error.style.display = 'block';
                        this.classList.add('error');
                    } else {
                        error.style.display = 'none';
                        this.classList.remove('error');
                    }
                });
                
                document.getElementById('edit-presentation').addEventListener('input', function() {
                    const error = document.getElementById('edit-presentation-error');
                    const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
                    if (words.length < 5) {
                        error.style.display = 'block';
                        error.textContent = 'Votre présentation doit contenir au moins 5 mots';
                        this.classList.add('error');
                    } else if (words.length > 255) {
                        error.style.display = 'block';
                        error.textContent = 'Votre présentation ne doit pas dépasser 255 mots';
                        this.classList.add('error');
                    } else {
                        error.style.display = 'none';
                        this.classList.remove('error');
                    }
                });
                
                document.getElementById('edit-motivation').addEventListener('input', function() {
                    const error = document.getElementById('edit-motivation-error');
                    const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
                    if (words.length < 5) {
                        error.style.display = 'block';
                        error.textContent = 'Votre motivation doit contenir au moins 5 mots';
                        this.classList.add('error');
                    } else if (words.length > 255) {
                        error.style.display = 'block';
                        error.textContent = 'Votre motivation ne doit pas dépasser 255 mots';
                        this.classList.add('error');
                    } else {
                        error.style.display = 'none';
                        this.classList.remove('error');
                    }
                });
                
                document.getElementById('edit-pourquoi').addEventListener('input', function() {
                    const error = document.getElementById('edit-pourquoi-error');
                    const words = this.value.trim().split(/\s+/).filter(w => w.length > 0);
                    if (words.length < 5) {
                        error.style.display = 'block';
                        error.textContent = 'Ce champ doit contenir au moins 5 mots';
                        this.classList.add('error');
                    } else if (words.length > 255) {
                        error.style.display = 'block';
                        error.textContent = 'Ce champ ne doit pas dépasser 255 mots';
                        this.classList.add('error');
                    } else {
                        error.style.display = 'none';
                        this.classList.remove('error');
                    }
                });
            }
        });
    </script>
</body>
</html>