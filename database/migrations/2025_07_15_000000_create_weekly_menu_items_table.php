<?php

return [
    'up' => function($db) {
        $db->exec('CREATE TABLE IF NOT EXISTS weekly_menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            is_available BOOLEAN DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        
        // Add some default weekly items
        $defaultItems = [
            ['name' => 'Ensalada César', 'description' => 'Lechuga romana, crutones, queso parmesano, aderezo César', 'price' => 8.99],
            ['name' => 'Sopa del Día', 'description' => 'Sopa del día preparada con ingredientes frescos', 'price' => 6.50],
            ['name' => 'Pasta Alfredo', 'description' => 'Pasta con salsa cremosa de queso parmesano', 'price' => 10.99],
            ['name' => 'Pollo a la Parrilla', 'description' => 'Pechuga de pollo a la parrilla con vegetales al vapor', 'price' => 12.99],
            ['name' => 'Postre del Día', 'description' => 'Postre casero del día', 'price' => 5.50]
        ];
        
        $stmt = $db->prepare('INSERT INTO weekly_menu_items (name, description, price) VALUES (:name, :description, :price)');
        
        foreach ($defaultItems as $item) {
            $stmt->execute([
                ':name' => $item['name'],
                ':description' => $item['description'],
                ':price' => $item['price']
            ]);
        }
    },
    'down' => function($db) {
        $db->exec('DROP TABLE IF EXISTS weekly_menu_items');
    }
];
