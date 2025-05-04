<?php
// Include database connection
include 'C:/Users/Abderrahmen/Desktop/2A40/cursor/website/projet/create project/db.php';

// Check if the project ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all_projects.php?error=no_id");
    exit;
}

$projectId = intval($_GET['id']);

try {
    // Fetch the project details
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        header("Location: all_projects.php?error=not_found");
        exit;
    }
} catch (PDOException $e) {
    header("Location: all_projects.php?error=database&message=" . urlencode($e->getMessage()));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['projectName']) ?> | Project Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #00b8a9;
            --accent-color: #ff6b6b;
            --light-accent: #d295ff;
            --background-color: #f8f9fc;
            --card-bg: #ffffff;
            --text-dark: #2c3e50;
            --text-medium: #5a6b7b;
            --text-light: #a3aebf;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
            --sidebar-bg-start: #2c3e50;
            --sidebar-bg-end: #1a252f;
        }

        /* Dark mode variables */
        body.dark-mode {
            --background-color: #121212;
            --card-bg: #1e1e1e;
            --text-dark: #ffffff;
            --text-medium: #b0b0b0;
            --text-light: #777777;
            --border-color: rgba(255, 255, 255, 0.1);
            --sidebar-bg-start: #000000;
            --sidebar-bg-end: #121212;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg-start), var(--sidebar-bg-end));
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 2rem 0;
            transition: var(--transition);
            z-index: 100;
            box-shadow: var(--shadow-lg);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 1.8rem;
            color: var(--secondary-color);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: var(--text-light);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--secondary-color);
            color: white;
        }

        .nav-link i {
            font-size: 1.2rem;
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .nav-divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 1.5rem 1.5rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .page-subtitle {
            color: var(--text-medium);
            font-size: 1.1rem;
            font-weight: 400;
            margin-top: 0.5rem;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            border: none;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #5a52e3;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-medium);
        }

        .btn-outline:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-dark);
        }

        /* Project Details */
        .project-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .project-header {
            display: flex;
            align-items: flex-start;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .project-image {
            width: 300px;
            height: 200px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: var(--shadow-sm);
        }

        .project-info {
            flex: 1;
        }

        .project-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .project-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-medium);
            font-size: 0.9rem;
        }

        .project-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .badge-category {
            background-color: rgba(108, 99, 255, 0.1);
            color: var(--primary-color);
        }

        .badge-status {
            margin-left: 0.5rem;
        }

        .status-active {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .status-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .status-completed {
            background-color: rgba(108, 99, 255, 0.1);
            color: #6c63ff;
        }

        .project-description {
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .project-section {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .detail-item {
            background-color: rgba(0, 0, 0, 0.02);
            padding: 1rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .detail-label {
            font-size: 0.9rem;
            color: var(--text-medium);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 500;
            color: var(--text-dark);
        }

        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .tag {
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-medium);
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .skill {
            padding: 0.25rem 0.75rem;
            border-radius: 100px;
            font-size: 0.75rem;
            background-color: rgba(108, 99, 255, 0.1);
            color: var(--primary-color);
        }

        /* Dark mode overrides */
        body.dark-mode .detail-item {
            background-color: rgba(255, 255, 255, 0.05);
        }

        body.dark-mode .tag {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="creative_dashboard.php" class="logo">
                <i class="fas fa-city"></i>
                <span>CityPulse</span>
            </a>
        </div>
        <nav>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="creative_dashboard.php" class="nav-link">
                        <i class="fas fa-columns"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="all_projects.php" class="nav-link active">
                        <i class="fas fa-project-diagram"></i>
                        <span>All Projects</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <div class="nav-divider"></div>
                <li class="nav-item">
                    <a href="../../../../views/frontoffice/createProject/createProject.html" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Project</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link settings-toggle">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Project Details</h1>
                <p class="page-subtitle">Viewing detailed information for project #<?= $project['id'] ?></p>
            </div>
            <div class="header-actions">
                <a href="../../../frontoffice/createProject/edit-project.php?id=<?= $project['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Project
                </a>
                <a href="all_projects.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Projects
                </a>
            </div>
        </div>

        <!-- Project Details Content -->
        <div class="project-card">
            <div class="project-header">
                <?php if (!empty($project['projectImage'])): ?>
                <img src="../../../../views/frontoffice/createProject/uploads/<?= htmlspecialchars($project['projectImage']) ?>" alt="<?= htmlspecialchars($project['projectName']) ?>" class="project-image">
                <?php else: ?>
                <div class="project-image placeholder-image">
                    <i class="fas fa-image"></i>
                </div>
                <?php endif; ?>
                <div class="project-info">
                    <h2 class="project-title"><?= htmlspecialchars($project['projectName']) ?></h2>
                    
                    <div class="project-badges">
                        <span class="project-badge badge-category">
                            <?= htmlspecialchars($project['projectCategory']) ?>
                        </span>
                        
                        <?php
                        $now = new DateTime();
                        $start = new DateTime($project['startDate']);
                        $end = new DateTime($project['endDate']);
                        
                        if ($now < $start) {
                            $status = 'pending';
                            $statusText = 'Pending';
                        } elseif ($now > $end) {
                            $status = 'completed';
                            $statusText = 'Completed';
                        } else {
                            $status = 'active';
                            $statusText = 'Active';
                        }
                        ?>
                        
                        <span class="project-badge badge-status status-<?= $status ?>">
                            <?= $statusText ?>
                        </span>
                    </div>
                    
                    <div class="project-meta">
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($project['projectLocation']) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span><?= date('M d, Y', strtotime($project['startDate'])) ?> - <?= date('M d, Y', strtotime($project['endDate'])) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>Team: <?= htmlspecialchars($project['teamSize']) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Budget: <?= number_format($project['projectBudget']) ?> €</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($project['projectWebsite'])): ?>
                    <a href="<?= htmlspecialchars($project['projectWebsite']) ?>" target="_blank" class="btn btn-outline">
                        <i class="fas fa-external-link-alt"></i> Visit Project Website
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="project-description">
                <div class="section-title">Project Description</div>
                <p><?= nl2br(htmlspecialchars($project['projectDescription'])) ?></p>
            </div>
            
            <div class="project-section">
                <div class="section-title">Project Details</div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Project ID</div>
                        <div class="detail-value"><?= $project['id'] ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Budget</div>
                        <div class="detail-value"><?= number_format($project['projectBudget']) ?> €</div>
                    </div>
                    
                    <?php if (!empty($project['fundingGoal'])): ?>
                    <div class="detail-item">
                        <div class="detail-label">Funding Goal</div>
                        <div class="detail-value"><?= number_format($project['fundingGoal']) ?> €</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-item">
                        <div class="detail-label">Team Size</div>
                        <div class="detail-value"><?= htmlspecialchars($project['teamSize']) ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Visibility</div>
                        <div class="detail-value"><?= ucfirst(htmlspecialchars($project['projectVisibility'])) ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($project['projectTags'])): ?>
            <div class="project-section">
                <div class="section-title">Tags</div>
                <div class="tag-list">
                    <?php
                    $tags = explode(',', $project['projectTags']);
                    foreach ($tags as $tag):
                        $tag = trim($tag);
                        if (!empty($tag)):
                    ?>
                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($project['skillsNeeded'])): ?>
            <div class="project-section">
                <div class="section-title">Required Skills</div>
                <div class="skills-list">
                    <?php
                    $skills = explode(',', $project['skillsNeeded']);
                    foreach ($skills as $skill):
                        $skill = trim($skill);
                        if (!empty($skill)):
                    ?>
                    <span class="skill"><?= htmlspecialchars($skill) ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($project['id'])): ?>
            <div style="text-align:right; margin-top: 20px;">
                <a href="../../../../views/frontoffice/tasks.php?project_id=<?= $project['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-tasks"></i> View Project Tasks
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Check for dark mode settings on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Load saved settings from localStorage
            try {
                const settings = JSON.parse(localStorage.getItem('dashboardSettings')) || {};
                
                // Apply dark mode if enabled
                if (settings.darkMode) {
                    document.body.classList.add('dark-mode');
                }
            } catch (error) {
                console.error('Error loading dashboard settings:', error);
            }
        });
    </script>
</body>
</html>