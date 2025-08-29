<?php
/**
 * Fix Session Headers Issue
 * 
 * This script fixes the issue with session headers being sent after output
 * by checking and modifying the config.php file
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

// Detect server environment
$is_server = file_exists('/home1/siloecom/siloe');
$root_path = $is_server ? '/home1/siloecom/siloe' : __DIR__;
$public_path = $root_path . ($is_server ? '/public' : '');

log_message("Environment: " . ($is_server ? "Server" : "Local"));
log_message("Root path: $root_path");
log_message("Public path: $public_path");

// Define the config file path
$config_file = $public_path . '/app/config/config.php';

if (!file_exists($config_file)) {
    log_message("Config file not found at: $config_file", 'error');
    exit;
}

// Read the current config file
$config_content = file_get_contents($config_file);
log_message("Original config file content:");
echo "<pre>" . htmlspecialchars($config_content) . "</pre>";

// Check for output before session settings
$pattern = '/^\s*<\?php\s+/';
if (!preg_match($pattern, $config_content)) {
    log_message("Config file doesn't start with proper PHP opening tag", 'warning');
    
    // Fix the PHP opening tag
    $config_content = "<?php\n" . preg_replace('/^\s*<\?php\s+/', '', $config_content);
    log_message("Added proper PHP opening tag", 'success');
}

// Check for whitespace or output before PHP tag
$pattern = '/^(\s|\n)*<\?php/';
if (!preg_match($pattern, $config_content)) {
    log_message("Found output before PHP opening tag", 'warning');
    
    // Remove any content before PHP tag
    $config_content = preg_replace('/^.*?<\?php/', '<?php', $config_content);
    log_message("Removed content before PHP opening tag", 'success');
}

// Check for session settings
$session_pattern = '/ini_set\([\'"]session\./';
if (preg_match($session_pattern, $config_content)) {
    log_message("Found session settings in config file", 'info');
    
    // Make sure session settings are at the beginning of the file
    $session_lines = [];
    preg_match_all('/ini_set\([\'"]session\.[^;]+;/', $config_content, $matches);
    
    if (!empty($matches[0])) {
        foreach ($matches[0] as $match) {
            $session_lines[] = $match;
            $config_content = str_replace($match, '', $config_content);
        }
        
        // Add session settings at the beginning after PHP tag
        $config_content = "<?php\n\n// Session settings\n" . implode("\n", $session_lines) . "\n\n" . preg_replace('/^\s*<\?php\s+/', '', $config_content);
        log_message("Moved session settings to the beginning of the file", 'success');
    }
}

// Create a backup of the original file
$backup_file = $config_file . '.bak';
if (copy($config_file, $backup_file)) {
    log_message("Created backup of config file at: $backup_file", 'success');
} else {
    log_message("Failed to create backup of config file", 'error');
}

// Write the updated config file
if (file_put_contents($config_file, $config_content) !== false) {
    log_message("Updated config file with fixed session settings", 'success');
} else {
    log_message("Failed to write updated config file", 'error');
}

// Check for other files with session_start() after output
$files_to_check = [
    $public_path . '/index.php',
    $public_path . '/app/bootstrap.php',
    $public_path . '/app/Core/App.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        log_message("Checking file: $file", 'info');
        $content = file_get_contents($file);
        
        // Check if session_start() is called after any output
        if (preg_match('/^\s*<\?php\s+/', $content) && strpos($content, 'session_start()') !== false) {
            log_message("Found session_start() in file", 'info');
            
            // Check if there's any output before session_start()
            $parts = explode('session_start()', $content, 2);
            if (!empty($parts[0]) && preg_match('/echo|print|<html>|<body>|<head>/', $parts[0])) {
                log_message("Found output before session_start() in $file", 'warning');
                
                // Create a backup
                $backup = $file . '.bak';
                if (copy($file, $backup)) {
                    log_message("Created backup of $file at: $backup", 'success');
                }
                
                // Move session_start() to the beginning
                $new_content = preg_replace('/^\s*<\?php\s+/', "<?php\nsession_start();\n\n", $content);
                $new_content = str_replace('session_start();', '', $new_content);
                
                if (file_put_contents($file, $new_content) !== false) {
                    log_message("Updated $file with session_start() at the beginning", 'success');
                } else {
                    log_message("Failed to update $file", 'error');
                }
            } else {
                log_message("No output before session_start() in $file", 'success');
            }
        }
    }
}

// Create a minimal index.php file to test the main site
$minimal_index = <<<'EOT'
<?php
/**
 * Minimal Index File
 * 
 * This is a minimal index.php file to test the main site
 * It includes the necessary bootstrap file and sets up the router
 */

// Start the session at the very beginning
session_start();

// Define the application root directory
define('APP_ROOT', dirname(__FILE__));

// Load the bootstrap file
require_once APP_ROOT . '/app/bootstrap.php';

// Initialize the application
$app = new App\Core\App();

// Run the application
$app->run();
EOT;

$minimal_index_file = $public_path . '/minimal_index.php';
if (file_put_contents($minimal_index_file, $minimal_index) !== false) {
    log_message("Created minimal index file at: $minimal_index_file", 'success');
} else {
    log_message("Failed to create minimal index file", 'error');
}

// Create a script to test the main site
$test_script = <<<'EOT'
<?php
/**
 * Test Main Site
 * 
 * This script tests the main site functionality
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

// Detect server environment
$is_server = file_exists('/home1/siloecom/siloe');
$root_path = $is_server ? '/home1/siloecom/siloe' : __DIR__;
$public_path = $root_path . ($is_server ? '/public' : '');

log_message("Environment: " . ($is_server ? "Server" : "Local"));
log_message("Root path: $root_path");
log_message("Public path: $public_path");

// Check if the minimal index file exists
$minimal_index_file = $public_path . '/minimal_index.php';
if (file_exists($minimal_index_file)) {
    log_message("Minimal index file found at: $minimal_index_file", 'success');
} else {
    log_message("Minimal index file not found", 'error');
}

// Check if the HomeController exists
$controller_file = $public_path . '/app/Controllers/HomeController.php';
if (file_exists($controller_file)) {
    log_message("HomeController found at: $controller_file", 'success');
} else {
    log_message("HomeController not found", 'error');
}

// Check if the home view exists
$view_file = $public_path . '/app/views/home/index.php';
if (file_exists($view_file)) {
    log_message("Home view found at: $view_file", 'success');
} else {
    log_message("Home view not found", 'error');
}

// Check if the web.php routes file exists
$routes_file = $public_path . '/app/routes/web.php';
if (file_exists($routes_file)) {
    log_message("Routes file found at: $routes_file", 'success');
    
    // Check if the home route exists
    $routes_content = file_get_contents($routes_file);
    if (strpos($routes_content, '$router->get(\'/\', \'HomeController\', \'index\');') !== false) {
        log_message("Home route found in routes file", 'success');
    } else {
        log_message("Home route not found in routes file", 'warning');
    }
} else {
    log_message("Routes file not found", 'error');
}

// Create a simple HTML page with links to test
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Siloe Main Site Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { 
            display: inline-block; 
            padding: 10px 15px; 
            background-color: #4CAF50; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px;
            margin: 5px;
        }
        .btn:hover { background-color: #45a049; }
        .container { margin-top: 20px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Siloe Main Site Test</h1>
    <p>Click the buttons below to test the site functionality:</p>
    
    <div>
        <a href='/' class='btn' target='_blank'>Home Page (Original)</a>
        <a href='/minimal_index.php' class='btn' target='_blank'>Home Page (Minimal)</a>
        <a href='/login' class='btn' target='_blank'>Login Page</a>
        <a href='/admin/companies' class='btn' target='_blank'>Companies Page</a>
    </div>
    
    <div class='container'>
        <h2>Manual Test Instructions</h2>
        <ol>
            <li>Click on the Home Page buttons to verify they load correctly</li>
            <li>If the minimal index works but the original doesn't, replace the original with the minimal version</li>
            <li>Click on the Login Page button to verify it loads correctly</li>
            <li>Log in as admin (admin@siloe.com / Admin123!)</li>
            <li>Click on the Companies Page button to verify it loads correctly</li>
        </ol>
    </div>
</body>
</html>
";
EOT;

$test_main_site_file = $public_path . '/test_main_site.php';
if (file_put_contents($test_main_site_file, $test_script) !== false) {
    log_message("Created test script at: $test_main_site_file", 'success');
} else {
    log_message("Failed to create test script", 'error');
}

log_message("Done", 'success');

// Create a simple script to fix session headers
$session_headers_fix = <<<'EOT'
<?php
/**
 * Session Headers Fix
 * 
 * This script fixes the session headers issue by:
 * 1. Setting proper session parameters
 * 2. Creating a session fix file to include in other scripts
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create session fix file
function create_session_fix() {
    $session_fix = <<<'EOT'
<?php
// Session fix for Siloe
// Include this file at the top of your index.php or other entry points

// Start session with extended lifetime
session_set_cookie_params(86400, '/', '', false, true);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize session arrays if needed
if (!isset($_SESSION['_flash'])) {
    $_SESSION['_flash'] = [];
}
if (!isset($_SESSION['_flash']['error'])) {
    $_SESSION['_flash']['error'] = [];
}
if (!isset($_SESSION['_flash']['success'])) {
    $_SESSION['_flash']['success'] = [];
}
EOT;

    // Write the session fix to a file
    $session_fix_file = __DIR__ . '/session_fix.php';
    if (file_put_contents($session_fix_file, $session_fix)) {
        echo "<p style='color:green'>✅ Created session fix at $session_fix_file</p>";
        return true;
    } else {
        echo "<p style='color:red'>❌ Failed to create session fix</p>";
        return false;
    }
}

// Function to update index.php to include session fix
function update_index_php() {
    $index_paths = [
        __DIR__ . '/public/index.php',
        __DIR__ . '/index.php'
    ];
    
    $success = false;
    
    foreach ($index_paths as $index_path) {
        if (file_exists($index_path)) {
            // Backup the original file
            $backup_path = $index_path . '.bak';
            if (!file_exists($backup_path)) {
                copy($index_path, $backup_path);
                echo "<p>Created backup at $backup_path</p>";
            }
            
            $index_content = file_get_contents($index_path);
            
            // Check if session_fix.php is already included
            if (strpos($index_content, 'session_fix.php') === false) {
                // Add session fix include at the top of the file
                $include_line = "<?php require_once __DIR__ . '/../session_fix.php'; ?>\n";
                
                // If it's in public directory, adjust the path
                if (strpos($index_path, '/public/') !== false) {
                    $include_line = "<?php require_once __DIR__ . '/../session_fix.php'; ?>\n";
                }
                
                $new_content = preg_replace('/^(<\?php|<\?)/i', $include_line, $index_content, 1);
                
                if ($new_content === $index_content) {
                    // If no replacement was made, prepend the include
                    $new_content = $include_line . $index_content;
                }
                
                if (file_put_contents($index_path, $new_content)) {
                    echo "<p style='color:green'>✅ Updated $index_path to include session fix</p>";
                    $success = true;
                } else {
                    echo "<p style='color:red'>❌ Failed to update $index_path</p>";
                }
            } else {
                echo "<p>Session fix already included in $index_path</p>";
                $success = true;
            }
        }
    }
    
    return $success;
}

// Main execution
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Session Headers Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: red;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px 0;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>Session Headers Fix</h1>";

// Create session fix
echo "<h2>Creating Session Fix</h2>";
$session_fix_created = create_session_fix();

// Update index.php
echo "<h2>Updating Index.php</h2>";
$index_updated = update_index_php();

// Summary
echo "<h2>Summary</h2>";
if ($session_fix_created && $index_updated) {
    echo "<div class='success'>
        <h3>✅ Fix Applied Successfully</h3>
        <p>The session headers fix has been applied successfully.</p>
    </div>";
} else {
    echo "<div class='error'>
        <h3>⚠️ Fix Applied Partially</h3>
        <p>The session headers fix was only partially applied. Please check the messages above for details.</p>
    </div>";
}

// Navigation
echo "<h2>Next Steps</h2>
<p>
    <a href='/admin_access.php' class='button'>Go to Admin Access</a>
    <a href='/' class='button'>Go to Home</a>
</p>";

echo "</body>
</html>";
EOT;

$session_headers_fix_file = $public_path . '/session_headers_fix.php';
if (file_put_contents($session_headers_fix_file, $session_headers_fix) !== false) {
    log_message("Created session headers fix script at: $session_headers_fix_file", 'success');
} else {
    log_message("Failed to create session headers fix script", 'error');
}
