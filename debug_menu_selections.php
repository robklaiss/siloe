<?php
// Debug script for menu selections route
require_once __DIR__ . '/config/database.php';

// Test the EmployeeMenuSelection model
$selectionModel = new \App\Models\EmployeeMenuSelection();

echo "Testing getSelectionsByCompanyAndDate method...\n";
echo "Company ID: 12\n";
echo "Date: " . date('Y-m-d') . "\n\n";

$selections = $selectionModel->getSelectionsByCompanyAndDate(12, date('Y-m-d'));

echo "Number of selections found: " . count($selections) . "\n\n";

if (!empty($selections)) {
    echo "First selection data:\n";
    print_r($selections[0]);
} else {
    echo "No selections found. Let's check if company 12 exists and has employees...\n\n";
    
    // Check if company exists
    $companyModel = new \App\Models\Company();
    $company = $companyModel->getCompanyById(12);
    
    if ($company) {
        echo "Company found: " . $company['name'] . "\n";
        
        // Check employees
        $userModel = new \App\Models\User();
        $employees = $userModel->getEmployeesByCompany(12);
        echo "Number of employees: " . count($employees) . "\n";
        
        if (!empty($employees)) {
            echo "First employee: " . $employees[0]['name'] . " (" . $employees[0]['email'] . ")\n";
        }
    } else {
        echo "Company 12 not found!\n";
    }
}
?>
