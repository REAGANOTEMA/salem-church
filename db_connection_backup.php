<?php
/**
 * Clean Database Connection - Works on Both Localhost and Hosting
 * Simple, reliable connection with automatic environment detection
 */

// Detect environment - Simplified for reliable hosting detection
function isLocalhost() {
    $http_host = $_SERVER['HTTP_HOST'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // Clear localhost indicators
    $localhost_patterns = ['localhost', '127.0.0.1', '::1', '192.168.', '10.0.', '172.16.'];
    
    foreach ($localhost_patterns as $pattern) {
        if (strpos($http_host, $pattern) !== false || strpos($server_name, $pattern) !== false) {
            return true;
        }
    }
    
    // Default to hosting for production
    return false;
}

// Main database connection function
function getConnection() {
    $is_localhost = isLocalhost();
    
    if ($is_localhost) {
        // Try different localhost configurations
        $configs = [
            [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'ReagaN23#',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
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
                'pass' => 'ReagaN23#',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ],
            [
                'host' => '127.0.0.1',
                'user' => 'root',
                'pass' => '',
                'name' => 'salem_dominion_ministries',
                'port' => 3306
            ]
        ];
    } else {
        // Hosting configuration
        $configs = [
            [
                'host' => 'localhost',
                'user' => 'salemdominionmin_db',
                'pass' => 'CtYeTnGktDxy9UvdtZJF',
                'name' => 'salemdominionmin_db',
                'port' => 3306
            ]
        ];
    }
    
    // Try each configuration
    foreach ($configs as $config) {
        try {
            // First try to connect to MySQL server
            $temp_conn = new mysqli($config['host'], $config['user'], $config['pass'], '', $config['port']);
            
            if ($temp_conn->connect_error) {
                continue; // Try next config
            }
            
            // Create database if it doesn't exist
            $temp_conn->query("CREATE DATABASE IF NOT EXISTS `" . $config['name'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $temp_conn->close();
            
            // Now try to connect to the specific database
            $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name'], $config['port']);
            
            if (!$conn->connect_error) {
                $conn->set_charset('utf8mb4');
                
                // Create essential tables
                createTables($conn);
                createAdminUser($conn);
                
                return $conn;
            }
        } catch (Exception $e) {
            continue; // Try next config
        }
    }
    
    return null; // All configurations failed
}

// Create essential tables
function createTables($conn) {
    // Admin users table
    $conn->query("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        is_active TINYINT(1) DEFAULT 1,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Regular users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(50),
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        is_active TINYINT(1) DEFAULT 1,
        profile_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Add profile_image column to existing users table if it doesn't exist
    $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL AFTER phone");
    
    // Donations table
    $conn->query("CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        donor_name VARCHAR(255) NOT NULL,
        donor_email VARCHAR(255),
        donor_phone VARCHAR(50),
        amount DECIMAL(10,2) NOT NULL,
        donation_type VARCHAR(50) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Messages table
    $conn->query("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        recipient_id INT,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        message_type ENUM('user_to_admin', 'admin_to_user', 'user_to_user') DEFAULT 'user_to_admin',
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        parent_message_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (recipient_id) REFERENCES admin_users(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Message attachments table
    $conn->query("CREATE TABLE IF NOT EXISTS message_attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Create default admin user
function createAdminUser($conn) {
    $check = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'MusasiziFaty'");
    if ($check && $check->fetch_assoc()['count'] == 0) {
        $password_hash = password_hash('Musasizi123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, email, full_name, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $username = 'MusasiziFaty';
            $email = 'pastor@salem-dominion-ministries.com';
            $full_name = 'Pastor Faty Musasizi';
            $role = 'admin';
            $stmt->bind_param("sssss", $username, $password_hash, $email, $full_name, $role);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Helper functions
function testConnection() {
    return getConnection() !== null;
}

function getDatabaseStatus() {
    $conn = getConnection();
    if ($conn) {
        // Check if admin_users table exists and has records
        $admin_users_exist = false;
        $tables_exist = false;
        
        try {
            // Check if admin_users table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
            if ($table_check && $table_check->num_rows > 0) {
                $tables_exist = true;
                
                // Check if admin_users table has records
                $count_check = $conn->query("SELECT COUNT(*) as count FROM admin_users");
                if ($count_check && $count_check->fetch_assoc()['count'] > 0) {
                    $admin_users_exist = true;
                }
            }
        } catch (Exception $e) {
            error_log("Database status check error: " . $e->getMessage());
        }
        
        return [
            'connected' => true,
            'environment' => isLocalhost() ? 'localhost' : 'hosting',
            'database_exists' => true,
            'tables_exist' => $tables_exist,
            'admin_users_exist' => $admin_users_exist
        ];
    }
    return [
        'connected' => false,
        'environment' => isLocalhost() ? 'localhost' : 'hosting',
        'database_exists' => false,
        'tables_exist' => false,
        'admin_users_exist' => false,
        'error' => 'Database connection failed'
    ];
}
?>
