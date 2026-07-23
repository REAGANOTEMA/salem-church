<?php
require_once 'config.php';
// ADMIN LOGOUT - Salem Dominion Ministries
// Secure logout for admin session
session_start();

// Destroy session
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header('Location: admin_login.php');
exit;
?>
