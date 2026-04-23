<?php
/**
 * Universal Database Connection - Cross-Platform Compatibility
 * Works on all hosting platforms: Shared, VPS, Cloud, Localhost
 */

// Include configuration
require_once 'config.php';
require_once 'hosting_config.php';

// Universal environment detection
function isLocalhost() {
    $http_host = $_SERVER['HTTP_HOST'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    $server_addr = $_SERVER['SERVER_ADDR'] ?? '';
    
    // Comprehensive localhost detection
    $localhost_patterns = [
        'localhost',
        '127.0.0.1',
        '::1',
        '192.168.',
        '10.0.',
        '172.16.',
        '0.0.0.0'
    ];
    
    foreach ($localhost_patterns as $pattern) {
        if (strpos($http_host, $pattern) !== false || 
            strpos($server_name, $pattern) !== false ||
            strpos($server_addr, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

// Get hosting platform type
function getHostingPlatform() {
    $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
    $document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    
    // Detect hosting platform
    if (isLocalhost()) {
        return 'localhost';
    } elseif (strpos($document_root, 'public_html') !== false) {
        return 'shared_hosting';
    } elseif (strpos($server_software, 'Apache') !== false) {
        return 'apache_server';
    } elseif (strpos($server_software, 'nginx') !== false) {
        return 'nginx_server';
    } else {
        return 'cloud_hosting';
    }
}

// Universal database connection with multi-platform support
function getConnection() {
    $platform = getHostingPlatform();
    $is_localhost = isLocalhost();
    
    if ($is_localhost) {
        // Localhost configurations
        $configs = [
            // Standard XAMPP with password
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'ReagaN23#',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
            // Fresh XAMPP without password
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
            // MAMP configuration
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'root',
                'name' => 'salem_dominion_ministries',
                'port' => 8889
            ],
            // WAMP configuration
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ]
        ];
    } else {
        // Production hosting configurations using config.php
        $configs = [
            // Use configuration from config.php
            [
                'host' => DB_HOST,
                'user' => DB_USER,
                'pass' => DB_PASS,
                'name' => DB_NAME,
                'port' => DB_PORT
            ],
            // Alternative: Try with different user
            [
                'host' => DB_HOST,
                'user' => 'salem_admin',
                'pass' => DB_PASS,
                'name' => DB_NAME,
                'port' => DB_PORT
            ],
            // Fallback to environment variables
            [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'user' => $_ENV['DB_USER'] ?? 'salem_admin',
                'pass' => $_ENV['DB_PASS'] ?? '',
                'name' => $_ENV['DB_NAME'] ?? 'salem_dominion_ministries',
                'port' => $_ENV['DB_PORT'] ?? 3306
            ],
            // Alternative localhost for shared hosting
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
            // Try 127.0.0.1 instead of localhost
            [
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => '',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ]
        ];
    }
    
    return $configs;
}

// Emergency fallback configurations
function getEmergencyDatabaseConfigs() {
    return [
        [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '',
            'name' => 'salem_dominion_ministries',
            'port' => 3306
        ],
        [
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => '',
            'name' => 'salem_dominion_ministries',
            'port' => 3306
        ],
        [
            'host' => 'localhost',
            'user' => 'salem_admin',
            'pass' => 'password',
            'name' => 'salem_dominion_ministries',
            'port' => 3306
        ],
        [
            'host' => 'localhost',
            'user' => 'salem_admin',
            'pass' => '123456',
            'name' => 'salem_dominion_ministries',
            'port' => 3306
        ]
    ];
}

// Create actual database connection by testing all configurations
function createDatabaseConnection() {
    // First try hosting platform configuration from hosting_config.php
    try {
        if (function_exists('getHostingDatabaseConfig')) {
            $hosting_config = getHostingDatabaseConfig();
            $hosting_conn = new mysqli($hosting_config['host'], $hosting_config['user'], $hosting_config['pass'], $hosting_config['name'], $hosting_config['port']);
            if (!$hosting_conn->connect_error) {
                // Test database access
                $test_result = $hosting_conn->query("SELECT 1");
                if ($test_result) {
                    return $hosting_conn; // Hosting connection successful
                }
            }
        }
    } catch (Exception $e) {
        // Continue to fallback configurations
    }
    
    $configs = getConnection();
    
    // Ensure we have an array of configurations
    if (!is_array($configs)) {
        return null;
    }
    
    // Try each configuration until one works
    foreach ($configs as $config) {
        if (is_array($config)) {
            $connection = testDatabaseConnection($config);
            if ($connection !== null) {
                return $connection; // Return the working connection
            }
        }
    }
    
    // If all main configs fail, try emergency configs
    $emergency_configs = getEmergencyDatabaseConfigs();
    if (is_array($emergency_configs)) {
        foreach ($emergency_configs as $config) {
            if (is_array($config)) {
                $connection = testDatabaseConnection($config);
                if ($connection !== null) {
                    return $connection;
                }
            }
        }
    }
    
    // Last resort: try simple connection
    $simple_conn = getSimpleConnection();
    if ($simple_conn !== null) {
        return $simple_conn;
    }
    
    return null; // No working connection found
}

// Check for hosting platform configuration
function getHostingDatabaseConnection() {
    if (file_exists('db_hosting_config.php')) {
        require_once 'db_hosting_config.php';
        if (function_exists('getHostingDatabaseConnection')) {
            return getHostingDatabaseConnection();
        }
    }
    return null;
}

// Simple direct connection fallback
function getSimpleConnection() {
    try {
        // First try hosting platform credentials
        $conn = new mysqli('localhost', 'salemdominionmin_db', 'CtYeTnGktDxy9UvdtZJF', 'salemdominionmin_db', 3306);
        if (!$conn->connect_error) {
            return $conn;
        }
        
        // Try localhost development configurations
        $conn = new mysqli('localhost', 'root', '', 'salem_dominion_ministries', 3306);
        if ($conn->connect_error) {
            // Try with password
            $conn = new mysqli('localhost', 'root', 'ReagaN23#', 'salem_dominion_ministries', 3306);
            if ($conn->connect_error) {
                return null;
            }
        }
        return $conn;
    } catch (Exception $e) {
        return null;
    }
}

// Test individual database connection
function testDatabaseConnection($config) {
    try {
        $conn = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['name'],
            $config['port']
        );
        
        if ($conn->connect_error) {
            return null;
        }
        
        // Test actual database access with multiple queries
        $queries = [
            "SELECT 1",
            "SHOW TABLES",
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '" . $config['name'] . "'"
        ];
        
        foreach ($queries as $query) {
            $result = $conn->query($query);
            if (!$result) {
                $conn->close();
                return null;
            }
        }
        
        return $conn; // Connection successful and database accessible
    } catch (Exception $e) {
        return null;
    }
}

// Universal file upload configuration
function getUploadConfig() {
    $platform = getHostingPlatform();
    
    // Base configuration
    $config = [
        'max_file_size' => 50 * 1024 * 1024, // 50MB
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'webm', 'ogg', 'mov'],
            'audio' => ['mp3', 'wav', 'ogg', 'm4a']
        ],
        'upload_path' => 'uploads/',
        'create_dirs' => true
    ];
    
    // Platform-specific adjustments
    switch ($platform) {
        case 'localhost':
            $config['upload_path'] = 'uploads/';
            break;
            
        case 'shared_hosting':
            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            $config['max_file_size'] = 20 * 1024 * 1024; // 20MB for shared
            break;
            
        case 'apache_server':
        case 'nginx_server':
            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            break;
            
        case 'cloud_hosting':
            $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            $config['max_file_size'] = 100 * 1024 * 1024; // 100MB for cloud
            break;
    }
    
    return $config;
}

// Create upload directories if they don't exist
function ensureUploadDirectories() {
    $config = getUploadConfig();
    $base_path = $config['upload_path'];
    
    $directories = [
        $base_path,
        $base_path . 'sermons/',
        $base_path . 'sermons/video/',
        $base_path . 'sermons/audio/',
        $base_path . 'gallery/',
        $base_path . 'gallery/image/',
        $base_path . 'gallery/video/',
        $base_path . 'gallery/audio/',
        $base_path . 'news/',
        $base_path . 'temp/'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            
            // Create .htaccess for security
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "AddType video/mp4 .mp4\n";
            $htaccess_content .= "AddType video/webm .webm\n";
            $htaccess_content .= "AddType audio/mpeg .mp3\n";
            $htaccess_content .= "AddType audio/wav .wav\n";
            
            file_put_contents($dir . '.htaccess', $htaccess_content);
        }
    }
}

// Universal error handler
function handleDatabaseError($error) {
    $platform = getHostingPlatform();
    
    error_log("Database Error on $platform: " . $error);
    
    // Return user-friendly message based on platform
    switch ($platform) {
        case 'localhost':
            return "Database connection failed. Please check XAMPP/WAMP/MAMP is running.";
        case 'shared_hosting':
            return "Database connection failed. Please contact hosting support or check database credentials.";
        case 'cloud_hosting':
            return "Database connection failed. Please check cloud database configuration.";
        default:
            return "Database connection failed. Please check server configuration.";
    }
}

// Initialize upload directories on file load
ensureUploadDirectories();
?>
