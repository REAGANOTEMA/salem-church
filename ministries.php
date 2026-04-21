<?php
// MINISTRIES PAGE - Salem Dominion Ministries - Professional & Mobile Responsive
require_once __DIR__ . '/db_connection.php';

$conn = getConnection();

// Initialize variables
$ministries = [];

try {
    if ($conn) {
        // Check if ministries table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'ministries'");
        if ($table_check && $table_check->num_rows > 0) {
            // Get ministries with proper error handling
            $stmt = $conn->prepare("SELECT * FROM ministries WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $ministries = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        } else {
            // Table doesn't exist, use empty array
            $ministries = [];
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    error_log("Ministries page error: " . $e->getMessage());
    $ministries = [];
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}

// Helper function to get ministry icon
function get_ministry_icon($category) {
    $icons = [
        'children' => 'fa-child',
        'youth' => 'fa-users',
        'men' => 'fa-male',
        'women' => 'fa-female',
        'outreach' => 'fa-hands-helping',
        'worship' => 'fa-music',
        'prayer' => 'fa-pray',
        'other' => 'fa-church'
    ];
    return $icons[$category] ?? 'fa-church';
}

// Helper function to get ministry color
function get_ministry_color($category) {
    $colors = [
        'children' => '#fbbf24',
        'youth' => '#0ea5e9',
        'men' => '#10b981',
        'women' => '#ec4899',
        'outreach' => '#f59e0b',
        'worship' => '#8b5cf6',
        'prayer' => '#ef4444',
        'other' => '#6b7280'
    ];
    return $colors[$category] ?? '#6b7280';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ministries | Salem Dominion Ministries</title>
    <meta name="description" content="Discover your divine calling through various ministries at Salem Dominion Ministries">
    <link rel="icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Mobile Responsive CSS -->
    <link href="assets/mobile-responsive.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--ocean-blue) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--midnight-blue);
            color: var(--snow-white);
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: var(--heavenly-gold) !important;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .navbar-nav .nav-link {
            color: var(--snow-white) !important;
            font-weight: 400;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: var(--heavenly-gold) !important;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/ministries-hero.jpg');
            background-size: cover;
            background-position: center;
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        /* Ministries Section */
        .ministries-section {
            padding: 80px 0;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            text-align: center;
            margin-bottom: 3rem;
        }

        .ministry-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
        }

        .ministry-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .ministry-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            border-color: var(--heavenly-gold);
        }

        .ministry-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-divine);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--snow-white);
            transition: all 0.3s ease;
        }

        .ministry-card:hover .ministry-icon {
            transform: scale(1.1);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
        }

        .ministry-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .ministry-description {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            min-height: 80px;
        }

        .ministry-leader {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .ministry-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-ministry {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-ministry:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
            text-decoration: none;
        }

        .btn-outline-ministry {
            background: transparent;
            color: var(--heavenly-gold);
            border: 2px solid var(--heavenly-gold);
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-outline-ministry:hover {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
            text-decoration: none;
        }

        /* Call to Action Section */
        .cta-section {
            padding: 80px 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .cta-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 25px;
            padding: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1.5rem;
        }

        .cta-description {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        /* Footer with 3D Effects */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 0 20px;
            margin-top: 80px;
        }
        
        .footer-widget {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            transform: perspective(1000px) rotateX(0deg);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .footer-widget:hover {
            transform: perspective(1000px) rotateX(-5deg) translateY(-10px);
            box-shadow: 0 20px 60px rgba(251, 191, 36, 0.2);
            border-color: rgba(251, 191, 36, 0.3);
        }
        
        .footer-widget h4 {
            position: relative;
            padding-bottom: 15px;
            color: var(--heavenly-gold);
            transform: translateZ(20px);
            transition: all 0.3s ease;
        }
        
        .footer-widget h4:hover {
            transform: translateZ(30px) scale(1.05);
        }
        
        .footer-widget h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--heavenly-gold);
        }
        
        .footer-links li {
            margin-bottom: 8px;
            transition: all 0.3s ease;
            transform: translateZ(5px);
        }
        
        .footer-links li:hover {
            transform: translateZ(15px) translateX(5px);
        }
        
        .footer-links a:hover {
            color: var(--heavenly-gold) !important;
        }
        
        .social-icon {
            transition: all 0.3s ease;
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
        
        .social-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.3), transparent);
            transition: all 0.6s ease;
        }
        
        .social-icon:hover::before {
            left: 100%;
        }
        
        .social-icon:hover {
            transform: translateZ(30px) rotateY(360deg) scale(1.2);
            background: linear-gradient(135deg, var(--heavenly-gold), var(--ocean-blue));
            border-color: var(--heavenly-gold);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.4);
            color: white !important;
        }

        /* Enhanced Mobile Responsive */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .ministry-card {
                padding: 2.2rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .ministry-card {
                padding: 2rem;
            }
            
            .ministry-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .navbar-brand {
                font-size: 1.6rem;
            }
            
            .navbar-brand img {
                width: 35px;
                height: 35px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 0 60px;
            }
            
            .hero-title {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .section-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }
            
            .ministries-section {
                padding: 60px 0;
            }
            
            .ministry-card {
                margin-bottom: 1.5rem;
                padding: 2rem;
                text-align: center;
            }
            
            .ministry-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .ministry-title {
                font-size: 1.3rem;
                margin-bottom: 0.8rem;
            }
            
            .ministry-description {
                font-size: 0.9rem;
                margin-bottom: 1rem;
                min-height: 60px;
            }
            
            .ministry-actions {
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            
            .btn-ministry, .btn-outline-ministry {
                width: 100%;
                max-width: 200px;
                padding: 10px 20px;
                font-size: 0.85rem;
                min-height: 44px;
            }
            
            .cta-section {
                padding: 60px 0;
            }
            
            .cta-card {
                padding: 2rem;
            }
            
            .cta-title {
                font-size: 1.8rem;
            }
            
            .cta-description {
                font-size: 1rem;
            }
            
            .navbar {
                padding: 0.5rem 0;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .navbar-brand img {
                width: 30px;
                height: 30px;
            }
            
            .navbar-nav .nav-link {
                font-size: 0.9rem;
                margin: 0 5px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section {
                padding: 80px 0 50px;
            }
            
            .hero-title {
                font-size: 2rem;
                margin-bottom: 0.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.95rem;
                margin-bottom: 1.2rem;
            }
            
            .section-title {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }
            
            .ministries-section {
                padding: 40px 0;
            }
            
            .ministry-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .ministry-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            .ministry-title {
                font-size: 1.2rem;
            }
            
            .ministry-description {
                font-size: 0.85rem;
                min-height: 50px;
            }
            
            .cta-section {
                padding: 40px 0;
            }
            
            .cta-card {
                padding: 1.5rem;
            }
            
            .cta-title {
                font-size: 1.6rem;
            }
            
            .cta-description {
                font-size: 0.95rem;
            }
            
            .navbar-brand {
                font-size: 1.2rem;
            }
            
            .navbar-brand img {
                width: 25px;
                height: 25px;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
            }
            
            .section-title {
                font-size: 1.6rem;
            }
            
            .ministry-card {
                padding: 1.2rem;
            }
            
            .ministry-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
            
            .ministry-title {
                font-size: 1.1rem;
            }
            
            .ministry-description {
                font-size: 0.8rem;
                min-height: 45px;
            }
            
            .btn-ministry, .btn-outline-ministry {
                font-size: 0.8rem;
                padding: 8px 16px;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            .navbar-brand img {
                width: 22px;
                height: 22px;
            }
        }
        
        @media (max-width: 360px) {
            .hero-title {
                font-size: 1.6rem;
            }
            
            .section-title {
                font-size: 1.4rem;
            }
            
            .ministry-card {
                padding: 1rem;
            }
            
            .ministry-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .ministry-title {
                font-size: 1rem;
            }
            
            .ministry-description {
                font-size: 0.75rem;
                min-height: 40px;
            }
            
            .btn-ministry, .btn-outline-ministry {
                font-size: 0.75rem;
                padding: 6px 12px;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .navbar-brand img {
                width: 20px;
                height: 20px;
            }
        }
        
        /* Touch-friendly adjustments */
        @media (hover: none) and (pointer: coarse) {
            .btn-ministry, .btn-outline-ministry {
                min-height: 44px;
                min-width: 44px;
                touch-action: manipulation;
            }
            
            .ministry-card {
                touch-action: manipulation;
            }
            
            .navbar-nav .nav-link {
                padding: 0.5rem 0.75rem;
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
        
        /* Landscape mobile adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .hero-section {
                padding: 60px 0 40px;
            }
            
            .hero-title {
                font-size: 2rem;
                margin-bottom: 0.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }
            
            .ministries-section {
                padding: 40px 0;
            }
            
            .ministry-card {
                padding: 1.5rem;
            }
            
            .footer-widget {
                transform: none;
                padding: 1.5rem;
            }
            
            .footer-widget:hover {
                transform: translateY(-5px);
            }
            
            .social-icon {
                width: 40px;
                height: 40px;
                line-height: 40px;
                font-size: 0.9rem;
            }
            
            .social-icon:hover {
                transform: scale(1.1);
            }
            
            .designer-credit {
                padding: 1rem;
                transform: none;
            }
            
            .designer-credit:hover {
                transform: translateY(-3px);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.7);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        /* Designer Credit Styles */
        .designer-credit {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(14, 165, 233, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
            transform: perspective(1000px) rotateX(0deg);
        }

        .designer-credit::before {
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

        .designer-credit:hover {
            transform: perspective(1000px) rotateX(-5deg) translateY(-5px);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.3);
        }

        .designer-name {
            transform: translateZ(20px);
            text-shadow: 0 2px 15px rgba(251, 191, 36, 0.6);
            animation: designerGlow 2s ease-in-out infinite alternate;
        }

        @keyframes designerGlow {
            0% { text-shadow: 0 2px 15px rgba(251, 191, 36, 0.6); }
            100% { text-shadow: 0 4px 25px rgba(251, 191, 36, 0.9); }
        }

        .designer-contact {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.4s ease;
            box-shadow: 0 5px 20px rgba(37, 211, 102, 0.3);
            transform: translateZ(15px);
        }

        .designer-contact:hover {
            transform: translateZ(25px) scale(1.1);
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.5);
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Professional Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo" class="me-2">
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
                        <a class="nav-link dropdown-toggle active" href="#" id="ministriesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-hands-helping me-1"></i> Ministries
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="ministries.php">All Ministries</a></li>
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Our Ministries</h1>
            <p class="hero-subtitle">Discover your divine calling and serve with purpose in our various ministries</p>
        </div>
    </section>

    <!-- Ministries Section -->
    <section class="ministries-section">
        <div class="container">
            <h2 class="section-title">Ministries We Offer</h2>
            
            <?php if (!empty($ministries)): ?>
                <div class="row g-4">
                    <?php foreach ($ministries as $ministry): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="ministry-card">
                                <div class="ministry-icon">
                                    <i class="fas <?= get_ministry_icon($ministry['category'] ?? 'other') ?>"></i>
                                </div>
                                <h3 class="ministry-title"><?= safe_html($ministry['name']) ?></h3>
                                <p class="ministry-description"><?= safe_html($ministry['description']) ?></p>
                                <?php if ($ministry['leader_name']): ?>
                                    <p class="ministry-leader">
                                        <i class="fas fa-user me-2"></i>Leader: <?= safe_html($ministry['leader_name']) ?>
                                    </p>
                                <?php endif; ?>
                                <div class="ministry-actions">
                                    <a href="#" class="btn btn-ministry">
                                        <i class="fas fa-info-circle me-2"></i>Learn More
                                    </a>
                                    <a href="#" class="btn btn-outline-ministry">
                                        <i class="fas fa-hand-point-up me-2"></i>Join
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-hands-helping"></i>
                    <h3>Ministries Coming Soon</h3>
                    <p>We are currently developing our ministry programs. Check back soon for opportunities to serve!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="cta-card">
                        <h2 class="cta-title">Ready to Serve?</h2>
                        <p class="cta-description">Join us in making a difference in our community and beyond. Every gift and talent matters in God's kingdom.</p>
                        <div class="ministry-actions justify-content-center">
                            <a href="contact.php" class="btn btn-ministry">
                                <i class="fas fa-envelope me-2"></i>Get Involved
                            </a>
                            <a href="book_pastor_call.php" class="btn btn-outline-ministry">
                                <i class="fas fa-phone-alt me-2"></i>Speak with Pastor
                            </a>
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
                    <div class="footer-widget">
                        <h4 class="text-white mb-3">
                            <i class="fas fa-church me-2"></i>Salem Dominion Ministries
                        </h4>
                        <p class="text-white-50">Empowering lives through the Word of God and the Power of the Holy Spirit. Join us in spreading the Gospel and making disciples.</p>
                        <div class="mt-3">
                            <a href="<?php echo CHURCH_WEBSITE; ?>" target="_blank" class="text-white me-3 social-icon">
                                <i class="fas fa-globe fa-lg"></i>
                            </a>
                            <a href="https://youtube.com/@musasizifaty?si=BxEArdVKNKVSac3X" target="_blank" class="text-white me-3 social-icon">
                                <i class="fab fa-youtube fa-lg"></i>
                            </a>
                            <a href="https://www.tiktok.com/@salem1dominionchurch?_r=1&_t=ZS-95E1n40LieS" target="_blank" class="text-white me-3 social-icon">
                                <i class="fab fa-tiktok fa-lg"></i>
                            </a>
                            <a href="https://www.facebook.com/share/1CoCEmvnBB/" target="_blank" class="text-white social-icon">
                                <i class="fab fa-facebook fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="text-white mb-3">Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="about.php" class="text-white-50 text-decoration-none">About Us</a></li>
                            <li><a href="leadership.php" class="text-white-50 text-decoration-none">Leadership</a></li>
                            <li><a href="sermons.php" class="text-white-50 text-decoration-none">Sermons</a></li>
                            <li><a href="events.php" class="text-white-50 text-decoration-none">Events</a></li>
                            <li><a href="ministries.php" class="text-white-50 text-decoration-none">Ministries</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="text-white mb-3">Services</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="prophetic-school.php" class="text-white-50 text-decoration-none">Prophetic School</a></li>
                            <li><a href="book_pastor_call.php" class="text-white-50 text-decoration-none">Book Pastor Call</a></li>
                            <li><a href="donate.php" class="text-white-50 text-decoration-none">Give & Donate</a></li>
                            <li><a href="testimonials.php" class="text-white-50 text-decoration-none">Testimonials</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="text-white mb-3">Contact Info</h5>
                        <ul class="list-unstyled footer-contact">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                                <span class="text-white-50">Kampala, Uganda</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone me-2 text-warning"></i>
                                <span class="text-white-50">+256 753 244 480</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-warning"></i>
                                <span class="text-white-50">info@salem-dominion-ministries.org</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-globe me-2 text-warning"></i>
                                <span class="text-white-50"><?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?></span>
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
                    <div class="designer-credit">
                        <p class="text-white-50 mb-2">
                            <i class="fas fa-code me-2"></i>Designed & Developed by
                        </p>
                        <h5 class="designer-name text-warning mb-2">Mr. Reagan Otema</h5>
                        <a href="https://wa.me/256772514889" target="_blank" class="designer-contact">
                            <i class="fab fa-whatsapp me-2"></i>+256 772 514 889
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
