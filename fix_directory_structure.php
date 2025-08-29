<?php
/**
 * Fix Directory Structure Script
 * 
 * This script fixes the nested public directory structure issue
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages
function log_message($message, $type = 'info') {
    $color = 'black';
    if ($type == 'success') $color = 'green';
    if ($type == 'error') $color = 'red';
    if ($type == 'warning') $color = 'orange';
    
    echo "<p style='color:$color'>$message</p>";
}

log_message("Starting directory structure fix...");

// Check if we're on the server
$server_path = '/home1/siloecom/siloe';
$is_server = file_exists($server_path);

if (!$is_server) {
    log_message("This script must be run on the server", 'error');
    exit;
}

// Check current structure
$public_path = $server_path . '/public';
$nested_public_path = $public_path . '/public';

if (!file_exists($nested_public_path)) {
    log_message("Nested public directory not found. Structure seems correct.", 'success');
    exit;
}

log_message("Found nested public directory structure. Fixing...", 'warning');

// Create a backup of the current structure
$backup_dir = $server_path . '/backup_' . date('Ymd_His');
log_message("Creating backup at: $backup_dir");

if (!mkdir($backup_dir, 0755, true)) {
    log_message("Failed to create backup directory", 'error');
    exit;
}

// Copy important files from nested public to backup
log_message("Backing up nested public directory...");
exec("cp -r $nested_public_path $backup_dir/");

// Move files from nested public to parent public
log_message("Moving files from nested public to parent public...");

// First check if index.php exists in both locations
if (file_exists($nested_public_path . '/index.php') && file_exists($public_path . '/index.php')) {
    // Compare the files
    $nested_index = file_get_contents($nested_public_path . '/index.php');
    $public_index = file_get_contents($public_path . '/index.php');
    
    if ($nested_index !== $public_index) {
        log_message("Different index.php files found in both locations. Backing up both...", 'warning');
        copy($public_path . '/index.php', $backup_dir . '/parent_index.php');
        copy($nested_public_path . '/index.php', $backup_dir . '/nested_index.php');
    }
}

// Move all files from nested public to parent public
$files = scandir($nested_public_path);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $source = $nested_public_path . '/' . $file;
    $destination = $public_path . '/' . $file;
    
    // If file exists in destination, back it up first
    if (file_exists($destination)) {
        log_message("File already exists in parent public: $file. Backing up...", 'warning');
        copy($destination, $backup_dir . '/' . $file);
    }
    
    // Move the file
    if (is_dir($source)) {
        // For directories, use recursive copy
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }
        exec("cp -r $source/* $destination/");
    } else {
        // For files, use simple copy
        copy($source, $destination);
    }
    
    log_message("Moved: $file");
}

// Update .htaccess in parent directory
$htaccess_path = $public_path . '/.htaccess';
if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);
    
    // Backup original .htaccess
    copy($htaccess_path, $backup_dir . '/.htaccess');
    
    // Update RewriteRule to not redirect to public
    $updated_htaccess = str_replace(
        "RewriteRule ^(.*)$ public/$1 [L]",
        "# RewriteRule ^(.*)$ public/$1 [L] - Disabled by fix_directory_structure.php",
        $htaccess_content
    );
    
    file_put_contents($htaccess_path, $updated_htaccess);
    log_message("Updated .htaccess file", 'success');
}

// Create a symlink for backward compatibility
if (!file_exists($nested_public_path)) {
    symlink($public_path, $nested_public_path);
    log_message("Created symlink for backward compatibility", 'success');
}

log_message("Directory structure fix completed successfully!", 'success');
log_message("Backup created at: $backup_dir", 'success');
log_message("You should now be able to access the application without the nested public directory.", 'success');
