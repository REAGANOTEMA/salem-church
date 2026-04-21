<?php
/**
 * Advanced Database Connection Test
 * Tests multiple ports, passwords, and configurations
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Advanced Database Test - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 8px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 10px; font-size: 12px; }
        .working-config { background: #d1f2eb; border: 2px solid #28a745; padding: 15px; border-radius: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-database me-2'></i>Advanced Database Connection Test</h3>
                <p class='mb-0'>Comprehensive testing of ports, passwords, and configurations</p>
            </div>
            <div class='card-body'>
                <h4><i class='fas fa-cog fa-spin me-2'></i>Testing All Database Configurations...</h4>";

// Test configurations
$hosts = ['localhost', '127.0.0.1'];
$ports = [3306, 3307, 3308];
$passwords = ['', 'ReagaN23#', 'root', 'password', '123456', 'admin'];
$working_configs = [];
$total_tests = 0;
$successful_tests = 0;

echo "<div class='mb-4'>
        <h5>Testing Connection Combinations...</h5>";

foreach ($hosts as $host) {
    foreach ($ports as $port) {
        foreach ($passwords as $password) {
            $total_tests++;
            $config_str = "$host:$port | root | " . ($password ? $password : '(empty)');
            
            echo "<div class='test-result'>";
            try {
                $conn = new mysqli($host, 'root', $password, '', $port);
                if ($conn->connect_error) {
                    echo "<span class='status-error'>FAILED</span> - $config_str<br>";
                    echo "<small>Error: " . htmlspecialchars($conn->connect_error) . "</small>";
                } else {
                    $successful_tests++;
                    echo "<span class='status-ok'>SUCCESS</span> - $config_str<br>";
                    
                    // Test database access
                    $db_test = $conn->query("SHOW DATABASES LIKE 'salem_dominion_ministries'");
                    if ($db_test && $db_test->num_rows > 0) {
                        echo "<small class='status-ok'>Database exists!</small>";
                        $working_configs[] = [
                            'host' => $host,
                            'port' => $port,
                            'password' => $password,
                            'database_exists' => true
                        ];
                    } else {
                        echo "<small class='status-warning'>Database not found (but can create)</small>";
                        $working_configs[] = [
                            'host' => $host,
                            'port' => $port,
                            'password' => $password,
                            'database_exists' => false
                        ];
                    }
                    $conn->close();
                }
            } catch (Exception $e) {
                echo "<span class='status-error'>ERROR</span> - $config_str<br>";
                echo "<small>" . htmlspecialchars($e->getMessage()) . "</small>";
            }
            echo "</div>";
        }
    }
}

echo "</div>";

// Show working configurations
if (!empty($working_configs)) {
    echo "<div class='working-config'>
            <h5><i class='fas fa-check-circle me-2'></i>Working Configurations Found!</h5>";
    
    foreach ($working_configs as $config) {
        echo "<div class='mb-3'>
                <strong>Configuration:</strong><br>
                Host: {$config['host']}<br>
                Port: {$config['port']}<br>
                User: root<br>
                Password: " . ($config['password'] ?: '(empty)') . "<br>
                Database Status: " . ($config['database_exists'] ? 'EXISTS' : 'CAN CREATE') . "
              </div>";
        
        // Generate the correct database configuration
        echo "<div class='alert alert-info'>
                <h6>Recommended db_connection.php settings:</h6>
                <pre>
if (!defined('DB_HOST')) {
    define('DB_HOST', '{$config['host']}');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '" . addslashes($config['password']) . "');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'salem_dominion_ministries');
}
                </pre>
              </div>";
        
        // Provide one-click fix button
        echo "<div class='text-center mb-3'>
                <button class='btn btn-success' onclick='applyConfig(\"{$config['host']}\", {$config['port']}, \"" . addslashes($config['password']) . "\")'>
                    <i class='fas fa-magic me-2'></i>Apply This Configuration
                </button>
              </div>";
        
        break; // Only show the first working config
    }
    echo "</div>";
} else {
    echo "<div class='alert alert-danger'>
            <h5><i class='fas fa-exclamation-triangle me-2'></i>No Working Configurations Found</h5>
            <p>None of the tested configurations worked. Please check:</p>
            <ul>
                <li>MySQL/XAMPP is running</li>
                <li>MySQL port is correct (check XAMPP control panel)</li>
                <li>MySQL root user password is correct</li>
                <li>No firewall blocking the connection</li>
            </ul>
          </div>";
}

// Summary
echo "<div class='mt-4'>
        <h5>Test Summary:</h5>
        <div class='row'>
            <div class='col-md-4'>
                <div class='card text-center'>
                    <div class='card-body'>
                        <h3 class='text-primary'>$total_tests</h3>
                        <p class='mb-0'>Total Tests</p>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-center'>
                    <div class='card-body'>
                        <h3 class='text-success'>$successful_tests</h3>
                        <p class='mb-0'>Successful</p>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-center'>
                    <div class='card-body'>
                        <h3 class='text-danger'>" . ($total_tests - $successful_tests) . "</h3>
                        <p class='mb-0'>Failed</p>
                    </div>
                </div>
            </div>
        </div>
      </div>";

echo "
                <div class='text-center mt-4'>
                    <a href='admin/' class='btn btn-primary me-2'>
                        <i class='fas fa-sign-in-alt me-2'></i>Try Admin Panel
                    </a>
                    <a href='force_database_setup.php' class='btn btn-warning me-2'>
                        <i class='fas fa-cog me-2'></i>Force Setup
                    </a>
                    <a href='index.php' class='btn btn-outline-primary'>
                        <i class='fas fa-home me-2'></i>Visit Website
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function applyConfig(host, port, password) {
        if (confirm('Apply this configuration to db_connection.php?')) {
            // This would require a separate script to update the file
            alert('Please manually update db_connection.php with the settings shown above.');
        }
    }
    </script>
</body>
</html>";
?>
