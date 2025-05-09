<?php
// Include database configuration
require_once '../model/config.php';

/**
 * Get all job applications (entretiens) from the database
 * 
 * @param string $filter Optional filter by status (pending, accepted, rejected)
 * @return array Array of entretiens with user and offer information
 */
function getAllEntretiens($filter = '') {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create a PDO connection
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare the query to get all entretiens with user and offer details
        $query = "SELECT e.*, u.nom, u.prenom, u.email, u.adresse, o.titre, o.entreprise, o.emplacement, o.type
                 FROM entretiens e
                 JOIN users u ON e.id_user = u.id
                 JOIN offres o ON e.id_offre = o.id";
                 
        // Add filter by status if specified
        if ($filter) {
            $query .= " WHERE e.status = :status";
        }
        
        $query .= " ORDER BY e.id DESC";
        
        $stmt = $pdo->prepare($query);
        
        // Bind status parameter if filter is specified
        if ($filter) {
            $stmt->bindParam(':status', $filter, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        
        // Fetch all entretiens
        $entretiens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $entretiens;
    } catch (PDOException $e) {
        // Log error or handle exception
        error_log("Database error in getAllEntretiens: " . $e->getMessage());
        return [];
    }
}

// Get filter from request
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Get all entretiens based on filter
$entretiens = getAllEntretiens($statusFilter);

// Get counts for tabs
function getEntretiensCount($status) {
    global $servername, $username, $password, $dbname;
    
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $query = "SELECT COUNT(*) as count FROM entretiens WHERE status = :status";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Database error in getEntretiensCount: " . $e->getMessage());
        return 0;
    }
}

$pendingCount = getEntretiensCount('pending');
$acceptedCount = getEntretiensCount('accepted');
$rejectedCount = getEntretiensCount('rejected');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CityPulse - Gestion des Candidatures</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <!-- Add jsPDF and html2canvas for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.1/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .user-avatar {
            width: 40px;
            height: 40px;
            background-color: #f7fafc;
            color: #6944ff;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 100px;
            font-size: 12px;
        }
        .status-badge.pending {
            color: #d97706;
            background-color: #fef3c7;
        }
        .status-badge.accepted {
            color: #059669;
            background-color: #d1fae5;
        }
        .status-badge.rejected {
            color: #dc2626;
            background-color: #fee2e2;
        }
        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .status-dot.pending {
            background-color: #d97706;
        }
        .status-dot.accepted {
            background-color: #059669;
        }
        .status-dot.rejected {
            background-color: #dc2626;
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
        .accept-btn {
            background-color: #059669;
        }
        .reject-btn {
            background-color: #dc2626;
        }
        .view-btn {
            background-color: #3b82f6;
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
        
        .tab-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .tab-btn.active {
            background-color: #6944ff;
            color: white;
        }
        
        .tab-btn:hover:not(.active) {
            background-color: #f7fafc;
        }
        
        .tab-count {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 50px;
            padding: 2px 8px;
            font-size: 0.75rem;
            margin-left: 6px;
        }
        /* Additional styles for rating system */
        .star-rating i {
            margin-right: 3px;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating i.active {
            color: #fbbf24;
        }
        .rating-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            background-color: white;
        }
        .rating-stars {
            color: #fbbf24;
            font-size: 14px;
        }
        .rating-meta {
            color: #9ca3af;
            font-size: 12px;
        }
        .rating-badge {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }
        .rating-badge.hire {
            background-color: #d1fae5;
            color: #059669;
        }
        .rating-badge.consider {
            background-color: #fef3c7;
            color: #d97706;
        }
        .rating-badge.reject {
            background-color: #fee2e2;
            color: #dc2626;
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
            <div class="nav-item">
                <a href="offres.php" class="flex items-center w-full">
                    <i class="fas fa-briefcase mr-3"></i> Offres
                </a>
            </div>
            <div class="nav-item active">
                <a href="entretiens.php" class="flex items-center w-full text-white">
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
                        <input type="text" id="searchInput" placeholder="Rechercher une candidature..." class="outline-none w-full">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="offres.php" class="filter-btn">
                        <i class="fas fa-briefcase text-gray-500"></i> Voir les Offres
                    </a>
                    <a href="../controllers/stats_entretiens.php" class="filter-btn">
                        <i class="fas fa-chart-pie text-blue-500"></i> Statistiques
                    </a>
                    <button id="exportToPdf" class="filter-btn">
                        <i class="fas fa-file-pdf text-red-500"></i> Exporter PDF
                    </button>
                    <div class="filter-dropdown" id="sortDropdownWrapper">
                        <div id="sortDropdownBtn">
                            <span>Trier par:</span> <span id="currentSort" class="text-gray-500">Date</span> <i class="fas fa-chevron-down ml-2 text-gray-400 text-xs"></i>
                        </div>
                        <div id="sortDropdown" class="absolute bg-white shadow-md rounded-md py-2 mt-1 z-10 hidden w-48">
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="status">Statut</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="name-asc">Nom (A-Z)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="name-desc">Nom (Z-A)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="job-asc">Poste (A-Z)</div>
                            <div class="sort-option px-4 py-2 hover:bg-gray-100 cursor-pointer" data-sort="job-desc">Poste (Z-A)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="flex gap-3 mb-6">
                <a href="entretiens.php" class="tab-btn <?php echo $statusFilter === '' ? 'active' : ''; ?>">
                    Toutes <span class="tab-count"><?php echo $pendingCount + $acceptedCount + $rejectedCount; ?></span>
                </a>
                <a href="entretiens.php?status=pending" class="tab-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                    En attente <span class="tab-count"><?php echo $pendingCount; ?></span>
                </a>
                <a href="entretiens.php?status=accepted" class="tab-btn <?php echo $statusFilter === 'accepted' ? 'active' : ''; ?>">
                    Acceptées <span class="tab-count"><?php echo $acceptedCount; ?></span>
                </a>
                <a href="entretiens.php?status=rejected" class="tab-btn <?php echo $statusFilter === 'rejected' ? 'active' : ''; ?>">
                    Rejetées <span class="tab-count"><?php echo $rejectedCount; ?></span>
                </a>
            </div>

            <table class="w-full">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" class="form-checkbox"></th>
                        <th>ID</th>
                        <th>Candidat</th>
                        <th>Offre</th>
                        <th>Compétences</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entretiens)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">Aucune candidature trouvée</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($entretiens as $entretien): ?>
                        <tr class="application-row">
                            <td><input type="checkbox" class="form-checkbox"></td>
                            <td><?php echo htmlspecialchars($entretien['id']); ?></td>
                            <td class="flex items-center gap-3">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($entretien['prenom'], 0, 1) . substr($entretien['nom'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-medium"><?php echo htmlspecialchars($entretien['prenom'] . ' ' . $entretien['nom']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($entretien['email']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium"><?php echo htmlspecialchars($entretien['titre']); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo htmlspecialchars($entretien['entreprise']); ?> | 
                                    <?php echo htmlspecialchars($entretien['emplacement']); ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                // Parse competences JSON
                                $competences = json_decode($entretien['competences'], true);
                                $competencesText = isset($competences['langages']) ? $competences['langages'] : '';
                                echo htmlspecialchars($competencesText);
                                ?>
                            </td>
                            <td>
                                <?php 
                                $status = isset($entretien['status']) ? $entretien['status'] : 'pending';
                                $statusText = '';
                                
                                switch ($status) {
                                    case 'accepted':
                                        $statusText = 'Accepté';
                                        $statusClass = 'accepted';
                                        break;
                                    case 'rejected':
                                        $statusText = 'Rejeté';
                                        $statusClass = 'rejected';
                                        break;
                                    default:
                                        $statusText = 'En attente';
                                        $statusClass = 'pending';
                                        break;
                                }
                                ?>
                                <div class="status-badge <?php echo $statusClass; ?>">
                                    <div class="status-dot <?php echo $statusClass; ?>"></div>
                                    <span><?php echo $statusText; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="action-btn view-btn view-details" data-id="<?php echo $entretien['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($status === 'pending'): ?>
                                    <button class="action-btn accept-btn accept-application" data-id="<?php echo $entretien['id']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="action-btn reject-btn reject-application" data-id="<?php echo $entretien['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php elseif ($status === 'accepted'): ?>
                                    <button class="action-btn view-btn edit-application" data-id="<?php echo $entretien['id']; ?>" style="background-color: #6944ff;">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="delete-btn delete-application" data-id="<?php echo $entretien['id']; ?>">
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
                <button class="page-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Application Details Modal -->
    <div id="applicationModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Détails de la Candidature</h2>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Tab Navigation -->
            <div class="flex border-b mb-6">
                <button class="modal-tab-btn px-4 py-2 focus:outline-none border-b-2 border-purple-600 text-purple-600" data-tab="details">
                    Informations
                </button>
                <button class="modal-tab-btn px-4 py-2 focus:outline-none border-b-2 border-transparent hover:text-purple-600" data-tab="rating">
                    Évaluation
                </button>
            </div>
            
            <!-- Details Tab Content -->
            <div id="details-tab" class="tab-content">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Informations du Candidat</h3>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Nom complet</div>
                                <div id="candidate-name" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Email</div>
                                <div id="candidate-email" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Adresse</div>
                                <div id="candidate-address" class="font-medium"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Informations de l'Offre</h3>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Poste</div>
                                <div id="job-title" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Entreprise</div>
                                <div id="job-company" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Localisation</div>
                                <div id="job-location" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Type</div>
                                <div id="job-type" class="font-medium"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-3">Compétences</h3>
                    <div id="candidate-skills" class="bg-gray-50 p-4 rounded-md"></div>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-3">Présentation</h3>
                    <div id="candidate-presentation" class="bg-gray-50 p-4 rounded-md"></div>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-3">Motivation</h3>
                    <div id="candidate-motivation" class="bg-gray-50 p-4 rounded-md"></div>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-3">Pourquoi nous devrions le choisir</h3>
                    <div id="candidate-why-him" class="bg-gray-50 p-4 rounded-md"></div>
                </div>
                
                <div id="modal-actions" class="flex justify-end gap-3 mt-6">
                    <button id="modal-reject-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Rejeter
                    </button>
                    <button id="modal-accept-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Accepter
                    </button>
                    <button id="cancelBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Fermer
                    </button>
                </div>
            </div>
            
            <!-- Rating Tab Content in Application Modal -->
            <div id="rating-tab" class="hidden">
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-medium">Évaluation du candidat</h3>
                        <div class="text-gray-500 text-sm" id="existing-rating-info"></div>
                    </div>
                    
                    <!-- Rating Form -->
                    <form id="rating-form" class="bg-gray-50 p-4 rounded-md">
                        <input type="hidden" id="rating-entretien-id" name="entretien_id">
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Technical Skills Rating -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Compétences techniques</label>
                                <div class="flex items-center">
                                    <div class="star-rating" data-field="technical_skills">
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="1"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="2"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="3"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="4"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="technical_skills" id="technical_skills" value="">
                                    <span class="ml-2 text-sm text-gray-600" id="technical_skills_text"></span>
                                </div>
                            </div>
                            
                            <!-- Communication Skills Rating -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Communication</label>
                                <div class="flex items-center">
                                    <div class="star-rating" data-field="communication">
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="1"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="2"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="3"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="4"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="communication" id="communication" value="">
                                    <span class="ml-2 text-sm text-gray-600" id="communication_text"></span>
                                </div>
                            </div>
                            
                            <!-- Experience Rating -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Expérience</label>
                                <div class="flex items-center">
                                    <div class="star-rating" data-field="experience">
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="1"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="2"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="3"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="4"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="experience" id="experience" value="">
                                    <span class="ml-2 text-sm text-gray-600" id="experience_text"></span>
                                </div>
                            </div>
                            
                            <!-- Cultural Fit Rating -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Adéquation culturelle</label>
                                <div class="flex items-center">
                                    <div class="star-rating" data-field="cultural_fit">
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="1"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="2"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="3"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="4"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="cultural_fit" id="cultural_fit" value="">
                                    <span class="ml-2 text-sm text-gray-600" id="cultural_fit_text"></span>
                                </div>
                            </div>
                            
                            <!-- Overall Rating -->
                            <div class="col-span-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Évaluation globale</label>
                                <div class="flex items-center">
                                    <div class="star-rating" data-field="overall_rating">
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="1"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="2"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="3"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="4"></i>
                                        <i class="fas fa-star text-gray-300 cursor-pointer hover:text-yellow-400" data-rating="5"></i>
                                    </div>
                                    <input type="hidden" name="overall_rating" id="overall_rating" value="">
                                    <span class="ml-2 text-sm text-gray-600" id="overall_rating_text"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Strengths -->
                            <div>
                                <label for="strengths" class="block text-gray-700 text-sm font-bold mb-2">Points forts</label>
                                <textarea id="strengths" name="strengths" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Points forts du candidat..."></textarea>
                            </div>
                            
                            <!-- Weaknesses -->
                            <div>
                                <label for="weaknesses" class="block text-gray-700 text-sm font-bold mb-2">Points à améliorer</label>
                                <textarea id="weaknesses" name="weaknesses" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Points à améliorer..."></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="general_feedback" class="block text-gray-700 text-sm font-bold mb-2">Commentaire général</label>
                            <textarea id="general_feedback" name="general_feedback" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Commentaire général sur le candidat..."></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="interview_notes" class="block text-gray-700 text-sm font-bold mb-2">Notes d'entretien</label>
                            <textarea id="interview_notes" name="interview_notes" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Notes prises pendant l'entretien..."></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Recommandation</label>
                            <div class="flex items-center space-x-4">
                                <div>
                                    <input type="radio" id="hire" name="recommendation" value="Hire" class="mr-1">
                                    <label for="hire" class="text-sm">Embaucher</label>
                                </div>
                                <div>
                                    <input type="radio" id="consider" name="recommendation" value="Consider" class="mr-1">
                                    <label for="consider" class="text-sm">À considérer</label>
                                </div>
                                <div>
                                    <input type="radio" id="reject" name="recommendation" value="Reject" class="mr-1">
                                    <label for="reject" class="text-sm">Rejeter</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                Enregistrer l'évaluation
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- All Ratings Section -->
                <div id="all-ratings-section" class="mt-8 hidden">
                    <h3 class="text-lg font-medium mb-3">Toutes les évaluations</h3>
                    <div id="rating-statistics" class="bg-gray-50 p-4 rounded-md mb-4">
                        <div class="grid grid-cols-5 gap-4 mb-2">
                            <div>
                                <div class="text-sm text-gray-500">Technique</div>
                                <div class="font-bold" id="avg-technical">-</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Communication</div>
                                <div class="font-bold" id="avg-communication">-</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Expérience</div>
                                <div class="font-bold" id="avg-experience">-</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Adéquation</div>
                                <div class="font-bold" id="avg-cultural-fit">-</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Global</div>
                                <div class="font-bold" id="avg-overall">-</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Embaucher</div>
                                <div class="font-bold text-green-600" id="hire-count">-</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">À considérer</div>
                                <div class="font-bold text-yellow-600" id="consider-count">-</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Rejeter</div>
                                <div class="font-bold text-red-600" id="reject-count">-</div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="all-ratings-list" class="space-y-3">
                        <!-- Ratings will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Application Modal -->
    <div id="editApplicationModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Modifier la Candidature</h2>
                <button id="closeEditModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editApplicationForm" novalidate>
                <input type="hidden" id="edit-id" name="id">
                
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-medium mb-3">Informations du Candidat</h3>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Nom complet</div>
                                <div id="edit-candidate-name" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Email</div>
                                <div id="edit-candidate-email" class="font-medium"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium mb-3">Informations de l'Offre</h3>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Poste</div>
                                <div id="edit-job-title" class="font-medium"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">Entreprise</div>
                                <div id="edit-job-company" class="font-medium"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="edit-competences" class="block text-gray-700 text-sm font-bold mb-2">Compétences (séparées par des virgules) *</label>
                    <input type="text" id="edit-competences" name="competences" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <p id="edit-competences-error" class="error-message hidden">Les compétences sont requises</p>
                </div>
                
                <div class="mb-6">
                    <label for="edit-presentation" class="block text-gray-700 text-sm font-bold mb-2">Présentation *</label>
                    <textarea id="edit-presentation" name="presentation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32" required></textarea>
                    <p id="edit-presentation-error" class="error-message hidden">La présentation doit contenir entre 5 et 255 mots</p>
                </div>
                
                <div class="mb-6">
                    <label for="edit-motivation" class="block text-gray-700 text-sm font-bold mb-2">Motivation *</label>
                    <textarea id="edit-motivation" name="motivation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32" required></textarea>
                    <p id="edit-motivation-error" class="error-message hidden">La motivation doit contenir entre 5 et 255 mots</p>
                </div>
                
                <div class="mb-6">
                    <label for="edit-pourquoi-lui" class="block text-gray-700 text-sm font-bold mb-2">Pourquoi devrait-on choisir ce candidat ? *</label>
                    <textarea id="edit-pourquoi-lui" name="pourquoi_lui" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32" required></textarea>
                    <p id="edit-pourquoi-lui-error" class="error-message hidden">Ce champ doit contenir entre 5 et 255 mots</p>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for application management -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Store all applications data
            const applications = <?php echo json_encode($entretiens); ?>;
            
            // Initialize PDF export functionality
            const { jsPDF } = window.jspdf;
            
            // Add PDF export functionality
            $('#exportToPdf').on('click', function() {
                // Create loading indicator
                const loadingIndicator = $('<div>').addClass('fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-50 z-50')
                    .html(`
                        <div class="bg-white p-4 rounded shadow-lg flex items-center">
                            <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full text-purple-600 mr-3" role="status"></div>
                            <p>Génération du PDF en cours...</p>
                        </div>
                    `);
                
                $('body').append(loadingIndicator);
                
                // Give browser time to render the loading indicator
                setTimeout(() => {
                    try {
                        // Create a new jsPDF instance
                        const doc = new jsPDF('landscape', 'mm', 'a4');
                        
                        // Add title
                        doc.setFontSize(18);
                        doc.setTextColor(0, 0, 128);
                        doc.text('Liste des Candidatures - CityPulse', 14, 20);
                        
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
                        
                        // Define table data
                        let tableData = [];
                        const statusTranslate = {
                            'pending': 'En attente',
                            'accepted': 'Accepté',
                            'rejected': 'Rejeté'
                        };
                        
                        // Process each application for the PDF table
                        applications.forEach(app => {
                            // Parse competences JSON
                            let competences = '';
                            try {
                                const compObj = JSON.parse(app.competences);
                                competences = compObj.langages || '';
                            } catch (e) {
                                competences = app.competences;
                            }
                            
                            // Get status text
                            const status = app.status || 'pending';
                            const statusText = statusTranslate[status] || 'En attente';
                            
                            // Add row to table data
                            tableData.push([
                                app.id,
                                `${app.prenom} ${app.nom}`,
                                app.email,
                                app.titre,
                                app.entreprise,
                                competences,
                                statusText
                            ]);
                        });
                        
                        // Define table headers
                        const headers = ['ID', 'Candidat', 'Email', 'Poste', 'Entreprise', 'Compétences', 'Statut'];
                        
                        // Add table to PDF
                        doc.autoTable({
                            head: [headers],
                            body: tableData,
                            startY: 30,
                            theme: 'grid',
                            styles: {
                                fontSize: 8,
                                cellPadding: 2
                            },
                            headStyles: {
                                fillColor: [105, 68, 255],
                                textColor: [255, 255, 255],
                                fontSize: 9,
                                fontStyle: 'bold'
                            },
                            alternateRowStyles: {
                                fillColor: [240, 240, 255]
                            },
                            columnStyles: {
                                0: {cellWidth: 15},  // ID
                                1: {cellWidth: 30},  // Candidat
                                2: {cellWidth: 40},  // Email
                                3: {cellWidth: 30},  // Poste
                                4: {cellWidth: 30},  // Entreprise
                                5: {cellWidth: 40},  // Compétences
                                6: {cellWidth: 20}   // Statut
                            }
                        });
                        
                        // Add footer with page numbers
                        const pageCount = doc.internal.getNumberOfPages();
                        for (let i = 1; i <= pageCount; i++) {
                            doc.setPage(i);
                            doc.setFontSize(8);
                            doc.setTextColor(100, 100, 100);
                            doc.text(`Page ${i} sur ${pageCount} - CityPulse © ${new Date().getFullYear()}`, doc.internal.pageSize.getWidth() / 2, doc.internal.pageSize.getHeight() - 10, { align: 'center' });
                        }
                        
                        // Use the save dialog instead of direct download
                        try {
                            const pdfBlob = doc.output('blob');
                            const url = URL.createObjectURL(pdfBlob);
                            
                            // Create and trigger a download link with the save dialog
                            const downloadLink = document.createElement('a');
                            downloadLink.href = url;
                            downloadLink.download = `Candidatures_CityPulse_${new Date().toISOString().slice(0, 10)}.pdf`;
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
                        loadingIndicator.remove();
                        
                    } catch (error) {
                        console.error('Error generating PDF:', error);
                        showNotification('Erreur lors de la génération du PDF', 'error');
                        
                        // Remove loading indicator
                        loadingIndicator.remove();
                    }
                }, 500);
            });
            
            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                
                $('.application-row').each(function() {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.indexOf(searchText) > -1);
                });
            });
            
            // View application details
            $('.view-details').on('click', function() {
                const id = $(this).data('id');
                const application = applications.find(app => app.id == id);
                
                if (application) {
                    // Parse JSON data
                    const competences = JSON.parse(application.competences);
                    const status = application.status || 'pending';
                    
                    // Populate modal with application details
                    $('#candidate-name').text(`${application.prenom} ${application.nom}`);
                    $('#candidate-email').text(application.email);
                    $('#candidate-address').text(application.adresse);
                    $('#job-title').text(application.titre);
                    $('#job-company').text(application.entreprise);
                    $('#job-location').text(application.emplacement);
                    $('#job-type').text(application.type);
                    $('#candidate-skills').text(competences.langages);
                    $('#candidate-presentation').text(application.presentation);
                    $('#candidate-motivation').text(application.motivation);
                    $('#candidate-why-him').text(application.pourquoi_lui);
                    
                    // Set button data attributes
                    $('#modal-accept-btn').data('id', id);
                    $('#modal-reject-btn').data('id', id);
                    
                    // Hide action buttons if not pending
                    if (status !== 'pending') {
                        $('#modal-actions').hide();
                    } else {
                        $('#modal-actions').show();
                    }
                    
                    // Set the application ID for ratings
                    $('#rating-entretien-id').val(application.id);
                    
                    // Show the details tab by default
                    $('.modal-tab-btn').removeClass('border-purple-600 text-purple-600').addClass('border-transparent');
                    $('.modal-tab-btn[data-tab="details"]').addClass('border-purple-600 text-purple-600');
                    $('.tab-content').hide();
                    $('#details-tab').show();
                    
                    // Load ratings for this application
                    loadRatings(application.id);
                    
                    // Show the modal
                    $('#applicationModal').css('display', 'block');
                }
            });
            
            // Tab switching in the application modal
            $('.modal-tab-btn').on('click', function() {
                const tab = $(this).data('tab');
                
                // Update tab button styling
                $('.modal-tab-btn').removeClass('border-purple-600 text-purple-600').addClass('border-transparent');
                $(this).addClass('border-purple-600 text-purple-600');
                
                // Show the selected tab content
                $('.tab-content').hide();
                $('#' + tab + '-tab').show();
            });
            
            // Initialize star ratings
            $('.star-rating i').on('mouseover', function() {
                const rating = $(this).data('rating');
                const field = $(this).closest('.star-rating').data('field');
                
                // Highlight stars on hover
                $(this).prevAll('i').addBack().addClass('text-yellow-400').removeClass('text-gray-300');
                $(this).nextAll('i').removeClass('text-yellow-400').addClass('text-gray-300');
                
                // Show rating text
                updateRatingText(field, rating);
            });
            
            $('.star-rating i').on('mouseout', function() {
                const field = $(this).closest('.star-rating').data('field');
                const currentRating = $(`#${field}`).val();
                
                // Reset stars to current rating
                if (currentRating) {
                    setStars(field, currentRating);
                } else {
                    // Reset to no rating
                    $(this).closest('.star-rating').find('i').removeClass('text-yellow-400 active').addClass('text-gray-300');
                    $(`#${field}_text`).text('');
                }
            });
            
            $('.star-rating i').on('click', function() {
                const rating = $(this).data('rating');
                const field = $(this).closest('.star-rating').data('field');
                
                // Set the rating value
                $(`#${field}`).val(rating);
                
                // Set the stars permanently
                setStars(field, rating);
            });
            
            // Function to set stars based on rating
            function setStars(field, rating) {
                const stars = $(`.star-rating[data-field="${field}"] i`);
                stars.removeClass('active text-yellow-400').addClass('text-gray-300');
                
                // Activate stars up to the rating
                stars.each(function(index) {
                    if (index < rating) {
                        $(this).addClass('active text-yellow-400').removeClass('text-gray-300');
                    }
                });
                
                // Update rating text
                updateRatingText(field, rating);
            }
            
            // Function to update rating text
            function updateRatingText(field, rating) {
                const textMap = {
                    1: 'Insuffisant',
                    2: 'Moyen',
                    3: 'Satisfaisant',
                    4: 'Bon',
                    5: 'Excellent'
                };
                
                $(`#${field}_text`).text(textMap[rating] || '');
            }
            
            // Submit rating form
            $('#rating-form').on('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = $(this).serialize();
                
                // Submit rating via AJAX
                $.ajax({
                    url: '../controllers/saveRating.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            
                            // Reload ratings
                            loadRatings($('#rating-entretien-id').val());
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showNotification('Une erreur est survenue lors de l\'enregistrement de l\'évaluation', 'error');
                        console.error(xhr.responseText);
                    }
                });
            });
            
            // Function to load ratings for an application
            function loadRatings(entretienId) {
                $.ajax({
                    url: '../controllers/getRatings.php',
                    type: 'GET',
                    data: { entretien_id: entretienId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Check if there are any ratings
                            if (response.ratings && response.ratings.length > 0) {
                                // Show the all ratings section
                                $('#all-ratings-section').removeClass('hidden');
                                
                                // Find the current user's rating (assuming user_id = 1 for now)
                                const userRating = response.ratings.find(r => r.rater_id == 1);
                                
                                // If user has already rated, populate the form
                                if (userRating) {
                                    // Set rating timestamp info
                                    const ratedDate = new Date(userRating.created_at);
                                    $('#existing-rating-info').text(`Votre évaluation précédente (${ratedDate.toLocaleDateString()})`);
                                    
                                    // Set form values
                                    $('input[name="technical_skills"]').val(userRating.technical_skills);
                                    $('input[name="communication"]').val(userRating.communication);
                                    $('input[name="experience"]').val(userRating.experience);
                                    $('input[name="cultural_fit"]').val(userRating.cultural_fit);
                                    $('input[name="overall_rating"]').val(userRating.overall_rating);
                                    $('#strengths').val(userRating.strengths);
                                    $('#weaknesses').val(userRating.weaknesses);
                                    $('#general_feedback').val(userRating.general_feedback);
                                    $('#interview_notes').val(userRating.interview_notes);
                                    
                                    // Set recommendation radio button
                                    if (userRating.recommendation) {
                                        $(`input[name="recommendation"][value="${userRating.recommendation}"]`).prop('checked', true);
                                    }
                                    
                                    // Set star ratings
                                    setStars('technical_skills', userRating.technical_skills);
                                    setStars('communication', userRating.communication);
                                    setStars('experience', userRating.experience);
                                    setStars('cultural_fit', userRating.cultural_fit);
                                    setStars('overall_rating', userRating.overall_rating);
                                } else {
                                    // Clear the form if no user rating
                                    $('#existing-rating-info').text('');
                                    $('#rating-form')[0].reset();
                                    $('.star-rating i').removeClass('active text-yellow-400').addClass('text-gray-300');
                                    $('.rating-text').text('');
                                }
                                
                                // Update statistics
                                if (response.statistics) {
                                    const stats = response.statistics;
                                    $('#avg-technical').text(parseFloat(stats.avg_technical).toFixed(1) || '-');
                                    $('#avg-communication').text(parseFloat(stats.avg_communication).toFixed(1) || '-');
                                    $('#avg-experience').text(parseFloat(stats.avg_experience).toFixed(1) || '-');
                                    $('#avg-cultural-fit').text(parseFloat(stats.avg_cultural_fit).toFixed(1) || '-');
                                    $('#avg-overall').text(parseFloat(stats.avg_overall).toFixed(1) || '-');
                                    $('#hire-count').text(stats.hire_count || '0');
                                    $('#consider-count').text(stats.consider_count || '0');
                                    $('#reject-count').text(stats.reject_count || '0');
                                }
                                
                                // Render all ratings
                                renderRatingsList(response.ratings);
                            } else {
                                // Hide the all ratings section if no ratings
                                $('#all-ratings-section').addClass('hidden');
                                $('#existing-rating-info').text('Aucune évaluation existante');
                                
                                // Clear the form
                                $('#rating-form')[0].reset();
                                $('.star-rating i').removeClass('active text-yellow-400').addClass('text-gray-300');
                                $('.rating-text').text('');
                            }
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showNotification('Une erreur est survenue lors du chargement des évaluations', 'error');
                        console.error(xhr.responseText);
                    }
                });
            }
            
            // Function to render the ratings list
            function renderRatingsList(ratings) {
                const container = $('#all-ratings-list');
                container.empty();
                
                ratings.forEach(rating => {
                    const ratingDate = new Date(rating.created_at);
                    const raterName = rating.rater_nom && rating.rater_prenom ? 
                        `${rating.rater_prenom} ${rating.rater_nom}` : 'Évaluateur';
                    
                    let recommendationBadge = '';
                    if (rating.recommendation === 'Hire') {
                        recommendationBadge = '<span class="rating-badge hire">Embaucher</span>';
                    } else if (rating.recommendation === 'Consider') {
                        recommendationBadge = '<span class="rating-badge consider">À considérer</span>';
                    } else if (rating.recommendation === 'Reject') {
                        recommendationBadge = '<span class="rating-badge reject">Rejeter</span>';
                    }
                    
                    const ratingCard = `
                        <div class="rating-card">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-medium">${raterName}</h4>
                                    <div class="rating-meta">${ratingDate.toLocaleDateString()} ${ratingDate.toLocaleTimeString()}</div>
                                </div>
                                <div class="flex items-center">
                                    <div class="rating-stars mr-2">
                                        ${getStarsHTML(rating.overall_rating)}
                                    </div>
                                    ${recommendationBadge}
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-4 mb-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Technique:</span> ${rating.technical_skills}/5
                                </div>
                                <div>
                                    <span class="text-gray-600">Communication:</span> ${rating.communication}/5
                                </div>
                                <div>
                                    <span class="text-gray-600">Expérience:</span> ${rating.experience}/5
                                </div>
                                <div>
                                    <span class="text-gray-600">Adéquation:</span> ${rating.cultural_fit}/5
                                </div>
                            </div>
                            
                            ${rating.general_feedback ? `
                                <div class="mb-2">
                                    <div class="text-gray-600 text-sm font-medium">Commentaire général:</div>
                                    <p class="text-sm">${rating.general_feedback}</p>
                                </div>
                            ` : ''}
                            
                            ${rating.strengths || rating.weaknesses ? `
                                <div class="grid grid-cols-2 gap-4 mb-2">
                                    ${rating.strengths ? `
                                        <div>
                                            <div class="text-gray-600 text-sm font-medium">Points forts:</div>
                                            <p class="text-sm">${rating.strengths}</p>
                                        </div>
                                    ` : ''}
                                    
                                    ${rating.weaknesses ? `
                                        <div>
                                            <div class="text-gray-600 text-sm font-medium">Points à améliorer:</div>
                                            <p class="text-sm">${rating.weaknesses}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    container.append(ratingCard);
                });
            }
            
            // Helper function to generate HTML for star ratings
            function getStarsHTML(rating) {
                let stars = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= rating) {
                        stars += '<i class="fas fa-star"></i>';
                    } else {
                        stars += '<i class="far fa-star"></i>';
                    }
                }
                return stars;
            }
            
            // Close details modal
            $('#closeModal, #cancelBtn').on('click', function() {
                $('#applicationModal').css('display', 'none');
            });
            
            // Edit application functionality
            $('.edit-application').on('click', function() {
                const id = $(this).data('id');
                const application = applications.find(app => app.id == id);
                
                if (application) {
                    // Parse JSON data
                    const competences = JSON.parse(application.competences);
                    
                    // Set the hidden ID field
                    $('#edit-id').val(application.id);
                    
                    // Populate modal with application details
                    $('#edit-candidate-name').text(`${application.prenom} ${application.nom}`);
                    $('#edit-candidate-email').text(application.email);
                    $('#edit-job-title').text(application.titre);
                    $('#edit-job-company').text(application.entreprise);
                    
                    // Populate form fields
                    $('#edit-competences').val(competences.langages);
                    $('#edit-presentation').val(application.presentation);
                    $('#edit-motivation').val(application.motivation);
                    $('#edit-pourquoi-lui').val(application.pourquoi_lui);
                    
                    // Reset error messages
                    $('.error-message').addClass('hidden');
                    
                    // Show the edit modal
                    $('#editApplicationModal').css('display', 'block');
                }
            });
            
            // Close edit modal
            $('#closeEditModal, #cancelEditBtn').on('click', function() {
                $('#editApplicationModal').css('display', 'none');
            });
            
            // Form field validation
            function validateField(field, errorElement, min = 5, max = 255) {
                const value = field.val().trim();
                let isValid = true;
                let errorMessage = '';
                
                if (value === '') {
                    isValid = false;
                    errorMessage = 'Ce champ est requis';
                } else if (field.is('textarea')) {
                    // Count words for textarea fields
                    const wordCount = value.split(/\s+/).filter(word => word.length > 0).length;
                    
                    if (wordCount < min) {
                        isValid = false;
                        errorMessage = `Ce champ doit contenir au moins ${min} mots`;
                    } else if (wordCount > max) {
                        isValid = false;
                        errorMessage = `Ce champ ne doit pas dépasser ${max} mots`;
                    }
                }
                
                // Display error message if invalid
                if (!isValid) {
                    errorElement.text(errorMessage).removeClass('hidden');
                    field.addClass('border-red-500');
                } else {
                    errorElement.addClass('hidden');
                    field.removeClass('border-red-500');
                }
                
                return isValid;
            }
            
            // Validate on input
            $('#edit-competences').on('input', function() {
                validateField($(this), $('#edit-competences-error'), 1, 1000);
            });
            
            $('#edit-presentation').on('input', function() {
                validateField($(this), $('#edit-presentation-error'));
            });
            
            $('#edit-motivation').on('input', function() {
                validateField($(this), $('#edit-motivation-error'));
            });
            
            $('#edit-pourquoi-lui').on('input', function() {
                validateField($(this), $('#edit-pourquoi-lui-error'));
            });
            
            // Submit edit form with validation
            $('#editApplicationForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate all fields
                const competencesValid = validateField($('#edit-competences'), $('#edit-competences-error'), 1, 1000);
                const presentationValid = validateField($('#edit-presentation'), $('#edit-presentation-error'));
                const motivationValid = validateField($('#edit-motivation'), $('#edit-motivation-error'));
                const pourquoiLuiValid = validateField($('#edit-pourquoi-lui'), $('#edit-pourquoi-lui-error'));
                
                // If all fields are valid, submit the form via AJAX
                if (competencesValid && presentationValid && motivationValid && pourquoiLuiValid) {
                    const formData = $(this).serialize();
                    
                    $.ajax({
                        url: '../controllers/updateApplication.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Close modal and show success message
                                $('#editApplicationModal').css('display', 'none');
                                showNotification(response.message, 'success');
                                
                                // Reload the page after a delay
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                showNotification(response.message, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            showNotification('Une erreur est survenue lors de la communication avec le serveur', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
            
            // Accept application
            $('.accept-application, #modal-accept-btn').on('click', function() {
                const id = $(this).data('id');
                
                $.ajax({
                    url: '../controllers/acceptApplication.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            // Reload the page after a delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Une erreur est survenue lors de la communication avec le serveur', 'error');
                    }
                });
            });
            
            // Reject application
            $('.reject-application, #modal-reject-btn').on('click', function() {
                const id = $(this).data('id');
                
                $.ajax({
                    url: '../controllers/rejectApplication.php',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            // Reload the page after a delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Une erreur est survenue lors de la communication avec le serveur', 'error');
                    }
                });
            });
            
            // Delete application
            $('.delete-application').on('click', function() {
                const id = $(this).data('id');
                if (confirm('Êtes-vous sûr de vouloir supprimer cette candidature ?')) {
                    $.ajax({
                        url: '../controllers/deleteApplication.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                // Reload the page after a delay
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                showNotification(response.message, 'error');
                            }
                        },
                        error: function() {
                            showNotification('Une erreur est survenue lors de la communication avec le serveur', 'error');
                        }
                    });
                }
            });
            
            // Function to show notification
            function showNotification(message, type) {
                const notification = $('<div>').addClass('fixed top-5 right-5 p-4 rounded-md shadow-md z-50');
                
                if (type === 'success') {
                    notification.addClass('bg-green-100 text-green-800');
                    notification.html(`<div class="flex items-center"><i class="fas fa-check-circle mr-3"></i><p>${message}</p></div>`);
                } else {
                    notification.addClass('bg-red-100 text-red-800');
                    notification.html(`<div class="flex items-center"><i class="fas fa-exclamation-circle mr-3"></i><p>${message}</p></div>`);
                }
                
                $('body').append(notification);
                
                // Remove notification after 5 seconds
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
            
            // Close modals when clicking outside of them
            $(window).on('click', function(e) {
                if ($(e.target).is('#applicationModal')) {
                    $('#applicationModal').css('display', 'none');
                }
                if ($(e.target).is('#editApplicationModal')) {
                    $('#editApplicationModal').css('display', 'none');
                }
            });

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
                const rows = Array.from(tbody.querySelectorAll('tr.application-row'));
                
                if (rows.length <= 1) {
                    return; // Nothing to sort
                }
                
                // Sort based on selected option
                rows.sort((a, b) => {
                    switch(sortType) {
                        case 'status':
                            return compareStatus(
                                a.querySelector('.status-badge span').textContent, 
                                b.querySelector('.status-badge span').textContent
                            );
                        case 'name-asc':
                            return compareStrings(
                                a.cells[2].querySelector('.font-medium').textContent, 
                                b.cells[2].querySelector('.font-medium').textContent
                            );
                        case 'name-desc':
                            return compareStrings(
                                b.cells[2].querySelector('.font-medium').textContent, 
                                a.cells[2].querySelector('.font-medium').textContent
                            );
                        case 'job-asc':
                            return compareStrings(
                                a.cells[3].querySelector('.font-medium').textContent, 
                                b.cells[3].querySelector('.font-medium').textContent
                            );
                        case 'job-desc':
                            return compareStrings(
                                b.cells[3].querySelector('.font-medium').textContent, 
                                a.cells[3].querySelector('.font-medium').textContent
                            );
                        default: // default sort by date (id desc)
                            return parseInt(b.cells[1].textContent) - parseInt(a.cells[1].textContent);
                    }
                });
                
                // Remove existing rows
                rows.forEach(row => tbody.removeChild(row));
                
                // Add sorted rows
                rows.forEach(row => tbody.appendChild(row));
                
                // Show notification
                showNotification('Tableau trié par ' + currentSortText.textContent, 'success');
            }
            
            // Helper function to compare status (custom order: En attente, Accepté, Rejeté)
            function compareStatus(a, b) {
                const statusOrder = { 'En attente': 1, 'Accepté': 2, 'Rejeté': 3 };
                return statusOrder[a] - statusOrder[b];
            }
            
            // Helper function to compare strings
            function compareStrings(a, b) {
                return a.trim().localeCompare(b.trim(), 'fr', {sensitivity: 'base'});
            }
            
            // Enhanced search functionality with text highlighting
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
                
                // Remove existing highlights
                document.querySelectorAll('mark').forEach(mark => {
                    const parent = mark.parentNode;
                    parent.replaceChild(document.createTextNode(mark.textContent), mark);
                    parent.normalize();
                });
                
                const rows = document.querySelectorAll('.application-row');
                let matchFound = false;
                
                rows.forEach(row => {
                    const elements = [
                        row.cells[1], // ID
                        row.cells[2].querySelector('.font-medium'), // Candidate name
                        row.cells[2].querySelector('.text-xs'), // Email
                        row.cells[3].querySelector('.font-medium'), // Job title
                        row.cells[3].querySelector('.text-xs'), // Company & location
                        row.cells[4], // Skills
                        row.cells[5].querySelector('.status-badge span') // Status
                    ];
                    
                    let rowMatch = false;
                    
                    elements.forEach(element => {
                        if (element && element.textContent) {
                            const text = element.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                rowMatch = true;
                                matchFound = true;
                                
                                // Highlight matching text
                                const highlightedText = highlightText(element.textContent, searchTerm);
                                element.innerHTML = highlightedText;
                            }
                        }
                    });
                    
                    row.style.display = rowMatch ? '' : 'none';
                });
                
                // Show a message if no matches found
                const noResultsRow = document.getElementById('noResultsRow');
                if (!matchFound && searchTerm !== '') {
                    if (!noResultsRow) {
                        const tbody = document.querySelector('table tbody');
                        const newRow = document.createElement('tr');
                        newRow.id = 'noResultsRow';
                        newRow.innerHTML = `<td colspan="7" class="text-center py-4">Aucune candidature trouvée pour "${searchTerm}"</td>`;
                        tbody.appendChild(newRow);
                    } else {
                        noResultsRow.querySelector('td').textContent = `Aucune candidature trouvée pour "${searchTerm}"`;
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
        });
    </script>
</body>
</html>