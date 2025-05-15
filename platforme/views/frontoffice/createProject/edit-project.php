<?php
require_once(__DIR__ . '/../../../config/Database.php');

// Get project ID from URL
$projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($projectId < 1) {
    die("<div class='error'>Invalid project ID</div>");
}

// Initialize database connection
$db = Database::getInstance()->getConnection();

// Fetch project details
$sql = "SELECT * FROM projects WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':id', $projectId, PDO::PARAM_INT);
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    die("<div class='error'>Project not found</div>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Collect and sanitize form data
        $projectName = htmlspecialchars($_POST['projectName']);
        $projectDescription = htmlspecialchars($_POST['projectDescription']);
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $projectLocation = htmlspecialchars($_POST['projectLocation']);
        $projectCategory = $_POST['projectCategory'];
        $projectTags = htmlspecialchars($_POST['projectTags']);
        $projectBudget = floatval($_POST['projectBudget']);
        $fundingGoal = floatval($_POST['fundingGoal']);
        $teamSize = $_POST['teamSize'];
        $skillsNeeded = isset($_POST['skills']) ? implode(", ", $_POST['skills']) : '';
        $projectVisibility = $_POST['projectVisibility'];
        $projectWebsite = filter_var($_POST['projectWebsite'], FILTER_VALIDATE_URL);
        
        // Handle image upload
        $imageDestination = $project['projectImage']; // Keep existing if no new upload
        if (!empty($_FILES['projectImage']['name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filename = time() . '_' . basename($_FILES['projectImage']['name']);
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['projectImage']['tmp_name'], $targetPath)) {
                $imageDestination = 'uploads/' . $filename;
                // Delete old image if it exists
                if (!empty($project['projectImage']) && file_exists($project['projectImage'])) {
                    unlink($project['projectImage']);
                }
            }
        }
        
        // Update project in database
        $sql = "UPDATE projects SET 
                projectName = :projectName,
                projectDescription = :projectDescription,
                startDate = :startDate,
                endDate = :endDate,
                projectLocation = :projectLocation,
                projectCategory = :projectCategory,
                projectTags = :projectTags,
                projectBudget = :projectBudget,
                fundingGoal = :fundingGoal,
                teamSize = :teamSize,
                skillsNeeded = :skillsNeeded,
                projectVisibility = :projectVisibility,
                projectImage = :projectImage,
                projectWebsite = :projectWebsite
                WHERE id = :id";

        $stmt = $db->prepare($sql);

        // Bind parameters
        $params = [
            ':projectName' => $projectName,
            ':projectDescription' => $projectDescription,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
            ':projectLocation' => $projectLocation,
            ':projectCategory' => $projectCategory,
            ':projectTags' => $projectTags,
            ':projectBudget' => $projectBudget,
            ':fundingGoal' => $fundingGoal,
            ':teamSize' => $teamSize,
            ':skillsNeeded' => $skillsNeeded,
            ':projectVisibility' => $projectVisibility,
            ':projectImage' => $imageDestination,
            ':projectWebsite' => $projectWebsite,
            ':id' => $projectId
        ];

        if ($stmt->execute($params)) {
            header("Location: project-details.php?id=" . $projectId);
            exit();
        } else {
            throw new Exception("Error updating project: " . implode(" ", $stmt->errorInfo()));
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - <?= htmlspecialchars($project['projectName']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #4cc9f0;
            --dark: #212529;
            --light: #f8f9fa;
            --border: #dee2e6;
            --error: #f72585;
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
        
        .edit-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        
        .page-title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .edit-form {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .image-preview {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 1rem;
            display: block;
        }
        
        .skills-checkbox {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            border: none;
            cursor: pointer;
        }
        
        .button-primary {
            background: var(--primary);
            color: white;
        }
        
        .button-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .error {
            color: var(--error);
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="edit-header">
        <div class="container">
            <h1 class="page-title">Edit Project</h1>
            <p>Editing: <?= htmlspecialchars($project['projectName']) ?></p>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($error)): ?>
        <div class="error-message" style="background: #ffebee; color: var(--error); padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form class="edit-form" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="projectName" class="form-label">Project Name</label>
                    <input type="text" id="projectName" name="projectName" class="form-control" 
                           value="<?= htmlspecialchars($project['projectName']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="projectCategory" class="form-label">Category</label>
                    <select id="projectCategory" name="projectCategory" class="form-control" required>
                        <option value="urban-development" <?= $project['projectCategory'] == 'urban-development' ? 'selected' : '' ?>>Urban Development</option>
                        <option value="environment" <?= $project['projectCategory'] == 'environment' ? 'selected' : '' ?>>Environment</option>
                        <option value="transportation" <?= $project['projectCategory'] == 'transportation' ? 'selected' : '' ?>>Transportation</option>
                        <option value="community" <?= $project['projectCategory'] == 'community' ? 'selected' : '' ?>>Community Services</option>
                        <option value="technology" <?= $project['projectCategory'] == 'technology' ? 'selected' : '' ?>>Smart Technology</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="projectDescription" class="form-label">Description</label>
                <textarea id="projectDescription" name="projectDescription" class="form-control" required><?= htmlspecialchars($project['projectDescription']) ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" id="startDate" name="startDate" class="form-control" 
                           value="<?= htmlspecialchars($project['startDate']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" id="endDate" name="endDate" class="form-control" 
                           value="<?= htmlspecialchars($project['endDate']) ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="projectLocation" class="form-label">Location</label>
                    <input type="text" id="projectLocation" name="projectLocation" class="form-control" 
                           value="<?= htmlspecialchars($project['projectLocation']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="projectTags" class="form-label">Tags (comma separated)</label>
                    <input type="text" id="projectTags" name="projectTags" class="form-control" 
                           value="<?= htmlspecialchars($project['projectTags']) ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="projectBudget" class="form-label">Budget ($)</label>
                    <input type="number" id="projectBudget" name="projectBudget" class="form-control" 
                           value="<?= htmlspecialchars($project['projectBudget']) ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="fundingGoal" class="form-label">Funding Goal ($)</label>
                    <input type="number" id="fundingGoal" name="fundingGoal" class="form-control" 
                           value="<?= htmlspecialchars($project['fundingGoal']) ?>" step="0.01" min="0">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="teamSize" class="form-label">Team Size</label>
                    <select id="teamSize" name="teamSize" class="form-control" required>
                        <option value="1-5" <?= $project['teamSize'] == '1-5' ? 'selected' : '' ?>>1-5 people</option>
                        <option value="6-10" <?= $project['teamSize'] == '6-10' ? 'selected' : '' ?>>6-10 people</option>
                        <option value="11-20" <?= $project['teamSize'] == '11-20' ? 'selected' : '' ?>>11-20 people</option>
                        <option value="20+" <?= $project['teamSize'] == '20+' ? 'selected' : '' ?>>20+ people</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Required Skills</label>
                    <div class="skills-checkbox">
                        <?php
                        $currentSkills = explode(',', $project['skillsNeeded']);
                        $allSkills = ['Design', 'Engineering', 'Community Outreach', 'Finance', 'Legal', 'Marketing', 'Research'];
                        
                        foreach ($allSkills as $skill) {
                            $isChecked = in_array(trim($skill), $currentSkills) ? 'checked' : '';
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="skills[]" value="' . htmlspecialchars($skill) . '" ' . $isChecked . '>';
                            echo htmlspecialchars($skill);
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="projectVisibility" class="form-label">Visibility</label>
                    <select id="projectVisibility" name="projectVisibility" class="form-control" required>
                        <option value="public" <?= $project['projectVisibility'] == 'public' ? 'selected' : '' ?>>Public</option>
                        <option value="private" <?= $project['projectVisibility'] == 'private' ? 'selected' : '' ?>>Private</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="projectWebsite" class="form-label">Project Website</label>
                    <input type="url" id="projectWebsite" name="projectWebsite" class="form-control" 
                           value="<?= htmlspecialchars($project['projectWebsite']) ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="projectImage" class="form-label">Project Image</label>
                <input type="file" id="projectImage" name="projectImage" class="form-control" accept="image/*">
                <?php if (!empty($project['projectImage'])): ?>
                <img src="<?= htmlspecialchars($project['projectImage']) ?>" alt="Current Project Image" class="image-preview">
                <p>Current image: <?= basename($project['projectImage']) ?></p>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-top: 2rem;">
                <button type="submit" class="button button-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="project-details.php?id=<?= $project['id'] ?>" class="button button-outline">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>