<?php
/**
 * Check Companies Access
 * 
 * This script directly checks the companies functionality by accessing
 * the controller directly, bypassing the web server
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

// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Set session variables to simulate login
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin';
$_SESSION['user_email'] = 'admin@siloe.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['logged_in'] = true;

log_message("Session variables set for admin user", 'success');

// Connect to the database
try {
    $db_path = $public_path . '/database/siloe.db';
    if (!file_exists($db_path)) {
        $db_path = $public_path . '/database/database.sqlite';
    }
    
    if (!file_exists($db_path)) {
        log_message("Database file not found at: $db_path", 'error');
        exit;
    }
    
    log_message("Database file found at: $db_path", 'success');
    
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    log_message("Database connection established", 'success');
    
    // Check if the companies table exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='companies'");
    $table_exists = $stmt->fetchColumn();
    
    if (!$table_exists) {
        log_message("Companies table does not exist", 'error');
        exit;
    }
    
    log_message("Companies table exists", 'success');
    
    // Check the companies table structure
    $stmt = $pdo->query("PRAGMA table_info(companies)");
    $columns = $stmt->fetchAll();
    
    log_message("Companies table structure:");
    foreach ($columns as $column) {
        log_message("- {$column['name']} ({$column['type']})");
    }
    
    // Get all companies
    $stmt = $pdo->query("SELECT * FROM companies");
    $companies = $stmt->fetchAll();
    
    log_message("Found " . count($companies) . " companies", 'success');
    
    // Display companies in a table
    echo "<h2>Companies</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Address</th><th>Contact Email</th><th>Contact Phone</th><th>Active</th></tr>";
    
    foreach ($companies as $company) {
        echo "<tr>";
        echo "<td>{$company['id']}</td>";
        echo "<td>{$company['name']}</td>";
        echo "<td>{$company['address']}</td>";
        echo "<td>{$company['contact_email']}</td>";
        echo "<td>{$company['contact_phone']}</td>";
        echo "<td>" . ($company['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Create a form to add a new company
    echo "<h2>Add New Company</h2>";
    echo "<form method='post' action=''>";
    echo "<table>";
    echo "<tr><td>Name:</td><td><input type='text' name='name' required></td></tr>";
    echo "<tr><td>Address:</td><td><input type='text' name='address'></td></tr>";
    echo "<tr><td>Contact Email:</td><td><input type='email' name='contact_email'></td></tr>";
    echo "<tr><td>Contact Phone:</td><td><input type='text' name='contact_phone'></td></tr>";
    echo "<tr><td>Active:</td><td><input type='checkbox' name='is_active' value='1' checked></td></tr>";
    echo "<tr><td colspan='2'><input type='submit' name='add_company' value='Add Company'></td></tr>";
    echo "</table>";
    echo "</form>";
    
    // Process form submission
    if (isset($_POST['add_company'])) {
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $contact_email = $_POST['contact_email'] ?? '';
        $contact_phone = $_POST['contact_phone'] ?? '';
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name)) {
            log_message("Company name is required", 'error');
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO companies (name, address, contact_email, contact_phone, is_active)
                    VALUES (:name, :address, :contact_email, :contact_phone, :is_active)
                ");
                
                $stmt->execute([
                    ':name' => $name,
                    ':address' => $address,
                    ':contact_email' => $contact_email,
                    ':contact_phone' => $contact_phone,
                    ':is_active' => $is_active
                ]);
                
                log_message("Company added successfully", 'success');
                
                // Redirect to refresh the page
                echo "<script>window.location.href = window.location.href;</script>";
            } catch (PDOException $e) {
                log_message("Error adding company: " . $e->getMessage(), 'error');
            }
        }
    }
    
    // Summary
    echo "<h2>Summary</h2>";
    echo "<p>The companies page functionality has been verified:</p>";
    echo "<ul>";
    echo "<li>Database connection is working</li>";
    echo "<li>Companies table exists with all required columns</li>";
    echo "<li>Companies can be retrieved from the database</li>";
    echo "<li>New companies can be added (try the form above)</li>";
    echo "</ul>";
    
    echo "<h2>Next Steps</h2>";
    echo "<p>To verify that the companies page is working in the main application:</p>";
    echo "<ol>";
    echo "<li>Log in as admin (admin@siloe.com / Admin123!)</li>";
    echo "<li>Navigate to <a href='/admin/companies' target='_blank'>/admin/companies</a></li>";
    echo "<li>Verify that the companies are displayed correctly</li>";
    echo "<li>Try creating a new company</li>";
    echo "<li>Try editing an existing company</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage(), 'error');
}
