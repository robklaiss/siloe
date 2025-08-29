<?php

// Define ROOT_PATH (required for config)
define('ROOT_PATH', realpath(__DIR__ . '/../'));

// Include configuration
require_once ROOT_PATH . '/app/config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Manually set session variables as if logged in as admin
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Show all session data
echo "<h1>Admin Login Test</h1>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Show session cookie
echo "<h2>Session Cookie:</h2>";
echo "<pre>";
var_dump(session_name());
var_dump(session_id());
echo "</pre>";

// Provide links to test
echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='/admin/dashboard'>Go to Admin Dashboard</a></li>";
echo "<li><a href='/logout'>Logout</a></li>";
echo "</ul>";

// Debug server info
echo "<h2>Server Info:</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session cookie params: \n";
print_r(session_get_cookie_params());
echo "</pre>";

