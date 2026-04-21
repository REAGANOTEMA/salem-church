<?php
/**
 * Final Database Connection - Perfect for Both Localhost and Hosting
 * Uses exact hosting credentials provided by user
 */

// Prevent multiple inclusions
if (!defined('DB_CONNECTION_FINAL_LOADED')) {
    define('DB_CONNECTION_FINAL_LOADED', true);

    /**
     * Final Database Connection Class
     * Perfect for localhost AND hosting with exact credentials
     */
    class FinalDatabaseConnection {
        private static $instance = null;
        private $connection = null;
        private $environment = null;
        
        private function __construct() {
            $this->detectEnvironment();
        }
        
        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        
        /**
         * Detect if we're on localhost or hosting
         */
        private function detectEnvironment() {
            $server_name = $_SERVER['SERVER_NAME'] ?? '';
            $http_host = $_SERVER['HTTP_HOST'] ?? '';
            $server_addr = $_SERVER['SERVER_ADDR'] ?? '';
            
            // Check for localhost indicators
            $localhost_indicators = [
                'localhost',
                '127.0.0.1',
                '::1',
                '192.168.',
                '10.0.',
                '172.16.',
                '.local',
                '.dev',
                '.test'
            ];
            
            foreach ($localhost_indicators as $indicator) {
                if (strpos($server_name, $indicator) === 0 || 
                    strpos($http_host, $indicator) === 0 ||
                    strpos($server_addr, $indicator) === 0) {
                    $this->environment = 'localhost';
                    return;
                }
            }
            
            $this->environment = 'hosting';
        }
        
        /**
         * Get database connection with exact credentials
         */
        public function getConnection() {
            if ($this->connection !== null && $this->connection->ping()) {
                return $this->connection;
            }
            
            try {
                if ($this->environment === 'localhost') {
                    // Localhost configuration
                    $this->connection = new mysqli(
                        'localhost',      // Host
                        'root',           // Username
                        'ReagaN23#',      // Password
                        'salem_dominion_ministries', // Database
                        3306              // Port
                    );
                } else {
                    // Hosting configuration - EXACT CREDENTIALS PROVIDED
                    $this->connection = new mysqli(
                        'localhost',                    // Host
                        'salemdominionmin_db',         // Username
                        'EtacdN8wXLmzr6vA2zaA',        // Password
                        'salemdominionmin_db',         // Database
                        3306                           // Port
                    );
                }
                
                if ($this->connection->connect_error) {
                    // Try to create database if it doesn't exist
                    if (strpos($this->connection->connect_error, 'Unknown database') !== false) {
                        return $this->createDatabaseAndConnect();
                    }
                    
                    error_log("Database connection failed: " . $this->connection->connect_error);
                    return null;
                }
                
                // Set charset
                $this->connection->set_charset('utf8mb4');
                
                // Create tables if needed
                $this->ensureTablesExist();
                
                return $this->connection;
                
            } catch (Exception $e) {
                error_log("Database connection exception: " . $e->getMessage());
                return null;
            }
        }
        
        /**
         * Create database and connect
         */
        private function createDatabaseAndConnect() {
            try {
                if ($this->environment === 'localhost') {
                    // Connect without database name for localhost
                    $temp_connection = new mysqli(
                        'localhost',
                        'root',
                        'ReagaN23#',
                        '',
                        3306
                    );
                    
                    $database_name = 'salem_dominion_ministries';
                } else {
                    // Connect without database name for hosting
                    $temp_connection = new mysqli(
                        'localhost',
                        'salemdominionmin_db',
                        'EtacdN8wXLmzr6vA2zaA',
                        '',
                        3306
                    );
                    
                    $database_name = 'salemdominionmin_db';
                }
                
                if ($temp_connection->connect_error) {
                    error_log("Temp connection failed: " . $temp_connection->connect_error);
                    return null;
                }
                
                // Create database
                $create_query = "CREATE DATABASE IF NOT EXISTS `" . $database_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                if (!$temp_connection->query($create_query)) {
                    error_log("Failed to create database: " . $temp_connection->error);
                    $temp_connection->close();
                    return null;
                }
                
                // Close temp connection
                $temp_connection->close();
                
                // Try to connect again with database name
                if ($this->environment === 'localhost') {
                    $this->connection = new mysqli(
                        'localhost',
                        'root',
                        'ReagaN23#',
                        'salem_dominion_ministries',
                        3306
                    );
                } else {
                    $this->connection = new mysqli(
                        'localhost',
                        'salemdominionmin_db',
                        'EtacdN8wXLmzr6vA2zaA',
                        'salemdominionmin_db',
                        3306
                    );
                }
                
                if ($this->connection->connect_error) {
                    error_log("Re-connection failed: " . $this->connection->connect_error);
                    return null;
                }
                
                $this->connection->set_charset('utf8mb4');
                $this->ensureTablesExist();
                
                return $this->connection;
                
            } catch (Exception $e) {
                error_log("Database creation exception: " . $e->getMessage());
                return null;
            }
        }
        
        /**
         * Ensure necessary tables exist
         */
        private function ensureTablesExist() {
            if (!$this->connection) return;
            
            // Create donations table
            $create_donations = "CREATE TABLE IF NOT EXISTS donations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                donor_name VARCHAR(255) NOT NULL,
                donor_email VARCHAR(255),
                donor_phone VARCHAR(50),
                amount DECIMAL(10,2) NOT NULL,
                donation_type VARCHAR(50) NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->connection->query($create_donations);
            
            // Create admin_users table
            $create_admin = "CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->connection->query($create_admin);
            
            // Create users table
            $create_users = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                phone VARCHAR(50),
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('user', 'admin') DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->connection->query($create_users);
            
            // Create default admin if not exists
            $check_admin = "SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'";
            $result = $this->connection->query($check_admin);
            if ($result) {
                $count = $result->fetch_assoc()['count'];
                if ($count == 0) {
                    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                    $insert_admin = "INSERT INTO admin_users (username, password_hash, email, full_name) VALUES ('admin', ?, 'admin@salem-dominion-ministries.com', 'Administrator')";
                    $stmt = $this->connection->prepare($insert_admin);
                    if ($stmt) {
                        $stmt->bind_param('s', $password_hash);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }
        
        /**
         * Test database connection
         */
        public function testConnection() {
            $conn = $this->getConnection();
            return $conn !== null;
        }
        
        /**
         * Get database status
         */
        public function getDatabaseStatus() {
            $status = [
                'environment' => $this->environment,
                'mysql_connected' => false,
                'database_exists' => false,
                'tables_exist' => false,
                'admin_users_exist' => false,
                'error' => null
            ];
            
            try {
                $conn = $this->getConnection();
                if ($conn) {
                    $status['mysql_connected'] = true;
                    $status['database_exists'] = true;
                    
                    // Check tables
                    $tables_check = $conn->query("SHOW TABLES");
                    if ($tables_check && $tables_check->num_rows > 0) {
                        $status['tables_exist'] = true;
                        
                        // Check admin_users
                        $admin_check = $conn->query("SELECT COUNT(*) as count FROM admin_users");
                        if ($admin_check) {
                            $admin_count = $admin_check->fetch_assoc()['count'];
                            $status['admin_users_exist'] = $admin_count > 0;
                        }
                    }
                }
            } catch (Exception $e) {
                $status['error'] = $e->getMessage();
            }
            
            return $status;
        }
        
        /**
         * Get current environment
         */
        public function getEnvironment() {
            return $this->environment;
        }
        
        /**
         * Get current configuration
         */
        public function getConfig() {
            if ($this->environment === 'localhost') {
                return [
                    'host' => 'localhost',
                    'user' => 'root',
                    'pass' => 'ReagaN23#',
                    'name' => 'salem_dominion_ministries',
                    'port' => 3306,
                    'charset' => 'utf8mb4'
                ];
            } else {
                return [
                    'host' => 'localhost',
                    'user' => 'salemdominionmin_db',
                    'pass' => 'EtacdN8wXLmzr6vA2zaA',
                    'name' => 'salemdominionmin_db',
                    'port' => 3306,
                    'charset' => 'utf8mb4'
                ];
            }
        }
    }
    
    /**
     * Global functions for backward compatibility
     */
    function getConnection() {
        try {
            $db = FinalDatabaseConnection::getInstance();
            return $db->getConnection();
        } catch (Exception $e) {
            error_log("Final DB connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    function testConnection() {
        try {
            $db = FinalDatabaseConnection::getInstance();
            return $db->testConnection();
        } catch (Exception $e) {
            error_log("Final DB test failed: " . $e->getMessage());
            return false;
        }
    }
    
    function getDatabaseStatus() {
        try {
            $db = FinalDatabaseConnection::getInstance();
            return $db->getDatabaseStatus();
        } catch (Exception $e) {
            error_log("Final DB status failed: " . $e->getMessage());
            return [
                'environment' => 'unknown',
                'mysql_connected' => false,
                'database_exists' => false,
                'tables_exist' => false,
                'admin_users_exist' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
