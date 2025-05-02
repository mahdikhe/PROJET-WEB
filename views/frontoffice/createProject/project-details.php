<?php
require_once(__DIR__ . '/../../../config/Database.php');

session_start();

try {
    // Get project ID from URL
    $projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($projectId < 1) {
        throw new Exception("Invalid project ID");
    }

    // Initialize database connection using Database class
    $db = Database::getInstance()->getConnection();

    // Fetch all project details with support count
    $sql = "SELECT p.*, COUNT(ps.id) as supporters_count,
            EXISTS(SELECT 1 FROM project_supporters ps 
                  WHERE ps.project_id = p.id AND ps.user_id = :userId) as is_supported
            FROM projects p
            LEFT JOIN project_supporters ps ON p.id = ps.project_id
            WHERE p.id = :id
            GROUP BY p.id";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id' => $projectId,
        ':userId' => $userId
    ]);
    
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        throw new Exception("Project not found");
    }

} catch (Exception $e) {
    die("<div class='error'>" . htmlspecialchars($e->getMessage()) . "</div>");
}

// Get all column names to display all attributes
$columns = array_keys($project);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['projectName'] ?? 'Project Details') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        
        .project-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 3rem 2rem;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .project-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .project-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .project-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        @media (min-width: 992px) {
            .project-content {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .project-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-title {
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        .attribute-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .attribute {
            margin-bottom: 1rem;
        }
        
        .attribute-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .attribute-value {
            background: var(--light);
            padding: 0.75rem;
            border-radius: 8px;
            word-break: break-word;
        }
        
        .project-image {
            width: 50%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
        
        .button-primary {
            background: var(--primary);
            color: white;
        }
        
        .button-secondary {
            background: var(--secondary);
            color: white;
        }
        
        .button-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .file-list {
            list-style: none;
            padding: 0;
        }
        
        .file-item {
            margin-bottom: 0.5rem;
        }
        
        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .skill-tag {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="project-header">
        <div class="container">
            <h1 class="project-title"><?= htmlspecialchars($project['projectName'] ?? 'Project Details') ?></h1>
            <div class="project-meta">
                <div class="meta-item">
                    <i class="fas fa-hashtag"></i>
                    <span>ID: <?= $project['id'] ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Created: <?= date('F j, Y', strtotime($project['created_at'] ?? 'now')) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span>Category: <?= htmlspecialchars($project['projectCategory'] ?? 'N/A') ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-eye"></i>
                    <span>Visibility: <?= htmlspecialchars($project['projectVisibility'] ?? 'Public') ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($project['projectImage'])): ?>
        <img src="<?= htmlspecialchars($project['projectImage']) ?>" alt="Project Image" class="project-image">
        <?php endif; ?>
        
        <div class="project-content">
            <div class="project-section">
                <h2 class="section-title">Project Overview</h2>
                <div class="attribute-grid">
                    <div class="attribute">
                        <div class="attribute-label">Description</div>
                        <div class="attribute-value"><?= nl2br(htmlspecialchars($project['projectDescription'] ?? 'No description provided')) ?></div>
                    </div>
                    
                    <div class="attribute">
                        <div class="attribute-label">Location</div>
                        <div class="attribute-value"><?= htmlspecialchars($project['projectLocation'] ?? 'N/A') ?></div>
                    </div>
                    
                    <div class="attribute">
                        <div class="attribute-label">Budget</div>
                        <div class="attribute-value">$<?= isset($project['projectBudget']) ? number_format($project['projectBudget'], 2) : '0.00' ?></div>
                    </div>
                    
                    <div class="attribute">
                        <div class="attribute-label">Funding Goal</div>
                        <div class="attribute-value">$<?= isset($project['fundingGoal']) ? number_format($project['fundingGoal'], 2) : 'Not specified' ?></div>
                    </div>
                    
                    <div class="attribute">
                        <div class="attribute-label">Team Size</div>
                        <div class="attribute-value"><?= htmlspecialchars($project['teamSize'] ?? 'Not specified') ?></div>
                    </div>
                    
                    <div class="attribute">
                        <div class="attribute-label">Status</div>
                        <div class="attribute-value"><?= htmlspecialchars($project['status'] ?? 'Active') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="project-section">
                <h2 class="section-title">Project Details</h2>
                <div class="attribute-grid">
                    <?php
                    // Display all remaining attributes that haven't been shown yet
                    $shownAttributes = ['id', 'projectName', 'projectDescription', 'projectLocation', 
                                       'projectCategory', 'projectBudget', 'fundingGoal', 'teamSize', 
                                       'projectVisibility', 'created_at', 'projectImage'];
                    
                    foreach ($project as $key => $value) {
                        if (!in_array($key, $shownAttributes)) {
                            echo '<div class="attribute">';
                            echo '<div class="attribute-label">' . ucfirst(str_replace('_', ' ', $key)) . '</div>';
                            
                            if ($key === 'skillsNeeded' && !empty($value)) {
                                $skills = explode(',', $value);
                                echo '<div class="skills-list">';
                                foreach ($skills as $skill) {
                                    echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
                                }
                                echo '</div>';
                            } elseif ($key === 'additionalFiles' && !empty($value)) {
                                $files = explode(',', $value);
                                echo '<ul class="file-list">';
                                foreach ($files as $file) {
                                    if (!empty(trim($file))) {
                                        echo '<li class="file-item"><a href="' . htmlspecialchars(trim($file)) . '" class="file-link" target="_blank">';
                                        echo '<i class="fas fa-file"></i> ' . basename($file);
                                        echo '</a></li>';
                                    }
                                }
                                echo '</ul>';
                            } else {
                                echo '<div class="attribute-value">';
                                echo !empty($value) ? htmlspecialchars($value) : 'N/A';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="project-section" style="margin-top: 2rem;">
            <h2 class="section-title">Actions</h2>
            <a href="../project.html" class="button button-outline">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
            <a href="edit-project.php?id=<?= $project['id'] ?>" class="button button-primary">
                <i class="fas fa-edit"></i> Edit Project
            </a>
            <a href="project_success.php?id=<?= $project['id'] ?>" class="button button-secondary" style="background-color: #4cc9f0; color: white;">
        <i class="fas fa-check-circle"></i> Back to Success Page
    </a>
            
        </div>
    </div>
</body>
</html>