<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller {
    public function showLoginForm() {
        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        // Display login form
        $this->view('auth/login', [
            'title' => 'Login - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function login() {
        // Debug: Log login attempt
        error_log('\n===== LOGIN ATTEMPT STARTED =====');
        error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('POST Data: ' . print_r($_POST, true));
        
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        error_log('CSRF Token: ' . ($token ? 'provided' : 'missing'));
        
        if (!$this->verifyCsrfToken($token)) {
            $error = 'Invalid CSRF token';
            error_log('Login failed: ' . $error);
            $_SESSION['error'] = $error;
            header('Location: /login');
            exit;
        }

        // Get form data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        error_log("Login attempt for email: $email");

        // Basic validation
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields';
            error_log('Login failed: ' . $error);
            $_SESSION['error'] = $error;
            header('Location: /login');
            exit;
        }
        
        error_log('Passed basic validation');

        // Get database connection
        try {
            $db = $this->getDbConnection();

            // Prepare and execute query
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            error_log('User found in DB: ' . ($user ? 'Yes' : 'No'));
            
            if ($user) {
                error_log('User role: ' . ($user['role'] ?? 'unknown'));
                error_log('Password hash: ' . substr($user['password'] ?? '', 0, 20) . '...');
            }

            // Verify user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                error_log('Password verification: SUCCESS');
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Redirect based on user role
                $redirect = match($user['role']) {
                    'admin' => '/admin/dashboard',
                    'company' => '/company/dashboard',
                    default => '/dashboard'
                };

                header('Location: ' . $redirect);
                exit;
            }
            
            // If we get here, login failed
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: /login');
            exit;
            
        } catch (\Exception $e) {
            error_log('Database error during login: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred during login. Please try again.';
            header('Location: /login');
            exit;
        }
    }

    public function logout() {
        // Clear all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }

    public function showRegistrationForm() {
        // Check if user is already logged in
        if (isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }
        
        // Display registration form
        $this->view('auth/register', [
            'title' => 'Register - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function register() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /register');
            exit;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';
        if ($password !== $password_confirm) $errors[] = 'Passwords do not match';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            header('Location: /register');
            exit;
        }

        // Check if email already exists
        $db = $this->getDbConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already registered';
            $_SESSION['old'] = ['name' => $name, 'email' => $email];
            header('Location: /register');
            exit;
        }

        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role
        
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, datetime("now"))');
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);

        if ($result) {
            $_SESSION['success'] = 'Registration successful! Please log in.';
            header('Location: /login');
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            header('Location: /register');
        }
        exit;
    }

    protected function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    protected function getDbConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $dsn = 'sqlite:' . DB_PATH;
                $pdo = new \PDO($dsn);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return $pdo;
    }
}
