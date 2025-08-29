<?php
// Minimal index.php file that will definitely work
session_start();

// Set session cookie parameters for better persistence
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400); // 24 hours

// Function to create or update admin user in database
function ensure_admin_user() {
    $db_paths = [
        '/home1/siloecom/siloe/database/database.sqlite',
        '/home1/siloecom/siloe/public/database/database.sqlite',
        '/home1/siloecom/public_html/database/database.sqlite',
        '/home1/siloecom/public_html/database.sqlite'
    ];
    
    foreach ($db_paths as $db_path) {
        if (file_exists($db_path) || is_writable(dirname($db_path))) {
            try {
                $dir = dirname($db_path);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                $db = new PDO("sqlite:$db_path");
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Create users table if it doesn't exist
                $db->exec("CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'user',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Check if admin user exists
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute(['admin@example.com']);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$admin) {
                    // Create admin user
                    $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute(['Administrator', 'admin@example.com', $password_hash, 'admin']);
                    return "Admin user created in database at $db_path";
                } else {
                    // Update admin password
                    $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $stmt->execute([$password_hash, 'admin@example.com']);
                    return "Admin user updated in database at $db_path";
                }
            } catch (PDOException $e) {
                continue;
            }
        }
    }
    
    return "Could not find or create database";
}

// Function to log in as admin
function login_as_admin() {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'admin@example.com';
    $_SESSION['user_role'] = 'admin';
    $_SESSION['authenticated'] = true;
    return true;
}

// Handle actions
$message = '';
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'login') {
        login_as_admin();
        $message = "Logged in as admin";
    } elseif ($_GET['action'] === 'db') {
        $message = ensure_admin_user();
    } elseif ($_GET['action'] === 'logout') {
        session_destroy();
        $message = "Logged out";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siloe - Emergency Access</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
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
        .button:hover {
            background-color: #45a049;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Siloe - Emergency Access</h1>
    
    <?php if (!empty($message)): ?>
        <div class="success">
            <p><?php echo $message; ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($is_logged_in): ?>
        <div class="success">
            <h2>You are logged in!</h2>
            <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
            <p>Email: <?php echo $_SESSION['user_email']; ?></p>
            <p>Role: <?php echo $_SESSION['user_role']; ?></p>
        </div>
        
        <h2>Actions</h2>
        <p>
            <a href="?action=logout" class="button">Logout</a>
        </p>
    <?php else: ?>
        <div class="info">
            <p>You are not logged in. Click the button below to log in as admin.</p>
        </div>
        
        <p>
            <a href="?action=login" class="button">Login as Admin</a>
            <a href="?action=db" class="button">Initialize Database</a>
        </p>
    <?php endif; ?>
    
    <h2>Server Information</h2>
    <div class="info">
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        <p>Script Path: <?php echo __FILE__; ?></p>
        <p>Session ID: <?php echo session_id(); ?></p>
    </div>
    
    <h2>Directory Structure</h2>
    <pre><?php
    $dirs = [
        '/home1/siloecom',
        '/home1/siloecom/public_html',
        '/home1/siloecom/siloe',
        '/home1/siloecom/siloe/public'
    ];
    
    foreach ($dirs as $dir) {
        echo "Directory: $dir\n";
        if (is_dir($dir)) {
            echo "  Exists: Yes\n";
            echo "  Readable: " . (is_readable($dir) ? "Yes" : "No") . "\n";
            echo "  Writable: " . (is_writable($dir) ? "Yes" : "No") . "\n";
            
            echo "  Contents:\n";
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    echo "    - $file\n";
                }
            }
        } else {
            echo "  Exists: No\n";
        }
        echo "\n";
    }
    ?></pre>
    
    <h2>Session Data</h2>
    <pre><?php print_r($_SESSION); ?></pre>
</body>
</html>
