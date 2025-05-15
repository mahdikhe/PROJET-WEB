<?php
class Router {
    private $routes = [];
    private $basePath;
    private $params = [];

    public function __construct($basePath = '') {
        $this->basePath = $basePath;
    }

    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->basePath . $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function handleRequest() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash if present
        $requestPath = rtrim($requestPath, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestPath)) {
                try {
                    // Load and instantiate the controller
                    $controllerClass = $route['controller'];
                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller {$controllerClass} not found");
                    }
                    
                    $controller = new $controllerClass();
                    $action = $route['action'];
                    
                    if (!method_exists($controller, $action)) {
                        throw new Exception("Action {$action} not found in {$controllerClass}");
                    }
                    
                    // Call the controller action with any route parameters
                    return call_user_func_array([$controller, $action], $this->params);
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $this->handleError(500, 'Internal Server Error');
                    return;
                }
            }
        }
        
        // No route found - handle 404
        $this->handleError(404, 'Page Not Found');
    }

    private function matchPath($routePath, $requestPath) {
        // Convert route parameters to regex pattern
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $requestPath, $matches)) {
            // Store route parameters
            $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        
        return false;
    }

    private function handleError($code, $message) {
        http_response_code($code);
        
        $errorFile = __DIR__ . "/../views/errors/{$code}.php";
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo "<h1>Error {$code}</h1>";
            echo "<p>{$message}</p>";
        }
    }
}