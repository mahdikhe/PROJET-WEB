<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/TaskModel.php';
require_once __DIR__ . '/../models/ProjectModel.php';

use Models\TaskModel;

class TaskController extends Controller {
    private $taskModel;
    private $projectModel;

    public function __construct() {
        parent::__construct();
        $this->taskModel = new TaskModel($this->db);
        $this->projectModel = new ProjectModel($this->db);
    }

    public function index() {
        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
        $tasks = $this->taskModel->getTasks($projectId);
        $projects = $this->projectModel->getAllProjects();
        
        return $this->render('frontoffice/tasks', [
            'tasks' => $tasks,
            'projects' => $projects,
            'project_id' => $projectId
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCSRF();
                $data = $this->validateTaskData($_POST);
                $taskId = $this->taskModel->createTask($data);
                
                return $this->redirectWithMessage(
                    "/tasks/{$taskId}", 
                    'Task created successfully'
                );
            } catch (Exception $e) {
                return $this->render('frontoffice/create-task', [
                    'error' => $e->getMessage(),
                    'projects' => $this->projectModel->getAllProjects(),
                    'data' => $_POST
                ]);
            }
        }

        return $this->render('frontoffice/create-task', [
            'projects' => $this->projectModel->getAllProjects()
        ]);
    }

    public function view($id) {
        $task = $this->taskModel->getTaskById($id);
        if (!$task) {
            return $this->redirectWithMessage('/tasks', 'Task not found', 'error');
        }

        return $this->render('frontoffice/view-task', [
            'task' => $task
        ]);
    }

    public function edit($id) {
        $task = $this->taskModel->getTaskById($id);
        if (!$task) {
            return $this->redirectWithMessage('/tasks', 'Task not found', 'error');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateCSRF();
                $data = $this->validateTaskData($_POST);
                $this->taskModel->updateTask($id, $data);
                
                return $this->redirectWithMessage(
                    "/tasks/{$id}",
                    'Task updated successfully'
                );
            } catch (Exception $e) {
                return $this->render('frontoffice/edit-task', [
                    'task' => $task,
                    'projects' => $this->projectModel->getAllProjects(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $this->render('frontoffice/edit-task', [
            'task' => $task,
            'projects' => $this->projectModel->getAllProjects()
        ]);
    }

    public function updateStatus() {
        if (!isset($_POST['task_id']) || !isset($_POST['status'])) {
            return $this->renderJSON([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
        }

        try {
            $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
            $status = $this->sanitizeInput($_POST['status']);
            
            $this->taskModel->updateTaskStatus($taskId, $status);
            
            return $this->renderJSON([
                'success' => true,
                'message' => 'Task status updated'
            ]);
        } catch (Exception $e) {
            return $this->renderJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete($id) {
        try {
            $this->validateCSRF();
            $this->taskModel->deleteTask($id);
            return $this->redirectWithMessage('/tasks', 'Task deleted successfully');
        } catch (Exception $e) {
            return $this->redirectWithMessage('/tasks', $e->getMessage(), 'error');
        }
    }

    private function validateTaskData($data) {
        $required = ['title', 'project_id', 'status', 'priority'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception(ucfirst($field) . ' is required');
            }
        }

        return [
            'title' => $this->sanitizeInput($data['title']),
            'description' => $this->sanitizeInput($data['description'] ?? ''),
            'project_id' => filter_var($data['project_id'], FILTER_VALIDATE_INT),
            'status' => $this->sanitizeInput($data['status']),
            'priority' => $this->sanitizeInput($data['priority']),
            'assigned_to' => $this->sanitizeInput($data['assigned_to'] ?? ''),
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => filter_var($data['estimated_hours'] ?? 0, FILTER_VALIDATE_FLOAT)
        ];
    }
}
