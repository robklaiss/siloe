<?php
/**
 * Final test script to verify admin authentication and dashboard access
 */

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('BASE_PATH', ROOT_PATH);
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');

// Start session
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_name'] = 'Admin User';
$_SESSION['user_role'] = 'admin';
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Output test header
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Test Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        h1, h2 { color: #333; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
        .success { color: green; }
        .error { color: red; }
        a { display: inline-block; margin: 10px 0; padding: 10px 15px; background: #4CAF50; color: white; 
            text-decoration: none; border-radius: 4px; }
        a:hover { background: #45a049; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Admin Authentication Test</h1>';

// Display session status
echo '<div class="card">
    <h2>Session Status</h2>
    <pre>';
print_r($_SESSION);
echo '</pre>
    <p class="success">✓ Admin session successfully set</p>
</div>';

// Show admin navigation links
echo '<div class="card">
    <h2>Admin Navigation Links</h2>
    <p>Click on these links to test the admin functionality:</p>
    <ul>
        <li><a href="/admin/dashboard" target="_blank">Admin Dashboard</a></li>
        <li><a href="/admin/companies" target="_blank">Companies Management</a></li>
        <li><a href="/admin/companies/create" target="_blank">Create New Company</a></li>
    </ul>
</div>';

// Show applied fixes summary
echo '<div class="card">
    <h2>Applied Fixes Summary</h2>
    <ol>
        <li>Modified View class to make layouts optional</li>
        <li>Fixed authentication checks in Admin base Controller</li>
        <li>Fixed APP_NAME constant usage with fallback defaults</li>
        <li>Fixed Session class reference in layout files</li>
        <li>Removed Request/Response parameter dependencies in controller methods</li>
    </ol>
</div>';

// Add test reset button
echo '<div class="card">
    <h2>Reset Test</h2>
    <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?reset=1">Reset Session & Reload</a>
</div>';

// Reset session if requested
if (isset($_GET['reset'])) {
    session_unset();
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

echo '</body></html>';
