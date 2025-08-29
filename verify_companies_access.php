<?php
/**
 * Verify Companies Access
 * 
 * This script verifies that the companies page is accessible after fixing the database schema
 * and directory structure issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages with timestamp
function log_message($message, $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $color = 'black';
    if ($type == 'success') $color = 'green';
    if ($type == 'error') $color = 'red';
    if ($type == 'warning') $color = 'orange';
    
    echo "<p style='color:$color'>[$timestamp] $message</p>";
}

// Function to make HTTP requests
function make_request($url, $method = 'GET', $data = [], $cookies = []) {
    log_message("Making $method request to $url");
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    // Set cookies if provided
    if (!empty($cookies)) {
        $cookie_string = '';
        foreach ($cookies as $name => $value) {
            $cookie_string .= "$name=$value; ";
        }
        curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
    }
    
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    // Extract cookies from response
    $cookies_out = [];
    preg_match_all('/Set-Cookie: (.*?);/m', $header, $matches);
    foreach ($matches[1] as $cookie) {
        $parts = explode('=', $cookie, 2);
        if (count($parts) == 2) {
            $cookies_out[$parts[0]] = $parts[1];
        }
    }
    
    return [
        'status_code' => $status_code,
        'header' => $header,
        'body' => $body,
        'cookies' => $cookies_out
    ];
}

// Function to extract CSRF token from HTML
function extract_csrf_token($html) {
    if (preg_match('/<input[^>]*name=["\']csrf_token["\'][^>]*value=["\']([^"\']+)["\'][^>]*>/', $html, $matches)) {
        return $matches[1];
    }
    return null;
}

// Start the test
log_message("Starting verification of companies page access", 'success');

// Define base URL
$base_url = 'https://www.siloe.com.py';

// Step 1: Access the login page
log_message("Step 1: Accessing login page");
$login_response = make_request("$base_url/login");

if ($login_response['status_code'] == 200) {
    log_message("Successfully accessed login page", 'success');
    
    // Extract CSRF token
    $csrf_token = extract_csrf_token($login_response['body']);
    if ($csrf_token) {
        log_message("Found CSRF token: $csrf_token", 'success');
    } else {
        log_message("Could not find CSRF token", 'error');
    }
    
    // Step 2: Submit login form
    log_message("Step 2: Submitting login form");
    $login_data = [
        'email' => 'admin@siloe.com',
        'password' => 'Admin123!',
        'csrf_token' => $csrf_token
    ];
    
    $auth_response = make_request("$base_url/login", 'POST', $login_data, $login_response['cookies']);
    
    if ($auth_response['status_code'] == 200 || $auth_response['status_code'] == 302) {
        log_message("Login form submitted with status code: {$auth_response['status_code']}", 'success');
        
        // Step 3: Access the companies page
        log_message("Step 3: Accessing companies page");
        $companies_response = make_request("$base_url/admin/companies", 'GET', [], $auth_response['cookies']);
        
        if ($companies_response['status_code'] == 200) {
            log_message("Successfully accessed companies page with status code: {$companies_response['status_code']}", 'success');
            
            // Check if the page contains company data
            if (strpos($companies_response['body'], 'Siloe Demo Company') !== false) {
                log_message("Found 'Siloe Demo Company' on the page", 'success');
            } else {
                log_message("Could not find company data on the page", 'warning');
            }
            
            // Display a snippet of the response
            $snippet = substr($companies_response['body'], 0, 500) . '...';
            log_message("Response snippet: " . htmlspecialchars($snippet));
            
        } else {
            log_message("Failed to access companies page with status code: {$companies_response['status_code']}", 'error');
            log_message("Response: " . htmlspecialchars(substr($companies_response['body'], 0, 500)));
        }
    } else {
        log_message("Login failed with status code: {$auth_response['status_code']}", 'error');
        log_message("Response: " . htmlspecialchars(substr($auth_response['body'], 0, 500)));
    }
} else {
    log_message("Failed to access login page with status code: {$login_response['status_code']}", 'error');
}

log_message("Verification completed", 'success');

// Display a summary of the test results
echo "<h2>Test Summary</h2>";
echo "<p>The companies page should now be working properly with the following fixes:</p>";
echo "<ol>";
echo "<li>Fixed nested public directory structure issue</li>";
echo "<li>Updated .htaccess file to remove nested redirection</li>";
echo "<li>Fixed companies table schema to include all required columns</li>";
echo "<li>Created a sample company record for testing</li>";
echo "</ol>";

echo "<h2>Next Steps</h2>";
echo "<p>To verify that the companies page is working properly:</p>";
echo "<ol>";
echo "<li>Log in as admin (admin@siloe.com / Admin123!)</li>";
echo "<li>Navigate to <a href='$base_url/admin/companies' target='_blank'>/admin/companies</a></li>";
echo "<li>Verify that the companies are displayed correctly</li>";
echo "<li>Try creating a new company</li>";
echo "<li>Try editing an existing company</li>";
echo "</ol>";
