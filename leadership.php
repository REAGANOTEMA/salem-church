<?php
// LEADERSHIP PAGE - Salem Dominion Ministries - Professional & Mobile Responsive
require_once 'config.php';
require_once 'db_connection.php';

$conn = getConnection();

// Initialize variables
$leadership = [];
$errors = [];

try {
    if ($conn) {
        // Get leadership data with proper error handling
        $stmt = $conn->prepare("SELECT * FROM leadership WHERE is_active = 1 ORDER BY order_position ASC, name ASC");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $leadership = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    error_log("Leadership page error: " . $e->getMessage());
    $errors[] = "Unable to load leadership data at this time.";
    $leadership = [];
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}

// Helper function to get leadership role icon
function get_role_icon($role) {
    $icons = [
        'pastor' => 'fa-cross',
        'senior pastor' => 'fa-crown',
        'assistant pastor' => 'fa-hands-helping',
        'elder' => 'fa-user-tie',
        'deacon' => 'fa-pray',
        'director' => 'fa-church',
        'coordinator' => 'fa-users',
        'leader' => 'fa-crown',
        'reverend' => 'fa-cross'
    ];
    return $icons[strtolower($role)] ?? 'fa-user';
}

// Helper function to get leadership role color
function get_role_color($role) {
    $colors = [
        'pastor' => '#fbbf24',
        'senior pastor' => '#dc2626',
        'assistant pastor' => '#0ea5e9',
        'elder' => '#10b981',
        'deacon' => '#8b5cf6',
        'director' => '#ef4444',
        'coordinator' => '#f59e0b',
        'leader' => '#6b7280',
        'reverend' => '#fbbf24'
    ];
    return $colors[strtolower($role)] ?? '#6b7280';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leadership | Salem Dominion Ministries</title>
    <meta name="description" content="Meet our leadership team at Salem Dominion Ministries">
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
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/leadership-hero.jpg');
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

        /* Leadership Section */
        .leadership-section {
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

        .leadership-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .leader-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
            backdrop-filter: blur(25px);
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 25px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
            perspective: 1000px;
            overflow: hidden;
            position: relative;
            box-shadow: 
                0 10px 40px rgba(15, 23, 42, 0.1),
                0 0 80px rgba(251, 191, 36, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .leader-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--heavenly-gold), var(--ocean-blue), var(--heavenly-gold));
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        .leader-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.6s ease;
            pointer-events: none;
        }

        .leader-card:hover {
            transform: translateY(-20px) rotateX(5deg) scale(1.02);
            box-shadow: 
                0 40px 80px rgba(15, 23, 42, 0.2),
                0 0 120px rgba(251, 191, 36, 0.3),
                0 0 200px rgba(14, 165, 233, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            border-color: var(--heavenly-gold);
            background: linear-gradient(135deg, rgba(255, 255, 255, 1), rgba(255, 255, 255, 0.95));
        }

        .leader-card:hover::after {
            opacity: 1;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .leader-avatar {
            width: 120px;
            height: 120px;
            background: var(--gradient-divine);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: var(--snow-white);
            font-weight: 600;
            position: relative;
            transition: all 0.3s ease;
        }

        .leader-card:hover .leader-avatar {
            transform: scale(1.1);
            box-shadow: 0 15px 30px rgba(251, 191, 36, 0.4);
        }

        .leader-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .leader-image {
            height: 240px;
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.08) 0%, rgba(255, 255, 255, 0.15) 50%, rgba(14, 165, 233, 0.08) 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 25px;
            margin-bottom: 1.8rem;
            border: 2px solid rgba(251, 191, 36, 0.3);
            box-shadow: 
                inset 0 3px 15px rgba(255, 255, 255, 0.6),
                0 8px 30px rgba(15, 23, 42, 0.15);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 240px;
        }
        
        .leader-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(251, 191, 36, 0.15) 0%, transparent 70%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.6s ease;
        }
        
        .leader-card:hover .leader-image::before {
            opacity: 1;
        }
        
        .leader-image img {
            width: 90%;
            height: 90%;
            max-width: 90%;
            max-height: 90%;
            object-fit: cover;
            object-position: center top;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 20px;
            box-shadow: 
                0 10px 35px rgba(15, 23, 42, 0.25),
                0 0 50px rgba(251, 191, 36, 0.15),
                inset 0 2px 0 rgba(255, 255, 255, 0.4);
            transform: translateZ(15px);
        }
        
        .leader-card:hover .leader-image img {
            transform: scale(1.05) rotateY(3deg) translateZ(25px);
            box-shadow: 
                0 20px 50px rgba(15, 23, 42, 0.35),
                0 0 80px rgba(251, 191, 36, 0.25),
                0 0 100px rgba(14, 165, 233, 0.15),
                inset 0 3px 0 rgba(255, 255, 255, 0.6);
        }

        .leader-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 0.8rem;
            transition: all 0.4s ease;
            text-shadow: 0 2px 10px rgba(15, 23, 42, 0.1);
            transform: translateZ(5px);
        }

        .leader-card:hover .leader-name {
            color: var(--heavenly-gold);
            transform: translateZ(15px) scale(1.05);
            text-shadow: 0 4px 20px rgba(251, 191, 36, 0.3);
        }

        .leader-title {
            color: var(--ocean-blue);
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s ease;
            transform: translateZ(3px);
        }

        .leader-card:hover .leader-title {
            color: var(--heavenly-gold);
            transform: translateZ(10px);
        }

        .leader-bio {
            color: var(--midnight-blue);
            line-height: 1.7;
            margin-bottom: 1.8rem;
            min-height: 100px;
            font-size: 0.95rem;
            opacity: 0.9;
            transition: all 0.4s ease;
            transform: translateZ(2px);
        }

        .leader-card:hover .leader-bio {
            opacity: 1;
            transform: translateZ(8px);
        }

        .leader-contact {
            display: flex;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            transform: translateZ(5px);
            transition: all 0.4s ease;
            margin-bottom: 1.5rem;
        }

        .leader-contact a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--ocean-blue), var(--sky-blue));
            color: var(--snow-white);
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 5px 15px rgba(14, 165, 233, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transform: translateZ(10px);
            position: relative;
            overflow: hidden;
        }

        .leader-contact a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .leader-contact a:hover::before {
            left: 100%;
        }

        .leader-contact a:hover {
            transform: translateZ(20px) scale(1.1) rotateY(10deg);
            box-shadow: 
                0 10px 25px rgba(14, 165, 233, 0.3),
                0 0 40px rgba(251, 191, 36, 0.2),
                inset 0 2px 0 rgba(255, 255, 255, 0.5);
        }

        .leader-contact .whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
            box-shadow: 
                0 5px 15px rgba(37, 211, 102, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        .leader-contact .whatsapp:hover {
            box-shadow: 
                0 10px 25px rgba(37, 211, 102, 0.3),
                0 0 40px rgba(37, 211, 102, 0.2),
                inset 0 2px 0 rgba(255, 255, 255, 0.5);
        }

        .leader-contact i {
            font-size: 1.1rem;
            z-index: 1;
            position: relative;
        }

        .leader-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-leader {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-leader:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
            text-decoration: none;
        }

        .btn-outline-leader {
            background: transparent;
            color: var(--heavenly-gold);
            border: 2px solid var(--heavenly-gold);
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-outline-leader:hover {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
            text-decoration: none;
        }

        /* Senior Leadership Section */
        .senior-leadership {
            padding: 60px 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .senior-leader-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(251, 191, 36, 0.5);
            border-radius: 25px;
            padding: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .senior-leader-card::before {
            content: 'SENIOR LEADER';
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--gradient-divine);
            color: var(--snow-white);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .senior-leader-card .leader-avatar {
            width: 150px;
            height: 150px;
            font-size: 4rem;
        }

        .senior-leader-card .leader-name {
            font-size: 2rem;
        }

        .senior-leader-card .leader-bio {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 0 20px;
            margin-top: 80px;
        }
        
        .footer-widget h4 {
            position: relative;
            padding-bottom: 15px;
            color: var(--heavenly-gold);
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
        }
        
        .footer-links li:hover {
            transform: translateX(5px);
        }
        
        .footer-links a:hover {
            color: var(--heavenly-gold) !important;
        }
        
        .social-icon {
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
            color: var(--heavenly-gold) !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .leadership-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .leader-card {
                padding: 2rem;
            }
            
            .leader-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .navbar-brand img {
                width: 30px;
                height: 30px;
            }
        }

        /* Responsive Design - Perfect Mobile Experience */
        @media (max-width: 992px) {
            .leadership-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
            }
            
            .leader-card {
                padding: 2rem;
            }
            
            .leader-image {
                height: 220px;
            }
        }

        @media (max-width: 768px) {
            .hero {
                min-height: 50vh;
                padding: 2rem 0;
            }

            .hero-slide {
                background-size: cover;
                background-position: center;
                filter: contrast(1.2) brightness(1.1) saturate(1.2);
            }

            .hero-slide.active {
                filter: contrast(1.25) brightness(1.15) saturate(1.25);
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 2rem;
            }

            .section {
                padding: 60px 0;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .leadership-grid {
                grid-template-columns: 1fr;
                gap: 1.8rem;
                margin: 0 1rem;
            }

            .leader-card {
                padding: 2rem;
                margin: 0;
            }

            .leader-image {
                height: 200px;
                margin-bottom: 1.5rem;
            }

            .leader-image img {
                width: 95%;
                height: 95%;
                border-radius: 18px;
            }

            .leader-content {
                padding: 1.5rem;
            }

            .leader-name {
                font-size: 1.4rem;
                margin-bottom: 0.6rem;
            }

            .leader-title {
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }

            .leader-bio {
                font-size: 0.9rem;
                line-height: 1.6;
                margin-bottom: 1.5rem;
                min-height: 80px;
            }

            .leader-contact {
                margin-bottom: 1rem;
            }

            .leader-contact a {
                width: 40px;
                height: 40px;
            }

            .leader-contact i {
                font-size: 1rem;
            }

            .cta-title {
                font-size: 2.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-cta {
                width: 250px;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .leadership-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                margin: 0 0.5rem;
            }
            
            .leader-card {
                padding: 1.5rem;
            }
            
            .leader-image {
                height: 180px;
                margin-bottom: 1.2rem;
            }
            
            .leader-image img {
                width: 95%;
                height: 95%;
                border-radius: 15px;
            }
            
            .leader-content {
                padding: 1rem;
            }
            
            .leader-name {
                font-size: 1.3rem;
            }
            
            .leader-title {
                font-size: 0.85rem;
            }
            
            .leader-bio {
                font-size: 0.85rem;
                min-height: 70px;
            }
            
            .leader-contact a {
                width: 35px;
                height: 35px;
            }
            
            .leader-contact i {
                font-size: 0.9rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
        }

        /* Image Loading and Error Handling */
        .leader-image img {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .leader-image img.loaded {
            opacity: 1;
        }

        /* Perfect Image Container */
        .leader-image {
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .leader-image[src]:not([src=""]) {
            background-image: none;
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
    </style>
</head>
<body>
    <!-- Professional Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo" class="me-2" style="width: 40px; height: 40px; border-radius: 50%;">
                <span class="fw-bold">Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="leadership.php"><i class="fas fa-users me-1"></i> Leadership</a></li>
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Our Leadership</h1>
            <p class="hero-subtitle">Meet the dedicated leaders serving God's kingdom at Salem Dominion Ministries</p>
        </div>
    </section>

    <!-- Error Display -->
    <?php if (!empty($errors)): ?>
        <section class="py-5">
            <div class="container">
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo safe_html($error); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Senior Leadership -->
    <?php
    $senior_leaders = array_filter($leadership, function($leader) {
        return in_array(strtolower($leader['role'] ?? ''), ['pastor', 'senior pastor', 'founder']);
    });
    ?>
    
    <?php if (!empty($senior_leaders)): ?>
        <section class="senior-leadership">
            <div class="container">
                <h2 class="section-title">Senior Leadership</h2>
                <div class="row justify-content-center">
                    <?php foreach ($senior_leaders as $leader): ?>
                        <div class="col-lg-8 mb-4">
                            <div class="senior-leader-card">
                                <div class="leader-avatar">
                                    <?php if (!empty($leader['photo'])): ?>
                                        <img src="<?= safe_html($leader['photo']) ?>" alt="<?= safe_html($leader['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <?= strtoupper(substr(safe_html($leader['name']), 0, 1)) ?>
                                    <?php endif; ?>
                                </div>
                                <h3 class="leader-name"><?= safe_html($leader['name']) ?></h3>
                                <div class="leader-role">
                                    <i class="fas <?= get_role_icon($leader['role'] ?? 'leader') ?> role-icon"></i>
                                    <?= safe_html($leader['role']) ?>
                                </div>
                                <p class="leader-bio"><?= safe_html($leader['bio']) ?></p>
                                <?php if (!empty($leader['email']) || !empty($leader['phone'])): ?>
                                    <div class="leader-contact">
                                        <?php if (!empty($leader['email'])): ?>
                                            <a href="mailto:<?= safe_html($leader['email']) ?>" class="contact-item">
                                                <i class="fas fa-envelope me-1"></i>Email
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($leader['phone'])): ?>
                                            <a href="tel:<?= safe_html($leader['phone']) ?>" class="contact-item">
                                                <i class="fas fa-phone me-1"></i>Call
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="leader-actions">
                                    <a href="#" class="btn-leader">
                                        <i class="fas fa-calendar me-2"></i>Schedule Meeting
                                    </a>
                                    <a href="#" class="btn-outline-leader">
                                        <i class="fas fa-pray me-2"></i>Prayer Request
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Leadership Section -->
    <section class="section section-heaven">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Leaders</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Dedicated servants called to lead and shepherd God's people</p>
            
            <div class="leadership-grid">
                <!-- Apostle Faty Musasizi -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="100" style="border: 3px solid var(--heavenly-gold); box-shadow: 0 0 30px rgba(251, 191, 36, 0.3);">
                    <div class="leader-image">
                        <img src="assets/mr-faty.jpeg" alt="Apostle Faty Musasizi" 
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name" style="color: var(--heavenly-gold);">Apostle Faty Musasizi</h3>
                        <p class="leader-title">President, Salem Dominion Ministries</p>
                        <p class="leader-bio">
                            Apostle Faty Musasizi is a visionary servant of God, called to establish and lead Salem Dominion Ministries with apostolic authority and spiritual insight. As both a leader and apostle, he is deeply committed to spreading the Gospel, raising leaders, and transforming lives through the power of God's Word.

                            Together with his beloved wife, Pastor Miriam Musasizi, they faithfully serve in ministry, providing spiritual guidance, mentorship, and care to the body of Christ. Their partnership reflects unity, wisdom, and a shared passion for advancing God's kingdom with love, humility, and purpose.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:pastor@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256753244480" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pastor Miriam Musasizi -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="leader-image">
                        <img src="assets/mirriam.jpeg" alt="Pastor Miriam Musasizi"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Miriam Musasizi</h3>
                        <p class="leader-title">Cells Director, Salem Dominion Ministries</p>
                        <p class="leader-bio">
                            Pastor Miriam Musasizi is a devoted servant of God and beloved wife of Apostle Faty Musasizi. She faithfully serves as the Cells Director at Salem Dominion Ministries, overseeing and nurturing cell groups that strengthen the spiritual growth and unity of the church.

                            With a heart full of compassion and wisdom, she is deeply committed to mentoring believers, raising leaders, and building strong spiritual foundations within the ministry. Together with her husband, she plays a vital role in advancing the vision of the church through prayer, leadership, and dedicated service to God's people.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:joyce@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256750947194" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pastor Damali Namwima -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="leader-image">
                        <img src="assets/Pastor-damali-namwuma-DSRkNJ6q.png" alt="Pastor Damali Namwima"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Damali Namwima</h3>
                        <p class="leader-title">Altar Pastor</p>
                        <p class="leader-bio">
                            Pastor Damali Namwima is a passionate and spirit-filled servant of God who leads the Altar Ministry with dedication and grace. She ministers at the altar, guiding individuals into deeper encounters with God, helping them discover their purpose and calling, and nurturing a strong and consistent life of prayer.

                            Through her ministry, many are strengthened, restored, and empowered to grow in their spiritual walk.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:youth@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256757475816" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                 
                <!-- Pastor Irene Mirembe -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="leader-image">
                        <img src="assets/APOSTLE-IRENE-MIREMBE-CwWfzcRx.jpeg" alt="Pastor Irene Mirembe"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Irene Mirembe</h3>
                        <p class="leader-title">Church Administrator</p>
                        <p class="leader-bio">
                            Pastor Irene Mirembe is a dedicated servant of God who faithfully serves as the Church Administrator at Salem Dominion Ministries. With a heart for excellence and organization, she plays a vital role in coordinating church activities and ensuring the smooth running of ministry operations.

                            Her commitment, wisdom, and passion for God's work continue to support the vision of the ministry, as she serves with diligence, humility, and a deep love for people.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:apostle@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256786990115" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pastor Gerald Tenywa -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="leader-image">
                        <img src="assets/jare.jpeg" alt="Pastor Gerald Tenywa"
                             onerror="this.src='assets/jare.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Gerald Tenywa</h3>
                        <p class="leader-title">Men Ministry Leader</p>
                        <p class="leader-bio">
                            A passionate pastor committed to leading men ministries, Pastor Gerald leads our men ministry, mentoring young and old people to discover their purpose and calling in God. His favorite scripture is John 1:1.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:visit@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256782595395" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pastor Miriam Tenywa -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="leader-image">
                        <img src="assets/Pastor-miriam-Gerald-CApzM7-5.jpeg" alt="Pastor Miriam Tenywa"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Miriam Tenywa</h3>
                        <p class="leader-title">Praise and Worship Leader</p>
                        <p class="leader-bio">
                            Pastor Miriam Tenywa is an anointed musician and passionate worship leader who ushers the congregation into the presence of God through powerful, spirit-filled praise and worship. 

                            With a heart fully devoted to God, she leads with excellence and sensitivity to the Holy Spirit, creating an atmosphere where lives are transformed, hearts are lifted, and true worship is experienced.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:worship@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256702241186" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pastor Nabulya Joyce -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="700">
                    <div class="leader-image">
                        <img src="assets/PASTOR-NABULYA-JOYCE-BdB4SkbM.jpeg" alt="Pastor Nabulya Joyce"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Nabulya Joyce</h3>
                        <p class="leader-title">Accounts & Finance Leader</p>
                        <p class="leader-bio">
                            Pastor Nabulya Joyce is a dedicated servant of God who faithfully oversees the Accounts and Finance department at Salem Dominion Ministries. With integrity, diligence, and wisdom, she ensures proper stewardship and accountability of the ministry's resources.

                            Her commitment to excellence and transparency supports the vision of the church, as she serves with faithfulness, discipline, and a heart devoted to God's work.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:joyce@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256755417717" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Evangelist Kisakye Halima -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="800">
                    <div class="leader-image">
                        <img src="assets/halima-kisakye.jpeg" alt="Evangelist Kisakye Halima"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Evangelist Kisakye Halima</h3>
                        <p class="leader-title">Evangelist & Mission Leader</p>
                        <p class="leader-bio">
                            Evangelist Kisakye Halima is a devoted servant of God, called to lead evangelistic outreach and mission initiatives within our ministry. She passionately ministers the Word of God to communities, helping people encounter Christ and experience transformation in their lives.  

                            Through her leadership in missions, Evangelist Halima inspires believers to walk in faith, serve others, and actively participate in spreading the Gospel, impacting lives both near and far with love, hope, and purpose.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:worship@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256744992074" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Pastor Jonathan Ngobi -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="900">
                    <div class="leader-image">
                        <img src="assets/pastor-jonathan-Ngobi-B-Ezegv1.jpeg" alt="Pastor Jonathan Ngobi"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Jonathan Ngobi</h3>
                        <p class="leader-title">Pastor</p>
                        <p class="leader-bio">
                            Pastor Jonathan Ngobi is a devoted shepherd of God's people, faithfully preaching the Word and nurturing the spiritual growth of the congregation. With compassion, humility, and biblical wisdom, he ministers to individuals, encourages believers in their walk with God, and strengthens the church through prayer, teaching, and pastoral care.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:pastor@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256705615333" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Church Elders Board Section -->
    <section class="section section-heaven">
        <div class="container">
            <!-- Section Title -->
            <h2 class="section-title" data-aos="fade-up">Church Elders Board</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Meet our dedicated elders guiding the congregation
            </p>

            <!-- Elders Grid -->
            <div class="leadership-grid">
                <!-- Elder 1 -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="leader-image">
                        <img src="assets/Kadha.jpeg" alt="Elder Joseph Meddy Mitala"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Elder Joseph Meddy Mitala</h3>
                        <p class="leader-title">Church Elder</p>
                        <p class="leader-bio">
                            Elder Joseph Meddy Mitala is a devoted servant of God, an apostle, and the founder of New Gospel Revival Ministries International, located in Butabala, Kamuli District, Uganda. 
                            He faithfully provides spiritual guidance and pastoral care, strengthening the faith of the congregation and fostering a vibrant, Christ-centered community.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:evangelist@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256704250692" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Elder 2 -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="leader-image">
                        <img src="assets/pastor jotham Bright Mulinde.jpeg" alt="Elder Jotham Bright Mulinde"
                             onerror="this.src='assets/jotham Bright Mulinde.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Elder Jotham Bright Mulinde</h3>
                        <p class="leader-title">Church Elder</p>
                        <p class="leader-bio">
                            Elder Jotham Bright Mulinde is a faithful shepherd of God's people, the founder and leader of Springs of Life Ministries International in Bwoyogerere, Kampala, Uganda. 
                            He provides spiritual oversight and guidance to the congregation with wisdom, humility, and a heart devoted to service. Through prayer, teaching, and counsel, he strengthens the church and helps believers grow in faith and fulfill their God-given purpose.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:evangelist@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/256704797818" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Elder 3 -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="leader-image">
                        <img src="assets/mukisa.jpeg" alt="Elder Kadha Mukisa"
                             onerror="this.src='assets/pastor.jpeg'"
                             loading="lazy">
                    </div>
                    <div class="leader-content">
                        <h3 class="leader-name">Elder Kadha Mukisa</h3>
                        <p class="leader-title">Church Elder</p>
                        <p class="leader-bio">
                            Elder Kadha Mukisa is devoted to spiritual mentorship and pastoral care. He serves as a leading pastor at Faith Harvest Ministries in Nandekula, Iganga, Uganda. 
                            Through his dedication, he nurtures believers, ensuring that every member grows in faith and lives a Christ-centered life.
                        </p>
                        <div class="leader-contact">
                            <a href="mailto:evangelist@<?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="https://wa.me/2567743221340" class="whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
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
                    <div class="footer-widget footer-3d">
                        <h4 class="text-white mb-3 footer-title-3d">
                            <i class="fas fa-church me-2"></i>Salem Dominion Ministries
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
                                <span class="text-white-50">Kampala, Uganda</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                <span class="text-white-50">+256 753 244 480</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <span class="text-white-50">info@salem-dominion-ministries.org</span>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Perfect Image Loading
        document.addEventListener('DOMContentLoaded', function() {
            const leaderImages = document.querySelectorAll('.leader-image img');
            
            leaderImages.forEach(function(img) {
                // Load image with proper error handling
                img.onload = function() {
                    this.classList.add('loaded');
                    this.style.opacity = '1';
                };
                
                img.onerror = function() {
                    // Set fallback image
                    this.src = 'assets/pastor.jpeg';
                    this.classList.add('loaded');
                    this.style.opacity = '1';
                };
                
                // Trigger load if already cached
                if (img.complete) {
                    img.classList.add('loaded');
                    img.style.opacity = '1';
                }
            });
        });

        // Mobile Touch Optimization
        if ('ontouchstart' in window) {
            document.querySelectorAll('.leader-card').forEach(card => {
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                card.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            });
        }

        // Responsive Image Optimization
        function optimizeImages() {
            const width = window.innerWidth;
            const leaderImages = document.querySelectorAll('.leader-image img');
            
            leaderImages.forEach(img => {
                if (width <= 480) {
                    img.style.objectPosition = 'center top';
                } else if (width <= 768) {
                    img.style.objectPosition = 'center';
                } else {
                    img.style.objectPosition = 'center top';
                }
            });
        }

        // Run on load and resize
        window.addEventListener('load', optimizeImages);
        window.addEventListener('resize', optimizeImages);

        // Smooth Scroll for Navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar Scroll Effect
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > lastScroll && currentScroll > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScroll = currentScroll;
        });
    </script>
</body>
</html>
