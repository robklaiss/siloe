<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Start transaction
$db->beginTransaction();

try {
    // Check if menu for tomorrow already exists
    $tomorrow = '2025-07-16';
    $stmt = $db->prepare('SELECT id FROM menus WHERE date = :date');
    $stmt->execute([':date' => $tomorrow]);
    $menu = $stmt->fetch();

    // Create menu for tomorrow if it doesn't exist
    if (!$menu) {
        $stmt = $db->prepare('INSERT INTO menus (name, description, date, available) VALUES (:name, :description, :date, 1)');
        $stmt->execute([
            ':name' => 'Menú ' . $tomorrow,
            ':description' => 'Menú disponible para el ' . $tomorrow,
            ':date' => $tomorrow
        ]);
        $menuId = $db->lastInsertId();
    } else {
        $menuId = $menu['id'];
    }

    // Menu items for tomorrow
    $menuItems = [
        [
            'name' => 'Pollo a la Parrilla con Ensalada César',
            'description' => 'Pechuga de pollo a la parrilla con nuestra famosa ensalada César y aderezo casero',
            'price' => 22000,
            'is_available' => 1
        ],
        [
            'name' => 'Pasta Alfredo con Pollo',
            'description' => 'Pasta en salsa Alfredo casera con tiras de pollo a la parrilla y parmesano',
            'price' => 20000,
            'is_available' => 1
        ]
    ];

    // Add menu items
    $stmt = $db->prepare('INSERT INTO menu_items (menu_id, name, description, price, is_available) VALUES (:menu_id, :name, :description, :price, :is_available)');
    
    foreach ($menuItems as $item) {
        try {
            $stmt->execute([
                ':menu_id' => $menuId,
                ':name' => $item['name'],
                ':description' => $item['description'],
                ':price' => $item['price'],
                ':is_available' => $item['is_available']
            ]);
            echo "Added: " . $item['name'] . " to menu " . $menuId . "\n";
        } catch (PDOException $e) {
            // Skip if item already exists
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') === false) {
                echo "Error adding " . $item['name'] . ": " . $e->getMessage() . "\n";
            }
        }
    }

    // Commit the transaction
    $db->commit();
    echo "Menu for " . $tomorrow . " has been set up successfully!\n";
    
} catch (Exception $e) {
    // Rollback the transaction if something failed
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
