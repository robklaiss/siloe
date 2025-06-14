<?php

namespace App\Core;

/**
 * Request class to handle HTTP requests
 */
class Request {
    /**
     * Get a value from the request (GET, POST, or JSON body)
     *
     * @param string $key The key to get
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get($key, $default = null) {
        // Check GET parameters
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        
        // Check POST parameters
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        
        // Check JSON body if content type is application/json
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($contentType, 'application/json') !== false) {
            $json = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json[$key])) {
                return $json[$key];
            }
        }
        
        return $default;
    }
    
    /**
     * Get all request data
     *
     * @return array
     */
    public function all() {
        $data = array_merge($_GET, $_POST);
        
        // Add JSON data if content type is application/json
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($contentType, 'application/json') !== false) {
            $json = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = array_merge($data, $json);
            }
        }
        
        return $data;
    }
    
    /**
     * Get the request method
     *
     * @return string
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Check if the request is an AJAX request
     *
     * @return bool
     */
    public function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get the request URI
     *
     * @return string
     */
    public function uri() {
        return $_SERVER['REQUEST_URI'];
    }
    
    /**
     * Get a header value
     *
     * @param string $key The header key
     * @param mixed $default Default value if header doesn't exist
     * @return mixed
     */
    public function header($key, $default = null) {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : $default;
    }
    
    /**
     * Get the client IP address
     *
     * @return string
     */
    public function ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
