<?php
/**
 * Quick Database Setup for Hosting
 * Creates essential tables for registration and messaging
 */

echo "<h2>Hosting Database Setup</h2>";

// Hosting database credentials
$config = [
    'host' => 'localhost',
    'user' => 'salemdominionmin_db',
    'pass' => 'CtYeTnGktDxy9UvdtZJF',
    'name' => 'salemdominionmin_db',
    'port' => 3306
];

try {
    // Connect to database
    $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['name'], $config['port']);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>Connected to hosting database successfully!</p>";
    
    // Create users table if it doesn't exist
    $create_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        username VARCHAR(100) UNIQUE DEFAULT NULL,
        password VARCHAR(255) DEFAULT NULL,
        role ENUM('user','member','admin','pastor') DEFAULT 'user',
        phone VARCHAR(20) DEFAULT NULL,
        profile_image VARCHAR(255) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        city VARCHAR(100) DEFAULT NULL,
        country VARCHAR(100) DEFAULT NULL,
        avatar TEXT DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        email_verified TINYINT(1) DEFAULT 0,
        last_login DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_users)) {
        echo "<p style='color: green;'>Users table created/verified</p>";
    } else {
        echo "<p style='color: red;'>Error creating users table: " . $conn->error . "</p>";
    }
    
    // Create admin_users table if it doesn't exist
    $create_admin = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        is_active TINYINT(1) DEFAULT 1,
        last_login TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_admin)) {
        echo "<p style='color: green;'>Admin users table created/verified</p>";
    } else {
        echo "<p style='color: red;'>Error creating admin users table: " . $conn->error . "</p>";
    }
    
    // Create messages table if it doesn't exist
    $create_messages = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        recipient_id INT DEFAULT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        message_type ENUM('user_to_admin', 'admin_to_user', 'user_to_user') DEFAULT 'user_to_admin',
        status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
        priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
        parent_message_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_sender (sender_id),
        INDEX idx_recipient (recipient_id),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_messages)) {
        echo "<p style='color: green;'>Messages table created/verified</p>";
    } else {
        echo "<p style='color: red;'>Error creating messages table: " . $conn->error . "</p>";
    }
    
    // Create message_attachments table if it doesn't exist
    $create_attachments = "CREATE TABLE IF NOT EXISTS message_attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message_id INT NOT NULL,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_message (message_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_attachments)) {
        echo "<p style='color: green;'>Message attachments table created/verified</p>";
    } else {
        echo "<p style='color: red;'>Error creating message attachments table: " . $conn->error . "</p>";
    }
    
    // Check if admin user exists, create if not
    $admin_check = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    $admin_count = $admin_check->fetch_assoc()['count'];
    
    if ($admin_count == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO admin_users (username, password_hash, email, full_name, role) VALUES ('admin', '$admin_password', 'admin@salem-dominion-ministries.org', 'Administrator', 'admin')";
        
        if ($conn->query($insert_admin)) {
            echo "<p style='color: green;'>Default admin user created (admin/admin123)</p>";
        } else {
            echo "<p style='color: red;'>Error creating admin user: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>Admin users already exist</p>";
    }
    
    // Verify tables
    echo "<h3>Database Verification:</h3>";
    $tables = ['users', 'admin_users', 'messages', 'message_attachments'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $count = $conn->query("SELECT COUNT(*) as count FROM `$table`")->fetch_assoc()['count'];
            echo "<p style='color: green;'>$table: $count records</p>";
        } else {
            echo "<p style='color: red;'>$table: NOT FOUND</p>";
        }
    }
    
    echo "<h3 style='color: green;'>Setup Complete!</h3>";
    echo "<p>Your hosting database is now ready for registration and messaging.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test the registration form</li>";
    echo "<li>Delete this setup script for security</li>";
    echo "</ul>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Setup failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials and try again.</p>";
}
?>
