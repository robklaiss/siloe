<?php

/**
 * Migration to add a unique index to orders table to prevent duplicates
 * Similar to what was done for the menus table
 */

require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $db = getDbConnection();
    
    echo "Starting migration: Adding unique index to orders table...\n";
    
    // Begin transaction
    $db->beginTransaction();
    
    // First, remove any duplicate orders (keeping the oldest one)
    echo "Checking for and removing duplicate orders...\n";
    
    // Find duplicates based on user_id, order_date, and special_requests
    $duplicates = $db->query("
        SELECT user_id, order_date, special_requests, MIN(id) as keep_id, COUNT(*) as count
        FROM orders
        GROUP BY user_id, order_date, special_requests
        HAVING COUNT(*) > 1
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $removed = 0;
    foreach ($duplicates as $duplicate) {
        // Delete all duplicates except the one to keep
        $stmt = $db->prepare("
            DELETE FROM orders 
            WHERE user_id = :user_id 
            AND order_date = :order_date 
            AND special_requests = :special_requests
            AND id != :keep_id
        ");
        
        $stmt->execute([
            ':user_id' => $duplicate['user_id'],
            ':order_date' => $duplicate['order_date'],
            ':special_requests' => $duplicate['special_requests'],
            ':keep_id' => $duplicate['keep_id']
        ]);
        
        $removed += $stmt->rowCount();
    }
    
    echo "Removed {$removed} duplicate orders.\n";
    
    // Add unique index to prevent future duplicates
    echo "Adding unique index to orders table...\n";
    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_orders_unique ON orders(user_id, order_date, special_requests)");
    
    // Commit transaction
    $db->commit();
    
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
