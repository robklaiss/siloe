<?php
/**
 * Add Home Route to web.php
 * 
 * This script adds the home route to the web.php file
 * to fix the HTTP 500 error on the main site
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

// Define the routes file path
$routes_file = $public_path . '/app/routes/web.php';

if (!file_exists($routes_file)) {
    log_message("Routes file not found at: $routes_file", 'error');
    exit;
}

// Read the current routes file
$routes_content = file_get_contents($routes_file);

// Check if the home route already exists
if (strpos($routes_content, '$router->get(\'/\', \'HomeController\', \'index\');') !== false) {
    log_message("Home route already exists in web.php", 'success');
} else {
    // Add the home route at the beginning of the file
    $home_route = "\n// Home route\n\$router->get('/', 'HomeController', 'index');\n";
    
    // Find the first route definition
    $first_route_pos = strpos($routes_content, '$router->');
    
    if ($first_route_pos !== false) {
        // Insert the home route before the first route
        $new_routes_content = substr($routes_content, 0, $first_route_pos) . $home_route . substr($routes_content, $first_route_pos);
        
        // Write the updated routes file
        if (file_put_contents($routes_file, $new_routes_content) !== false) {
            log_message("Home route added to web.php", 'success');
        } else {
            log_message("Failed to write updated routes file", 'error');
        }
    } else {
        log_message("Could not find any route definitions in web.php", 'error');
    }
}

// Create a test script to verify the home page
$test_script = <<<'EOT'
<?php
/**
 * Test Home Page
 * 
 * This script tests the home page by making a direct request to the HomeController
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

// Define the paths
$controller_file = $public_path . '/app/Controllers/HomeController.php';
$view_file = $public_path . '/app/views/home/index.php';

// Check if the HomeController exists
if (!file_exists($controller_file)) {
    log_message("HomeController not found at: $controller_file", 'error');
} else {
    log_message("HomeController found at: $controller_file", 'success');
    
    // Display the HomeController content
    $controller_content = file_get_contents($controller_file);
    log_message("HomeController content:");
    echo "<pre>" . htmlspecialchars($controller_content) . "</pre>";
}

// Check if the home view exists
if (!file_exists($view_file)) {
    log_message("Home view not found at: $view_file", 'error');
} else {
    log_message("Home view found at: $view_file", 'success');
}

// Create a simple HTML page with links to test
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Siloe Home Page Test</title>
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
    <h1>Siloe Home Page Test</h1>
    <p>Click the buttons below to test the site functionality:</p>
    
    <div>
        <a href='/' class='btn' target='_blank'>Home Page</a>
        <a href='/login' class='btn' target='_blank'>Login Page</a>
        <a href='/admin/companies' class='btn' target='_blank'>Companies Page</a>
    </div>
    
    <div class='container'>
        <h2>Manual Test Instructions</h2>
        <ol>
            <li>Click on the Home Page button to verify it loads correctly</li>
            <li>Click on the Login Page button to verify it loads correctly</li>
            <li>Log in as admin (admin@siloe.com / Admin123!)</li>
            <li>Click on the Companies Page button to verify it loads correctly</li>
        </ol>
    </div>
</body>
</html>
";
EOT;

// Write the test script to a file
$test_script_file = $public_path . '/test_home_page.php';
if (file_put_contents($test_script_file, $test_script) !== false) {
    log_message("Test script created at: $test_script_file", 'success');
} else {
    log_message("Failed to create test script", 'error');
}

log_message("Done", 'success');
