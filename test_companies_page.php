<?php
/**
 * Test Companies Page Access
 * 
 * This script tests access to the companies page with authentication
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    
    // Check if the admin user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => 'admin@siloe.com']);
    $user = $stmt->fetch();
    
    if (!$user) {
        log_message("Admin user not found!", 'error');
        exit;
    }
    
    log_message("Found admin user: " . $user['name'], 'success');
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set session variables to simulate login
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    log_message("Session variables set for admin user", 'success');
    
    // Check if the companies table exists and has the required columns
    $stmt = $pdo->query("PRAGMA table_info(companies)");
    $columns = $stmt->fetchAll();
    
    log_message("Companies table structure:");
    foreach ($columns as $column) {
        log_message("- {$column['name']} ({$column['type']})");
    }
    
    // Check if there are any companies
    $stmt = $pdo->query("SELECT COUNT(*) FROM companies");
    $count = $stmt->fetchColumn();
    
    log_message("Found $count companies", $count > 0 ? 'success' : 'warning');
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM companies LIMIT 5");
        $companies = $stmt->fetchAll();
        
        log_message("Companies in database:");
        foreach ($companies as $company) {
            log_message("- ID: {$company['id']}, Name: {$company['name']}");
        }
    }
    
    // Check if the CompanyController exists and has the required methods
    $controller_path = __DIR__ . '/app/Controllers/Admin/CompanyController.php';
    $server_controller_path = '/home1/siloecom/siloe/public/app/Controllers/Admin/CompanyController.php';
    
    if (file_exists($controller_path)) {
        log_message("CompanyController found at: $controller_path", 'success');
    } elseif (file_exists($server_controller_path)) {
        log_message("CompanyController found at: $server_controller_path", 'success');
    } else {
        log_message("CompanyController not found!", 'error');
    }
    
    // Check if the companies view exists
    $view_path = __DIR__ . '/app/views/admin/companies/index.php';
    $server_view_path = '/home1/siloecom/siloe/public/app/views/admin/companies/index.php';
    
    if (file_exists($view_path)) {
        log_message("Companies view found at: $view_path", 'success');
    } elseif (file_exists($server_view_path)) {
        log_message("Companies view found at: $server_view_path", 'success');
    } else {
        log_message("Companies view not found!", 'error');
    }
    
    // Test URL access
    log_message("Testing URL access to /admin/companies...");
    log_message("Note: This test requires manual verification by visiting https://www.siloe.com.py/admin/companies in a browser after logging in as admin.");
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage(), 'error');
}
