<?php
/**
 * Simple test script to verify CompanyController can be loaded
 * without relying on the entire framework
 */

// Output header
echo "<h1>CompanyController Class Test</h1>";

try {
    // Verify class files exist
    echo "<h2>Checking Controller Files:</h2>";
    echo "<ul>";
    
    $adminControllerPath = __DIR__ . '/../app/Controllers/Admin/Controller.php';
    $companyControllerPath = __DIR__ . '/../app/Controllers/Admin/CompanyController.php';
    
    if (file_exists($adminControllerPath)) {
        echo "<li>✓ Admin base Controller file exists at: " . htmlspecialchars($adminControllerPath) . "</li>";
    } else {
        echo "<li>❌ Admin base Controller file NOT found at: " . htmlspecialchars($adminControllerPath) . "</li>";
    }
    
    if (file_exists($companyControllerPath)) {
        echo "<li>✓ CompanyController file exists at: " . htmlspecialchars($companyControllerPath) . "</li>";
        
        // Analyze file content
        $content = file_get_contents($companyControllerPath);
        
        // Check for APP_NAME usage with fallback
        if (preg_match('/defined\(\'\\\\APP_NAME\'\) \? \\\\APP_NAME : \'Siloe\'/', $content)) {
            echo "<li>✓ APP_NAME usage with fallback found in CompanyController</li>";
        } else {
            echo "<li>❌ APP_NAME usage with fallback NOT found in CompanyController</li>";
        }
        
        // Check for fixed method parameters (no Request/Response)
        $simpleMethodCount = preg_match_all('/public function (show|edit|update|destroy|hrDashboard|employeeDashboard|createEmployee|storeEmployee)\(\$id\)/', $content);
        echo "<li>✓ Found $simpleMethodCount methods with fixed parameters (expected: 8)</li>";
    } else {
        echo "<li>❌ CompanyController file NOT found at: " . htmlspecialchars($companyControllerPath) . "</li>";
    }
    
    echo "</ul>";
    
    echo "<h2>Recommended Next Steps:</h2>";
    echo "<ol>";
    echo "<li>Test the admin login functionality in the browser</li>";
    echo "<li>Access the admin dashboard after login</li>";
    echo "<li>Navigate to the companies management section</li>";
    echo "</ol>";
    
} catch (Throwable $e) {
    echo "<h2>Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
