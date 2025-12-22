<?php

/**
 * Application Entry Point
 * All requests are routed through this file
 */

// Start output buffering
ob_start();

// Set timezone
date_default_timezone_set('Africa/Cairo'); // Egypt timezone (UTC+2)

// Set error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Define base URL for redirects (handles subdirectories)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$baseUrl = dirname($scriptName);
$baseUrl = rtrim($baseUrl, '/');
if (empty($baseUrl)) {
    $baseUrl = '';
}
define('BASE_URL', $baseUrl);

// Load helper functions
require_once BASE_PATH . '/helpers/functions.php';

// Autoload core classes
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/app/core/Router.php';

// Autoload factories
require_once BASE_PATH . '/app/factories/UserFactory.php';

// Initialize router
$router = new Router();

// Define routes for Authentication Module
$router->get('/', 'AuthController', 'home');
$router->get('/home', 'AuthController', 'home');
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'register');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');
$router->post('/logout', 'AuthController', 'logout');

// Define routes for Admin Module
$router->get('/admin/dashboard', 'AdminController', 'dashboard');
$router->get('/admin/elections', 'AdminController', 'elections');
$router->get('/admin/voters', 'AdminController', 'voters');
$router->get('/admin/monitor', 'AdminController', 'monitor');
$router->get('/admin/monitor-data/{id}', 'AdminController', 'monitorData');
$router->post('/admin/save-election', 'AdminController', 'saveElection');
$router->post('/admin/update-election', 'AdminController', 'updateElection');
$router->get('/admin/get-election/{id}', 'AdminController', 'getElection');
$router->post('/admin/delete-election/{id}', 'AdminController', 'deleteElection');
$router->post('/admin/add-voter', 'AdminController', 'addVoter');
$router->post('/admin/remove-voter', 'AdminController', 'removeVoter');

// Define routes for Results Module
$router->get('/admin/generate-results', 'ResultController', 'generateResults');
$router->post('/admin/generate-results', 'ResultController', 'generateResultsAction');
$router->get('/admin/results', 'ResultController', 'viewResults');
$router->get('/admin/results-data', 'ResultController', 'getResultsData');
$router->get('/admin/export-results', 'ResultController', 'exportResults');
$router->get('/voter/results', 'ResultController', 'voterResults');
$router->get('/voter/results-data', 'ResultController', 'getVoterResultsData');

// Define routes for Voter Module
$router->get('/voter/dashboard', 'VoterController', 'dashboard');
$router->get('/voter/ballot', 'VoterController', 'ballot');
$router->post('/voter/cast-vote', 'VoterController', 'castVote');
$router->get('/voter/verify', 'VoterController', 'verify');
$router->get('/voter/results', 'VoterController', 'results');

// Dispatch the request
$router->dispatch();

// Flush output buffer
ob_end_flush();

