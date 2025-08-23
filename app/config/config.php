<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Application settings
define('APP_DEBUG', false);
define('APP_ENV', 'production'); // 'production' or 'development'

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
    $base = rtrim(APP_URL, '/');
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

// Helper function to generate a proper logo URL from various stored formats
function logo_url($path) {
    if (!$path) {
        return '';
    }
    // If already an absolute URL, return as-is
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }

    // Normalize common stored formats
    $trimmed = trim($path); // remove leading/trailing whitespace
    // Handle paths starting with 'public/uploads/...'
    if (stripos($trimmed, 'public/uploads/') === 0) {
        $trimmed = substr($trimmed, strlen('public/')); // -> 'uploads/...'
    }
    // Also handle leading '/public/uploads/...'
    if (strpos($trimmed, '/public/uploads/') === 0) {
        $trimmed = substr($trimmed, strlen('/public/')); // -> 'uploads/...'
    }

    // If path starts with '/uploads/...', use asset directly
    if (strpos($trimmed, '/uploads/') === 0) {
        return asset($trimmed);
    }
    // If path starts with 'uploads/...', use asset directly (no extra prefixing)
    if (strpos($trimmed, 'uploads/') === 0) {
        return asset($trimmed);
    }

    // Handle legacy 'logos/...' or '/logos/...' paths by mapping to 'uploads/logos/...'
    if (strpos($trimmed, '/logos/') === 0) {
        $rest = substr($trimmed, strlen('/logos/'));
        return asset('uploads/logos/' . ltrim($rest, '/'));
    }
    if (strpos($trimmed, 'logos/') === 0) {
        $rest = substr($trimmed, strlen('logos/'));
        return asset('uploads/logos/' . ltrim($rest, '/'));
    }

    // Otherwise, assume it's a bare filename and prepend the standard logos directory
    return asset('uploads/logos/' . ltrim($trimmed, '/'));
}

// Helper function to generate URL
function url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

// Note: Autoloading is handled in `public/index.php` for web requests.
// CLI scripts that need autoloading should include the appropriate bootstrap.
