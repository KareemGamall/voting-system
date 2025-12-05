<?php

/**
 * Helper Functions
 * General utility functions for the application
 */

/**
 * Generate a URL with base path
 * 
 * @param string $path URL path (e.g., '/login', '/register')
 * @return string Full URL with base path
 */
function url($path = '/') {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    
    // Ensure path starts with /
    if (empty($path) || $path[0] !== '/') {
        $path = '/' . $path;
    }
    
    return $baseUrl . $path;
}

/**
 * Generate an asset URL (for CSS, JS, images)
 * 
 * @param string $path Asset path (e.g., 'css/style.css')
 * @return string Full asset URL
 */
function asset($path) {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    return $baseUrl . '/public/' . $path;
}

