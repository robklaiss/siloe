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
        
        // Get users for the order form
        $users = $this->getAllUsers();
        
        // Render the order creation form
        $this->view('orders/create', [
            'title' => 'Create Order - ' . APP_NAME,
            'menus' => $menus,
            'users' => $users,
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
        
        // Check for duplicate submission using submission_id
        $submissionId = $_POST['submission_id'] ?? '';
        
        // If we have a submission ID, check if it matches the last one processed
        if (!empty($submissionId) && isset($_SESSION['processed_submissions']) && in_array($submissionId, $_SESSION['processed_submissions'])) {
            // This is a duplicate submission - redirect to orders list
            header('Location: /orders');
            exit;
        }
        
        // Get form data
        $menu_id = intval($_POST['menu_id'] ?? 0);
        $user_id = intval($_POST['user_id'] ?? $_SESSION['user_id']);
        $quantity = intval($_POST['quantity'] ?? 1);
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'pending';

        // Basic validation
        $errors = [];
        if ($menu_id <= 0) $errors[] = 'Please select a valid menu';
        if ($user_id <= 0) $errors[] = 'Please select a valid user';
        if ($quantity <= 0) $errors[] = 'Quantity must be greater than zero';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'menu_id' => $menu_id,
                'user_id' => $user_id,
                'quantity' => $quantity, 
                'notes' => $notes,
                'status' => $status
            ];
            header('Location: /orders/create');
            exit;
        }

        // Verify menu exists and is available
        $menu = $this->getMenuById($menu_id);
        if (!$menu || $menu['available'] != 1) {
            $_SESSION['error'] = 'Selected menu is not available';
            header('Location: /orders/create');
            exit;
        }

        // Calculate total price
        $total_price = $menu['price'] * $quantity;

        try {
            $db = $this->getDbConnection();
            $db->beginTransaction();
            
            // Get user's company_id
            $stmt = $db->prepare('SELECT company_id FROM users WHERE id = :user_id');
            $stmt->execute([':user_id' => $user_id]);
            $user = $stmt->fetch();
            $company_id = $user['company_id'] ?? 1; // Default to company ID 1 if not found
            
            // Users are allowed to place identical orders on the same day
            
            // Create new order
            $stmt = $db->prepare('INSERT INTO orders (user_id, company_id, order_date, status, special_requests, created_at) VALUES (:user_id, :company_id, date("now"), :status, :special_requests, datetime("now"))');
            $stmt->execute([
                ':user_id' => $user_id,
                ':company_id' => $company_id,
                ':status' => $status,
                ':special_requests' => $notes
            ]);
            
            // Get the new order ID
            $order_id = $db->lastInsertId();
            
            // Create order item entry
            $stmt = $db->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, special_requests) VALUES (:order_id, :menu_item_id, :quantity, :special_requests)');
            $stmt->execute([
                ':order_id' => $order_id,
                ':menu_item_id' => $menu_id,
                ':quantity' => $quantity,
                ':special_requests' => $notes
            ]);
            
            $db->commit();
            
            // Store the submission ID to prevent duplicate processing
            if (!empty($submissionId)) {
                if (!isset($_SESSION['processed_submissions'])) {
                    $_SESSION['processed_submissions'] = [];
                }
                // Keep only the last 5 submissions to avoid session bloat
                if (count($_SESSION['processed_submissions']) >= 5) {
                    array_shift($_SESSION['processed_submissions']);
                }
                $_SESSION['processed_submissions'][] = $submissionId;
            }
            
            $_SESSION['success'] = 'Order placed successfully';
            header('Location: /orders');
        } catch (\PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            // Check if this is a constraint violation (duplicate entry)
            if ($e->getCode() == '23000' || strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $_SESSION['error'] = 'This exact order was already submitted. Please check your orders list.';
            } else {
                // Log the detailed error for debugging
                error_log('Order creation error: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to place order. Please try again.';
            }
            
            // Store form data in session for repopulating the form
            $_SESSION['old'] = [
                'user_id' => $user_id,
                'menu_id' => $menu_id, 
                'quantity' => $quantity, 
                'notes' => $notes,
                'status' => $status
            ];
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
        
        // Get all users for the dropdown
        $users = $this->getAllUsers();
        
        // Render the order edit form
        $this->view('orders/edit', [
            'title' => 'Edit Order - ' . APP_NAME,
            'order' => $order,
            'menus' => $menus,
            'users' => $users,
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
        $db->beginTransaction();
        
        try {
            // Update order basic info
            $stmt = $db->prepare('UPDATE orders SET special_requests = :special_requests, status = :status WHERE id = :id');
            $stmt->execute([
                ':special_requests' => $notes,
                ':status' => $status,
                ':id' => $id
            ]);
            
            // Check if order item exists
            $stmt = $db->prepare('SELECT id FROM order_items WHERE order_id = :order_id LIMIT 1');
            $stmt->execute([':order_id' => $id]);
            $orderItem = $stmt->fetch();
            
            if ($orderItem) {
                // Update existing order item
                $stmt = $db->prepare('UPDATE order_items SET menu_item_id = :menu_item_id, quantity = :quantity, special_requests = :special_requests WHERE order_id = :order_id');
                $stmt->execute([
                    ':menu_item_id' => $menu_id,
                    ':quantity' => $quantity,
                    ':special_requests' => $notes,
                    ':order_id' => $id
                ]);
            } else {
                // Create new order item if none exists
                $stmt = $db->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, special_requests) VALUES (:order_id, :menu_item_id, :quantity, :special_requests)');
                $stmt->execute([
                    ':order_id' => $id,
                    ':menu_item_id' => $menu_id,
                    ':quantity' => $quantity,
                    ':special_requests' => $notes
                ]);
            }
            
            $db->commit();
            $result = true;
        } catch (\PDOException $e) {
            $db->rollBack();
            error_log('Order update error: ' . $e->getMessage());
            $result = false;
        }

        if ($result) {
            $_SESSION['success'] = 'Order updated successfully';
            header('Location: /orders');
        } else {
            $_SESSION['error'] = 'Failed to update order';
            header('Location: /orders/' . $id . '/edit');
        }
        exit;
    }
    
    public function updateStatus($id) {
        // Only admin can update order status
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'You do not have permission to update order status';
            header('Location: /orders');
            exit;
        }
        
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /orders/' . $id);
            exit;
        }
        
        // Get status from form
        $status = $_POST['status'] ?? '';
        
        // Validate status
        if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
            $_SESSION['error'] = 'Invalid status';
            header('Location: /orders/' . $id);
            exit;
        }
        
        // Get order by ID
        $order = $this->getOrderById($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Order not found';
            header('Location: /orders');
            exit;
        }
        
        // Update order status
        $db = $this->getDbConnection();
        $stmt = $db->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $result = $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);
        
        if ($result) {
            $_SESSION['success'] = 'Order status updated to ' . ucfirst($status);
        } else {
            $_SESSION['error'] = 'Failed to update order status';
        }
        
        header('Location: /orders/' . $id);
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
        
        // Instead of deleting, consider marking as cancelled first
        if ($order['status'] !== 'cancelled') {
            $_SESSION['error'] = 'Please cancel the order before deleting it';
            header('Location: /orders/' . $id);
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
                $query = 'SELECT o.*, u.name as user_name, 
                         (SELECT SUM(oi.quantity * m.price) 
                          FROM order_items oi 
                          JOIN menus m ON oi.menu_item_id = m.id 
                          WHERE oi.order_id = o.id) as total_amount 
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC';
                $stmt = $db->query($query);
            } else {
                // Otherwise, only get user's orders
                $query = 'SELECT o.*, 
                         (SELECT SUM(oi.quantity * m.price) 
                          FROM order_items oi 
                          JOIN menus m ON oi.menu_item_id = m.id 
                          WHERE oi.order_id = o.id) as total_amount 
                         FROM orders o 
                         WHERE o.user_id = :user_id 
                         ORDER BY o.created_at DESC';
                $stmt = $db->prepare($query);
                $stmt->execute([':user_id' => $_SESSION['user_id']]);
            }
            $orders = $stmt->fetchAll();

            // Attach ordered items summary to each order
            foreach ($orders as &$order) {
                $orderId = $order['id'];
                $itemStmt = $db->prepare('
                    SELECT m.name, oi.quantity
                    FROM order_items oi
                    JOIN menus m ON oi.menu_item_id = m.id
                    WHERE oi.order_id = :order_id
                ');
                $itemStmt->execute([':order_id' => $orderId]);
                $items = $itemStmt->fetchAll();
                $summaryArr = [];
                foreach ($items as $item) {
                    $summaryArr[] = $item['name'] . ' x' . $item['quantity'];
                }
                $order['items_summary'] = implode(', ', $summaryArr);
            }
            unset($order);
            return $orders;
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
            $query = 'SELECT o.*, u.name as user_name, u.email as user_email, 
                     oi.menu_item_id as menu_id, oi.quantity, m.price, m.name as menu_name
                     FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     LEFT JOIN order_items oi ON o.id = oi.order_id
                     LEFT JOIN menus m ON oi.menu_item_id = m.id
                     WHERE o.id = :id';
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            $order = $stmt->fetch();
            
            // Calculate total if we have menu price and quantity
            if ($order && isset($order['price']) && isset($order['quantity'])) {
                $order['total'] = $order['price'] * $order['quantity'];
            }
            
            return $order;
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
    
    private function getAllUsers() {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->query('SELECT id, name, email FROM users ORDER BY name');
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log('Error getting users: ' . $e->getMessage());
            return [];
        }
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