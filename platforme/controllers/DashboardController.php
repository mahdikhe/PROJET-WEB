<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController extends Controller {
    private $dashboardModel;

    public function __construct() {
        parent::__construct();
        $this->dashboardModel = new DashboardModel();
    }

    public function index() {
        // Check if user is authenticated
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        // Get dashboard data
        $stats = $this->dashboardModel->getProjectStats();
        $recentProjects = $this->dashboardModel->getRecentProjects();
        $userActivities = $this->dashboardModel->getUserActivities();

        // Render dashboard view with data
        return $this->render('backoffice/dashboard/index', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'userActivities' => $userActivities
            // CSRF token is automatically added by parent render method
        ]);
    }

    public function userStats() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->renderJSON(['error' => 'Method not allowed']);
        }

        try {
            $stats = $this->dashboardModel->getUserStats();
            return $this->renderJSON($stats);
        } catch (Exception $e) {
            return $this->renderJSON(['error' => $e->getMessage()]);
        }
    }

    public function projectMetrics() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $this->renderJSON(['error' => 'Method not allowed']);
        }

        $timeframe = $this->getQueryParams()['timeframe'] ?? 'week';
        
        try {
            $metrics = $this->dashboardModel->getProjectMetrics($timeframe);
            return $this->renderJSON($metrics);
        } catch (Exception $e) {
            return $this->renderJSON(['error' => $e->getMessage()]);
        }
    }

    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/dashboard');
        }

        try {
            $this->validateCSRF();
            $settings = $this->getPostData();
            $this->dashboardModel->updateUserSettings($settings);
            return $this->redirect('/dashboard?success=settings_updated');
        } catch (Exception $e) {
            return $this->redirect('/dashboard?error=' . urlencode($e->getMessage()));
        }
    }
}
