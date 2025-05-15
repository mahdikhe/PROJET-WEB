<?php
require_once __DIR__ . '/../config/config.php';

abstract class Controller {
    protected $db;
    protected $viewData = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->initSession();
        $this->setCSRFToken();
    }

    protected function render($view, $data = []) {
        // Extract data to make variables available in view
        $this->viewData = array_merge($this->viewData, $data);
        extract($this->viewData);
        
        // Include common data available to all views
        $baseUrl = BASE_URL;
        $csrfToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';
        $flashMessage = $this->getFlashMessage();
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = BASE_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new Exception("View {$view} not found");
        }
        require $viewPath;
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Include the layout if it exists
        $layoutPath = BASE_PATH . '/views/layouts/main.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function renderJSON($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }

    protected function redirectWithMessage($url, $message, $type = 'success') {
        $this->setFlashMessage($message, $type);
        $this->redirect($url);
    }

    protected function setFlashMessage($message, $type = 'success') {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    protected function getFlashMessage() {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function validateCSRF() {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$token || $token !== $_SESSION[CSRF_TOKEN_NAME]) {
            throw new Exception('Invalid CSRF token');
        }
    }

    private function setCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
    }

    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true,
                'cookie_secure' => ENVIRONMENT !== 'development',
                'cookie_samesite' => 'Lax',
                'gc_maxlifetime' => SESSION_LIFETIME
            ]);
        }
    }

    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirectWithMessage('/login', 'Please log in to access this page', 'warning');
        }
    }

    protected function getPostData() {
        return $_POST;
    }

    protected function getQueryParams() {
        return $_GET;
    }

    protected function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    protected function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error uploading file');
        }

        $allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
        $maxSize = $maxSize ?? MAX_UPLOAD_SIZE;

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('File too large');
        }

        return true;
    }
}