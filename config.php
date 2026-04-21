<?php
/**
 * Database Configuration for Hosting Platforms
 * Update these values for your hosting environment
 */

// Dynamic base URL detection for universal compatibility
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . '://' . $host;

// Define constants for universal usage
define('BASE_URL', $base_url);
define('CHURCH_LOGO', 'public/logo-icon.jpeg');
define('LOGO_PATH', BASE_URL . '/' . CHURCH_LOGO);

// Database Configuration - Multiple fallbacks for hosting platforms
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'salem_dominion_ministries');
define('DB_PORT', 3306);

// Alternative configurations for different hosting platforms
// Uncomment the appropriate configuration for your hosting:

// cPanel/Shared Hosting (most common)
// define('DB_HOST', 'localhost');
// define('DB_USER', 'salem_admin');
// define('DB_PASS', '');
// define('DB_NAME', 'salem_dominion_ministries');

// Plesk Hosting
// define('DB_HOST', 'localhost');
// define('DB_USER', 'salem_admin');
// define('DB_PASS', 'your_password_here');
// define('DB_NAME', 'salem_dominion_ministries');

// VPS/Dedicated Server
// define('DB_HOST', 'localhost');
// define('DB_USER', 'salem_admin');
// define('DB_PASS', 'your_password_here');
// define('DB_NAME', 'salem_dominion_ministries');

// Cloud Hosting (AWS, DigitalOcean, etc.)
// define('DB_HOST', 'your_database_host');
// define('DB_USER', 'salem_admin');
// define('DB_PASS', 'your_password_here');
// define('DB_NAME', 'salem_dominion_ministries');

// Alternative configurations (uncomment if needed)
// define('DB_HOST', '127.0.0.1');
// define('DB_USER', 'salem_admin');
// define('DB_PASS', 'your_database_password');
// define('DB_NAME', 'salem_dominion_ministries');

// Environment variables fallback (recommended for production)
if (!defined('DB_HOST') && isset($_ENV['DB_HOST'])) {
    define('DB_HOST_ENV', $_ENV['DB_HOST']);
}
if (!defined('DB_USER') && isset($_ENV['DB_USER'])) {
    define('DB_USER_ENV', $_ENV['DB_USER']);
}
if (!defined('DB_PASS') && isset($_ENV['DB_PASS'])) {
    define('DB_PASS_ENV', $_ENV['DB_PASS']);
}
if (!defined('DB_NAME') && isset($_ENV['DB_NAME'])) {
    define('DB_NAME_ENV', $_ENV['DB_NAME']);
}

// Church Branding
define('CHURCH_NAME', 'Salem Dominion Ministries');
define('CHURCH_LOGO', 'public/logo-icon.jpeg');
define('CHURCH_PASTOR', 'Pastor Faty Musasizi');

// Dynamic website URL for different environments
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('CHURCH_WEBSITE', $protocol . '://' . $host);
define('CHURCH_DESCRIPTION', 'Salem Dominion Ministries - A vibrant church community led by Pastor Faty Musasizi. Join us for worship, fellowship, and spiritual growth.');

// Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Email Configuration (for contact forms)
define('ADMIN_EMAIL', 'admin@salem-church.org');
define('CHURCH_EMAIL', 'info@salem-church.org');

// Security
define('HASH_SALT', 'salem_dominion_2024');
?>
