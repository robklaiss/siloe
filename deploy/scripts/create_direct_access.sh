#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Creating direct admin access script..."

# Create a direct admin access script
cat > /tmp/admin_access.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Direct Admin Access</h1>";

// Define paths to check
$possiblePaths = [
    __DIR__ . '/../../database/siloe.db',
    __DIR__ . '/../database/siloe.db',
    __DIR__ . '/database/siloe.db',
    '/home1/siloecom/siloe/public/database/siloe.db',
    '/home1/siloecom/siloe/public/public/database/siloe.db'
];

// Find the first existing database
$dbPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $dbPath = $path;
        echo "<p>Found database at: $dbPath</p>";
        break;
    }
}

if (!$dbPath) {
    echo "<p class='error'>No database found!</p>";
} else {
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if users table exists
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('users', $tables)) {
            echo "<p class='error'>Users table not found!</p>";
        } else {
            // Get admin user
            $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@example.com' AND role = 'admin'");
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo "<p class='error'>Admin user not found!</p>";
                
                // Create admin user
                echo "<p>Creating admin user...</p>";
                $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
                $stmt->execute([
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'password' => password_hash('password', PASSWORD_DEFAULT),
                    'role' => 'admin'
                ]);
                
                // Get the created user
                $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@example.com'");
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    echo "<p class='error'>Failed to create admin user!</p>";
                    exit;
                }
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            echo "<p class='success'>Admin session created successfully!</p>";
            echo "<p>User ID: {$user['id']}</p>";
            echo "<p>Name: {$user['name']}</p>";
            echo "<p>Email: {$user['email']}</p>";
            echo "<p>Role: {$user['role']}</p>";
            
            // Create navigation links
            echo "<div class='navigation'>";
            echo "<h2>Navigation</h2>";
            echo "<ul>";
            echo "<li><a href='/'>Home</a></li>";
            echo "<li><a href='/dashboard'>Dashboard</a></li>";
            echo "<li><a href='/admin/dashboard'>Admin Dashboard</a></li>";
            echo "</ul>";
            echo "</div>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
    }
}

// Add some CSS for better presentation
echo <<<HTML
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        color: #333;
    }
    h1, h2 {
        color: #2c3e50;
    }
    h1 {
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    p {
        margin: 10px 0;
    }
    .success {
        color: #27ae60;
        font-weight: bold;
    }
    .error {
        color: #e74c3c;
        font-weight: bold;
    }
    .navigation {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }
    ul {
        list-style-type: none;
        padding: 0;
    }
    li {
        margin-bottom: 10px;
    }
    a {
        display: inline-block;
        padding: 8px 15px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 3px;
    }
    a:hover {
        background-color: #2980b9;
    }
</style>
HTML;
EOL

# Upload the direct admin access script
echo "Uploading direct admin access script to server..."
scp -o PreferredAuthentications=password /tmp/admin_access.php $SERVER:/home1/siloecom/siloe/public/public/admin_access.php

echo "Direct admin access script uploaded. Access it at: http://www.siloe.com.py/admin_access.php"
