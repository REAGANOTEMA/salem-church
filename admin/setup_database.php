<?php
/**
 * Database Setup Script for Admin
 * This script creates the necessary database tables for the admin system
 */

session_start();
require_once '../db_connection.php';

// Check if admin is already logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: welcome.php');
    exit;
}

$message = '';
$error = '';

// Handle database setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
    try {
        $conn = getConnection();
        if (!$conn) {
            throw new Exception("Database connection failed");
        }

        // SQL statements to create tables
        $tables = [
            // Admin users table
            "CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                role ENUM('admin', 'super_admin') DEFAULT 'admin',
                is_active TINYINT(1) DEFAULT 1,
                last_login DATETIME,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            // Regular users table
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                country VARCHAR(50),
                role ENUM('user', 'member') DEFAULT 'user',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            // Sermons table
            "CREATE TABLE IF NOT EXISTS sermons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                description TEXT,
                sermon_date DATE,
                speaker VARCHAR(100),
                video_url VARCHAR(255),
                audio_url VARCHAR(255),
                thumbnail VARCHAR(255),
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            // Events table
            "CREATE TABLE IF NOT EXISTS events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                description TEXT,
                event_date DATE NOT NULL,
                event_time TIME,
                location VARCHAR(255),
                max_attendees INT,
                status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            // News table
            "CREATE TABLE IF NOT EXISTS news (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                content TEXT NOT NULL,
                excerpt VARCHAR(500),
                featured_image VARCHAR(255),
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            // Ministries table
            "CREATE TABLE IF NOT EXISTS ministries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                category VARCHAR(50),
                leader_name VARCHAR(100),
                leader_email VARCHAR(100),
                meeting_schedule VARCHAR(255),
                status ENUM('active', 'inactive') DEFAULT 'active',
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )"
        ];

        $success_count = 0;
        $error_count = 0;

        foreach ($tables as $sql) {
            try {
                if ($conn->query($sql)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            } catch (Exception $e) {
                $error_count++;
            }
        }

        // Insert default admin user if not exists
        $admin_username = 'MusasiziFaty';
        $admin_password = '123456'; // This will be hashed
        $admin_email = 'admin@salem-dominion-ministries.org';
        $admin_full_name = 'Musasizi Faty';
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        $check_admin = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $check_admin->bind_param("s", $admin_username);
        $check_admin->execute();

        if ($check_admin->get_result()->num_rows == 0) {
            $insert_admin = $conn->prepare("INSERT INTO admin_users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'super_admin')");
            $insert_admin->bind_param("ssss", $admin_username, $hashed_password, $admin_email, $admin_full_name);
            
            if ($insert_admin->execute()) {
                $message = "Database setup completed successfully! $success_count tables created. Default admin user created.";
            } else {
                $error = "Error creating admin user.";
            }
            $insert_admin->close();
        } else {
            $message = "Database setup completed successfully! $success_count tables created. Admin user already exists.";
        }
        $check_admin->close();
        $conn->close();

    } catch (Exception $e) {
        $error = "Database setup failed: " . $e->getMessage();
    }
}

// Check database status
$conn = getConnection();
$database_status = '';
$tables_exist = false;

if ($conn) {
    try {
        $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
        if ($table_check && $table_check->num_rows > 0) {
            $tables_exist = true;
            $database_status = "Database tables exist and are ready.";
        } else {
            $database_status = "Database connection OK, but tables need to be created.";
        }
        $conn->close();
    } catch (Exception $e) {
        $database_status = "Database connection error: " . $e->getMessage();
    }
} else {
    $database_status = "Database connection failed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Salem Dominion Ministries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .setup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .setup-body {
            padding: 2rem;
        }
        
        .status-card {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .status-ok {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .status-warning {
            border-color: #f59e0b;
            background: #fffbeb;
        }
        
        .status-error {
            border-color: #ef4444;
            background: #fef2f2;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <img src="../public/logo-icon.jpeg" alt="Salem Dominion Ministries" style="width: 80px; height: 80px; border-radius: 50%; background: white; padding: 10px; margin-bottom: 1rem;">
            <h1>Database Setup</h1>
            <p>Salem Dominion Ministries Admin Panel</p>
        </div>
        
        <div class="setup-body">
            <div class="status-card <?php echo $tables_exist ? 'status-ok' : ($conn ? 'status-warning' : 'status-error'); ?>">
                <h5><i class="fas fa-database me-2"></i>Database Status</h5>
                <p><?php echo htmlspecialchars($database_status); ?></p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$tables_exist && $conn): ?>
                <form method="POST">
                    <div class="text-center">
                        <h5>Setup Required</h5>
                        <p class="mb-3">The database tables need to be created to use the admin panel.</p>
                        <button type="submit" name="setup_database" class="btn btn-primary btn-lg">
                            <i class="fas fa-database me-2"></i>
                            Setup Database Tables
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="welcome.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                </a>
                <?php if ($tables_exist): ?>
                    <a href="../admin_dashboard.php" class="btn btn-success">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
