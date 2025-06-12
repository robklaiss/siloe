<?php

namespace App\Controllers;

use App\Core\View;

/**
 * Base Controller
 * 
 * Provides common functionality for all controllers
 */
class Controller {
    /**
     * The request data
     */
    protected $request = [];
    
    /**
     * The current user
     */
    protected $user = null;
    
    /**
     * Create a new controller instance
     */
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get request data
        $this->request = array_merge($_GET, $_POST);
        
        // Set current user if logged in
        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
        }
    }
    
    /**
     * Render a view
     */
    protected function view($view, $data = []) {
        return View::render($view, $data);
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit();
    }
    
    /**
     * Redirect back to the previous page
     */
    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Redirect to a named route
     */
    protected function route($name, $params = []) {
        $url = route($name, $params);
        $this->redirect($url);
    }
    
    /**
     * Return a JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Check if the current request is an AJAX request
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if the current user is authenticated
     */
    protected function isAuthenticated() {
        return $this->user !== null;
    }
    
    /**
     * Check if the current user is a guest
     */
    protected function isGuest() {
        return !$this->isAuthenticated();
    }
    
    /**
     * Check if the current user has a specific role
     */
    protected function hasRole($role) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return $this->user->role === $role;
    }
    
    /**
     * Require authentication to access the page
     */
    protected function requireAuth() {
        if ($this->isGuest()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
        }
    }
    
    /**
     * Require a specific role to access the page
     */
    protected function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            $this->redirect('/unauthorized');
        }
    }
    
    /**
     * Add a flash message
     */
    protected function flash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Get the current URL
     */
    protected function currentUrl() {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
               "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Get the previous URL
     */
    protected function previousUrl() {
        return $_SERVER['HTTP_REFERER'] ?? '/';
    }
    
    /**
     * Validate request data
     */
    protected function validate($rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->request[$field] ?? null;
            $rules = explode('|', $rule);
            
            foreach ($rules as $r) {
                $params = explode(':', $r);
                $ruleName = $params[0];
                $ruleParam = $params[1] ?? null;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "The {$field} field is required.";
                        }
                        break;
                        
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "The {$field} must be a valid email address.";
                        }
                        break;
                        
                    case 'min':
                        if (strlen($value) < $ruleParam) {
                            $errors[$field][] = "The {$field} must be at least {$ruleParam} characters.";
                        }
                        break;
                        
                    case 'max':
                        if (strlen($value) > $ruleParam) {
                            $errors[$field][] = "The {$field} may not be greater than {$ruleParam} characters.";
                        }
                        break;
                        
                    case 'confirmed':
                        $confirmationField = $field . '_confirmation';
                        if (!isset($this->request[$confirmationField]) || $value !== $this->request[$confirmationField]) {
                            $errors[$field][] = "The {$field} confirmation does not match.";
                        }
                        break;
                }
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $this->request;
            $this->back();
        }
        
        return true;
    }
    
    /**
     * Get validation errors
     */
    protected function getValidationErrors() {
        return $_SESSION['errors'] ?? [];
    }
    
    /**
     * Get old input value
     */
    protected function old($key, $default = null) {
        return $_SESSION['old'][$key] ?? $default;
    }
    
    /**
     * Check if there are any validation errors
     */
    protected function hasErrors() {
        return !empty($_SESSION['errors']);
    }
    
    /**
     * Check if a specific field has errors
     */
    protected function hasError($field) {
        return !empty($_SESSION['errors'][$field]);
    }
    
    /**
     * Get the first error message for a field
     */
    protected function getFirstError($field) {
        return $_SESSION['errors'][$field][0] ?? null;
    }
    
    /**
     * Clear validation errors
     */
    protected function clearValidationErrors() {
        unset($_SESSION['errors']);
        unset($_SESSION['old']);
    }
}
