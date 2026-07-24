<?php
require_once __DIR__ . '/config.php';

$userId = $_SESSION['admin_id'] ?? 0;
if ($userId) {
    try {
        require_once __DIR__ . '/includes/database.php';
        require_once __DIR__ . '/includes/helpers.php';
        logActivity(Database::getNamed('admin')->getPdo(), 'logout', 'admin', $userId);
    } catch (Exception $e) {
        error_log("Admin logout activity log failed: " . $e->getMessage());
    }
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header('Location: admin/login.php');
exit;
?>
