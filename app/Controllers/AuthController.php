<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;
use PDOException;

class AuthController extends Controller {
    /**
     * Get a database connection
     * 
     * @return PDO
     * @throws PDOException
     */
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
                    // Fallback to SQLite
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
    public function showLoginForm() {
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
    
    /**
     * Show the employee login form for a specific company
     * 
     * @param int $company_id The ID of the company
     */
    public function showEmployeeLoginForm($company_id) {
        // If already logged in, redirect to the appropriate company-scoped dashboard
        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'] ?? null;
            $sessionCompanyId = $_SESSION['company_id'] ?? null;

            if ($sessionCompanyId == $company_id) {
                if ($role === 'company_admin') {
                    header('Location: /admin/companies/' . $company_id . '/hr');
                } else { // hr or employee
                    header('Location: /hr/' . $company_id . '/dashboard');
                }
                exit;
            }
            // If logged into a different company, fall through to show the form so the user can switch
        }
        
        // Get company info
        $companyModel = new \App\Models\Company();
        $company = $companyModel->getCompanyById($company_id);
        
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada';
            header('Location: /login');
            exit;
        }
        
        // Display employee login form
        $this->view('auth/employee_login', [
            'title' => 'Inicio de sesión de empleados - ' . $company['name'],
            'company' => $company,
            'company_id' => $company_id,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Handle employee login for a specific company
     * 
     * @param int $company_id The ID of the company
     */
    public function employeeLogin($company_id) {
        // Verify CSRF token
        $token = $_POST['_token'] ?? '';
        if (!$this->verifyCsrfToken($token)) {
            $_SESSION['error'] = 'Token CSRF inválido';
            header('Location: /hr/' . $company_id . '/login');
            exit;
        }

        // Get form data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Por favor completa todos los campos';
            header('Location: /hr/' . $company_id . '/login');
            exit;
        }

        // Get database connection
        try {
            $db = $this->getDbConnection();

            // Prepare and execute query to find user with matching email and company
            // Allow multiple roles (employee, hr, company_admin) to authenticate via company login
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND company_id = :company_id LIMIT 1');
            $stmt->execute([
                ':email' => $email,
                ':company_id' => $company_id
            ]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Verify user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Only allow specific roles to use the company login portal
                $allowedRoles = ['employee', 'hr', 'company_admin'];
                if (!in_array($user['role'], $allowedRoles, true)) {
                    $_SESSION['error'] = 'Esta cuenta no tiene permiso para iniciar sesión aquí.';
                    header('Location: /hr/' . $company_id . '/login');
                    exit;
                }

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['company_id'] = $company_id;
                $_SESSION['last_activity'] = time();
                // Store display name for greetings
                $_SESSION['user_name'] = $user['name'] ?? (isset($user['email']) ? explode('@', $user['email'])[0] : 'Usuario');

                // Redirect based on role
                if ($user['role'] === 'hr') {
                    header('Location: /hr/' . $company_id . '/dashboard');
                } elseif ($user['role'] === 'company_admin') {
                    header('Location: /admin/companies/' . $company_id . '/hr');
                } else { // employee
                    header('Location: /hr/' . $company_id . '/dashboard');
                }
                exit;
            }
            
            // If we get here, login failed
            $_SESSION['error'] = 'Correo electrónico o contraseña inválidos';
            header('Location: /hr/' . $company_id . '/login');
            exit;
            
        } catch (\PDOException $e) {
            error_log('Database error during employee login: ' . $e->getMessage());
            $_SESSION['error'] = 'Ocurrió un error durante el inicio de sesión. Inténtalo nuevamente.';
            header('Location: /hr/' . $company_id . '/login');
            exit;
        }
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
                // Store display name for greetings
                $_SESSION['user_name'] = $user['name'] ?? (isset($user['email']) ? explode('@', $user['email'])[0] : 'Usuario');
                
                // Set company_id in session if available
                if (isset($user['company_id'])) {
                    $_SESSION['company_id'] = $user['company_id'];
                    error_log('Set company_id in session: ' . $user['company_id']);
                } else {
                    error_log('No company_id found for user: ' . $user['email']);
                }

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
        // Determine redirect target before clearing session
        $redirect = '/login';
        if (isset($_SESSION['company_id']) && $_SESSION['company_id']) {
            $redirect = '/hr/' . $_SESSION['company_id'] . '/login';
        }

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
        
        // Redirect to appropriate login page
        header('Location: ' . $redirect);
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

    protected function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // getDbConnection method is already defined at the top of the class
}
