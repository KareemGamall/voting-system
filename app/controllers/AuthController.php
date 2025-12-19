<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../factories/UserFactory.php';

/**
 * Authentication Controller
 * Handles user authentication: login, register, logout, and home page
 * Uses Factory Method Pattern for user creation
 */
class AuthController extends Controller {
    
    private $userModel;
    private $userFactory;
    
    /**
     * Constructor - Initialize User model and UserFactory
     */
    public function __construct() {
        $this->userModel = new User();
        $this->userFactory = new UserFactory();
    }
    
    /**
     * Home page - Display home page
     * Shows different content based on authentication status
     * 
     * @return void
     */
    public function home() {
        Session::start();
        
        $activeElections = [];
        $votedElectionIds = [];
        
        if (Session::isLoggedIn()) {
            require_once __DIR__ . '/../models/Election.php';
            $electionModel = new Election();
            $activeElections = $electionModel->getLatestElections(6);
            
            // Check which elections the voter has already voted in
            if (Session::isVoter()) {
                require_once __DIR__ . '/../models/Vote.php';
                $voteModel = new Vote();
                $userId = Session::get('user_id');
                
                foreach ($activeElections as $election) {
                    if ($voteModel->hasVoted($election['id'], $userId)) {
                        $votedElectionIds[] = $election['id'];
                    }
                }
            }
        }
        
        $data = [
            'title' => 'Home - Voting System',
            'isLoggedIn' => Session::isLoggedIn(),
            'isVoter' => Session::isVoter(),
            'isAdmin' => Session::isAdmin(),
            'user' => Session::getUser(),
            'activeElections' => $activeElections,
            'votedElectionIds' => $votedElectionIds,
            'flash' => $this->getFlash()
        ];
        
        $this->view('home', $data);
    }
    
    /**
     * Show login form
     * Redirects to home if already logged in
     * 
     * @return void
     */
    public function login() {
        Session::start();
        
        // Redirect if already logged in
        if (Session::isLoggedIn()) {
            $this->redirect('/');
        }
        
        // Handle POST request (form submission)
        if ($this->isPost()) {
            $this->handleLogin();
            return;
        }
        
        // Display login form (GET request)
        $data = [
            'title' => 'Login - Voting System',
            'flash' => $this->getFlash()
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Handle login form submission
     * Validates credentials and authenticates user
     * 
     * @return void
     */
    private function handleLogin() {
        // Get form data
        $email = trim($this->post('email') ?? '');
        $password = $this->post('password') ?? '';
        
        // Validate input
        $errors = $this->validateLoginInput($email, $password);
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('/login');
            return;
        }
        
        // Attempt to authenticate user
        $user = $this->userModel->login($email, $password);
        
        if ($user) {
            // Set user session
            Session::setUser($user);
            
            // Redirect based on user role
            if (Session::isAdmin()) {
                $this->setFlash('success', 'Welcome back, Admin!');
                $this->redirect('/admin/dashboard');
            } else {
                $this->setFlash('success', 'Welcome back!');
                $this->redirect('/');
            }
        } else {
            $this->setFlash('error', 'Invalid email or password. Please try again.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Validate login input
     * 
     * @param string $email
     * @param string $password
     * @return array Array of error messages
     */
    private function validateLoginInput($email, $password) {
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        
        return $errors;
    }
    
    /**
     * Show registration form
     * Redirects to home if already logged in
     * 
     * @return void
     */
    public function register() {
        Session::start();
        
        // Redirect if already logged in
        if (Session::isLoggedIn()) {
            $this->redirect('/');
        }
        
        // Handle POST request (form submission)
        if ($this->isPost()) {
            $this->handleRegister();
            return;
        }
        
        // Display registration form (GET request)
        $data = [
            'title' => 'Register - Voting System',
            'flash' => $this->getFlash()
        ];
        
        $this->view('auth/register', $data);
    }
    
    /**
     * Handle registration form submission
     * Validates data and creates new user account
     * 
     * @return void
     */
    private function handleRegister() {
        // Get form data
        $name = trim($this->post('name') ?? '');
        $email = trim($this->post('email') ?? '');
        $password = $this->post('password') ?? '';
        $confirmPassword = $this->post('confirm_password') ?? '';
        
        // Validate input
        $errors = $this->validateRegisterInput($name, $email, $password, $confirmPassword);
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('/register');
            return;
        }
        
        // Check if email already exists
        if ($this->userModel->findByEmail($email)) {
            $this->setFlash('error', 'Email already registered. Please use a different email or login.');
            $this->redirect('/register');
            return;
        }
        
        // Prepare user data
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $password // Will be hashed in User model via Factory
        ];
        
        // Use Factory Method Pattern to create user (defaults to voter)
        $user = $this->userFactory->createVoter($userData);
        
        if ($user) {
            // Remove password from user data before storing in session
            unset($user['password']);
            
            // Set user session
            Session::setUser($user);
            
            // Redirect based on user role
            if (Session::isAdmin()) {
                $this->setFlash('success', 'Registration successful! Welcome, Admin!');
                $this->redirect('/admin/dashboard');
            } else {
                $this->setFlash('success', 'Registration successful! Welcome to Voting System!');
                $this->redirect('/');
            }
        } else {
            $this->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('/register');
        }
    }
    
    /**
     * Validate registration input
     * 
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $confirmPassword
     * @return array Array of error messages
     */
    private function validateRegisterInput($name, $email, $password, $confirmPassword) {
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required.';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters long.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Name must not exceed 100 characters.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        
        if (empty($confirmPassword)) {
            $errors[] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        return $errors;
    }
    
    /**
     * Logout user
     * Clears session and redirects to home
     * 
     * @return void
     */
    public function logout() {
        Session::start();
        Session::destroy();
        $this->setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/');
    }
}

