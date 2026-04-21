<?php
// COMPREHENSIVE HOSTING DATABASE TEST AND DIAGNOSTIC SCRIPT
// Tests database connection on hosting platform with detailed diagnostics

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Hosting Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .status { font-weight: bold; }
        .details { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
        .code { font-family: monospace; background: #f1f1f1; padding: 5px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Hosting Database Connection Test</h1>
            <p>Comprehensive testing for Salem Dominion Ministries database connection</p>
        </div>";

// Include the enhanced database connection
require_once 'db_connection_hosting.php';

echo "<div class='test-section info'>
    <h3>Environment Detection</h3>";
    
$db = DatabaseConnection::getInstance();
$environment = $db->getEnvironment();
$config = $db->getConfig();

echo "<p><strong>Environment:</strong> <span class='status'>" . ucfirst($environment) . "</span></p>";
echo "<div class='details'>";
echo "<p><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "</p>";
echo "<p><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</p>";
echo "<p><strong>Server IP:</strong> " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>MySQLi Extension:</strong> " . (extension_loaded('mysqli') ? 'Installed' : 'Not Installed') . "</p>";
echo "</div>";

echo "<h4>Database Configuration:</h4>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>Host</td><td class='code'>" . $config['host'] . "</td></tr>";
echo "<tr><td>Username</td><td class='code'>" . $config['user'] . "</td></tr>";
echo "<tr><td>Password</td><td class='code'>" . str_repeat('*', strlen($config['pass'])) . "</td></tr>";
echo "<tr><td>Database</td><td class='code'>" . $config['name'] . "</td></tr>";
echo "<tr><td>Port</td><td class='code'>" . $config['port'] . "</td></tr>";
echo "<tr><td>Charset</td><td class='code'>" . $config['charset'] . "</td></tr>";
echo "</table>";
echo "</div>";

// Test basic connection
echo "<div class='test-section'>";
echo "<h3>Basic Connection Test</h3>";

try {
    $conn = $db->getConnection();
    if ($conn) {
        echo "<p class='success'><strong>Connection Status:</strong> <span class='status'>SUCCESS</span></p>";
        
        // Test basic query
        $result = $conn->query("SELECT 1 as test, NOW() as current_time, VERSION() as mysql_version");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<div class='details'>";
            echo "<p><strong>Test Query:</strong> PASSED</p>";
            echo "<p><strong>Current Time:</strong> " . $row['current_time'] . "</p>";
            echo "<p><strong>MySQL Version:</strong> " . $row['mysql_version'] . "</p>";
            echo "<p><strong>Connection ID:</strong> " . $conn->thread_id . "</p>";
            echo "</div>";
        }
        
        // Check database info
        echo "<h4>Database Information:</h4>";
        $db_info = $conn->query("SELECT DATABASE() as current_db, SCHEMA() as schema");
        if ($db_info) {
            $info = $db_info->fetch_assoc();
            echo "<table>";
            echo "<tr><th>Property</th><th>Value</th></tr>";
            echo "<tr><td>Current Database</td><td class='code'>" . $info['current_db'] . "</td></tr>";
            echo "<tr><td>Schema</td><td class='code'>" . $info['schema'] . "</td></tr>";
            echo "</table>";
        }
        
    } else {
        echo "<p class='error'><strong>Connection Status:</strong> <span class='status'>FAILED</span></p>";
        echo "<p>Unable to establish database connection. Check credentials and server status.</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>Connection Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Test table existence
echo "<div class='test-section'>";
echo "<h3>Database Tables Check</h3>";

try {
    $conn = $db->getConnection();
    if ($conn) {
        $tables = ['admin_users', 'users', 'donations', 'sermons', 'events', 'testimonials', 'contact_messages'];
        $existing_tables = [];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $existing_tables[] = $table;
            } else {
                $missing_tables[] = $table;
            }
        }
        
        echo "<p><strong>Tables Found:</strong> " . count($existing_tables) . "/" . count($tables) . "</p>";
        
        if (!empty($existing_tables)) {
            echo "<h4>Existing Tables:</h4>";
            echo "<div class='details success'>";
            foreach ($existing_tables as $table) {
                // Get table row count
                $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                $count = $count_result ? $count_result->fetch_assoc()['count'] : 0;
                echo "<p> <strong>$table</strong> - $count records</p>";
            }
            echo "</div>";
        }
        
        if (!empty($missing_tables)) {
            echo "<h4>Missing Tables:</h4>";
            echo "<div class='details error'>";
            foreach ($missing_tables as $table) {
                echo "<p> <strong>$table</strong> - Not found</p>";
            }
            echo "</div>";
        }
        
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>Table Check Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Test admin users
echo "<div class='test-section'>";
echo "<h3>Admin Users Test</h3>";

try {
    $conn = $db->getConnection();
    if ($conn) {
        $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            if ($count > 0) {
                echo "<p class='success'><strong>Admin Users:</strong> $count found</p>";
                
                // Show admin users (without passwords)
                $users_result = $conn->query("SELECT id, username, email, created_at FROM admin_users");
                if ($users_result) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
                    while ($user = $users_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td>" . $user['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            } else {
                echo "<p class='warning'><strong>Admin Users:</strong> None found. Need to create admin users.</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>Admin Users Test Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Test user registration
echo "<div class='test-section'>";
echo "<h3>User Registration Test</h3>";

try {
    $conn = $db->getConnection();
    if ($conn) {
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "<p><strong>Registered Users:</strong> $count found</p>";
            
            if ($count > 0) {
                $users_result = $conn->query("SELECT id, username, email, created_at FROM users LIMIT 5");
                if ($users_result) {
                    echo "<table>";
                    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
                    while ($user = $users_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                        echo "<td>" . $user['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>User Registration Test Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Test donations
echo "<div class='test-section'>";
echo "<h3>Donations Test</h3>";

try {
    $conn = $db->getConnection();
    if ($conn) {
        $result = $conn->query("SELECT COUNT(*) as count FROM donations");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "<p><strong>Total Donations:</strong> $count found</p>";
            
            if ($count > 0) {
                $total_result = $conn->query("SELECT SUM(amount) as total FROM donations");
                $total = $total_result ? $total_result->fetch_assoc()['total'] : 0;
                echo "<p><strong>Total Amount:</strong> UGX " . number_format($total) . "</p>";
                
                $recent_result = $conn->query("SELECT donor_name, amount, donation_type, created_at FROM donations ORDER BY created_at DESC LIMIT 5");
                if ($recent_result) {
                    echo "<table>";
                    echo "<tr><th>Name</th><th>Amount</th><th>Type</th><th>Date</th></tr>";
                    while ($donation = $recent_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($donation['donor_name']) . "</td>";
                        echo "<td>UGX " . number_format($donation['amount']) . "</td>";
                        echo "<td>" . htmlspecialchars($donation['donation_type']) . "</td>";
                        echo "<td>" . $donation['created_at'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='error'><strong>Donations Test Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Final status
echo "<div class='test-section'>";
echo "<h3>Final Status</h3>";

$status = $db->getConnectionStatus();
echo "<table>";
echo "<tr><th>Component</th><th>Status</th></tr>";
echo "<tr><td>Environment</td><td class='status'>" . ucfirst($status['environment']) . "</td></tr>";
echo "<tr><td>Database Connected</td><td class='status'>" . ($status['connected'] ? 'YES' : 'NO') . "</td></tr>";
echo "<tr><td>Database Exists</td><td class='status'>" . ($status['database_exists'] ? 'YES' : 'NO') . "</td></tr>";
echo "<tr><td>Tables Exist</td><td class='status'>" . ($status['tables_exist'] ? 'YES' : 'NO') . "</td></tr>";

if (isset($status['error'])) {
    echo "<tr><td>Error</td><td class='error'>" . htmlspecialchars($status['error']) . "</td></tr>";
}

echo "</table>";

if ($status['connected'] && $status['database_exists'] && $status['tables_exist']) {
    echo "<p class='success'><strong>Overall Status:</strong> <span class='status'>WORKING PERFECTLY</span></p>";
    echo "<p>Database connection is working correctly on hosting platform!</p>";
} else {
    echo "<p class='error'><strong>Overall Status:</strong> <span class='status'>ISSUES FOUND</span></p>";
    echo "<p>Database connection needs attention. Check the errors above.</p>";
}

echo "</div>";

echo "<div class='test-section info'>";
echo "<h3>Next Steps</h3>";
echo "<div class='details'>";
echo "<p><strong>If all tests pass:</strong></p>";
echo "<ul>";
echo "<li>Database connection is working perfectly on hosting</li>";
echo "<li>All website features should work correctly</li>";
echo "<li>Admin login and user registration should function</li>";
echo "<li>Donations and contact forms should work</li>";
echo "</ul>";

echo "<p><strong>If tests fail:</strong></p>";
echo "<ul>";
echo "<li>Verify database credentials with hosting provider</li>";
echo "<li>Check if database user has proper permissions</li>";
echo "<li>Ensure database exists on hosting server</li>";
echo "<li>Import SQL schema if tables are missing</li>";
echo "<li>Contact hosting support if connection issues persist</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<button class='btn btn-success' onclick='location.reload()'>Run Test Again</button>";
echo "<button class='btn' onclick='window.location.href=\"index.php\"'>Go to Homepage</button>";
echo "<button class='btn' onclick='window.location.href=\"admin_login.php\"'>Go to Admin Login</button>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
