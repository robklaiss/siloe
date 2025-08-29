<?php
/**
 * Test script to verify CompanyController functionality
 */

// Define required constants
define('ROOT_PATH', dirname(__DIR__));
define('BASE_PATH', ROOT_PATH);
define('APP_PATH', ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');

// Include config file after constants are defined
require_once __DIR__ . '/../app/config/config.php';

// Include necessary core files
require_once APP_PATH . '/Core/View.php';
require_once APP_PATH . '/Core/Model.php';
require_once APP_PATH . '/Core/QueryBuilder.php';
require_once APP_PATH . '/Models/Company.php';
require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Models/EmployeeMenuSelection.php';
require_once APP_PATH . '/Controllers/Controller.php';
require_once APP_PATH . '/Controllers/Admin/Controller.php';
require_once APP_PATH . '/Controllers/Admin/CompanyController.php';

// Start session
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Create controller instance
$controller = new \App\Controllers\Admin\CompanyController();

// Test output
echo "<h1>CompanyController Test</h1>";
echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Test controller methods without rendering views
try {
    echo "<h2>Testing controller initialization:</h2>";
    echo "✓ CompanyController initialized successfully<br>";
    
    echo "<h2>Testing controller methods:</h2>";
    echo "<ul>";
    
    // Test method existence (without executing them)
    $methods = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
        'hrDashboard', 'employeeDashboard', 'createEmployee', 'storeEmployee'
    ];
    
    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "<li>✓ Method '{$method}' exists</li>";
        } else {
            echo "<li>❌ Method '{$method}' not found</li>";
        }
    }
    
    echo "</ul>";
} catch (Throwable $e) {
    echo "❌ Error testing controller: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Show links to admin area
echo "<h2>Navigation Links:</h2>";
echo "<ul>";
echo "<li><a href='/admin/dashboard'>Admin Dashboard</a></li>";
echo "<li><a href='/admin/companies'>Companies List</a></li>";
echo "<li><a href='/admin/companies/create'>Create Company</a></li>";
echo "</ul>";
