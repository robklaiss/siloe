#!/bin/bash

# Fix permissions script for Siloe server
# This script will set proper permissions on all files and directories

echo "Fixing permissions on Siloe server..."

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SERVER_WEB_ROOT="/home1/siloecom/public_html"
SERVER_APP_ROOT="/home1/siloecom/siloe"

# Create a simple PHP script to check and fix permissions
cat > fix_permissions.php << 'EOL'
<?php
// Fix permissions script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing Permissions</h1>";

// Function to fix permissions recursively
function fix_permissions($dir, $file_mode = 0644, $dir_mode = 0755) {
    if (!is_dir($dir)) {
        echo "<p>Error: $dir is not a directory</p>";
        return false;
    }
    
    echo "<p>Fixing permissions for: $dir</p>";
    
    // Fix the directory itself
    if (!chmod($dir, $dir_mode)) {
        echo "<p>Failed to chmod $dir</p>";
    }
    
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $count = 0;
    foreach ($items as $item) {
        $count++;
        if ($count > 1000) {
            echo "<p>Processed 1000 items, stopping to avoid timeout...</p>";
            break;
        }
        
        $path = $item->getPathname();
        
        if (is_dir($path)) {
            if (!chmod($path, $dir_mode)) {
                echo "<p>Failed to chmod directory: $path</p>";
            }
        } else {
            if (!chmod($path, $file_mode)) {
                echo "<p>Failed to chmod file: $path</p>";
            }
        }
    }
    
    echo "<p>Fixed permissions for $count items</p>";
    return true;
}

// Create a .htaccess file with proper settings
function create_htaccess($path) {
    $htaccess_content = <<<'EOT'
# Enable rewrite engine
RewriteEngine On

# Set base directory
RewriteBase /

# Redirect to HTTPS (uncomment if needed)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Handle front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Set default index file
DirectoryIndex index.php

# PHP settings
php_flag display_errors on
php_value error_reporting E_ALL

# Allow access to all files
<FilesMatch ".*">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOT;

    if (file_put_contents($path, $htaccess_content)) {
        echo "<p>Created .htaccess file at $path</p>";
        chmod($path, 0644);
        return true;
    } else {
        echo "<p>Failed to create .htaccess file at $path</p>";
        return false;
    }
}

// Paths to fix
$paths = [
    '/home1/siloecom/public_html',
    '/home1/siloecom/siloe'
];

// Fix permissions for each path
foreach ($paths as $path) {
    if (is_dir($path)) {
        fix_permissions($path);
    } else {
        echo "<p>Directory not found: $path</p>";
    }
}

// Create .htaccess files
create_htaccess('/home1/siloecom/public_html/.htaccess');

// Create a simple test file
$test_content = <<<'EOT'
<?php
echo "<h1>Permission Test</h1>";
echo "<p>If you can see this, permissions are working correctly!</p>";
echo "<p>Server time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";
EOT;

if (file_put_contents('/home1/siloecom/public_html/permission_test.php', $test_content)) {
    echo "<p>Created test file at /home1/siloecom/public_html/permission_test.php</p>";
    chmod('/home1/siloecom/public_html/permission_test.php', 0644);
} else {
    echo "<p>Failed to create test file</p>";
}

echo "<h2>Permissions Fixed</h2>";
echo "<p>You can now test if the 403 errors are resolved by visiting:</p>";
echo "<ul>";
echo "<li><a href='/permission_test.php'>Permission Test</a></li>";
echo "<li><a href='/direct_login_fix.php'>Direct Login Fix</a></li>";
echo "</ul>";
EOL

# Upload the fix permissions script
echo "Uploading fix permissions script..."
scp -o PreferredAuthentications=password fix_permissions.php "$SERVER:$SERVER_WEB_ROOT/"

# Clean up local file
rm fix_permissions.php

echo "Done!"
echo "Access the fix permissions script at: http://www.siloe.com.py/fix_permissions.php"
