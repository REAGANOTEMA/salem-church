<?php
// Logo serving script to handle 500 errors
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year

$logo_path = 'public/logo-icon.jpeg';

// Check if file exists
if (file_exists($logo_path)) {
    // Get file info
    $file_size = filesize($logo_path);
    $last_modified = filemtime($logo_path);
    
    // Set headers
    header('Content-Length: ' . $file_size);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
    
    // Output file
    readfile($logo_path);
} else {
    // Fallback: create a simple placeholder image
    $img = imagecreatetruecolor(200, 200);
    $bg_color = imagecolorallocate($img, 70, 130, 180); // Blue background
    $text_color = imagecolorallocate($img, 255, 255, 255); // White text
    
    imagefill($img, 0, 0, $bg_color);
    
    // Add text
    $text = 'CHURCH';
    $font_size = 5;
    $x = (200 - imagefontwidth($font_size) * strlen($text)) / 2;
    $y = (200 - imagefontheight($font_size)) / 2;
    
    imagestring($img, $font_size, $x, $y, $text, $text_color);
    
    // Output the image
    header('Content-Type: image/jpeg');
    imagejpeg($img);
    imagedestroy($img);
}
?>
