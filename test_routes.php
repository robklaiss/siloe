<?php
/**
 * Simple route testing script for Siloe
 */

$routes = [
    '/admin/users',
    '/menus',
    '/orders',
    '/profile'
];

$baseUrl = 'http://localhost:8000';

echo "Testing routes on $baseUrl\n";
echo "------------------------\n";

foreach ($routes as $route) {
    $url = $baseUrl . $route;
    echo "Testing route: $route\n";
    
    // Initialize curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    // Get content type
    $contentType = "";
    if (preg_match('/Content-Type: (.*?)(\r\n|\n)/i', $header, $matches)) {
        $contentType = trim($matches[1]);
    }
    
    // Close curl
    curl_close($ch);
    
    // Output results
    echo "Status: $httpCode\n";
    echo "Content-Type: $contentType\n";
    
    // Check for specific errors
    if ($httpCode == 200) {
        echo "Status: OK\n";
        // Check if it's HTML and has a title
        if (strpos($contentType, 'text/html') !== false) {
            if (preg_match('/<title>(.*?)<\/title>/i', $body, $matches)) {
                echo "Page title: " . trim($matches[1]) . "\n";
            }
        }
    } elseif ($httpCode == 302 || $httpCode == 301) {
        echo "Status: Redirect\n";
        if (preg_match('/Location: (.*?)(\r\n|\n)/i', $header, $matches)) {
            echo "Redirected to: " . trim($matches[1]) . "\n";
        }
    } else {
        echo "Status: ERROR\n";
        echo "First 100 chars of response: " . substr($body, 0, 100) . "...\n";
    }
    
    echo "------------------------\n";
}

echo "Route testing complete!\n";
