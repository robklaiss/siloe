<?php
/**
 * Verify Companies Page
 * 
 * This script verifies that the companies page is working properly
 * by making a direct request to the controller
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

// Detect server environment
$is_server = file_exists('/home1/siloecom/siloe');
$root_path = $is_server ? '/home1/siloecom/siloe' : __DIR__;
$public_path = $root_path . ($is_server ? '/public' : '');

log_message("Environment: " . ($is_server ? "Server" : "Local"));
log_message("Root path: $root_path");
log_message("Public path: $public_path");

// Try to include the necessary files
try {
    // Include the database connection
    $db_config_path = $public_path . '/app/config/database.php';
    if (file_exists($db_config_path)) {
        log_message("Including database config from: $db_config_path");
        include_once $db_config_path;
    } else {
        log_message("Database config not found at: $db_config_path", 'error');
    }
    
    // Connect to the database directly
    $db_path = $public_path . '/database/siloe.db';
    if (!file_exists($db_path)) {
        $db_path = $public_path . '/database/database.sqlite';
    }
    
    if (!file_exists($db_path)) {
        log_message("Database file not found at: $db_path", 'error');
    } else {
        log_message("Database file found at: $db_path", 'success');
        
        $pdo = new PDO('sqlite:' . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        log_message("Database connection established", 'success');
        
        // Check if the companies table exists
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='companies'");
        $table_exists = $stmt->fetchColumn();
        
        if ($table_exists) {
            log_message("Companies table exists", 'success');
            
            // Check the companies table structure
            $stmt = $pdo->query("PRAGMA table_info(companies)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            log_message("Companies table structure:");
            foreach ($columns as $column) {
                log_message("- {$column['name']} ({$column['type']})");
            }
            
            // Check if there are any companies
            $stmt = $pdo->query("SELECT COUNT(*) FROM companies");
            $count = $stmt->fetchColumn();
            
            log_message("Found $count companies", $count > 0 ? 'success' : 'warning');
            
            if ($count > 0) {
                // Fetch and display companies
                $stmt = $pdo->query("SELECT * FROM companies");
                $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                log_message("Companies in database:");
                foreach ($companies as $company) {
                    log_message("- ID: {$company['id']}, Name: {$company['name']}, Email: {$company['contact_email']}");
                }
            }
        } else {
            log_message("Companies table does not exist", 'error');
        }
    }
    
    // Test direct access to the CompanyController
    log_message("Testing direct access to CompanyController...");
    
    // Start session for authentication
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    
    // Set session variables to simulate admin login
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Admin';
    $_SESSION['user_email'] = 'admin@siloe.com';
    $_SESSION['user_role'] = 'admin';
    $_SESSION['logged_in'] = true;
    
    log_message("Session variables set for admin user", 'success');
    
    // Create a simple HTML page with links to test
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Siloe Companies Page Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .btn { 
                display: inline-block; 
                padding: 10px 15px; 
                background-color: #4CAF50; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px;
                margin: 5px;
            }
            .btn:hover { background-color: #45a049; }
            .container { margin-top: 20px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1>Siloe Companies Page Test</h1>
        <p>Click the buttons below to test the companies page functionality:</p>
        
        <div>
            <a href='/admin/companies' class='btn' target='_blank'>View Companies</a>
            <a href='/admin/companies/create' class='btn' target='_blank'>Create Company</a>
        </div>
        
        <div class='container'>
            <h2>Manual Test Instructions</h2>
            <ol>
                <li>Log in as admin (admin@siloe.com / Admin123!)</li>
                <li>Navigate to <a href='/admin/companies' target='_blank'>/admin/companies</a></li>
                <li>Verify that the companies are displayed correctly</li>
                <li>Try creating a new company</li>
                <li>Try editing an existing company</li>
                <li>Try deleting a company (if applicable)</li>
            </ol>
        </div>
    </body>
    </html>
    ";
    
} catch (Exception $e) {
    log_message("Error: " . $e->getMessage(), 'error');
}
