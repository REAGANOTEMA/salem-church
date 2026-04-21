<?php
// Admin sections logo configuration
// This file provides logo configuration for admin sections

// Get base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host;

// Define logo paths for admin sections
define('ADMIN_LOGO_DIRECT', $base_url . '/public/logo-icon.jpeg');
define('ADMIN_LOGO_SERVE', $base_url . '/serve_logo.php');
define('ADMIN_LOGO_FALLBACK', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8A8A');

// Function to get admin logo URL with fallbacks
function getAdminLogoUrl() {
    // Try serve script first (most reliable)
    if (file_exists('../serve_logo.php')) {
        return ADMIN_LOGO_SERVE;
    }
    
    // Try direct path
    if (file_exists('../public/logo-icon.jpeg') && is_readable('../public/logo-icon.jpeg')) {
        return ADMIN_LOGO_DIRECT;
    }
    
    // Use base64 fallback (always works)
    return ADMIN_LOGO_FALLBACK;
}

// Function to generate admin logo img tag
function getAdminLogoImg($width = 30, $height = 30, $extra_style = '') {
    $url = getAdminLogoUrl();
    $style = "width: {$width}px; height: {$height}px;" . ($extra_style ? " {$extra_style}" : "");
    return "<img src='{$url}' alt='Salem Dominion Ministries' style='{$style}'>";
}
?>
