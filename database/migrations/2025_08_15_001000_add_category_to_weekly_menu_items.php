<?php

return [
    'up' => function($db) {
        // Add category column if it doesn't exist
        $columns = $db->query("PRAGMA table_info(weekly_menu_items)")->fetchAll(PDO::FETCH_ASSOC);
        $hasCategory = false;
        $hasIsBeverage = false;
        foreach ($columns as $col) {
            if (strcasecmp($col['name'], 'category') === 0) {
                $hasCategory = true;
            }
            if (strcasecmp($col['name'], 'is_beverage') === 0) {
                $hasIsBeverage = true;
            }
        }

        if (!$hasCategory) {
            $db->exec('ALTER TABLE weekly_menu_items ADD COLUMN category TEXT');
        }

        // Backfill categories based on existing flags and name heuristics
        // 1) Beverages
        if ($hasIsBeverage) {
            $db->exec("UPDATE weekly_menu_items SET category = 'bebidas' WHERE category IS NULL AND is_beverage = 1");
        }
        $db->exec("UPDATE weekly_menu_items SET category = 'bebidas' WHERE category IS NULL AND (
            LOWER(name) LIKE '%agua%' OR LOWER(name) LIKE '%refresco%' OR LOWER(name) LIKE '%jugo%' OR
            LOWER(name) LIKE '%café%' OR LOWER(name) LIKE '%cafe%' OR LOWER(name) LIKE '%té%' OR LOWER(name) LIKE '%te%' OR
            LOWER(name) LIKE '%mate%' OR LOWER(name) LIKE '%terer%' OR LOWER(name) LIKE '%soda%' OR LOWER(name) LIKE '%gaseosa%' OR
            LOWER(name) LIKE '%cola%' OR LOWER(name) LIKE '%coca%' OR LOWER(name) LIKE '%pepsi%' OR LOWER(name) LIKE '%sprite%' OR LOWER(name) LIKE '%fanta%' OR
            LOWER(name) LIKE '%bebida%'
        )");

        // 2) Desserts
        $db->exec("UPDATE weekly_menu_items SET category = 'postres' WHERE category IS NULL AND (
            LOWER(name) LIKE '%postre%' OR LOWER(name) LIKE '%dessert%' OR LOWER(name) LIKE '%torta%' OR LOWER(name) LIKE '%pastel%' OR LOWER(name) LIKE '%helado%'
        )");

        // 3) Merienda (snacks/light meals)
        $db->exec("UPDATE weekly_menu_items SET category = 'merienda' WHERE category IS NULL AND (
            LOWER(name) LIKE '%merienda%' OR LOWER(name) LIKE '%sandwich%' OR LOWER(name) LIKE '%sándwich%' OR LOWER(name) LIKE '%empanada%' OR LOWER(name) LIKE '%chipa%' OR LOWER(name) LIKE '%tostada%'
        )");

        // 4) Default remaining to Almuerzo (main meals)
        $db->exec("UPDATE weekly_menu_items SET category = 'almuerzo' WHERE category IS NULL");
    },
    'down' => function($db) {
        // Rebuild table without the category column while preserving data
        $db->exec('BEGIN TRANSACTION');

        // Detect if is_beverage exists so we can preserve it
        $columns = $db->query("PRAGMA table_info(weekly_menu_items)")->fetchAll(PDO::FETCH_ASSOC);
        $hasIsBeverage = false;
        foreach ($columns as $col) {
            if (strcasecmp($col['name'], 'is_beverage') === 0) {
                $hasIsBeverage = true;
                break;
            }
        }

        if ($hasIsBeverage) {
            $db->exec('CREATE TABLE IF NOT EXISTS weekly_menu_items_tmp (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                is_available BOOLEAN DEFAULT 1,
                is_beverage BOOLEAN DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )');
            $db->exec('INSERT INTO weekly_menu_items_tmp (id, name, description, price, is_available, is_beverage, created_at, updated_at)
                       SELECT id, name, description, price, is_available, is_beverage, created_at, updated_at FROM weekly_menu_items');
        } else {
            $db->exec('CREATE TABLE IF NOT EXISTS weekly_menu_items_tmp (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                is_available BOOLEAN DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )');
            $db->exec('INSERT INTO weekly_menu_items_tmp (id, name, description, price, is_available, created_at, updated_at)
                       SELECT id, name, description, price, is_available, created_at, updated_at FROM weekly_menu_items');
        }

        $db->exec('DROP TABLE weekly_menu_items');
        $db->exec('ALTER TABLE weekly_menu_items_tmp RENAME TO weekly_menu_items');

        $db->exec('COMMIT');
    }
];
