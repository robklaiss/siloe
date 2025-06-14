<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class DashboardController extends Controller {
    public function __construct() {
        // Check if user is logged in and is an admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }
    
    public function index() {
        // Get some basic stats for the dashboard
        $stats = $this->getStats();
        
        // Render the admin dashboard view
        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard - ' . APP_NAME,
            'stats' => $stats
        ]);
    }
    
    private function getStats() {
        try {
            $db = $this->getDbConnection();
            
            // Get user count
            $userCount = $db->query('SELECT COUNT(*) as count FROM users')->fetch()['count'];
            
            // Get latest 5 menus
            $latestMenus = $db->query('SELECT * FROM menus ORDER BY created_at DESC LIMIT 5')->fetchAll();
            
            // Get latest 5 orders with user names and total amount
            $latestOrders = $db->query('
                SELECT o.*, u.name as user_name,
                (SELECT SUM(oi.quantity * m.price) 
                 FROM order_items oi 
                 JOIN menus m ON oi.menu_item_id = m.id 
                 WHERE oi.order_id = o.id) as total_amount
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT 5
            ')->fetchAll();
            
            return [
                'users' => $userCount,
                'last_login' => date('Y-m-d H:i:s'),
                'latest_menus' => $latestMenus,
                'latest_orders' => $latestOrders,
                'menu_count' => $db->query('SELECT COUNT(*) as count FROM menus')->fetch()['count'],
                'order_count' => $db->query('SELECT COUNT(*) as count FROM orders')->fetch()['count']
            ];
        } catch (\Exception $e) {
            error_log('Error getting dashboard stats: ' . $e->getMessage());
            return [
                'users' => 0,
                'last_login' => date('Y-m-d H:i:s'),
                'latest_menus' => [],
                'latest_orders' => [],
                'menu_count' => 0,
                'order_count' => 0
            ];
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
