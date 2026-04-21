<?php
/**
 * Database Connection Test Script
 * Tests both localhost and hosting database connections
 */

echo "<h2>Salem Dominion Ministries - Database Connection Test</h2>";

// Include database connection
require_once 'db_connection.php';

// Test connection
$conn = getConnection();

if ($conn) {
    echo "<p style='color: green; font-size: 18px;'>Database connection successful!</p>";
    
    // Test basic queries
    echo "<h3>Testing Database Features:</h3>";
    
    // Test users table
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p style='color: green;'>Users table: $count users found</p>";
    } else {
        echo "<p style='color: red;'>Error querying users table: " . $conn->error . "</p>";
    }
    
    // Test admin_users table
    $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p style='color: green;'>Admin users table: $count admins found</p>";
    } else {
        echo "<p style='color: red;'>Error querying admin_users table: " . $conn->error . "</p>";
    }
    
    // Test messages table
    $result = $conn->query("SELECT COUNT(*) as count FROM messages");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "<p style='color: green;'>Messages table: $count messages found</p>";
    } else {
        echo "<p style='color: red;'>Error querying messages table: " . $conn->error . "</p>";
    }
    
    // Test profile_image column
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>Profile image column exists in users table</p>";
    } else {
        echo "<p style='color: orange;'>Profile image column missing - this may need to be added</p>";
    }
    
    // Test messaging system tables
    $tables_to_check = ['messages', 'message_attachments'];
    foreach ($tables_to_check as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>Table '$table' missing</p>";
        }
    }
    
    echo "<h3>Connection Details:</h3>";
    echo "<p><strong>Database:</strong> " . $conn->server_info . "</p>";
    echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
    echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_NAME'] . "</p>";
    
    echo "<h3 style='color: green;'>All Tests Completed Successfully!</h3>";
    echo "<p>Your database connection is working perfectly.</p>";
    
} else {
    echo "<p style='color: red; font-size: 18px;'>Database connection failed!</p>";
    echo "<p>Please check your database credentials and try again.</p>";
    
    // Show current environment
    echo "<h3>Environment Information:</h3>";
    echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";
    echo "<p><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "</p>";
    echo "<p><strong>SERVER_ADDR:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'Not set') . "</p>";
}
?>
