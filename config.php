<?php
/**
 * Salem Dominion Ministries - Application Configuration
 * Production-ready configuration for all environments
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load .env file into $_ENV-style array
function _sdm_load_env(string $path): array {
    $vars = [];
    if (!file_exists($path)) {
        return $vars;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), '"\'');
        $vars[$key] = $value;
    }
    return $vars;
}

$_env = _sdm_load_env(__DIR__ . '/.env');
function _sdm_env(string $key, string $default = ''): string {
    global $_env;
    return $_env[$key] ?? getenv($key) ?: $default;
}

// Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host;
$siteUrl = $baseUrl;

define('BASE_URL', $baseUrl);
define('SITE_URL', $siteUrl);

// Paths
define('ROOT_PATH', __DIR__);
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// ============================================================
// Database Constants - Website Database
// ============================================================
define('DB_HOST', _sdm_env('DB_HOST', 'localhost'));
define('DB_USER', _sdm_env('DB_USER', 'root'));
define('DB_PASS', _sdm_env('DB_PASSWORD', _sdm_env('DB_PASS', '')));
define('DB_NAME', _sdm_env('DB_NAME', 'salemdominionmin_website'));
define('DB_PORT', _sdm_env('DB_PORT', '3306'));
define('DB_CHARSET', _sdm_env('DB_CHARSET', 'utf8mb4'));

// ============================================================
// Database Constants - Admin Database
// ============================================================
define('ADMIN_DB_HOST', _sdm_env('ADMIN_DB_HOST', DB_HOST));
define('ADMIN_DB_USER', _sdm_env('ADMIN_DB_USER', DB_USER));
define('ADMIN_DB_PASS', _sdm_env('ADMIN_DB_PASSWORD', DB_PASS));
define('ADMIN_DB_NAME', _sdm_env('ADMIN_DB_NAME', 'salemdominionmin_admin'));

// ============================================================
// Database Constants - Members Database
// ============================================================
define('MEMBERS_DB_HOST', _sdm_env('MEMBERS_DB_HOST', DB_HOST));
define('MEMBERS_DB_USER', _sdm_env('MEMBERS_DB_USER', DB_USER));
define('MEMBERS_DB_PASS', _sdm_env('MEMBERS_DB_PASSWORD', DB_PASS));
define('MEMBERS_DB_NAME', _sdm_env('MEMBERS_DB_NAME', 'salemdominionmin_members'));

// Church Info
define('CHURCH_NAME', 'Salem Dominion Ministries');
define('CHURCH_PASTOR', 'Pastor Faty Musasizi');
define('CHURCH_PHONE', '+256 753 244 480');
define('CHURCH_EMAIL', 'info@salem-dominion-ministries.com');
define('CHURCH_ADDRESS', 'Nampirika, Iganga District, Uganda');
define('CHURCH_WEBSITE', 'www.salemdominionministries.com');

// Logo
define('LOGO_PATH', PUBLIC_PATH . '/logo-icon.jpeg');
define('LOGO_URL', SITE_URL . '/public/logo-icon.jpeg');
define('LOGO_FALLBACK', 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect fill="%23333" width="100" height="100" rx="50"/><text x="50" y="60" text-anchor="middle" fill="white" font-size="40">S</text></svg>');

// Upload Config
define('MAX_FILE_SIZE', 50 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/webm', 'video/ogg']);
define('ALLOWED_AUDIO_TYPES', ['audio/mpeg', 'audio/wav', 'audio/ogg']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Admin emails
define('ADMIN_EMAIL', 'admin@salem-dominion-ministries.com');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);

// Session Config
define('SESSION_LIFETIME', 3600);
define('ADMIN_SESSION_LIFETIME', 7200);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Social Media
define('YOUTUBE_URL', 'https://youtube.com/@musasizifaty');
define('TIKTOK_URL', 'https://www.tiktok.com/@salem1dominionchurch');
define('FACEBOOK_URL', 'https://www.facebook.com/share/1CoCEmvnBB/');
define('WHATSAPP_URL', 'https://wa.me/256753244480');

// Payment Gateways (set keys in .env or here)
define('STRIPE_SECRET_KEY', _sdm_env('STRIPE_SECRET_KEY', ''));
define('STRIPE_PUBLIC_KEY', _sdm_env('STRIPE_PUBLIC_KEY', ''));
define('FLUTTERWAVE_SECRET_KEY', _sdm_env('FLUTTERWAVE_SECRET_KEY', ''));
define('FLUTTERWAVE_PUBLIC_KEY', _sdm_env('FLUTTERWAVE_PUBLIC_KEY', ''));
define('PAYPAL_CLIENT_ID', _sdm_env('PAYPAL_CLIENT_ID', ''));
define('PAYPAL_CLIENT_SECRET', _sdm_env('PAYPAL_CLIENT_SECRET', ''));
define('PAYPAL_MODE', 'sandbox');

// App Environment
define('APP_ENV', _sdm_env('APP_ENV', 'development'));
define('APP_DEBUG', APP_ENV !== 'production');

// Timezone
date_default_timezone_set('Africa/Kampala');

// Error Reporting - disable display in production
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
ini_set('log_errors', '1');
ini_set('error_log', UPLOADS_PATH . '/error.log');

// Auto-create upload directories
$dirs = [
    UPLOADS_PATH,
    UPLOADS_PATH . '/sermons/video',
    UPLOADS_PATH . '/sermons/audio',
    UPLOADS_PATH . '/gallery/image',
    UPLOADS_PATH . '/gallery/video',
    UPLOADS_PATH . '/gallery/audio',
    UPLOADS_PATH . '/news',
    UPLOADS_PATH . '/avatars',
    UPLOADS_PATH . '/temp',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
