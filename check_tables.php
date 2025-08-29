<?php
require 'config/database.php';

$db = getDbConnection();

// Check if we're connected
if ($db) {
    echo "Successfully connected to the database.\n";
    
    // List all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nTables in the database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
        
        // Show table structure
        $columns = $db->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "  - {$column['name']} ({$column['type']})\n";
        }
    }
} else {
    echo "Failed to connect to the database.\n";
}
