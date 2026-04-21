<?php
// Logo serving script to handle 500 errors
// Disable error display for clean output
error_reporting(0);
ini_set('display_errors', 0);

// Set proper headers
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year

$logo_path = 'public/logo-icon.jpeg';

// Check if file exists and is readable
if (file_exists($logo_path) && is_readable($logo_path)) {
    // Get file info
    $file_size = filesize($logo_path);
    $last_modified = filemtime($logo_path);
    
    // Set additional headers
    header('Content-Length: ' . $file_size);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
    header('Access-Control-Allow-Origin: *');
    
    // Clear output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output file
    readfile($logo_path);
    exit;
} else {
    // Fallback: create a simple placeholder image
    if (function_exists('imagecreatetruecolor')) {
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
        
        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output the image
        header('Content-Type: image/jpeg');
        imagejpeg($img);
        imagedestroy($img);
        exit;
    } else {
        // Ultimate fallback - redirect to base64
        header('Location: data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');
        exit;
    }
}
?>
