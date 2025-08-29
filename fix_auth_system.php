<?php
/**
 * Siloe Authentication System - All-in-One Fix
 * 
 * This single file will:
 * 1. Fix the AuthController.php file
 * 2. Create the database directory and file if needed
 * 3. Create the necessary auth tables
 * 4. Add a default admin user
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Siloe Authentication System Fix</h1>";

// Define paths
$baseDir = __DIR__;
$controllersDir = $baseDir . '/app/Controllers';
$dbDir = $baseDir . '/database';
$dbFile = $dbDir . '/siloe.db';

// Step 1: Fix the AuthController.php file
echo "<h2>Step 1: Fixing AuthController.php</h2>";

// Create Controllers directory if it doesn't exist
if (!is_dir($controllersDir)) {
    if (!mkdir($controllersDir, 0755, true)) {
        die("<p class='error'>Error: Failed to create Controllers directory at: $controllersDir</p>");
    }
    echo "<p class='success'>Created Controllers directory: $controllersDir</p>";
}

// Define the fixed AuthController content
$authControllerContent = <<<'EOT'
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
                    $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../../database/siloe.db';
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
EOT;

// Write the fixed AuthController.php file
$authControllerFile = $controllersDir . '/AuthController.php';
$originalContent = '';

if (file_exists($authControllerFile)) {
    // Backup the original file
    $originalContent = file_get_contents($authControllerFile);
    $backupFile = $authControllerFile . '.bak.' . date('YmdHis');
    file_put_contents($backupFile, $originalContent);
    echo "<p>Created backup of original AuthController.php at: $backupFile</p>";
}

// Get the rest of the AuthController content if it exists
$restOfContent = '';
if (!empty($originalContent)) {
    // Find where the class declaration ends and the first method begins
    $classPos = strpos($originalContent, 'class AuthController');
    if ($classPos !== false) {
        $methodPos = strpos($originalContent, 'function', $classPos);
        if ($methodPos !== false) {
            // Find the first method that's not getDbConnection
            if (strpos($originalContent, 'function getDbConnection', $classPos) === $methodPos) {
                // Skip getDbConnection method
                $nextMethodPos = strpos($originalContent, 'function', $methodPos + 10);
                if ($nextMethodPos !== false) {
                    $restOfContent = substr($originalContent, $nextMethodPos - 4);
                }
            } else {
                $restOfContent = substr($originalContent, $methodPos - 4);
            }
        }
    }
}

// If we couldn't extract the rest of the content, add a placeholder
if (empty($restOfContent)) {
    $restOfContent = <<<'EOT'
    // Default methods if original content couldn't be parsed
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

    public function showRegisterForm() {
        $this->view('auth/register', [
            'title' => 'Register',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }

        // Verify CSRF token
        if (!$this->session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Invalid security token. Please try again.');
            header('Location: /register');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $this->session->setFlash('errors', $errors);
            $this->session->setFlash('old', [
                'name' => $name,
                'email' => $email
            ]);
            header('Location: /register');
            exit;
        }

        try {
            $db = $this->getDbConnection();
            
            // Check if email already exists
            $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $this->session->setFlash('error', 'Email already registered.');
                $this->session->setFlash('old', [
                    'name' => $name
                ]);
                header('Location: /register');
                exit;
            }

            // Create user
            $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user'
            ]);

            $this->session->setFlash('success', 'Registration successful! You can now log in.');
            header('Location: /login');
            exit;
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            $this->session->setFlash('error', 'An error occurred. Please try again later.');
            header('Location: /register');
            exit;
        }
    }
EOT;
}

// Write the complete AuthController.php file
file_put_contents($authControllerFile, $authControllerContent . "\n" . $restOfContent . "\n}");
chmod($authControllerFile, 0644);
echo "<p class='success'>Fixed AuthController.php successfully!</p>";

// Step 2: Create database directory and file
echo "<h2>Step 2: Setting up the Database</h2>";

// Create database directory if it doesn't exist
if (!is_dir($dbDir)) {
    if (!mkdir($dbDir, 0755, true)) {
        die("<p class='error'>Error: Failed to create database directory at: $dbDir</p>");
    }
    echo "<p class='success'>Created database directory: $dbDir</p>";
}

// Create empty database file if it doesn't exist
if (!file_exists($dbFile)) {
    if (!touch($dbFile)) {
        die("<p class='error'>Error: Failed to create database file at: $dbFile</p>");
    }
    chmod($dbFile, 0644);
    echo "<p class='success'>Created empty database file: $dbFile</p>";
}

// Step 3: Create the necessary auth tables
echo "<h2>Step 3: Creating Auth Tables</h2>";

try {
    // Create or open the database
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Create users table if it doesn't exist
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
    echo "<p class='success'>Created users table</p>";
    
    // Create password_resets table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL);
    echo "<p class='success'>Created password_resets table</p>";
    
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
    echo "<p class='success'>Created sessions table</p>";
    
    // Step 4: Add a default admin user
    echo "<h2>Step 4: Creating Default Admin User</h2>";
    
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
        echo "<p class='success'>Created default admin user</p>";
    } else {
        echo "<p>Admin user already exists</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
}

// Set proper permissions
chmod($dbDir, 0755);
chmod($dbFile, 0644);

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
    .warning {
        color: #f39c12;
        font-weight: bold;
    }
</style>
HTML;

// Final summary
echo <<<HTML
<div class="summary">
    <h2>Authentication System Fix Complete!</h2>
    <p>The authentication system has been successfully fixed:</p>
    <ul>
        <li>AuthController.php has been fixed with proper database connection handling</li>
        <li>Database directory and file have been created</li>
        <li>Auth tables have been created</li>
        <li>Default admin user has been created</li>
    </ul>
    <p>You can now login with:</p>
    <ul>
        <li><strong>Email:</strong> admin@example.com</li>
        <li><strong>Password:</strong> password</li>
    </ul>
    <p class="warning">IMPORTANT: Change these credentials immediately after logging in!</p>
    <p><a href="/">Go to Homepage</a></p>
</div>
HTML;
