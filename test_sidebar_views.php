<?php
/**
 * Test script to verify sidebar consistency across refactored views
 * This script will check:
 * 1. Each view to ensure it uses the main layout properly
 * 2. No duplicate sidebars are rendered
 * 3. The correct sidebar item is highlighted as active
 */

// Define the base URL
$baseUrl = 'http://localhost:8000';

// Define the pages to test with their expected active menu item
$pages = [
    '/menus' => 'menus',
    '/menus/new' => 'menus',
    '/menus/create' => 'menus',
    '/menus/1/edit' => 'menus', // Replace 1 with a valid menu ID if needed
    '/orders' => 'orders',
    '/profile' => 'profile',
];

echo "=== Sidebar Consistency Test ===\n\n";

foreach ($pages as $path => $expectedActive) {
    $url = $baseUrl . $path;
    echo "Testing $url...\n";
    
    // Use cURL to fetch the page content
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo "  ERROR: HTTP code $httpCode\n";
        continue;
    }
    
    // Count sidebars (should only be one)
    $sidebarCount = substr_count($response, 'class="w-64 bg-gray-800 text-white p-4"');
    echo "  Sidebar count: $sidebarCount (should be 1)\n";
    
    // Check if the correct menu item is highlighted as active
    $activeHighlighted = false;
    if (strpos($response, "data-path=\"/$expectedActive\"") !== false && 
        strpos($response, 'class="block py-2 px-4 rounded bg-gray-700 text-white"') !== false) {
        $activeHighlighted = true;
    }
    echo "  Active menu item '$expectedActive' highlighted: " . ($activeHighlighted ? 'YES' : 'NO') . "\n";
    
    // Check for DEBUG comments (our debug markers in views)
    if (strpos($response, '<!-- DEBUG:') !== false) {
        preg_match_all('/<!-- DEBUG: ([^>]+) -->/', $response, $matches);
        if (!empty($matches[1])) {
            echo "  Debug info: " . implode(', ', $matches[1]) . "\n";
        }
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";
echo "Remember to manually verify the visual appearance of each page!\n";
