<?php

// Define ROOT_PATH (required for config)
define('ROOT_PATH', realpath(__DIR__ . '/../'));

// Include configuration and required files
require_once ROOT_PATH . '/app/config/config.php';
require_once ROOT_PATH . '/app/Core/View.php';

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
echo "<h1>Admin Dashboard Test</h1>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Try to load and render the admin dashboard directly
echo "<h2>Admin Dashboard Content:</h2>";
try {
    // Set the view path
    \App\Core\View::setViewPath(ROOT_PATH . '/app/views/');
    
    // Get base admin controller
    require_once ROOT_PATH . '/app/Controllers/Controller.php';
    require_once ROOT_PATH . '/app/Controllers/Admin/Controller.php';
    require_once ROOT_PATH . '/app/Controllers/Admin/DashboardController.php';
    
    // Instantiate controller and call index method
    $controller = new \App\Controllers\Admin\DashboardController();
    $dashboardView = $controller->index();
    
    // Output the rendered view
    echo $dashboardView;
} catch (Exception $e) {
    echo "<div style='color: red; border: 2px solid red; padding: 10px;'>";
    echo "<h3>Error Rendering Dashboard:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
