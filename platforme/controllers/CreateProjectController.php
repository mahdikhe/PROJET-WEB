// controllers/CreateProjectController.php

require_once '../models/ProjectModel.php';
require_once '../config/Database.php';

class CreateProjectController {
    private $projectModel;

    public function __construct() {
        // Initialize the ProjectModel
        $this->projectModel = new ProjectModel();
    }

    // Handle the creation of a new project
    public function createProject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate input
            $data = [
                'projectName' => trim($_POST['projectName']),
                'projectDescription' => trim($_POST['projectDescription']),
                'projectBudget' => floatval($_POST['projectBudget']),
                'teamSize' => intval($_POST['teamSize']),
                'status' => $_POST['status'],
                'projectTags' => $_POST['projectTags'],
                'skillsNeeded' => $_POST['skillsNeeded'],
                'projectVisibility' => $_POST['projectVisibility'],
                'fundingGoal' => floatval($_POST['fundingGoal'])
            ];

            // Validate required fields
            if (empty($data['projectName']) || empty($data['projectDescription'])) {
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=Please fill all required fields');
                exit;
            }

            // Validate numeric fields
            if ($data['projectBudget'] <= 0 || $data['teamSize'] <= 0 || $data['fundingGoal'] < 0) {
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=Invalid budget, team size, or funding goal');
                exit;
            }

            // Handle file upload
            if (!isset($_FILES['projectImage']) || $_FILES['projectImage']['error'] !== UPLOAD_ERR_OK) {
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=Error uploading project image');
                exit;
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            $fileType = $_FILES['projectImage']['type'];
            $fileSize = $_FILES['projectImage']['size'];
            $fileName = basename($_FILES['projectImage']['name']);

            if (!in_array($fileType, $allowedTypes)) {
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=Invalid file type. Only JPG, PNG, and GIF are allowed.');
                exit;
            }

            if ($fileSize > $maxFileSize) {
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=File size exceeds the limit of 5MB.');
                exit;
            }

            // Generate a unique filename
            $uniqueFileName = uniqid('project_', true) . '_' . $fileName;
            $targetDir = "../uploads/";
            $targetFile = $targetDir . $uniqueFileName;

            if (!move_uploaded_file($_FILES['projectImage']['tmp_name'], $targetFile)) {
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=Failed to upload project image');
                exit;
            }

            // Add the uploaded file name to the data array
            $data['projectImage'] = $uniqueFileName;

            // Save the project using the model
            try {
                $projectId = $this->projectModel->createProject($data);
                header('Location: ../views/backoffice/dashboard/create_project/project_success.php?success=Project created successfully');
                exit;
            } catch (Exception $e) {
                error_log("Error creating project: " . $e->getMessage());
                header('Location: ../views/backoffice/dashboard/create_project/createProject.html?error=An error occurred while creating the project');
                exit;
            }
        } else {
            // If not a POST request, show the form
            require '../views/backoffice/dashboard/create_project/createProject.html';
        }
    }
}