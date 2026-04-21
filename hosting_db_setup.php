<?php
/**
 * Hosting Database Setup Assistant
 * Helps configure database for different hosting platforms
 */

require_once 'config.php';

// Test different database configurations
function testDatabaseConfig($host, $user, $pass, $name, $port = 3306) {
    try {
        $conn = new mysqli($host, $user, $pass, $name, $port);
        
        if ($conn->connect_error) {
            return [
                'success' => false,
                'error' => $conn->connect_error,
                'config' => [
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass ? '***' : '',
                    'name' => $name,
                    'port' => $port
                ]
            ];
        }
        
        // Test database access
        $test_query = $conn->query("SELECT 1");
        if ($test_query) {
            $conn->close();
            return [
                'success' => true,
                'message' => 'Connection successful!',
                'config' => [
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass ? '***' : '',
                    'name' => $name,
                    'port' => $port
                ]
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Database query failed',
                'config' => [
                    'host' => $host,
                    'user' => $user,
                    'pass' => $pass ? '***' : '',
                    'name' => $name,
                    'port' => $port
                ]
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'config' => [
                'host' => $host,
                'user' => $user,
                'pass' => $pass ? '***' : '',
                'name' => $name,
                'port' => $port
            ]
        ];
    }
}

// Common hosting database configurations
$configs = [
    // Standard cPanel/Shared Hosting
    [
        'name' => 'Shared Hosting (cPanel)',
        'host' => 'localhost',
        'user' => 'salem_admin',
        'pass' => '',
        'name' => 'salem_dominion_ministries',
        'port' => 3306
    ],
    // Alternative Shared Hosting
    [
        'name' => 'Shared Hosting (Root)',
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'salem_dominion_ministries',
        'port' => 3306
    ],
    // Database with password
    [
        'name' => 'Shared Hosting (With Password)',
        'host' => 'localhost',
        'user' => 'salem_admin',
        'pass' => 'your_password_here',
        'name' => 'salem_dominion_ministries',
        'port' => 3306
    ],
    // Remote Database (VPS/Dedicated)
    [
        'name' => 'Remote Database',
        'host' => '127.0.0.1',
        'user' => 'salem_admin',
        'pass' => '',
        'name' => 'salem_dominion_ministries',
        'port' => 3306
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - <?php echo CHURCH_NAME; ?></title>
    
    <!-- Church Branding -->
    <link rel="icon" type="image/jpeg" href="<?php echo CHURCH_LOGO; ?>">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
        }
        .config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .config-item {
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f8fafc;
        }
        .test-btn {
            background: #0ea5e9;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin: 10px 0;
        }
        .test-btn:hover {
            background: #0284c7;
        }
        .result {
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
        }
        .success {
            background: #10b981;
            color: white;
            border: 1px solid #059669;
        }
        .error {
            background: #ef4444;
            color: white;
            border: 1px solid #dc2626;
        }
        .config-details {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo CHURCH_LOGO; ?>" alt="<?php echo CHURCH_NAME; ?>" class="logo">
            <h1>Database Setup Assistant</h1>
            <p>Test and configure your database connection for hosting platforms</p>
        </div>
        
        <div class="config-grid">
            <?php foreach ($configs as $config): ?>
                <div class="config-item">
                    <h3><?php echo htmlspecialchars($config['name']); ?></h3>
                    <div class="config-details">
                        <strong>Host:</strong> <?php echo htmlspecialchars($config['host']); ?><br>
                        <strong>User:</strong> <?php echo htmlspecialchars($config['user']); ?><br>
                        <strong>Password:</strong> <?php echo htmlspecialchars($config['pass']); ?><br>
                        <strong>Database:</strong> <?php echo htmlspecialchars($config['name']); ?><br>
                        <strong>Port:</strong> <?php echo htmlspecialchars($config['port']); ?>
                    </div>
                    <button class="test-btn" onclick="testConfig(<?php echo json_encode($config); ?>)">
                        Test Connection
                    </button>
                    <div id="result-<?php echo md5($config['name']); ?>"></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="result" id="overall-result"></div>
    </div>
    
    <script>
        function testConfig(config) {
            const resultDiv = document.getElementById('result-' + btoa(config.name));
            const overallResult = document.getElementById('overall-result');
            
            resultDiv.innerHTML = '<div style="text-align: center; padding: 10px;">Testing...</div>';
            overallResult.innerHTML = '';
            
            fetch('hosting_db_setup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'test',
                    config: JSON.stringify(config)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'result success';
                    resultDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                } else {
                    resultDiv.className = 'result error';
                    resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.error;
                }
                
                // Update overall status
                updateOverallStatus();
            })
            .catch(error => {
                resultDiv.className = 'result error';
                resultDiv.innerHTML = '<i class="fas fa-times-circle"></i> Connection failed: ' + error.message;
            });
        }
        
        function updateOverallStatus() {
            const results = document.querySelectorAll('.result');
            let allConnected = true;
            
            results.forEach(result => {
                if (result.classList.contains('error')) {
                    allConnected = false;
                }
            });
            
            const overallResult = document.getElementById('overall-result');
            if (allConnected) {
                overallResult.className = 'result success';
                overallResult.innerHTML = '<i class="fas fa-check-circle"></i> <strong>Database Connection Successful!</strong><br><small>Update your config.php with the working configuration above.</small>';
            } else {
                overallResult.className = 'result error';
                overallResult.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <strong>Database Connection Failed</strong><br><small>Try different configurations or contact your hosting provider.</small>';
            }
        }
    </script>
</body>
</html>
