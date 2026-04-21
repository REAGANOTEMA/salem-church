<?php
/**
 * Terms of Service - Salem Dominion Ministries
 * Compact and user-friendly terms of service page
 */

session_start();
require_once 'db_connection.php';

// Check if admin is logged in
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service | Salem Dominion Ministries</title>
    <meta name="description" content="Terms of Service for Salem Dominion Ministries website">
    <link rel="icon" href="public/logo-icon.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/mobile-responsive.css" rel="stylesheet">
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 100%);
            color: var(--snow-white);
            min-height: 100vh;
        }
        
        .terms-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .terms-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-top: 2rem;
        }
        
        .terms-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.3);
        }
        
        .terms-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }
        
        .terms-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .terms-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.1);
            border-color: rgba(251, 191, 36, 0.3);
        }
        
        .terms-section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .terms-section-title i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        .terms-content {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .terms-content ul {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .terms-content li {
            margin-bottom: 0.5rem;
        }
        
        .terms-highlight {
            background: rgba(251, 191, 36, 0.1);
            border-left: 3px solid var(--heavenly-gold);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }
        
        .terms-footer {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
        }
        
        .btn-back {
            background: var(--heavenly-gold);
            color: var(--midnight-blue);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #f59e0b;
            color: var(--midnight-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
            text-decoration: none;
        }
        
        .last-updated {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .terms-title {
                font-size: 2rem;
            }
            
            .terms-card {
                padding: 1.5rem;
            }
            
            .terms-section-title {
                font-size: 1.1rem;
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
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

    <div class="terms-container">
        <div class="terms-header">
            <h1 class="terms-title">Terms of Service</h1>
            <p class="terms-subtitle">Salem Dominion Ministries</p>
            <p class="last-updated">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-handshake"></i>
                Agreement to Terms
            </h2>
            <div class="terms-content">
                <p>By accessing and using Salem Dominion Ministries' website, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our website.</p>
            </div>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-church"></i>
                Ministry Services
            </h2>
            <div class="terms-content">
                <p>We provide spiritual services including:</p>
                <ul>
                    <li>Online sermons and religious content</li>
                    <li>Event registration and information</li>
                    <li>Donation processing for ministry support</li>
                    <li>Prayer request submissions</li>
                    <li>Community engagement platforms</li>
                </ul>
                <div class="terms-highlight">
                    <strong>Note:</strong> All services are provided for spiritual and religious purposes. Participation is voluntary.
                </div>
            </div>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-user-shield"></i>
                User Responsibilities
            </h2>
            <div class="terms-content">
                <p>As a user of our website, you agree to:</p>
                <ul>
                    <li>Provide accurate information when registering</li>
                    <li>Respect other users and ministry staff</li>
                    <li>Use the website for lawful purposes only</li>
                    <li>Not post inappropriate or offensive content</li>
                    <li>Respect copyright and intellectual property rights</li>
                </ul>
            </div>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-donate"></i>
                Donations and Payments
            </h2>
            <div class="terms-content">
                <p>Regarding financial contributions:</p>
                <ul>
                    <li>All donations are voluntary and non-refundable</li>
                    <li>We use secure payment processors for transactions</li>
                    <li>Donations support ministry operations and outreach</li>
                    <li>Tax receipts may be provided where applicable</li>
                    <li>We are not responsible for payment processing errors by third parties</li>
                </ul>
            </div>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-gavel"></i>
                Limitation of Liability
            </h2>
            <div class="terms-content">
                <p>Salem Dominion Ministries provides services "as is" without warranties. We are not liable for:</p>
                <ul>
                    <li>Direct or indirect damages from website use</li>
                    <li>Service interruptions or technical issues</li>
                    <li>Content accuracy or completeness</li>
                    <li>Third-party website links or content</li>
                </ul>
            </div>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-sync-alt"></i>
                Terms Modifications
            </h2>
            <div class="terms-content">
                <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of the website constitutes acceptance of any modifications.</p>
            </div>
        </div>

        <div class="terms-card">
            <h2 class="terms-section-title">
                <i class="fas fa-envelope"></i>
                Contact Information
            </h2>
            <div class="terms-content">
                <p>For questions about these Terms of Service, please contact:</p>
                <ul>
                    <li>Email: info@salem-dominion-ministries.com</li>
                    <li>Phone: +256 753 244 480</li>
                    <li>Location: Nampirika, Iganga District, Uganda</li>
                </ul>
            </div>
        </div>

        <div class="terms-footer">
            <p class="mb-3">Thank you for being part of our ministry community!</p>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
