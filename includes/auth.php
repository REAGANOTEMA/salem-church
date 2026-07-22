<?php
/**
 * Salem Dominion Ministries - Authentication System
 * Secure admin authentication with session management
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

class Auth {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function adminLogin(string $username, string $password): array {
        $user = $this->db->fetch(
            "SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$username, $username]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account locked. Try again in {$minutes} minutes."];
        }

        if (!password_verify($password, $user['password'])) {
            $attempts = $user['login_attempts'] + 1;
            $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', time() + 1800) : null;
            $this->db->query(
                "UPDATE admin_users SET login_attempts = ?, locked_until = ? WHERE id = ?",
                [$attempts, $lockUntil, $user['id']]
            );
            $this->logLogin($user['id'], $username, 'failed', 'Invalid password');
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        $this->db->query(
            "UPDATE admin_users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?",
            [$user['id']]
        );

        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_login_time'] = time();

        $this->logLogin($user['id'], $username, 'success');
        logActivity($this->db->getPdo(), 'login', 'admin', $user['id'], 'Admin login successful');

        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    }

    public function adminLogout(): void {
        $userId = $_SESSION['admin_id'] ?? 0;
        if ($userId) {
            logActivity($this->db->getPdo(), 'logout', 'admin', $userId);
        }
        session_destroy();
    }

    public function isAdminLoggedIn(): bool {
        if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }
        $loginTime = $_SESSION['admin_login_time'] ?? 0;
        if (time() - $loginTime > ADMIN_SESSION_LIFETIME) {
            $this->adminLogout();
            return false;
        }
        $_SESSION['admin_login_time'] = time();
        return true;
    }

    public function requireAdmin(): void {
        if (!$this->isAdminLoggedIn()) {
            redirect(BASE_URL . '/admin/login.php');
        }
    }

    public function getAdmin(): ?array {
        if (!$this->isAdminLoggedIn()) return null;
        return [
            'id'       => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'name'     => $_SESSION['admin_name'],
            'email'    => $_SESSION['admin_email'],
            'role'     => $_SESSION['admin_role'],
        ];
    }

    public function getPermissions(string $role): array {
        $permissions = [
            'super_admin' => [
                'dashboard', 'news', 'events', 'sermons', 'gallery', 'videos',
                'live', 'donations', 'prayer_requests', 'contact_messages',
                'subscribers', 'users', 'roles', 'pages', 'menus', 'media',
                'announcements', 'settings', 'appearance', 'seo', 'backups',
                'logs', 'reports', 'profile', 'homepage',
            ],
            'admin' => [
                'dashboard', 'news', 'events', 'sermons', 'gallery', 'videos',
                'live', 'donations', 'prayer_requests', 'contact_messages',
                'subscribers', 'users', 'pages', 'menus', 'media',
                'announcements', 'settings', 'profile',
            ],
            'editor' => [
                'dashboard', 'news', 'events', 'sermons', 'gallery', 'videos',
                'announcements', 'media',
            ],
            'media_team' => [
                'dashboard', 'gallery', 'videos', 'sermons', 'media', 'live',
            ],
            'pastor' => [
                'dashboard', 'sermons', 'prayer_requests', 'events',
                'announcements', 'profile',
            ],
            'secretary' => [
                'dashboard', 'contact_messages', 'subscribers', 'events',
                'news', 'announcements',
            ],
            'finance' => [
                'dashboard', 'donations', 'reports',
            ],
            'volunteer' => [
                'dashboard', 'events',
            ],
        ];
        return $permissions[$role] ?? ['dashboard'];
    }

    public function hasPermission(string $permission): bool {
        $admin = $this->getAdmin();
        if (!$admin) return false;
        $perms = $this->getPermissions($admin['role']);
        return in_array($permission, $perms);
    }

    private function logLogin(int $adminId, string $username, string $status, string $reason = ''): void {
        try {
            $this->db->query(
                "INSERT INTO admin_login_logs (admin_id, username, ip_address, user_agent, status, failure_reason) VALUES (?, ?, ?, ?, ?, ?)",
                [$adminId, $username, $_SERVER['REMOTE_ADDR'] ?? '', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500), $status, $reason]
            );
        } catch (Exception $e) {
            error_log("Login log failed: " . $e->getMessage());
        }
    }

    public function changePassword(int $userId, string $oldPass, string $newPass): array {
        $user = $this->db->fetch("SELECT password FROM admin_users WHERE id = ?", [$userId]);
        if (!$user || !password_verify($oldPass, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        if (strlen($newPass) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters'];
        }
        $hash = password_hash($newPass, HASH_ALGO, ['cost' => HASH_COST]);
        $this->db->query("UPDATE admin_users SET password = ? WHERE id = ?", [$hash, $userId]);
        return ['success' => true, 'message' => 'Password changed successfully'];
    }

    public function createAdmin(array $data): int {
        $data['password'] = password_hash($data['password'], HASH_ALGO, ['cost' => HASH_COST]);
        return $this->db->insert('admin_users', $data);
    }
}

function auth(): Auth {
    static $auth = null;
    if ($auth === null) {
        $auth = new Auth();
    }
    return $auth;
}

function requireAdminAuth(): void {
    auth()->requireAdmin();
}

function isAdminLoggedIn(): bool {
    return auth()->isAdminLoggedIn();
}

function currentAdmin(): ?array {
    return auth()->getAdmin();
}

function hasPermission(string $perm): bool {
    return auth()->hasPermission($perm);
}
