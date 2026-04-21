<?php
require_once 'db_connection.php';

echo "<h2>Enhanced Database Connection Test</h2>";
echo "<style>body{font-family:Arial;padding:20px;}.status{padding:10px;margin:5px;border-radius:5px;}.success{background:#d4edda;color:#155724;}.error{background:#f8d7da;color:#721c24;}.info{background:#d1ecf1;color:#0c5460;}</style>";

// Test environment detection
echo "<div class='status info'>";
echo "<strong>Environment Detection:</strong><br>";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "<br>";
echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'not set') . "<br>";
echo "Server Addr: " . ($_SERVER['SERVER_ADDR'] ?? 'not set') . "<br>";
echo "Is Localhost: " . (function_exists('isLocalhost') ? (isLocalhost() ? 'Yes' : 'No') : 'Function not found') . "<br>";
echo "</div>";

// Test database connection
echo "<h3>Database Connection Test:</h3>";
$conn = getConnection();

if ($conn) {
    echo "<div class='status success'>";
    echo "<strong>SUCCESS: Database Connected!</strong><br>";
    echo "Host Info: " . $conn->host_info . "<br>";
    echo "Server Version: " . $conn->server_info . "<br>";
    echo "Client Version: " . $conn->client_info . "<br>";
    echo "</div>";
    
    // Test database operations
    echo "<h3>Database Operations Test:</h3>";
    
    // Test query
    $result = $conn->query("SELECT 1 as test");
    if ($result) {
        echo "<div class='status success'>Basic query test: PASSED</div>";
    } else {
        echo "<div class='status error'>Basic query test: FAILED</div>";
    }
    
    // Check tables
    $tables = $conn->query("SHOW TABLES");
    if ($tables && $tables->num_rows > 0) {
        echo "<div class='status success'>Found " . $tables->num_rows . " tables:</div>";
        echo "<div class='status info'>";
        while ($table = $tables->fetch_row()) {
            echo "- " . $table[0] . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='status error'>No tables found</div>";
    }
    
    // Test admin users
    $admin_check = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    if ($admin_check) {
        $admin_count = $admin_check->fetch_assoc()['count'];
        echo "<div class='status success'>Admin users: " . $admin_count . "</div>";
    }
    
    $conn->close();
    
} else {
    echo "<div class='status error'>";
    echo "<strong>FAILED: Database Connection Failed!</strong><br>";
    echo "Please check:<br>";
    echo "1. MySQL/XAMPP service is running<br>";
    echo "2. Database credentials are correct<br>";
    echo "3. Database exists<br>";
    echo "</div>";
    
    // Provide troubleshooting steps
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<div class='status info'>";
    echo "1. <strong>Start XAMPP MySQL:</strong> Open XAMPP Control Panel and click 'Start' next to MySQL<br>";
    echo "2. <strong>Check MySQL Port:</strong> Ensure port 3306 is available<br>";
    echo "3. <strong>Verify Credentials:</strong> Check username/password in db_connection.php<br>";
    echo "4. <strong>Create Database:</strong> Run 'CREATE DATABASE salem_dominion_ministries' in phpMyAdmin<br>";
    echo "5. <strong>Test Manually:</strong> Try connecting with phpMyAdmin<br>";
    echo "</div>";
}

echo "<h3>Connection Test Complete</h3>";
echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a> | <a href='index.php'>Go to Homepage</a></p>";
?>
