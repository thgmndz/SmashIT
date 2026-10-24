<?php
// Script to make black background transparent in logo.png
$source = __DIR__ . '/../../logo.png';
$dest = __DIR__ . '/../../logo_transparent.png';

if (!file_exists($source)) {
    die("Error: No se encuentra logo.png en $source\n");
}

$im = imagecreatefrompng($source);
if (!$im) {
    // Try to load as jpeg or other if needed, but it is logo.png
    die("Error: No se pudo abrir la imagen PNG.\n");
}

// Find the black color (usually 0, 0, 0)
// To be safe, let's turn all pixels that are very dark (e.g. R < 15, G < 15, B < 15) to transparent
$width = imagesx($im);
$height = imagesy($im);

// Create a new truecolor image with alpha transparency
$transparent_img = imagecreatetruecolor($width, $height);
imagealphablending($transparent_img, false);
imagesavealpha($transparent_img, true);

// Allocate a fully transparent color
$transparent_color = imagecolorallocatealpha($transparent_img, 0, 0, 0, 127);
imagefill($transparent_img, 0, 0, $transparent_color);

for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $rgb = imagecolorat($im, $x, $y);
        $colors = imagecolorsforindex($im, $rgb);
        
        $r = $colors['red'];
        $g = $colors['green'];
        $b = $colors['blue'];
        $alpha = $colors['alpha'];
        
        // If it is black or very dark, make it transparent
        if ($r < 18 && $g < 18 && $b < 18) {
            imagesetpixel($transparent_img, $x, $y, $transparent_color);
        } else {
            // Keep original pixel but preserve alpha
            $color = imagecolorallocatealpha($transparent_img, $r, $g, $b, $alpha);
            imagesetpixel($transparent_img, $x, $y, $color);
        }
    }
}

imagepng($transparent_img, $dest);
imagedestroy($im);
imagedestroy($transparent_img);

echo "[+] Imagen transparente creada con éxito en: $dest\n";
