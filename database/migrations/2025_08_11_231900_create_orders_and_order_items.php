<?php

return [
    'up' => function($db) {
        // Ensure foreign keys are enabled
        $db->exec('PRAGMA foreign_keys = ON');

        // Helper to check if a table exists
        $tableExists = function($name) use ($db) {
            $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :name");
            $stmt->execute([':name' => $name]);
            return (bool)$stmt->fetchColumn();
        };

        // Create orders table if not exists
        if (!$tableExists('orders')) {
            $db->exec('CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                company_id INTEGER,
                order_date DATETIME NOT NULL,
                status VARCHAR(50) NOT NULL DEFAULT "pending",
                special_requests TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )');

            // Useful indexes
            $db->exec('CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id)');
            $db->exec('CREATE INDEX IF NOT EXISTS idx_orders_company_id ON orders(company_id)');
            $db->exec('CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)');
            $db->exec('CREATE INDEX IF NOT EXISTS idx_orders_order_date ON orders(order_date)');
        }

        // Create order_items table if not exists
        if (!$tableExists('order_items')) {
            $db->exec('CREATE TABLE order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                menu_item_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                special_requests TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
            )');

            // Useful indexes
            $db->exec('CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id)');
            $db->exec('CREATE INDEX IF NOT EXISTS idx_order_items_menu_item_id ON order_items(menu_item_id)');
        }
    },
    'down' => function($db) {
        // Down migration (drop tables) - be cautious in production
        $db->exec('DROP TABLE IF EXISTS order_items');
        $db->exec('DROP TABLE IF EXISTS orders');
    }
];
