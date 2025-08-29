<?php
/**
 * Direct Login Fix Script
 * 
 * This script will:
 * 1. Fix session handling
 * 2. Create a direct admin login
 * 3. Provide navigation links
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with extended lifetime
session_set_cookie_params(86400, '/', '', false, true);
session_start();

// Set admin session variables
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['authenticated'] = true;

// Function to check database and create admin user if needed
function check_database() {
    // Possible database paths
    $db_paths = [
        __DIR__ . '/database/database.sqlite',
        __DIR__ . '/../database/database.sqlite',
        __DIR__ . '/../siloe/database/database.sqlite',
        '/home1/siloecom/siloe/database/database.sqlite',
        '/home1/siloecom/database/database.sqlite',
        '/home1/siloecom/public_html/database/database.sqlite'
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
        $db_dir = __DIR__ . '/database';
        if (!is_dir($db_dir)) {
            if (!mkdir($db_dir, 0755, true)) {
                echo "<p style='color:red'>Failed to create database directory</p>";
                return false;
            }
        }
        $db_path = $db_dir . '/database.sqlite';
        echo "<p>Creating new database at: $db_path</p>";
        
        // Create empty file
        if (!file_put_contents($db_path, '')) {
            echo "<p style='color:red'>Failed to create database file</p>";
            return false;
        }
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
        
        // Check if admin user exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = "admin"');
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();
        
        // Create admin user if none exists
        if ($admin_count == 0) {
            $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute(['Admin User', 'admin@example.com', $password_hash, 'admin']);
            echo "<p style='color:green'>Created admin user: admin@example.com / Admin123!</p>";
        } else {
            // Update admin password
            $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE role = "admin"');
            $stmt->execute([$password_hash]);
            echo "<p style='color:green'>Updated admin password to: Admin123!</p>";
        }
        
        // Show all users
        $stmt = $pdo->query('SELECT id, name, email, role FROM users');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Users in Database:</h3>";
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
        
        return true;
    } catch (PDOException $e) {
        echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Function to create session fix files
function create_session_fix() {
    // Create a session fix file
    $session_fix = <<<'EOT'
<?php
// Session fix for Siloe
// Include this file at the top of your index.php

// Start session with extended lifetime
session_set_cookie_params(86400, '/', '', false, true);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
EOT;

    // Write the session fix to a file
    $session_fix_file = __DIR__ . '/session_fix.php';
    if (file_put_contents($session_fix_file, $session_fix)) {
        echo "<p style='color:green'>Created session fix at $session_fix_file</p>";
        
        // Try to include it in index.php
        $index_path = __DIR__ . '/index.php';
        if (file_exists($index_path)) {
            $index_content = file_get_contents($index_path);
            
            // Check if session_fix.php is already included
            if (strpos($index_content, 'session_fix.php') === false) {
                // Add session fix include at the top of the file
                $include_line = "<?php require_once __DIR__ . '/session_fix.php'; ?>\n";
                $new_content = preg_replace('/^(<\?php|<\?)/i', $include_line, $index_content, 1);
                
                if ($new_content === $index_content) {
                    // If no replacement was made, prepend the include
                    $new_content = $include_line . $index_content;
                }
                
                if (file_put_contents($index_path, $new_content)) {
                    echo "<p style='color:green'>Updated index.php to include session fix</p>";
                } else {
                    echo "<p style='color:red'>Failed to update index.php</p>";
                }
            } else {
                echo "<p>Session fix already included in index.php</p>";
            }
        } else {
            echo "<p style='color:orange'>index.php not found at $index_path</p>";
        }
        
        return true;
    } else {
        echo "<p style='color:red'>Failed to create session fix</p>";
        return false;
    }
}

// Function to create direct access scripts
function create_direct_access() {
    // Create a direct admin access script
    $direct_access = <<<'EOT'
<?php
// Direct admin access script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with extended lifetime
session_set_cookie_params(86400, '/', '', false, true);
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

// Create session test link
echo "<p><a href='/session_test.php'>Test Session Persistence</a></p>";
EOT;

    // Create a session test script
    $session_test = <<<'EOT'
<?php
// Session test script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Session Test</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green'>✅ Session is working correctly!</p>";
} else {
    echo "<p style='color:red'>❌ Session is not persisting!</p>";
}

echo "<p><a href='/admin_access.php'>Back to Admin Access</a></p>";
EOT;

    // Write the direct access script to a file
    $direct_access_file = __DIR__ . '/admin_access.php';
    $session_test_file = __DIR__ . '/session_test.php';
    
    $success = true;
    
    if (file_put_contents($direct_access_file, $direct_access)) {
        echo "<p style='color:green'>Created direct admin access script at $direct_access_file</p>";
    } else {
        echo "<p style='color:red'>Failed to create direct admin access script</p>";
        $success = false;
    }
    
    if (file_put_contents($session_test_file, $session_test)) {
        echo "<p style='color:green'>Created session test script at $session_test_file</p>";
    } else {
        echo "<p style='color:red'>Failed to create session test script</p>";
        $success = false;
    }
    
    return $success;
}

// Function to create a simple login page
function create_simple_login() {
    $simple_login = <<<'EOT'
<?php
// Simple login page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with extended lifetime
session_set_cookie_params(86400, '/', '', false, true);
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Possible database paths
    $db_paths = [
        __DIR__ . '/database/database.sqlite',
        __DIR__ . '/../database/database.sqlite',
        __DIR__ . '/../siloe/database/database.sqlite',
        '/home1/siloecom/siloe/database/database.sqlite',
        '/home1/siloecom/database/database.sqlite',
        '/home1/siloecom/public_html/database/database.sqlite'
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
    
    <p><a href="/admin_access.php">Direct Admin Access</a></p>
</body>
</html>
EOT;

    // Write the simple login to a file
    $simple_login_file = __DIR__ . '/simple_login.php';
    if (file_put_contents($simple_login_file, $simple_login)) {
        echo "<p style='color:green'>Created simple login page at $simple_login_file</p>";
        return true;
    } else {
        echo "<p style='color:red'>Failed to create simple login page</p>";
        return false;
    }
}

// Function to fix AuthController
function fix_auth_controller() {
    // Possible AuthController paths
    $controller_paths = [
        __DIR__ . '/app/Controllers/AuthController.php',
        __DIR__ . '/../app/Controllers/AuthController.php',
        '/home1/siloecom/siloe/app/Controllers/AuthController.php',
        '/home1/siloecom/app/Controllers/AuthController.php'
    ];
    
    // Find the correct controller path
    $controller_path = null;
    foreach ($controller_paths as $path) {
        if (file_exists($path)) {
            $controller_path = $path;
            echo "<p>Found AuthController at: $path</p>";
            break;
        }
    }
    
    if (!$controller_path) {
        echo "<p style='color:red'>AuthController not found</p>";
        return false;
    }
    
    // Create fixed AuthController content
    $fixed_controller = <<<'EOT'
<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function showLoginForm()
    {
        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        // Display login form
        return $this->view('auth/login', [
            'title' => 'Login - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function login()
    {
        // Debug info
        error_log("Login attempt started");
        
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /login');
            exit;
        }
        
        // Get form data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Basic validation
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: /login');
            exit;
        }
        
        // Debug info
        error_log("Email: $email");
        error_log("Password length: " . strlen($password));
        
        try {
            // Find database file
            $db_paths = [
                APP_ROOT . '/database/database.sqlite',
                APP_ROOT . '/../database/database.sqlite',
                APP_ROOT . '/../siloe/database/database.sqlite',
                '/home1/siloecom/siloe/database/database.sqlite',
                '/home1/siloecom/database/database.sqlite',
                '/home1/siloecom/public_html/database/database.sqlite'
            ];
            
            $db_path = null;
            foreach ($db_paths as $path) {
                if (file_exists($path)) {
                    $db_path = $path;
                    error_log("Using database at: $path");
                    break;
                }
            }
            
            if (!$db_path) {
                error_log("Database not found");
                $_SESSION['error'] = 'Database not found';
                header('Location: /login');
                exit;
            }
            
            // Connect to database
            $pdo = new \PDO('sqlite:' . $db_path);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Get user by email
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Debug info
            error_log("User found: " . ($user ? 'Yes' : 'No'));
            if ($user) {
                error_log("User ID: " . $user['id']);
                error_log("User role: " . $user['role']);
                error_log("Password hash: " . substr($user['password'], 0, 10) . '...');
            }
            
            // Verify password
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                
                // Debug info
                error_log("Login successful");
                error_log("Session ID: " . session_id());
                error_log("Session data: " . print_r($_SESSION, true));
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: /admin/dashboard');
                } else {
                    header('Location: /dashboard');
                }
                exit;
            } else {
                // Debug info
                error_log("Invalid credentials");
                if ($user) {
                    error_log("Password verification failed");
                    $verify_result = password_verify($password, $user['password']);
                    error_log("Verification result: " . ($verify_result ? 'True' : 'False'));
                }
                
                $_SESSION['error'] = 'Invalid credentials';
                header('Location: /login');
                exit;
            }
        } catch (\Exception $e) {
            // Debug info
            error_log("Login error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $_SESSION['error'] = 'An error occurred during login';
            header('Location: /login');
            exit;
        }
    }
    
    public function logout()
    {
        // Clear session data
        $_SESSION = [];
        
        // Destroy session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }
    
    public function showRegisterForm()
    {
        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        // Display register form
        return $this->view('auth/register', [
            'title' => 'Register - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function register()
    {
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /register');
            exit;
        }
        
        // Get form data
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';
        
        // Basic validation
        if (empty($name) || empty($email) || empty($password) || empty($password_confirmation)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: /register');
            exit;
        }
        
        if ($password !== $password_confirmation) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: /register');
            exit;
        }
        
        try {
            // Find database file
            $db_paths = [
                APP_ROOT . '/database/database.sqlite',
                APP_ROOT . '/../database/database.sqlite',
                APP_ROOT . '/../siloe/database/database.sqlite',
                '/home1/siloecom/siloe/database/database.sqlite',
                '/home1/siloecom/database/database.sqlite',
                '/home1/siloecom/public_html/database/database.sqlite'
            ];
            
            $db_path = null;
            foreach ($db_paths as $path) {
                if (file_exists($path)) {
                    $db_path = $path;
                    break;
                }
            }
            
            if (!$db_path) {
                $_SESSION['error'] = 'Database not found';
                header('Location: /register');
                exit;
            }
            
            // Connect to database
            $pdo = new \PDO('sqlite:' . $db_path);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $_SESSION['error'] = 'Email already exists';
                header('Location: /register');
                exit;
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $password_hash, 'user']);
            
            // Set success message
            $_SESSION['success'] = 'Registration successful. Please log in.';
            
            // Redirect to login page
            header('Location: /login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'An error occurred during registration';
            header('Location: /register');
            exit;
        }
    }
    
    private function generateCsrfToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    private function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
EOT;

    // Write the fixed controller to the file
    if (file_put_contents($controller_path, $fixed_controller)) {
        echo "<p style='color:green'>Fixed AuthController at $controller_path</p>";
        return true;
    } else {
        echo "<p style='color:red'>Failed to fix AuthController</p>";
        return false;
    }
}

// Main execution
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Direct Login Fix</title>
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
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 15px 0;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .navigation {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .navigation a {
            display: inline-block;
            margin-right: 15px;
            text-decoration: none;
            color: #0066cc;
        }
        .navigation a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Siloe Direct Login Fix</h1>";

// Check database and create admin user if needed
echo "<h2>Database Check</h2>";
check_database();

// Create session fix
echo "<h2>Session Fix</h2>";
create_session_fix();

// Create direct access scripts
echo "<h2>Direct Access Scripts</h2>";
create_direct_access();

// Create simple login page
echo "<h2>Simple Login Page</h2>";
create_simple_login();

// Fix AuthController
echo "<h2>AuthController Fix</h2>";
fix_auth_controller();

// Navigation links
echo "<div class='navigation'>
    <h3>Navigation</h3>
    <a href='/'>Home</a>
    <a href='/admin_access.php'>Direct Admin Access</a>
    <a href='/simple_login.php'>Simple Login</a>
    <a href='/session_test.php'>Test Session</a>
</div>";

// Self-destruct option
echo "<div style='margin-top: 30px; padding: 15px; background-color: #fff0f0; border-radius: 5px;'>
    <h3 style='color: #cc0000;'>⚠️ Security Warning</h3>
    <p>For security reasons, please delete this script after use.</p>
    <form method='post'>
        <input type='submit' name='delete_script' value='Delete This Script' style='background-color: #cc0000; color: white; padding: 8px 15px; border: none; cursor: pointer; border-radius: 3px;'>
    </form>
</div>";

// Handle self-destruct
if (isset($_POST['delete_script'])) {
    unlink(__FILE__);
    echo "<script>window.location = '/';</script>";
    exit;
}

echo "</body>
</html>";
?>
