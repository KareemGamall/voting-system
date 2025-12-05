<?php

/**
 * Database Migration Runner
 * This script runs all migration files in the migrations folder
 */

require_once __DIR__ . '/../config/database.php';

try {
    // Get database configuration
    $config = require __DIR__ . '/../config/database.php';
    
    // Create PDO connection
    $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    echo "Connected to MySQL server...\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$config['database']}' created or already exists.\n";
    
    // Use the database
    $pdo->exec("USE {$config['database']}");
    echo "Using database '{$config['database']}'...\n\n";
    
    // Get all migration files
    $migrationPath = __DIR__ . '/migrations/';
    $migrationFiles = glob($migrationPath . '*.sql');
    
    // Sort migration files
    sort($migrationFiles);
    
    if (empty($migrationFiles)) {
        echo "No migration files found.\n";
        exit;
    }
    
    echo "Running migrations...\n";
    echo str_repeat('-', 50) . "\n";
    
    // Run each migration
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        echo "Running migration: $filename\n";
        
        // Read SQL file
        $sql = file_get_contents($file);
        
        // Execute SQL
        try {
            $pdo->exec($sql);
            echo "✓ Migration successful: $filename\n\n";
        } catch (PDOException $e) {
            echo "✗ Migration failed: $filename\n";
            echo "Error: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo str_repeat('-', 50) . "\n";
    echo "All migrations completed!\n\n";
    
    // Ask if user wants to seed sample data
    echo "Do you want to seed sample data? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim(strtolower($line));
    fclose($handle);
    
    if ($answer === 'yes' || $answer === 'y') {
        echo "\nSeeding sample data...\n";
        $seedFile = __DIR__ . '/seeds/sample_data.sql';
        
        if (file_exists($seedFile)) {
            $sql = file_get_contents($seedFile);
            try {
                $pdo->exec($sql);
                echo "✓ Sample data seeded successfully!\n";
            } catch (PDOException $e) {
                echo "✗ Seeding failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "✗ Sample data file not found.\n";
        }
    }
    
    echo "\nDatabase setup complete!\n";
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}
