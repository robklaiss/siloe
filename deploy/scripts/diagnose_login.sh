#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Creating diagnostic script..."

# Create a diagnostic PHP script
cat > /tmp/diagnose_auth.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Siloe Authentication Diagnostic</h1>";

// Define paths
$baseDir = __DIR__;
$dbDir = $baseDir . '/database';
$dbFile = $dbDir . '/siloe.db';
$controllersDir = $baseDir . '/app/Controllers';
$authControllerFile = $controllersDir . '/AuthController.php';

echo "<h2>1. Checking File Structure</h2>";
echo "<p>Base directory: $baseDir</p>";
echo "<p>Database directory exists: " . (is_dir($dbDir) ? 'Yes' : 'No') . "</p>";
echo "<p>Database file exists: " . (file_exists($dbFile) ? 'Yes' : 'No') . "</p>";
echo "<p>Controllers directory exists: " . (is_dir($controllersDir) ? 'Yes' : 'No') . "</p>";
echo "<p>AuthController.php exists: " . (file_exists($authControllerFile) ? 'Yes' : 'No') . "</p>";

echo "<h2>2. Checking Database Connection</h2>";
try {
    if (file_exists($dbFile)) {
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='success'>Successfully connected to SQLite database</p>";
        
        // Check if tables exist
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
        
        // Check if users table exists and has the expected structure
        if (in_array('users', $tables)) {
            $columns = $db->query("PRAGMA table_info(users);")->fetchAll(PDO::FETCH_COLUMN, 1);
            echo "<p>Users table columns: " . implode(', ', $columns) . "</p>";
            
            // Check if admin user exists
            $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@example.com'");
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "<p class='success'>Admin user found: ID={$user['id']}, Name={$user['name']}, Role={$user['role']}</p>";
                
                // Test password verification
                $stmt = $db->prepare("SELECT password FROM users WHERE email = 'admin@example.com'");
                $stmt->execute();
                $hash = $stmt->fetchColumn();
                
                if (password_verify('password', $hash)) {
                    echo "<p class='success'>Password verification successful</p>";
                } else {
                    echo "<p class='error'>Password verification failed. Hash: " . substr($hash, 0, 10) . "...</p>";
                }
            } else {
                echo "<p class='error'>Admin user not found</p>";
            }
        } else {
            echo "<p class='error'>Users table not found</p>";
        }
    } else {
        echo "<p class='error'>Database file does not exist</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Checking AuthController.php</h2>";
if (file_exists($authControllerFile)) {
    $content = file_get_contents($authControllerFile);
    echo "<p>File size: " . strlen($content) . " bytes</p>";
    
    // Check for key methods
    $methods = [
        'getDbConnection' => strpos($content, 'function getDbConnection') !== false,
        'showLoginForm' => strpos($content, 'function showLoginForm') !== false,
        'login' => strpos($content, 'function login') !== false,
        'logout' => strpos($content, 'function logout') !== false
    ];
    
    foreach ($methods as $method => $exists) {
        echo "<p>Method '$method' exists: " . ($exists ? 'Yes' : 'No') . "</p>";
    }
    
    // Check for namespace and use statements
    echo "<p>Namespace declaration: " . (strpos($content, 'namespace App\\Controllers') !== false ? 'Yes' : 'No') . "</p>";
    echo "<p>Use App\\Core\\Controller: " . (strpos($content, 'use App\\Core\\Controller') !== false ? 'Yes' : 'No') . "</p>";
    echo "<p>Use PDO: " . (strpos($content, 'use PDO') !== false ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p class='error'>AuthController.php does not exist</p>";
}

echo "<h2>4. Checking Core Controller</h2>";
$coreControllerFile = $baseDir . '/app/Core/Controller.php';
if (file_exists($coreControllerFile)) {
    $content = file_get_contents($coreControllerFile);
    echo "<p>File size: " . strlen($content) . " bytes</p>";
    
    // Check for key methods
    $methods = [
        'generateCsrfToken' => strpos($content, 'function generateCsrfToken') !== false,
        'view' => strpos($content, 'function view') !== false
    ];
    
    foreach ($methods as $method => $exists) {
        echo "<p>Method '$method' exists: " . ($exists ? 'Yes' : 'No') . "</p>";
    }
} else {
    echo "<p class='error'>Core Controller.php does not exist</p>";
}

echo "<h2>5. Checking Login Route</h2>";
$routesFile = $baseDir . '/app/routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    echo "<p>Routes file exists, size: " . strlen($content) . " bytes</p>";
    
    // Check for login routes
    $loginRoutes = [
        'get /login' => strpos($content, '$router->get(\'/login\'') !== false,
        'post /login' => strpos($content, '$router->post(\'/login\'') !== false
    ];
    
    foreach ($loginRoutes as $route => $exists) {
        echo "<p>Route '$route' exists: " . ($exists ? 'Yes' : 'No') . "</p>";
    }
} else {
    echo "<p class='error'>Routes file does not exist</p>";
}

echo "<h2>6. Checking Session Handling</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p>Session active: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

// Add some CSS for better presentation
echo <<<HTML
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        color: #333;
    }
    h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    h2 {
        color: #2980b9;
        margin-top: 20px;
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
    .summary {
        background-color: #f8f9fa;
        border-left: 4px solid #3498db;
        padding: 15px;
        margin: 20px 0;
    }
</style>
HTML;
EOL

# Upload and execute the diagnostic script
echo "Uploading diagnostic script to server..."
scp -o PreferredAuthentications=password /tmp/diagnose_auth.php $SERVER:/home1/siloecom/siloe/public/public/diagnose_auth.php

echo "Diagnostic script uploaded. Access it at: http://www.siloe.com.py/diagnose_auth.php"

# Create a direct database check script
cat > /tmp/check_db.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Check</h1>";

// Define paths
$dbFile = __DIR__ . '/database/siloe.db';

try {
    if (file_exists($dbFile)) {
        echo "<p>Database file exists at: $dbFile</p>";
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p>Successfully connected to SQLite database</p>";
        
        // List all tables
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
        echo "<h2>Tables:</h2>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check users table
        if (in_array('users', $tables)) {
            echo "<h2>Users:</h2>";
            $users = $db->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['name']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Users table not found</p>";
        }
    } else {
        echo "<p>Database file does not exist at: $dbFile</p>";
        
        // Check parent directory
        $dbDir = dirname($dbFile);
        if (is_dir($dbDir)) {
            echo "<p>Database directory exists at: $dbDir</p>";
            echo "<p>Contents:</p>";
            echo "<ul>";
            $files = scandir($dbDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "<li>$file</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p>Database directory does not exist at: $dbDir</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
EOL

# Upload the database check script
echo "Uploading database check script..."
scp -o PreferredAuthentications=password /tmp/check_db.php $SERVER:/home1/siloecom/siloe/public/public/check_db.php

echo "Database check script uploaded. Access it at: http://www.siloe.com.py/check_db.php"

# Create a fix script that recreates the database in the correct location
cat > /tmp/fix_db_location.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Location Fix</h1>";

// Define paths
$baseDir = __DIR__;
$dbDir = $baseDir . '/database';
$dbFile = $dbDir . '/siloe.db';
$publicDbDir = $baseDir . '/public/database';
$publicDbFile = $publicDbDir . '/siloe.db';

echo "<h2>Checking Paths</h2>";
echo "<p>Base directory: $baseDir</p>";
echo "<p>Expected DB path: $dbFile</p>";
echo "<p>Public DB path: $publicDbFile</p>";

// Create database directories if they don't exist
if (!is_dir($dbDir)) {
    if (mkdir($dbDir, 0755, true)) {
        echo "<p class='success'>Created database directory: $dbDir</p>";
    } else {
        echo "<p class='error'>Failed to create database directory: $dbDir</p>";
    }
}

if (!is_dir($publicDbDir)) {
    if (mkdir($publicDbDir, 0755, true)) {
        echo "<p class='success'>Created public database directory: $publicDbDir</p>";
    } else {
        echo "<p class='error'>Failed to create public database directory: $publicDbDir</p>";
    }
}

// Create database files if they don't exist
if (!file_exists($dbFile)) {
    if (touch($dbFile)) {
        chmod($dbFile, 0644);
        echo "<p class='success'>Created database file: $dbFile</p>";
    } else {
        echo "<p class='error'>Failed to create database file: $dbFile</p>";
    }
}

if (!file_exists($publicDbFile)) {
    if (touch($publicDbFile)) {
        chmod($publicDbFile, 0644);
        echo "<p class='success'>Created public database file: $publicDbFile</p>";
    } else {
        echo "<p class='error'>Failed to create public database file: $publicDbFile</p>";
    }
}

// Create tables in both locations
try {
    // Create tables in main location
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    
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
    
    // Create password_resets table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL);
    
    // Create sessions table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    payload TEXT,
    last_activity INTEGER
);
SQL);
    
    // Check if admin user already exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => 'admin@example.com']);
    if (!$stmt->fetch()) {
        // Create admin user
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
        $stmt->execute([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password is 'password'
            'role' => 'admin'
        ]);
        echo "<p class='success'>Created default admin user in main database</p>";
    } else {
        echo "<p>Admin user already exists in main database</p>";
    }
    
    echo "<p class='success'>Created tables in main database</p>";
    
    // Create tables in public location
    $publicDb = new PDO('sqlite:' . $publicDbFile);
    $publicDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $publicDb->exec('PRAGMA foreign_keys = ON;');
    
    // Create users table
    $publicDb->exec(<<<SQL
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
    
    // Create password_resets table
    $publicDb->exec(<<<SQL
CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL);
    
    // Create sessions table
    $publicDb->exec(<<<SQL
CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    payload TEXT,
    last_activity INTEGER
);
SQL);
    
    // Check if admin user already exists
    $stmt = $publicDb->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => 'admin@example.com']);
    if (!$stmt->fetch()) {
        // Create admin user
        $stmt = $publicDb->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
        $stmt->execute([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password is 'password'
            'role' => 'admin'
        ]);
        echo "<p class='success'>Created default admin user in public database</p>";
    } else {
        echo "<p>Admin user already exists in public database</p>";
    }
    
    echo "<p class='success'>Created tables in public database</p>";
    
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
    h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    h2 {
        color: #2980b9;
        margin-top: 20px;
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
    .summary {
        background-color: #f8f9fa;
        border-left: 4px solid #3498db;
        padding: 15px;
        margin: 20px 0;
    }
</style>
HTML;

echo "<div class='summary'>";
echo "<h2>Database Fix Complete</h2>";
echo "<p>The database has been created in both locations:</p>";
echo "<ul>";
echo "<li>$dbFile</li>";
echo "<li>$publicDbFile</li>";
echo "</ul>";
echo "<p>You can now login with:</p>";
echo "<ul>";
echo "<li><strong>Email:</strong> admin@example.com</li>";
echo "<li><strong>Password:</strong> password</li>";
echo "</ul>";
echo "<p class='warning'>IMPORTANT: Change these credentials immediately after logging in!</p>";
echo "</div>";
EOL

# Upload the database location fix script
echo "Uploading database location fix script..."
scp -o PreferredAuthentications=password /tmp/fix_db_location.php $SERVER:/home1/siloecom/siloe/public/public/fix_db_location.php

echo "Database location fix script uploaded. Access it at: http://www.siloe.com.py/fix_db_location.php"
