<?php
/**
 * Privacy Policy - Salem Dominion Ministries
 * Compact and user-friendly privacy policy page
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
    <title>Privacy Policy | Salem Dominion Ministries</title>
    <meta name="description" content="Privacy Policy for Salem Dominion Ministries website">
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
        
        .privacy-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .privacy-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-top: 2rem;
        }
        
        .privacy-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.3);
        }
        
        .privacy-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }
        
        .privacy-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .privacy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.1);
            border-color: rgba(251, 191, 36, 0.3);
        }
        
        .privacy-section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .privacy-section-title i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        .privacy-content {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .privacy-content ul {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .privacy-content li {
            margin-bottom: 0.5rem;
        }
        
        .privacy-highlight {
            background: rgba(251, 191, 36, 0.1);
            border-left: 3px solid var(--heavenly-gold);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }
        
        .privacy-footer {
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
            .privacy-title {
                font-size: 2rem;
            }
            
            .privacy-card {
                padding: 1.5rem;
            }
            
            .privacy-section-title {
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

    <div class="privacy-container">
        <div class="privacy-header">
            <h1 class="privacy-title">Privacy Policy</h1>
            <p class="privacy-subtitle">Salem Dominion Ministries</p>
            <p class="last-updated">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-shield-alt"></i>
                Our Commitment to Privacy
            </h2>
            <div class="privacy-content">
                <p>At Salem Dominion Ministries, we respect your privacy and are committed to protecting your personal information. This policy explains how we collect, use, and safeguard your data.</p>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-info-circle"></i>
                Information We Collect
            </h2>
            <div class="privacy-content">
                <p>We may collect the following types of information:</p>
                <ul>
                    <li><strong>Personal Information:</strong> Name, email address, phone number</li>
                    <li><strong>Donation Details:</strong> Payment information for contributions</li>
                    <li><strong>Event Registration:</strong> Information for ministry events</li>
                    <li><strong>Prayer Requests:</strong> Personal prayer needs and concerns</li>
                    <li><strong>Technical Data:</strong> IP address, browser type, device information</li>
                </ul>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-cogs"></i>
                How We Use Your Information
            </h2>
            <div class="privacy-content">
                <p>Your information is used to:</p>
                <ul>
                    <li>Provide ministry services and spiritual support</li>
                    <li>Process donations and provide receipts</li>
                    <li>Communicate about events and ministry updates</li>
                    <li>Respond to prayer requests and inquiries</li>
                    <li>Improve our website and user experience</li>
                    <li>Fulfill legal and regulatory requirements</li>
                </ul>
                <div class="privacy-highlight">
                    <strong>Important:</strong> We never sell your personal information to third parties.
                </div>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-lock"></i>
                Data Security
            </h2>
            <div class="privacy-content">
                <p>We implement appropriate security measures to protect your information:</p>
                <ul>
                    <li>Secure HTTPS encryption for all data transmission</li>
                    <li>Protected database with access controls</li>
                    <li>Regular security updates and monitoring</li>
                    <li>Limited staff access to personal information</li>
                    <li>Secure payment processing through trusted providers</li>
                </ul>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-cookie-bite"></i>
                Cookies and Tracking
            </h2>
            <div class="privacy-content">
                <p>Our website uses cookies to:</p>
                <ul>
                    <li>Remember user preferences and login status</li>
                    <li>Analyze website traffic and usage patterns</li>
                    <li>Provide personalized content and functionality</li>
                    <li>Ensure website security and prevent fraud</li>
                </ul>
                <p>You can control cookies through your browser settings.</p>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-share-alt"></i>
                Third-Party Services
            </h2>
            <div class="privacy-content">
                <p>We may share information with:</p>
                <ul>
                    <li><strong>Payment Processors:</strong> For secure donation processing</li>
                    <li><strong>Email Services:</strong> For ministry communications</li>
                    <li><strong>Analytics Providers:</strong> For website improvement</li>
                    <li><strong>Legal Authorities:</strong> When required by law</li>
                </ul>
                <p>All third-party services are carefully vetted for security and privacy compliance.</p>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-user-cog"></i>
                Your Rights
            </h2>
            <div class="privacy-content">
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal information</li>
                    <li>Correct inaccurate information</li>
                    <li>Request deletion of your data</li>
                    <li>Opt-out of marketing communications</li>
                    <li>Withdraw consent for data processing</li>
                </ul>
                <p>To exercise these rights, contact us using the information below.</p>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-child"></i>
                Children's Privacy
            </h2>
            <div class="privacy-content">
                <p>Our website is not directed to children under 13. We do not knowingly collect personal information from children. If we become aware of such collection, we will promptly remove it.</p>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-sync"></i>
                Policy Updates
            </h2>
            <div class="privacy-content">
                <p>We may update this privacy policy periodically. Changes will be posted on this page with a revised "Last updated" date. Your continued use of our website constitutes acceptance of any changes.</p>
            </div>
        </div>

        <div class="privacy-card">
            <h2 class="privacy-section-title">
                <i class="fas fa-envelope"></i>
                Contact Us
            </h2>
            <div class="privacy-content">
                <p>For privacy-related questions or concerns, please contact:</p>
                <ul>
                    <li>Email: prinfo@salem-dominion-ministries.com</li>
                    <li>Phone: +256 753 244 480</li>
                    <li>Address: Nampirika, Iganga District, Uganda</li>
                </ul>
                <p>We will respond to privacy inquiries within 30 days.</p>
            </div>
        </div>

        <div class="privacy-footer">
            <p class="mb-3">Your privacy is important to us. Thank you for trusting Salem Dominion Ministries!</p>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
