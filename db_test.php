<?php
// Simple Database Connection Test
echo "<h1>Database Connection Test</h1>";

// Test with hosting credentials
$host = 'localhost';
$user = 'salemdominionmin_db';
$pass = 'EtacdN8wXLmzr6vA2zaA';
$name = 'salemdominionmin_db';

echo "<h2>Testing Connection With:</h2>";
echo "<p>Host: $host</p>";
echo "<p>User: $user</p>";
echo "<p>Database: $name</p>";

try {
    $conn = new mysqli($host, $user, $pass, $name);
    
    if ($conn->connect_error) {
        echo "<p style='color: red; font-weight: bold;'>✗ Connection Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    } else {
        echo "<p style='color: green; font-weight: bold;'>✓ Connection Successful!</p>";
        
        // Check if admin_users table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
        if ($table_check && $table_check->num_rows > 0) {
            echo "<p style='color: green;'>✓ Admin Users Table Exists</p>";
            
            // Check for MusasiziFaty user
            $user_check = $conn->prepare("SELECT username FROM admin_users WHERE username = ?");
            $user_check->bind_param("s", "MusasiziFaty");
            $user_check->execute();
            $user_result = $user_check->get_result();
            
            if ($user_result->num_rows > 0) {
                echo "<p style='color: green;'>✓ MusasiziFaty User Found</p>";
            } else {
                echo "<p style='color: orange;'>⚠ MusasiziFaty User Not Found - Will Create</p>";
                
                // Create MusasiziFaty user
                $password_hash = password_hash('Musasizi123', PASSWORD_DEFAULT);
                $insert = $conn->prepare("INSERT INTO admin_users (username, password_hash, email, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $insert->bind_param("sssss", "MusasiziFaty", $password_hash, "pastor@salem-dominion-ministries.com", "Pastor Faty Musasizi", "admin");
                
                if ($insert->execute()) {
                    echo "<p style='color: green;'>✓ MusasiziFaty User Created Successfully</p>";
                } else {
                    echo "<p style='color: red;'>✗ Failed to Create MusasiziFaty User</p>";
                }
                $insert->close();
            }
            $user_check->close();
        } else {
            echo "<p style='color: red;'>✗ Admin Users Table Missing</p>";
            
            // Create admin_users table
            $create_table = "CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(255),
                role ENUM('user', 'admin') DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            if ($conn->query($create_table)) {
                echo "<p style='color: green;'>✓ Admin Users Table Created</p>";
                
                // Create MusasiziFaty user
                $password_hash = password_hash('Musasizi123', PASSWORD_DEFAULT);
                $insert = $conn->prepare("INSERT INTO admin_users (username, password_hash, email, full_name, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $insert->bind_param("sssss", "MusasiziFaty", $password_hash, "pastor@salem-dominion-ministries.com", "Pastor Faty Musasizi", "admin");
                
                if ($insert->execute()) {
                    echo "<p style='color: green;'>✓ MusasiziFaty User Created Successfully</p>";
                } else {
                    echo "<p style='color: red;'>✗ Failed to Create MusasiziFaty User</p>";
                }
                $insert->close();
            } else {
                echo "<p style='color: red;'>✗ Failed to Create Admin Users Table</p>";
            }
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='admin/welcome.php'>Test Admin Login</a></p>";
echo "<p><a href='index.php'>Back to Website</a></p>";
?>
