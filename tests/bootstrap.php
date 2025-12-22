<?php

/**
 * PHPUnit Bootstrap File
 * Initializes testing environment
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('TESTING', true);

// Load core files
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Model.php';
require_once BASE_PATH . '/app/core/Session.php';
require_once BASE_PATH . '/helpers/functions.php';

// Load test helpers
require_once __DIR__ . '/Helpers/TestCase.php';
require_once __DIR__ . '/Helpers/DatabaseHelper.php';
require_once __DIR__ . '/Helpers/TestDataFactory.php';

// Set up test database configuration
$_ENV['DB_HOST'] = getenv('DB_HOST') ?: 'localhost';
$_ENV['DB_NAME'] = getenv('DB_NAME') ?: 'voting_system_test';
$_ENV['DB_USER'] = getenv('DB_USER') ?: 'root';
$_ENV['DB_PASS'] = getenv('DB_PASS') ?: '';

// Start output buffering to prevent session headers issues
ob_start();

echo "\n✓ Test environment initialized\n";
echo "✓ Database: {$_ENV['DB_NAME']}\n";
echo "✓ Running tests...\n\n";
