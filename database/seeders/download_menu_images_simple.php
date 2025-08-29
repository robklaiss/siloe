<?php

require_once __DIR__ . '/../../config/unsplash.php';

// Menu items and their corresponding image search terms
$menuItems = [
    // Main Dishes
    'Pollo a la Parrilla con Ensalada César' => 'grilled+chicken+caesar+salad+white+background',
    'Pasta Alfredo con Pollo' => 'chicken+alfredo+pasta+white+background',
    'Salmón a la Parrilla con Vegetales' => 'grilled+salmon+with+vegetables+white+background',
    'Risotto de Champiñones' => 'mushroom+risotto+white+background',
    'Arroz con Pollo Tradicional' => 'traditional+chicken+rice+white+background',
    'Ensalada César con Pollo a la Parrilla' => 'caesar+salad+with+grilled+chicken+white+background',
    'Pasta Bolognesa' => 'spaghetti+bolognese+white+background',
    'Ensalada de Quinoa y Vegetales Asados' => 'quinoa+salad+with+roasted+vegetables+white+background',
    'Pechuga de Pollo Rellena de Espinacas y Queso' => 'chicken+breast+stuffed+with+spinach+and+cheese+white+background',
    'Wok de Vegetales con Tofu' => 'vegetable+tofu+stir+fry+white+background',
    
    // Beverages
    'Jugo Natural' => 'fresh+fruit+juice+white+background',
    'Limonada Natural' => 'homemade+lemonade+white+background',
    'Té Frío' => 'iced+tea+white+background',
    
    // Desserts
    'Flan Casero' => 'creme+caramel+flan+white+background',
    'Tres Leches' => 'tres+leches+cake+white+background',
    'Flan Casero (Doble Porción para Compartir)' => 'double+portion+creme+caramel+flan+white+background',
    'Tres Leches (Doble Porción para Compartir)' => 'double+portion+tres+leches+cake+white+background'
];

// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/../../../public/images/menu_items';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Download default image if it doesn't exist
$defaultImagePath = $imageDir . '/default.jpg';
if (!file_exists($defaultImagePath)) {
    $defaultImageUrl = 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=500&h=500&fit=crop&auto=format&q=80';
    file_put_contents($defaultImagePath, file_get_contents($defaultImageUrl));
    echo "Downloaded default image\n";
}

// Download menu item images
$accessKey = $config['access_key'];
$baseUrl = 'https://api.unsplash.com/photos/random';

foreach ($menuItems as $itemName => $searchQuery) {
    $filename = strtolower(str_replace(' ', '_', $itemName)) . '.jpg';
    $filepath = $imageDir . '/' . $filename;
    
    // Skip if image already exists
    if (file_exists($filepath)) {
        echo "Skipping {$itemName} - image already exists\n";
        continue;
    }
    
    // Prepare API request
    $url = "{$baseUrl}?query={$searchQuery}&orientation=squarish&client_id={$accessKey}";
    
    // Make API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo "Error downloading image for {$itemName}: " . curl_error($ch) . "\n";
        continue;
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['urls']['regular'])) {
            $imageUrl = $data['urls']['regular'];
            
            // Download the image
            $imageData = file_get_contents($imageUrl);
            if ($imageData !== false) {
                file_put_contents($filepath, $imageData);
                echo "Downloaded image for {$itemName}\n";
            } else {
                echo "Failed to download image for {$itemName}\n";
            }
        } else {
            echo "No image found for {$itemName}\n";
        }
    } else {
        echo "API request failed for {$itemName} with status code {$httpCode}\n";
        echo "Response: {$response}\n";
    }
    
    // Be nice to the API
    sleep(1);
    curl_close($ch);
}

echo "Image download process completed.\n";
