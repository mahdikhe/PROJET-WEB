<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once(__DIR__ . '/../../../config/Database.php');

// Validate and sanitize the project ID
$projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$projectId || $projectId < 1) {
    header('Location: projects.php');
    exit();
}

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Prepare and execute the query using PDO
    $sql = "SELECT * FROM projects WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        header('Location: projects.php');
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Error in project_success.php: " . $e->getMessage());
    header('Location: projects.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Created Successfully</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --error: #f72585;
            --dark: #212529;
            --light: #f8f9fa;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
            padding: 2rem;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-container {
            max-width: 800px;
            width: 100%;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .error {
            color: var(--error);
            padding: 1rem;
            border: 1px solid var(--error);
            border-radius: 8px;
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }
        
        .project-info {
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--light);
            border-radius: 8px;
            text-align: left;
        }
        
        .project-info p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }
        
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .button-primary {
            background: var(--primary);
            color: white;
        }
        
        .button-primary:hover {
            background: #3651d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 600px) {
            body {
                padding: 1rem;
            }
            
            .success-container {
                padding: 1.5rem;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1>Project Created Successfully!</h1>
        
        <div class="project-info">
            <p><strong>Project Name:</strong> <?= htmlspecialchars($project['projectName']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($project['projectLocation']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($project['projectCategory']) ?></p>
            <p><strong>Created On:</strong> <?= date('F j, Y', strtotime($project['created_at'] ?? 'now')) ?></p>
        </div>
        
        <div class="button-group">
            <a href="projects.php" class="button button-primary">
                <i class="fas fa-list"></i> View All Projects
            </a>
            <a href="project-details.php?id=<?= $project['id'] ?>" class="button button-primary">
                <i class="fas fa-eye"></i> View Project Details
            </a>
        </div>
    </div>
</body>
</html>