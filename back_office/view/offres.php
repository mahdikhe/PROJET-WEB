<?php
// Include the getAllOffers script
require_once '../model/getAllOffers.php';

// Get all offers from the database
$offers = getAllOffers();

// Define an array of icons to use for different job types
$icons = [
    'en ligne' => '<i class="fas fa-globe text-purple-500"></i>',
    'hybride' => '<i class="fas fa-building text-blue-500"></i>',
    'sur place' => '<i class="fas fa-map-marker-alt text-red-500"></i>',
    // Default icon if type doesn't match
    'default' => '<i class="fas fa-briefcase text-gray-500"></i>'
];

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
    
    // Format date as "12 Juin 2023"
    return $date->format('d') . ' ' . strftime('%B', $date->getTimestamp()) . ' ' . $date->format('Y');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Gestion des Offres</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <!-- Add flatpickr for date picking -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Add jsPDF and html2canvas for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.1/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
        }
        .sidebar {
            width: 190px;
            min-width: 190px;
            background-color: #fff;
            height: 100vh;
            position: fixed;
            border-right: 1px solid #edf2f7;
        }
        .main-content {
            margin-left: 190px;
            padding: 0px 20px;
        }
        .logo {
            display: flex;
            align-items: center;
            padding: 20px;
            font-weight: bold;
            font-size: 20px;
            color: #6944ff;
        }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #718096;
            cursor: pointer;
            transition: all 0.2s;
        }
        .nav-item:hover {
            background-color: #f7fafc;
        }
        .nav-item.active {
            background-color: #6944ff;
            color: white;
            border-radius: 8px;
            margin: 0 10px;
            padding: 12px 10px;
        }
        .section-title {
            font-size: 12px;
            color: #a0aec0;
            padding: 20px 20px 10px;
        }
        .search-bar {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 6px;
            padding: 8px 16px;
            margin: 10px 0;
            border: 1px solid #e2e8f0;
        }
        .filter-section {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .filter-dropdown {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 120px;
        }
        .filter-btn {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .add-btn {
            background: #6944ff;
            color: white;
            border-radius: 6px;
            padding: 8px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            text-align: left;
            padding: 15px;
            color: #718096;
            font-weight: 500;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #f7fafc;
            color: #4a5568;
            vertical-align: middle;
        }
        .company-img {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            background-color: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 12px;
            color: #38a169;
            background-color: #f0fff4;
        }
        .status-dot {
            width: 6px;
            height: 6px;
            background-color: #38a169;
            border-radius: 50%;
        }
        .action-btn {
            padding: 5px 12px;
            border-radius: 6px;
            background-color: #6944ff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }
        .delete-btn {
            color: #718096;
            background: none;
            border: none;
            cursor: pointer;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        .page-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
        }
        .page-btn.active {
            background-color: #ebf4ff;
            color: #6944ff;
            border-color: #6944ff;
        }
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        .header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Modal styles */
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
            width: 60%;
            max-width: 800px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <div class="w-8 h-8 rounded-md bg-purple-600 flex items-center justify-center text-white mr-2">
                    <i class="fas fa-city"></i>
                </div>
                CityPulse
            </div>

            <div class="nav-item">
                <i class="fas fa-th-large mr-3"></i> Tableau de bord
            </div>
            <div class="nav-item">
                <i class="fas fa-shopping-bag mr-3"></i> Commandes
            </div>
            <div class="nav-item">
                <i class="fas fa-users mr-3"></i> Clients
            </div>
            <div class="nav-item">
                <i class="fas fa-comment mr-3"></i> Messages
            </div>

            <div class="section-title">OUTILS</div>
            <div class="nav-item active">
                <a href="offres.php" class="flex items-center w-full text-white">
                    <i class="fas fa-briefcase mr-3"></i> Offres
                </a>
            </div>
            <div class="nav-item">
                <a href="entretiens.php" class="flex items-center w-full">
                    <i class="fas fa-file-alt mr-3"></i> Candidatures
                </a>
            </div>
            <div class="nav-item">
                <i class="fas fa-plug mr-3"></i> Intégrations
            </div>
            <div class="nav-item">
                <i class="fas fa-chart-bar mr-3"></i> Analyses
            </div>
            <div class="nav-item">
                <i class="fas fa-receipt mr-3"></i> Factures
            </div>
            <div class="nav-item">
                <i class="fas fa-percentage mr-3"></i> Remises
            </div>

            <div class="section-title">PARAMÈTRES</div>
            <div class="nav-item">
                <i class="fas fa-cog mr-3"></i> Paramètres
            </div>
            <div class="nav-item">
                <i class="fas fa-shield-alt mr-3"></i> Sécurité
            </div>
            <div class="nav-item">
                <i class="fas fa-question-circle mr-3"></i> Aide
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header-wrapper">
                <div class="search-bar w-1/3">
                    <i class="fas fa-search mr-2 text-gray-400"></i>
                    <input type="text" placeholder="Rechercher..." class="outline-none w-full">
                    <div class="px-2 text-gray-400">⌘ K</div>
                </div>
                <div class="user-section">
                    <button class="bg-white p-2 rounded-full">
                        <i class="far fa-clipboard text-gray-500"></i>
                    </button>
                    <button class="bg-white p-2 rounded-full">
                        <i class="far fa-bell text-gray-500"></i>
                    </button>
                    <div class="avatar relative">
                        <div class="w-full h-full bg-red-400 text-white flex items-center justify-center">
                            EN
                        </div>
                        <div class="absolute right-0 bottom-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="text-sm font-medium">Estiaq Noor <i class="fas fa-chevron-down ml-1 text-gray-400 text-xs"></i></div>
                </div>
            </div>

            <div class="flex justify-between items-center mb-4">
                <div class="flex gap-2">
                    <button class="p-2 border border-gray-200 rounded-md bg-white">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button class="p-2 border border-gray-200 rounded-md bg-white">
                        <i class="fas fa-th"></i>
                    </button>
                    <div class="search-bar">
                        <i class="fas fa-search mr-2 text-gray-400"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher une offre..." class="outline-none w-full">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="entretiens.php" class="filter-btn">
                        <i class="fas fa-file-alt text-gray-500"></i> Voir les Candidatures
                    </a>
                    <button id="exportToPdf" class="filter-btn">
                        <i class="fas fa-file-pdf text-red-500"></i> Exporter PDF
                    </button>
                    <a href="../controllers/stats_offres.php" class="filter-btn">
                        <i class="fas fa-chart-bar text-blue-500"></i> Statistiques
                    </a>
                    <div class="filter-dropdown" id="sortDropdownWrapper">
                        <div id="sortDropdownBtn">
                            Trier par: <span id="currentSort">Par défaut</span> <i class="fas fa-chevron-down ml-2 text-gray-400 text-xs"></i>
                        </div>
                        <div id="sortDropdown" class="absolute bg-white shadow-md rounded-md py-2 mt-1 z-10 hidden w-48">
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="default">Par défaut</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="date-desc">Date (Plus récent)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="date-asc">Date (Plus ancien)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="title-asc">Titre (A-Z)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="title-desc">Titre (Z-A)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="type-asc">Type (A-Z)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="type-desc">Type (Z-A)</div>
                        </div>
                    </div>
                    <button id="addOfferBtn" class="add-btn">
                        <i class="fas fa-plus"></i> Ajouter une Offre
                    </button>
                </div>
            </div>
            <table class="w-full">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" class="form-checkbox"></th>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Entreprise</th>
                        <th>Emplacement</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($offers)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">Aucune offre trouvée</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td><input type="checkbox" class="form-checkbox"></td>
                            <td><?php echo htmlspecialchars($offer['id']); ?></td>
                            <td class="flex items-center gap-3">
                                <div class="company-img">
                                    <?php 
                                    // Display icon based on job type, or default if not found
                                    echo isset($icons[$offer['type']]) ? $icons[$offer['type']] : $icons['default'];
                                    ?>
                                </div>
                                <div><?php echo htmlspecialchars($offer['titre']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($offer['entreprise']); ?></td>
                            <td><?php echo htmlspecialchars($offer['emplacement']); ?></td>
                            <td class="truncate"><?php echo htmlspecialchars($offer['description']); ?></td>
                            <td><?php echo htmlspecialchars($offer['date']); ?></td>
                            <td>
                                <div class="status-badge">
                                    <div class="status-dot"></div>
                                    <span><?php echo htmlspecialchars($offer['type']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="action-btn edit-btn" data-id="<?php echo htmlspecialchars($offer['id']); ?>">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button class="delete-btn" data-id="<?php echo htmlspecialchars($offer['id']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination">
                <button class="page-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn">4</button>
                <button class="page-btn">5</button>
                <button class="page-btn">6</button>
                <div class="page-btn bg-transparent border-none">...</div>
                <button class="page-btn">24</button>
                <button class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal for adding a new offer -->
    <div id="addOfferModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Ajouter une Offre</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addOfferForm" method="post" action="../model/addOffer.php">
                <div class="form-group">
                    <label for="titre" class="block text-sm font-medium text-gray-700 mb-1">Titre de l'offre</label>
                    <input type="text" id="titre" name="titre" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                    <div id="titreError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label for="entreprise" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise</label>
                    <input type="text" id="entreprise" name="entreprise" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                    <div id="entrepriseError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label for="emplacement" class="block text-sm font-medium text-gray-700 mb-1">Emplacement</label>
                    <select id="emplacement" name="emplacement" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                        <option value="" disabled selected>Sélectionnez une ville</option>
                        <!-- Tunisian cities will be populated by JavaScript -->
                    </select>
                    <div id="emplacementError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required></textarea>
                    <div id="descriptionError" class="error-message hidden"></div>
                    <div class="flex justify-between">
                        <div class="text-sm text-gray-500 mt-1">Entre 5 et 255 mots</div>
                        <div id="descriptionWordCount" class="text-sm text-gray-500 mt-1">0 mots</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="text" id="date" name="date" class="datepicker w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                    <div id="dateError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="en ligne" class="mr-2" required>
                            <span>En ligne</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="hybride" class="mr-2">
                            <span>Hybride</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="sur place" class="mr-2">
                            <span>Sur place</span>
                        </label>
                    </div>
                    <div id="typeError" class="error-message hidden"></div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-2 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 opacity-50 cursor-not-allowed" disabled>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for editing an offer -->
    <div id="editOfferModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Modifier une Offre</h2>
                <button id="closeEditModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editOfferForm" method="post" action="../model/modifyOffer.php">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_titre" class="block text-sm font-medium text-gray-700 mb-1">Titre de l'offre</label>
                    <input type="text" id="edit_titre" name="titre" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                    <div id="edit_titreError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_entreprise" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise</label>
                    <input type="text" id="edit_entreprise" name="entreprise" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                    <div id="edit_entrepriseError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_emplacement" class="block text-sm font-medium text-gray-700 mb-1">Emplacement</label>
                    <select id="edit_emplacement" name="emplacement" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                        <option value="" disabled>Sélectionnez une ville</option>
                        <!-- Tunisian cities will be populated by JavaScript -->
                    </select>
                    <div id="edit_emplacementError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="edit_description" name="description" rows="4" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required></textarea>
                    <div id="edit_descriptionError" class="error-message hidden"></div>
                    <div class="flex justify-between">
                        <div class="text-sm text-gray-500 mt-1">Entre 5 et 255 mots</div>
                        <div id="editDescriptionWordCount" class="text-sm text-gray-500 mt-1">0 mots</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="text" id="edit_date" name="date" class="datepicker w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-600" required>
                    <div id="edit_dateError" class="error-message hidden"></div>
                </div>
                
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="edit_type" value="en ligne" class="mr-2" required>
                            <span>En ligne</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="edit_type" value="hybride" class="mr-2">
                            <span>Hybride</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="edit_type" value="sur place" class="mr-2">
                            <span>Sur place</span>
                        </label>
                    </div>
                    <div id="edit_typeError" class="error-message hidden"></div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md mr-2 hover:bg-gray-300">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 opacity-50 cursor-not-allowed" disabled>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white p-5 rounded-md shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full text-purple-600" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-gray-700">Chargement...</p>
            </div>
        </div>
    </div>

    <!-- Include the JS for form validation -->
    <script src="../controllers/offresadd.js"></script>
    <!-- Include the JS for delete functionality -->
    <script src="../controllers/deleteOffer.js"></script>
    <!-- Include the JS for edit functionality -->
    <script src="../controllers/offresModify.js"></script>
    
    <!-- Success and error message handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize PDF export functionality
            const { jsPDF } = window.jspdf;
            
            // Add search functionality with text highlighting
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
                const tableRows = document.querySelectorAll('table tbody tr');
                
                // Remove any existing highlights
                document.querySelectorAll('mark').forEach(mark => {
                    const parent = mark.parentNode;
                    parent.replaceChild(document.createTextNode(mark.textContent), mark);
                    parent.normalize();
                });
                
                if (searchTerm === '') {
                    // If search is empty, show all rows
                    tableRows.forEach(row => {
                        row.style.display = '';
                    });
                    return;
                }
                
                let matchFound = false;
                
                // Search through each row
                tableRows.forEach(row => {
                    const cellsToSearch = [
                        row.cells[1], // ID
                        row.cells[2], // Title
                        row.cells[3], // Company
                        row.cells[4], // Location
                        row.cells[5], // Description
                        row.cells[6], // Date
                        row.cells[7]  // Type
                    ];
                    
                    let rowMatch = false;
                    
                    cellsToSearch.forEach(cell => {
                        if (cell) {
                            // For most cells, search directly in textContent
                            let cellText;
                            let elementToHighlight;
                            
                            if (cell.cellIndex === 2) { // Title cell (has nested div)
                                elementToHighlight = cell.querySelector('div:last-child');
                                cellText = elementToHighlight ? elementToHighlight.textContent : cell.textContent;
                            } else if (cell.cellIndex === 7) { // Type cell (has nested span)
                                elementToHighlight = cell.querySelector('.status-badge span');
                                cellText = elementToHighlight ? elementToHighlight.textContent : cell.textContent;
                            } else {
                                elementToHighlight = cell;
                                cellText = cell.textContent;
                            }
                            
                            if (cellText.toLowerCase().includes(searchTerm)) {
                                rowMatch = true;
                                matchFound = true;
                                
                                // Highlight the matching text
                                if (elementToHighlight) {
                                    const highlightedText = highlightText(elementToHighlight.textContent, searchTerm);
                                    if (elementToHighlight.innerHTML !== highlightedText) {
                                        elementToHighlight.innerHTML = highlightedText;
                                    }
                                }
                            }
                        }
                    });
                    
                    // Show/hide row based on match
                    row.style.display = rowMatch ? '' : 'none';
                });
                
                // Show a message if no matches found
                const noResultsRow = document.getElementById('noResultsRow');
                if (!matchFound) {
                    if (!noResultsRow) {
                        const tbody = document.querySelector('table tbody');
                        const newRow = document.createElement('tr');
                        newRow.id = 'noResultsRow';
                        newRow.innerHTML = `<td colspan="9" class="text-center py-4">Aucune offre trouvée pour "${searchTerm}"</td>`;
                        tbody.appendChild(newRow);
                    } else {
                        noResultsRow.querySelector('td').textContent = `Aucune offre trouvée pour "${searchTerm}"`;
                        noResultsRow.style.display = '';
                    }
                } else if (noResultsRow) {
                    noResultsRow.style.display = 'none';
                }
            });
            
            // Function to highlight matching text
            function highlightText(text, searchTerm) {
                if (!searchTerm) return text;
                
                const regex = new RegExp(`(${escapeRegExp(searchTerm)})`, 'gi');
                return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
            }
            
            // Helper function to escape special regex characters
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }
            
            // Toggle sort dropdown
            const sortDropdownBtn = document.getElementById('sortDropdownBtn');
            const sortDropdown = document.getElementById('sortDropdown');
            const currentSortText = document.getElementById('currentSort');
            
            sortDropdownBtn.addEventListener('click', function() {
                sortDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('#sortDropdownWrapper')) {
                    sortDropdown.classList.add('hidden');
                }
            });
            
            // Handle sort option selection
            const sortOptions = document.querySelectorAll('.sort-option');
            sortOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const sortType = this.getAttribute('data-sort');
                    const sortText = this.textContent;
                    
                    // Update dropdown text
                    currentSortText.textContent = sortText;
                    
                    // Sort the table
                    sortTable(sortType);
                    
                    // Hide dropdown
                    sortDropdown.classList.add('hidden');
                });
            });
            
            // Function to sort the table
            function sortTable(sortType) {
                const table = document.querySelector('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                if (rows.length <= 1) {
                    return; // Nothing to sort
                }
                
                // Sort based on selected option
                rows.sort((a, b) => {
                    switch(sortType) {
                        case 'date-desc':
                            return compareDates(b.cells[6].textContent, a.cells[6].textContent);
                        case 'date-asc':
                            return compareDates(a.cells[6].textContent, b.cells[6].textContent);
                        case 'title-asc':
                            return compareStrings(a.cells[2].textContent, b.cells[2].textContent);
                        case 'title-desc':
                            return compareStrings(b.cells[2].textContent, a.cells[2].textContent);
                        case 'type-asc':
                            return compareStrings(a.cells[7].querySelector('.status-badge span').textContent, 
                                                b.cells[7].querySelector('.status-badge span').textContent);
                        case 'type-desc':
                            return compareStrings(b.cells[7].querySelector('.status-badge span').textContent, 
                                                a.cells[7].querySelector('.status-badge span').textContent);
                        default: // default sort by ID (ascending)
                            return parseInt(a.cells[1].textContent) - parseInt(b.cells[1].textContent);
                    }
                });
                
                // Remove existing rows
                rows.forEach(row => tbody.removeChild(row));
                
                // Add sorted rows
                rows.forEach(row => tbody.appendChild(row));
                
                // Show notification
                showNotification('Tableau trié par ' + currentSortText.textContent, 'success');
            }
            
            // Helper function to compare dates
            function compareDates(a, b) {
                // Parse dates in format DD Month YYYY or direct date strings
                const dateA = new Date(a);
                const dateB = new Date(b);
                return dateA - dateB;
            }
            
            // Helper function to compare strings
            function compareStrings(a, b) {
                return a.trim().localeCompare(b.trim(), 'fr', {sensitivity: 'base'});
            }
            
            // Add PDF export functionality
            document.getElementById('exportToPdf').addEventListener('click', function() {
                // Create loading indicator
                const loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-50 z-50';
                loadingIndicator.innerHTML = `
                    <div class="bg-white p-4 rounded shadow-lg flex items-center">
                        <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full text-purple-600 mr-3" role="status"></div>
                        <p>Génération du PDF en cours...</p>
                    </div>
                `;
                document.body.appendChild(loadingIndicator);
                
                // Give browser time to render the loading indicator
                setTimeout(() => {
                    // Create a new jsPDF instance
                    const doc = new jsPDF('landscape', 'mm', 'a4');
                    
                    // Add title
                    doc.setFontSize(18);
                    doc.setTextColor(0, 0, 128);
                    doc.text('Liste des Offres d\'Emploi - CityPulse', 14, 20);
                    
                    // Add date
                    doc.setFontSize(10);
                    doc.setTextColor(100, 100, 100);
                    const today = new Date();
                    const dateStr = today.toLocaleDateString('fr-FR', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    doc.text(`Exporté le ${dateStr}`, 14, 25);
                    
                    // Get table data
                    const table = document.querySelector('table');
                    const rows = table.querySelectorAll('tbody tr');
                    
                    // Define table columns
                    const headers = ['ID', 'Titre', 'Entreprise', 'Emplacement', 'Date', 'Type'];
                    const tableData = [];
                    
                    // Extract data from table
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length >= 8) {
                            tableData.push([
                                cells[1].textContent.trim(),                  // ID
                                cells[2].textContent.trim(),                  // Titre
                                cells[3].textContent.trim(),                  // Entreprise
                                cells[4].textContent.trim(),                  // Emplacement
                                cells[6].textContent.trim(),                  // Date
                                cells[7].querySelector('.status-badge span').textContent.trim()  // Type
                            ]);
                        }
                    });
                    
                    // Add table to PDF
                    doc.autoTable({
                        head: [headers],
                        body: tableData,
                        startY: 30,
                        theme: 'grid',
                        styles: {
                            fontSize: 9,
                            cellPadding: 3
                        },
                        headStyles: {
                            fillColor: [104, 87, 232],
                            textColor: [255, 255, 255],
                            fontSize: 10,
                            fontStyle: 'bold'
                        },
                        alternateRowStyles: {
                            fillColor: [240, 240, 255]
                        }
                    });
                    
                    // Add footer
                    const pageCount = doc.internal.getNumberOfPages();
                    for (let i = 1; i <= pageCount; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8);
                        doc.setTextColor(100, 100, 100);
                        doc.text(`Page ${i} of ${pageCount} - CityPulse © ${new Date().getFullYear()}`, doc.internal.pageSize.getWidth() / 2, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
                    }
                    
                    // Use the save dialog instead of direct download
                    try {
                        const pdfBlob = doc.output('blob');
                        const url = URL.createObjectURL(pdfBlob);
                        
                        // Create and trigger a download link with the save dialog
                        const downloadLink = document.createElement('a');
                        downloadLink.href = url;
                        downloadLink.download = `Offres_CityPulse_${new Date().toISOString().slice(0, 10)}.pdf`;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                        URL.revokeObjectURL(url);
                        
                        // Show success notification
                        showNotification('PDF généré avec succès !', 'success');
                    } catch (error) {
                        console.error('Error saving PDF:', error);
                        showNotification('Erreur lors de la génération du PDF', 'error');
                    }
                    
                    // Remove loading indicator
                    document.body.removeChild(loadingIndicator);
                    
                    // Show success notification
                    showNotification('PDF exporté avec succès !', 'success');
                }, 500);
            });
            
            // Parse URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Check for success message
            if (urlParams.has('success')) {
                showNotification('Offre ajoutée avec succès!', 'success');
                // Reload the page after a delay to show updated offers
                setTimeout(() => {
                    window.location.href = 'offres.php';
                }, 2500);
            }
            
            // Check for error message
            if (urlParams.has('error')) {
                const errorType = urlParams.get('error');
                const errorMsg = urlParams.get('message');
                
                if (errorType === 'db') {
                    showNotification('Erreur de base de données: ' + errorMsg, 'error');
                } else if (errorType === 'validation') {
                    showNotification('Erreur de validation: ' + errorMsg, 'error');
                }
            }
            
            // Function to show notification
            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `fixed top-5 right-5 p-4 rounded-md shadow-md ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                notification.innerHTML = `
                    <div class="flex items-center">
                        <div class="mr-3">
                            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        </div>
                        <p>${message}</p>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Remove notification after some time
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        });
    </script>
</body>
</html>