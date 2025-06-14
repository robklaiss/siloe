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
        return $db;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw $e;
    }
}
