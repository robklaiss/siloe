<?php
/**
 * Test Admin Views Files
 * 
 * This script checks if all required files for the admin views are present
 * and properly structured
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

// Check if the CompanyController exists
$controller_path = $public_path . '/app/Controllers/Admin/CompanyController.php';
if (file_exists($controller_path)) {
    log_message("CompanyController found at: $controller_path", 'success');
    
    // Check if the controller extends the base Controller class
    $controller_content = file_get_contents($controller_path);
    if (strpos($controller_content, 'extends Controller') !== false) {
        log_message("CompanyController extends Controller class", 'success');
    } else {
        log_message("CompanyController does not extend Controller class", 'error');
    }
    
    // Check if the controller has the index method
    if (strpos($controller_content, 'function index') !== false) {
        log_message("CompanyController has index method", 'success');
    } else {
        log_message("CompanyController does not have index method", 'error');
    }
} else {
    log_message("CompanyController not found at: $controller_path", 'error');
}

// Check if the base Controller class exists
$base_controller_path = $public_path . '/app/Core/Controller.php';
if (file_exists($base_controller_path)) {
    log_message("Base Controller class found at: $base_controller_path", 'success');
} else {
    log_message("Base Controller class not found at: $base_controller_path", 'error');
}

// Check if the companies view exists
$view_path = $public_path . '/app/views/admin/companies/index.php';
if (file_exists($view_path)) {
    log_message("Companies view found at: $view_path", 'success');
} else {
    log_message("Companies view not found at: $view_path", 'error');
}

// Check if the Company model exists
$model_path = $public_path . '/app/Models/Company.php';
if (file_exists($model_path)) {
    log_message("Company model found at: $model_path", 'success');
} else {
    log_message("Company model not found at: $model_path", 'error');
}

// Check if the base Model class exists
$base_model_path = $public_path . '/app/Core/Model.php';
if (file_exists($base_model_path)) {
    log_message("Base Model class found at: $base_model_path", 'success');
} else {
    log_message("Base Model class not found at: $base_model_path", 'error');
}

// Check if the routes file exists
$routes_path = $public_path . '/app/routes/web.php';
if (file_exists($routes_path)) {
    log_message("Routes file found at: $routes_path", 'success');
    
    // Check if the routes file has the companies routes
    $routes_content = file_get_contents($routes_path);
    if (strpos($routes_content, '/admin/companies') !== false) {
        log_message("Routes file has companies routes", 'success');
    } else {
        log_message("Routes file does not have companies routes", 'error');
    }
} else {
    log_message("Routes file not found at: $routes_path", 'error');
}

// Check if the Router class exists
$router_path = $public_path . '/app/Core/Router.php';
if (file_exists($router_path)) {
    log_message("Router class found at: $router_path", 'success');
} else {
    log_message("Router class not found at: $router_path", 'error');
}

// Check if the middleware files exist
$auth_middleware_path = $public_path . '/app/Middleware/AuthMiddleware.php';
if (file_exists($auth_middleware_path)) {
    log_message("AuthMiddleware found at: $auth_middleware_path", 'success');
} else {
    log_message("AuthMiddleware not found at: $auth_middleware_path", 'error');
}

// Check if the database connection is working
try {
    $db_path = $public_path . '/database/siloe.db';
    
    if (!file_exists($db_path)) {
        $db_path = $public_path . '/database/database.sqlite';
    }
    
    if (!file_exists($db_path)) {
        log_message("Database file not found at: $db_path", 'error');
    } else {
        log_message("Database file found at: $db_path", 'success');
        
        $pdo = new PDO('sqlite:' . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        log_message("Database connection established", 'success');
        
        // Check if the companies table exists
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='companies'");
        $table_exists = $stmt->fetchColumn();
        
        if ($table_exists) {
            log_message("Companies table exists", 'success');
            
            // Check the companies table structure
            $stmt = $pdo->query("PRAGMA table_info(companies)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            log_message("Companies table structure:");
            foreach ($columns as $column) {
                log_message("- {$column['name']} ({$column['type']})");
            }
            
            // Check if there are any companies
            $stmt = $pdo->query("SELECT COUNT(*) FROM companies");
            $count = $stmt->fetchColumn();
            
            log_message("Found $count companies", $count > 0 ? 'success' : 'warning');
        } else {
            log_message("Companies table does not exist", 'error');
        }
    }
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage(), 'error');
}

// Check if the index.php file is properly configured
$index_path = $public_path . '/index.php';
if (file_exists($index_path)) {
    log_message("Index file found at: $index_path", 'success');
    
    // Check if the index file loads the router
    $index_content = file_get_contents($index_path);
    if (strpos($index_content, 'Router') !== false && strpos($index_content, 'dispatch') !== false) {
        log_message("Index file loads the router", 'success');
    } else {
        log_message("Index file does not load the router", 'error');
    }
} else {
    log_message("Index file not found at: $index_path", 'error');
}

// Check if the .htaccess file is properly configured
$htaccess_path = $public_path . '/.htaccess';
if (file_exists($htaccess_path)) {
    log_message("Htaccess file found at: $htaccess_path", 'success');
    
    // Check if the htaccess file has the rewrite rules
    $htaccess_content = file_get_contents($htaccess_path);
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        log_message("Htaccess file has rewrite rules", 'success');
    } else {
        log_message("Htaccess file does not have rewrite rules", 'error');
    }
} else {
    log_message("Htaccess file not found at: $htaccess_path", 'error');
}

log_message("Test completed", 'success');
