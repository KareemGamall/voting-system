<?php

/**
 * Base Controller Class
 * All controllers extend this class
 */
abstract class Controller {
    
    /**
     * Render a view file
     * 
     * @param string $view View file name (without .php extension)
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view($view, $data = []) {
        // Extract data array to variables
        extract($data);
        
        // Include the view file
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View file not found: {$view}.php");
        }
    }
    
    /**
     * Generate a URL with base path (helper method for views)
     * 
     * @param string $path URL path
     * @return string Full URL
     */
    protected function url($path = '/') {
        return url($path);
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect($url) {
        // If URL doesn't start with http:// or https://, prepend base URL
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $url = $baseUrl . $url;
        }
        header("Location: " . $url);
        exit();
    }
    
    /**
     * Set a flash message in session
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     * @return void
     */
    protected function setFlash($type, $message) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get and clear flash message from session
     * 
     * @return array|null Flash message array or null
     */
    protected function getFlash() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        
        return null;
    }
    
    /**
     * Check if request is POST
     * 
     * @return bool
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     * 
     * @return bool
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Get POST data
     * 
     * @param string $key Optional key to get specific value
     * @return mixed
     */
    protected function post($key = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? null;
    }
    
    /**
     * Get GET data
     * 
     * @param string $key Optional key to get specific value
     * @return mixed
     */
    protected function get($key = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }
}

