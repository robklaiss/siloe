<?php

namespace App\Controllers;

use App\Core\Controller;

class OrderController extends Controller {
    public function __construct() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        // Get all orders
        $orders = $this->getAllOrders();
        
        // Render the orders list view
        $this->view('orders/index', [
            'title' => 'Orders - ' . APP_NAME,
            'orders' => $orders
        ]);
    }
    
    public function create() {
        // Get available menus for the order form
        $menus = $this->getAvailableMenus();
        
        // Render the order creation form
        $this->view('orders/create', [
            'title' => 'Create Order - ' . APP_NAME,
            'menus' => $menus,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function store() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /orders/create');
            exit;
        }

        // Get form data
        $menu_id = intval($_POST['menu_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        $notes = trim($_POST['notes'] ?? '');
        $user_id = $_SESSION['user_id'];

        // Basic validation
        $errors = [];
        if ($menu_id <= 0) $errors[] = 'Please select a valid menu';
        if ($quantity <= 0) $errors[] = 'Quantity must be greater than zero';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'menu_id' => $menu_id, 
                'quantity' => $quantity, 
                'notes' => $notes
            ];
            header('Location: /orders/create');
            exit;
        }

        // Verify menu exists and is available
        $menu = $this->getMenuById($menu_id);
        if (!$menu || !$menu['available']) {
            $_SESSION['error'] = 'Selected menu is not available';
            header('Location: /orders/create');
            exit;
        }

        // Calculate total price
        $total_price = $menu['price'] * $quantity;

        // Create new order
        $db = $this->getDbConnection();
        $stmt = $db->prepare('INSERT INTO orders (user_id, menu_id, quantity, total_price, notes, status, created_at) VALUES (:user_id, :menu_id, :quantity, :total_price, :notes, :status, datetime("now"))');
        $result = $stmt->execute([
            ':user_id' => $user_id,
            ':menu_id' => $menu_id,
            ':quantity' => $quantity,
            ':total_price' => $total_price,
            ':notes' => $notes,
            ':status' => 'pending'
        ]);

        if ($result) {
            $_SESSION['success'] = 'Order placed successfully';
            header('Location: /orders');
        } else {
            $_SESSION['error'] = 'Failed to place order';
            header('Location: /orders/create');
        }
        exit;
    }
    
    public function show($id) {
        // Get order by ID
        $order = $this->getOrderWithDetails($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Order not found';
            header('Location: /orders');
            exit;
        }
        
        // Check if user has access to this order
        if ($_SESSION['user_role'] !== 'admin' && $order['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'You do not have permission to view this order';
            header('Location: /orders');
            exit;
        }
        
        // Render the order details view
        $this->view('orders/show', [
            'title' => 'Order Details - ' . APP_NAME,
            'order' => $order
        ]);
    }
    
    public function edit($id) {
        // Only admin can edit orders
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to edit orders';
            header('Location: /orders');
            exit;
        }
        
        // Get order by ID
        $order = $this->getOrderWithDetails($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Order not found';
            header('Location: /orders');
            exit;
        }
        
        // Get available menus for the order form
        $menus = $this->getAvailableMenus();
        
        // Render the order edit form
        $this->view('orders/edit', [
            'title' => 'Edit Order - ' . APP_NAME,
            'order' => $order,
            'menus' => $menus,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function update($id) {
        // Only admin can update orders
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to update orders';
            header('Location: /orders');
            exit;
        }
        
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /orders/' . $id . '/edit');
            exit;
        }

        // Get order by ID
        $order = $this->getOrderById($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Order not found';
            header('Location: /orders');
            exit;
        }

        // Get form data
        $menu_id = intval($_POST['menu_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'pending';

        // Basic validation
        $errors = [];
        if ($menu_id <= 0) $errors[] = 'Please select a valid menu';
        if ($quantity <= 0) $errors[] = 'Quantity must be greater than zero';
        if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $errors[] = 'Invalid status';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'menu_id' => $menu_id, 
                'quantity' => $quantity, 
                'notes' => $notes,
                'status' => $status
            ];
            header('Location: /orders/' . $id . '/edit');
            exit;
        }

        // Verify menu exists
        $menu = $this->getMenuById($menu_id);
        if (!$menu) {
            $_SESSION['error'] = 'Selected menu does not exist';
            header('Location: /orders/' . $id . '/edit');
            exit;
        }

        // Calculate total price
        $total_price = $menu['price'] * $quantity;

        // Update order
        $db = $this->getDbConnection();
        $stmt = $db->prepare('UPDATE orders SET menu_id = :menu_id, quantity = :quantity, total_price = :total_price, notes = :notes, status = :status WHERE id = :id');
        $result = $stmt->execute([
            ':menu_id' => $menu_id,
            ':quantity' => $quantity,
            ':total_price' => $total_price,
            ':notes' => $notes,
            ':status' => $status,
            ':id' => $id
        ]);

        if ($result) {
            $_SESSION['success'] = 'Order updated successfully';
            header('Location: /orders');
        } else {
            $_SESSION['error'] = 'Failed to update order';
            header('Location: /orders/' . $id . '/edit');
        }
        exit;
    }
    
    public function destroy($id) {
        // Only admin can delete orders
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to delete orders';
            header('Location: /orders');
            exit;
        }
        
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /orders');
            exit;
        }

        // Get order by ID
        $order = $this->getOrderById($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Order not found';
            header('Location: /orders');
            exit;
        }

        // Delete order
        $db = $this->getDbConnection();
        $stmt = $db->prepare('DELETE FROM orders WHERE id = :id');
        $result = $stmt->execute([':id' => $id]);

        if ($result) {
            $_SESSION['success'] = 'Order deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete order';
        }
        
        header('Location: /orders');
        exit;
    }
    
    private function getAllOrders() {
        try {
            $db = $this->getDbConnection();
            
            // If admin, get all orders
            if ($_SESSION['user_role'] === 'admin') {
                $query = 'SELECT o.*, m.name as menu_name, u.name as user_name 
                         FROM orders o 
                         LEFT JOIN menus m ON o.menu_id = m.id 
                         LEFT JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC';
                $stmt = $db->query($query);
            } else {
                // Otherwise, only get user's orders
                $query = 'SELECT o.*, m.name as menu_name 
                         FROM orders o 
                         LEFT JOIN menus m ON o.menu_id = m.id 
                         WHERE o.user_id = :user_id 
                         ORDER BY o.created_at DESC';
                $stmt = $db->prepare($query);
                $stmt->execute([':user_id' => $_SESSION['user_id']]);
            }
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting orders: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getOrderById($id) {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT * FROM orders WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting order: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getOrderWithDetails($id) {
        try {
            $db = $this->getDbConnection();
            $query = 'SELECT o.*, m.name as menu_name, m.description as menu_description, u.name as user_name, u.email as user_email 
                     FROM orders o 
                     LEFT JOIN menus m ON o.menu_id = m.id 
                     LEFT JOIN users u ON o.user_id = u.id 
                     WHERE o.id = :id';
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting order details: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getAvailableMenus() {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->query('SELECT id, name, description, price FROM menus WHERE available = 1 ORDER BY name');
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting available menus: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getMenuById($id) {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT id, name, description, price, available FROM menus WHERE id = :id');
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