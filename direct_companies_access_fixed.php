<?php
/**
 * Direct Companies Page Access - Fixed for Directory Structure
 * 
 * This script provides direct access to the companies page functionality
 * and works with the current directory structure
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

// Database connection
try {
    $db_path = $public_path . '/database/siloe.db';
    
    if (!file_exists($db_path)) {
        $db_path = $public_path . '/database/database.sqlite';
    }
    
    if (!file_exists($db_path)) {
        log_message("Database file not found at: $db_path", 'error');
        exit;
    }
    
    log_message("Using database at: $db_path", 'success');
    
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    log_message("Database connection established", 'success');
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set admin user session
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => 'admin@siloe.com']);
    $user = $stmt->fetch();
    
    if (!$user) {
        log_message("Admin user not found!", 'error');
        exit;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    log_message("Admin user session established", 'success');
    
    // Check if the CompanyController exists
    $controller_path = $public_path . '/app/Controllers/Admin/CompanyController.php';
    
    if (file_exists($controller_path)) {
        log_message("CompanyController found at: $controller_path", 'success');
    } else {
        log_message("CompanyController not found at: $controller_path", 'error');
    }
    
    // Check if the companies view exists
    $view_path = $public_path . '/app/views/admin/companies/index.php';
    
    if (file_exists($view_path)) {
        log_message("Companies view found at: $view_path", 'success');
    } else {
        log_message("Companies view not found at: $view_path", 'error');
    }
    
    // Get all companies directly from the database
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY name ASC");
    $companies = $stmt->fetchAll();
    
    log_message("Retrieved " . count($companies) . " companies", 'success');
    
    // Display companies in a table with Bootstrap styling
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .container { max-width: 1200px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Companies Management</h1>
        <div class="mb-3">
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCompanyModal">Create New Company</a>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Companies List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact Email</th>
                                <th>Contact Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
    
    foreach ($companies as $company) {
        echo '<tr>
                <td>' . htmlspecialchars($company['id']) . '</td>
                <td>' . htmlspecialchars($company['name']) . '</td>
                <td>' . htmlspecialchars($company['address'] ?? '') . '</td>
                <td>' . htmlspecialchars($company['contact_email'] ?? '') . '</td>
                <td>' . htmlspecialchars($company['contact_phone'] ?? '') . '</td>
                <td>' . ($company['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>') . '</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="#" class="btn btn-outline-primary">View</a>
                        <a href="#" class="btn btn-outline-secondary">Edit</a>
                        <a href="#" class="btn btn-outline-info">HR</a>
                    </div>
                </td>
            </tr>';
    }
    
    echo '</tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Create Company Modal -->
        <div class="modal fade" id="createCompanyModal" tabindex="-1" aria-labelledby="createCompanyModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCompanyModalLabel">Create New Company</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createCompanyForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address">
                            </div>
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email">
                            </div>
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="contact_phone" name="contact_phone">
                            </div>
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveCompany">Save Company</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("saveCompany").addEventListener("click", function() {
            alert("This is a demonstration. In a real application, this would save the company to the database.");
        });
    </script>
</body>
</html>';
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage(), 'error');
}
