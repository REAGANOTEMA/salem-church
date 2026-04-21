<?php
session_start();
require_once 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_username = $_SESSION['admin_username'] ?? 'admin';

// Initialize database connection
$conn = getConnection();
$db_status = [
    'connected' => false,
    'database_exists' => false,
    'tables_exist' => false,
    'admin_users_exist' => false,
    'error' => null
];

if ($conn) {
    $db_status['connected'] = true;
    try {
        // Check if database exists and has tables
        $result = $conn->query("SHOW TABLES");
        if ($result && $result->num_rows > 0) {
            $db_status['tables_exist'] = true;
            
            // Check if admin_users table exists
            $admin_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
            if ($admin_check && $admin_check->num_rows > 0) {
                $db_status['admin_users_exist'] = true;
            }
        }
    } catch (Exception $e) {
        $db_status['error'] = $e->getMessage();
    }
} else {
    $db_status['error'] = 'Database connection failed';
}

// Get basic statistics
$stats = [
    'users' => 0,
    'sermons' => 0,
    'events' => 0,
    'news' => 0,
    'testimonials' => 0
];

if ($conn && $db_status['tables_exist']) {
    try {
        $tables = ['users', 'sermons', 'events', 'news', 'testimonials'];
        foreach ($tables as $table) {
            $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            if ($result) {
                $stats[$table] = $result->fetch_assoc()['count'];
            }
        }
    } catch (Exception $e) {
        // Tables might not exist, continue with zeros
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Salem Dominion Ministries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            min-height: 100vh;
        }
        .dashboard-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fbbf24;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-online { background: #4ade80; }
        .status-offline { background: #f87171; }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #fbbf24 !important;
        }
        .nav-link.active {
            color: #fbbf24 !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
                    <p class="mb-0">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
                </div>
                <div class="col-md-6 text-end">
                    <span class="status-indicator <?php echo $db_status['connected'] ? 'status-online' : 'status-offline'; ?>"></span>
                    Database: <?php echo $db_status['connected'] ? 'Connected' : 'Disconnected'; ?>
                    <span class="ms-3">
                        <a href="admin/welcome.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-dark border-secondary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-bars me-2"></i>Navigation</h5>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="#"><i class="fas fa-home me-2"></i>Dashboard</a>
                            <a class="nav-link" href="#"><i class="fas fa-users me-2"></i>Users</a>
                            <a class="nav-link" href="#"><i class="fas fa-book me-2"></i>Sermons</a>
                            <a class="nav-link" href="#"><i class="fas fa-calendar me-2"></i>Events</a>
                            <a class="nav-link" href="#"><i class="fas fa-newspaper me-2"></i>News</a>
                            <a class="nav-link" href="#"><i class="fas fa-images me-2"></i>Gallery</a>
                            <a class="nav-link" href="#"><i class="fas fa-comments me-2"></i>Testimonials</a>
                            <a class="nav-link" href="#"><i class="fas fa-envelope me-2"></i>Messages</a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Database Status -->
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-database me-2"></i>System Status</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Database:</strong><br>
                                <span class="status-indicator <?php echo $db_status['connected'] ? 'status-online' : 'status-offline'; ?>"></span>
                                <?php echo $db_status['connected'] ? 'Connected' : 'Disconnected'; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Tables:</strong><br>
                                <span class="status-indicator <?php echo $db_status['tables_exist'] ? 'status-online' : 'status-offline'; ?>"></span>
                                <?php echo $db_status['tables_exist'] ? 'Ready' : 'Missing'; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Admin Users:</strong><br>
                                <span class="status-indicator <?php echo $db_status['admin_users_exist'] ? 'status-online' : 'status-offline'; ?>"></span>
                                <?php echo $db_status['admin_users_exist'] ? 'Available' : 'Missing'; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Environment:</strong><br>
                                <span class="status-indicator status-online"></span>
                                <?php echo (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) ? 'Localhost' : 'Hosting'; ?>
                            </div>
                        </div>
                        <?php if ($db_status['error']): ?>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($db_status['error']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-2-4">
                        <div class="stat-card">
                            <i class="fas fa-users fa-2x mb-3"></i>
                            <div class="stat-number"><?php echo $stats['users']; ?></div>
                            <div>Users</div>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="stat-card">
                            <i class="fas fa-book fa-2x mb-3"></i>
                            <div class="stat-number"><?php echo $stats['sermons']; ?></div>
                            <div>Sermons</div>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="stat-card">
                            <i class="fas fa-calendar fa-2x mb-3"></i>
                            <div class="stat-number"><?php echo $stats['events']; ?></div>
                            <div>Events</div>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="stat-card">
                            <i class="fas fa-newspaper fa-2x mb-3"></i>
                            <div class="stat-number"><?php echo $stats['news']; ?></div>
                            <div>News</div>
                        </div>
                    </div>
                    <div class="col-md-2-4">
                        <div class="stat-card">
                            <i class="fas fa-comments fa-2x mb-3"></i>
                            <div class="stat-number"><?php echo $stats['testimonials']; ?></div>
                            <div>Testimonials</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card bg-dark border-secondary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Add Sermon
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-2"></i>Add Event
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-info w-100">
                                    <i class="fas fa-plus me-2"></i>Add News
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-warning w-100">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
