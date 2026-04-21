<?php
// Logo helper functions for robust logo loading

function getLogoUrl() {
    // Try multiple approaches to get a working logo URL
    
    // 1. Try the direct path first
    $direct_path = 'public/logo-icon.jpeg';
    if (file_exists($direct_path) && is_readable($direct_path)) {
        return BASE_URL . '/' . $direct_path;
    }
    
    // 2. Try the serve_logo.php script
    $serve_script = BASE_URL . '/serve_logo.php';
    return $serve_script;
}

function getLogoTag($width = 30, $height = 30, $class = '') {
    $url = getLogoUrl();
    $class_attr = $class ? " class='$class'" : '';
    return "<img src='$url' alt='Salem Dominion Ministries' style='width: {$width}px; height: {$height}px;'$class_attr>";
}

function testLogoLoading() {
    $results = [];
    
    // Test direct path
    $direct_path = BASE_URL . '/public/logo-icon.jpeg';
    $results['direct'] = [
        'url' => $direct_path,
        'exists' => file_exists('public/logo-icon.jpeg'),
        'readable' => is_readable('public/logo-icon.jpeg')
    ];
    
    // Test serve script
    $serve_url = BASE_URL . '/serve_logo.php';
    $results['serve_script'] = [
        'url' => $serve_url,
        'exists' => file_exists('serve_logo.php'),
        'readable' => is_readable('serve_logo.php')
    ];
    
    return $results;
}
?>
