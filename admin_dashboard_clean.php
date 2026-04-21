<?php
// ADMIN DASHBOARD - Salem Dominion Ministries
// Complete content management system for Pastor Faty Musasizi
session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Pastor Faty Musasizi';

// Initialize variables
$success = '';
$error = '';
$active_section = $_GET['section'] ?? 'dashboard';

// Initialize database connection
$conn = getConnection();
$db_connected = ($conn !== null);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_sermon':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sermon_date = $_POST['sermon_date'] ?? '';
            $category = $_POST['category'] ?? '';
            $media_type = $_POST['media_type'] ?? 'video';
            $sermon_series = trim($_POST['sermon_series'] ?? '');
            
            if (empty($title) || empty($description) || empty($sermon_date)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (!$conn) {
                    $error = 'Database connection required. Please check database configuration.';
                } else {
                    $success = 'Sermon added successfully!';
                }
            }
            break;
            
        case 'add_event':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $event_time = $_POST['event_time'] ?? '';
            $location = trim($_POST['location'] ?? '');
            
            if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (!$conn) {
                    $error = 'Database connection required. Please check database configuration.';
                } else {
                    $success = 'Event added successfully!';
                }
            }
            break;
            
        case 'add_news':
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($title) || empty($content)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (!$conn) {
                    $error = 'Database connection required. Please check database configuration.';
                } else {
                    $success = 'News added successfully!';
                }
            }
            break;
            
        case 'add_gallery':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $media_type = $_POST['media_type'] ?? 'image';
            
            if (empty($title) || empty($description)) {
                $error = 'Please fill in all required fields.';
            } else {
                if (!$conn) {
                    $error = 'Database connection required. Please check database configuration.';
                } else {
                    $success = 'Gallery item added successfully!';
                }
            }
            break;
            
        case 'delete_item':
            $item_type = $_POST['item_type'] ?? '';
            $item_id = intval($_POST['item_id'] ?? 0);
            
            if (!$conn) {
                $error = 'Database connection required. Please check database configuration.';
            } else {
                try {
                    switch ($item_type) {
                        case 'sermon':
                        $stmt = $conn->prepare("DELETE FROM sermons WHERE id = ?");
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $success = 'Sermon deleted successfully!';
                            break;
                            
                        case 'event':
                            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $success = 'Event deleted successfully!';
                            break;
                            
                        case 'news':
                            $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $success = 'News deleted successfully!';
                            break;
                            
                        case 'gallery':
                            $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
                            $stmt->bind_param("i", $item_id);
                            $stmt->execute();
                            $success = 'Gallery item deleted successfully!';
                            break;
                    }
                } catch (Exception $e) {
                    $error = 'Failed to delete item: ' . $e->getMessage();
                }
            }
            break;
    }
}

// Get statistics
$stats = [];
if ($conn) {
    try {
        $stats['users'] = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $stats['sermons'] = $conn->query("SELECT COUNT(*) as count FROM sermons")->fetch_assoc()['count'];
        $stats['events'] = $conn->query("SELECT COUNT(*) as count FROM events WHERE status != 'deleted'")->fetch_assoc()['count'];
        $stats['news'] = $conn->query("SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
        $stats['gallery'] = $conn->query("SELECT COUNT(*) as count FROM gallery")->fetch_assoc()['count'];
        $stats['testimonials'] = $conn->query("SELECT COUNT(*) as count FROM testimonials")->fetch_assoc()['count'];
    } catch (Exception $e) {
        $stats = ['users' => 0, 'sermons' => 0, 'events' => 0, 'news' => 0, 'gallery' => 0, 'testimonials' => 0];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Salem Dominion Ministries</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a;
            --secondary-color: #0ea5e9;
            --accent-color: #fbbf24;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-color);
            color: var(--primary-color);
            line-height: 1.6;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 280px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .admin-main {
            flex: 1;
            padding: 2rem;
            background: var(--light-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }

        .stat-label {
            color: var(--secondary-color);
            font-weight: 500;
        }

        .content-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: var(--success-color);
            color: white;
            border: 1px solid var(--success-color);
        }

        .alert-danger {
            background: var(--danger-color);
            color: white;
            border: 1px solid var(--danger-color);
        }

        .alert-warning {
            background: var(--warning-color);
            color: white;
            border: 1px solid var(--warning-color);
        }

        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 0.5rem;
        }

        .nav-link:hover {
            background: var(--light-color);
            color: var(--secondary-color);
        }

        .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }

        .logout-btn {
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }
            
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 80%;
                max-width: 280px;
                height: 100vh;
                z-index: 999;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.mobile-open {
                left: 0;
            }
            
            .admin-main {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .mobile-menu-toggle {
            display: none;
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
        }

        .mobile-menu-toggle:hover {
            background: var(--secondary-color);
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        .mobile-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                </div>
                <div>
                    <span><i class="fas fa-user-shield me-2"></i><?php echo htmlspecialchars($admin_name); ?></span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="p-4">
                <h5 style="color: var(--accent-color); margin-bottom: 1rem;">
                    <i class="fas fa-crown"></i> Admin Menu
                </h5>
                <nav>
                    <a href="?section=dashboard" class="nav-link <?php echo $active_section === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                    <a href="?section=sermons" class="nav-link <?php echo $active_section === 'sermons' ? 'active' : ''; ?>">
                        <i class="fas fa-microphone-alt"></i>Sermons
                    </a>
                    <a href="?section=events" class="nav-link <?php echo $active_section === 'events' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar"></i>Events
                    </a>
                    <a href="?section=news" class="nav-link <?php echo $active_section === 'news' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i>News
                    </a>
                    <a href="?section=gallery" class="nav-link <?php echo $active_section === 'gallery' ? 'active' : ''; ?>">
                        <i class="fas fa-images"></i>Gallery
                    </a>
                    <a href="?section=testimonials" class="nav-link <?php echo $active_section === 'testimonials' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i>Testimonials
                    </a>
                    <a href="?section=messages" class="nav-link <?php echo $active_section === 'messages' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i>Messages
                    </a>
                    <a href="?section=users" class="nav-link <?php echo $active_section === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>Users Management
                    </a>
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>View Website
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Statistics -->
            <?php if ($active_section === 'dashboard'): ?>
                <div class="content-section">
                    <h2 class="section-title">
                        <i class="fas fa-chart-bar me-2"></i>Dashboard Statistics
                    </h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats['users'] ?? 0); ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-microphone-alt"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats['sermons'] ?? 0); ?></div>
                            <div class="stat-label">Total Sermons</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats['events'] ?? 0); ?></div>
                            <div class="stat-label">Total Events</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats['news'] ?? 0); ?></div>
                            <div class="stat-label">Total News</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats['gallery'] ?? 0); ?></div>
                            <div class="stat-label">Gallery Items</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="stat-number"><?php echo number_format($stats['testimonials'] ?? 0); ?></div>
                            <div class="stat-label">Testimonials</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Database Status -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-database me-2"></i>Database Status
                </h2>
                
                <div class="alert <?php echo $db_connected ? 'alert-success' : 'alert-danger'; ?>">
                    <?php if ($db_connected): ?>
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Database Connected</strong><br>
                        <small>All admin features are available with real-time data.</small>
                    <?php else: ?>
                        <i class="fas fa-times-circle me-2"></i>
                        <strong>Database Offline</strong><br>
                        <small>Dashboard is running in offline mode. Some features may be limited.</small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-section">
                <h2 class="section-title">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h2>
                
                <div class="stats-grid">
                    <a href="?section=sermons" class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="stat-label">Add Sermon</div>
                    </a>
                    
                    <a href="?section=events" class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="stat-label">Add Event</div>
                    </a>
                    
                    <a href="?section=news" class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="stat-label">Add News</div>
                    </a>
                    
                    <a href="?section=gallery" class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="stat-label">Upload Media</div>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile Menu Functionality
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const sidebar = document.querySelector('.admin-sidebar');
        
        if (mobileMenuToggle && mobileOverlay && sidebar) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
            });
            
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 3000);
    </script>
</body>
</html>
