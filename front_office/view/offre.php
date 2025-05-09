<?php
// Start session
session_start();

// Include the PHP functions to get job offer data
require_once '../model/getOffers.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$emplacement = isset($_GET['emplacement']) ? $_GET['emplacement'] : '';

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offersPerPage = 6; // Number of offers to display per page
$offset = ($page - 1) * $offersPerPage;

// Prepare filters array
$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($type)) {
    $filters['type'] = $type;
}
if (!empty($emplacement)) {
    $filters['emplacement'] = $emplacement;
}

// Get offers from database
$offers = getFrontOfficeOffers($filters, $offersPerPage, $offset);

// Count total offers for pagination
$totalOffers = countOffers($filters);
$totalPages = ceil($totalOffers / $offersPerPage);

// Get filter options
$locations = getLocations();
$jobTypes = getJobTypes();

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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Offres d'emploi</title>
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
        
        .job-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(104, 87, 232, 0.1), 0 10px 10px -5px rgba(104, 87, 232, 0.04);
        }
        
        .job-type-badge {
            background: var(--card-gradient);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .header-nav {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .search-bar {
            background: white;
            border-radius: 50px;
            box-shadow: var(--shadow);
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
        
        .filter-section {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .active-nav-link {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .pagination-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pagination-btn:hover {
            background-color: #f0f4ff;
        }
        
        .active-page {
            background: var(--gradient-bg);
            color: white;
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
                    <a href="offre.php" class="py-2 px-1 <?php echo basename($_SERVER['PHP_SELF']) === 'offre.php' || basename($_SERVER['PHP_SELF']) === 'offre-detail.php' ? 'active-nav-link' : 'text-gray-600 hover:text-gray-900'; ?>">Offres</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="mes-candidatures.php" class="py-2 px-1 <?php echo basename($_SERVER['PHP_SELF']) === 'mes-candidatures.php' ? 'active-nav-link' : 'text-gray-600 hover:text-gray-900'; ?>">Mes Candidatures</a>
                    <?php endif; ?>
                    <a href="#" class="py-2 px-1 text-gray-600 hover:text-gray-900">Transport</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="text-gray-600">Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur'); ?></span>
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
        <!-- Page Title -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Découvrez nos offres d'emploi</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">Trouvez l'opportunité idéale dans notre ville intelligente. <?php echo $totalOffers; ?> offres d'emploi disponibles dans divers secteurs.</p>
        </div>
        
        <!-- Search and Filter Section -->
        <div class="mb-12">
            <form action="offre.php" method="GET" class="mb-6">
                <div class="search-bar p-2 flex items-center mb-6">
                    <i class="bx bx-search text-gray-400 text-xl px-3"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher un poste, une entreprise..." class="w-full py-2 px-4 outline-none">
                    <button type="submit" class="primary-btn rounded-full py-2 px-6 ml-2">Rechercher</button>
                </div>
                
                <div class="filter-section p-4 flex flex-wrap justify-between items-center gap-4">
                    <div class="flex flex-wrap gap-4">
                        <div class="relative">
                            <select name="type" class="appearance-none bg-gray-100 rounded-lg py-2 px-4 pr-8 focus:outline-none" onchange="this.form.submit()">
                                <option value="">Type d'emploi</option>
                                <?php foreach ($jobTypes as $jobType): ?>
                                <option value="<?php echo htmlspecialchars($jobType); ?>" <?php echo $type === $jobType ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($jobType); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="bx bx-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                        </div>
                        
                        <div class="relative">
                            <select name="emplacement" class="appearance-none bg-gray-100 rounded-lg py-2 px-4 pr-8 focus:outline-none" onchange="this.form.submit()">
                                <option value="">Localisation</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location); ?>" <?php echo $emplacement === $location ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="bx bx-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                        </div>
                    </div>
                    
                    <div>
                        <button type="button" onclick="window.location.href='offre.php'" class="secondary-btn rounded-lg py-2 px-4 flex items-center">
                            <i class="bx bx-reset mr-2"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Job Listings Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php if (empty($offers)): ?>
                <div class="col-span-3 text-center py-10">
                    <h3 class="text-xl font-semibold mb-2">Aucune offre trouvée</h3>
                    <p class="text-gray-600">Veuillez ajuster vos filtres pour voir plus de résultats.</p>
                </div>
            <?php else: ?>
                <?php foreach ($offers as $offer): ?>
                    <!-- Job Card -->
                    <div class="job-card p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center">
                                <div class="company-logo mr-4">
                                    <?php echo generateLogoPlaceholder($offer['entreprise']); ?>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($offer['titre']); ?></h3>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($offer['entreprise']); ?></p>
                                </div>
                            </div>
                            <button class="text-gray-400 hover:text-primary-600">
                                <i class="bx bx-bookmark text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex items-center text-gray-500 mb-2">
                                <i class="bx bx-map text-lg mr-2"></i>
                                <span><?php echo htmlspecialchars($offer['emplacement']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-500">
                                <i class="bx bx-calendar text-lg mr-2"></i>
                                <span>Publié le <?php echo formatOfferDate($offer['date']); ?></span>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-4 line-clamp-3"><?php echo htmlspecialchars($offer['description']); ?></p>
                        
                        <div class="flex justify-between items-center">
                            <span class="job-type-badge py-1 px-3"><?php echo htmlspecialchars($offer['type']); ?></span>
                            <?php 
                            // Debug information for the offer ID
                            $offerID = $offer['id'];
                            echo '<!-- Debug: Offer ID = ' . $offerID . ', Type: ' . gettype($offerID) . ' -->';
                            ?>
                            <a href="offre-detail.php?id=<?php echo urlencode($offer['id']); ?>" 
                               class="primary-btn rounded-lg py-2 px-4" 
                               onclick="console.log('Details button clicked for ID: <?php echo addslashes($offer['id']); ?>'); return true;">
                                Voir détails
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center space-x-2 mb-10">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?><?php echo !empty($emplacement) ? '&emplacement=' . urlencode($emplacement) : ''; ?>" class="pagination-btn border border-gray-200">
                        <i class="bx bx-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                // Determine which page numbers to show
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<a href="?page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($type) ? '&type=' . urlencode($type) : '') . (!empty($emplacement) ? '&emplacement=' . urlencode($emplacement) : '') . '" class="pagination-btn border border-gray-200">1</a>';
                    
                    if ($startPage > 2) {
                        echo '<span class="mx-2">...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $page) ? 'active-page' : 'border border-gray-200';
                    echo '<a href="?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($type) ? '&type=' . urlencode($type) : '') . (!empty($emplacement) ? '&emplacement=' . urlencode($emplacement) : '') . '" class="pagination-btn ' . $activeClass . '">' . $i . '</a>';
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="mx-2">...</span>';
                    }
                    
                    echo '<a href="?page=' . $totalPages . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($type) ? '&type=' . urlencode($type) : '') . (!empty($emplacement) ? '&emplacement=' . urlencode($emplacement) : '') . '" class="pagination-btn border border-gray-200">' . $totalPages . '</a>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($type) ? '&type=' . urlencode($type) : ''; ?><?php echo !empty($emplacement) ? '&emplacement=' . urlencode($emplacement) : ''; ?>" class="pagination-btn border border-gray-200">
                        <i class="bx bx-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Newsletter -->
        <div class="gradient-bg rounded-2xl p-8 text-white text-center mb-12">
            <h2 class="text-2xl font-bold mb-4">Ne manquez aucune opportunité</h2>
            <p class="mb-6 max-w-xl mx-auto">Recevez les dernières offres correspondant à votre profil directement dans votre boîte mail</p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <input type="email" placeholder="Votre adresse email" class="rounded-full py-3 px-6 w-full sm:w-96 focus:outline-none text-gray-700">
                <button class="bg-white text-primary-600 rounded-full py-3 px-6 font-semibold hover:bg-gray-100 transition-colors whitespace-nowrap">S'abonner</button>
            </div>
        </div>
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
</body>
</html>