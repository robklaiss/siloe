<?php
// Display server and request information
echo "<h1>Server Information</h1>";
echo "<pre>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "PHP Version: " . phpversion() . "\n";

// List files in the public directory
echo "\nPublic directory contents:\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    echo "- $file\n";}

// List files in the css directory
if (is_dir(__DIR__ . '/css')) {
    echo "\nCSS directory contents:\n";
    $cssFiles = scandir(__DIR__ . '/css');
    foreach ($cssFiles as $file) {
        echo "- $file\n";
    }
}

// List files in the js directory
if (is_dir(__DIR__ . '/js')) {
    echo "\nJS directory contents:\n";
    $jsFiles = scandir(__DIR__ . '/js');
    foreach ($jsFiles as $file) {
        echo "- $file\n";
    }
}

echo "</pre>";
?>
