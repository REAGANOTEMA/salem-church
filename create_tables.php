<?php
/**
 * Database Setup Script for Salem Dominion Ministries
 * This script creates the necessary tables for the website
 */

require_once 'db_connection.php';

echo "<h2>Database Setup for Salem Dominion Ministries</h2>";

// Test connection first
$conn = getConnection();

if (!$conn) {
    echo "<div style='color: red; font-weight: bold;'>ERROR: Cannot connect to database!</div>";
    echo "<div>Please check your database connection details in db_connection.php</div>";
    exit;
}

echo "<div style='color: green; font-weight: bold;'>SUCCESS: Database connected!</div>";

// SQL statements to create tables
$tables = [
    // Admin users table
    "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'super_admin') DEFAULT 'admin',
        is_active TINYINT(1) DEFAULT 1,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Regular users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        country VARCHAR(50),
        role ENUM('user', 'member') DEFAULT 'user',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Sermons table
    "CREATE TABLE IF NOT EXISTS sermons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        sermon_date DATE,
        speaker VARCHAR(100),
        video_url VARCHAR(255),
        audio_url VARCHAR(255),
        thumbnail VARCHAR(255),
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Events table
    "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        event_time TIME,
        location VARCHAR(255),
        max_attendees INT,
        status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // News table
    "CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        content TEXT NOT NULL,
        excerpt VARCHAR(500),
        featured_image VARCHAR(255),
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Ministries table
    "CREATE TABLE IF NOT EXISTS ministries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        category VARCHAR(50),
        leader_name VARCHAR(100),
        leader_email VARCHAR(100),
        meeting_schedule VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
];

echo "<h3>Creating Tables...</h3>";

$success_count = 0;
$error_count = 0;

foreach ($tables as $sql) {
    try {
        if ($conn->query($sql)) {
            $success_count++;
            echo "<div style='color: green;'>Table created successfully</div>";
        } else {
            $error_count++;
            echo "<div style='color: orange;'>Table may already exist or error occurred</div>";
        }
    } catch (Exception $e) {
        $error_count++;
        echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Insert default admin user if not exists
echo "<h3>Creating Default Admin User...</h3>";

$admin_username = 'MusasiziFaty';
$admin_password = '123456'; // This will be hashed
$admin_email = 'admin@salem-dominion-ministries.org';
$admin_full_name = 'Musasizi Faty';
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$check_admin = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
$check_admin->bind_param("s", $admin_username);
$check_admin->execute();

if ($check_admin->get_result()->num_rows == 0) {
    $insert_admin = $conn->prepare("INSERT INTO admin_users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')");
    $insert_admin->bind_param("ssss", $admin_username, $hashed_password, $admin_email, $admin_full_name);
    
    if ($insert_admin->execute()) {
        echo "<div style='color: green;'>Default admin user created successfully!</div>";
        echo "<div>Username: <strong>$admin_username</strong></div>";
        echo "<div>Password: <strong>$admin_password</strong></div>";
        echo "<div style='color: orange; font-weight: bold;'>Please change this password after first login!</div>";
    } else {
        echo "<div style='color: red;'>Error creating admin user</div>";
    }
} else {
    echo "<div style='color: blue;'>Admin user already exists</div>";
}

echo "<hr>";
echo "<h3>Setup Summary:</h3>";
echo "<div>Tables created: $success_count</div>";
echo "<div>Errors: $error_count</div>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='test_new_db.php'>Test your database connection</a></li>";
echo "<li><a href='admin_login.php'>Login to admin panel</a></li>";
echo "<li><a href='index.php'>Visit your homepage</a></li>";
echo "</ol>";

$conn->close();
?>
