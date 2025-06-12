<?php

namespace App\Middleware;

class GuestMiddleware extends Middleware {
    /**
     * Redirect authenticated users away from guest-only pages
     */
    public function beforeAction() {
        if (isset($_SESSION['user_id'])) {
            // Get the intended URL or default to dashboard
            $redirectTo = $_SESSION['intended_url'] ?? 'dashboard';
            
            // Clear the intended URL to prevent redirect loops
            unset($_SESSION['intended_url']);
            
            // Redirect to the intended URL or dashboard
            header('Location: ' . url($redirectTo));
            exit;
        }
    }
}
