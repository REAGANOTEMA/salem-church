<?php
require_once 'config.php';

/**
 * Secure Session Helper
 * Prevents session warnings by checking status before starting
 */

function secure_session_start() {
    // Only set ini settings if session hasn't started
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        @ini_set('session.cookie_httponly', 1);
        @ini_set('session.cookie_secure', !defined('DEBUG_MODE') || !DEBUG_MODE);
        @ini_set('session.cookie_samesite', 'Strict');
        @ini_set('session.use_strict_mode', 1);
        
        // Start the session
        session_start();
    }
}

// Alternative function for backward compatibility
function safe_session_start() {
    return secure_session_start();
}
?>
