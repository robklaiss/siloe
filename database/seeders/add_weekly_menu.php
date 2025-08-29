<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Menu items for Wednesday, July 16th
$wednesdayItems = [
    [
        'name' => 'Pollo a la Parrilla con Ensalada César',
        'description' => 'Pechuga de pollo a la parrilla con nuestra famosa ensalada César y aderezo casero',
        'price' => 22000,
        'date' => '2025-07-16'
    ],
    [
        'name' => 'Pasta Alfredo con Pollo',
        'description' => 'Pasta en salsa Alfredo casera con tiras de pollo a la parrilla y parmesano',
        'price' => 20000,
        'date' => '2025-07-16'
    ]
];

// Menu items for Thursday, July 17th
$thursdayItems = [
    [
        'name' => 'Salmón a la Parrilla con Vegetales',
        'description' => 'Filete de salmón fresco a la parrilla con vegetales de temporada al vapor',
        'price' => 25000,
        'date' => '2025-07-17'
    ],
    [
        'name' => 'Risotto de Champiñones',
        'description' => 'Risotto cremoso con champiñones silvestres y vino blanco',
        'price' => 18000,
        'date' => '2025-07-17'
    ]
];

// Function to add menu items
function addMenuItems($db, $items) {
    $stmt = $db->prepare('INSERT INTO menus (name, description, price, date, available) VALUES (:name, :description, :price, :date, 1)');
    
    foreach ($items as $item) {
        try {
            $stmt->execute([
                ':name' => $item['name'],
                ':description' => $item['description'],
                ':price' => $item['price'],
                ':date' => $item['date']
            ]);
            echo "Added: " . $item['name'] . " for " . $item['date'] . "\n";
        } catch (PDOException $e) {
            // Skip if item already exists
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') === false) {
                echo "Error adding " . $item['name'] . ": " . $e->getMessage() . "\n";
            }
        }
    }
}

// Add menu items
addMenuItems($db, $wednesdayItems);
addMenuItems($db, $thursdayItems);

echo "Menu items added successfully!\n";
