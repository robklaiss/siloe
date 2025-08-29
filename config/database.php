<?php

/**
 * Database connection configuration
 */

/**
 * Get a PDO database connection
 * 
 * @return PDO
 */
function getDbConnection() {
    $db_path = __DIR__ . '/../database/siloe.db';
    
    try {
        $db = new PDO('sqlite:' . $db_path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Ensure SQLite foreign key constraints are enforced (required for cascades)
        $db->exec('PRAGMA foreign_keys = ON');
        return $db;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw $e;
    }
}
