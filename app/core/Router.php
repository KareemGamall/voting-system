<?php

/**
 * Simple Router Class
 * Handles URL routing to controllers
 */
class Router {
    private $routes = [];
    
    /**
     * Add a route
     * 
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path URL path
     * @param string $controller Controller class name
     * @param string $action Method name in controller
     */
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Add GET route
     */
    public function get($path, $controller, $action) {
        $this->add('GET', $path, $controller, $action);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $controller, $action) {
        $this->add('POST', $path, $controller, $action);
    }
    
    /**
     * Dispatch request to appropriate controller
     */
    public function dispatch() {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];
        
        file_put_contents('debug_log.txt', "DEBUG Router: URI=" . $uri . ", Method=" . $method . "\n", FILE_APPEND);
        
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $uri, $method)) {
                file_put_contents('debug_log.txt', "DEBUG Router: Route matched - " . $route['controller'] . "::" . $route['action'] . "\n", FILE_APPEND);
                
                $controllerName = $route['controller'];
                $action = $route['action'];
                
                // Check if controller file exists
                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    if (class_exists($controllerName)) {
                        $controller = new $controllerName();
                        
                        if (method_exists($controller, $action)) {
                            $controller->$action();
                            return;
                        }
                    }
                }
            }
        }
        
        file_put_contents('debug_log.txt', "DEBUG Router: No route matched, calling 404\n", FILE_APPEND);
        // 404 - Route not found
        $this->handle404();
    }
    
    /**
     * Get URI from request
     */
    private function getUri() {
        // First, check if route is passed as query parameter (fallback for when mod_rewrite is disabled)
        if (isset($_GET['route'])) {
            $uri = $_GET['route'];
            // Ensure URI starts with /
            if (empty($uri) || $uri[0] !== '/') {
                $uri = '/' . $uri;
            }
            return $uri;
        }
        
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Get the script name (e.g., /software project/voting-system/public/index.php)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        
        // Get the directory of the script (e.g., /software project/voting-system/public)
        $basePath = dirname($scriptName);
        
        // Normalize base path (remove trailing slash, but keep leading slash)
        $basePath = rtrim($basePath, '/');
        if (empty($basePath)) {
            $basePath = '/';
        }
        
        // URL decode the URI to handle encoded characters (like %20 for spaces)
        $decodedUri = urldecode($uri);
        
        // Remove base path from decoded URI if it exists
        if ($basePath !== '/' && strpos($decodedUri, $basePath) === 0) {
            $uri = substr($decodedUri, strlen($basePath));
        }
        
        // If URI is empty or just '/', make it '/'
        if (empty($uri) || $uri === '/') {
            $uri = '/';
        } else {
            // Ensure URI starts with /
            if ($uri[0] !== '/') {
                $uri = '/' . $uri;
            }
        }
        
        return $uri;
    }
    
    /**
     * Check if route matches URI and extract parameters
     */
    private function matchRoute($route, $uri, $method) {
        // Check method
        if ($route['method'] !== $method && $route['method'] !== 'ANY') {
            return false;
        }
        
        // Exact match
        if ($route['path'] === $uri) {
            return true;
        }
        
        // Pattern matching with parameter extraction
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $route['path']);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameter names from route
            preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route['path'], $paramNames);
            
            // Store parameters in $_GET for controller access
            if (isset($paramNames[1])) {
                for ($i = 0; $i < count($paramNames[1]); $i++) {
                    if (isset($matches[$i + 1])) {
                        $_GET[$paramNames[1][$i]] = $matches[$i + 1];
                    }
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle 404 error
     */
    private function handle404() {
        http_response_code(404);
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $requestedUri = $this->getUri();
        $debugInfo = '';
        
        // Show debug info in development mode
        if (defined('BASE_PATH') && file_exists(BASE_PATH . '/.env')) {
            // Only show if .env exists (development)
        } else {
            // Show basic debug info
            $debugInfo = "<p style='color: #666; font-size: 12px; margin-top: 20px;'>Requested URI: <code>{$requestedUri}</code></p>";
        }
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>404 - Page Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                h1 { color: #667eea; }
                code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
            </style>
        </head>
        <body>
            <h1>404 - Page Not Found</h1>
            <p>The page you are looking for does not exist.</p>
            {$debugInfo}
            <a href='{$baseUrl}/'>Go to Home</a>
        </body>
        </html>";
    }
}

