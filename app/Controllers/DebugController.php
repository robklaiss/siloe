<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Router;

class DebugController extends Controller {
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * DebugController constructor.
     * @param Router $router
     */
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->router = $router;
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
