<?php
/**
 * Database Initialization Script for Siloe
 * 
 * This script initializes the SQLite database with the required tables
 * and creates an initial admin user.
 * 
 * Usage: php database/init_db.php
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path
define('ROOT_PATH', dirname(__DIR__));

echo "Siloe Database Initialization\n";
echo "==========================\n\n";

// Check if SQLite is available
if (!extension_loaded('pdo_sqlite')) {
    die("Error: SQLite PDO extension is not enabled.\n");
}

// Load configuration
$configPath = ROOT_PATH . '/app/config/config.php';
if (!file_exists($configPath)) {
    die("Error: Configuration file not found at $configPath\n");}

require_once $configPath;

// Check if database directory exists
if (!is_dir(dirname(DB_PATH))) {
    if (!mkdir(dirname(DB_PATH), 0755, true)) {
        die("Error: Failed to create database directory\n");
    }
}

try {
    // Connect to SQLite database (this will create the file if it doesn't exist)
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    echo "• Connected to database: " . DB_PATH . "\n";
    
    // Read and execute the schema
    $schemaPath = __DIR__ . '/migrations/001_initial_schema.sql';
    if (!file_exists($schemaPath)) {
        die("Error: Schema file not found at $schemaPath\n");
    }
    
    $sql = file_get_contents($schemaPath);
    
    // Split the SQL into individual statements
    $queries = array_filter(
        array_map('trim', 
            preg_split('/;\s*\n/', $sql)
        ),
        'strlen'
    );
    
    // Execute each query
    $successCount = 0;
    $errorCount = 0;
    
    $pdo->beginTransaction();
    
    try {
        foreach ($queries as $query) {
            try {
                $pdo->exec($query);
                $successCount++;
            } catch (PDOException $e) {
                // Skip duplicate table errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
                $successCount++;
            }
        }
        
        $pdo->commit();
        
        echo "• Successfully executed $successCount SQL statements\n";
        echo "• Database initialized successfully!\n\n";
        
        // Display admin credentials
        echo "Admin User Created:\n";
        echo "------------------\n";
        echo "Email: admin@siloe.com\n";
        echo "Password: admin123\n\n";
        
        echo "Important: Change the default password after first login!\n";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

echo "\nDatabase setup complete!\n";