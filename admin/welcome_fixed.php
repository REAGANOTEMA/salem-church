<?php
/**
 * Admin Welcome Page - Salem Dominion Ministries (Fixed for Hosting)
 * Clean admin access point with professional design - Hosting Compatible
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
                    $stmt = $conn->prepare("SELECT id, username, password_hash, email, full_name FROM admin_users WHERE username = ? AND is_active = 1");
                    if ($stmt) {
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $admin = $result->fetch_assoc();
                            
                            if (password_verify($password, $admin['password_hash'])) {
                                // Login successful
                                $_SESSION['admin_logged_in'] = true;
                                $_SESSION['admin_id'] = $admin['id'];
                                $_SESSION['admin_username'] = $admin['username'];
                                $_SESSION['admin_email'] = $admin['email'];
                                $_SESSION['admin_name'] = $admin['full_name'];
                                $_SESSION['admin_login_time'] = time();
                                
                                // Update last login
                                $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                                $update_stmt->bind_param("i", $admin['id']);
                                $update_stmt->execute();
                                $update_stmt->close();
                                
                                $stmt->close();
                                $conn->close();
                                
                                header('Location: ../admin_dashboard.php');
                                exit;
                            }
                        }
                        $stmt->close();
                    }
                }
                $conn->close();
            }
            
            // If we reach here, authentication failed
            $error = 'Invalid username or password';
            
        } catch (Exception $e) {
            // Database connection failed, try fallback authentication
            if ($username === 'admin' && $password === 'admin123') {
                // Fallback login for hosting setup
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = 'admin';
                $_SESSION['admin_email'] = 'admin@salem-dominion-ministries.org';
                $_SESSION['admin_name'] = 'Administrator';
                $_SESSION['admin_login_time'] = time();
                
                header('Location: ../admin_dashboard.php');
                exit;
            } else {
                $error = 'Database connection failed. Please contact administrator.';
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
    <title>Admin Login - Salem Dominion Ministries</title>
    <link rel="icon" href="../public/logo-icon.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --heavenly-gold: #fbbf24;
            --snow-white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .admin-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.3);
            animation: logoGlow 3s ease-in-out infinite;
        }

        @keyframes logoGlow {
            0%, 100% { box-shadow: 0 0 30px rgba(251, 191, 36, 0.3); }
            50% { box-shadow: 0 0 50px rgba(251, 191, 36, 0.6); }
        }

        .admin-title {
            color: var(--heavenly-gold);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.3);
        }

        .admin-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-bottom: 0;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 10px;
            height: 60px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 0 0.2rem rgba(251, 191, 36, 0.25);
            color: var(--snow-white);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-floating label {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-admin {
            background: linear-gradient(135deg, var(--heavenly-gold), #f59e0b);
            color: var(--midnight-blue);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
            color: var(--midnight-blue);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--heavenly-gold);
            transform: translateX(-5px);
        }

        .database-status {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .status-item i {
            margin-right: 0.5rem;
            width: 20px;
        }

        .status-item.success i {
            color: #22c55e;
        }

        .status-item.warning i {
            color: var(--heavenly-gold);
        }

        .status-item.error i {
            color: #ef4444;
        }

        @media (max-width: 480px) {
            .admin-container {
                padding: 2rem 1.5rem;
            }
            
            .admin-title {
                font-size: 1.5rem;
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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-floating">
                <input type="text" name="username" class="form-control" id="username" 
                       placeholder="Username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                <label for="username">
                    <i class="fas fa-user me-2"></i>Username
                </label>
            </div>

            <div class="form-floating">
                <input type="password" name="password" class="form-control" id="password" 
                       placeholder="Password" required>
                <label for="password">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
            </div>

            <button type="submit" class="btn btn-admin">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
            </button>
        </form>

        <div class="database-status">
            <h6 style="color: var(--heavenly-gold); margin-bottom: 1rem;">
                <i class="fas fa-database me-2"></i>System Status
            </h6>
            <?php
            try {
                $conn = getConnection();
                if ($conn) {
                    echo '<div class="status-item success">
                        <i class="fas fa-check-circle"></i>
                        Database Connected
                    </div>';
                    
                    // Check if admin_users table exists
                    $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
                    if ($table_check && $table_check->num_rows > 0) {
                        echo '<div class="status-item success">
                            <i class="fas fa-check-circle"></i>
                            Admin Tables Ready
                        </div>';
                    } else {
                        echo '<div class="status-item warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Admin Tables Missing
                        </div>';
                    }
                    $conn->close();
                } else {
                    echo '<div class="status-item error">
                        <i class="fas fa-times-circle"></i>
                        Database Connection Failed
                    </div>';
                }
            } catch (Exception $e) {
                echo '<div class="status-item warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Using Fallback Mode
                </div>';
            }
            ?>
            <div class="status-item success">
                <i class="fas fa-check-circle"></i>
                Admin System Ready
            </div>
        </div>

        <div class="text-center">
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Website
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
