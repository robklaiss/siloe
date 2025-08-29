<?php
/**
 * Verify Login Functionality
 * 
 * This script verifies if the login functionality is working
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Clear any existing session data
session_unset();
session_destroy();
session_start();

echo "<h1>Siloe Login Verification</h1>";

// Function to log messages
function log_message($message, $type = 'info') {
    $color = 'black';
    if ($type == 'success') $color = 'green';
    if ($type == 'error') $color = 'red';
    if ($type == 'warning') $color = 'orange';
    
    echo "<p style='color:$color'>$message</p>";
}

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
    log_message("Database file not found!", 'error');
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    log_message("Successfully connected to the database", 'success');
    
    // Test login with admin credentials
    $email = 'admin@siloe.com';
    $password = 'admin123';
    
    log_message("Testing login with: $email / $password");
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        log_message("User not found!", 'error');
        exit;
    }
    
    log_message("User found: " . $user['name']);
    
    if (password_verify($password, $user['password'])) {
        log_message("Password verification successful!", 'success');
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        log_message("Session variables set", 'success');
        log_message("Session ID: " . session_id());
        
        // Display session data
        echo "<h2>Session Data:</h2>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        log_message("Login verification completed successfully", 'success');
        log_message("You can now log in to the system with:");
        log_message("Email: $email");
        log_message("Password: $password");
    } else {
        log_message("Password verification failed!", 'error');
        log_message("Stored hash: " . substr($user['password'], 0, 20) . "...");
        
        // Generate a new hash for comparison
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        log_message("New hash: " . substr($new_hash, 0, 20) . "...");
    }
} catch (Exception $e) {
    log_message("Error: " . $e->getMessage(), 'error');
}
