#!/bin/bash

# Complete Server Wipe Script for Siloe
# This script will completely wipe the server and deploy a clean version of the application

echo "===== COMPLETE SERVER WIPE SCRIPT ====="
echo "WARNING: This script will completely wipe all files on the server!"
echo "Press Ctrl+C now to abort, or wait 5 seconds to continue..."
sleep 5

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SERVER_WEB_ROOT="/home1/siloecom/public_html"
SERVER_APP_ROOT="/home1/siloecom/siloe"

# Create a temporary directory for our files
TEMP_DIR=$(mktemp -d)
echo "Created temporary directory: $TEMP_DIR"

# Create a minimal index.php file
cat > "$TEMP_DIR/index.php" << 'EOL'
<?php
// Minimal index.php file
session_start();

// Display a simple page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siloe - Clean Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Siloe - Clean Installation</h1>
    
    <div class="success">
        <h2>Server Successfully Wiped</h2>
        <p>The server has been successfully wiped and a clean installation has been deployed.</p>
    </div>
    
    <div class="info">
        <h2>Next Steps</h2>
        <p>You can now:</p>
        <ul>
            <li>Deploy your application code</li>
            <li>Initialize the database</li>
            <li>Set up proper configurations</li>
        </ul>
    </div>
    
    <h2>Server Information</h2>
    <div class="info">
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
    </div>
</body>
</html>
EOL

# Create a simple admin access script
cat > "$TEMP_DIR/admin_access.php" << 'EOL'
<?php
// Direct admin access script
session_start();

// Set admin session variables
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['authenticated'] = true;

// Display success message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access - Siloe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px 0;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>Admin Access</h1>
    
    <div class="success">
        <h2>Admin Access Granted</h2>
        <p>You have been logged in as an administrator.</p>
        <p>Session ID: <?php echo session_id(); ?></p>
    </div>
    
    <p>
        <a href="/" class="button">Go to Home</a>
        <a href="/dashboard" class="button">Go to Dashboard</a>
        <a href="/admin/dashboard" class="button">Go to Admin Dashboard</a>
    </p>
    
    <h2>Session Information</h2>
    <pre><?php print_r($_SESSION); ?></pre>
</body>
</html>
EOL

# Create a database initialization script
cat > "$TEMP_DIR/init_db.php" << 'EOL'
<?php
// Database initialization script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create SQLite database
function create_sqlite_database($db_path) {
    try {
        // Check if database file exists
        $db_exists = file_exists($db_path);
        
        // Create or open the database
        $db = new PDO("sqlite:$db_path");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // If database didn't exist, create tables
        if (!$db_exists) {
            // Create users table
            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create admin user
            $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Administrator', 'admin@example.com', $password_hash, 'admin']);
            
            return "Database created and initialized with admin user";
        }
        
        return "Database already exists";
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

// Possible database paths
$db_paths = [
    '/home1/siloecom/siloe/database/database.sqlite',
    '/home1/siloecom/public_html/database/database.sqlite',
    '/home1/siloecom/public_html/database.sqlite'
];

$results = [];

// Try to create/initialize database at each path
foreach ($db_paths as $db_path) {
    // Create directory if it doesn't exist
    $db_dir = dirname($db_path);
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0755, true);
    }
    
    $results[$db_path] = create_sqlite_database($db_path);
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization - Siloe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px 0;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>Database Initialization</h1>
    
    <div class="success">
        <h2>Database Initialization Results</h2>
        <?php foreach ($results as $path => $result): ?>
            <p><strong><?php echo $path; ?>:</strong> <?php echo $result; ?></p>
        <?php endforeach; ?>
    </div>
    
    <div class="info">
        <h2>Admin User Credentials</h2>
        <p>Email: admin@example.com</p>
        <p>Password: Admin123!</p>
    </div>
    
    <p>
        <a href="/" class="button">Go to Home</a>
        <a href="/admin_access.php" class="button">Admin Access</a>
    </p>
</body>
</html>
EOL

# Create a .htaccess file
cat > "$TEMP_DIR/.htaccess" << 'EOL'
# Enable rewrite engine
RewriteEngine On

# Set base directory
RewriteBase /

# Handle front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Set default index file
DirectoryIndex index.php

# PHP settings
php_flag display_errors on
php_value error_reporting E_ALL

# Allow access to all files
<FilesMatch ".*">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOL

# Create a simple login page
cat > "$TEMP_DIR/simple_login.php" << 'EOL'
<?php
// Simple login page
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Try to find the database
        $db_paths = [
            '/home1/siloecom/siloe/database/database.sqlite',
            '/home1/siloecom/public_html/database/database.sqlite',
            '/home1/siloecom/public_html/database.sqlite'
        ];
        
        $db = null;
        foreach ($db_paths as $db_path) {
            if (file_exists($db_path)) {
                try {
                    $db = new PDO("sqlite:$db_path");
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    break;
                } catch (PDOException $e) {
                    // Try next path
                }
            }
        }
        
        if ($db) {
            // Query for user
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                
                // Redirect to home page
                header('Location: /');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Database not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login - Siloe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .error {
            color: red;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        form {
            margin: 20px 0;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Simple Login</h1>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <p><?php echo $error; ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div>
            <button type="submit">Login</button>
        </div>
    </form>
    
    <div class="info">
        <h2>Default Admin Credentials</h2>
        <p>Email: admin@example.com</p>
        <p>Password: Admin123!</p>
    </div>
</body>
</html>
EOL

# Create a phpinfo file
cat > "$TEMP_DIR/info.php" << 'EOL'
<?php
// Display PHP information
phpinfo();
EOL

echo "Created all necessary files in temporary directory"

# SSH to server and wipe everything
echo "Connecting to server to wipe everything..."
ssh -o PreferredAuthentications=password "$SERVER" << 'ENDSSH'
# Create backup directory
BACKUP_DIR="/home1/siloecom/backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup current files
echo "Backing up current files to $BACKUP_DIR..."
cp -r /home1/siloecom/public_html/* "$BACKUP_DIR/" 2>/dev/null || true
cp -r /home1/siloecom/siloe/* "$BACKUP_DIR/siloe/" 2>/dev/null || true

# Wipe public_html directory
echo "Wiping public_html directory..."
rm -rf /home1/siloecom/public_html/* /home1/siloecom/public_html/.htaccess 2>/dev/null || true

# Wipe siloe directory
echo "Wiping siloe directory..."
rm -rf /home1/siloecom/siloe/* 2>/dev/null || true

# Create necessary directories
echo "Creating necessary directories..."
mkdir -p /home1/siloecom/siloe/database
mkdir -p /home1/siloecom/public_html/database

echo "Server wiped successfully!"
ENDSSH

# Upload files to server
echo "Uploading files to server..."
scp -o PreferredAuthentications=password "$TEMP_DIR"/* "$SERVER:$SERVER_WEB_ROOT/"
scp -o PreferredAuthentications=password "$TEMP_DIR/.htaccess" "$SERVER:$SERVER_WEB_ROOT/"

# Set permissions
echo "Setting permissions..."
ssh -o PreferredAuthentications=password "$SERVER" << 'ENDSSH'
chmod -R 755 /home1/siloecom/public_html
chmod -R 755 /home1/siloecom/siloe
chmod 644 /home1/siloecom/public_html/.htaccess
chmod 644 /home1/siloecom/public_html/*.php
ENDSSH

# Clean up temporary directory
echo "Cleaning up temporary directory..."
rm -rf "$TEMP_DIR"

echo "===== SERVER WIPE COMPLETE ====="
echo "The server has been wiped and a clean installation has been deployed."
echo "You can now access the following URLs:"
echo "- Home page: http://www.siloe.com.py/"
echo "- Admin access: http://www.siloe.com.py/admin_access.php"
echo "- Database initialization: http://www.siloe.com.py/init_db.php"
echo "- Simple login: http://www.siloe.com.py/simple_login.php"
echo "- PHP info: http://www.siloe.com.py/info.php"
