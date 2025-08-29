<?php
// Include the bootstrap file to set up the application
require_once __DIR__ . "/../app/bootstrap.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type
echo "<pre>
";

echo "Test route working!

";

// Show current working directory
echo "Current working directory: " . getcwd() . "

";

// Show defined paths
echo "ROOT_PATH: " . (defined('ROOT_PATH') ? ROOT_PATH : 'Not defined') . "
";
echo "APP_PATH: " . (defined('APP_PATH') ? APP_PATH : 'Not defined') . "
";
echo "DB_PATH: " . (defined('DB_PATH') ? DB_PATH : 'Not defined') . "

";

// Show session data
echo "Session data:
";
print_r($_SESSION);

echo "
Trying to connect to database...
";

// Try to connect to database
if (!defined('DB_PATH')) {
    die("Error: DB_PATH is not defined. Check your bootstrap.php and config.php files.");
}

echo "Trying to connect to database at: " . DB_PATH . "
";

if (!file_exists(DB_PATH)) {
    die("Error: Database file does not exist at: " . DB_PATH);
}

if (!is_readable(DB_PATH)) {
    die("Error: Database file is not readable. Check permissions.");
}

try {
    $db = new PDO("sqlite:" . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!

";
    
    // Test query
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type=table");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables in database: " . implode(", ", $tables) . "

";
    
    // Try to get orders
    try {
        $stmt = $db->query("SELECT * FROM orders LIMIT 5");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Sample orders (up to 5):
";
        print_r($orders);
    } catch (Exception $e) {
        echo "Error querying orders table: " . $e->getMessage() . "
";
    }
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage() . "
";
}

echo "</pre>";
?>
