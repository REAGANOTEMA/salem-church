<?php
/**
 * Production Configuration for Salem Dominion Ministries
 * 
 * USAGE INSTRUCTIONS:
 * 1. Update the database credentials below with your hosting provider's details
 * 2. Upload this file to your hosting server
 * 3. The system will automatically detect and use these settings
 * 
 * IMPORTANT: Replace the placeholder values with your actual hosting database details
 */

// Production Database Configuration
define('PROD_DB_HOST', 'localhost');        // Usually localhost on shared hosting
define('PROD_DB_USER', 'your_db_username');  // Replace with your hosting DB username
define('PROD_DB_PASS', 'your_db_password');  // Replace with your hosting DB password  
define('PROD_DB_NAME', 'your_db_name');      // Replace with your hosting DB name
define('PROD_DB_PORT', 3306);                // Usually 3306 on hosting

// Production Site Configuration
define('PROD_SITE_URL', 'https://yourdomain.com'); // Replace with your actual domain
define('PROD_SITE_NAME', 'Salem Dominion Ministries');

// Production Email Configuration (for contact forms, notifications, etc.)
define('PROD_EMAIL_FROM', 'noreply@yourdomain.com');
define('PROD_EMAIL_TO', 'info@yourdomain.com');

// Production Upload Configuration
define('PROD_UPLOAD_PATH', '/home/yourusername/public_html/uploads/'); // Adjust to your hosting path

// Production Security Settings
define('PROD_FORCE_HTTPS', true);  // Set to true to force HTTPS
define('PROD_DEBUG_MODE', false);   // Set to false in production

/**
 * DO NOT EDIT BELOW THIS LINE
 * The following code automatically applies the production settings
 */

// Override database constants for production
if (!defined('DB_HOST')) {
    define('DB_HOST', PROD_DB_HOST);
}
if (!defined('DB_USER')) {
    define('DB_USER', PROD_DB_USER);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', PROD_DB_PASS);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', PROD_DB_NAME);
}
if (!defined('DB_PORT_PRIMARY')) {
    define('DB_PORT_PRIMARY', PROD_DB_PORT);
}
if (!defined('DB_PORT_FALLBACK')) {
    define('DB_PORT_FALLBACK', PROD_DB_PORT);
}

// Override site constants for production
if (!defined('SITE_URL')) {
    define('SITE_URL', PROD_SITE_URL);
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', PROD_SITE_NAME);
}

// Production environment flag
define('IS_PRODUCTION', true);

// Log production configuration (remove this line in actual production)
error_log("Production configuration loaded for " . PROD_SITE_URL);
?>
