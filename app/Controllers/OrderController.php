<?php

namespace App\Controllers;

class OrderController extends Controller {
    /**
     * Get database connection
     * @return \PDO
     */
    protected function getDbConnection() {
        try {
            $db = new \PDO('sqlite:' . ROOT_PATH . '/database/siloe.db');
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            return $db;
        } catch (\PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar a la base de datos');
        }
    }

    public function __construct() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Display a single order
     * @param int $id Order ID
     */
    /**
     * Show the form for editing the specified order.
     * @param int $id Order ID
     */
    public function edit($id) {
        try {
            $db = $this->getDbConnection();
            
            // Start transaction for consistent data
            $db->beginTransaction();
            
            try {
                // Get order with user info in a single query
                $orderQuery = '
                    SELECT o.*, u.name as user_name, u.email as user_email,
                           (SELECT GROUP_CONCAT(menu_item_id) FROM order_items WHERE order_id = o.id) as item_ids
                    FROM orders o 
                    LEFT JOIN users u ON o.user_id = u.id 
                    WHERE o.id = :id';
                
                $orderStmt = $db->prepare($orderQuery);
                $orderStmt->execute([':id' => $id]);
                $order = $orderStmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$order) {
                    throw new \Exception('Order not found');
                }
                
                // Check authorization
                if ($_SESSION['user_role'] !== 'admin' && $order['user_id'] != $_SESSION['user_id']) {
                    throw new \Exception('You are not authorized to edit this order');
                }
                
                // Get order items with menu info in a single query
                $itemsQuery = '
                    SELECT oi.id as order_item_id, oi.menu_item_id, oi.quantity, 
                           mi.name, mi.price, m.name as menu_name, m.id as menu_id
                    FROM order_items oi
                    JOIN menu_items mi ON oi.menu_item_id = mi.id
                    JOIN menus m ON mi.menu_id = m.id
                    WHERE oi.order_id = :order_id';
                
                $itemStmt = $db->prepare($itemsQuery);
                $itemStmt->execute([':order_id' => $id]);
                $order['items'] = $itemStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Calculate totals
                $order['total_amount'] = array_reduce($order['items'], function($total, $item) {
                    return $total + ($item['quantity'] * $item['price']);
                }, 0);
                
                // Get available menu items (cached if possible)
                $cacheKey = 'available_menu_items';
                if (!isset($GLOBALS[$cacheKey])) {
                    $menuItemsStmt = $db->query('
                        SELECT mi.id, mi.name, m.name as menu_name, 
                               mi.price, m.id as menu_id, mi.description
                        FROM menu_items mi
                        JOIN menus m ON mi.menu_id = m.id
                        WHERE mi.is_available = 1
                        ORDER BY m.name, mi.name
                    ');
                    $GLOBALS[$cacheKey] = $menuItemsStmt->fetchAll(\PDO::FETCH_ASSOC);
                }
                $menuItems = $GLOBALS[$cacheKey];
                
                // Get users (admin only, cached)
                $users = [];
                if ($_SESSION['user_role'] === 'admin') {
                    $cacheKey = 'all_users';
                    if (!isset($GLOBALS[$cacheKey])) {
                        $usersStmt = $db->query('SELECT id, name, email FROM users ORDER BY name');
                        $GLOBALS[$cacheKey] = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);
                    }
                    $users = $GLOBALS[$cacheKey];
                }
                
                $db->commit();
                
                // Prepare view data
                $viewData = [
                    'title' => 'Editar Pedido #' . $order['id'] . ' - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
                    'order' => $order,
                    'menuItems' => $menuItems,
                    'menus' => $menuItems, // For backward compatibility
                    'users' => $users,
                    'statuses' => [
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado'
                    ],
                    'csrf_token' => $_SESSION['csrf_token'] ?? ''
                ];
                
                // Add any old input from previous failed validation
                if (isset($_SESSION['old'])) {
                    $viewData['old'] = $_SESSION['old'];
                    unset($_SESSION['old']);
                }
                
                $viewData['hideNavbar'] = true;
                $viewData['wrapContainer'] = false;
                $viewData['sidebarTitle'] = 'Siloe empresas';
                $viewData['active'] = 'orders';
                return $this->view('orders/edit', $viewData)->layout('layouts/app');
                
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log('Error in OrderController::edit(): ' . $e->getMessage());
            $_SESSION['error'] = 'No se pudo cargar el pedido para editar. ' . $e->getMessage();
            header('Location: /orders');
            exit;
        }
    }
    
    /**
     * Display the specified order.
     * @param int $id Order ID
     */
    /**
     * Update the specified order in storage.
     * @param int $id Order ID
     */
    public function update($id) {
        try {
            // Verify CSRF token
            if (!isset($_POST['_token']) || $_POST['_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            $db = $this->getDbConnection();
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Get the order
                $stmt = $db->prepare('SELECT * FROM orders WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new \Exception('Order not found');
                }
                
                // Check if user is authorized to update this order
                if ($_SESSION['user_role'] !== 'admin' && $order['user_id'] !== $_SESSION['user_id']) {
                    throw new \Exception('You are not authorized to update this order');
                }
                
                // Validate input
                $status = $_POST['status'] ?? $order['status'];
                $specialRequests = trim($_POST['special_requests'] ?? $order['special_requests'] ?? '');
                
                // Update order
                $updateStmt = $db->prepare('
                    UPDATE orders 
                    SET status = :status, 
                        special_requests = :special_requests,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ');
                
                $updateStmt->execute([
                    ':status' => $status,
                    ':special_requests' => $specialRequests,
                    ':id' => $id
                ]);
                
                // Commit transaction
                $db->commit();
                
                $_SESSION['success'] = 'Pedido actualizado con éxito';
                header('Location: /orders/' . $id);
                exit;
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                $db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log('Error in OrderController::update(): ' . $e->getMessage());
            $_SESSION['error'] = 'No se pudo actualizar el pedido. ' . $e->getMessage();
            
            // Store old input for form repopulation
            $_SESSION['old'] = $_POST;
            
            header('Location: /orders/' . $id . '/edit');
            exit;
        }
    }
    
    /**
     * Display the specified order.
     * @param int $id Order ID
     */
    /**
     * Update order status via AJAX
     * @param int $id Order ID
     */
    public function updateStatus($id) {
        header('Content-Type: application/json');
        
        try {
            // Verify CSRF token
            if (!isset($_POST['_token']) || $_POST['_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Validate status
            $status = $_POST['status'] ?? '';
            $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
            
            if (!in_array($status, $validStatuses)) {
                throw new \Exception('Estado inválido');
            }
            
            $db = $this->getDbConnection();
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Get the order
                $stmt = $db->prepare('SELECT * FROM orders WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new \Exception('Order not found');
                }
                
                // Check if user is authorized to update this order
                if ($_SESSION['user_role'] !== 'admin' && $order['user_id'] !== $_SESSION['user_id']) {
                    throw new \Exception('You are not authorized to update this order');
                }
                
                // Update status
                $updateStmt = $db->prepare('UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                $updateStmt->execute([
                    ':status' => $status,
                    ':id' => $id
                ]);
                
                // Commit transaction
                $db->commit();
                
                // Return success response
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado del pedido actualizado con éxito',
                    'status' => $status
                ]);
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                $db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
    
    /**
     * Display the specified order.
     * @param int $id Order ID
     */
    public function show($id) {
        try {
            $db = $this->getDbConnection();
            
            // Get order details
            $query = 'SELECT o.*, u.name as user_name, u.email as user_email
                     FROM orders o 
                     LEFT JOIN users u ON o.user_id = u.id 
                     WHERE o.id = :id';
            
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new \Exception('Order not found');
            }
            
            // Check if user is authorized to view this order
            if ($_SESSION['user_role'] !== 'admin' && $order['user_id'] !== $_SESSION['user_id']) {
                $_SESSION['error'] = 'No está autorizado para ver este pedido';
                header('Location: /orders');
                exit;
            }
            
            // Get order items
            $itemStmt = $db->prepare('
                SELECT mi.name, oi.quantity, mi.price, mi.id as menu_item_id, m.name as menu_name,
                       (oi.quantity * mi.price) as item_total
                FROM order_items oi
                JOIN menu_items mi ON oi.menu_item_id = mi.id
                JOIN menus m ON mi.menu_id = m.id
                WHERE oi.order_id = :order_id
            ');
            $itemStmt->execute([':order_id' => $id]);
            $items = $itemStmt->fetchAll();
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['item_total'];
            }
            $order['items'] = $items;
            $order['total_amount'] = $totalAmount;
            
            // Render the order detail view
            return $this->view('orders/show', [
                'title' => 'Pedido #' . $order['id'] . ' - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
                'order' => $order,
                'hideNavbar' => true,
                'wrapContainer' => false,
                'sidebarTitle' => 'Siloe empresas',
                'active' => 'orders'
            ])->layout('layouts/app');
            
        } catch (\Exception $e) {
            error_log('Error in OrderController::show(): ' . $e->getMessage());
            $_SESSION['error'] = 'No se pudo cargar el pedido. ' . $e->getMessage();
            header('Location: /orders');
            exit;
        }
    }
    
    public function index() {
        try {
            $db = $this->getDbConnection();
            
            // If admin, get all orders with user info
            if ($_SESSION['user_role'] === 'admin') {
                $query = 'SELECT o.*, u.name as user_name, u.email as user_email,
                         (SELECT SUM(oi.quantity * mi.price) 
                          FROM order_items oi 
                          JOIN menu_items mi ON oi.menu_item_id = mi.id 
                          WHERE oi.order_id = o.id) as total_amount 
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC';
                $stmt = $db->query($query);
            } else {
                // Otherwise, only get user's orders
                $query = 'SELECT o.*, 
                         (SELECT SUM(oi.quantity * mi.price) 
                          FROM order_items oi 
                          JOIN menu_items mi ON oi.menu_item_id = mi.id 
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
                    SELECT mi.name, oi.quantity, mi.price, mi.id as menu_item_id, m.name as menu_name
                    FROM order_items oi
                    JOIN menu_items mi ON oi.menu_item_id = mi.id
                    JOIN menus m ON mi.menu_id = m.id
                    WHERE oi.order_id = :order_id
                ');
                $itemStmt->execute([':order_id' => $orderId]);
                $items = $itemStmt->fetchAll();
                $order['items'] = $items;
                
                // Calculate total amount if not already set
                if (!isset($order['total_amount'])) {
                    $total = 0;
                    foreach ($items as $item) {
                        $total += $item['quantity'] * $item['price'];
                    }
                    $order['total_amount'] = $total;
                }
                
                // Create items summary
                $summaryArr = [];
                foreach ($items as $item) {
                    $summaryArr[] = $item['name'] . ' x' . $item['quantity'];
                }
                $order['items_summary'] = implode(', ', $summaryArr);
            }
            unset($order); // Break the reference
            
            // Debug: Log the orders to check data
            error_log('Orders data: ' . print_r($orders, true));
            
            // Render the orders list view
            return $this->view('orders/index', [
                'title' => 'Pedidos - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
                'orders' => $orders,
                'hideNavbar' => true,
                'wrapContainer' => false,
                'sidebarTitle' => 'Siloe empresas',
                'active' => 'orders'
            ])->layout('layouts/app');
            
        } catch (\Exception $e) {
            error_log('Error in OrderController::index(): ' . $e->getMessage());
            $_SESSION['error'] = 'No se pudieron cargar los pedidos. Por favor, inténtelo de nuevo.';
            header('Location: /dashboard');
            exit;
        }
    }
}
