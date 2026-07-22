<?php
/**
 * Salem Dominion Ministries - User Logout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

$userId = $_SESSION['user_id'] ?? 0;
if ($userId) {
    try {
        logActivity(Database::getInstance(), 'logout', 'auth', $userId, 'User logged out');
    } catch (Exception $e) {
        error_log("Logout activity log failed: " . $e->getMessage());
    }
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header('Location: index.php');
exit;
