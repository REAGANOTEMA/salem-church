<?php
/**
 * Registration Database Connection Debug Script
 * Helps identify why registration is failing
 */

echo "<h2>Registration Database Connection Debug</h2>";

// Include database connection
require_once 'db_connection.php';

echo "<h3>Environment Detection:</h3>";
echo "<p><strong>HTTP_HOST:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "</p>";
echo "<p><strong>SERVER_NAME:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Not set') . "</p>";
echo "<p><strong>SERVER_ADDR:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'Not set') . "</p>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";

// Test isLocalhost function
function isLocalhost() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    $http_host = $_SERVER['HTTP_HOST'] ?? '';
    $server_addr = $_SERVER['SERVER_ADDR'] ?? '';
    
    // Enhanced localhost indicators
    $localhost_indicators = [
        'localhost', '127.0.0.1', '::1', 
        '192.168.', '10.0.', '172.16.', 
        '.local', '.dev', '.test', '.localdomain',
        'xampp', 'wamp', 'mamp', 'laravel', 'wordpress'
    ];
    
    foreach ($localhost_indicators as $indicator) {
        if (strpos($server_name, $indicator) !== false || 
            strpos($http_host, $indicator) !== false ||
            strpos($server_addr, $indicator) !== false) {
            return true;
        }
    }
    
    // Additional check for common hosting environments
    $hosting_indicators = [
        'salemdominionministries.com', 'www.salemdominionministries.com',
        '.com', '.org', '.net'
    ];
    
    foreach ($hosting_indicators as $indicator) {
        if (strpos($http_host, $indicator) !== false) {
            return false; // This is hosting
        }
    }
    
    // Default to localhost if no clear indicators
    return true;
}

$is_localhost = isLocalhost();
echo "<p><strong>Detected as Localhost:</strong> " . ($is_localhost ? 'Yes' : 'No') . "</p>";

echo "<h3>Database Connection Test:</h3>";

if ($is_localhost) {
    echo "<p>Using Localhost Configuration:</p>";
    echo "<p>Host: localhost</p>";
    echo "<p>User: root</p>";
    echo "<p>Database: salem_dominion_ministries</p>";
    
    // Test localhost connection
    try {
        $test_conn = new mysqli('localhost', 'root', 'ReagaN23#', 'salem_dominion_ministries', 3306);
        if ($test_conn->connect_error) {
            echo "<p style='color: red;'>Localhost connection failed: " . $test_conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>Localhost connection successful!</p>";
            $test_conn->close();
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Localhost exception: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Using Hosting Configuration:</p>";
    echo "<p>Host: localhost</p>";
    echo "<p>User: salemdominionmin_db</p>";
    echo "<p>Database: salemdominionmin_db</p>";
    
    // Test hosting connection
    try {
        $test_conn = new mysqli('localhost', 'salemdominionmin_db', 'CtYeTnGktDxy9UvdtZJF', 'salemdominionmin_db', 3306);
        if ($test_conn->connect_error) {
            echo "<p style='color: red;'>Hosting connection failed: " . $test_conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>Hosting connection successful!</p>";
            
            // Test if users table exists
            $result = $test_conn->query("SHOW TABLES LIKE 'users'");
            if ($result && $result->num_rows > 0) {
                echo "<p style='color: green;'>Users table exists</p>";
            } else {
                echo "<p style='color: orange;'>Users table missing - need to create database structure</p>";
            }
            
            $test_conn->close();
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Hosting exception: " . $e->getMessage() . "</p>";
    }
}

// Test getConnection() function
echo "<h3>getConnection() Function Test:</h3>";
$conn = getConnection();
if ($conn) {
    echo "<p style='color: green;'>getConnection() successful!</p>";
    
    // Test basic query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "<p style='color: green;'>Basic query successful</p>";
    } else {
        echo "<p style='color: red;'>Basic query failed: " . $conn->error . "</p>";
    }
    
    $conn->close();
} else {
    echo "<p style='color: red;'>getConnection() failed!</p>";
}

echo "<h3>Recommendations:</h3>";
echo "<ul>";
echo "<li>If hosting connection fails, check database credentials</li>";
echo "<li>If users table missing, run the SQL setup script</li>";
echo "<li>If environment detection wrong, update isLocalhost() function</li>";
echo "<li>Delete this debug script after fixing issues</li>";
echo "</ul>";
?>
