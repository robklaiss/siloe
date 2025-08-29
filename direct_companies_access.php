<?php
/**
 * Direct Companies Page Access
 * 
 * This script provides direct access to the companies page functionality
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

// Database connection
try {
    $db_path = '/home1/siloecom/siloe/public/database/siloe.db';
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    log_message("Database connection established", 'success');
    
    // Start session
    session_start();
    
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
    
    // Get all companies directly from the database
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY name ASC");
    $companies = $stmt->fetchAll();
    
    log_message("Retrieved " . count($companies) . " companies", 'success');
    
    // Display companies in a table
    echo '<h1>Companies</h1>';
    echo '<a href="/admin/companies/create" class="btn btn-primary">Create New Company</a>';
    echo '<table border="1" cellpadding="5" style="border-collapse: collapse; margin-top: 20px;">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Name</th>';
    echo '<th>Address</th>';
    echo '<th>Contact Email</th>';
    echo '<th>Contact Phone</th>';
    echo '<th>Status</th>';
    echo '<th>Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($companies as $company) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($company['id']) . '</td>';
        echo '<td>' . htmlspecialchars($company['name']) . '</td>';
        echo '<td>' . htmlspecialchars($company['address'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($company['contact_email'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($company['contact_phone'] ?? '') . '</td>';
        echo '<td>' . ($company['is_active'] ? 'Active' : 'Inactive') . '</td>';
        echo '<td>';
        echo '<a href="/admin/companies/' . $company['id'] . '">View</a> | ';
        echo '<a href="/admin/companies/' . $company['id'] . '/edit">Edit</a> | ';
        echo '<a href="/admin/companies/' . $company['id'] . '/hr">HR Dashboard</a>';
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    // Create a new company form
    echo '<h2>Create New Company</h2>';
    echo '<form method="post" action="/admin/companies">';
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="name">Name:</label><br>';
    echo '<input type="text" id="name" name="name" required style="width: 300px; padding: 5px;">';
    echo '</div>';
    
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="address">Address:</label><br>';
    echo '<input type="text" id="address" name="address" style="width: 300px; padding: 5px;">';
    echo '</div>';
    
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="contact_email">Contact Email:</label><br>';
    echo '<input type="email" id="contact_email" name="contact_email" style="width: 300px; padding: 5px;">';
    echo '</div>';
    
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="contact_phone">Contact Phone:</label><br>';
    echo '<input type="text" id="contact_phone" name="contact_phone" style="width: 300px; padding: 5px;">';
    echo '</div>';
    
    echo '<div style="margin-bottom: 15px;">';
    echo '<label for="is_active">Status:</label><br>';
    echo '<select id="is_active" name="is_active" style="width: 300px; padding: 5px;">';
    echo '<option value="1">Active</option>';
    echo '<option value="0">Inactive</option>';
    echo '</select>';
    echo '</div>';
    
    echo '<button type="submit" style="padding: 8px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Create Company</button>';
    echo '</form>';
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage(), 'error');
}
