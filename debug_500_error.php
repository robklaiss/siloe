<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start session and simulate admin login
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';

// Set up request environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/companies/12/hr/menu-selections/today';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';

echo "Testing menu selections route...\n";

try {
    // Test the models first
    require_once __DIR__ . '/config/database.php';
    
    echo "1. Testing database connection...\n";
    $db = getDbConnection();
    echo "   Database connected successfully\n";
    
    echo "2. Testing Company model...\n";
    require_once __DIR__ . '/app/Models/Company.php';
    $companyModel = new \App\Models\Company();
    $company = $companyModel->getCompanyById(12);
    echo "   Company model works: " . ($company ? $company['name'] : 'not found') . "\n";
    
    echo "3. Testing User model...\n";
    require_once __DIR__ . '/app/Models/User.php';
    $userModel = new \App\Models\User();
    echo "   User model instantiated successfully\n";
    
    echo "4. Testing EmployeeMenuSelection model...\n";
    require_once __DIR__ . '/app/Models/EmployeeMenuSelection.php';
    $selectionModel = new \App\Models\EmployeeMenuSelection();
    $selections = $selectionModel->getSelectionsByCompanyAndDate(12, date('Y-m-d'));
    echo "   Selection model works, found " . count($selections) . " selections\n";
    
    echo "5. Testing Controller...\n";
    require_once __DIR__ . '/app/Core/Router.php';
    require_once __DIR__ . '/app/Core/Controller.php';
    require_once __DIR__ . '/app/Core/Request.php';
    require_once __DIR__ . '/app/Core/Response.php';
    require_once __DIR__ . '/app/Core/Session.php';
    require_once __DIR__ . '/app/Controllers/Admin/CompanyController.php';
    
    $router = new \App\Core\Router();
    $controller = new \App\Controllers\Admin\CompanyController($router);
    echo "   Controller instantiated successfully\n";
    
    echo "6. Testing controller method...\n";
    ob_start();
    $result = $controller->todaysMenuSelections('12');
    $output = ob_get_clean();
    echo "   Method executed, output length: " . strlen($output) . "\n";
    
    if (strlen($output) > 0) {
        echo "   First 200 chars of output:\n";
        echo "   " . substr($output, 0, 200) . "...\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
