<?php

/**
 * Migration to drop the unique index from orders table
 * This allows users to place identical orders as needed
 */

require_once __DIR__ . '/../../config/database.php';

try {
    // Get database connection
    $db = getDbConnection();
    
    echo "Starting migration: Dropping unique index from orders table...\n";
    
    // Begin transaction
    $db->beginTransaction();
    
    // Drop the unique index
    echo "Dropping unique index from orders table...\n";
    $db->exec("DROP INDEX IF EXISTS idx_orders_unique");
    
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
