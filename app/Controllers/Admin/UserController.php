<?php

namespace App\Controllers\Admin;

class UserController extends Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        // Build grouped datasets for accordions
        $db = $this->getDbConnection();

        // System Admins (admins without company scope)
        $stmt = $db->query("SELECT u.id, u.name, u.email, u.role, u.created_at, u.company_id, c.name AS company_name
                             FROM users u
                             LEFT JOIN companies c ON c.id = u.company_id
                             WHERE u.role = 'admin' AND u.company_id IS NULL
                             ORDER BY u.name ASC");
        $systemAdmins = $stmt->fetchAll();

        // Normal Admins (admins associated to a company, if any)
        $stmt = $db->query("SELECT u.id, u.name, u.email, u.role, u.created_at, u.company_id, c.name AS company_name
                             FROM users u
                             LEFT JOIN companies c ON c.id = u.company_id
                             WHERE u.role = 'admin' AND u.company_id IS NOT NULL
                             ORDER BY c.name ASC, u.name ASC");
        $normalAdmins = $stmt->fetchAll();

        // HR Manager Admins (company_admin)
        $stmt = $db->query("SELECT u.id, u.name, u.email, u.role, u.created_at, u.company_id, c.name AS company_name
                             FROM users u
                             LEFT JOIN companies c ON c.id = u.company_id
                             WHERE u.role = 'company_admin'
                             ORDER BY c.name ASC, u.name ASC");
        $hrManagers = $stmt->fetchAll();

        // Main System Users (generic users not tied to company workflows)
        $stmt = $db->query("SELECT u.id, u.name, u.email, u.role, u.created_at
                             FROM users u
                             WHERE u.role = 'user'
                             ORDER BY u.name ASC");
        $systemUsers = $stmt->fetchAll();

        // Employees are managed per company; show a global count only
        $employeesTotal = (int)($db->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'employee'")->fetch()['cnt'] ?? 0);

        // Render the grouped users view
        return $this->view('admin/users/index', [
            'title' => 'Manage Users - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'system_admins' => $systemAdmins,
            'normal_admins' => $normalAdmins,
            'hr_managers' => $hrManagers,
            'system_users' => $systemUsers,
            'employees_total' => $employeesTotal,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function create() {
        // Prevent browser caching of the form page
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        // Get all companies for the company selection dropdown
        $companyModel = new \App\Models\Company();
        $companies = $companyModel->getAllCompanies();

        // Render the user creation form
        return $this->view('admin/users/create', [
            'title' => 'Create User - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken(),
            'companies' => $companies
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
        $companyId = ($_POST['company_id'] ?? null) ? (int)$_POST['company_id'] : null;

        // Basic validation
        $errors = [];
        // Enforce role whitelist to satisfy DB CHECK constraint
        $allowedRoles = ['admin', 'company_admin', 'employee'];
        if (!in_array($role, $allowedRoles, true)) {
            $errors[] = 'Invalid role selected';
        }
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';
        
        // Validate company for company admins
        if ($role === 'company_admin' && empty($companyId)) {
            $errors[] = 'Company is required for Company Admin role';
        }

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
        
        // Prepare the query based on whether we have a company_id
        $query = 'INSERT INTO users (name, email, password, role, company_id, created_at) 
                 VALUES (:name, :email, :password, :role, :company_id, datetime("now"))';
        
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role,
            ':company_id' => $companyId
        ];
        
        $stmt = $db->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            $_SESSION['success'] = 'User created successfully';
            header('Location: /admin/users');
        } else {
            $_SESSION['error'] = 'Failed to create user';
            header('Location: /admin/users/create');
        }
        exit;
    }
    
    public function edit($id) {
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
        return $this->view('admin/users/edit', [
            'title' => 'Edit User - ' . APP_NAME,
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function update($id) {
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
        // Enforce role whitelist to satisfy DB CHECK constraint
        $allowedRoles = ['admin', 'company_admin', 'employee'];
        if (!in_array($role, $allowedRoles, true)) {
            $errors[] = 'Invalid role selected';
        }
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (!empty($password) && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';
        // If password provided, ensure confirmation matches (field name: password_confirm)
        if (!empty($password)) {
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            if ($password !== $passwordConfirm) {
                $errors[] = 'Password confirmation does not match';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }

        try {
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

            // Build update statement
            $params = [
                ':name' => $name,
                ':email' => $email,
                ':role' => $role,
                ':id' => $id
            ];

            $sql = 'UPDATE users SET name = :name, email = :email, role = :role';

            // Only update password if provided
            if (!empty($password)) {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                } catch (\Throwable $th) {
                    error_log('Password hash error in Admin\\UserController::update: ' . $th->getMessage());
                    $_SESSION['error'] = 'Failed to process password. Please try again.';
                    $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
                    header('Location: /admin/users/' . $id . '/edit');
                    exit;
                }
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
        } catch (\PDOException $e) {
            error_log('PDO error in Admin\\UserController::update: ' . $e->getMessage());
            $_SESSION['error'] = 'A database error occurred while updating the user.';
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        } catch (\Throwable $e) {
            error_log('Unexpected error in Admin\\UserController::update: ' . $e->getMessage());
            $_SESSION['error'] = 'An unexpected error occurred while updating the user.';
            $_SESSION['old'] = ['name' => $name, 'email' => $email, 'role' => $role];
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }
    }
    
    public function destroy($id) {
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

    protected function verifyCsrfToken($token) {
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
