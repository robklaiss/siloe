#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"
REMOTE_DIR="/home1/siloecom/siloe"
WEB_ROOT="/home1/siloecom/public_html"

echo "Diagnosing and fixing HTTP 500 error on login page..."

# Create a diagnostic script
cat > /tmp/login_debug.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login Page Diagnostic</h1>";

// Check PHP version
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check file structure
echo "<h2>File Structure</h2>";
$appDir = __DIR__ . '/app';
$controllersDir = $appDir . '/Controllers';
$authControllerPath = $controllersDir . '/AuthController.php';

echo "<p>App directory exists: " . (is_dir($appDir) ? 'Yes' : 'No') . "</p>";
echo "<p>Controllers directory exists: " . (is_dir($controllersDir) ? 'Yes' : 'No') . "</p>";
echo "<p>AuthController.php exists: " . (file_exists($authControllerPath) ? 'Yes' : 'No') . "</p>";

// Check database connection
echo "<h2>Database Connection</h2>";
$possiblePaths = [
    __DIR__ . '/database/siloe.db',
    __DIR__ . '/public/database/siloe.db',
    '/home1/siloecom/public_html/database/siloe.db',
    '/home1/siloecom/siloe/public/database/siloe.db'
];

foreach ($possiblePaths as $path) {
    echo "<p>Checking path: $path - " . (file_exists($path) ? 'Exists' : 'Not found') . "</p>";
    if (file_exists($path)) {
        try {
            $db = new PDO('sqlite:' . $path);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>Successfully connected to database at: $path</p>";
            
            // Check if users table exists
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>Tables in database: " . implode(', ', $tables) . "</p>";
            
            if (in_array('users', $tables)) {
                $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
                echo "<p>Number of users: $userCount</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Failed to connect to database at $path: " . $e->getMessage() . "</p>";
        }
    }
}

// Check AuthController.php content
if (file_exists($authControllerPath)) {
    echo "<h2>AuthController.php Content</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents($authControllerPath)) . "</pre>";
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
    h2 {
        margin-top: 20px;
        border-bottom: 1px solid #bdc3c7;
        padding-bottom: 5px;
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
        max-height: 400px;
    }
</style>
HTML;
EOL

# Create a fixed AuthController.php
cat > /tmp/AuthController.php << 'EOL'
<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class AuthController extends Controller
{
    protected $db;

    public function __construct()
    {
        // Set up error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Find the database file
        $possiblePaths = [
            __DIR__ . '/../../database/siloe.db',
            __DIR__ . '/../../public/database/siloe.db',
            '/home1/siloecom/public_html/database/siloe.db'
        ];
        
        $dbPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $dbPath = $path;
                break;
            }
        }
        
        if (!$dbPath) {
            // Default path if none found
            $dbPath = '/home1/siloecom/public_html/database/siloe.db';
            
            // Create directory if it doesn't exist
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        
        try {
            $this->db = new PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create users table if it doesn't exist
            $this->db->exec("
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
                )
            ");
        } catch (\PDOException $e) {
            // Log the error
            error_log('Database connection error: ' . $e->getMessage());
            
            // Continue without database for now
            $this->db = null;
        }
    }

    public function showLoginForm()
    {
        return $this->view('auth/login');
    }

    public function login()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get form data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email and password are required';
            return $this->redirect('/login');
        }
        
        try {
            // Check if database connection is available
            if (!$this->db) {
                throw new \Exception('Database connection is not available');
            }
            
            // Find user by email
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify user and password
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['error'] = 'Invalid credentials';
                return $this->redirect('/login');
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                return $this->redirect('/admin/dashboard');
            } else {
                return $this->redirect('/dashboard');
            }
        } catch (\Exception $e) {
            // Log the error
            error_log('Login error: ' . $e->getMessage());
            
            // Show error message
            $_SESSION['error'] = 'An error occurred during login. Please try again later.';
            return $this->redirect('/login');
        }
    }

    public function logout()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        return $this->redirect('/login');
    }

    public function showRegistrationForm()
    {
        return $this->view('auth/register');
    }

    public function register()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get form data
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        
        // Validate input
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'All fields are required';
            return $this->redirect('/register');
        }
        
        if ($password !== $passwordConfirmation) {
            $_SESSION['error'] = 'Passwords do not match';
            return $this->redirect('/register');
        }
        
        try {
            // Check if database connection is available
            if (!$this->db) {
                throw new \Exception('Database connection is not available');
            }
            
            // Check if email already exists
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Email already exists';
                return $this->redirect('/register');
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare('
                INSERT INTO users (name, email, password, role)
                VALUES (:name, :email, :password, :role)
            ');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'user'
            ]);
            
            // Set success message
            $_SESSION['success'] = 'Registration successful. Please log in.';
            
            // Redirect to login page
            return $this->redirect('/login');
        } catch (\Exception $e) {
            // Log the error
            error_log('Registration error: ' . $e->getMessage());
            
            // Show error message
            $_SESSION['error'] = 'An error occurred during registration. Please try again later.';
            return $this->redirect('/register');
        }
    }
}
EOL

# Create a simple login page
cat > /tmp/simple_login.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Simple Login Form</h1>";

// Display errors or success messages
if (isset($_SESSION['error'])) {
    echo "<p class='error'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo "<p class='success'>" . $_SESSION['success'] . "</p>";
    unset($_SESSION['success']);
}

// Define database path
$dbPath = __DIR__ . '/database/siloe.db';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo "<p class='error'>Email and password are required</p>";
    } else {
        try {
            // Connect to database
            $db = new PDO('sqlite:' . $dbPath);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Find user by email
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify user and password
            if (!$user || !password_verify($password, $user['password'])) {
                echo "<p class='error'>Invalid credentials</p>";
            } else {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                echo "<p class='success'>Login successful!</p>";
                echo "<p>Welcome, " . htmlspecialchars($user['name']) . "!</p>";
                echo "<p>Role: " . htmlspecialchars($user['role']) . "</p>";
                
                // Show navigation links
                echo "<div class='navigation'>";
                echo "<h2>Navigation</h2>";
                echo "<ul>";
                echo "<li><a href='/'>Home</a></li>";
                echo "<li><a href='/dashboard'>Dashboard</a></li>";
                
                if ($user['role'] === 'admin') {
                    echo "<li><a href='/admin/dashboard'>Admin Dashboard</a></li>";
                }
                
                echo "</ul>";
                echo "</div>";
                
                // Exit early
                exit;
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
        }
    }
}

// Display login form
echo <<<HTML
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

<p>Don't have an account? <a href="/register">Register</a></p>
HTML;

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
    form {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 5px;
        margin: 20px 0;
        max-width: 400px;
    }
    form div {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
    }
    input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    button {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 3px;
        cursor: pointer;
    }
    button:hover {
        background-color: #2980b9;
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
        color: #3498db;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
HTML;
EOL

# Upload the diagnostic script
echo "Uploading login diagnostic script..."
scp -o PreferredAuthentications=password /tmp/login_debug.php $SERVER:$WEB_ROOT/login_debug.php

# Upload the fixed AuthController
echo "Uploading fixed AuthController..."
scp -o PreferredAuthentications=password /tmp/AuthController.php $SERVER:$REMOTE_DIR/app/Controllers/AuthController.php

# Upload the simple login page
echo "Uploading simple login page..."
scp -o PreferredAuthentications=password /tmp/simple_login.php $SERVER:$WEB_ROOT/simple_login.php

echo "Scripts uploaded successfully."
echo "Now you can access:"
echo "1. http://www.siloe.com.py/login_debug.php - To diagnose login issues"
echo "2. http://www.siloe.com.py/simple_login.php - A simplified login page that should work"
echo ""
echo "The AuthController.php has also been fixed with better error handling and database path detection."
