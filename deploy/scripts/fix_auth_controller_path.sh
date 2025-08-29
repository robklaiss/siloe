#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Creating updated AuthController with correct database path..."

# Create a temporary AuthController file with the correct database path
cat > /tmp/AuthController.php << 'EOL'
<?php
namespace App\Controllers;

use App\Core\Controller;
use PDO;
use PDOException;

class AuthController extends Controller {
    protected function getDbConnection() {
        static $db = null;
        if ($db === null) {
            try {
                if (defined('DB_DRIVER') && DB_DRIVER === 'mysql') {
                    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                    $db = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } else {
                    // Try multiple database paths
                    $possiblePaths = [
                        __DIR__ . '/../../database/siloe.db',
                        __DIR__ . '/../database/siloe.db',
                        __DIR__ . '/database/siloe.db',
                        '/home1/siloecom/siloe/public/database/siloe.db',
                        '/home1/siloecom/siloe/public/public/database/siloe.db'
                    ];
                    
                    $dbPath = null;
                    foreach ($possiblePaths as $path) {
                        if (file_exists($path)) {
                            $dbPath = $path;
                            break;
                        }
                    }
                    
                    if (!$dbPath) {
                        // Default to the first path if none exist
                        $dbPath = $possiblePaths[0];
                    }
                    
                    $db = new PDO('sqlite:' . $dbPath);
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $db->exec('PRAGMA foreign_keys = ON;');
                }
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                throw $e;
            }
        }
        return $db;
    }

    public function showLoginForm() {
        $this->view('auth/login', [
            'title' => 'Login',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Verify CSRF token
        if (!$this->session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Invalid security token. Please try again.');
            header('Location: /login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $this->session->setFlash('error', 'Invalid email or password.');
                header('Location: /login');
                exit;
            }

            // Login successful
            $this->session->set('user_id', $user['id']);
            $this->session->set('user_name', $user['name']);
            $this->session->set('user_email', $user['email']);
            $this->session->set('user_role', $user['role']);

            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30); // 30 days

                $stmt = $db->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
                $stmt->execute([
                    'token' => $token,
                    'id' => $user['id']
                ]);

                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /dashboard');
            }
            exit;
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->session->setFlash('error', 'An error occurred. Please try again later.');
            header('Location: /login');
            exit;
        }
    }

    public function logout() {
        $this->session->destroy();
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        header('Location: /login');
        exit;
    }
}
EOL

# Upload the updated AuthController
echo "Uploading updated AuthController to server..."
scp -o PreferredAuthentications=password /tmp/AuthController.php $SERVER:/home1/siloecom/siloe/public/app/Controllers/AuthController.php

# Create a debug script to show database connection details
cat > /tmp/debug_db.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Debug</h1>";

// Define paths to check
$possiblePaths = [
    __DIR__ . '/../../database/siloe.db',
    __DIR__ . '/../database/siloe.db',
    __DIR__ . '/database/siloe.db',
    '/home1/siloecom/siloe/public/database/siloe.db',
    '/home1/siloecom/siloe/public/public/database/siloe.db'
];

echo "<h2>Checking Database Paths</h2>";
echo "<ul>";
foreach ($possiblePaths as $path) {
    echo "<li>Path: $path - Exists: " . (file_exists($path) ? 'Yes' : 'No') . "</li>";
}
echo "</ul>";

// Try to connect to each existing database
echo "<h2>Testing Database Connections</h2>";
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "<h3>Testing connection to: $path</h3>";
        try {
            $db = new PDO('sqlite:' . $path);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>Successfully connected to database at $path</p>";
            
            // Check if users table exists
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
            
            if (in_array('users', $tables)) {
                // Check for admin user
                $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@example.com'");
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo "<p class='success'>Admin user found in $path: ID={$user['id']}, Name={$user['name']}, Role={$user['role']}</p>";
                    
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
                    echo "<p class='error'>Admin user not found in $path</p>";
                }
            } else {
                echo "<p class='error'>Users table not found in $path</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Database error with $path: " . $e->getMessage() . "</p>";
        }
    }
}

// Create a test login form
echo "<h2>Test Login Form</h2>";
echo "<form action='/login' method='post'>";
echo "<input type='hidden' name='csrf_token' value='test_token'>";
echo "<div><label>Email: <input type='email' name='email' value='admin@example.com'></label></div>";
echo "<div><label>Password: <input type='password' name='password' value='password'></label></div>";
echo "<div><button type='submit'>Login</button></div>";
echo "</form>";

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
    form {
        background-color: #f8f9fa;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    form div {
        margin-bottom: 10px;
    }
    input {
        padding: 5px;
        width: 300px;
    }
    button {
        padding: 8px 15px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
</style>
HTML;
EOL

# Upload the debug script
echo "Uploading debug script to server..."
scp -o PreferredAuthentications=password /tmp/debug_db.php $SERVER:/home1/siloecom/siloe/public/public/debug_db.php

echo "Fix completed! AuthController updated with multiple database path support."
echo "Debug script uploaded. Access it at: http://www.siloe.com.py/debug_db.php"
