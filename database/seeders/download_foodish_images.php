<?php

// Menu items and their corresponding image types from Foodish API
$menuItems = [
    // Main Dishes
    'Pollo a la Parrilla con Ensalada César' => 'chicken',
    'Pasta Alfredo con Pollo' => 'pasta',
    'Salmón a la Parrilla con Vegetales' => 'salmon',
    'Risotto de Champiñones' => 'risotto',
    'Arroz con Pollo Tradicional' => 'chicken',
    'Ensalada César con Pollo a la Parrilla' => 'salad',
    'Pasta Bolognesa' => 'pasta',
    'Ensalada de Quinoa y Vegetales Asados' => 'salad',
    'Pechuga de Pollo Rellena de Espinacas y Queso' => 'chicken',
    'Wok de Vegetales con Tofu' => 'tofu',
    
    // Beverages
    'Jugo Natural' => 'juice',
    'Limonada Natural' => 'lemonade',
    'Té Frío' => 'tea',
    
    // Desserts
    'Flan Casero' => 'dessert',
    'Tres Leches' => 'cake',
    'Flan Casero (Doble Porción para Compartir)' => 'dessert',
    'Tres Leches (Doble Porción para Compartir)' => 'cake'
];

// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/../../../public/images/menu_items';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Download default image if it doesn't exist
$defaultImagePath = $imageDir . '/default.jpg';
if (!file_exists($defaultImagePath)) {
    $defaultImage = file_get_contents('https://foodish-api.com/images/burger/burger1.jpg');
    if ($defaultImage !== false) {
        file_put_contents($defaultImagePath, $defaultImage);
        echo "Downloaded default image\n";
    }
}

// Download menu item images
foreach ($menuItems as $itemName => $foodType) {
    $filename = strtolower(str_replace(' ', '_', $itemName)) . '.jpg';
    $filepath = $imageDir . '/' . $filename;
    
    // Skip if image already exists
    if (file_exists($filepath)) {
        echo "Skipping {$itemName} - image already exists\n";
        continue;
    }
    
    // Try to get a specific food type image, fall back to random
    $imageUrl = "https://foodish-api.com/images/{$foodType}/{$foodType}" . rand(1, 30) . ".jpg";
    $imageData = @file_get_contents($imageUrl);
    
    // If specific type fails, try random food image
    if ($imageData === false) {
        $imageUrl = 'https://foodish-api.com/images/burger/burger' . rand(1, 30) . '.jpg';
        $imageData = @file_get_contents($imageUrl);
    }
    
    if ($imageData !== false) {
        file_put_contents($filepath, $imageData);
        echo "Downloaded image for {$itemName}\n";
    } else {
        echo "Failed to download image for {$itemName}, using default\n";
        // Copy default image
        copy($defaultImagePath, $filepath);
    }
    
    // Be nice to the server
    usleep(500000); // 0.5 second delay
}

echo "Image download process completed.\n";
