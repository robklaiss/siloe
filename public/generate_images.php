<?php
// Menu items array
$menuItems = [
    'Pollo a la Parrilla con Ensalada César',
    'Pasta Alfredo con Pollo',
    'Salmón a la Parrilla con Vegetales',
    'Risotto de Champiñones',
    'Arroz con Pollo Tradicional',
    'Ensalada César con Pollo a la Parrilla',
    'Pasta Bolognesa',
    'Ensalada de Quinoa y Vegetales Asados',
    'Pechuga de Pollo Rellena de Espinacas y Queso',
    'Wok de Vegetales con Tofu',
    'Jugo Natural',
    'Limonada Natural',
    'Té Frío',
    'Flan Casero',
    'Tres Leches',
    'Flan Casero (Doble Porción para Compartir)',
    'Tres Leches (Doble Porción para Compartir)'
];

// Create images directory
$imageDir = __DIR__ . '/images/menu_items';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// Generate images
foreach ($menuItems as $item) {
    $filename = strtolower(str_replace(' ', '_', $item)) . '.jpg';
    $filepath = $imageDir . '/' . $filename;
    
    // Create image
    $im = imagecreatetruecolor(500, 500);
    
    // Generate a color based on the item name
    $hash = md5($item);
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 4, 2));
    $b = hexdec(substr($hash, 8, 2));
    
    // Make it pastel
    $r = $r % 155 + 100;
    $g = $g % 155 + 100;
    $b = $b % 155 + 100;
    
    $bgColor = imagecolorallocate($im, $r, $g, $b);
    $textColor = imagecolorallocate($im, 255, 255, 255);
    
    // Fill background
    imagefill($im, 0, 0, $bgColor);
    
    // Add text
    $font = 5; // Built-in font
    $text = wordwrap($item, 15, "\n");
    $lines = explode("\n", $text);
    $lineHeight = 20;
    $startY = (500 - (count($lines) * $lineHeight)) / 2;
    
    foreach ($lines as $i => $line) {
        $textWidth = imagefontwidth($font) * strlen($line);
        $x = (500 - $textWidth) / 2;
        $y = $startY + ($i * $lineHeight);
        imagestring($im, $font, $x, $y, $line, $textColor);
    }
    
    // Save image
    imagejpeg($im, $filepath, 90);
    imagedestroy($im);
    
    echo "Created: $filename\n";
}

echo "All images have been generated!\n";
