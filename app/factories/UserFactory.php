<?php

require_once __DIR__ . '/../models/User.php';

/**
 * User Factory
 * Factory Method Pattern - Creates User objects based on role
 */
class UserFactory {
    
    /**
     * Create a user based on role
     * Factory Method Pattern implementation
     * 
     * @param string $role 'admin' or 'voter'
     * @param array $userData User data array
     * @return array|false User data array on success, false on failure
     */
    public function createUser($role, $userData) {
        $userModel = new User();
        
        // Set role-specific defaults
        switch (strtolower($role)) {
            case 'admin':
                $userData['is_admin'] = 1;
                $userData['is_voter'] = 0;
                break;
                
            case 'voter':
            default:
                $userData['is_admin'] = 0;
                $userData['is_voter'] = 1;
                break;
        }
        
        // Register the user (password will be hashed in User model)
        $userId = $userModel->register($userData);
        
        if ($userId) {
            // Return the created user data
            return $userModel->find($userId);
        }
        
        return false;
    }
    
    /**
     * Create a voter user
     * Convenience method for creating voters
     * 
     * @param array $userData User data array
     * @return array|false User data array on success, false on failure
     */
    public function createVoter($userData) {
        return $this->createUser('voter', $userData);
    }
    
    /**
     * Create an admin user
     * Convenience method for creating admins
     * 
     * @param array $userData User data array
     * @return array|false User data array on success, false on failure
     */
    public function createAdmin($userData) {
        return $this->createUser('admin', $userData);
    }
}



