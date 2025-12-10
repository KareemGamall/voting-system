<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';
    
    private $userId;
    private $name;
    private $email;
    private $password;
    private $isAdmin;
    private $isVoter;
    
    /**
     * Login user - Authenticate user with email and password
     * 
     * @param string $email
     * @param string $password
     * @return array|false User data if authenticated, false otherwise
     */
    public function login($email, $password) {
        $user = $this->findWhere('email = :email', ['email' => $email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Validate email format
     * 
     * @param string $email
     * @return bool
     */
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check if email exists in database
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        return $this->findByEmail($email) !== false;
    }
    
    /**
     * Register a new user
     * 
     * @param array $userData
     * @return bool
     */
    public function register($userData) {
        // Hash password before storing
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Generate unique user ID
        if (!isset($userData['user_id'])) {
            $userData['user_id'] = $this->generateUserId();
        }
        
        return $this->create($userData);
    }
    
    /**
     * Check if user is admin
     * 
     * @param int $userId
     * @return bool
     */
    public function isAdmin($userId) {
        $user = $this->find($userId);
        return $user && $user['is_admin'] == 1;
    }
    
    /**
     * Check if user is voter
     * 
     * @param int $userId
     * @return bool
     */
    public function isVoter($userId) {
        $user = $this->find($userId);
        return $user && $user['is_voter'] == 1;
    }
    
    /**
     * Find user by email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        return $this->findWhere('email = :email', ['email' => $email]);
    }
    
    /**
     * Find user by user_id
     * 
     * @param string $userId
     * @return array|false
     */
    public function findByUserId($userId) {
        return $this->findWhere('user_id = :user_id', ['user_id' => $userId]);
    }
    
    /**
     * Generate unique user ID
     * 
     * @return string
     */
    private function generateUserId() {
        return 'USR-' . time() . '-' . rand(1000, 9999);
    }
    
    /**
     * Update user password
     * 
     * @param int $id
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }
    
    /**
     * Get all voters
     * 
     * @return array
     */
    public function getVoters() {
        return $this->where('is_voter = 1');
    }
    
    /**
     * Get all admins
     * 
     * @return array
     */
    public function getAdmins() {
        return $this->where('is_admin = 1');
    }
    
    /**
     * Get users by role
     * 
     * @param string $role 'voter' or 'admin'
     * @return array
     */
    public function getByRole($role) {
        if ($role === 'voter') {
            return $this->where('is_voter = 1');
        } elseif ($role === 'admin') {
            return $this->where('is_admin = 1');
        }
        return [];
    }
}
