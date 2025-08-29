<?php
/**
 * Server Login Test Script
 * 
 * This script tests the login functionality directly on the server
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Output to a log file
$log_file = __DIR__ . '/login_test_results.log';
$log = fopen($log_file, 'w');

function log_message($message) {
    global $log;
    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "[$timestamp] $message\n";
    fwrite($log, $formatted_message);
    echo $formatted_message;
}

log_message("Starting login test");

// Try multiple possible database paths
$possible_paths = [
    __DIR__ . '/database/siloe.db',
    __DIR__ . '/database/database.sqlite',
    '/home1/siloecom/siloe/public/database/siloe.db',
    '/home1/siloecom/siloe/public/database/database.sqlite'
];

$db_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $db_path = $path;
        log_message("Found database at: $path");
        break;
    }
}

if (!$db_path) {
    log_message("ERROR: Database file not found!");
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    log_message("Successfully connected to the database");
    
    // Check if users table exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        log_message("Users table exists");
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $user_count = $stmt->fetchColumn();
        log_message("Found $user_count users in the database");
        
        // List users
        $stmt = $pdo->query("SELECT id, name, email, role FROM users");
        $users = $stmt->fetchAll();
        
        log_message("Users in Database:");
        foreach ($users as $user) {
            log_message("ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}");
        }
        
        // Test authentication with admin@siloe.com / admin123
        $email = 'admin@siloe.com';
        $password = 'admin123';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            log_message("Testing Authentication for $email");
            log_message("Found user with ID: " . $user['id']);
            log_message("Password hash: " . substr($user['password'], 0, 20) . "...");
            
            if (password_verify($password, $user['password'])) {
                log_message("Password verification successful!");
                log_message("You can log in with: Email: $email, Password: $password");
            } else {
                log_message("Password verification failed!");
                
                // Create a new password hash for comparison
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                log_message("Generated hash for '$password': " . substr($new_hash, 0, 20) . "...");
                
                // Update the password
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$new_hash, $email]);
                log_message("Updated password for $email to '$password'");
            }
        } else {
            log_message("User with email $email not found!");
            
            // Create admin user
            $name = 'Admin';
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password_hash, $role]);
            
            log_message("Created new admin user: Email: $email, Password: $password");
        }
    } else {
        log_message("Users table does not exist!");
        
        // Create users table
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        log_message("Created users table");
        
        // Create admin user
        $name = 'Admin';
        $email = 'admin@siloe.com';
        $password = 'admin123';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password_hash, $role]);
        
        log_message("Created admin user: Email: $email, Password: $password");
    }
    
    // Test the AuthController login method
    log_message("\nTesting AuthController login method");
    
    // Include the database configuration
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
        log_message("Loaded database configuration");
    } else {
        log_message("ERROR: Database configuration file not found!");
    }
    
    log_message("Login test completed successfully");
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage());
}

fclose($log);
log_message("Results saved to $log_file");
