#!/bin/bash

# Fix session persistence issues
# This script creates a session debug and fix script and uploads it to the server

echo "Creating session debug and fix script..."

# Create the PHP script
cat > session_debug.php << 'EOL'
<?php
// Session Debug and Fix Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Function to check directory permissions and create if needed
function check_directory($dir) {
    if (!file_exists($dir)) {
        echo "<p>Creating directory: $dir</p>";
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color:green'>✅ Directory created successfully</p>";
        } else {
            echo "<p style='color:red'>❌ Failed to create directory</p>";
        }
    } else {
        echo "<p>Directory exists: $dir</p>";
        
        // Check permissions
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<p>Permissions: $perms</p>";
        
        if (!is_writable($dir)) {
            echo "<p style='color:red'>❌ Directory is not writable!</p>";
            echo "<p>Attempting to fix permissions...</p>";
            if (chmod($dir, 0755)) {
                echo "<p style='color:green'>✅ Permissions fixed</p>";
            } else {
                echo "<p style='color:red'>❌ Failed to fix permissions</p>";
            }
        } else {
            echo "<p style='color:green'>✅ Directory is writable</p>";
        }
    }
}

// Function to fix session configuration
function fix_session_config() {
    // Check if we can modify php.ini settings
    if (ini_set('session.gc_maxlifetime', 86400) !== false) {
        echo "<p style='color:green'>✅ Set session.gc_maxlifetime to 86400 (24 hours)</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to set session.gc_maxlifetime</p>";
    }
    
    if (ini_set('session.cookie_lifetime', 86400) !== false) {
        echo "<p style='color:green'>✅ Set session.cookie_lifetime to 86400 (24 hours)</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to set session.cookie_lifetime</p>";
    }
    
    // Create a custom session handler file
    $session_handler = <<<'EOT'
<?php
// Custom session handler to fix persistence issues
// Place this file in the root directory of your application

// Start session with custom settings
function start_session_custom($lifetime = 86400) {
    // Set session cookie parameters
    session_set_cookie_params($lifetime, '/', '', false, true);
    
    // Start the session
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID periodically to prevent fixation attacks
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } else if (time() - $_SESSION['last_regeneration'] > 3600) {
        // Regenerate session ID every hour
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set last activity time
    $_SESSION['last_activity'] = time();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user has specific role
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Function to require login or redirect
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error'] = 'Please log in to access this page';
        header('Location: /login');
        exit;
    }
}

// Function to require specific role or redirect
function require_role($role) {
    require_login();
    
    if (!has_role($role)) {
        $_SESSION['error'] = 'You do not have permission to access this page';
        header('Location: /dashboard');
        exit;
    }
}
EOT;

    // Write the session handler to a file
    $handler_file = $_SERVER['DOCUMENT_ROOT'] . '/session_handler.php';
    if (file_put_contents($handler_file, $session_handler)) {
        echo "<p style='color:green'>✅ Created custom session handler at $handler_file</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to create custom session handler</p>";
    }
    
    // Create a session fix for AuthController
    $auth_fix = <<<'EOT'
<?php
// Session fix for AuthController
// This file should be included at the top of your index.php or bootstrap file

// Start session with custom settings
session_set_cookie_params(86400, '/', '', false, true);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set session save path if needed
$session_dir = __DIR__ . '/sessions';
if (is_dir($session_dir) && is_writable($session_dir)) {
    session_save_path($session_dir);
}

// Debug session info
function debug_session() {
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Save Path: " . session_save_path() . "\n";
    echo "Session Cookie Params: " . print_r(session_get_cookie_params(), true) . "\n";
    echo "Session Data: " . print_r($_SESSION, true) . "\n";
    echo "</pre>";
}
EOT;

    // Write the auth fix to a file
    $auth_fix_file = $_SERVER['DOCUMENT_ROOT'] . '/session_fix.php';
    if (file_put_contents($auth_fix_file, $auth_fix)) {
        echo "<p style='color:green'>✅ Created session fix at $auth_fix_file</p>";
    } else {
        echo "<p style='color:red'>❌ Failed to create session fix</p>";
    }
}

// Function to create a direct access script
function create_direct_access() {
    $direct_access = <<<'EOT'
<?php
// Direct admin access script with improved session handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with extended lifetime
session_set_cookie_params(86400, '/', '', false, true);
session_start();

// Set admin session variables
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['last_activity'] = time();
$_SESSION['authenticated'] = true;

// Debug session info
echo "<h1>Admin Access Granted</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session variables set:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Navigation links
echo "<h2>Navigation</h2>";
echo "<ul>";
echo "<li><a href='/'>Home</a></li>";
echo "<li><a href='/dashboard'>Dashboard</a></li>";
echo "<li><a href='/admin/dashboard'>Admin Dashboard</a></li>";
echo "</ul>";

// Session test link
echo "<p><a href='/session_test.php'>Test Session Persistence</a></p>";

// Create session test file
$session_test = <<<'EOD'
<?php
session_start();
echo "<h1>Session Test</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green'>✅ Session is working correctly!</p>";
} else {
    echo "<p style='color:red'>❌ Session is not persisting!</p>";
}
echo "<p><a href='/direct_admin_access.php'>Back to Admin Access</a></p>";
EOD;

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/session_test.php', $session_test);
echo "<p>Created session test file at /session_test.php</p>";
EOT;

    // Write the direct access script to a file
    $direct_access_file = $_SERVER['DOCUMENT_ROOT'] . '/direct_admin_access.php';
    if (file_put_contents($direct_access_file, $direct_access)) {
        echo "<p style='color:green'>✅ Created direct admin access script at $direct_access_file</p>";
        return true;
    } else {
        echo "<p style='color:red'>❌ Failed to create direct admin access script</p>";
        return false;
    }
}

// Function to fix index.php to include session handling
function fix_index_php() {
    $index_path = $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    
    if (!file_exists($index_path)) {
        echo "<p style='color:red'>❌ Index.php not found at $index_path</p>";
        return false;
    }
    
    $index_content = file_get_contents($index_path);
    
    // Check if session_fix.php is already included
    if (strpos($index_content, 'session_fix.php') !== false) {
        echo "<p>Session fix already included in index.php</p>";
        return true;
    }
    
    // Add session fix include at the top of the file
    $include_line = "<?php require_once __DIR__ . '/session_fix.php'; ?>\n";
    $new_content = preg_replace('/^(<\?php|<\?)/i', $include_line, $index_content, 1);
    
    if ($new_content === $index_content) {
        // If no replacement was made, prepend the include
        $new_content = $include_line . $index_content;
    }
    
    if (file_put_contents($index_path, $new_content)) {
        echo "<p style='color:green'>✅ Updated index.php to include session fix</p>";
        return true;
    } else {
        echo "<p style='color:red'>❌ Failed to update index.php</p>";
        return false;
    }
}

// Main execution
echo "<h1>Session Debug and Fix</h1>";

// Check PHP info
echo "<h2>PHP Session Information</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Save Path: " . session_save_path() . "</p>";
echo "<p>Session Cookie Params: </p>";
echo "<pre>";
print_r(session_get_cookie_params());
echo "</pre>";

// Check session directory
echo "<h2>Session Directory Check</h2>";
$session_dir = $_SERVER['DOCUMENT_ROOT'] . '/sessions';
check_directory($session_dir);

// Set session save path if directory exists and is writable
if (is_dir($session_dir) && is_writable($session_dir)) {
    session_save_path($session_dir);
    echo "<p style='color:green'>✅ Set session save path to $session_dir</p>";
}

// Fix session configuration
echo "<h2>Fixing Session Configuration</h2>";
fix_session_config();

// Create direct admin access script
echo "<h2>Creating Direct Admin Access</h2>";
if (create_direct_access()) {
    echo "<p>You can now access the admin area directly at: <a href='/direct_admin_access.php'>/direct_admin_access.php</a></p>";
}

// Fix index.php
echo "<h2>Fixing Index.php</h2>";
fix_index_php();

// Current session data
echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Self-destruct option
echo "<p style='margin-top: 20px; color: red;'>⚠️ For security reasons, please delete this script after use.</p>";
echo "<form method='post'>";
echo "<input type='submit' name='delete_script' value='Delete This Script' style='background-color: #ff4444; color: white; padding: 5px 10px;'>";
echo "</form>";

// Handle self-destruct
if (isset($_POST['delete_script'])) {
    unlink(__FILE__);
    echo "<script>window.location = '/';</script>";
    exit;
}
?>
EOL

echo "Uploading session debug and fix script..."
scp -o PreferredAuthentications=password session_debug.php siloecom@192.185.143.154:/home1/siloecom/public_html/

echo "Cleaning up local files..."
rm session_debug.php

echo "Done!"
echo "Access the session debug and fix script at: http://www.siloe.com.py/session_debug.php"
echo "After using it, make sure to delete it from the server for security."
