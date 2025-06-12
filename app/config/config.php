<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Application settings
define('APP_DEBUG', true);
define('APP_ENV', 'development'); // 'production' or 'development'

// Database configuration
define('DB_PATH', ROOT_PATH . '/database/siloe.db');
define('DB_DRIVER', 'sqlite');

// Session configuration handled in public/index.php
// Removed duplicate session_start to avoid regenerating session and CSRF token
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Lax');
}

// Timezone
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Database connection function
function getDbConnection() {
    static $pdo;
    
    if ($pdo === null) {
        try {
            $dsn = 'sqlite:' . DB_PATH;
            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            if (APP_DEBUG) {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                die('A database error occurred. Please try again later.');
            }
        }
    }
    
    return $pdo;
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('CSRF_TOKEN', $_SESSION['csrf_token']);

// Helper function to check CSRF token
function csrf_token() {
    return CSRF_TOKEN;
}

// Helper function to verify CSRF token
function verify_csrf_token($token) {
    return hash_equals(CSRF_TOKEN, $token);
}

// Helper function to generate asset URL
function asset($path) {
    return APP_URL . '/public/' . ltrim($path, '/');
}

// Helper function to generate URL
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
