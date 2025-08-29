<?php

require_once __DIR__ . '/../../config/database.php';

$db = getDbConnection();

// Start transaction
$db->beginTransaction();

try {
    // Weekly menu data
    $weeklyMenu = [
        '2025-07-16' => [ // Wednesday
            'main_dishes' => [
                [
                    'name' => 'Pollo a la Parrilla con Ensalada César',
                    'description' => 'Pechuga de pollo a la parrilla con nuestra famosa ensalada César y aderezo casero',
                    'price' => 22000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 1,
                    'has_dairy' => 1
                ],
                [
                    'name' => 'Pasta Alfredo con Pollo',
                    'description' => 'Pasta en salsa Alfredo casera con tiras de pollo a la parrilla y parmesano',
                    'price' => 20000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 1,
                    'has_dairy' => 1
                ]
            ]
        ],
        '2025-07-17' => [ // Thursday
            'main_dishes' => [
                [
                    'name' => 'Salmón a la Parrilla con Vegetales',
                    'description' => 'Filete de salmón fresco a la parrilla con vegetales de temporada al vapor',
                    'price' => 25000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 0,
                    'has_dairy' => 0
                ],
                [
                    'name' => 'Risotto de Champiñones',
                    'description' => 'Risotto cremoso con champiñones silvestres y vino blanco',
                    'price' => 18000,
                    'is_vegetarian' => 1,
                    'has_gluten' => 1,
                    'has_dairy' => 1
                ]
            ]
        ],
        '2025-07-18' => [ // Friday
            'main_dishes' => [
                [
                    'name' => 'Arroz con Pollo Tradicional',
                    'description' => 'Arroz con pollo estilo casero con vegetales y especias',
                    'price' => 19000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 0,
                    'has_dairy' => 0
                ],
                [
                    'name' => 'Ensalada César con Pollo a la Parrilla',
                    'description' => 'Lechuga romana, crutones, queso parmesano y aderezo César con pechuga de pollo a la parrilla',
                    'price' => 21000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 1,
                    'has_dairy' => 1
                ]
            ]
        ],
        '2025-07-21' => [ // Monday
            'main_dishes' => [
                [
                    'name' => 'Pasta Bolognesa',
                    'description' => 'Pasta con salsa bolognesa de carne molida y tomate, espolvoreado con queso parmesano',
                    'price' => 21000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 1,
                    'has_dairy' => 1
                ],
                [
                    'name' => 'Ensalada de Quinoa y Vegetales Asados',
                    'description' => 'Quinoa con mezcla de vegetales asados y aderezo de limón',
                    'price' => 19000,
                    'is_vegetarian' => 1,
                    'has_gluten' => 0,
                    'has_dairy' => 0
                ]
            ]
        ],
        '2025-07-22' => [ // Tuesday
            'main_dishes' => [
                [
                    'name' => 'Pechuga de Pollo Rellena de Espinacas y Queso',
                    'description' => 'Pechuga de pollo rellena de espinacas y queso, acompañada de puré de papas',
                    'price' => 23000,
                    'is_vegetarian' => 0,
                    'has_gluten' => 1,
                    'has_dairy' => 1
                ],
                [
                    'name' => 'Wok de Vegetales con Tofu',
                    'description' => 'Mezcla de vegetales salteados al wok con tofu y salsa de soja baja en sodio',
                    'price' => 20000,
                    'is_vegetarian' => 1,
                    'has_gluten' => 1,
                    'has_dairy' => 0
                ]
            ]
        ]
    ];

    // Common beverages and desserts for all days
    $beverages = [
        [
            'name' => 'Jugo Natural',
            'description' => 'Jugo de frutas naturales de temporada',
            'price' => 2000,
            'is_vegetarian' => 1,
            'has_gluten' => 0,
            'has_dairy' => 0
        ],
        [
            'name' => 'Limonada Natural',
            'description' => 'Limonada natural endulzada con miel',
            'price' => 2000,
            'is_vegetarian' => 1,
            'has_gluten' => 0,
            'has_dairy' => 0
        ],
        [
            'name' => 'Té Frío',
            'description' => 'Té helado con limón y menta',
            'price' => 1800,
            'is_vegetarian' => 1,
            'has_gluten' => 0,
            'has_dairy' => 0
        ]
    ];

    $desserts = [
        [
            'name' => 'Flan Casero',
            'description' => 'Tradicional flan de huevo con caramelo',
            'price' => 3000,
            'is_vegetarian' => 1,
            'has_gluten' => 0,
            'has_dairy' => 1
        ],
        [
            'name' => 'Tres Leches',
            'description' => 'Bizcocho esponjoso bañado en mezcla de tres leches',
            'price' => 3500,
            'is_vegetarian' => 1,
            'has_gluten' => 1,
            'has_dairy' => 1
        ],
        [
            'name' => 'Flan Casero (Doble Porción para Compartir)',
            'description' => 'Doble porción de nuestro tradicional flan de huevo con caramelo para compartir',
            'price' => 5000,
            'is_vegetarian' => 1,
            'has_gluten' => 0,
            'has_dairy' => 1
        ],
        [
            'name' => 'Tres Leches (Doble Porción para Compartir)',
            'description' => 'Doble porción de nuestro delicioso tres leches para compartir',
            'price' => 6000,
            'is_vegetarian' => 1,
            'has_gluten' => 1,
            'has_dairy' => 1
        ]
    ];

    // Prepare statements
    $menuStmt = $db->prepare('INSERT INTO menus (name, description, date, available) VALUES (:name, :description, :date, 1)');
    $itemStmt = $db->prepare('INSERT INTO menu_items (menu_id, name, description, price, is_available, is_vegetarian, has_gluten, has_dairy) VALUES (:menu_id, :name, :description, :price, 1, :is_vegetarian, :has_gluten, :has_dairy)');

    foreach ($weeklyMenu as $date => $dayMenu) {
        // Create menu for the day
        $menuStmt->execute([
            ':name' => 'Menú ' . $date,
            ':description' => 'Menú disponible para el ' . $date,
            ':date' => $date
        ]);
        $menuId = $db->lastInsertId();
        
        echo "Created menu for $date (ID: $menuId)\n";

        // Add main dishes
        foreach ($dayMenu['main_dishes'] as $item) {
            $itemStmt->execute([
                ':menu_id' => $menuId,
                ':name' => $item['name'],
                ':description' => $item['description'],
                ':price' => $item['price'],
                ':is_vegetarian' => $item['is_vegetarian'],
                ':has_gluten' => $item['has_gluten'],
                ':has_dairy' => $item['has_dairy']
            ]);
            echo "  - Added main dish: {$item['name']}\n";
        }

        // Add beverages
        foreach ($beverages as $beverage) {
            $itemStmt->execute([
                ':menu_id' => $menuId,
                ':name' => $beverage['name'],
                ':description' => $beverage['description'],
                ':price' => $beverage['price'],
                ':is_vegetarian' => $beverage['is_vegetarian'],
                ':has_gluten' => $beverage['has_gluten'],
                ':has_dairy' => $beverage['has_dairy']
            ]);
            echo "  - Added beverage: {$beverage['name']}\n";
        }

        // Add desserts (including double portions for sharing)
        foreach ($desserts as $dessert) {
            $itemStmt->execute([
                ':menu_id' => $menuId,
                ':name' => $dessert['name'],
                ':description' => $dessert['description'],
                ':price' => $dessert['price'],
                ':is_vegetarian' => $dessert['is_vegetarian'],
                ':has_gluten' => $dessert['has_gluten'],
                ':has_dairy' => $dessert['has_dairy']
            ]);
            echo "  - Added dessert: {$dessert['name']}\n";
        }
    }

    // Commit the transaction
    $db->commit();
    echo "Weekly menu has been set up successfully!\n";
    
} catch (Exception $e) {
    // Rollback the transaction if something failed
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
