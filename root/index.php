<?php
session_start();
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Router.php';
require_once __DIR__ . '/../controllers/FrontController.php';
require_once __DIR__ . '/../controllers/ProjectController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../controllers/ContributionController.php';

// Initialize router
$router = new Router();

// Front routes
$router->addRoute('GET', '/', 'FrontController', 'showHomepage');
$router->addRoute('GET', '/dashboard', 'DashboardController', 'index');

// Project routes
$router->addRoute('GET', '/projects', 'ProjectController', 'listProjects');
$router->addRoute('GET', '/projects/create', 'ProjectController', 'createProject');
$router->addRoute('POST', '/projects/create', 'ProjectController', 'createProject');
$router->addRoute('GET', '/projects/{id}', 'ProjectController', 'viewProject');
$router->addRoute('GET', '/projects/{id}/edit', 'ProjectController', 'editProject');
$router->addRoute('POST', '/projects/{id}/edit', 'ProjectController', 'editProject');
$router->addRoute('POST', '/projects/{id}/delete', 'ProjectController', 'deleteProject');
$router->addRoute('POST', '/projects/{id}/support', 'ProjectController', 'handleProjectSupport');

// Task routes 
$router->addRoute('GET', '/tasks', 'TaskController', 'index');
$router->addRoute('GET', '/tasks/create', 'TaskController', 'create');
$router->addRoute('POST', '/tasks/create', 'TaskController', 'store');
$router->addRoute('GET', '/tasks/{id}', 'TaskController', 'view');
$router->addRoute('GET', '/tasks/{id}/edit', 'TaskController', 'edit');
$router->addRoute('POST', '/tasks/{id}', 'TaskController', 'update');
$router->addRoute('POST', '/tasks/{id}/delete', 'TaskController', 'delete');

// Contribution routes
$router->addRoute('GET', '/contributions', 'ContributionController', 'index');
$router->addRoute('GET', '/contribute', 'ContributionController', 'showForm');
$router->addRoute('POST', '/contribute', 'ContributionController', 'submitContribution');
$router->addRoute('GET', '/contribution-success', 'ContributionController', 'showSuccess');

// API routes
$router->addRoute('POST', '/api/tasks/status', 'TaskController', 'updateStatus');
$router->addRoute('GET', '/api/projects/stats', 'ProjectController', 'getStats');

// Handle the request
try {
    $router->handleRequest();
} catch (Exception $e) {
    error_log($e->getMessage());
    include __DIR__ . '/../views/errors/500.php';
}