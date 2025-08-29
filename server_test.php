<?php
// Super simple PHP file to test server access
echo "<h1>Siloe Server Test</h1>";
echo "<p>This is a simple test file to verify PHP is working.</p>";
echo "<p>Server time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";

// Try to create a simple file to test write permissions
$test_file = 'test_write.txt';
$result = file_put_contents($test_file, 'Test write at ' . date('Y-m-d H:i:s'));
if ($result !== false) {
    echo "<p style='color:green'>Successfully wrote to test file</p>";
    echo "<p>Content: " . file_get_contents($test_file) . "</p>";
    unlink($test_file);
    echo "<p>Test file removed</p>";
} else {
    echo "<p style='color:red'>Failed to write to test file</p>";
}

// Display server variables
echo "<h2>Server Variables</h2>";
echo "<pre>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "</pre>";

// Display directory listing
echo "<h2>Directory Listing</h2>";
echo "<pre>";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo $file . "\n";
    }
}
echo "</pre>";
?>
