<?php
/**
 * Test script to verify the refactored admin views are working properly
 * Tests /menus and /orders routes with the new unified layout
 */

// Set up environment
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/app/Core/init.php';

echo "Starting test for refactored admin views...\n";

// Test URLs
$urls = [
    '/menus',
    '/orders'
];

// Simple HTTP client to test routes
function testUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000' . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $_COOKIE['PHPSESSID'] ?? '');
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => $output,
    ];
}

// Create a session with admin access if needed
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin User';
    $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
}

// Test each URL
foreach ($urls as $url) {
    echo "\nTesting $url... ";
    $response = testUrl($url);
    
    if ($response['code'] === 200) {
        echo "SUCCESS (HTTP 200)\n";
        
        // Check for expected content to verify proper layout
        if (strpos($response['body'], 'layout') !== false) {
            echo "  ✓ Page appears to use layout\n";
        } else {
            echo "  ✗ Layout might be missing\n";
        }
        
        if (strpos($response['body'], 'sidebar') !== false) {
            echo "  ✓ Sidebar appears to be present\n";
        } else {
            echo "  ✗ Sidebar might be missing\n";
        }
        
        // Check for old structure (should not be present)
        if (strpos($response['body'], '<html') !== false && strpos($response['body'], '</html>') !== false) {
            echo "  ✗ WARNING: Found full HTML structure which should have been removed\n";
        } else {
            echo "  ✓ No duplicate HTML structure detected\n";
        }
    } else {
        echo "FAILED (HTTP " . $response['code'] . ")\n";
    }
}

echo "\nTests completed!\n";
