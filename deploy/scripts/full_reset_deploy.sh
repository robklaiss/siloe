#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"
LOCAL_DIR="/Users/robinklaiss/Dev/siloe"
REMOTE_DIR="/home1/siloecom/siloe"
WEB_ROOT="/home1/siloecom/siloe/public/public"

echo "=========================================="
echo "Full Reset and Deployment Script for Siloe"
echo "=========================================="

# 1. Create backup of current production
echo "Creating backup of current production..."
ssh -o PreferredAuthentications=password $SERVER "cd /home1/siloecom && tar -czf siloe_backup_$(date +%Y%m%d_%H%M%S).tar.gz siloe"

# 2. Remove current installation
echo "Removing current installation..."
ssh -o PreferredAuthentications=password $SERVER "rm -rf $REMOTE_DIR"

# 3. Create new directory structure
echo "Creating new directory structure..."
ssh -o PreferredAuthentications=password $SERVER "mkdir -p $REMOTE_DIR/public/public"

# 4. Upload all files
echo "Uploading all files..."
rsync -avz --exclude='.git/' --exclude='node_modules/' --exclude='vendor/' -e "ssh -o PreferredAuthentications=password" $LOCAL_DIR/ $SERVER:$REMOTE_DIR/

# 5. Set proper permissions
echo "Setting proper permissions..."
ssh -o PreferredAuthentications=password $SERVER "find $REMOTE_DIR -type d -exec chmod 755 {} \; && find $REMOTE_DIR -type f -exec chmod 644 {} \; && chmod +x $REMOTE_DIR/deploy/scripts/*.sh"

# 6. Create database directory
echo "Creating database directory..."
ssh -o PreferredAuthentications=password $SERVER "mkdir -p $WEB_ROOT/database && chmod 777 $WEB_ROOT/database"

# 7. Create sessions directory
echo "Creating sessions directory..."
ssh -o PreferredAuthentications=password $SERVER "mkdir -p $WEB_ROOT/sessions && chmod 777 $WEB_ROOT/sessions"

# 8. Create SQLite database
echo "Creating SQLite database..."
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

# Upload and run the database initialization script
echo "Uploading database initialization script..."
scp -o PreferredAuthentications=password /tmp/init_db.php $SERVER:$WEB_ROOT/init_db.php

echo "Running database initialization script..."
echo "Visit http://www.siloe.com.py/init_db.php to initialize the database"

# 9. Create direct admin access script
echo "Creating direct admin access script..."
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

# Upload the direct admin access script
echo "Uploading direct admin access script..."
scp -o PreferredAuthentications=password /tmp/admin_access.php $SERVER:$WEB_ROOT/admin_access.php

echo "=========================================="
echo "Full reset and deployment completed!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Visit http://www.siloe.com.py/init_db.php to initialize the database"
echo "2. Visit http://www.siloe.com.py/admin_access.php to access the admin area"
echo ""
echo "Admin credentials:"
echo "Email: admin@example.com"
echo "Password: password"
echo ""
echo "IMPORTANT: Change the admin password after first login!"
