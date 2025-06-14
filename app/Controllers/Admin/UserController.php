<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class UserController extends Controller {
    public function __construct() {
        // Check if user is logged in and is an admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        // Get all users
        $users = $this->getAllUsers();
        
        // Render the users list view
        $this->view('admin/users/index', [
            'title' => 'Manage Users - ' . APP_NAME,
            'users' => $users
        ]);
    }
    
    public function create() {
        // Prevent browser caching of the form page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Render the user creation form
        $this->view('admin/users/create', [
            'title' => 'Create User - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function store() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
            header('Location: /admin/users/create');
            exit;
        }
        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'employee';

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/create');
            exit;
        }

        // Check if email already exists
        $db = $this->getDbConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already registered';
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/create');
            exit;
        }

        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, :role, datetime("now"))');
        $result = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);

        if ($result) {
            $_SESSION['success'] = 'User created successfully';
            header('Location: /admin/users');
        } else {
            $_SESSION['error'] = 'Failed to create user';
            header('Location: /admin/users/create');
        }
        exit;
    }
    
    public function edit(Request $request, Response $response, $id) {
        // Prevent browser caching of the form page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Get user by ID
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: /admin/users');
            exit;
        }
        
        // Render the user edit form
        $this->view('admin/users/edit', [
            'title' => 'Edit User - ' . APP_NAME,
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function update(Request $request, Response $response, $id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }
        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // Get user by ID
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: /admin/users');
            exit;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'employee';
        $password = $_POST['password'] ?? '';

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (!empty($password) && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }

        // Check if email already exists (for another user)
        $db = $this->getDbConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND id != :id');
        $stmt->execute([':email' => $email, ':id' => $id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already registered to another user';
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }

        // Update user
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':role' => $role,
            ':id' => $id
        ];
        
        $sql = 'UPDATE users SET name = :name, email = :email, role = :role';
        
        // Only update password if provided
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ', password = :password';
            $params[':password'] = $hashedPassword;
        }
        
        $sql .= ' WHERE id = :id';
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            $_SESSION['success'] = 'User updated successfully';
            header('Location: /admin/users');
        } else {
            $_SESSION['error'] = 'Failed to update user';
            header('Location: /admin/users/' . $id . '/edit');
        }
        exit;
    }
    
    public function destroy(Request $request, Response $response, $id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token. Please try again.';
            header('Location: /admin/users');
            exit;
        }
        // Invalidate the used token by generating a new one for the session.
        $this->generateCsrfToken();

        // Get user by ID
        $user = $this->getUserById($id);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: /admin/users');
            exit;
        }
        
        // Prevent deleting self
        if ($user['id'] == $_SESSION['user_id']) {
            $_SESSION['error'] = 'You cannot delete your own account';
            header('Location: /admin/users');
            exit;
        }

        // Delete user
        $db = $this->getDbConnection();
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        $result = $stmt->execute([':id' => $id]);

        if ($result) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    private function getAllUsers() {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC');
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting users: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getUserById($id) {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting user: ' . $e->getMessage());
            return null;
        }
    }
    
    protected function generateCsrfToken(): string {
        // Always generate a new token to prevent reuse
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
