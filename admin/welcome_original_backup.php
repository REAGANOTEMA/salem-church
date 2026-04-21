<?php
/**
 * Admin Welcome Page - Salem Dominion Ministries
 * Clean admin access point with professional design
 */

session_start();
require_once '../db_connection.php';

// Check if admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ../admin_dashboard.php');
    exit;
}

// Handle login attempt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $conn = getConnection();
            if ($conn) {
                // Check if admin_users table exists
                $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
                if ($table_check && $table_check->num_rows > 0) {
                    // Table exists, proceed with database authentication
                    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $admin = $result->fetch_assoc();
                        
                        if (password_verify($password, $admin['password'])) {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_user_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            $_SESSION['admin_name'] = $admin['full_name'];
                            $_SESSION['admin_role'] = $admin['role'];
                            $_SESSION['login_time'] = time();
                            
                            // Update last login
                            $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                            $update_stmt->bind_param("i", $admin['id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                            
                            header('Location: ../admin_dashboard.php');
                            exit;
                        }
                    }
                    
                    $stmt->close();
                    $conn->close();
                    $error = 'Invalid credentials. Please try again.';
                } else {
                    // Table doesn't exist, use fallback authentication
                    $conn->close();
                    
                    // Fallback authentication for initial setup
                    if ($username === 'MusasiziFaty' && $password === '123456') {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_user_id'] = 1;
                        $_SESSION['admin_username'] = $username;
                        $_SESSION['admin_name'] = 'Musasizi Faty';
                        $_SESSION['admin_role'] = 'super_admin';
                        $_SESSION['login_time'] = time();
                        
                        header('Location: ../admin_dashboard.php');
                        exit;
                    } else {
                        $error = 'Invalid credentials. Please try again.';
                    }
                }
            } else {
                // Database connection failed - try fallback authentication
                $error = 'Database connection failed. Using fallback authentication.';
                
                // Fallback authentication for initial setup
                if ($username === 'MusasiziFaty' && $password === '123456') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = 1;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_name'] = 'Musasizi Faty';
                    $_SESSION['admin_role'] = 'super_admin';
                    $_SESSION['login_time'] = time();
                    
                    header('Location: ../admin_dashboard.php');
                    exit;
                } else {
                    $error = 'Database connection failed. Invalid fallback credentials.';
                }
            }
        } catch (Exception $e) {
            $error = 'Database connection failed. Using fallback authentication.';
            
            // Fallback authentication for initial setup
            if ($username === 'MusasiziFaty' && $password === '123456') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user_id'] = 1;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_name'] = 'Musasizi Faty';
                $_SESSION['admin_role'] = 'super_admin';
                $_SESSION['login_time'] = time();
                
                header('Location: ../admin_dashboard.php');
                exit;
            } else {
                $error = 'Database connection failed. Invalid fallback credentials.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Salem Dominion Ministries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/mobile-responsive.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .admin-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .admin-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            padding: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .admin-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .admin-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .admin-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-admin {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .features {
            background: var(--light-color);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .feature-item i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }

        @media (max-width: 480px) {
            .admin-container {
                margin: 0 10px;
            }
            
            .admin-header {
                padding: 1.5rem;
            }
            
            .admin-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <img src="../public/logo-icon.jpeg" alt="Salem Dominion Ministries" class="admin-logo">
            <h1 class="admin-title">Admin Portal</h1>
            <p class="admin-subtitle">Salem Dominion Ministries</p>
        </div>
        
        <div class="admin-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php
            // Show helpful login info
         ?>
            <?php
            // Get detailed database status
            $db_status = getDatabaseStatus();
            
            if (!$db_status['mysql_connected']): ?>
                <div class="alert alert-danger" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none;">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>MySQL Server Not Running</strong><br>
                    <small>Please start XAMPP MySQL service and refresh this page.</small>
                </div>
            <?php elseif (!$db_status['database_exists']): ?>
                <div class="alert alert-warning" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; border: none;">
                    <i class="fas fa-database me-2"></i>
                    <strong>Database Setup Required</strong><br>
                    <small>The database needs to be created and configured. <a href="../setup_database_complete.php" style="color: white; text-decoration: underline;">Click here to set up the database automatically</a>.</small>
                </div>
            <?php elseif (!$db_status['tables_exist']): ?>
                <div class="alert alert-info" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; border: none;">
                    <i class="fas fa-table me-2"></i>
                    <strong>Database Tables Required</strong><br>
                    <small>Database exists but tables need to be imported. <a href="../import_database.php" style="color: white; text-decoration: underline;">Import database tables</a>.</small>
                </div>
            <?php elseif (!$db_status['admin_users_exist']): ?>
                <div class="alert alert-info" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; border: none;">
                    <i class="fas fa-users me-2"></i>
                    <strong>Admin Users Required</strong><br>
                    <small>Database tables exist but admin users need to be imported. <a href="../import_database.php" style="color: white; text-decoration: underline;">Import admin users</a>.</small>
                </div>
            <?php else: ?>
                <div class="alert alert-success" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Database Fully Configured</strong><br>
                    <small>Admin system is ready with complete database functionality.</small>
                </div>
            <?php endif; ?>
            
            <div class="features">
                <h5 style="margin-bottom: 1rem; color: var(--dark-color);">Admin Features:</h5>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Manage Sermons & Events</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Publish News & Updates</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>User Management</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Gallery & Media</span>
                </div>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           placeholder="Enter admin username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter admin password" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-admin">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Access Admin Panel
                </button>
            </form>
            
            <div class="back-link">
                <a href="../index.php">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>
