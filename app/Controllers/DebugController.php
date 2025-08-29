<?php

namespace App\Controllers;

use App\Core\Controller as BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use PDO;
use PDOException;

class DebugController extends BaseController {
    /**
     * DebugController constructor.
     */
    public function __construct(
        Router $router,
        ?Request $request = null,
        ?Response $response = null,
        ?Session $session = null
    ) {
        parent::__construct($router, $request, $response, $session);
    }
    
    /**
     * Get database connection
     * @return PDO
     */
    protected function getDbConnection() {
        // Use the application-wide connection which enables PRAGMA foreign_keys
        return \getDbConnection();
    }
    
    /**
     * Debug orders page
     */
    public function debugOrders() {
        try {
            header('Content-Type: text/plain');
            
            // Check session
            echo "=== SESSION ===\n";
            echo "Session ID: " . session_id() . "\n";
            echo "Session Data: " . print_r($_SESSION, true) . "\n\n";
            
            // Check database connection
            $db = $this->getDbConnection();
            if (!$db) {
                throw new \Exception("Failed to connect to database");
            }
            echo "=== DATABASE CONNECTION ===\n";
            echo "Connected to database successfully\n\n";
            
            // Check if orders table exists
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='orders'")->fetchAll();
            if (empty($tables)) {
                echo "ERROR: 'orders' table does not exist\n";
            } else {
                echo "'orders' table exists\n";
                
                // Try to fetch some orders
                $query = 'SELECT o.*, u.name as user_name, u.email as user_email
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.id 
                         ORDER BY o.created_at DESC 
                         LIMIT 3';
                $stmt = $db->query($query);
                $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                echo "=== ORDERS (first 3) ===\n";
                if (empty($orders)) {
                    echo "No orders found in the database\n";
                } else {
                    foreach ($orders as $order) {
                        echo "Order #{$order['id']} - User: {$order['user_name']} - Status: {$order['status']}\n";
                    }
                }
            }
            
        } catch (\Exception $e) {
            echo "\nERROR: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
        exit;
    }
    
    /**
     * Display all registered routes
     */
    public function routes() {
        // Only allow in development environment
        if (APP_ENV !== 'development') {
            if (isset($this->response)) {
                return $this->response->redirect('/');
            }
            header('Location: /');
            exit;
        }

        try {
            if (!$this->router) {
                throw new \RuntimeException('Router not initialized');
            }
            
            $routes = $this->router->getRoutes();
            
            header('Content-Type: text/plain');
            echo "Registered Routes (" . count($routes) . "):\n";
            echo str_repeat("=", 100) . "\n";
            
            if (empty($routes)) {
                echo "No routes registered.\n";
            } else {
                foreach ($routes as $route) {
                    printf("%-8s %-40s => %s::%s\n", 
                        $route['method'], 
                        $route['route'], 
                        $route['controller'], 
                        $route['action']
                    );
                }
            }
            
            // Dump router info for debugging
            echo "\nRouter Info:\n" . str_repeat("-", 100) . "\n";
            echo "Class: " . get_class($this->router) . "\n";
            
            exit(0);
            
        } catch (\Exception $e) {
            header('Content-Type: text/plain');
            http_response_code(500);
            echo "Error generating route list:\n";
            echo $e->getMessage() . "\n\n";
            echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n\n";
            echo "Stack Trace:\n" . $e->getTraceAsString();
            exit(1);
        }
    }
}
