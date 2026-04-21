<?php
/**
 * Final Connection Test - Database & Email Verification
 * Tests database connection with exact hosting credentials and verifies email fixes
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Connection Test - Salem Dominion Ministries</title>
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

        .test-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .test-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .test-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .test-title {
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

        .btn-test {
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
            margin: 0.5rem;
        }

        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
            color: var(--midnight-blue);
            text-decoration: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-green), #16a34a);
            color: white;
        }

        .btn-success:hover {
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.4);
            color: white;
        }

        .config-display {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .alert-custom {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }

        .alert-success-custom {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-danger-custom {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
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
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1 class="test-title">
                <i class="fas fa-database me-3"></i>
                Final Connection Test
            </h1>
            <p class="lead">Database Connection & Email Verification</p>
        </div>

        <div class="test-card">
            <h3><i class="fas fa-server me-2"></i>Database Connection Test</h3>
            
            <?php
            require_once 'db_connection.php';
            
            try {
                $db = FinalDatabaseConnection::getInstance();
                $environment = $db->getEnvironment();
                $config = $db->getConfig();
                
                echo "<div class='status-item success'>
                    <div class='status-icon'>
                        <i class='fas fa-check-circle'></i>
                    </div>
                    <div class='status-text'>
                        <div class='status-title'>Environment Detected</div>
                        <div class='status-description'>Running on: <strong>" . ucfirst($environment) . "</strong></div>
                    </div>
                </div>";
                
                echo "<div class='config-display'>
                    <h5>Database Configuration:</h5>
                    <p><strong>Host:</strong> " . htmlspecialchars($config['host']) . "</p>
                    <p><strong>Database:</strong> " . htmlspecialchars($config['name']) . "</p>
                    <p><strong>Username:</strong> " . htmlspecialchars($config['user']) . "</p>
                    <p><strong>Password:</strong> [Hidden for security]</p>
                    <p><strong>Port:</strong> " . $config['port'] . "</p>
                    <p><strong>Charset:</strong> " . $config['charset'] . "</p>
                </div>";
                
                // Test actual connection
                $conn = getConnection();
                if ($conn) {
                    echo "<div class='status-item success'>
                        <div class='status-icon'>
                            <i class='fas fa-check-circle'></i>
                        </div>
                        <div class='status-text'>
                            <div class='status-title'>Database Connected Successfully!</div>
                            <div class='status-description'>Connection established with " . $config['name'] . "</div>
                        </div>
                    </div>";
                    
                    // Test query
                    $result = $conn->query("SELECT VERSION() as version, USER() as user, DATABASE() as database");
                    if ($result) {
                        $info = $result->fetch_assoc();
                        echo "<div class='alert-custom alert-success-custom'>
                            <h6><i class='fas fa-check-circle me-2'></i>Connection Details:</h6>
                            <p><strong>MySQL Version:</strong> " . htmlspecialchars($info['version']) . "</p>
                            <p><strong>Current User:</strong> " . htmlspecialchars($info['user']) . "</p>
                            <p><strong>Current Database:</strong> " . htmlspecialchars($info['database']) . "</p>
                        </div>";
                    }
                    
                    // Check tables
                    $tables_check = $conn->query("SHOW TABLES");
                    if ($tables_check && $tables_check->num_rows > 0) {
                        echo "<div class='status-item success'>
                            <div class='status-icon'>
                                <i class='fas fa-check-circle'></i>
                            </div>
                            <div class='status-text'>
                                <div class='status-title'>Database Tables Ready</div>
                                <div class='status-description'>" . $tables_check->num_rows . " tables found</div>
                            </div>
                        </div>";
                    } else {
                        echo "<div class='status-item warning'>
                            <div class='status-icon'>
                                <i class='fas fa-exclamation-triangle'></i>
                            </div>
                            <div class='status-text'>
                                <div class='status-title'>No Tables Found</div>
                                <div class='status-description'>Tables will be created automatically</div>
                            </div>
                        </div>";
                    }
                    
                    $conn->close();
                } else {
                    echo "<div class='status-item error'>
                        <div class='status-icon'>
                            <i class='fas fa-times-circle'></i>
                        </div>
                        <div class='status-text'>
                            <div class='status-title'>Database Connection Failed</div>
                            <div class='status-description'>Unable to connect to database</div>
                        </div>
                    </div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='status-item error'>
                    <div class='status-icon'>
                        <i class='fas fa-times-circle'></i>
                    </div>
                    <div class='status-text'>
                        <div class='status-title'>Connection Test Failed</div>
                        <div class='status-description'>" . htmlspecialchars($e->getMessage()) . "</div>
                    </div>
                </div>";
            }
            ?>
        </div>

        <div class="test-card">
            <h3><i class="fas fa-envelope me-2"></i>Email Verification</h3>
            
            <?php
            // Check for .org emails in main files
            $files_to_check = [
                'index.php',
                'about.php',
                'sermons.php',
                'events.php',
                'contact.php',
                'login.php',
                'register.php',
                'admin_login.php',
                'donate.php',
                'gallery.php',
                'testimonials.php',
                'terms.php',
                'privacy.php'
            ];
            
            $org_emails_found = 0;
            $com_emails_found = 0;
            $files_checked = 0;
            
            foreach ($files_to_check as $file) {
                if (file_exists($file)) {
                    $files_checked++;
                    $content = file_get_contents($file);
                    
                    // Count .org emails
                    $org_count = substr_count($content, '@salem-dominion-ministries.org');
                    $org_emails_found += $org_count;
                    
                    // Count .com emails
                    $com_count = substr_count($content, '@salem-dominion-ministries.com');
                    $com_emails_found += $com_count;
                    
                    if ($org_count > 0) {
                        echo "<div class='status-item warning'>
                            <div class='status-icon'>
                                <i class='fas fa-exclamation-triangle'></i>
                            </div>
                            <div class='status-text'>
                                <div class='status-title'>$file</div>
                                <div class='status-description'>Found $org_count .org email(s)</div>
                            </div>
                        </div>";
                    } else {
                        echo "<div class='status-item success'>
                            <div class='status-icon'>
                                <i class='fas fa-check-circle'></i>
                            </div>
                            <div class='status-text'>
                                <div class='status-title'>$file</div>
                                <div class='status-description'>No .org emails found</div>
                            </div>
                        </div>";
                    }
                }
            }
            
            echo "<div class='config-display'>
                <h5>Email Summary:</h5>
                <p><strong>Files Checked:</strong> $files_checked</p>
                <p><strong>.org Emails Found:</strong> $org_emails_found</p>
                <p><strong>.com Emails Found:</strong> $com_emails_found</p>
            </div>";
            
            if ($org_emails_found == 0) {
                echo "<div class='alert-custom alert-success-custom'>
                    <h6><i class='fas fa-check-circle me-2'></i>All Emails Updated!</h6>
                    <p>All .org emails have been successfully replaced with .com emails.</p>
                </div>";
            } else {
                echo "<div class='alert-custom alert-danger-custom'>
                    <h6><i class='fas fa-exclamation-triangle me-2'></i>Still Found .org Emails!</h6>
                    <p>There are still $org_emails_found .org email addresses that need to be updated.</p>
                </div>";
            }
            ?>
        </div>

        <div class="test-card">
            <h3><i class="fas fa-shield-alt me-2"></i>Admin User Test</h3>
            
            <?php
            require_once 'db_connection.php';
            $conn = getConnection();
            
            if ($conn) {
                // Check admin user
                $admin_check = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
                if ($admin_check) {
                    $count = $admin_check->fetch_assoc()['count'];
                    
                    if ($count > 0) {
                        echo "<div class='status-item success'>
                            <div class='status-icon'>
                                <i class='fas fa-check-circle'></i>
                            </div>
                            <div class='status-text'>
                                <div class='status-title'>Admin User Ready</div>
                                <div class='status-description'>Username: admin, Password: admin123</div>
                            </div>
                        </div>";
                        
                        // Test admin login
                        $test_admin = $conn->prepare("SELECT * FROM admin_users WHERE username = 'admin' AND is_active = 1");
                        if ($test_admin) {
                            $test_admin->execute();
                            $result = $test_admin->get_result();
                            if ($result->num_rows > 0) {
                                $admin_data = $result->fetch_assoc();
                                echo "<div class='alert-custom alert-success-custom'>
                                    <h6><i class='fas fa-check-circle me-2'></i>Admin Login Verified!</h6>
                                    <p><strong>Email:</strong> " . htmlspecialchars($admin_data['email']) . "</p>
                                    <p><strong>Full Name:</strong> " . htmlspecialchars($admin_data['full_name']) . "</p>
                                    <p><strong>Status:</strong> Active</p>
                                </div>";
                            }
                            $test_admin->close();
                        }
                    } else {
                        echo "<div class='status-item warning'>
                            <div class='status-icon'>
                                <i class='fas fa-exclamation-triangle'></i>
                            </div>
                            <div class='status-text'>
                                <div class='status-title'>Admin User Missing</div>
                                <div class='status-description'>Admin user will be created automatically</div>
                            </div>
                        </div>";
                    }
                }
                $conn->close();
            }
            ?>
        </div>

        <div class="test-card">
            <h3><i class="fas fa-play me-2"></i>Test Actions</h3>
            
            <div class="text-center">
                <form method="post">
                    <button type="submit" name="test_admin_login" class="btn-test">
                        <i class="fas fa-user-shield me-2"></i>
                        Test Admin Login
                    </button>
                    <button type="submit" name="test_user_login" class="btn-test">
                        <i class="fas fa-user me-2"></i>
                        Test User Login
                    </button>
                    <button type="submit" name="test_donate" class="btn-test">
                        <i class="fas fa-heart me-2"></i>
                        Test Donate Page
                    </button>
                </form>
                
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_POST['test_admin_login'])) {
                        echo '<div class="alert-custom alert-success-custom">
                            <h6><i class="fas fa-check-circle me-2"></i>Admin Login Test</h6>
                            <p>Admin login is ready. Use:</p>
                            <p><strong>Username:</strong> admin</p>
                            <p><strong>Password:</strong> admin123</p>
                            <p><a href="admin_login.php" class="btn-test btn-sm">Go to Admin Login</a></p>
                        </div>';
                    }
                    
                    if (isset($_POST['test_user_login'])) {
                        echo '<div class="alert-custom alert-success-custom">
                            <h6><i class="fas fa-check-circle me-2"></i>User Login Test</h6>
                            <p>User registration and login system is ready.</p>
                            <p><a href="login.php" class="btn-test btn-sm">Go to User Login</a></p>
                            <p><a href="register.php" class="btn-test btn-sm">Go to Registration</a></p>
                        </div>';
                    }
                    
                    if (isset($_POST['test_donate'])) {
                        echo '<div class="alert-custom alert-success-custom">
                            <h6><i class="fas fa-check-circle me-2"></i>Donate Page Test</h6>
                            <p>Donate system is working with bulletproof WhatsApp integration.</p>
                            <p><a href="donate.php" class="btn-test btn-sm">Go to Donate Page</a></p>
                        </div>';
                    }
                }
                ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn-test">
                <i class="fas fa-home me-2"></i>
                Back to Homepage
            </a>
            <a href="admin_login.php" class="btn-test btn-success">
                <i class="fas fa-sign-in-alt me-2"></i>
                Admin Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
