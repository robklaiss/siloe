<?php

namespace App\Core;

class Controller {
    /**
     * @var Router
     */
    protected $router;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * @var Response
     */
    protected $response;
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * Controller constructor.
     * @param Router $router
     * @param Request|null $request
     * @param Response|null $response
     * @param Session|null $session
     */
    public function __construct(
        Router $router,
        ?Request $request = null,
        ?Response $response = null,
        ?Session $session = null
    ) {
        $this->router = $router;
        $this->request = $request ?? new Request();
        $this->response = $response ?? new Response();
        $this->session = $session ?? Session::getInstance();
    }
    /**
     * Render a view file
     *
     * @param string $view The view file to render (without .php extension)
     * @param array $data Associative array of data to be available in the view
     * @return void
     */
    protected function view($view, $data = []) {
        // Log view rendering attempt
        error_log("Rendering view: $view");
        error_log("Data passed to view: " . print_r($data, true));
        
        // Define VIEWS_PATH if not already defined
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', dirname(__DIR__, 2) . '/app/views');
            error_log("VIEWS_PATH was not defined. Set to: " . VIEWS_PATH);
        }
        
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        error_log("Looking for view file at: $viewFile");
        
        if (!file_exists($viewFile)) {
            $error = "View file not found: $viewFile";
            error_log($error);
            throw new \Exception($error);
        }
        
        error_log("Including view file: $viewFile");
        require $viewFile;
        
        // Get the content and end buffering
        $content = ob_get_clean();
        if ($content === false) {
            $error = "Failed to get view content. Output buffering may be disabled.";
            error_log($error);
            throw new \Exception($error);
        }
        
        // Determine layout handling
        // Allow explicit override via $data['layout'] (string path like 'layouts/app', false or 'none' to disable)
        $explicitLayout = isset($data) && is_array($data) ? ($data['layout'] ?? null) : null;

        // If layout explicitly disabled
        if ($explicitLayout === false || $explicitLayout === 'none') {
            error_log("Layout disabled explicitly. Outputting content directly.");
            echo $content;
            return;
        }

        // If explicit layout string provided, compute its path
        if (is_string($explicitLayout) && $explicitLayout !== '') {
            $layoutPath = VIEWS_PATH . '/' . ltrim(str_replace('.', '/', $explicitLayout), '/') . '.php';
        } else {
            // Auto-detect: if content is a full HTML document, output directly
            if (stripos($content, '<!DOCTYPE') !== false || stripos($content, '<html') !== false) {
                error_log("Detected full HTML document in view output. Outputting content directly.");
                echo $content;
                return;
            }
            // Default layout
            $layoutPath = VIEWS_PATH . '/layouts/app.php';
        }

        // Render with layout if it exists; otherwise fallback to direct content
        if (file_exists($layoutPath)) {
            error_log("Rendering layout: " . $layoutPath);
            // Make $content and $viewFile available to layout
            require $layoutPath;
        } else {
            error_log("Layout file not found: " . $layoutPath . ". Outputting content directly.");
            echo $content;
        }
    }
    
    /**
     * Generate a CSRF token and add it to the view data
     * 
     * @param array $data View data array
     * @return array Updated view data with CSRF token
     */
    protected function withCsrfToken($data = []) {
        $data['csrf_token'] = $this->generateCsrfToken();
        return $data;
    }
    
    /**
     * Generate a CSRF token
     * 
     * @return string The generated CSRF token
     */
    protected function generateCsrfToken() {
        return $this->session->generateCsrfToken();
    }
    
    /**
     * Verify a CSRF token
     * 
     * @param string $token The token to verify
     * @return bool True if the token is valid, false otherwise
     */
    protected function verifyCsrfToken($token) {
        return $this->session->verifyCsrfToken($token);
    }
    
    /**
     * Send a JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to a different URL
     *
     * @param string $url The URL to redirect to
     * @param int $statusCode HTTP status code for redirect (default: 302)
     * @return void
     */
    protected function redirect($url, $statusCode = 302) {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = url($url);
        }
        
        header("Location: " . $url, true, $statusCode);
        exit;
    }
    
    /**
     * Check if the request is an AJAX request
     *
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get POST data with optional filtering
     *
     * @param string|null $key The key to get, or null to get all POST data
     * @param mixed $default Default value if key doesn't exist
     * @param int $filter The filter to apply (from filter_var)
     * @param mixed $options Options for the filter
     * @return mixed
     */
    protected function post($key = null, $default = null, $filter = FILTER_DEFAULT, $options = []) {
        if ($key === null) {
            return filter_input_array(INPUT_POST, $filter) ?: [];
        }
        
        return filter_input(INPUT_POST, $key, $filter, $options) ?: $default;
    }
    
    /**
     * Get GET data with optional filtering
     *
     * @param string|null $key The key to get, or null to get all GET data
     * @param mixed $default Default value if key doesn't exist
     * @param int $filter The filter to apply (from filter_var)
     * @param mixed $options Options for the filter
     * @return mixed
     */
    protected function get($key = null, $default = null, $filter = FILTER_DEFAULT, $options = []) {
        if ($key === null) {
            return filter_input_array(INPUT_GET, $filter) ?: [];
        }
        
        return filter_input(INPUT_GET, $key, $filter, $options) ?: $default;
    }
    
    /**
     * Check if the request method matches
     *
     * @param string $method The method to check (GET, POST, PUT, DELETE, etc.)
     * @return bool
     */
    protected function isMethod($method) {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }
    
    /**
     * Require a specific request method
     *
     * @param string|array $methods Allowed HTTP methods
     * @throws \Exception If the request method doesn't match
     * @return void
     */
    protected function requireMethod($methods) {
        $methods = is_array($methods) ? $methods : [$methods];
        $methods = array_map('strtoupper', $methods);
        
        if (!in_array($_SERVER['REQUEST_METHOD'], $methods)) {
            throw new \Exception('Method not allowed', 405);
        }
    }
    
    /**
     * Add a flash message to the session
     *
     * @param string $key The key to store the message under
     * @param string $message The message to store
     * @param string $type The type of message (e.g., 'success', 'error', 'info')
     * @return void
     */
    protected function flash($key, $message, $type = 'info') {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        
        $_SESSION['flash_messages'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }
    
    /**
     * Get and clear flash messages
     *
     * @param string|null $key The key of the message to get, or null to get all
     * @return array|false The flash message(s) or false if not found
     */
    protected function getFlash($key = null) {
        if (!isset($_SESSION['flash_messages'])) {
            return $key === null ? [] : false;
        }
        
        if ($key === null) {
            $messages = $_SESSION['flash_messages'];
            unset($_SESSION['flash_messages']);
            return $messages;
        }
        
        if (isset($_SESSION['flash_messages'][$key])) {
            $message = $_SESSION['flash_messages'][$key];
            unset($_SESSION['flash_messages'][$key]);
            return $message;
        }
        
        return false;
    }
    
    /**
     * Check if the user is logged in
     *
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Require the user to be logged in
     *
     * @param string $redirect URL to redirect to if not logged in
     * @return void
     */
    protected function requireLogin($redirect = '/login') {
        if (!$this->isLoggedIn()) {
            $this->flash('error', 'Por favor inicia sesión para continuar', 'error');
            $this->redirect($redirect);
        }
    }
    
    /**
     * Require the user to have a specific role
     *
     * @param string|array $roles Role or array of roles to check against
     * @param string $redirect URL to redirect to if role check fails
     * @return void
     */
    protected function requireRole($roles, $redirect = '/') {
        $this->requireLogin($redirect);
        
        $userRole = $_SESSION['user_role'] ?? null;
        $roles = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($userRole, $roles)) {
            $this->flash('error', 'No tienes permiso para acceder a esta página', 'error');
            $this->redirect($redirect);
        }
    }
}
