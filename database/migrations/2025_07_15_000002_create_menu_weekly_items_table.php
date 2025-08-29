<?php

return [
    'up' => function($db) {
        // Create menu_weekly_items table
        $db->exec('CREATE TABLE IF NOT EXISTS menu_weekly_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            menu_id INTEGER NOT NULL,
            weekly_item_id INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
            FOREIGN KEY (weekly_item_id) REFERENCES weekly_menu_items(id) ON DELETE CASCADE,
            UNIQUE(menu_id, weekly_item_id)
        )');
        
        // Add index for better performance
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menu_weekly_items_menu_id ON menu_weekly_items(menu_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menu_weekly_items_weekly_item_id ON menu_weekly_items(weekly_item_id)');
    },
    
    'down' => function($db) {
        $db->exec('DROP TABLE IF EXISTS menu_weekly_items');
    }
];
