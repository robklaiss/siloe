<?php

namespace App\Controllers\Admin;

use App\Core\View;
use App\Models\DeleteRequest;

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        // Get some basic stats for the dashboard
        $stats = $this->getStats();
        
        // Define a default app name if APP_NAME constant is not defined
        $appName = defined('APP_NAME') ? APP_NAME : 'Siloe';
        
        // Render the admin dashboard view
        return $this->view('admin/dashboard', [
            'title' => 'Panel de Administración - ' . $appName,
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
            
            // Obtener el recuento de solicitudes de eliminación pendientes
            $deleteRequestModel = new DeleteRequest();
            $pendingDeleteRequests = $deleteRequestModel->countPendingRequests();

            return [
                'users' => $userCount,
                'last_login' => date('Y-m-d H:i:s'),
                'latest_menus' => $latestMenus,
                'latest_orders' => $latestOrders,
                'menu_count' => $db->query('SELECT COUNT(*) as count FROM menus')->fetch()['count'],
                'order_count' => $db->query('SELECT COUNT(*) as count FROM orders')->fetch()['count'],
                'pending_delete_requests' => $pendingDeleteRequests
            ];
        } catch (\Exception $e) {
            error_log('Error al obtener estadísticas del panel: ' . $e->getMessage());
            return [
                'users' => 0,
                'last_login' => date('Y-m-d H:i:s'),
                'latest_menus' => [],
                'latest_orders' => [],
                'menu_count' => 0,
                'order_count' => 0,
                'pending_delete_requests' => 0
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
