<?php
/**
 * Test script to verify sidebar consistency across refactored view files
 */

// Define the base directory for views
$viewsDir = __DIR__ . '/app/views';

// Define the pages to test for sidebar issues
$views = [
    'menus/create.php',
    'menus/edit.php',
    'menus/index_new.php',
];

echo "=== Sidebar Consistency Test - File Analysis ===\n\n";

foreach ($views as $view) {
    $filepath = $viewsDir . '/' . $view;
    echo "Testing $view...\n";
    
    if (!file_exists($filepath)) {
        echo "  ERROR: File does not exist\n";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // Check for full HTML structure (should not have these in refactored views)
    $hasDoctype = strpos($content, '<!DOCTYPE') !== false;
    $hasHtmlTag = strpos($content, '<html') !== false;
    $hasHeadTag = strpos($content, '<head>') !== false;
    $hasBodyTag = strpos($content, '<body') !== false;
    
    echo "  Contains DOCTYPE: " . ($hasDoctype ? 'YES (ISSUE)' : 'NO (GOOD)') . "\n";
    echo "  Contains <html> tag: " . ($hasHtmlTag ? 'YES (ISSUE)' : 'NO (GOOD)') . "\n";
    echo "  Contains <head> tag: " . ($hasHeadTag ? 'YES (ISSUE)' : 'NO (GOOD)') . "\n";
    echo "  Contains <body> tag: " . ($hasBodyTag ? 'YES (ISSUE)' : 'NO (GOOD)') . "\n";
    
    // Check for hardcoded sidebar
    $hasSidebar = strpos($content, 'class="w-64 bg-gray-800') !== false;
    echo "  Contains hardcoded sidebar: " . ($hasSidebar ? 'YES (ISSUE)' : 'NO (GOOD)') . "\n";
    
    // Check for debug comments
    if (strpos($content, '<!-- DEBUG:') !== false) {
        preg_match_all('/<!-- DEBUG: ([^>]+) -->/', $content, $matches);
        if (!empty($matches[1])) {
            echo "  Debug info: " . implode(', ', $matches[1]) . "\n";
        }
    }
    
    echo "\n";
}

// Check if controllers pass the correct parameters
echo "=== Controller Parameter Check ===\n\n";

$controllerFiles = [
    'MenuController.php' => '/Users/robinklaiss/Dev/siloe/app/Controllers/MenuController.php',
    'OrderController.php' => '/Users/robinklaiss/Dev/siloe/app/Controllers/OrderController.php'
];

foreach ($controllerFiles as $name => $filepath) {
    echo "Checking $name...\n";
    
    if (!file_exists($filepath)) {
        echo "  ERROR: File does not exist\n";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // Check for required parameters in view method calls
    $hasHideNavbar = preg_match("/'hideNavbar'\s*=>\s*true/", $content);
    $hasWrapContainer = preg_match("/'wrapContainer'\s*=>\s*false/", $content);
    $hasActive = preg_match("/'active'\s*=>\s*'[^']+'/", $content);
    
    echo "  Sets 'hideNavbar' parameter: " . ($hasHideNavbar ? 'YES (GOOD)' : 'NO (ISSUE)') . "\n";
    echo "  Sets 'wrapContainer' parameter: " . ($hasWrapContainer ? 'YES (GOOD)' : 'NO (ISSUE)') . "\n";
    echo "  Sets 'active' parameter: " . ($hasActive ? 'YES (GOOD)' : 'NO (ISSUE)') . "\n";
    
    // Extract the active menu items set by each controller
    preg_match_all("/'active'\s*=>\s*'([^']+)'/", $content, $matches);
    if (!empty($matches[1])) {
        echo "  Active menu items set: " . implode(', ', array_unique($matches[1])) . "\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";
echo "Manual verification recommended for full functionality testing.\n";
