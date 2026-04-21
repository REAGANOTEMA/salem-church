<?php
/**
 * Enhanced Database Connection - Universal Compatibility
 * Works on all devices, localhost, and hosting platforms
 */

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

// Enhanced database connection with universal compatibility
function getConnection() {
    $is_localhost = isLocalhost();
    
    if ($is_localhost) {
        // Localhost configurations - try multiple setups
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
            // Alternative host with password
            [
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => 'ReagaN23#',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
            // Alternative host without password
            [
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => '',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
            // WAMP/MAMP configurations
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'root',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ]
        ];
    } else {
        // Hosting configurations - multiple hosting providers
        $configs = [
            // Primary hosting credentials
            [
                'host' => 'localhost',
                'user' => 'salemdominionmin_db',
                'pass' => 'CtYeTnGktDxy9UvdtZJF',
                'name' => 'salemdominionmin_db',
                'port' => 3306
            ],
            // Alternative hosting configurations
            [
                'host' => 'localhost',
                'user' => 'salemdominionmin_db',
                'pass' => 'CtYeTnGktDxy9UvdtZJF',
                'name' => 'salemdominionmin_db',
                'port' => 3306
            ],
            // Common hosting patterns
            [
                'host' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'user' => 'salemdominionmin_db',
                'pass' => 'CtYeTnGktDxy9UvdtZJF',
                'name' => 'salemdominionmin_db',
                'port' => 3306
            ]
        ];
    }
    
    // Try each configuration with enhanced error handling
    foreach ($configs as $config) {
        try {
            // Step 1: Test basic MySQL connection
            $test_conn = new mysqli($config['host'], $config['user'], $config['pass'], '', $config['port']);
            
            if ($test_conn->connect_error) {
                error_log("Connection failed with config: " . json_encode($config) . " - " . $test_conn->connect_error);
                continue;
            }
            
            // Step 2: Create database if needed
            $create_db = $test_conn->query("CREATE DATABASE IF NOT EXISTS `" . $config['name'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $test_conn->close();
            
            if (!$create_db) {
                continue;
            }
            
            // Step 3: Connect to the specific database
            $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name'], $config['port']);
            
            if ($conn->connect_error) {
                continue;
            }
            
            // Step 4: Set charset and verify connection
            $conn->set_charset('utf8mb4');
            
            // Step 5: Test database functionality
            $test_query = $conn->query("SELECT 1");
            if (!$test_query) {
                $conn->close();
                continue;
            }
            
            // Step 6: Create essential tables if needed
            createEssentialTables($conn);
            
            error_log("Database connected successfully with config: " . $config['host'] . "/" . $config['name']);
            return $conn;
            
        } catch (Exception $e) {
            error_log("Database connection exception: " . $e->getMessage());
            continue;
        }
    }
    
    // Log final failure
    error_log("All database connection configurations failed");
    return null;
}

// Create essential tables for universal compatibility
function createEssentialTables($conn) {
    // Admin users table
    $conn->query("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
        is_active BOOLEAN DEFAULT 1,
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Create default admin user if not exists
    $admin_check = $conn->query("SELECT id FROM admin_users WHERE username = 'admin' LIMIT 1");
    if ($admin_check && $admin_check->num_rows == 0) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin_users (username, password_hash, email, full_name, role) 
                      VALUES ('admin', '$password_hash', 'admin@salemchurch.com', 'Administrator', 'super_admin')");
    }
    
    // Users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NULL,
        profile_image VARCHAR(255) NULL,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Messages table
    $conn->query("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NULL,
        recipient_id INT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        message_type ENUM('user_to_admin', 'admin_to_user') DEFAULT 'user_to_admin',
        priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        status ENUM('unread', 'read', 'replied', 'archived') DEFAULT 'unread',
        parent_message_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (recipient_id) REFERENCES admin_users(id) ON DELETE SET NULL,
        FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    return true;
}
?>
