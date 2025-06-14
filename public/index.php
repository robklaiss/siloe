<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Define application constants and paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', __DIR__);

define('APP_NAME', 'Siloe Lunch System');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);

// Log request details for debugging
function logRequest() {
    $log = sprintf(
        "[%s] %s %s %s\nHeaders: %s\nPOST: %s\nGET: %s\nSession: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        $_SERVER['SERVER_PROTOCOL'],
        json_encode(getallheaders()),
        json_encode($_POST),
        json_encode($_GET),
        isset($_SESSION) ? json_encode($_SESSION) : 'Session not started'
    );
    file_put_contents(ROOT_PATH . '/storage/logs/requests.log', $log, FILE_APPEND);
}

// Create logs directory if it doesn't exist
if (!is_dir(ROOT_PATH . '/storage/logs')) {
    mkdir(ROOT_PATH . '/storage/logs', 0755, true);
}

// Register autoloader
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $base_dir = APP_PATH . '/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Start session with secure settings
$sessionParams = [
    'cookie_lifetime' => 0, // 0 = Until browser is closed
    'cookie_path' => '/',
    'cookie_domain' => '',
    'cookie_secure' => false, // TODO: Set to true in production with HTTPS
    'cookie_httponly' => true, // Prevent client-side script access
    'cookie_samesite' => 'Lax', // Mitigates CSRF
    'use_strict_mode' => 1, // Prevent session fixation attacks
    'use_only_cookies' => 1,
    'gc_maxlifetime' => 1440, // 24 minutes
    'gc_probability' => 1,
    'gc_divisor' => 100
];

if (session_status() === PHP_SESSION_NONE) {
    session_start($sessionParams);
}

// Log the request
logRequest();

// Load configuration
require_once APP_PATH . '/config/config.php';

// Load helper functions
require_once APP_PATH . '/helpers/functions.php';

// Create router instance
$router = new App\Core\Router();

// Load routes
require_once APP_PATH . '/routes/web.php';

// Dispatch the request
$router->dispatch();
