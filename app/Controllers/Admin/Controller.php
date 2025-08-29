<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller as BaseController;
use App\Core\View;

/**
 * Base Admin Controller
 * 
 * Provides common functionality for all admin controllers
 */
class Controller extends BaseController {
    /**
     * Create a new controller instance
     */
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Log session data for debugging
        error_log('Admin Controller - Session data: ' . print_r($_SESSION, true));
        
        // Check if user is logged in and is an admin
        if (!isset($_SESSION['user_id'])) {
            error_log('Admin access denied: No user_id in session');
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            exit;
        }
        
        if ($_SESSION['user_role'] !== 'admin') {
            error_log('Admin access denied: Role is ' . ($_SESSION['user_role'] ?? 'not set'));
            header('Location: /dashboard');
            exit;
        }
        
        error_log('Admin access granted for user: ' . $_SESSION['user_id']);
    }
    
    /**
     * Render a view with the app layout
     */
    protected function view($view, $data = []) {
        // Set default title if not provided
        if (!isset($data['title'])) {
            $data['title'] = APP_NAME;
        }
        
        // Render the view with the app layout
        return View::make($view, $data)->layout('layouts/app');
    }
}
