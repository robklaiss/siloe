<?php
session_start();
require_once __DIR__ . "/../app/bootstrap.php";

// Simple test page
echo "<h1>Orders Test Page</h1>";

try {
    // Test database connection
    $db = new PDO("sqlite:" . __DIR__ . "/../database/siloe.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Database connection successful!</p>";
    
    // Get recent orders
    $stmt = $db->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($orders) > 0) {
        echo "<h2>Recent Orders</h2>";
        echo "<ul>";
        foreach ($orders as $order) {
            echo "<li>Order #" . htmlspecialchars($order[id]) . " - " . 
                 htmlspecialchars($order[user_name]) . " - " . 
                 htmlspecialchars($order[created_at]) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No orders found in the database.</p>";
    }
    
} catch(PDOException $e) {
    echo "<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Show session data
echo "<h2>Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
