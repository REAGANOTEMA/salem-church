<?php
/**
 * HOSTING PLATFORM CONFIGURATION
 * Update these values for your specific hosting platform
 * 
 * COMMON HOSTING DATABASE FORMATS:
 * - cPanel: username_domain_db, username_domain_user
 * - Plesk: username_db, username_user
 * - DirectAdmin: username_db, username_user
 * - Custom: Check your hosting control panel
 */

// === UPDATE THESE VALUES FOR YOUR HOSTING ===
$HOSTING_CONFIG = [
    'host' => 'localhost',           // Usually 'localhost' or '127.0.0.1'
    'user' => 'salemdominionmin_db', // Your database username
    'pass' => 'CtYeTnGktDxy9UvdtZJF', // Your database password
    'name' => 'salemdominionmin_db', // Your database name
    'port' => 3306                   // Usually 3306
];

// Alternative configurations (uncomment and modify if needed)
/*
$HOSTING_CONFIG = [
    'host' => 'localhost',
    'user' => 'salemchur_root',
    'pass' => 'your_password_here',
    'name' => 'salemchur_salem_ministries',
    'port' => 3306
];
*/

/*
$HOSTING_CONFIG = [
    'host' => 'localhost',
    'user' => 'your_domain_admin',
    'pass' => 'your_password_here',
    'name' => 'your_domain_salem_ministries',
    'port' => 3306
];
*/

// === DO NOT MODIFY BELOW THIS LINE ===

// Function to get hosting configuration
function getHostingDatabaseConfig() {
    global $HOSTING_CONFIG;
    return $HOSTING_CONFIG;
}

// Test hosting configuration
function testHostingConfig() {
    $config = getHostingDatabaseConfig();
    
    try {
        $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name'], $config['port']);
        
        if ($conn->connect_error) {
            return [
                'success' => false,
                'error' => $conn->connect_error
            ];
        }
        
        // Test query
        $test_query = $conn->query("SELECT 1");
        if (!$test_query) {
            $conn->close();
            return [
                'success' => false,
                'error' => $conn->error
            ];
        }
        
        $conn->close();
        return [
            'success' => true,
            'message' => 'Hosting database connection successful'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Auto-add hosting config to database_config.php if needed
function addHostingConfigToDatabaseConfig() {
    $config = getHostingDatabaseConfig();
    
    // This function can be called to update the database_config.php
    // with the hosting-specific configuration
    return $config;
}
?>
