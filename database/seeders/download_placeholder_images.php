<?php

// Menu items with their corresponding image keywords
$menuItems = [
    // Main Dishes
    'Pollo a la Parrilla con Ensalada César' => 'chicken,grilled,salad',
    'Pasta Alfredo con Pollo' => 'pasta,chicken,alfredo',
    'Salmón a la Parrilla con Vegetales' => 'salmon,grilled,vegetables',
    'Risotto de Champiñones' => 'risotto,mushroom',
    'Arroz con Pollo Tradicional' => 'chicken,rice',
    'Ensalada César con Pollo a la Parrilla' => 'caesar,salad,chicken',
    'Pasta Bolognesa' => 'pasta,bolognese',
    'Ensalada de Quinoa y Vegetales Asados' => 'quinoa,salad,roasted,vegetables',
    'Pechuga de Pollo Rellena de Espinacas y Queso' => 'chicken,spinach,cheese',
    'Wok de Vegetales con Tofu' => 'tofu,vegetables,stirfry',
    
    // Beverages
    'Jugo Natural' => 'juice,fresh',
    'Limonada Natural' => 'lemonade',
    'Té Frío' => 'iced,tea',
    
    // Desserts
    'Flan Casero' => 'flan,dessert',
    'Tres Leches' => 'cake,dessert',
    'Flan Casero (Doble Porción para Compartir)' => 'flan,dessert,double',
    'Tres Leches (Doble Porción para Compartir)' => 'cake,dessert,double'
];

// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/../../../public/images/menu_items';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Download default image if it doesn't exist
$defaultImagePath = $imageDir . '/default.jpg';
if (!file_exists($defaultImagePath)) {
    // Create a simple colored placeholder
    $im = imagecreatetruecolor(500, 500);
    $bgColor = imagecolorallocate($im, 240, 240, 240);
    $textColor = imagecolorallocate($im, 150, 150, 150);
    imagefill($im, 0, 0, $bgColor);
    $text = "Food\nImage";
    $font = 5; // Built-in font
    $textWidth = imagefontwidth($font) * strlen(explode("\n", $text)[0]);
    $textHeight = imagefontheight($font) * count(explode("\n", $text));
    $x = (500 - $textWidth) / 2;
    $y = (500 - $textHeight) / 2;
    foreach (explode("\n", $text) as $i => $line) {
        imagestring($im, $font, $x, $y + ($i * 20), $line, $textColor);
    }
    imagejpeg($im, $defaultImagePath, 90);
    imagedestroy($im);
    echo "Created default image\n";
}

// Download menu item images
foreach ($menuItems as $itemName => $keywords) {
    $filename = strtolower(str_replace(' ', '_', $itemName)) . '.jpg';
    $filepath = $imageDir . '/' . $filename;
    
    // Skip if image already exists
    if (file_exists($filepath)) {
        echo "Skipping {$itemName} - image already exists\n";
        continue;
    }
    
    // Create a simple colored placeholder with text
    $im = imagecreatetruecolor(500, 500);
    
    // Generate a color based on the item name
    $hash = md5($itemName);
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    
    // Make it pastel
    $r = $r % 155 + 100;
    $g = $g % 155 + 100;
    $b = $b % 155 + 100;
    
    $bgColor = imagecolorallocate($im, $r, $g, $b);
    $textColor = imagecolorallocate($im, 255, 255, 255);
    
    imagefill($im, 0, 0, $bgColor);
    
    // Add text (item name)
    $font = 5; // Built-in font
    $text = wordwrap($itemName, 20, "\n");
    $lines = explode("\n", $text);
    $lineHeight = 20;
    $startY = (500 - (count($lines) * $lineHeight)) / 2;
    
    foreach ($lines as $i => $line) {
        $textWidth = imagefontwidth($font) * strlen($line);
        $x = (500 - $textWidth) / 2;
        $y = $startY + ($i * $lineHeight);
        imagestring($im, $font, $x, $y, $line, $textColor);
    }
    
    // Save the image
    imagejpeg($im, $filepath, 90);
    imagedestroy($im);
    
    echo "Created placeholder for {$itemName}\n";
    
    // Small delay
    usleep(100000); // 0.1 second
}

echo "Image creation process completed.\n";
