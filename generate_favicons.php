<?php
$src = __DIR__ . '/assets/logo-DEFqnQ4s.jpeg';
if (!file_exists($src)) {
    echo "Source logo not found at: $src\n";
    exit(1);
}

$img = imagecreatefromjpeg($src);
if (!$img) {
    echo "Cannot create image from source\n";
    exit(1);
}

$sizes = [16, 32, 48, 72, 96, 128, 144, 152, 192, 384, 512];
$iconsDir = __DIR__ . '/public/icons';

if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

foreach ($sizes as $s) {
    $resized = imagescale($img, $s, $s);
    $file = $iconsDir . '/icon-' . $s . 'x' . $s . '.png';
    imagepng($resized, $file);
    imagedestroy($resized);
    echo "Created: icon-{$s}x{$s}.png\n";
}

$at = imagescale($img, 180, 180);
imagepng($at, __DIR__ . '/public/apple-touch-icon.png');
imagedestroy($at);
echo "Created: apple-touch-icon.png\n";

$fav32 = imagescale($img, 32, 32);
imagepng($fav32, __DIR__ . '/public/images/favicon.png');
imagedestroy($fav32);
echo "Created: favicon.png\n";

$fav16 = imagescale($img, 16, 16);
imagepng($fav16, __DIR__ . '/public/images/favicon-16x16.png');
imagedestroy($fav16);
echo "Created: favicon-16x16.png\n";

$favSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="%230f172a" width="100" height="100" rx="20"/><text x="50" y="65" text-anchor="middle" fill="%23fbbf24" font-size="42" font-weight="bold" font-family="serif">SDM</text></svg>';
file_put_contents(__DIR__ . '/public/favicon.svg', $favSvg);
echo "Created: favicon.svg\n";

copy($src, __DIR__ . '/public/logo-icon.jpeg');
echo "Copied: logo-icon.jpeg\n";

imagedestroy($img);
echo "All favicons generated successfully!\n";
