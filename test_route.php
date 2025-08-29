<?php
session_start();

// Set up basic session data to simulate logged-in admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';
$_SESSION['csrf_token'] = 'test_token';

// Include necessary files
require_once __DIR__ . '/app/Core/Controller.php';
require_once __DIR__ . '/app/Models/Company.php';
require_once __DIR__ . '/app/Models/EmployeeMenuSelection.php';
require_once __DIR__ . '/app/Controllers/Admin/CompanyController.php';

// Test the controller method directly
try {
    $controller = new \App\Controllers\Admin\CompanyController();
    
    echo "Testing todaysMenuSelections method for company ID 12...\n";
    
    // Call the method
    $result = $controller->todaysMenuSelections('12');
    
    if ($result) {
        echo "Method executed successfully\n";
        echo "Result type: " . gettype($result) . "\n";
    } else {
        echo "Method returned null/false\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
