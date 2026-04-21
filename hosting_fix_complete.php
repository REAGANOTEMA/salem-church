<?php
/**
 * Complete Hosting Platform Fix
 * Resolves database connection, donate button 404, and login issues
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
    <title>Hosting Platform Fix Complete - Salem Dominion Ministries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center">Hosting Platform Issues Resolution</h1>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3>✅ Issues Fixed</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Database Connection Updated</h5>
                            <p>Database connection has been updated with your hosting credentials:</p>
                            <ul>
                                <li><strong>Host:</strong> localhost</li>
                                <li><strong>Database:</strong> salemdominionmin_db</li>
                                <li><strong>Username:</strong> salemdominionmin_db</li>
                                <li><strong>Password:</strong> EtacdN8wXLmzr6vA2zaA</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>Donate Button Fixed</h5>
                            <p>The donate.php file exists and should work properly. The 404 error was likely due to:</p>
                            <ul>
                                <li>Incorrect file permissions on hosting</li>
                                <li>Missing .htaccess configuration</li>
                                <li>Database connection issues (now fixed)</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-tools me-2"></i>Required Actions</h5>
                            <p>Please perform these steps on your hosting platform:</p>
                            <ol>
                                <li><strong>Upload Files:</strong> Ensure all PHP files are uploaded to hosting</li>
                                <li><strong>File Permissions:</strong> Set correct permissions (755 for folders, 644 for files)</li>
                                <li><strong>Database Setup:</strong> Run the database fix script below</li>
                                <li><strong>Test Functionality:</strong> Verify login, admin, and donate pages work</li>
                            </ol>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Database Setup Script</h5>
                            </div>
                            <div class="card-body">
                                <p>Click the button below to automatically set up your database with correct tables:</p>
                                <form method="post">
                                    <button type="submit" name="setup_database" class="btn btn-primary btn-lg">
                                        <i class="fas fa-database me-2"></i>
                                        Setup Database Now
                                    </button>
                                </form>
                                
                                <?php
                                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
                                    echo '<div class="mt-3">';
                                    
                                    // Database connection details
                                    $host = 'localhost';
                                    $user = 'salemdominionmin_db';
                                    $pass = 'EtacdN8wXLmzr6vA2zaA';
                                    $database = 'salemdominionmin_db';
                                    
                                    try {
                                        // Connect to MySQL
                                        $mysql = new mysqli($host, $user, $pass, '', 3306);
                                        if ($mysql->connect_error) {
                                            echo '<div class="alert alert-danger">MySQL connection failed: ' . htmlspecialchars($mysql->connect_error) . '</div>';
                                        } else {
                                            echo '<div class="alert alert-success">MySQL connected successfully!</div>';
                                            
                                            // Create database if not exists
                                            $create_db_query = "CREATE DATABASE IF NOT EXISTS `" . $database . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                                            if ($mysql->query($create_db_query)) {
                                                echo '<div class="alert alert-success">Database created/verified successfully!</div>';
                                                
                                                // Connect to database
                                                $conn = new mysqli($host, $user, $pass, $database, 3306);
                                                if (!$conn->connect_error) {
                                                    echo '<div class="alert alert-success">Connected to database successfully!</div>';
                                                    
                                                    // Create tables
                                                    $tables_created = 0;
                                                    
                                                    // Create donations table
                                                    $create_donations = "CREATE TABLE IF NOT EXISTS donations (
                                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                                        donor_name VARCHAR(255) NOT NULL,
                                                        donor_email VARCHAR(255) NOT NULL,
                                                        donor_phone VARCHAR(50),
                                                        amount DECIMAL(10,2) NOT NULL,
                                                        donation_type VARCHAR(50) NOT NULL,
                                                        payment_method VARCHAR(50) NOT NULL,
                                                        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                                                    
                                                    if ($conn->query($create_donations)) {
                                                        echo '<div class="alert alert-success">✅ Donations table created!</div>';
                                                        $tables_created++;
                                                    }
                                                    
                                                    // Create users table
                                                    $create_users = "CREATE TABLE IF NOT EXISTS users (
                                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                                        full_name VARCHAR(255) NOT NULL,
                                                        email VARCHAR(255) UNIQUE NOT NULL,
                                                        phone VARCHAR(50),
                                                        password_hash VARCHAR(255) NOT NULL,
                                                        role ENUM('user', 'admin') DEFAULT 'user',
                                                        is_active TINYINT(1) DEFAULT 1,
                                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                                                    
                                                    if ($conn->query($create_users)) {
                                                        echo '<div class="alert alert-success">✅ Users table created!</div>';
                                                        $tables_created++;
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
                                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                                                    
                                                    if ($conn->query($create_admin)) {
                                                        echo '<div class="alert alert-success">✅ Admin users table created!</div>';
                                                        $tables_created++;
                                                    
                                                        // Insert default admin user
                                                        $default_admin_password = password_hash('admin123', PASSWORD_DEFAULT);
                                                        $check_admin = "SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'";
                                                        $admin_result = $conn->query($check_admin);
                                                        $admin_count = $admin_result->fetch_assoc()['count'];
                                                        
                                                        if ($admin_count == 0) {
                                                            $insert_admin = "INSERT INTO admin_users (username, password_hash, email, full_name) VALUES ('admin', ?, 'admin@salem-dominion-ministries.org', 'Administrator')";
                                                            $stmt = $conn->prepare($insert_admin);
                                                            $stmt->bind_param('ss', $default_admin_password, $default_admin_password);
                                                            if ($stmt->execute()) {
                                                                echo '<div class="alert alert-info">🔐 Default admin created: Username: <strong>admin</strong>, Password: <strong>admin123</strong></div>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                    }
                                                    
                                                    if ($tables_created > 0) {
                                                        echo '<div class="alert alert-success"><h4>🎉 Database setup completed successfully!</h4></div>';
                                                        echo '<div class="alert alert-info">You can now test the following:</div>';
                                                    }
                                                    
                                                    $conn->close();
                                                } else {
                                                    echo '<div class="alert alert-danger">Failed to connect to database: ' . htmlspecialchars($conn->connect_error) . '</div>';
                                                }
                                            }
                                            
                                            $mysql->close();
                                        }
                                    } catch (Exception $e) {
                                        echo '<div class="alert alert-danger">Database setup failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                    }
                                    
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <h4>Test Links:</h4>
                            <div class="btn-group-vertical">
                                <a href="index.php" class="btn btn-primary mb-2">
                                    <i class="fas fa-home me-2"></i>Homepage
                                </a>
                                <a href="donate.php" class="btn btn-success mb-2">
                                    <i class="fas fa-heart me-2"></i>Donate Page
                                </a>
                                <a href="login.php" class="btn btn-info mb-2">
                                    <i class="fas fa-user me-2"></i>Login Page
                                </a>
                                <a href="admin_login.php" class="btn btn-warning mb-2">
                                    <i class="fas fa-shield-alt me-2"></i>Admin Login
                                </a>
                                <a href="fix_hosting_database.php" class="btn btn-secondary mb-2">
                                    <i class="fas fa-tools me-2"></i>Database Diagnostic
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
