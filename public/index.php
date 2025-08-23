<?php
// Error reporting: verbose logging, no display in production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// When using PHP built-in server with index.php as the router, let it serve
// existing static files directly (e.g., assets under /uploads/*) to avoid 404s.
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false; // Delegate to the built-in webserver for static file
    }
}

// Define application constants and paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', __DIR__);

define('APP_NAME', 'Siloe Lunch System');
// Get the base URL, handling both HTTP and HTTPS
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Remove 'public' from the path if it exists
$basePath = str_replace('/public', '', $scriptName);

// Define the base URL
if ($basePath === '/') {
    define('APP_URL', $protocol . $host);
} else {
    define('APP_URL', $protocol . $host . $basePath);
}

// Load the configuration file
require_once APP_PATH . '/config/config.php';

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
    } else {
        // Fallback for case-sensitive servers when local dev used lowercase directory names
        $altFile = str_replace('/Core/', '/core/', $file);
        if ($altFile !== $file && file_exists($altFile)) {
            require $altFile;
        }
    }
});

// Start session with secure settings
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');

$sessionParams = [
    'cookie_lifetime' => 0, // 0 = Until browser is closed
    'cookie_path' => '/',
    'cookie_domain' => '',
    'cookie_secure' => $isHttps, // Set based on current protocol
    'cookie_httponly' => true, // Prevent client-side script access
    'cookie_samesite' => 'Lax', // Mitigates CSRF
    'use_strict_mode' => 1, // Prevent session fixation attacks
    'use_only_cookies' => 1,
    'gc_maxlifetime' => 1440, // 24 minutes
    'gc_probability' => 1,
    'gc_divisor' => 100,
    'read_and_close' => false // Make sure session is not closed too early
];

// Ensure session is started only once and with proper parameters
if (session_status() === PHP_SESSION_NONE) {
    // Set session name to avoid conflicts
    session_name('siloe_session');
    
    // Start the session
    session_start($sessionParams);
    
    // Regenerate session ID to prevent session fixation
    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// Log the request
logRequest();

// Load helper functions
require_once APP_PATH . '/helpers/functions.php';

// Create router instance
// Fallback: if autoloader fails to resolve App\Core\Router due to opcode/realpath cache,
// explicitly require the file to keep the site running.
if (!class_exists('App\\Core\\Router')) {
    $fallbackRouter = APP_PATH . '/Core/Router.php';
    if (file_exists($fallbackRouter)) {
        require_once $fallbackRouter;
        error_log('Fallback loaded Core/Router.php');
    } else {
        error_log('Fallback missing: ' . $fallbackRouter);
    }
}
$router = new App\Core\Router();

// Load routes
require_once APP_PATH . '/routes/web.php';

// Dispatch the request
$router->dispatch();
