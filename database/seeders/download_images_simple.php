<?php

// Menu items and their corresponding image search terms
$menuItems = [
    // Main Dishes
    'Pollo a la Parrilla con Ensalada César' => 'grilled+chicken+caesar+salad',
    'Pasta Alfredo con Pollo' => 'chicken+alfredo+pasta',
    'Salmón a la Parrilla con Vegetales' => 'grilled+salmon+with+vegetables',
    'Risotto de Champiñones' => 'mushroom+risotto',
    'Arroz con Pollo Tradicional' => 'traditional+chicken+rice',
    'Ensalada César con Pollo a la Parrilla' => 'caesar+salad+with+grilled+chicken',
    'Pasta Bolognesa' => 'spaghetti+bolognese',
    'Ensalada de Quinoa y Vegetales Asados' => 'quinoa+salad+with+roasted+vegetables',
    'Pechuga de Pollo Rellena de Espinacas y Queso' => 'chicken+breast+stuffed+with+spinach+and+cheese',
    'Wok de Vegetales con Tofu' => 'vegetable+tofu+stir+fry',
    
    // Beverages
    'Jugo Natural' => 'fresh+fruit+juice',
    'Limonada Natural' => 'homemade+lemonade',
    'Té Frío' => 'iced+tea',
    
    // Desserts
    'Flan Casero' => 'creme+caramel+flan',
    'Tres Leches' => 'tres+leches+cake',
    'Flan Casero (Doble Porción para Compartir)' => 'double+portion+creme+caramel+flan',
    'Tres Leches (Doble Porción para Compartir)' => 'double+portion+tres+leches+cake'
];

// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/../../../public/images/menu_items';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Download default image if it doesn't exist
$defaultImagePath = $imageDir . '/default.jpg';
if (!file_exists($defaultImagePath)) {
    $defaultImageUrl = 'https://source.unsplash.com/500x500/?food';
    file_put_contents($defaultImagePath, file_get_contents($defaultImageUrl));
    echo "Downloaded default image\n";
}

// Download menu item images
foreach ($menuItems as $itemName => $searchQuery) {
    $filename = strtolower(str_replace(' ', '_', $itemName)) . '.jpg';
    $filepath = $imageDir . '/' . $filename;
    
    // Skip if image already exists
    if (file_exists($filepath)) {
        echo "Skipping {$itemName} - image already exists\n";
        continue;
    }
    
    // Use Unsplash source with search query
    $imageUrl = "https://source.unsplash.com/500x500/?" . $searchQuery;
    
    // Download the image
    $imageData = @file_get_contents($imageUrl);
    if ($imageData !== false) {
        file_put_contents($filepath, $imageData);
        echo "Downloaded image for {$itemName}\n";
    } else {
        echo "Failed to download image for {$itemName}\n";
        // Copy default image
        copy($defaultImagePath, $filepath);
        echo "  -> Used default image for {$itemName}\n";
    }
    
    // Be nice to the server
    sleep(1);
}

echo "Image download process completed.\n";
