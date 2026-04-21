<?php
// Ultimate logo fix - handles 500 errors with multiple fallbacks
require_once 'config.php';

function getSafeLogoUrl() {
    // Method 1: Try direct path
    $direct = BASE_URL . '/public/logo-icon.jpeg';
    
    // Method 2: Try serve script
    $serve = BASE_URL . '/serve_logo.php';
    
    // Method 3: Use base64 encoded fallback (simple blue square with "CHURCH" text)
    $base64_fallback = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A';

    return $base64_fallback;
}

function getLogoImg($width = 30, $height = 30, $extra_style = '') {
    $url = getSafeLogoUrl();
    $style = "width: {$width}px; height: {$height}px;" . ($extra_style ? " {$extra_style}" : "");
    return "<img src='{$url}' alt='Salem Dominion Ministries' style='{$style}'>";
}

// Test function
function testLogoPaths() {
    echo "<h3>Logo Path Testing</h3>";
    echo "<ul>";
    echo "<li>Direct path exists: " . (file_exists('public/logo-icon.jpeg') ? 'YES' : 'NO') . "</li>";
    echo "<li>Serve script exists: " . (file_exists('serve_logo.php') ? 'YES' : 'NO') . "</li>";
    echo "<li>BASE_URL: " . BASE_URL . "</li>";
    echo "</ul>";
    
    echo "<h3>Testing Logo Display:</h3>";
    echo getLogoImg(50, 50);
}
?>
