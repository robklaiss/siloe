#!/bin/bash

# Server wipe and reset script
# This script will completely wipe the server and deploy a clean version of the application

echo "=== SILOE SERVER WIPE AND RESET ==="
echo "This script will:"
echo "1. Backup current files"
echo "2. Wipe the server directories"
echo "3. Deploy a clean version of the application"
echo "4. Set up the database"
echo "5. Create an admin user"
echo ""
echo "WARNING: This is a destructive operation! All data will be lost!"
echo ""

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SERVER_WEB_ROOT="/home1/siloecom/public_html"
SERVER_APP_ROOT="/home1/siloecom/siloe"

# Local paths
LOCAL_APP_ROOT="/Users/robinklaiss/Dev/siloe"

# Confirm before proceeding
read -p "Are you sure you want to proceed? (y/n): " confirm
if [[ "$confirm" != "y" ]]; then
    echo "Operation cancelled."
    exit 0
fi

echo "Starting server wipe and reset..."

# Create a temporary directory for the backup
echo "Creating backup directory on server..."
ssh -o PreferredAuthentications=password "$SERVER" "mkdir -p ~/backup_$(date +%Y%m%d_%H%M%S)"

# Backup current files
echo "Backing up current files..."
ssh -o PreferredAuthentications=password "$SERVER" "cp -r $SERVER_WEB_ROOT ~/backup_$(date +%Y%m%d_%H%M%S)/public_html"
ssh -o PreferredAuthentications=password "$SERVER" "cp -r $SERVER_APP_ROOT ~/backup_$(date +%Y%m%d_%H%M%S)/siloe 2>/dev/null || echo 'No siloe directory to backup'"

# Wipe server directories
echo "Wiping server directories..."
ssh -o PreferredAuthentications=password "$SERVER" "rm -rf $SERVER_WEB_ROOT/* $SERVER_WEB_ROOT/.[^.]* 2>/dev/null || true"
ssh -o PreferredAuthentications=password "$SERVER" "rm -rf $SERVER_APP_ROOT/* $SERVER_APP_ROOT/.[^.]* 2>/dev/null || true"
ssh -o PreferredAuthentications=password "$SERVER" "mkdir -p $SERVER_APP_ROOT"

# Create a clean deployment package
echo "Creating deployment package..."
DEPLOY_DIR=$(mktemp -d)
mkdir -p "$DEPLOY_DIR/public"
mkdir -p "$DEPLOY_DIR/app"
mkdir -p "$DEPLOY_DIR/config"
mkdir -p "$DEPLOY_DIR/database"

# Copy necessary files
echo "Copying files to deployment package..."
cp -r "$LOCAL_APP_ROOT/app" "$DEPLOY_DIR/"
cp -r "$LOCAL_APP_ROOT/config" "$DEPLOY_DIR/"
cp -r "$LOCAL_APP_ROOT/database" "$DEPLOY_DIR/"
cp -r "$LOCAL_APP_ROOT/public"/* "$DEPLOY_DIR/public/"
cp "$LOCAL_APP_ROOT/.htaccess" "$DEPLOY_DIR/" 2>/dev/null || true

# Create index.php with proper paths
cat > "$DEPLOY_DIR/public/index.php" << 'EOL'
<?php
// Define application root path
define('APP_ROOT', realpath(__DIR__ . '/..'));
define('APP_NAME', 'Siloe');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Autoload classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = APP_ROOT . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load routes
require_once APP_ROOT . '/app/routes.php';
EOL

# Create a simple database initialization script
cat > "$DEPLOY_DIR/public/init_db.php" << 'EOL'
<?php
// Database initialization script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application root path
define('APP_ROOT', realpath(__DIR__ . '/..'));

// Possible database paths
$db_paths = [
    APP_ROOT . '/database/database.sqlite',
    APP_ROOT . '/../database/database.sqlite',
    APP_ROOT . '/../siloe/database/database.sqlite',
    '/home1/siloecom/siloe/database/database.sqlite',
    '/home1/siloecom/database/database.sqlite'
];

// Find the correct database path
$db_path = null;
foreach ($db_paths as $path) {
    if (file_exists($path)) {
        $db_path = $path;
        echo "<p>Found database at: $path</p>";
        break;
    }
}

// If database not found, create it
if (!$db_path) {
    $db_dir = APP_ROOT . '/database';
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0755, true);
    }
    $db_path = $db_dir . '/database.sqlite';
    echo "<p>Creating new database at: $db_path</p>";
    
    // Create empty file
    file_put_contents($db_path, '');
}

try {
    // Connect to the database
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table if it doesn't exist
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "user",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    // Create companies table if it doesn't exist
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            address TEXT,
            phone TEXT,
            email TEXT,
            logo TEXT,
            contact_name TEXT,
            status TEXT DEFAULT "active",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    // Create weekly_menu_items table if it doesn't exist
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS weekly_menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            day TEXT NOT NULL,
            name TEXT NOT NULL,
            description TEXT,
            image TEXT,
            price REAL NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    // Create employee_menu_selections table if it doesn't exist
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS employee_menu_selections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            menu_item_id INTEGER NOT NULL,
            company_id INTEGER NOT NULL,
            selection_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (menu_item_id) REFERENCES weekly_menu_items(id),
            FOREIGN KEY (company_id) REFERENCES companies(id)
        )
    ');
    
    // Check if admin user exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = "admin"');
    $stmt->execute();
    $admin_count = $stmt->fetchColumn();
    
    // Create admin user if none exists
    if ($admin_count == 0) {
        $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Admin User', 'admin@example.com', $password_hash, 'admin']);
        echo "<p>Created admin user: admin@example.com / Admin123!</p>";
    } else {
        echo "<p>Admin user already exists</p>";
    }
    
    echo "<h1>Database initialized successfully!</h1>";
    echo "<p><a href='/'>Go to homepage</a></p>";
    echo "<p><a href='/login'>Go to login</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Database Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
EOL

# Create a direct admin access script
cat > "$DEPLOY_DIR/public/admin_access.php" << 'EOL'
<?php
// Direct admin access script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set admin session variables
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['authenticated'] = true;

echo "<h1>Admin Access Granted</h1>";
echo "<p>You are now logged in as admin.</p>";
echo "<h2>Navigation</h2>";
echo "<ul>";
echo "<li><a href='/'>Home</a></li>";
echo "<li><a href='/dashboard'>Dashboard</a></li>";
echo "<li><a href='/admin/dashboard'>Admin Dashboard</a></li>";
echo "</ul>";
EOL

# Create a simple login page
cat > "$DEPLOY_DIR/public/simple_login.php" << 'EOL'
<?php
// Simple login page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application root path
define('APP_ROOT', realpath(__DIR__ . '/..'));

// Start session
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Possible database paths
    $db_paths = [
        APP_ROOT . '/database/database.sqlite',
        APP_ROOT . '/../database/database.sqlite',
        APP_ROOT . '/../siloe/database/database.sqlite',
        '/home1/siloecom/siloe/database/database.sqlite',
        '/home1/siloecom/database/database.sqlite'
    ];
    
    // Find the correct database path
    $db_path = null;
    foreach ($db_paths as $path) {
        if (file_exists($path)) {
            $db_path = $path;
            break;
        }
    }
    
    if ($db_path) {
        try {
            // Connect to the database
            $pdo = new PDO('sqlite:' . $db_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get user by email
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                
                echo "<h1>Login successful!</h1>";
                echo "<p>Welcome, " . htmlspecialchars($user['name']) . "!</p>";
                echo "<p>Role: " . htmlspecialchars($user['role']) . "</p>";
                echo "<h2>Navigation</h2>";
                echo "<ul>";
                echo "<li><a href='/'>Home</a></li>";
                echo "<li><a href='/dashboard'>Dashboard</a></li>";
                if ($user['role'] === 'admin') {
                    echo "<li><a href='/admin/dashboard'>Admin Dashboard</a></li>";
                }
                echo "</ul>";
                exit;
            } else {
                $error = "Invalid credentials";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Database not found";
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
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h1>Simple Login Form</h1>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Login</button>
    </form>
    
    <p><a href="/init_db.php">Initialize Database</a></p>
    <p><a href="/admin_access.php">Direct Admin Access</a></p>
</body>
</html>
EOL

# Create a deployment package
echo "Creating deployment archive..."
cd "$DEPLOY_DIR"
tar -czf /tmp/siloe_deploy.tar.gz .

# Upload the deployment package
echo "Uploading deployment package to server..."
scp -o PreferredAuthentications=password /tmp/siloe_deploy.tar.gz "$SERVER:/tmp/"

# Extract the deployment package on the server
echo "Extracting deployment package on server..."
ssh -o PreferredAuthentications=password "$SERVER" "mkdir -p $SERVER_APP_ROOT"
ssh -o PreferredAuthentications=password "$SERVER" "tar -xzf /tmp/siloe_deploy.tar.gz -C $SERVER_APP_ROOT"
ssh -o PreferredAuthentications=password "$SERVER" "cp -r $SERVER_APP_ROOT/public/* $SERVER_WEB_ROOT/"
ssh -o PreferredAuthentications=password "$SERVER" "cp $SERVER_APP_ROOT/.htaccess $SERVER_WEB_ROOT/ 2>/dev/null || true"

# Set proper permissions
echo "Setting proper permissions..."
ssh -o PreferredAuthentications=password "$SERVER" "chmod -R 755 $SERVER_APP_ROOT"
ssh -o PreferredAuthentications=password "$SERVER" "chmod -R 755 $SERVER_WEB_ROOT"
ssh -o PreferredAuthentications=password "$SERVER" "find $SERVER_APP_ROOT -type f -exec chmod 644 {} \;"
ssh -o PreferredAuthentications=password "$SERVER" "find $SERVER_WEB_ROOT -type f -exec chmod 644 {} \;"

# Initialize the database
echo "Initializing database..."
ssh -o PreferredAuthentications=password "$SERVER" "mkdir -p $SERVER_APP_ROOT/database"
ssh -o PreferredAuthentications=password "$SERVER" "touch $SERVER_APP_ROOT/database/database.sqlite"
ssh -o PreferredAuthentications=password "$SERVER" "chmod 777 $SERVER_APP_ROOT/database/database.sqlite"

# Clean up
echo "Cleaning up..."
rm -rf "$DEPLOY_DIR"
rm /tmp/siloe_deploy.tar.gz
ssh -o PreferredAuthentications=password "$SERVER" "rm /tmp/siloe_deploy.tar.gz"

echo "=== SERVER WIPE AND RESET COMPLETE ==="
echo "You can now access the application at: http://www.siloe.com.py/"
echo "Login with: admin@example.com / Admin123!"
echo "Direct admin access: http://www.siloe.com.py/admin_access.php"
echo "Simple login: http://www.siloe.com.py/simple_login.php"
echo "Initialize database: http://www.siloe.com.py/init_db.php"
