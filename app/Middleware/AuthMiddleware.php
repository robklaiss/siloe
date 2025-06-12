<?php

namespace App\Middleware;

class AuthMiddleware extends Middleware {
    /**
     * Check if user is authenticated before accessing the route
     */
    public function beforeAction() {
        if (!isset($_SESSION['user_id'])) {
            // Store the intended URL for redirecting after login
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            }
            
            // Set flash message
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Por favor inicia sesión para acceder a esta página.'
            ];
            
            // Redirect to login
            header('Location: ' . url('login'));
            exit;
        }
    }
}
