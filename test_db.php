<?php
// Simple database connection test
require_once 'db_connection.php';

echo "<h3>Database Connection Test</h3>";

// Test connection
$conn = createDatabaseConnection();

if ($conn === null) {
    echo "<p style='color: red;'><strong>FAILED:</strong> Database connection returned null</p>";
} elseif (is_array($conn)) {
    echo "<p style='color: red;'><strong>FAILED:</strong> Database connection returned array instead of connection object</p>";
    echo "<pre>" . print_r($conn, true) . "</pre>";
} else {
    echo "<p style='color: green;'><strong>SUCCESS:</strong> Database connection established</p>";
    
    // Test query
    try {
        $result = $conn->query("SELECT 1");
        if ($result) {
            echo "<p style='color: green;'><strong>SUCCESS:</strong> Database query test passed</p>";
        } else {
            echo "<p style='color: orange;'><strong>WARNING:</strong> Connection established but query failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Show connection info
    echo "<h4>Connection Details:</h4>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($conn->host_info) . "</li>";
    echo "<li>Server Info: " . htmlspecialchars($conn->server_info) . "</li>";
    echo "<li>Client Version: " . htmlspecialchars($conn->client_version) . "</li>";
    echo "</ul>";
    
    $conn->close();
}

echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
?>
