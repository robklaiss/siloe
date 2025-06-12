<?php

namespace App\Middleware;

class VerifyCsrfToken extends Middleware {
    /**
     * Skip CSRF verification for these HTTP methods
     */
    protected $except = [
        // Add routes that should be excluded from CSRF protection
    ];
    
    /**
     * Verify the CSRF token for the incoming request
     */
    public function beforeAction() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Skip CSRF verification for GET, HEAD, and OPTIONS requests
        if (in_array($requestMethod, ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }
        
        // Skip CSRF verification for excluded routes
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($this->except as $excludedPath) {
            if ($path === $excludedPath || 
                (str_ends_with($excludedPath, '*') && 
                 str_starts_with($path, rtrim($excludedPath, '*')))) {
                return;
            }
        }
        
        // Get the token from the request
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        // Verify the token
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            // Log CSRF token verification failure
            error_log('CSRF token verification failed');
            
            // For AJAX requests, return a JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(419); // CSRF token mismatch
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Token CSRF no válido o expirado']);
                exit;
            }
            
            // For regular form submissions, redirect back with error
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'La sesión ha expirado. Por favor, inténtalo de nuevo.'
            ];
            
            // Store form data in session for repopulating the form
            if ($_POST) {
                $_SESSION['old_input'] = $_POST;
            }
            
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? url('/'));
            exit;
        }
    }
}
