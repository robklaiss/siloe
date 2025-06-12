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
            
            // Get other stats as needed
            // For example: orders, menus, etc.
            
            return [
                'users' => $userCount,
                'last_login' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            error_log('Error getting dashboard stats: ' . $e->getMessage());
            return [
                'users' => 0,
                'last_login' => date('Y-m-d H:i:s'),
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
