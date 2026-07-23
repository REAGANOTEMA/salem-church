<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

$currentPage = $currentPage ?? 'home';
$pageTitle = $pageTitle ?? 'Salem Dominion Ministries';
$pageDescription = $pageDescription ?? 'Salem Dominion Ministries - Divine Worship Experience with Apostle Faty Musasizi in Iganga, Uganda';
$pageKeywords = $pageKeywords ?? 'church, divine worship, Apostle Faty Musasizi, Iganga, Uganda, Salem Dominion, Christianity, Holy Spirit';

$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="author" content="Salem Dominion Ministries">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#0f172a">
    <meta name="msapplication-TileColor" content="#0f172a">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta property="og:image" content="<?php echo LOGO_URL; ?>">
    <meta property="og:image:alt" content="Salem Dominion Ministries Logo">
    <meta property="og:site_name" content="Salem Dominion Ministries">
    <meta property="og:locale" content="en_US">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo LOGO_URL; ?>">

    <link rel="icon" type="image/png" sizes="32x32" href="public/images/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="public/images/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="public/favicon.svg">
    <link rel="shortcut icon" href="public/images/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="public/apple-touch-icon.png">
    <link rel="manifest" href="public/site.webmanifest">
    <meta name="msapplication-TileImage" content="public/icons/icon-144x144.png">
    <meta name="msapplication-TileColor" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SDM">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">

    <style>
        :root {
            --midnight: #0f172a;
            --ocean: #0ea5e9;
            --sky: #38bdf8;
            --ice: #7dd3fc;
            --white: #ffffff;
            --pearl: #f8fafc;
            --gold: #fbbf24;
            --divine-light: #fef3c7;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --nav-height: 80px;
            --transition-fast: 0.2s ease;
            --transition-smooth: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
            --shadow-glow: 0 0 30px rgba(14,165,233,0.15);
        }

        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--gray-700);
            background: var(--white);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; color: var(--midnight); }
        a { text-decoration: none; transition: var(--transition-fast); }

        /* ========== NAVBAR ========== */
        .sdm-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            height: var(--nav-height);
            display: flex;
            align-items: center;
            transition: var(--transition-smooth);
            background: transparent;
        }

        .sdm-navbar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,0.6) 0%, rgba(15,23,42,0.2) 70%, transparent 100%);
            transition: var(--transition-smooth);
            z-index: -1;
        }

        .sdm-navbar.scrolled {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 1px 0 rgba(0,0,0,0.06), 0 4px 20px rgba(0,0,0,0.06);
            height: 68px;
        }

        .sdm-navbar.scrolled::before { display: none; }

        .sdm-nav-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 24px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sdm-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
            z-index: 1060;
        }

        .sdm-brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
            transition: var(--transition-smooth);
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }

        .sdm-navbar.scrolled .sdm-brand-logo {
            border-color: var(--ocean);
        }

        .sdm-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .sdm-brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.3px;
            transition: var(--transition-smooth);
        }

        .sdm-navbar.scrolled .sdm-brand-name {
            color: var(--midnight);
        }

        .sdm-brand-tagline {
            font-family: 'Great Vibes', cursive;
            font-size: 0.75rem;
            color: var(--gold);
            letter-spacing: 0.5px;
            transition: var(--transition-smooth);
        }

        .sdm-navbar.scrolled .sdm-brand-tagline {
            color: var(--ocean);
        }

        .sdm-nav-links {
            display: flex;
            align-items: center;
            gap: 2px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sdm-nav-links .nav-item { position: relative; }

        .sdm-nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
            border-radius: 10px;
            transition: var(--transition-smooth);
            white-space: nowrap;
            position: relative;
            letter-spacing: 0.2px;
        }

        .sdm-nav-link i { font-size: 0.75rem; opacity: 0.8; }

        .sdm-nav-link::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: var(--transition-smooth);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .sdm-nav-link:hover::after,
        .sdm-nav-link.active::after {
            width: 60%;
        }

        .sdm-nav-link:hover {
            color: var(--white);
        }

        .sdm-navbar.scrolled .sdm-nav-link {
            color: var(--gray-600);
        }

        .sdm-navbar.scrolled .sdm-nav-link:hover {
            color: var(--ocean);
            background: rgba(14,165,233,0.06);
        }

        .sdm-navbar.scrolled .sdm-nav-link.active {
            color: var(--ocean);
            background: rgba(14,165,233,0.08);
        }

        .sdm-navbar.scrolled .sdm-nav-link.active::after {
            background: var(--ocean);
        }

        .sdm-nav-link.active {
            color: var(--white) !important;
        }

        .sdm-navbar.scrolled .sdm-nav-link.active {
            color: var(--ocean) !important;
        }

        /* Dropdown */
        .sdm-dropdown { position: relative; }

        .sdm-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%) translateY(8px);
            min-width: 220px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: var(--transition-smooth);
            z-index: 1100;
        }

        .sdm-dropdown:hover .sdm-dropdown-menu,
        .sdm-dropdown.show .sdm-dropdown-menu {
            opacity: 1;
            visibility: visible;
            pointer-events: all;
            transform: translateX(-50%) translateY(0);
        }

        .sdm-dropdown-toggle {
            cursor: pointer;
        }

        .sdm-dropdown-toggle .fa-chevron-down {
            font-size: 0.6rem;
            transition: var(--transition-fast);
            margin-left: 2px;
        }

        .sdm-dropdown:hover .sdm-dropdown-toggle .fa-chevron-down {
            transform: rotate(180deg);
        }

        .sdm-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--gray-600);
            border-radius: 10px;
            transition: var(--transition-fast);
            text-decoration: none;
        }

        .sdm-dropdown-item i {
            width: 18px;
            text-align: center;
            color: var(--ocean);
            font-size: 0.8rem;
        }

        .sdm-dropdown-item:hover {
            background: rgba(14,165,233,0.06);
            color: var(--ocean);
            transform: translateX(4px);
        }

        /* Admin / Auth buttons */
        .sdm-nav-auth {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 12px;
            flex-shrink: 0;
        }

        .sdm-admin-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(14,165,233,0.1);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ocean);
        }

        .sdm-admin-badge i { font-size: 0.7rem; }

        .sdm-btn-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            background: linear-gradient(135deg, var(--ocean), var(--sky));
            color: var(--white);
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: var(--transition-smooth);
            box-shadow: 0 2px 8px rgba(14,165,233,0.3);
            text-decoration: none;
        }

        .sdm-btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14,165,233,0.4);
            color: var(--white);
        }

        /* Hamburger */
        .sdm-hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background: none;
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 12px;
            cursor: pointer;
            padding: 0;
            z-index: 1060;
            transition: var(--transition-smooth);
        }

        .sdm-hamburger span {
            display: block;
            width: 20px;
            height: 2px;
            background: var(--white);
            border-radius: 2px;
            transition: var(--transition-smooth);
            margin: 3px 0;
        }

        .sdm-navbar.scrolled .sdm-hamburger {
            border-color: var(--gray-300);
        }

        .sdm-navbar.scrolled .sdm-hamburger span {
            background: var(--midnight);
        }

        .sdm-hamburger.open span:nth-child(1) {
            transform: rotate(45deg) translate(4px, 4px);
        }
        .sdm-hamburger.open span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }
        .sdm-hamburger.open span:nth-child(3) {
            transform: rotate(-45deg) translate(4px, -4px);
        }

        /* Mobile Overlay */
        .sdm-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.5);
            backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            transition: opacity var(--transition-smooth);
        }

        .sdm-mobile-overlay.show {
            display: block;
            opacity: 1;
        }

        .sdm-spacer {
            height: var(--nav-height);
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 1024px) {
            .sdm-nav-links {
                position: fixed;
                top: 0;
                right: -320px;
                width: 300px;
                height: 100vh;
                height: 100dvh;
                background: var(--white);
                flex-direction: column;
                align-items: stretch;
                padding: 90px 20px 30px;
                gap: 4px;
                box-shadow: -4px 0 30px rgba(0,0,0,0.15);
                transition: right var(--transition-smooth);
                z-index: 1050;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .sdm-nav-links.open { right: 0; }

            .sdm-nav-links .sdm-nav-link {
                color: var(--gray-700) !important;
                padding: 14px 16px;
                border-radius: 12px;
                font-size: 0.95rem;
                min-height: 48px;
            }

            .sdm-nav-links .sdm-nav-link:hover,
            .sdm-nav-links .sdm-nav-link.active {
                background: rgba(14,165,233,0.08) !important;
                color: var(--ocean) !important;
            }

            .sdm-nav-links .sdm-nav-link.active::after { display: none; }

            .sdm-dropdown-menu {
                position: static;
                transform: none;
                box-shadow: none;
                background: var(--gray-100);
                border-radius: 12px;
                margin-top: 4px;
                opacity: 1;
                visibility: visible;
                pointer-events: all;
                max-height: 0;
                overflow: hidden;
                transition: max-height var(--transition-smooth), padding var(--transition-smooth);
                padding: 0 8px;
            }

            .sdm-dropdown.show .sdm-dropdown-menu {
                max-height: 400px;
                padding: 8px;
            }

            .sdm-dropdown-item {
                padding: 12px 14px;
                min-height: 44px;
            }

            .sdm-nav-auth {
                margin-left: 0;
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px solid var(--gray-200);
                flex-direction: column;
                align-items: stretch;
            }

            .sdm-btn-login {
                min-height: 48px;
                justify-content: center;
                font-size: 0.95rem;
            }

            .sdm-hamburger { display: flex; }
            .sdm-spacer { height: 68px; }
        }

        @media (max-width: 480px) {
            .sdm-brand-name { font-size: 1rem; }
            .sdm-brand-tagline { font-size: 0.65rem; }
            .sdm-brand-logo { width: 40px; height: 40px; }
            .sdm-nav-links { width: 88vw; max-width: 300px; }
            .sdm-nav-container { padding: 0 16px; }
            .sdm-hamburger { width: 42px; height: 42px; border-radius: 10px; }
        }

        /* ===== SHARED RESPONSIVE BASE (All Pages) ===== */
        .sdm-hero h1 { font-size: clamp(1.8rem, 5vw, 2.8rem); }
        .sdm-hero h2 { font-size: clamp(1.5rem, 4vw, 2.2rem); }
        .sdm-hero p { font-size: clamp(0.9rem, 2.5vw, 1.1rem); padding: 0 16px; }

        .section-gap { padding: 60px 0; }
        .section-title-custom { font-size: clamp(1.6rem, 4vw, 2.5rem); }
        .section-subtitle-custom { font-size: clamp(0.95rem, 2vw, 1.1rem); padding: 0 16px; }

        .donate-form-card, .enroll-form-card, .form-card, .login-card {
            padding: 24px 20px;
            border-radius: 16px;
        }

        img { max-width: 100%; height: auto; }

        .alert-success-custom, .alert-danger {
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .section-gap { padding: 40px 0; }
            .section-gap.alt-bg { padding: 40px 0; }
        }

        @media (max-width: 576px) {
            .section-gap { padding: 30px 0; }
            .row { --bs-gutter-x: 16px; }
        }
    </style>
</head>
<body>

<nav class="sdm-navbar" id="sdmNavbar">
    <div class="sdm-nav-container">
        <a class="sdm-brand" href="index.php">
            <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries" class="sdm-brand-logo">
            <div class="sdm-brand-text">
                <span class="sdm-brand-name">Salem Dominion</span>
                <span class="sdm-brand-tagline">Ministries</span>
            </div>
        </a>

        <ul class="sdm-nav-links" id="sdmNavLinks">
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'home' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="about.php">
                    <i class="fas fa-info-circle"></i> About
                </a>
            </li>
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'leadership' ? 'active' : ''; ?>" href="leadership.php">
                    <i class="fas fa-cross"></i> Leadership
                </a>
            </li>
            <li class="nav-item sdm-dropdown">
                <a class="sdm-nav-link sdm-dropdown-toggle <?php echo $currentPage === 'ministries' ? 'active' : ''; ?>" href="ministries.php">
                    <i class="fas fa-hands-praying"></i> Ministries <i class="fas fa-chevron-down"></i>
                </a>
                <div class="sdm-dropdown-menu">
                    <a class="sdm-dropdown-item" href="ministries.php">
                        <i class="fas fa-layer-group"></i> All Ministries
                    </a>
                    <a class="sdm-dropdown-item" href="ministries.php?cat=worship">
                        <i class="fas fa-music"></i> Worship Team
                    </a>
                    <a class="sdm-dropdown-item" href="ministries.php?cat=youth">
                        <i class="fas fa-people-group"></i> Youth Ministry
                    </a>
                    <a class="sdm-dropdown-item" href="ministries.php?cat=children">
                        <i class="fas fa-child"></i> Children's Ministry
                    </a>
                    <a class="sdm-dropdown-item" href="ministries.php?cat=prayer">
                        <i class="fas fa-person-praying"></i> Prayer Ministry
                    </a>
                    <a class="sdm-dropdown-item" href="ministries.php?cat=outreach">
                        <i class="fas fa-hand-holding-heart"></i> Outreach
                    </a>
                </div>
            </li>
            <li class="nav-item sdm-dropdown">
                <a class="sdm-nav-link sdm-dropdown-toggle <?php echo $currentPage === 'media' ? 'active' : ''; ?>" href="sermons.php">
                    <i class="fas fa-photo-film"></i> Media <i class="fas fa-chevron-down"></i>
                </a>
                <div class="sdm-dropdown-menu">
                    <a class="sdm-dropdown-item" href="sermons.php">
                        <i class="fas fa-book-bible"></i> Sermons
                    </a>
                    <a class="sdm-dropdown-item" href="gallery.php">
                        <i class="fas fa-images"></i> Gallery
                    </a>
                    <a class="sdm-dropdown-item" href="news.php">
                        <i class="fas fa-newspaper"></i> News
                    </a>
                    <a class="sdm-dropdown-item" href="testimonials.php">
                        <i class="fas fa-quote-left"></i> Testimonials
                    </a>
                </div>
            </li>
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'events' ? 'active' : ''; ?>" href="events.php">
                    <i class="fas fa-calendar-days"></i> Events
                </a>
            </li>
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'book-pastor' ? 'active' : ''; ?>" href="book-pastor.php">
                    <i class="fas fa-calendar-check"></i> Book Pastor
                </a>
            </li>
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'donate' ? 'active' : ''; ?>" href="donate.php">
                    <i class="fas fa-heart"></i> Donate
                </a>
            </li>
            <li class="nav-item">
                <a class="sdm-nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="contact.php">
                    <i class="fas fa-envelope"></i> Contact
                </a>
            </li>

            <div class="sdm-nav-auth">
                <?php if ($admin_logged_in): ?>
                    <div class="sdm-admin-badge">
                        <i class="fas fa-user-shield"></i>
                        <?php echo htmlspecialchars($admin_name); ?>
                    </div>
                    <a href="admin/dashboard.php" class="sdm-btn-login">
                        <i class="fas fa-gauge-high"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="admin/login.php" class="sdm-btn-login">
                        <i class="fas fa-right-to-bracket"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </ul>

        <button class="sdm-hamburger" id="sdmHamburger" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<div class="sdm-mobile-overlay" id="sdmOverlay"></div>
<div class="sdm-spacer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('sdmNavbar');
    const hamburger = document.getElementById('sdmHamburger');
    const navLinks = document.getElementById('sdmNavLinks');
    const overlay = document.getElementById('sdmOverlay');

    window.addEventListener('scroll', function() {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
    });

    if (window.scrollY > 60) navbar.classList.add('scrolled');

    hamburger.addEventListener('click', function() {
        this.classList.toggle('open');
        navLinks.classList.toggle('open');
        overlay.classList.toggle('show');
        document.body.style.overflow = navLinks.classList.contains('open') ? 'hidden' : '';
    });

    overlay.addEventListener('click', function() {
        hamburger.classList.remove('open');
        navLinks.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    });

    document.querySelectorAll('.sdm-dropdown-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                e.preventDefault();
                e.stopPropagation();
                this.closest('.sdm-dropdown').classList.toggle('show');
            }
        });
    });

    document.querySelectorAll('.sdm-nav-link').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                hamburger.classList.remove('open');
                navLinks.classList.remove('open');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });
});
</script>
</body>
</html>
