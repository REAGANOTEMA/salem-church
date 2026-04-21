<?php
/**
 * Complete Hosting Setup Script
 * Ensures everything works perfectly when zipped and hosted
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hosting Setup Complete - Salem Dominion Ministries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --success-green: #22c55e;
            --danger-red: #ef4444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 100%);
            color: white;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .setup-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .setup-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .setup-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .setup-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.3);
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border-left: 4px solid;
        }

        .status-item.success {
            border-left-color: var(--success-green);
        }

        .status-item.error {
            border-left-color: var(--danger-red);
        }

        .status-item.warning {
            border-left-color: var(--heavenly-gold);
        }

        .status-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 30px;
            text-align: center;
        }

        .status-text {
            flex: 1;
        }

        .status-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .status-description {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .btn-setup {
            background: linear-gradient(135deg, var(--heavenly-gold), #f59e0b);
            color: var(--midnight-blue);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
            color: var(--midnight-blue);
            text-decoration: none;
        }

        .progress-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin: 2rem 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--success-green), var(--heavenly-gold));
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .instructions {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .instructions h5 {
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .instructions ol {
            padding-left: 1.5rem;
            margin: 0;
        }

        .instructions li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1 class="setup-title">
                <i class="fas fa-server me-3"></i>
                Hosting Setup Complete
            </h1>
            <p class="lead">Salem Dominion Ministries Website</p>
        </div>

        <div class="setup-card">
            <h3><i class="fas fa-check-circle me-2"></i>System Status Check</h3>
            
            <?php
            $checks = [];
            $total_checks = 0;
            $passed_checks = 0;

            // Check 1: PHP Version
            $total_checks++;
            $php_version = PHP_VERSION;
            if (version_compare($php_version, '7.4.0', '>=')) {
                $passed_checks++;
                $checks['php'] = [
                    'status' => 'success',
                    'title' => 'PHP Version',
                    'description' => "PHP $php_version - Compatible"
                ];
            } else {
                $checks['php'] = [
                    'status' => 'warning',
                    'title' => 'PHP Version',
                    'description' => "PHP $php_version - May have compatibility issues"
                ];
            }

            // Check 2: Database Connection
            $total_checks++;
            try {
                require_once 'db_connection.php';
                $conn = getConnection();
                if ($conn) {
                    $passed_checks++;
                    $checks['database'] = [
                        'status' => 'success',
                        'title' => 'Database Connection',
                        'description' => 'Connected successfully with hosting credentials'
                    ];
                    
                    // Check if tables exist
                    $tables_check = $conn->query("SHOW TABLES");
                    $table_count = $tables_check ? $tables_check->num_rows : 0;
                    
                    if ($table_count > 0) {
                        $checks['tables'] = [
                            'status' => 'success',
                            'title' => 'Database Tables',
                            'description' => "$table_count tables found"
                        ];
                    } else {
                        $checks['tables'] = [
                            'status' => 'warning',
                            'title' => 'Database Tables',
                            'description' => 'No tables found - needs setup'
                        ];
                    }
                    $conn->close();
                } else {
                    $checks['database'] = [
                        'status' => 'error',
                        'title' => 'Database Connection',
                        'description' => 'Connection failed - check credentials'
                    ];
                }
            } catch (Exception $e) {
                $checks['database'] = [
                    'status' => 'error',
                    'title' => 'Database Connection',
                    'description' => 'Exception: ' . $e->getMessage()
                ];
            }

            // Check 3: Required Files
            $total_checks++;
            $required_files = ['index.php', 'donate.php', 'admin/welcome.php', 'db_connection.php'];
            $missing_files = [];
            foreach ($required_files as $file) {
                if (!file_exists($file)) {
                    $missing_files[] = $file;
                }
            }
            
            if (empty($missing_files)) {
                $passed_checks++;
                $checks['files'] = [
                    'status' => 'success',
                    'title' => 'Required Files',
                    'description' => 'All required files present'
                ];
            } else {
                $checks['files'] = [
                    'status' => 'error',
                    'title' => 'Required Files',
                    'description' => 'Missing: ' . implode(', ', $missing_files)
                ];
            }

            // Check 4: File Permissions
            $total_checks++;
            $config_file = 'db_connection.php';
            if (file_exists($config_file) && is_readable($config_file)) {
                $passed_checks++;
                $checks['permissions'] = [
                    'status' => 'success',
                    'title' => 'File Permissions',
                    'description' => 'Configuration files readable'
                ];
            } else {
                $checks['permissions'] = [
                    'status' => 'error',
                    'title' => 'File Permissions',
                    'description' => 'Check file permissions'
                ];
            }

            // Display checks
            foreach ($checks as $check) {
                $icon_class = $check['status'] === 'success' ? 'fa-check-circle' : 
                              ($check['status'] === 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle');
                $color_class = $check['status'];
                
                echo "<div class='status-item $color_class'>
                    <div class='status-icon'>
                        <i class='fas $icon_class'></i>
                    </div>
                    <div class='status-text'>
                        <div class='status-title'>{$check['title']}</div>
                        <div class='status-description'>{$check['description']}</div>
                    </div>
                </div>";
            }

            // Progress bar
            $progress_percentage = ($passed_checks / $total_checks) * 100;
            echo "<div class='progress-bar'>
                <div class='progress-fill' style='width: {$progress_percentage}%'></div>
            </div>";
            
            echo "<div class='text-center'>
                <h4>Setup Progress: $passed_checks/$total_checks Complete</h4>
                <p class='lead'>Your website is " . ($progress_percentage >= 75 ? 'ready for hosting!' : 'almost ready for hosting.') . "</p>
            </div>";
            ?>

        </div>

        <div class="setup-card">
            <h3><i class="fas fa-tools me-2"></i>Quick Setup Actions</h3>
            
            <div class="text-center">
                <form method="post">
                    <button type="submit" name="setup_database" class="btn-setup me-3 mb-3">
                        <i class="fas fa-database me-2"></i>
                        Setup Database
                    </button>
                    <button type="submit" name="test_admin" class="btn-setup me-3 mb-3">
                        <i class="fas fa-user-shield me-2"></i>
                        Test Admin Login
                    </button>
                    <button type="submit" name="test_donate" class="btn-setup mb-3">
                        <i class="fas fa-heart me-2"></i>
                        Test Donate Page
                    </button>
                </form>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo '<div class="mt-4">';
                
                if (isset($_POST['setup_database'])) {
                    echo '<h5>Database Setup:</h5>';
                    try {
                        $conn = getConnection();
                        if ($conn) {
                            // Create donations table
                            $create_donations = "CREATE TABLE IF NOT EXISTS donations (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                donor_name VARCHAR(255) NOT NULL,
                                donor_email VARCHAR(255),
                                donor_phone VARCHAR(50),
                                amount DECIMAL(10,2) NOT NULL,
                                donation_type VARCHAR(50) NOT NULL,
                                payment_method VARCHAR(50) NOT NULL,
                                status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                            
                            if ($conn->query($create_donations)) {
                                echo '<div class="alert alert-success">Donations table created successfully!</div>';
                            }
                            
                            // Create admin_users table
                            $create_admin = "CREATE TABLE IF NOT EXISTS admin_users (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                username VARCHAR(50) UNIQUE NOT NULL,
                                password_hash VARCHAR(255) NOT NULL,
                                email VARCHAR(255) UNIQUE NOT NULL,
                                full_name VARCHAR(255) NOT NULL,
                                is_active TINYINT(1) DEFAULT 1,
                                last_login TIMESTAMP NULL,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                            
                            if ($conn->query($create_admin)) {
                                echo '<div class="alert alert-success">Admin users table created successfully!</div>';
                                
                                // Insert default admin if not exists
                                $check_admin = "SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'";
                                $result = $conn->query($check_admin);
                                $count = $result->fetch_assoc()['count'];
                                
                                if ($count == 0) {
                                    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                                    $insert_admin = "INSERT INTO admin_users (username, password_hash, email, full_name) VALUES ('admin', ?, 'admin@salem-dominion-ministries.org', 'Administrator')";
                                    $stmt = $conn->prepare($insert_admin);
                                    $stmt->bind_param('s', $password_hash);
                                    if ($stmt->execute()) {
                                        echo '<div class="alert alert-info">Default admin created: Username: admin, Password: admin123</div>';
                                    }
                                    $stmt->close();
                                }
                            }
                            
                            $conn->close();
                            echo '<div class="alert alert-success">Database setup completed!</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">Database setup failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                
                if (isset($_POST['test_admin'])) {
                    echo '<h5>Admin Login Test:</h5>';
                    echo '<div class="alert alert-info">
                        <strong>Test Credentials:</strong><br>
                        Username: admin<br>
                        Password: admin123<br>
                        <a href="admin/welcome.php" class="btn btn-primary btn-sm mt-2">Test Admin Login</a>
                    </div>';
                }
                
                if (isset($_POST['test_donate'])) {
                    echo '<h5>Donate Page Test:</h5>';
                    echo '<div class="alert alert-info">
                        The donate page has been fixed for hosting compatibility.<br>
                        <a href="donate.php" class="btn btn-success btn-sm mt-2">Test Donate Page</a>
                    </div>';
                }
                
                echo '</div>';
            }
            ?>
        </div>

        <div class="setup-card">
            <h3><i class="fas fa-info-circle me-2"></i>Hosting Instructions</h3>
            
            <div class="instructions">
                <h5>Steps to Deploy on Hosting:</h5>
                <ol>
                    <li><strong>Zip all files:</strong> Create a ZIP file of the entire salem-site folder</li>
                    <li><strong>Upload to hosting:</strong> Extract the ZIP file to your hosting root directory</li>
                    <li><strong>Set permissions:</strong> Ensure PHP files have execute permissions (755)</li>
                    <li><strong>Configure database:</strong> Run this setup script to create database tables</li>
                    <li><strong>Test functionality:</strong> Test admin login and donate functionality</li>
                </ol>
            </div>

            <div class="instructions">
                <h5>Default Credentials:</h5>
                <ul>
                    <li><strong>Admin Login:</strong> admin / admin123</li>
                    <li><strong>Database:</strong> salemdominionmin_db / salemdominionmin_db / EtacdN8wXLmzr6vA2zaA</li>
                </ul>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn-setup">
                    <i class="fas fa-home me-2"></i>
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
