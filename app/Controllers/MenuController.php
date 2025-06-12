<?php

namespace App\Controllers;

use App\Core\Controller;

class MenuController extends Controller {
    public function __construct() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        // Get all menus
        $menus = $this->getAllMenus();
        
        // Render the menus list view
        $this->view('menus/index', [
            'title' => 'Menus - ' . APP_NAME,
            'menus' => $menus
        ]);
    }
    
    public function create() {
        // Render the menu creation form
        $this->view('menus/create', [
            'title' => 'Create Menu - ' . APP_NAME,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function store() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /menus/create');
            exit;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $available = isset($_POST['available']) ? 1 : 0;

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if ($price <= 0) $errors[] = 'Price must be greater than zero';
        if (empty($date)) $errors[] = 'Date is required';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'date' => $date,
                'available' => $available
            ];
            header('Location: /menus/create');
            exit;
        }

        // Get database connection
        $db = $this->getDbConnection();
        
        // Debug approach - check if the menu item already exists first
        // Check if a menu with this name and description already exists
        $checkStmt = $db->prepare('SELECT COUNT(*) as count FROM menus WHERE name = :name AND description = :description');
        $checkStmt->execute([
            ':name' => $name,
            ':description' => $description
        ]);
        $checkResult = $checkStmt->fetch();
        
        // Debug output
        error_log("Menu check - Name: {$name}, Description: {$description}");
        error_log("Menu check - Count: {$checkResult['count']}");
        
        // If a menu with this name and description already exists, show an error
        if ($checkResult && $checkResult['count'] > 0) {
            $_SESSION['error'] = 'A menu item with this name and description already exists';
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'date' => $date,
                'available' => $available
            ];
            header('Location: /menus/create');
            exit;
        }
        
        // If we get here, the menu item doesn't exist, so create it
        try {
            // Create new menu
            $stmt = $db->prepare('INSERT INTO menus (name, description, price, date, available, is_active) VALUES (:name, :description, :price, :date, :available, :is_active)');
            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':date' => $date,
                ':available' => $available,
                ':is_active' => $available // Using available as is_active
            ]);
            
            // Debug output
            error_log("Menu insert - Result: " . ($result ? 'success' : 'failure'));
            
            // Set success message
            $_SESSION['success'] = 'Menu created successfully';
            header('Location: /menus');
            exit;
        } catch (\PDOException $e) {
            // Debug output
            error_log("Menu insert - Error: {$e->getMessage()}");
            
            $_SESSION['error'] = 'Failed to create menu: ' . $e->getMessage();
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'date' => $date,
                'available' => $available
            ];
            header('Location: /menus/create');
            exit;
        }

        // Success/failure handling is now done inside the transaction block
        exit;
    }
    
    public function edit($id) {
        // Get menu by ID
        $menu = $this->getMenuById($id);
        
        if (!$menu) {
            $_SESSION['error'] = 'Menu not found';
            header('Location: /menus');
            exit;
        }
        
        // Render the menu edit form
        $this->view('menus/edit', [
            'title' => 'Edit Menu - ' . APP_NAME,
            'menu' => $menu,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function update($id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /menus/' . $id . '/edit');
            exit;
        }

        // Get menu by ID
        $menu = $this->getMenuById($id);
        
        if (!$menu) {
            $_SESSION['error'] = 'Menu not found';
            header('Location: /menus');
            exit;
        }

        // Get form data
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $available = isset($_POST['available']) ? 1 : 0;

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if ($price <= 0) $errors[] = 'Price must be greater than zero';
        if (empty($date)) $errors[] = 'Date is required';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'date' => $date,
                'available' => $available
            ];
            header('Location: /menus/' . $id . '/edit');
            exit;
        }

        // Update menu
        $db = $this->getDbConnection();
        $stmt = $db->prepare('UPDATE menus SET name = :name, description = :description, price = :price, date = :date, available = :available WHERE id = :id');
        $result = $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':date' => $date,
            ':available' => $available,
            ':id' => $id
        ]);

        if ($result) {
            $_SESSION['success'] = 'Menu updated successfully';
            header('Location: /menus');
        } else {
            $_SESSION['error'] = 'Failed to update menu';
            header('Location: /menus/' . $id . '/edit');
        }
        exit;
    }
    
    public function destroy($id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /menus');
            exit;
        }

        // Get menu by ID
        $menu = $this->getMenuById($id);
        
        if (!$menu) {
            $_SESSION['error'] = 'Menu not found';
            header('Location: /menus');
            exit;
        }

        // Check if menu has associated menu items
        $db = $this->getDbConnection();
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM menu_items WHERE menu_id = :menu_id');
        $stmt->execute([':menu_id' => $id]);
        $result = $stmt->fetch();
        
        if ($result && $result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete menu with associated menu items';
            header('Location: /menus');
            exit;
        }

        // Delete menu
        $stmt = $db->prepare('DELETE FROM menus WHERE id = :id');
        $result = $stmt->execute([':id' => $id]);

        if ($result) {
            $_SESSION['success'] = 'Menu deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete menu';
        }
        
        header('Location: /menus');
        exit;
    }
    
    private function getAllMenus() {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->query('SELECT id, name, description, price, date, available, created_at FROM menus ORDER BY date DESC');
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting menus: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getMenuById($id) {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT id, name, description, price, date, available, created_at FROM menus WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting menu: ' . $e->getMessage());
            return null;
        }
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
