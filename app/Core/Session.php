<?php

namespace App\Core;

/**
 * Session class to handle session management
 */
class Session {
    /**
     * @var Session Singleton instance
     */
    private static $instance = null;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Get the singleton instance
     *
     * @return Session
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Set a session value
     *
     * @param string $key Session key
     * @param mixed $value Session value
     * @return void
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get a session value
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Check if a session key exists
     *
     * @param string $key Session key
     * @return bool
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove a session value
     *
     * @param string $key Session key
     * @return void
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Clear all session data
     *
     * @return void
     */
    public static function clear() {
        session_unset();
    }
    
    /**
     * Destroy the session
     *
     * @return void
     */
    public static function destroy() {
        session_destroy();
    }
    
    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return bool
     */
    public static function regenerate($deleteOldSession = true) {
        return session_regenerate_id($deleteOldSession);
    }
    
    /**
     * Set a flash message that will be available only for the next request
     *
     * @param string $key Flash key
     * @param mixed $value Flash value
     * @return void
     */
    public static function setFlash($key, $value) {
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Get a flash message and remove it
     *
     * @param string $key Flash key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function getFlash($key, $default = null) {
        $value = $default;
        
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
        }
        
        return $value;
    }
    
    /**
     * Check if a flash message exists
     *
     * @param string $key Flash key
     * @return bool
     */
    public static function hasFlash($key) {
        return isset($_SESSION['_flash'][$key]);
    }
    
    /**
     * Generate a CSRF token
     *
     * @return string
     */
    public static function generateCsrfToken() {
        // Check if we already have a valid token
        $token = self::get('csrf_token');
        
        // If no token exists or it's expired, generate a new one
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            self::set('csrf_token', $token);
            error_log('Generated new CSRF token: ' . $token);
        } else {
            error_log('Using existing CSRF token: ' . $token);
        }
        
        return $token;
    }
    
    /**
     * Get the current CSRF token
     *
     * @return string|null The CSRF token or null if not set
     */
    public static function getCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            self::generateCsrfToken();
        }
        return $_SESSION['csrf_token'] ?? null;
    }
    
    /**
     * Verify a CSRF token
     *
     * @param string $token Token to verify
     * @return bool
     */
    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            error_log('CSRF token not found in session');
            return false;
        }
        
        $isValid = hash_equals($_SESSION['csrf_token'], $token);
        
        // After verification, always regenerate the token for the next request
        self::generateCsrfToken();
        
        if ($isValid) {
            error_log('CSRF token verified successfully');
        } else {
            error_log(sprintf('CSRF token verification failed. Expected: %s, Got: %s', 
                $_SESSION['csrf_token'], 
                $token
            ));
        }
        
        return $isValid;
    }
}
