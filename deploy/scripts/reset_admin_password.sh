#!/bin/bash

# Reset admin password script for Siloe
# This script creates a PHP file that resets the admin password and uploads it to the server

echo "Creating admin password reset script..."

# Create the PHP script
cat > admin_password_reset.php << 'EOL'
<?php
// Admin password reset script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try multiple database paths
$db_paths = [
    __DIR__ . '/database/siloe.db',
    __DIR__ . '/database/database.sqlite',
    __DIR__ . '/../database/siloe.db',
    __DIR__ . '/../database/database.sqlite',
    __DIR__ . '/../siloe/database/siloe.db',
    __DIR__ . '/../siloe/database/database.sqlite',
    '/home1/siloecom/siloe/database/siloe.db',
    '/home1/siloecom/siloe/database/database.sqlite',
    '/home1/siloecom/public_html/database/siloe.db',
    '/home1/siloecom/public_html/database/database.sqlite'
];

$db = null;
$used_path = null;

foreach ($db_paths as $path) {
    if (file_exists($path)) {
        try {
            $db = new PDO('sqlite:' . $path);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $used_path = $path;
            echo "<p>Successfully connected to database at: $path</p>";
            break;
        } catch (PDOException $e) {
            echo "<p>Failed to connect to database at $path: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Database file not found at: $path</p>";
    }
}

if (!$db) {
    die("<p>Could not connect to any database. Please check file paths and permissions.</p>");
}

// Check if admin user exists
$stmt = $db->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// New password to set
$new_password = 'Admin123!';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

if ($admin) {
    // Update existing admin
    $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    $result = $stmt->execute([
        ':password' => $hashed_password,
        ':id' => $admin['id']
    ]);
    
    if ($result) {
        echo "<h2>✅ Admin password reset successfully!</h2>";
        echo "<p>Admin email: " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p>New password: Admin123!</p>";
    } else {
        echo "<h2>❌ Failed to reset admin password.</h2>";
    }
} else {
    // Create new admin user
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, datetime('now'))");
    $result = $stmt->execute([
        ':name' => 'Administrator',
        ':email' => 'admin@example.com',
        ':password' => $hashed_password,
        ':role' => 'admin'
    ]);
    
    if ($result) {
        echo "<h2>✅ Admin user created successfully!</h2>";
        echo "<p>Admin email: admin@example.com</p>";
        echo "<p>Password: Admin123!</p>";
    } else {
        echo "<h2>❌ Failed to create admin user.</h2>";
    }
}

// Show all users for debugging
echo "<h3>Current Users in Database:</h3>";
$stmt = $db->query("SELECT id, name, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($users) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in the database.</p>";
}

// Provide login links
echo "<h3>Login Links:</h3>";
echo "<ul>";
echo "<li><a href='/login'>Standard Login</a></li>";
echo "<li><a href='/simple_login.php'>Simple Login</a></li>";
echo "<li><a href='/admin_access.php'>Direct Admin Access</a></li>";
echo "</ul>";

// Self-destruct option
echo "<p style='margin-top: 20px; color: red;'>⚠️ For security reasons, please delete this script after use.</p>";
echo "<form method='post'>";
echo "<input type='submit' name='delete_script' value='Delete This Script' style='background-color: #ff4444; color: white; padding: 5px 10px;'>";
echo "</form>";

// Handle self-destruct
if (isset($_POST['delete_script'])) {
    unlink(__FILE__);
    echo "<script>window.location = '/';</script>";
    exit;
}
?>
EOL

echo "Uploading password reset script to server..."
scp admin_password_reset.php siloecom@192.185.143.154:/home1/siloecom/public_html/

echo "Cleaning up local files..."
rm admin_password_reset.php

echo "Done!"
echo "Access the password reset script at: http://www.siloe.com.py/admin_password_reset.php"
echo "After using it, make sure to delete it from the server for security."
