<?php

/**
 * Session Management Class
 * Handles session operations for authentication
 */
class Session {
    
    /**
     * Start session if not already started
     * 
     * @return void
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Set a session variable
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session variable
     * 
     * @param string $key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session variable exists
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session variable
     * 
     * @param string $key
     * @return void
     */
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Destroy entire session
     * 
     * @return void
     */
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
    }
    
    /**
     * Set user session after login
     * 
     * @param array $user User data array
     * @return void
     */
    public static function setUser($user) {
        self::set('user', $user);
        self::set('user_id', $user['id']);
        self::set('is_logged_in', true);
    }
    
    /**
     * Get current logged in user
     * 
     * @return array|null User data or null
     */
    public static function getUser() {
        return self::get('user', null);
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function isLoggedIn() {
        return self::get('is_logged_in', false);
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public static function isAdmin() {
        $user = self::getUser();
        return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
    }
    
    /**
     * Check if user is voter
     * 
     * @return bool
     */
    public static function isVoter() {
        $user = self::getUser();
        return $user && isset($user['is_voter']) && $user['is_voter'] == 1;
    }
    
    /**
     * Get user ID
     * 
     * @return int|null
     */
    public static function getUserId() {
        return self::get('user_id', null);
    }
    
    /**
     * Clear user session (logout)
     * 
     * @return void
     */
    public static function clearUser() {
        self::remove('user');
        self::remove('user_id');
        self::remove('is_logged_in');
    }
}

