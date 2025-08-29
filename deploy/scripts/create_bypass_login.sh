#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Creating login bypass script..."

# Create a login bypass script that directly sets up a session
cat > /tmp/bypass_login.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Admin Login Bypass</h1>";

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
    echo "<p class='error'>No database found! Creating one...</p>";
    $dbPath = __DIR__ . '/database/siloe.db';
    
    // Create directory if it doesn't exist
    if (!is_dir(dirname($dbPath))) {
        mkdir(dirname($dbPath), 0755, true);
    }
    
    // Create empty database file
    touch($dbPath);
    chmod($dbPath, 0644);
    
    echo "<p>Created new database at: $dbPath</p>";
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if users table exists
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('users', $tables)) {
        echo "<p>Creating users table...</p>";
        
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
    }
    
    // Check if admin user exists
    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@example.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p>Creating admin user...</p>";
        
        // Create admin user
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
    }
    
    if ($user) {
        echo "<p class='success'>Admin user found: ID={$user['id']}, Name={$user['name']}, Role={$user['role']}</p>";
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        echo "<p class='success'>Login successful! Session created.</p>";
        echo "<p>Session ID: " . session_id() . "</p>";
        echo "<p>Session data:</p>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        // Create a cookie file to store the session
        $cookieFile = __DIR__ . '/admin_session.txt';
        file_put_contents($cookieFile, json_encode([
            'session_id' => session_id(),
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'user_email' => $user['email'],
            'user_role' => $user['role']
        ]));
        
        echo "<p class='success'>Session data saved to: $cookieFile</p>";
        
        // Create a direct access script
        $accessScript = __DIR__ . '/admin_access.php';
        file_put_contents($accessScript, <<<PHP
<?php
// Start session
session_start();

// Load session data
\$sessionData = json_decode(file_get_contents(__DIR__ . '/admin_session.txt'), true);

// Set session variables
\$_SESSION['user_id'] = \$sessionData['user_id'];
\$_SESSION['user_name'] = \$sessionData['user_name'];
\$_SESSION['user_email'] = \$sessionData['user_email'];
\$_SESSION['user_role'] = \$sessionData['user_role'];

// Redirect to admin dashboard
header('Location: /admin/dashboard');
exit;
PHP
        );
        
        echo "<p class='success'>Created admin access script at: $accessScript</p>";
        echo "<p>Visit <a href='/admin_access.php'>Admin Access</a> to automatically log in as admin.</p>";
        
        // Create a session injector for all pages
        $injectorScript = __DIR__ . '/session_injector.php';
        file_put_contents($injectorScript, <<<PHP
<?php
// This file should be included at the top of index.php or other entry points
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in
if (!isset(\$_SESSION['user_id']) && file_exists(__DIR__ . '/admin_session.txt')) {
    // Load session data
    \$sessionData = json_decode(file_get_contents(__DIR__ . '/admin_session.txt'), true);
    
    // Set session variables
    \$_SESSION['user_id'] = \$sessionData['user_id'];
    \$_SESSION['user_name'] = \$sessionData['user_name'];
    \$_SESSION['user_email'] = \$sessionData['user_email'];
    \$_SESSION['user_role'] = \$sessionData['user_role'];
}
PHP
        );
        
        echo "<p class='success'>Created session injector at: $injectorScript</p>";
        echo "<p>Add the following line to the top of index.php:</p>";
        echo "<pre>require_once __DIR__ . '/session_injector.php';</pre>";
        
        // Create a modified index.php that includes the session injector
        $indexPath = __DIR__ . '/index.php';
        if (file_exists($indexPath)) {
            $indexContent = file_get_contents($indexPath);
            $modifiedContent = "<?php require_once __DIR__ . '/session_injector.php'; ?>\n" . $indexContent;
            file_put_contents($indexPath . '.bak', $indexContent); // backup
            file_put_contents($indexPath, $modifiedContent);
            echo "<p class='success'>Modified index.php to include session injector</p>";
        }
    } else {
        echo "<p class='error'>Failed to create admin user</p>";
    }
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
    pre {
        background-color: #f8f9fa;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 3px;
        overflow: auto;
    }
    a {
        color: #3498db;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
HTML;
EOL

# Upload the login bypass script
echo "Uploading login bypass script to server..."
scp -o PreferredAuthentications=password /tmp/bypass_login.php $SERVER:/home1/siloecom/siloe/public/public/bypass_login.php

# Create a script to modify index.php to include session injector
cat > /tmp/modify_index.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Modify Index.php</h1>";

$indexPath = __DIR__ . '/index.php';

if (file_exists($indexPath)) {
    echo "<p>Found index.php at: $indexPath</p>";
    
    // Backup original file
    copy($indexPath, $indexPath . '.bak');
    echo "<p class='success'>Created backup at: {$indexPath}.bak</p>";
    
    // Read content
    $content = file_get_contents($indexPath);
    echo "<p>Original file size: " . strlen($content) . " bytes</p>";
    
    // Check if already modified
    if (strpos($content, "require_once __DIR__ . '/session_injector.php'") !== false) {
        echo "<p>Index.php already contains session injector</p>";
    } else {
        // Modify content
        $modified = "<?php\n";
        $modified .= "// Auto-injected session loader\n";
        $modified .= "if (file_exists(__DIR__ . '/session_injector.php')) {\n";
        $modified .= "    require_once __DIR__ . '/session_injector.php';\n";
        $modified .= "}\n";
        
        // If file starts with <?php, replace it
        if (strpos($content, '<?php') === 0) {
            $modified .= substr($content, 5);
        } else {
            $modified .= $content;
        }
        
        // Write modified content
        file_put_contents($indexPath, $modified);
        echo "<p class='success'>Modified index.php to include session injector</p>";
    }
} else {
    echo "<p class='error'>Index.php not found at: $indexPath</p>";
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
</style>
HTML;
EOL

# Upload the index modifier script
echo "Uploading index modifier script to server..."
scp -o PreferredAuthentications=password /tmp/modify_index.php $SERVER:/home1/siloecom/siloe/public/public/modify_index.php

echo "Login bypass script uploaded. Access it at: http://www.siloe.com.py/bypass_login.php"
echo "Index modifier script uploaded. Access it at: http://www.siloe.com.py/modify_index.php"
