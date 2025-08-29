<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log the error
    if ($data) {
        $logMessage = sprintf(
            "[%s] Resource Error - Type: %s, URL: %s, Page: %s, User Agent: %s\n",
            date('Y-m-d H:i:s'),
            $data['resourceType'] ?? 'unknown',
            $data['resourceUrl'] ?? 'unknown',
            $data['pageUrl'] ?? 'unknown',
            $data['userAgent'] ?? 'unknown'
        );
        
        file_put_contents(__DIR__ . '/../storage/logs/resource_errors.log', $logMessage, FILE_APPEND);
    }
    
    // Return a success response
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
}

// Return a 404 for non-POST requests
http_response_code(404);
echo 'Not Found';
