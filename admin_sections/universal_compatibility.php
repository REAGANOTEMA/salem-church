<?php
/**
 * Universal Compatibility Layer
 * Ensures admin dashboard works on all platforms and devices
 */

// Platform detection
function detectPlatform() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $platform = 'unknown';
    
    // Detect browser
    if (preg_match('/Chrome/i', $user_agent)) {
        $platform = 'chrome';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $platform = 'firefox';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $platform = 'safari';
    } elseif (preg_match('/Edge/i', $user_agent)) {
        $platform = 'edge';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $platform = 'opera';
    }
    
    return $platform;
}

// Device detection
function detectDevice() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device = 'desktop';
    
    // Mobile detection
    if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent)) {
        $device = 'mobile';
    }
    
    // Tablet detection
    elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
        $device = 'tablet';
    }
    
    return $device;
}

// OS detection
function detectOS() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $os = 'unknown';
    
    if (preg_match('/Windows/i', $user_agent)) {
        $os = 'windows';
    } elseif (preg_match('/Mac/i', $user_agent)) {
        $os = 'mac';
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $os = 'linux';
    } elseif (preg_match('/iOS/i', $user_agent)) {
        $os = 'ios';
    } elseif (preg_match('/Android/i', $user_agent)) {
        $os = 'android';
    }
    
    return $os;
}

// Connection method detection
function detectConnectionMethod() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    return [
        'protocol' => $protocol,
        'host' => $host,
        'full_url' => $protocol . '://' . $host,
        'is_local' => in_array($host, ['localhost', '127.0.0.1', '::1'])
    ];
}

// Performance optimization
function optimizeForDevice($device, $platform) {
    $optimizations = [];
    
    // Mobile optimizations
    if ($device === 'mobile') {
        $optimizations = [
            'reduce_animations' => true,
            'compress_images' => true,
            'lazy_load' => true,
            'touch_optimized' => true
        ];
    }
    
    // Browser-specific optimizations
    if ($platform === 'chrome') {
        $optimizations['chrome_features'] = true;
    } elseif ($platform === 'safari') {
        $optimizations['safari_optimized'] = true;
    }
    
    return $optimizations;
}

// Universal error handler
function universalErrorHandler($errno, $errstr, $errfile, $errline) {
    $platform = detectPlatform();
    $device = detectDevice();
    
    // Log error with context
    $error_context = [
        'timestamp' => date('Y-m-d H:i:s'),
        'platform' => $platform,
        'device' => $device,
        'error' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
    ];
    
    error_log(json_encode($error_context));
    
    // User-friendly error messages
    if ($device === 'mobile') {
        echo '<div class="alert alert-warning">';
        echo '<i class="fas fa-exclamation-triangle"></i> ';
        echo 'Mobile Error: Something went wrong. Please try again.';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">';
        echo '<i class="fas fa-times-circle"></i> ';
        echo 'Error: ' . htmlspecialchars($errstr);
        echo '</div>';
    }
}

// Universal success handler
function universalSuccessHandler($message, $context = []) {
    $device = detectDevice();
    
    if ($device === 'mobile') {
        echo '<div class="alert alert-success">';
        echo '<i class="fas fa-check-circle"></i> ';
        echo 'Success: ' . htmlspecialchars($message);
        echo '</div>';
    } else {
        echo '<div class="alert alert-success">';
        echo '<i class="fas fa-check-circle"></i> ';
        echo htmlspecialchars($message);
        echo '</div>';
    }
}

// Set universal error handler
set_error_handler('universalErrorHandler');

// Universal CSS classes
function getUniversalClasses($device, $platform) {
    $classes = ['admin-body'];
    
    if ($device === 'mobile') {
        $classes[] = 'mobile-device';
        $classes[] = 'touch-device';
    }
    
    if ($platform === 'chrome') {
        $classes[] = 'chrome-browser';
    } elseif ($platform === 'safari') {
        $classes[] = 'safari-browser';
    }
    
    return implode(' ', $classes);
}

// Universal JavaScript loader
function loadUniversalScripts($device, $platform) {
    $scripts = [];
    
    // Mobile-specific scripts
    if ($device === 'mobile') {
        $scripts[] = '<script src="assets/js/mobile-optimizations.js"></script>';
        $scripts[] = '<script src="assets/js/touch-handlers.js"></script>';
    }
    
    // Browser-specific scripts
    if ($platform === 'chrome') {
        $scripts[] = '<script src="assets/js/chrome-features.js"></script>';
    }
    
    return implode("\n", $scripts);
}

// Universal meta tags
function getUniversalMeta($device, $platform) {
    $meta = [];
    
    // Mobile meta tags
    if ($device === 'mobile') {
        $meta[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        $meta[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
        $meta[] = '<meta name="mobile-web-app-capable" content="yes">';
    }
    
    // Platform-specific meta
    if ($platform === 'chrome') {
        $meta[] = '<meta name="chrome-webstore-item" content="Salem Church Admin">';
    }
    
    return implode("\n", $meta);
}
?>
