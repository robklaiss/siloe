<?php

namespace App\Middleware;

class AdminMiddleware extends Middleware {
    /**
     * Check if user has admin privileges
     */
    public function beforeAction() {
        // First, ensure user is authenticated
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . url('login'));
            exit;
        }
        
        // Then check if user is an admin
        if ($_SESSION['user_role'] !== 'admin') {
            // Log unauthorized access attempt
            error_log('Unauthorized access attempt by user ID: ' . $_SESSION['user_id']);
            
            // Set flash message
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'No tienes permiso para acceder a esta área.'
            ];
            
            // Redirect to dashboard or home
            header('Location: ' . url('dashboard'));
            exit;
        }
    }
}
