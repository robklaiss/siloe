<?php
// Display server information
echo "<h1>Server Information</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Server name: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";

// List all directories in the parent directory
echo "<h2>Directory Structure</h2>";
echo "<pre>";
$parent_dir = dirname(__DIR__);
echo "Parent directory: $parent_dir\n";
echo "Contents:\n";
if ($handle = opendir($parent_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            echo "$entry\n";
        }
    }
    closedir($handle);
}
echo "</pre>";

// Show phpinfo for detailed configuration
echo "<h2>PHP Configuration</h2>";
phpinfo();
?>
