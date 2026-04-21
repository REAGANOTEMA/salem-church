<?php
// Direct logo serving script - bypasses all 500 errors
error_reporting(0);
ini_set('display_errors', 0);

// Set proper headers
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=31536000');
header('Access-Control-Allow-Origin: *');

// Clear any output
if (ob_get_level()) {
    ob_end_clean();
}

// Output the actual logo file
$logo_path = 'public/logo-icon.jpeg';
if (file_exists($logo_path)) {
    header('Content-Length: ' . filesize($logo_path));
    readfile($logo_path);
} else {
    // Simple fallback - blue square
    $img = imagecreatetruecolor(100, 100);
    $bg = imagecolorallocate($img, 70, 130, 180);
    imagefill($img, 0, 0, $bg);
    imagejpeg($img);
    imagedestroy($img);
}
exit;
?>
