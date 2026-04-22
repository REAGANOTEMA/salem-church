<?php
/**
 * Salem Dominion Ministries - Main Homepage
 * This is the main entry point for the website
 */

session_start();
require_once 'db_connection.php';

// Check if admin is logged in
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';

// Get latest sermons, events, and news for homepage
$latest_sermons = [];
$latest_events = [];
$latest_news = [];
$database_ready = true;

// Initialize database connection
$conn = createDatabaseConnection();

if ($conn) {
    try {
        // Get latest sermons
        $stmt = $conn->prepare("SELECT * FROM sermons WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $latest_sermons = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        // Get upcoming events
        $stmt = $conn->prepare("SELECT * FROM events WHERE status = 'upcoming' ORDER BY event_date ASC LIMIT 3");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $latest_events = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        // Get latest news
        $stmt = $conn->prepare("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $latest_news = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

    } catch(Exception $e) {
        // Database not set up yet - show welcome message
        $database_ready = false;
        error_log("Database query error: " . $e->getMessage());
    }
    
    $conn->close();
} else {
    $database_ready = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salem Dominion Ministries - Welcome</title>
    
    <!-- PWA Meta Tags - Universal Device Support -->
    <meta name="description" content="Welcome to Salem Dominion Ministries - Your spiritual home in Nampirika, Iganga District. Join us for worship, prayer, and community.">
    <meta name="theme-color" content="#fbbf24">
    
    <!-- Search Engine and Social Media Meta Tags -->
    <meta name="keywords" content="Salem Dominion Ministries, church, worship, prayer, Iganga, Uganda, Apostle Faty Musasizi, Christian, ministry, sermons, events">
    <meta name="author" content="Salem Dominion Ministries">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="Salem Dominion Ministries - Your Spiritual Home">
    <meta property="og:description" content="Welcome to Salem Dominion Ministries - Your spiritual home in Nampirika, Iganga District. Join us for worship, prayer, and community.">
    <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/logo-icon.jpeg">
    <meta property="og:image:secure_url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/logo-icon.jpeg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="Salem Dominion Ministries Logo">
    <meta property="og:site_name" content="Salem Dominion Ministries">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@SalemDominion">
    <meta name="twitter:creator" content="@SalemDominion">
    <meta name="twitter:title" content="Salem Dominion Ministries - Your Spiritual Home">
    <meta name="twitter:description" content="Welcome to Salem Dominion Ministries - Your spiritual home in Nampirika, Iganga District. Join us for worship, prayer, and community.">
    <meta name="twitter:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/logo-icon.jpeg">
    <meta name="twitter:image:alt" content="Salem Dominion Ministries Logo">
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Salem Dominion Ministries",
        "url": "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>",
        "logo": "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/logo-icon.jpeg",
        "description": "Salem Dominion Ministries - Your spiritual home in Nampirika, Iganga District. Join us for worship, prayer, and community.",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "Nampirika",
            "addressRegion": "Iganga District",
            "addressCountry": "Uganda"
        },
        "sameAs": [
            "<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>"
        ]
    }
    </script>
    
    <!-- iOS and Safari Support -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SDM">
    <meta name="apple-touch-fullscreen" content="yes">
    
    <!-- Universal App Support -->
    <meta name="application-name" content="Salem Dominion Ministries">
    <meta name="msapplication-TileColor" content="#fbbf24">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <meta name="msapplication-TileImage" content="public/logo-icon.jpeg">
    
    <!-- Viewport and Responsive Design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="email=no">
    <meta name="format-detection" content="address=no">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="public/site.webmanifest">
    
    <!-- Favicon - Church Logo Only -->
    <link rel="icon" href="public/logo-icon.jpeg">
    <link rel="shortcut icon" href="public/logo-icon.jpeg">
    
    <!-- Android Specific -->
    <meta name="theme-color" content="#fbbf24">
    <meta name="background-color" content="#0f172a">
    
    <!-- PWA Service Worker -->
    <script>
        // Register Service Worker for Universal Device Support
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/public/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker registered successfully:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
        
        // iOS PWA Install Prompt
        let deferredPrompt;
        let isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        
        // Show install prompt for iOS
        if (isIOS && !window.navigator.standalone) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    showIOSInstallPrompt();
                }, 3000);
            });
        }
        
        // Android PWA Install Prompt
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            showAndroidInstallPrompt();
        });
        
        function showIOSInstallPrompt() {
            const prompt = document.createElement('div');
            prompt.className = 'pwa-install-prompt show';
            prompt.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-download"></i>
                    <span>Install this app on your iPhone: tap 
                        <i class="fas fa-share-square"></i> then "Add to Home Screen"
                    </span>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(prompt);
            
            setTimeout(function() {
                if (prompt.parentElement) {
                    prompt.remove();
                }
            }, 10000);
        }
        
        function showAndroidInstallPrompt() {
            const prompt = document.createElement('div');
            prompt.className = 'pwa-install-prompt show';
            prompt.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-download"></i>
                    <span>Install this app for a better experience</span>
                    <button onclick="installApp()" style="background: rgba(255,255,255,0.2); border: 1px solid white; color: white; padding: 5px 10px; border-radius: 5px; margin-right: 10px;">
                        Install
                    </button>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 20px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(prompt);
            
            setTimeout(function() {
                if (prompt.parentElement) {
                    prompt.remove();
                }
            }, 15000);
        }
        
        function installApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        }
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/mobile-responsive.css" rel="stylesheet">
    <link href="assets/universal-device-support.css" rel="stylesheet">
    <style>
        /* Professional Styling */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .hero-section .container {
            position: relative;
            z-index: 1;
        }
        
        .section-title {
            color: #764ba2;
            font-weight: bold;
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        .card-hover {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 0 20px;
            margin-top: 80px;
        }
        
        .footer-widget h4 {
            position: relative;
            padding-bottom: 15px;
        }
        
        .footer-widget h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: #667eea;
        }
        
        .footer-links li {
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .footer-links li:hover {
            transform: translateX(5px);
        }
        
        .footer-links a:hover {
            color: #667eea !important;
        }
        
        .social-icon {
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
            color: #667eea !important;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-lg {
            padding: 15px 35px;
            font-size: 1.1rem;
        }
        
        .btn-sm {
            padding: 8px 20px;
            font-size: 0.9rem;
        }
        
        /* Enhanced Mobile Responsive Design */
        @media (max-width: 1200px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero-section h1 {
                font-size: 2.2rem;
            }
            
            .hero-section .lead {
                font-size: 1.1rem;
            }
            
            .navbar-brand span {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
                text-align: center;
            }
            
            .hero-section h1 {
                font-size: 2rem;
                line-height: 1.2;
                margin-bottom: 1rem;
            }
            
            .hero-section .lead {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .hero-section .row {
                text-align: center;
            }
            
            .btn-lg {
                padding: 12px 25px;
                font-size: 0.9rem;
                margin: 5px;
                display: block;
                width: 100%;
                max-width: 250px;
            }
            
            .navbar {
                padding: 0.5rem 0;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .navbar-brand span {
                font-size: 1rem;
            }
            
            .navbar-brand img {
                height: 30px !important;
            }
            
            .hero-logo {
                max-height: 80px !important;
            }
            
            .footer-logo {
                max-height: 60px !important;
            }
            
            .navbar-nav .nav-link {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
            
            .btn-sm {
                padding: 6px 15px;
                font-size: 0.8rem;
                margin: 2px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section {
                padding: 40px 0;
            }
            
            .hero-section h1 {
                font-size: 1.8rem;
            }
            
            .hero-section .lead {
                font-size: 0.9rem;
            }
            
            .btn-lg {
                padding: 10px 20px;
                font-size: 0.85rem;
                margin-bottom: 10px;
            }
            
            .navbar-brand span {
                font-size: 0.9rem;
            }
            
            .container {
                padding: 0 15px;
            }
        }
        
        @media (max-width: 480px) {
            .hero-section h1 {
                font-size: 1.5rem;
            }
            
            .hero-section .lead {
                font-size: 0.85rem;
            }
            
            .btn-lg {
                padding: 8px 16px;
                font-size: 0.8rem;
            }
            
            .navbar-brand span {
                font-size: 0.8rem;
            }
        }
        
        /* Mobile Navigation Improvements */
        @media (max-width: 768px) {
            .navbar-nav {
                background: rgba(0,0,0,0.9);
                padding: 1rem;
                border-radius: 10px;
                margin-top: 1rem;
            }
            
            .navbar-nav .nav-item {
                margin-bottom: 0.5rem;
            }
            
            .dropdown-menu {
                background: rgba(0,0,0,0.8);
                border: 1px solid rgba(255,255,255,0.1);
            }
            
            .dropdown-item {
                color: white !important;
                padding: 0.5rem 1rem;
            }
            
            /* Donate Button Mobile Styles */
            .donate-btn-mobile {
                min-height: 44px !important;
                min-width: 44px !important;
                padding: 12px 20px !important;
                font-size: 16px !important;
                font-weight: 600 !important;
                border-radius: 8px !important;
                transition: all 0.3s ease !important;
                text-decoration: none !important;
                display: inline-block !important;
                touch-action: manipulation !important;
            }
            
            .donate-btn-mobile:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3) !important;
            }
            
            .donate-btn-mobile:active {
                transform: translateY(0) !important;
            }
        }
        
        @media (max-width: 576px) {
            .donate-btn-mobile {
                width: 100% !important;
                max-width: 280px !important;
                margin: 0 auto !important;
                display: block !important;
                padding: 14px 24px !important;
                font-size: 16px !important;
            }
        }
        
        @media (max-width: 480px) {
            .donate-btn-mobile {
                padding: 12px 20px !important;
                font-size: 15px !important;
                max-width: 250px !important;
            }
        }
        
        /* Logo Styles */
        .navbar-brand img {
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar-brand img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .hero-logo {
            animation: logoFloat 6s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }
        
        .footer-logo {
            transition: all 0.3s ease;
        }
        
        .footer-logo:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.4);
        }

        /* Touch-friendly adjustments */
        @media (hover: none) and (pointer: coarse) {
            .btn {
                min-height: 44px;
                min-width: 44px;
            }
            
            .nav-link {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Professional Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo" class="me-2" style="height: 40px; width: auto; border-radius: 50%; object-fit: cover;">
                <span class="fw-bold">Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
                    <li class="nav-item"><a class="nav-link" href="leadership.php"><i class="fas fa-users me-1"></i> Leadership</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ministriesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-hands-helping me-1"></i> Ministries
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="ministries.php">All Ministries</a></li>
                            <li><a class="dropdown-item" href="children_ministry.php">Children Ministry</a></li>
                            <li><a class="dropdown-item" href="prophetic-school.php">Prophetic School</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="mediaDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-play-circle me-1"></i> Media
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="sermons.php">Sermons</a></li>
                            <li><a class="dropdown-item" href="gallery.php">Gallery</a></li>
                            <li><a class="dropdown-item" href="news.php">News & Updates</a></li>
                            <li><a class="dropdown-item" href="testimonials.php">Testimonials</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="events.php"><i class="fas fa-calendar-alt me-1"></i> Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_pastor_call.php"><i class="fas fa-phone-alt me-1"></i> Book Pastor</a></li>
                    <li class="nav-item"><a class="nav-link" href="donate.php"><i class="fas fa-heart me-1"></i> Donate</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact</a></li>
                    <?php if ($admin_logged_in): ?>
                        <!-- Admin Logged In -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-warning btn-sm text-dark" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-shield-alt me-1"></i> <?php echo htmlspecialchars($admin_name); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Admin Panel</h6></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=sermons">
                                    <i class="fas fa-book me-2"></i>Manage Sermons
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=events">
                                    <i class="fas fa-calendar me-2"></i>Manage Events
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=news">
                                    <i class="fas fa-newspaper me-2"></i>Manage News
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=users">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Not Logged In -->
                        <li class="nav-item ms-2">
                            <a href="login.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-user me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item ms-1">
                            <a href="register.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                        <li class="nav-item ms-1">
                            <a href="admin_login.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-shield-alt me-1"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin Welcome Section (only shown when admin is logged in) -->
    <?php if ($admin_logged_in): ?>
    <section class="py-4 bg-success text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Welcome back, <?php echo htmlspecialchars($admin_name); ?>! 
                        <span class="badge bg-warning text-dark ms-2"><?php echo htmlspecialchars(ucfirst($admin_role)); ?></span>
                    </h4>
                    <p class="mb-0 mt-1">You have full administrative access to manage the website content.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="admin_dashboard.php" class="btn btn-warning btn-sm me-2">
                        <i class="fas fa-tachometer-alt me-1"></i> Admin Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="mb-4">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo" class="img-fluid hero-logo" style="max-height: 120px; width: auto; border-radius: 50%; object-fit: cover; box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);">
            </div>
            <?php if ($admin_logged_in): ?>
                <h1 class="display-4 fw-bold mb-4">Admin Dashboard View</h1>
                <p class="lead mb-4">Managing Salem Dominion Ministries Website</p>
                <p class="mb-4">"For the kingdom of God is not a matter of talk but of power." - 1 Corinthians 4:20</p>
            <?php else: ?>
                <h1 class="display-4 fw-bold mb-4">Welcome to Salem Dominion Ministries</h1>
                <p class="lead mb-4">Empowering lives through the Word of God and the Power of the Holy Spirit</p>
                <p class="mb-4">"For the kingdom of God is not a matter of talk but of power." - 1 Corinthians 4:20</p>
            <?php endif; ?>
            <div class="mt-4">
                <div class="row justify-content-center">
                    <?php if ($admin_logged_in): ?>
                        <!-- Admin Quick Actions -->
                        <div class="col-md-auto mb-2">
                            <a href="admin_dashboard.php" class="btn btn-warning btn-lg me-2 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="admin_dashboard.php?section=sermons" class="btn btn-light btn-lg me-2 mb-2">
                                <i class="fas fa-book me-2"></i> Add Sermon
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="admin_dashboard.php?section=events" class="btn btn-light btn-lg me-2 mb-2">
                                <i class="fas fa-calendar-plus me-2"></i> Add Event
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="admin_dashboard.php?section=news" class="btn btn-light btn-lg me-2 mb-2">
                                <i class="fas fa-newspaper me-2"></i> Add News
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="admin_dashboard.php?section=users" class="btn btn-light btn-lg me-2 mb-2">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Regular User Actions -->
                        <div class="col-md-auto mb-2">
                            <a href="sermons.php" class="btn btn-light btn-lg me-2 mb-2">
                                <i class="fas fa-play-circle me-2"></i> Watch Sermons
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="events.php" class="btn btn-outline-light btn-lg me-2 mb-2">
                                <i class="fas fa-calendar-alt me-2"></i> Upcoming Events
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="donate.php" class="btn btn-warning btn-lg me-2 mb-2 donate-btn-mobile">
                                <i class="fas fa-heart me-2"></i> Give Now
                            </a>
                        </div>
                        <div class="col-md-auto mb-2">
                            <a href="book_pastor_call.php" class="btn btn-success btn-lg mb-2">
                                <i class="fas fa-phone-alt me-2"></i> Book Pastor
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    
    <!-- Latest Sermons -->
    <?php if (!empty($latest_sermons)): ?>
    <section class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Latest Sermons</h2>
            <div class="row">
                <?php foreach ($latest_sermons as $sermon): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-hover h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($sermon['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($sermon['description']), 0, 100) . '...'; ?></p>
                            <p class="text-muted small">
                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($sermon['sermon_date'])); ?>
                            </p>
                            <a href="sermons.php" class="btn btn-primary">Watch Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Upcoming Events -->
    <?php if (!empty($latest_events)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center">Upcoming Events</h2>
            <div class="row">
                <?php foreach ($latest_events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-hover h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($event['description']), 0, 100) . '...'; ?></p>
                            <p class="text-muted small">
                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                <i class="fas fa-clock ms-2"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                            </p>
                            <a href="events.php" class="btn btn-success">Register Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Latest News -->
    <?php if (!empty($latest_news)): ?>
    <section class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Latest News</h2>
            <div class="row">
                <?php foreach ($latest_news as $news): ?>
                <div class="col-md-4 mb-4">
                    <div class="card card-hover h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($news['content']), 0, 100) . '...'; ?></p>
                            <p class="text-muted small">
                                <i class="fas fa-newspaper"></i> <?php echo date('M j, Y', strtotime($news['created_at'])); ?>
                            </p>
                            <a href="news.php" class="btn btn-info">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quick Links -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center">Quick Links</h2>
            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-pray fa-3x text-primary mb-3"></i>
                            <h5>Prayer Requests</h5>
                            <p>Submit your prayer requests and join us in prayer</p>
                            <a href="contact.php" class="btn btn-primary">Pray Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-hand-holding-heart fa-3x text-success mb-3"></i>
                            <h5>Give</h5>
                            <p>Support our ministry and help us reach more people</p>
                            <a href="donate.php" class="btn btn-success donate-btn-mobile">Donate</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-graduation-cap fa-3x text-warning mb-3"></i>
                            <h5>Prophetic School</h5>
                            <p>Join our prophetic training programs</p>
                            <a href="prophetic-school.php" class="btn btn-warning">Apply Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x text-info mb-3"></i>
                            <h5>Get Involved</h5>
                            <p>Join our ministries and serve the community</p>
                            <a href="ministries.php" class="btn btn-info">Join Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Professional Footer with 3D Effects -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <div class="text-center mb-3">
                            <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo" class="img-fluid footer-logo" style="max-height: 80px; width: auto; border-radius: 50%; object-fit: cover; box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);">
                        </div>
                        <h4 class="text-white mb-3 footer-title-3d text-center">
                            Salem Dominion Ministries
                        </h4>
                        <p class="text-white-50">Empowering lives through the Word of God and the Power of the Holy Spirit. Join us in spreading the Gospel and making disciples.</p>
                        <div class="mt-3">
                            <a href="https://Salemdominionministries.com" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fas fa-globe fa-lg"></i>
                            </a>
                            <a href="https://youtube.com/@musasizifaty?si=BxEArdVKNKVSac3X" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fab fa-youtube fa-lg"></i>
                            </a>
                            <a href="https://www.tiktok.com/@salem1dominionchurch?_r=1&_t=ZS-95E1n40LieS" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fab fa-tiktok fa-lg"></i>
                            </a>
                            <a href="https://www.facebook.com/share/1CoCEmvnBB/" target="_blank" class="text-white social-icon-3d">
                                <i class="fab fa-facebook fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h5 class="text-white mb-3 footer-title-3d">Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="about.php" class="text-white-50 text-decoration-none footer-link-3d">About Us</a></li>
                            <li><a href="leadership.php" class="text-white-50 text-decoration-none footer-link-3d">Leadership</a></li>
                            <li><a href="sermons.php" class="text-white-50 text-decoration-none footer-link-3d">Sermons</a></li>
                            <li><a href="events.php" class="text-white-50 text-decoration-none footer-link-3d">Events</a></li>
                            <li><a href="ministries.php" class="text-white-50 text-decoration-none footer-link-3d">Ministries</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h5 class="text-white mb-3 footer-title-3d">Services</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="prophetic-school.php" class="text-white-50 text-decoration-none footer-link-3d">Prophetic School</a></li>
                            <li><a href="book_pastor_call.php" class="text-white-50 text-decoration-none footer-link-3d">Book Pastor Call</a></li>
                            <li><a href="children_ministry.php" class="text-white-50 text-decoration-none footer-link-3d">Children Ministry</a></li>
                            <li><a href="donate.php" class="text-white-50 text-decoration-none footer-link-3d">Give & Donate</a></li>
                            <li><a href="testimonials.php" class="text-white-50 text-decoration-none footer-link-3d">Testimonials</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h5 class="text-white mb-3 footer-title-3d">Contact Info</h5>
                        <ul class="list-unstyled footer-contact">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <span class="text-white-50">Nampirika, Iganga District, Uganda</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                <span class="text-white-50">+256 753 244 480</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <span class="text-white-50">info@salem-dominion-ministries.com</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-globe me-2 text-primary"></i>
                                <span class="text-white-50">www.salemdominionministries.com</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <hr class="bg-white my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white-50 me-3 text-decoration-none">Privacy Policy</a>
                    <a href="terms.php" class="text-white-50 text-decoration-none">Terms of Service</a>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <div class="designer-credit-3d">
                        <p class="text-white-50 mb-2">
                            <i class="fas fa-code me-2"></i>Designed & Developed by
                        </p>
                        <h5 class="designer-name-3d text-warning mb-2">Mr. Reagan Otema</h5>
                        <a href="https://wa.me/256772514889" target="_blank" class="designer-contact-3d">
                            <i class="fab fa-whatsapp me-2"></i>+256 772 514 889
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        /* 3D Footer Effects */
        .footer-3d {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            transform: perspective(1000px) rotateX(0deg);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .footer-3d:hover {
            transform: perspective(1000px) rotateX(-5deg) translateY(-10px);
            box-shadow: 0 20px 60px rgba(251, 191, 36, 0.2);
            border-color: rgba(251, 191, 36, 0.3);
        }

        .footer-title-3d {
            position: relative;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.5);
            transform: translateZ(20px);
            transition: all 0.3s ease;
        }

        .footer-title-3d:hover {
            transform: translateZ(30px) scale(1.05);
            text-shadow: 0 4px 20px rgba(251, 191, 36, 0.8);
        }

        .social-icon-3d {
            display: inline-block;
            width: 50px;
            height: 50px;
            line-height: 50px;
            text-align: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            transform: translateZ(10px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .social-icon-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.3), transparent);
            transition: all 0.6s ease;
        }

        .social-icon-3d:hover::before {
            left: 100%;
        }

        .social-icon-3d:hover {
            transform: translateZ(30px) rotateY(360deg) scale(1.2);
            background: linear-gradient(135deg, var(--heavenly-gold), var(--ocean-blue));
            border-color: var(--heavenly-gold);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.4);
        }

        .footer-link-3d {
            display: inline-block;
            transform: translateZ(5px);
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-link-3d::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--heavenly-gold);
            transition: all 0.3s ease;
        }

        .footer-link-3d:hover {
            transform: translateZ(15px) translateX(5px);
            color: var(--heavenly-gold) !important;
        }

        .footer-link-3d:hover::after {
            width: 100%;
        }

        .designer-credit-3d {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(14, 165, 233, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            transform: perspective(1000px) rotateX(0deg);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
        }

        .designer-credit-3d::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(251, 191, 36, 0.1), transparent);
            animation: designerShimmer 3s infinite;
        }

        @keyframes designerShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }

        .designer-credit-3d:hover {
            transform: perspective(1000px) rotateX(-5deg) translateY(-5px);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.3);
        }

        .designer-name-3d {
            transform: translateZ(20px);
            text-shadow: 0 2px 15px rgba(251, 191, 36, 0.6);
            animation: designerGlow 2s ease-in-out infinite alternate;
        }

        @keyframes designerGlow {
            0% { text-shadow: 0 2px 15px rgba(251, 191, 36, 0.6); }
            100% { text-shadow: 0 4px 25px rgba(251, 191, 36, 0.9); }
        }

        .designer-contact-3d {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            transform: translateZ(15px);
            transition: all 0.4s ease;
            box-shadow: 0 5px 20px rgba(37, 211, 102, 0.3);
        }

        .designer-contact-3d:hover {
            transform: translateZ(25px) scale(1.1);
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.5);
            text-decoration: none;
            color: white;
        }

        /* Mobile Responsive 3D Effects */
        @media (max-width: 768px) {
            .footer-3d {
                transform: none;
                padding: 1.5rem;
            }
            
            .footer-3d:hover {
                transform: translateY(-5px);
            }
            
            .social-icon-3d {
                width: 40px;
                height: 40px;
                line-height: 40px;
                font-size: 0.9rem;
            }
            
            .social-icon-3d:hover {
                transform: scale(1.1);
            }
            
            .designer-credit-3d {
                padding: 1rem;
                transform: none;
            }
            
            .designer-credit-3d:hover {
                transform: translateY(-3px);
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker registered successfully with scope:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }
        
        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show install button or banner
            const installButton = document.createElement('button');
            installButton.textContent = 'Install App';
            installButton.className = 'btn btn-primary position-fixed';
            installButton.style.cssText = 'bottom: 20px; right: 20px; z-index: 9999; display: flex; align-items: center; gap: 8px;';
            installButton.innerHTML = '<i class="fas fa-download"></i> Install App';
            
            installButton.addEventListener('click', () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        } else {
                            console.log('User dismissed the install prompt');
                        }
                        deferredPrompt = null;
                        installButton.remove();
                    });
                }
            });
            
            document.body.appendChild(installButton);
        });
        
        // Hide install button after successful installation
        window.addEventListener('appinstalled', (evt) => {
            console.log('App was installed');
            const installButton = document.querySelector('.btn-primary.position-fixed');
            if (installButton) {
                installButton.remove();
            }
        });
    </script>
</body>
</html>
