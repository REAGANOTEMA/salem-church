<?php
/**
 * Admin Directory Index - Salem Dominion Ministries
 * Works on both localhost and hosting platforms
 */

// Start session
session_start();

// Add headers to prevent caching issues on hosting
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Check if admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ../admin_dashboard.php');
    exit;
}

// Check if welcome.php exists
if (file_exists(__DIR__ . '/welcome.php')) {
    // Redirect to admin welcome page
    header('Location: welcome.php');
    exit;
} else {
    // Fallback to admin login if welcome.php doesn't exist
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    header('Location: ' . $protocol . '://' . $host . '/admin_login.php');
    exit;
}
?>
