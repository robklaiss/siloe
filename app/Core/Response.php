<?php

namespace App\Core;

/**
 * Response class to handle HTTP responses
 */
class Response {
    /**
     * Set HTTP status code
     *
     * @param int $code HTTP status code
     * @return $this
     */
    public function status($code) {
        http_response_code($code);
        return $this;
    }
    
    /**
     * Set a response header
     *
     * @param string $key Header name
     * @param string $value Header value
     * @return $this
     */
    public function header($key, $value) {
        header("$key: $value");
        return $this;
    }
    
    /**
     * Send a JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function json($data, $statusCode = 200) {
        $this->status($statusCode);
        $this->header('Content-Type', 'application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send a plain text response
     *
     * @param string $text Text content
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function text($text, $statusCode = 200) {
        $this->status($statusCode);
        $this->header('Content-Type', 'text/plain');
        echo $text;
        exit;
    }
    
    /**
     * Send an HTML response
     *
     * @param string $html HTML content
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function html($html, $statusCode = 200) {
        $this->status($statusCode);
        $this->header('Content-Type', 'text/html');
        echo $html;
        exit;
    }
    
    /**
     * Redirect to a URL
     *
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (default: 302)
     * @return void
     */
    public function redirect($url, $statusCode = 302) {
        // Ensure session data is saved before redirect
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            // Relative URL, make it absolute
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                       (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                       (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ? 'https' : 'http';
            
            $host = $_SERVER['HTTP_HOST'];
            $baseUrl = rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME']), '/');
            $url = $protocol . '://' . $host . $baseUrl . '/' . ltrim($url, '/');
        }
        
        // Clear output buffer
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Set status code and location header
        $this->status($statusCode);
        $this->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->header('Pragma', 'no-cache');
        $this->header('Expires', '0');
        $this->header('Location', $url);
        
        // Ensure no further output is sent
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        
        exit;
    }
    
    /**
     * Send a file download response
     *
     * @param string $filePath Path to the file
     * @param string $fileName Name for the downloaded file
     * @param string $mimeType MIME type of the file
     * @return void
     */
    public function download($filePath, $fileName = null, $mimeType = null) {
        if (!file_exists($filePath)) {
            $this->status(404);
            echo 'File not found';
            exit;
        }
        
        $fileName = $fileName ?? basename($filePath);
        $mimeType = $mimeType ?? mime_content_type($filePath);
        
        $this->header('Content-Type', $mimeType);
        $this->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->header('Content-Length', filesize($filePath));
        
        readfile($filePath);
        exit;
    }
}
