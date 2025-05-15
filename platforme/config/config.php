<?php



// Define environment
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Base path configuration
define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));
define('UPLOAD_PATH', BASE_PATH . '/views/frontoffice/createProject/uploads');

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'project_creation');  // Updated database name
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// URL configuration
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost/platforme');

// Session configuration
define('SESSION_LIFETIME', 7200); // 2 hours
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);

// Security configuration
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_ALGO', PASSWORD_ARGON2ID);

// Project settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('DEFAULT_PROJECT_IMAGE', 'default-project-image.jpg');

// Pagination settings
define('ITEMS_PER_PAGE', 12);


