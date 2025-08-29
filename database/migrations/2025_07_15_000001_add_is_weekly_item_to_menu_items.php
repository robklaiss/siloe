<?php

return [
    'up' => function($db) {
        // Add is_weekly_item column if it doesn't exist
        $db->exec('PRAGMA table_info(menu_items)');
        $columns = $db->query("SELECT name FROM PRAGMA_TABLE_INFO('menu_items')")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('is_weekly_item', $columns)) {
            $db->exec('ALTER TABLE menu_items ADD COLUMN is_weekly_item BOOLEAN DEFAULT 0');
        }
    },
    
    'down' => function($db) {
        // SQLite doesn't support dropping columns, so we'll create a new table
        
        // Create a backup of the current table
        $db->exec('CREATE TABLE IF NOT EXISTS menu_items_backup AS SELECT * FROM menu_items');
        
        // Get the schema of the menu_items table
        $schema = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='menu_items'")->fetchColumn();
        
        // Drop the original table
        $db->exec('DROP TABLE IF EXISTS menu_items');
        
        // Recreate the table without the is_weekly_item column
        $createTableSql = preg_replace('/,?\s*is_weekly_item\s+BOOLEAN(?:\s+DEFAULT\s+\d+)?/i', '', $schema);
        $db->exec($createTableSql);
        
        // Get column names from the new table
        $columns = $db->query("SELECT name FROM PRAGMA_TABLE_INFO('menu_items')")->fetchAll(PDO::FETCH_COLUMN);
        $columnList = implode(', ', $columns);
        
        // Copy data back from backup (excluding the is_weekly_item column)
        $db->exec("INSERT INTO menu_items ($columnList) SELECT $columnList FROM menu_items_backup");
        
        // Drop the backup table
        $db->exec('DROP TABLE IF EXISTS menu_items_backup');
    }
];
