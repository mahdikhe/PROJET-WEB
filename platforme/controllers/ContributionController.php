<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/ContributionModel.php';
require_once __DIR__ . '/../models/ProjectModel.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/Controller.php';
// ...existing code...
class ContributionController extends Controller {
    private $contributionModel;
    private $projectModel;

    public function __construct() {
        parent::__construct();
        $this->contributionModel = new ContributionModel($this->db);
        $this->projectModel = new ProjectModel($this->db);

        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            if (method_exists($this, $action)) {
                $this->$action();
            }
        }
    }

    public function handleContributionSubmit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirectWithError('contribute.php', 'Invalid request method');
        }

        try {
            $data = [
                'firstName' => $this->sanitizeInput($_POST['firstName']),
                'lastName' => $this->sanitizeInput($_POST['lastName']),
                'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
                'city' => $this->sanitizeInput($_POST['city']),
                'phone' => $this->sanitizeInput($_POST['phone-number']),
                'age' => $this->sanitizeInput($_POST['age-group']),
                'projectId' => filter_var($_POST['project_id'], FILTER_VALIDATE_INT),
                'availability' => $this->sanitizeInput($_POST['location-availability']),
                'type' => $this->sanitizeInput($_POST['contributionType']),
                'message' => $this->sanitizeInput($_POST['message'])
            ];

            // Validate the input
            $errors = $this->validateInput($data);
            if (!empty($errors)) {
                return $this->redirectWithError('contribute.php', implode(', ', $errors));
            }

            // Handle file upload if present
            if (!empty($_FILES['fileUpload']['name'])) {
                $uploadResult = $this->handleFileUpload($_FILES['fileUpload']);
                if ($uploadResult['success']) {
                    $data['filePath'] = $uploadResult['path'];
                }
            }

            // Save the contribution
            $this->contributionModel->saveContributorDetails($data);
            
            // Redirect to success page
            header('Location: views/frontoffice/contribution-success.php');
            exit;

        } catch (Exception $e) {
            error_log("Error in contribution submission: " . $e->getMessage());
            return $this->redirectWithError('contribute.php', 'An error occurred while saving your contribution');
        }
    }

    private function validateInput($data) {
        $errors = [];
        if (empty($data['firstName'])) $errors[] = "First name is required";
        if (empty($data['lastName'])) $errors[] = "Last name is required";
        if (empty($data['email'])) $errors[] = "Email is required";
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        if (empty($data['type'])) $errors[] = "Contribution type is required";
        if (empty($data['message'])) $errors[] = "Message is required";
        return $errors;
    }

    private function handleFileUpload($file) {
        $uploadDir = __DIR__ . '/../views/frontoffice/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'path' => 'uploads/' . $fileName];
        }
        return ['success' => false, 'error' => 'Failed to upload file'];
    }

    private function redirectWithError($page, $message) {
        header("Location: views/frontoffice/{$page}?error=" . urlencode($message));
        exit;
    }

    public function index() {
        $contributions = $this->contributionModel->getAllContributions();
        return $this->render('contributions/index', ['contributions' => $contributions]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCSRF();
                
                $data = [
                    'project_id' => filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT),
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'contribution_type' => $this->sanitizeInput($_POST['contribution_type'] ?? ''),
                    'description' => $this->sanitizeInput($_POST['description'] ?? ''),
                    'amount' => filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT),
                    'status' => 'pending'
                ];

                // Validate required fields
                if (!$data['project_id'] || !$data['user_id'] || empty($data['contribution_type'])) {
                    throw new Exception('Missing required fields');
                }

                // Verify project exists
                $project = $this->projectModel->getProjectById($data['project_id']);
                if (!$project) {
                    throw new Exception('Project not found');
                }

                $contributionId = $this->contributionModel->createContribution($data);
                return $this->redirectWithMessage('/contributions/' . $contributionId, 'Contribution submitted successfully');
            } catch (Exception $e) {
                return $this->render('contributions/create', [
                    'error' => $e->getMessage(),
                    'project_id' => $_POST['project_id'] ?? null
                ]);
            }
        }

        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
        $project = $projectId ? $this->projectModel->getProjectById($projectId) : null;
        
        return $this->render('contributions/create', [
            'project' => $project
        ]);
    }

    public function view($id) {
        $contribution = $this->contributionModel->getContributionById($id);
        if (!$contribution) {
            return $this->redirectWithMessage('/contributions', 'Contribution not found', 'error');
        }

        return $this->render('contributions/view', [
            'contribution' => $contribution
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/contributions/' . $id);
        }

        try {
            $this->validateCSRF();
            
            $data = [
                'status' => $this->sanitizeInput($_POST['status'] ?? ''),
                'admin_notes' => $this->sanitizeInput($_POST['admin_notes'] ?? '')
            ];

            $this->contributionModel->updateContribution($id, $data);
            return $this->redirectWithMessage('/contributions/' . $id, 'Contribution updated successfully');
        } catch (Exception $e) {
            return $this->redirectWithMessage('/contributions/' . $id, $e->getMessage(), 'error');
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/contributions/' . $id);
        }

        try {
            $this->validateCSRF();
            $this->contributionModel->deleteContribution($id);
            return $this->redirectWithMessage('/contributions', 'Contribution deleted successfully');
        } catch (Exception $e) {
            return $this->redirectWithMessage('/contributions/' . $id, $e->getMessage(), 'error');
        }
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}