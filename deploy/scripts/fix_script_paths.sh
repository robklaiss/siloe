#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"
REMOTE_DIR="/home1/siloecom/siloe"
WEB_ROOT="/home1/siloecom/public_html"

echo "Fixing script paths..."

# Create initialization scripts
cat > /tmp/init_db.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Initialize Database</h1>";

// Define database path
$dbPath = __DIR__ . '/database/siloe.db';

// Create directory if it doesn't exist
if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0777, true);
    echo "<p>Created directory: " . dirname($dbPath) . "</p>";
}

try {
    // Create database connection
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to database at: $dbPath</p>";
    
    // Create users table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    company_id INTEGER,
    remember_token TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL);
    echo "<p>Created users table</p>";
    
    // Create admin user
    $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
    $stmt->execute([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => password_hash('password', PASSWORD_DEFAULT),
        'role' => 'admin'
    ]);
    echo "<p>Created admin user: admin@example.com / password</p>";
    
    echo "<p style='color:green;font-weight:bold;'>Database initialized successfully!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>Database error: " . $e->getMessage() . "</p>";
}
EOL

cat > /tmp/admin_access.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Direct Admin Access</h1>";

// Define database path
$dbPath = __DIR__ . '/database/siloe.db';

if (!file_exists($dbPath)) {
    echo "<p class='error'>Database not found at: $dbPath</p>";
    echo "<p>Please run the <a href='/init_db.php'>database initialization script</a> first.</p>";
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to database at: $dbPath</p>";
    
    // Get admin user
    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@example.com' AND role = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p class='error'>Admin user not found!</p>";
        echo "<p>Please run the <a href='/init_db.php'>database initialization script</a> first.</p>";
        exit;
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
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
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

# Upload the scripts to the correct location
echo "Uploading scripts to the public_html directory..."
scp -o PreferredAuthentications=password /tmp/init_db.php $SERVER:$WEB_ROOT/init_db.php
scp -o PreferredAuthentications=password /tmp/admin_access.php $SERVER:$WEB_ROOT/admin_access.php

# Create database directory in public_html
echo "Creating database directory in public_html..."
ssh -o PreferredAuthentications=password $SERVER "mkdir -p $WEB_ROOT/database && chmod 777 $WEB_ROOT/database"

# Create sessions directory in public_html
echo "Creating sessions directory in public_html..."
ssh -o PreferredAuthentications=password $SERVER "mkdir -p $WEB_ROOT/sessions && chmod 777 $WEB_ROOT/sessions"

echo "Scripts uploaded to the correct location."
echo "Now you can access:"
echo "1. http://www.siloe.com.py/init_db.php"
echo "2. http://www.siloe.com.py/admin_access.php"
