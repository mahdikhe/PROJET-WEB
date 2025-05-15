<?php

class ProjectController {
    private $projectModel;

    public function __construct() {
        require_once __DIR__ . '/../models/ProjectModel.php';
        $this->projectModel = new ProjectModel();
    }

    public function handleCreateProject() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        try {
            // Validate required fields
            $required = ['projectName', 'projectDescription', 'startDate', 'endDate', 
                        'projectLocation', 'projectCategory', 'projectTags', 'teamSize', 
                        'projectVisibility', 'terms'];
            
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Required field '$field' is missing");
                }
            }

            // Handle file upload
            $imageDestination = $this->handleImageUpload();

            // Prepare project data
            $projectData = [
                'name' => $_POST['projectName'],
                'description' => $_POST['projectDescription'],
                'startDate' => $_POST['startDate'],
                'endDate' => $_POST['endDate'],
                'location' => $_POST['projectLocation'],
                'category' => $_POST['projectCategory'],
                'tags' => $_POST['projectTags'],
                'budget' => floatval($_POST['projectBudget'] ?? 0),
                'fundingGoal' => floatval($_POST['fundingGoal'] ?? 0),
                'teamSize' => $_POST['teamSize'],
                'skills' => isset($_POST['skills']) ? implode(',', $_POST['skills']) : '',
                'visibility' => $_POST['projectVisibility'],
                'website' => $_POST['projectWebsite'] ?? '',
                'image' => $imageDestination
            ];

            // Save project
            $projectId = $this->projectModel->createProject($projectData);

            echo json_encode([
                'success' => true,
                'message' => 'Project created successfully',
                'projectId' => $projectId
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    private function handleImageUpload() {
        if (!isset($_FILES['projectImage']) || $_FILES['projectImage']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Project image is required');
        }

        $file = $_FILES['projectImage'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid image type. Only JPEG, PNG and GIF are allowed');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('Image size must be less than 5MB');
        }

        $uploadDir = __DIR__ . '/../views/frontoffice/createProject/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . basename($file['name']);
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to upload image');
        }

        return 'uploads/' . $filename;
    }
}