<?php
require_once(__DIR__ . '/../../../config/Database.php');
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : 0;

// Debugging: Log the session data
error_log("Session data in projects.php: " . print_r($_SESSION, true));
error_log("User logged in: " . ($isLoggedIn ? 'Yes' : 'No'));

// Fonction pour construire les URLs de tri
function buildSortUrl($field, $order) {
    $params = [
        'search' => $_GET['search'] ?? '',
        'category' => $_GET['category'] ?? '',
        'sort' => $field,
        'order' => $order
    ];
    return '?' . http_build_query(array_filter($params));
}

// Récupération et validation des paramètres de tri
$sortField = $_GET['sort'] ?? 'created_at';
$sortOrder = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

// Champs et ordres de tri autorisés
$allowedSortFields = ['projectName', 'created_at', 'teamSize', 'supporters_count', 'id', 'project_duration', 'startDate'];
$allowedOrders = ['ASC', 'DESC'];

if (!in_array($sortField, $allowedSortFields)) {
    $sortField = 'created_at';
}
if (!in_array($sortOrder, $allowedOrders)) {
    $sortOrder = 'DESC';
}

// Requête SQL avec tri
$projects = [];

// Récupérer la connexion à la base de données
$db = Database::getInstance()->getConnection(); // Connexion DB

$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM project_supporters WHERE project_id = p.id) AS supporters_count,
          EXISTS(SELECT 1 FROM project_supporters ps WHERE ps.project_id = p.id AND ps.user_id = :userId) AS is_supported,
          DATEDIFF(COALESCE(p.endDate, CURRENT_DATE), p.startDate) AS project_duration
          FROM projects p ";

// Add ORDER BY clause
if ($sortField == 'teamSize') {
    $query .= "ORDER BY CAST(p.teamSize AS SIGNED) " . $sortOrder;
} else if ($sortField == 'supporters_count') {
    $query .= "ORDER BY supporters_count " . $sortOrder;
} else if ($sortField == 'project_duration') {
    $query .= "ORDER BY project_duration " . $sortOrder;
} else {
    $query .= "ORDER BY p." . $sortField . " " . $sortOrder;
}

try {
    $stmt = $db->prepare($query);  // Utilisation de la variable $db pour la préparation de la requête
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching projects: " . $e->getMessage();
    $projects = [];
}

// Filtrage
$searchTerm = isset($_GET['search']) ? strtolower($_GET['search']) : '';
$categoryFilter = $_GET['category'] ?? '';

$filteredProjects = array_filter($projects, function($project) use ($searchTerm, $categoryFilter) {
    $matchesSearch = empty($searchTerm) || 
                    strpos(strtolower($project['projectName']), $searchTerm) !== false || 
                    strpos(strtolower($project['projectDescription']), $searchTerm) !== false;
    
    $matchesCategory = empty($categoryFilter) || 
                      $project['projectCategory'] == $categoryFilter;
    
    return $matchesSearch && $matchesCategory;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Projects - CityPulse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/css/style1.css" />
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
            --border: #dee2e6;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-title {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .project-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .project-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .project-content {
            padding: 1.5rem;
        }
        
        .project-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        
        .project-description {
            color: #6c757d;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-clamp: 3;
            box-orient: vertical;
        }
        
        .project-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .project-actions {
            display: flex;
            gap: 1rem;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .button-primary {
            background: var(--primary);
            color: white;
        }
        
        .button-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        
        .button-ticket {
            background: #28a745;
            color: white;
        }
        
        .button-ticket:hover {
            background: #218838;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .search-bar {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 1.5rem;
            cursor: pointer;
        }
        
        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .filter-button {
            text-decoration: none;
            background: white;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .filter-button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .no-projects {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .projects-grid {
                grid-template-columns: 1fr;
            }
            
            .project-actions {
                flex-direction: column;
            }
        }

        /*support*/
        .button-support {
    background: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
    cursor: pointer;
    transition: all 0.3s ease; /* Add transition for smooth effects */
}

/* Only target the heart icon */
.button-support .fa-heart {
    color: var(--primary);
    transition: color 0.3s ease; /* Smooth color transition */
}

/* When supported, only change the heart */
.button-support.supported .fa-heart {
    color: #ff4d4d; /* Red heart */
    transform: scale(1.1); /* Optional: slight grow effect */
}

.support-count {
    margin-left: 5px;
}

/* Bulk Actions */
.bulk-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.select-all {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.button-danger {
    background: #dc3545;
    color: white;
    border: none;
}

.button-danger:hover {
    background: #c82333;
}

/* Selection checkbox */
.project-selector {
    position: absolute;
    top: 15px;
    left: 15px;
    width: 20px;
    height: 20px;
    z-index: 2;
    cursor: pointer;
}

.project-card {
    position: relative; /* Add this for absolute positioning of checkbox */
}

/*tri*/

.sort-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.sort-button {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    text-decoration: none;
    background: white;
    border: 1px solid var(--border);
    color: var(--dark);
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.sort-button:hover {
    background: #e9ecef;
}

.sort-button.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.sort-button i {
    font-size: 0.8em;
}

.back-button {
    position: absolute;
    
    left: 1rem; 
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: var(--primary);
    font-weight: 600;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    background: var(--light);
    transition: all 0.3s ease;
}


        .back-button:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
        }
    </style>


 <?php include_once '../../../../user/views/includes/header.php'; ?>
<body>
    <div class="container">
        <div class="page-header">
        <a href="../project.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Projects
            </a>
            <h1 class="page-title">All Projects</h1>
            <p>Browse through all active community projects</p>
        </div>
     
        
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="search-input" placeholder="Search projects..." 
                   value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <!-- tri-->
        <div class="sort-options">
    <span>Trier par :</span>
    <a href="<?= buildSortUrl('projectName', 'ASC') ?>" class="sort-button <?= ($sortField == 'projectName' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        Nom (A-Z) <i class="fas fa-sort-alpha-down"></i>
    </a>
    <a href="<?= buildSortUrl('projectName', 'DESC') ?>" class="sort-button <?= ($sortField == 'projectName' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        Nom (Z-A) <i class="fas fa-sort-alpha-down-alt"></i>
    </a>
    <a href="<?= buildSortUrl('created_at', 'DESC') ?>" class="sort-button <?= ($sortField == 'created_at' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        Plus récent <i class="fas fa-arrow-down"></i>
    </a>
    <a href="<?= buildSortUrl('created_at', 'ASC') ?>" class="sort-button <?= ($sortField == 'created_at' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        Plus ancien <i class="fas fa-arrow-up"></i>
    </a>
    <a href="<?= buildSortUrl('teamSize', 'DESC') ?>" class="sort-button <?= ($sortField == 'teamSize' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        Membres (↓) <i class="fas fa-users"></i>
    </a>
    <a href="<?= buildSortUrl('teamSize', 'ASC') ?>" class="sort-button <?= ($sortField == 'teamSize' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        Membres (↑) <i class="fas fa-users"></i>
    </a>
    <a href="<?= buildSortUrl('supporters_count', 'DESC') ?>" class="sort-button <?= ($sortField == 'supporters_count' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        Soutiens (↓) <i class="fas fa-heart"></i>
    </a>
    <a href="<?= buildSortUrl('supporters_count', 'ASC') ?>" class="sort-button <?= ($sortField == 'supporters_count' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        Soutiens (↑) <i class="fas fa-heart"></i>
    </a>
    <a href="<?= buildSortUrl('id', 'ASC') ?>" class="sort-button <?= ($sortField == 'id' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        ID (↑) <i class="fas fa-sort-numeric-down"></i>
    </a>
    <a href="<?= buildSortUrl('id', 'DESC') ?>" class="sort-button <?= ($sortField == 'id' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        ID (↓) <i class="fas fa-sort-numeric-down-alt"></i>
    </a>
    <a href="<?= buildSortUrl('project_duration', 'ASC') ?>" class="sort-button <?= ($sortField == 'project_duration' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        Durée (↑) <i class="fas fa-clock"></i>
    </a>
    <a href="<?= buildSortUrl('project_duration', 'DESC') ?>" class="sort-button <?= ($sortField == 'project_duration' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        Durée (↓) <i class="fas fa-clock"></i>
    </a>
    <a href="<?= buildSortUrl('startDate', 'ASC') ?>" class="sort-button <?= ($sortField == 'startDate' && $sortOrder == 'ASC') ? 'active' : '' ?>">
        Date début (↑) <i class="fas fa-calendar"></i>
    </a>
    <a href="<?= buildSortUrl('startDate', 'DESC') ?>" class="sort-button <?= ($sortField == 'startDate' && $sortOrder == 'DESC') ? 'active' : '' ?>">
        Date début (↓) <i class="fas fa-calendar"></i>
    </a>
</div>

        <!-- Add this below your search bar -->
<div class="bulk-actions">
    <label class="select-all">
        <input type="checkbox" id="selectAll"> Select All
    </label>
    <button id="deleteSelected" class="button button-danger" disabled>
        <i class="fas fa-trash"></i> Delete Selected
    </button>
    <a href="projects-map.php" class="button button-primary">
        <i class="fas fa-map-marked-alt"></i> View Projects on Map
    </a>
</div>
        
        <div class="filter-options">
            <a href="?category=" class="filter-button <?= empty($categoryFilter) ? 'active' : '' ?>">All</a>
            <a href="?category=urban-development" class="filter-button <?= $categoryFilter == 'urban-development' ? 'active' : '' ?>">Urban Development</a>
            <a href="?category=environment" class="filter-button <?= $categoryFilter == 'environment' ? 'active' : '' ?>">Environment</a>
            <a href="?category=transportation" class="filter-button <?= $categoryFilter == 'transportation' ? 'active' : '' ?>">Transportation</a>
            <a href="?category=community" class="filter-button <?= $categoryFilter == 'community' ? 'active' : '' ?>">Community</a>
        </div>
        
        <div class="projects-grid">
            
            <?php if (!empty($filteredProjects)): ?>
                <?php foreach ($filteredProjects as $project): ?>
                    <div class="project-card">
                    <input type="checkbox" class="project-selector" data-project-id="<?= $project['id'] ?>">
                        <?php if (!empty($project['projectImage'])): ?>
                            <img src="<?= htmlspecialchars($project['projectImage']) ?>" alt="Project Image" class="project-image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=Project+Image" alt="Project Image" class="project-image">
                        <?php endif; ?>
                        <div class="project-content">
                            <h3 class="project-title"><?= htmlspecialchars($project['projectName']) ?></h3>
                            <p class="project-description"><?= htmlspecialchars($project['projectDescription']) ?></p>
                            <div class="project-meta">
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($project['projectLocation'] ?? 'Not specified') ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>
                                        <?= date('M Y', strtotime($project['startDate'])) ?> - 
                                        <?= !empty($project['endDate']) ? date('M Y', strtotime($project['endDate'])) : 'Ongoing' ?>
                                    </span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <span><?= $project['teamSize'] ?? 'Not specified' ?></span>
                                </div>
                                <?php if (isset($project['is_paid'])): ?>
                                <div class="meta-item">
                                    <i class="<?= $project['is_paid'] ? 'fas fa-euro-sign' : 'fas fa-unlock' ?>"></i>
                                    <span><?= $project['is_paid'] ? htmlspecialchars(number_format($project['ticket_price'], 2) . ' €') : 'Free Access' ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="project-actions">
    <a href="project-details.php?id=<?= $project['id'] ?>" class="button button-primary">
        <i class="fas fa-eye"></i> View Details
    </a>
    <?php if (isset($project['is_paid']) && $project['is_paid']): ?>
    <a href="cart.php?action=add&project_id=<?= $project['id'] ?>" class="button button-ticket">
        <i class="fas fa-ticket-alt"></i> Buy Ticket
    </a>
    <?php endif; ?>
    <button class="button button-support <?= $project['is_supported'] ? 'supported' : '' ?>" 
            data-project-id="<?= $project['id'] ?>">
        <i class="fas fa-heart"></i> 
        <span class="support-text"><?= $project['is_supported'] ? 'Supported' : 'Support' ?></span>
        <span class="support-count">(<?= $project['supporters_count'] ?>)</span>
    </button>
</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-projects" style="grid-column: 1 / -1;">
                    <h3>No projects found</h3>
                    <p>Try adjusting your search or filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function () {
  const selectAll = document.getElementById('selectAll');
  const deleteBtn = document.getElementById('deleteSelected');
  const projectSelectors = document.querySelectorAll('.project-selector');
  const projectCards = document.querySelectorAll('.project-card');

  // 🔁 Fonction pour activer/désactiver le bouton Delete
  function updateDeleteButton() {
    const anySelected = [...projectSelectors].some(cb => cb.checked);
    deleteBtn.disabled = !anySelected;
  }

  // ✅ Select All
  selectAll.addEventListener('change', function () {
    projectSelectors.forEach(checkbox => {
      checkbox.checked = this.checked;
      checkbox.dispatchEvent(new Event('change')); // met à jour l'état visuel
    });
  });

  // ✅ Checkbox individuelle
  projectSelectors.forEach(checkbox => {
    const card = checkbox.closest('.project-card');
    checkbox.addEventListener('change', function () {
      card.classList.toggle('selected', this.checked);
      updateDeleteButton();
    });
  });

  // ✅ Clic sur carte = toggle checkbox
  projectCards.forEach(card => {
    card.addEventListener('click', function (e) {
      // Ne pas agir si clic sur bouton (View / Support) ou sur la checkbox elle-même
      if (
        e.target.closest('.button') ||
        e.target.classList.contains('project-selector') ||
        e.target.tagName === 'INPUT'
      ) return;

      const checkbox = card.querySelector('.project-selector');
      if (checkbox) {
        checkbox.checked = !checkbox.checked;
        checkbox.dispatchEvent(new Event('change'));
      }
    });
  });

  // ✅ Supprimer les projets sélectionnés
  deleteBtn.addEventListener('click', async function () {
    const selectedProjects = [...document.querySelectorAll('.project-selector:checked')]
      .map(checkbox => checkbox.dataset.projectId);

    if (selectedProjects.length === 0) return;

    if (!confirm(`Are you sure you want to delete ${selectedProjects.length} project(s)?`)) {
      return;
    }

    try {
      console.log("Sending delete request for projects:", selectedProjects);
      
      const response = await fetch('delete-projects.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ projectIds: selectedProjects })
      });

      console.log("Response status:", response.status);
      console.log("Response headers:", response.headers);
      
      const responseText = await response.text();
      console.log("Raw response:", responseText);
      
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (e) {
        console.error("Error parsing JSON response:", e);
        alert("Error: Invalid response from server");
        return;
      }
      
      console.log("Parsed response:", result);

      if (result.success) {
        // Supprimer les cartes du DOM
        selectedProjects.forEach(id => {
          const card = document.querySelector(`.project-selector[data-project-id="${id}"]`).closest('.project-card');
          if (card) card.remove();
        });

        selectAll.checked = false;
        updateDeleteButton();

        alert(`Successfully deleted ${selectedProjects.length} project(s).`);
      } else {
        alert("Error: " + result.message);
      }
    } catch (error) {
      console.error("Error deleting projects:", error);
      alert("An error occurred while deleting projects.");
    }
  });

  // ✅ Supporter un projet (💙 bouton cœur)
  document.querySelectorAll('.button-support').forEach(button => {
    button.addEventListener('click', async function (e) {
      e.stopPropagation(); // évite de déclencher le clic sur la carte

      const projectId = this.dataset.projectId;
      
      // Check if user is logged in
      <?php if (!$isLoggedIn): ?>
        alert('You need to be logged in to support a project. Please log in or sign up.');
        window.location.href = '../login.php?redirect=' + encodeURIComponent(window.location.pathname);
        return;
      <?php endif; ?>

      try {
        const response = await fetch('support-project.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `projectId=${projectId}`
        });

        if (!response.ok) {
          throw new Error('Network response was not ok');
        }

        const result = await response.json();
        console.log("SUPPORT RESULT:", result);

        if (result.status === 'success') {
          this.classList.toggle('supported', result.is_supported);

          const supportText = this.querySelector('.support-text');
          const countElement = this.querySelector('.support-count');

          supportText.textContent = result.is_supported ? 'Supported' : 'Support';
          countElement.textContent = `(${result.supporters_count})`;
        } else {
          console.error('Support error:', result.message);
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Support error:', error);
        alert('An error occurred while processing your support request');
      }
    });
  });
});
//tri
document.querySelectorAll('.sort-button').forEach(button => {
    button.addEventListener('click', function(e) {
        // Juste pour le feedback visuel
        document.querySelectorAll('.sort-button').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');
        
        // La navigation se fera via le lien href normalement
        // Pas besoin de preventDefault() ici
    });
});
</script>

</body>
</html>