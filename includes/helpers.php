<?php
/**
 * Salem Dominion Ministries - Security Helpers
 * CSRF, XSS, sanitization, and utility functions
 */

// CSRF Token Generation
function generateCSRFToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

function csrfToken(): string {
    return generateCSRFToken();
}

function verifyCSRFToken(): bool {
    $token = $_POST[CSRF_TOKEN_NAME]
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    if (empty($token) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $jsonData = json_decode($rawInput, true);
            if (is_array($jsonData) && isset($jsonData[CSRF_TOKEN_NAME])) {
                $token = $jsonData[CSRF_TOKEN_NAME];
            }
        }
    }

    if (empty($token) || empty($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function requireCSRF(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCSRFToken()) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid CSRF token']));
    }
}

// Input Sanitization
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeInput(string $input): string {
    return filter_var(trim($input), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
}

function cleanInput(string $input): string {
    return trim(strip_tags($input));
}

function safe_html(string $input): string {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Database sanitization helpers
function escapeString(string $input): string {
    return addslashes($input);
}

// Validation helpers
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUrl(string $url): bool {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function validatePhone(string $phone): bool {
    return preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $phone);
}

// Flash Messages
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function displayFlash(): void {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo sanitize($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

// Redirect helper
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function redirectWithFlash(string $url, string $type, string $message): void {
    setFlash($type, $message);
    redirect($url);
}

// JSON Response
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function jsonError(string $message, int $code = 400): void {
    jsonResponse(['success' => false, 'error' => $message], $code);
}

function jsonSuccess(array $data = [], string $message = 'Success'): void {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}

// File Upload Helpers
function uploadFile(array $file, string $subDir, array $allowedTypes = []): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }
    if (!empty($allowedTypes)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }
    }
    $uploadDir = UPLOADS_PATH . '/' . $subDir;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('file_', true) . '.' . strtolower($ext);
    $filepath = $uploadDir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . $subDir . '/' . $filename;
    }
    return null;
}

function deleteFile(string $filepath): bool {
    $fullPath = ROOT_PATH . '/' . ltrim($filepath, '/');
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

// Pagination
function paginate(string $table, $db, int $perPage = ITEMS_PER_PAGE, int $currentPage = 1, string $where = '', array $params = [], string $orderBy = 'id DESC'): array {
    if ($db instanceof Database) {
        $pdo = $db->getPdo();
    } elseif ($db instanceof PDO) {
        $pdo = $db;
    } else {
        $pdo = Database::getInstance()->getPdo();
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        return ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage, 'total_pages' => 1];
    }
    if (!preg_match('/^[a-zA-Z0-9_.,\s\(\)]+\s+(ASC|DESC)$/i', trim($orderBy))) {
        $orderBy = 'id DESC';
    }

    $countQuery = "SELECT COUNT(*) FROM {$table}";
    if (!empty($where)) {
        $countQuery .= " WHERE {$where}";
    }
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    $query = "SELECT * FROM {$table}";
    if (!empty($where)) {
        $query .= " WHERE {$where}";
    }
    $query .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return [
        'items'       => $items,
        'total'       => $total,
        'page'        => $currentPage,
        'per_page'    => $perPage,
        'total_pages' => $totalPages,
    ];
}

// Date/Time Helpers
function formatDate(string $date, string $format = 'M j, Y'): string {
    return date($format, strtotime($date));
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}

// Activity Logging
function logActivity($db, string $action, string $module = '', int $userId = 0, string $details = ''): void {
    try {
        // Activity logs are in the admin database
        $pdo = Database::getNamed('admin')->getPdo();

        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, module, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $userId ?: ($_SESSION['admin_id'] ?? 0),
            $action,
            $module,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]);
    } catch (Exception $e) {
        error_log("Activity log failed: " . $e->getMessage());
    }
}

// Thumbnail generation
function getYouTubeThumbnail(string $url): string {
    $videoId = '';
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $videoId = $matches[1];
    }
    if ($videoId) {
        return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
    }
    return LOGO_URL;
}

function extractYouTubeId(string $url): string {
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}

// String Helpers
function truncate(string $text, int $length = 100): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

// Settings Helpers
function getSetting(string $key, string $default = ''): string {
    try {
        $db = Database::getInstance();
        $row = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return $row ? (string)$row['setting_value'] : $default;
    } catch (Exception $e) {
        error_log("getSetting failed for key '{$key}': " . $e->getMessage());
        return $default;
    }
}

function setSetting(string $key, string $value): bool {
    try {
        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            $db->update('settings', ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')], 'setting_key = ?', [$key]);
        } else {
            $db->insert('settings', [
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    } catch (Exception $e) {
        error_log("setSetting failed for key '{$key}': " . $e->getMessage());
        return false;
    }
}
