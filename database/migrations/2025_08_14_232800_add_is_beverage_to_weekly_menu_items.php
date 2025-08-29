<?php

return [
    'up' => function($db) {
        // Add is_beverage column if it doesn't exist
        $columns = $db->query("PRAGMA table_info(weekly_menu_items)")->fetchAll(PDO::FETCH_ASSOC);
        $hasColumn = false;
        foreach ($columns as $col) {
            if (strcasecmp($col['name'], 'is_beverage') === 0) {
                $hasColumn = true;
                break;
            }
        }

        if (!$hasColumn) {
            $db->exec('ALTER TABLE weekly_menu_items ADD COLUMN is_beverage BOOLEAN DEFAULT 0');
        }

        // Best-effort backfill: mark likely beverages by name patterns (Spanish + common brands)
        $db->exec("UPDATE weekly_menu_items 
                   SET is_beverage = 1 
                   WHERE LOWER(name) LIKE '%agua%'
                      OR LOWER(name) LIKE '%refresco%'
                      OR LOWER(name) LIKE '%jugo%'
                      OR LOWER(name) LIKE '%café%'
                      OR LOWER(name) LIKE '%cafe%'
                      OR LOWER(name) LIKE '%té%'
                      OR LOWER(name) LIKE '%te%'
                      OR LOWER(name) LIKE '%mate%'
                      OR LOWER(name) LIKE '%terer%'
                      OR LOWER(name) LIKE '%bebida%'
                      OR LOWER(name) LIKE '%soda%'
                      OR LOWER(name) LIKE '%gaseosa%'
                      OR LOWER(name) LIKE '%cola%'
                      OR LOWER(name) LIKE '%coca%'
                      OR LOWER(name) LIKE '%pepsi%'
                      OR LOWER(name) LIKE '%sprite%'
                      OR LOWER(name) LIKE '%fanta%' ");
    },
    'down' => function($db) {
        // SQLite does not support dropping columns directly; recreate table without the column
        // We'll perform a table rebuild to remove is_beverage
        $db->exec('BEGIN TRANSACTION');
        
        // Create a temp table without is_beverage
        $db->exec('CREATE TABLE IF NOT EXISTS weekly_menu_items_tmp (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            is_available BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        
        // Copy data across (excluding is_beverage)
        $db->exec('INSERT INTO weekly_menu_items_tmp (id, name, description, price, is_available, created_at, updated_at)
                   SELECT id, name, description, price, is_available, created_at, updated_at FROM weekly_menu_items');
        
        // Drop original table and rename tmp back
        $db->exec('DROP TABLE weekly_menu_items');
        $db->exec('ALTER TABLE weekly_menu_items_tmp RENAME TO weekly_menu_items');
        
        $db->exec('COMMIT');
    }
];
