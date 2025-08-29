<?php

return [
    'up' => function($db) {
        // Add order_id column to employee_menu_selections if it doesn't exist
        $columns = $db->query("PRAGMA table_info(employee_menu_selections)")->fetchAll(PDO::FETCH_ASSOC);
        $hasOrderId = false;
        foreach ($columns as $col) {
            if (strcasecmp($col['name'], 'order_id') === 0) {
                $hasOrderId = true;
                break;
            }
        }

        if (!$hasOrderId) {
            $db->exec('ALTER TABLE employee_menu_selections ADD COLUMN order_id INTEGER NULL');
        }

        // Create index to speed up lookups
        $db->exec('CREATE INDEX IF NOT EXISTS idx_employee_menu_selections_order_id ON employee_menu_selections(order_id)');
    },
    'down' => function($db) {
        // SQLite cannot drop columns easily; leaving as no-op for safety.
        // To revert, manually recreate the table without order_id and copy data.
    }
];
