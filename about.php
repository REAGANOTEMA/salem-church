<?php
// ABOUT PAGE - Salem Dominion Ministries
session_start();
// Database connection with error handling
require_once 'db_connection.php';

$conn = createDatabaseConnection();
$leadership = [];
$testimonials = [];
$stats = ['ministries' => 6, 'members' => 500, 'events' => 50, 'years' => 15];

try {
    if ($conn) {
        // Get statistics
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ministries WHERE is_active = 1");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['ministries'] = $result->fetch_assoc()['count'] ?? 6;
            $stmt->close();
        }
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['members'] = $result->fetch_assoc()['count'] ?? 500;
            $stmt->close();
        }
        
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE status = 'completed'");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['events'] = $result->fetch_assoc()['count'] ?? 50;
            $stmt->close();
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $leadership = [];
    $testimonials = [];
    $stats = ['ministries' => 6, 'members' => 500, 'events' => 50, 'years' => 15];
}

// Admin session variables
$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    <meta name="description" content="About Salem Dominion Ministries - Our mission, vision, leadership, and history">
    <title>About Us - Salem Dominion Ministries</title>
    
    <!-- PWA Meta Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="About">
    <meta name="application-name" content="SDM About">
    <meta name="msapplication-TileColor" content="#0f172a">
    <meta name="msapplication-config" content="/browserconfig.xml">
    
    <!-- PWA Manifest and Icons -->
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/jpeg" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        /* ICONIC DESIGN SYSTEM - Top Notch Colors Only */
        :root {
            /* Primary Palette - Ultra Premium */
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --sky-blue: #38bdf8;
            --ice-blue: #7dd3fc;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
            
            /* Divine Accents */
            --heavenly-gold: #fbbf24;
            --divine-light: #fef3c7;
            
            /* Shadows & Effects */
            --shadow-divine: 0 20px 40px rgba(15, 23, 42, 0.15);
            --shadow-heavenly: 0 25px 50px rgba(251, 191, 36, 0.2);
            --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.08);
            --shadow-glow: 0 0 40px rgba(14, 165, 233, 0.3);
            
            /* Gradients - Iconic */
            --gradient-ocean: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 50%, var(--sky-blue) 100%);
            --gradient-heaven: linear-gradient(135deg, var(--snow-white) 0%, var(--pearl-white) 50%, var(--ice-blue) 100%);
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--divine-light) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 300;
            line-height: 1.6;
            color: var(--midnight-blue);
            background: var(--snow-white);
            overflow-x: hidden;
            position: relative;
        }

        /* Divine Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(251, 191, 36, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(56, 189, 248, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        /* Typography - Iconic */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1.2;
            color: var(--midnight-blue);
        }

        .font-divine {
            font-family: 'Great Vibes', cursive;
            color: var(--heavenly-gold);
        }

        /* Navigation Styles */
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .dropdown-item:hover {
            background: #667eea;
            color: white;
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
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.7) 0%, rgba(14, 165, 233, 0.5) 100%), url('assets/ourmembers.jpeg');
            background-size: cover;
            background-position: center 20%;
            background-repeat: no-repeat;
            background-attachment: scroll;
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: divineShimmer 15s infinite;
        }

        @keyframes divineShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--heavenly-gold);
            border-radius: 50%;
            opacity: 0.6;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            color: var(--snow-white);
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero-logo {
            margin-bottom: 2rem;
            animation: logoFloat 8s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-15px) scale(1.05); }
        }

        .hero-logo img {
            height: 100px;
            width: 100px;
            border-radius: 50%;
            background: var(--snow-white);
            padding: 12px;
            box-shadow: 0 0 40px rgba(251, 191, 36, 0.4);
            transition: all 0.5s ease;
            filter: brightness(1.1) contrast(1.1);
        }

        .hero-logo:hover img {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 0 70px rgba(251, 191, 36, 0.6);
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.02em;
            animation: titleGlow 4s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% { text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); }
            100% { text-shadow: 0 4px 30px rgba(251, 191, 36, 0.4); }
        }

        .hero-subtitle {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.95;
            letter-spacing: 0.05em;
            animation: subtitleFloat 6s ease-in-out infinite;
        }

        @keyframes subtitleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Sections - Iconic Design */
        .section {
            padding: 100px 0;
            position: relative;
        }

        .section-light {
            background: var(--snow-white);
        }

        .section-heaven {
            background: var(--gradient-heaven);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 3.5rem);
            font-weight: 900;
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-divine);
            border-radius: 2px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 300;
        }

        /* Mission Section */
        .mission-card {
            background: var(--snow-white);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(125, 211, 252, 0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .mission-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .mission-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-divine);
        }

        .mission-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-divine);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--midnight-blue);
            font-size: 2rem;
            margin: 0 auto 2rem;
            box-shadow: 0 15px 35px rgba(251, 191, 36, 0.3);
            transition: all 0.5s ease;
        }

        .mission-card:hover .mission-icon {
            transform: scale(1.1) rotate(15deg);
            box-shadow: 0 20px 45px rgba(251, 191, 36, 0.4);
        }

        .mission-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }

        .mission-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--ocean-blue);
            text-align: center;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            background: var(--gradient-heaven);
            border-radius: 25px;
            border: 1px solid var(--ice-blue);
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-divine);
            background: var(--snow-white);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--heavenly-gold);
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--midnight-blue);
            font-weight: 600;
        }

        /* Leadership Section */
        .leadership-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .leader-card {
            background: var(--snow-white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(125, 211, 252, 0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .leader-card:hover {
            transform: translateY(-15px) rotateX(5deg) rotateY(2deg);
            box-shadow: var(--shadow-divine);
        }

        .leader-image {
            height: 350px;
            width: 100%;
            background: var(--gradient-ocean);
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid var(--heavenly-gold);
        }

        .leader-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center top;
            transition: all 0.6s ease;
            filter: brightness(1.05) contrast(1.05);
        }

        .leader-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(251, 191, 36, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .leader-card:hover .leader-image::before {
            opacity: 1;
        }

        /* 3D Lighting Cross */
        .divine-cross {
            display: inline-block;
            color: var(--heavenly-gold);
            animation: cross-glow 2s infinite alternate ease-in-out;
            filter: drop-shadow(0 0 10px var(--heavenly-gold));
            font-size: 1.5rem;
        }

        @keyframes cross-glow {
            from { transform: scale(1); filter: brightness(1) drop-shadow(0 0 5px var(--heavenly-gold)); }
            to { transform: scale(1.2); filter: brightness(1.5) drop-shadow(0 0 25px var(--heavenly-gold)); }
        }

        .leader-card:hover .leader-image img {
            transform: scale(1.1);
        }

        .leader-content {
            padding: 2.5rem;
            text-align: center;
        }

        .leader-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .leader-title {
            color: var(--heavenly-gold);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .leader-bio {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--ocean-blue);
            margin-bottom: 2rem;
        }

        .leader-contact {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .leader-contact a {
            width: 45px;
            height: 45px;
            background: var(--gradient-ocean);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--snow-white);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .leader-contact a:hover {
            transform: scale(1.1);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
        }

        .leader-contact a.whatsapp:hover {
            background: #25d366;
        }

        /* Testimonials Section */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .testimonial-card {
            background: var(--snow-white);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(125, 211, 252, 0.2);
            transition: all 0.3s ease;
            position: relative;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-divine);
        }

        .testimonial-quote {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--ocean-blue);
            margin-bottom: 2rem;
            font-style: italic;
            position: relative;
        }

        .testimonial-quote::before {
            content: '"';
            font-size: 4rem;
            color: var(--heavenly-gold);
            position: absolute;
            top: -20px;
            left: -10px;
            font-family: 'Playfair Display', serif;
            opacity: 0.3;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: var(--gradient-divine);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--midnight-blue);
            font-weight: 700;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .author-info {
            flex: 1;
        }

        .author-name {
            font-weight: 600;
            color: var(--midnight-blue);
            margin-bottom: 0.25rem;
        }

        .author-role {
            font-size: 0.9rem;
            color: var(--heavenly-gold);
        }

        /* CTA Section */
        .cta-section {
            background: var(--gradient-ocean);
            padding: 100px 0;
            text-align: center;
            color: var(--snow-white);
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: divineShimmer 15s infinite;
        }

        .cta-content {
            position: relative;
            z-index: 10;
        }

        .cta-title {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 900;
            margin-bottom: 2rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .cta-subtitle {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            opacity: 0.95;
        }

        .cta-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 6px 0 #1e293b, 0 12px 20px rgba(0,0,0,0.2);
        }

        .btn-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 0 #1e293b, 0 15px 25px rgba(0,0,0,0.3);
        }

        .btn-cta:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 #1e293b, 0 4px 10px rgba(0,0,0,0.2);
        }

        .btn-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
        }

        .btn-cta:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--snow-white);
            color: var(--midnight-blue);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 255, 255, 0.4);
            color: var(--midnight-blue);
        }

        .btn-outline {
            background: transparent;
            color: var(--snow-white);
            border: 2px solid var(--snow-white);
            backdrop-filter: blur(10px);
        }

        .btn-outline:hover {
            background: var(--snow-white);
            color: var(--midnight-blue);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 255, 255, 0.3);
        }

        /* Responsive Design - Perfect Mobile Experience */
        @media (max-width: 768px) {
            .hero {
                min-height: 60vh;
                padding: 2rem 0;
                background-attachment: scroll;
                background-position: center 30%;
            }

            .hero-title {
                font-size: 2.2rem;
                line-height: 1.2;
            }

            .hero-subtitle {
                font-size: 1.6rem;
            }

            .hero-content {
                padding: 20px 15px;
            }

            .hero-logo img {
                height: 80px;
                width: 80px;
                padding: 10px;
            }

            .section {
                padding: 60px 0;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            .section-subtitle {
                font-size: 1rem;
                margin-bottom: 2rem;
            }

            .mission-card {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }

            .mission-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .mission-title {
                font-size: 1.5rem;
            }

            .mission-description {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .stat-item {
                padding: 1.5rem 1rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .stat-label {
                font-size: 0.9rem;
            }

            .leadership-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .leader-card {
                margin: 0 auto;
                max-width: 400px;
            }

            .leader-image {
                height: 280px;
            }

            .leader-content {
                padding: 1.5rem;
            }

            .leader-name {
                font-size: 1.3rem;
            }

            .leader-title {
                font-size: 1rem;
            }

            .leader-bio {
                font-size: 0.9rem;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .testimonial-card {
                padding: 2rem 1.5rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .btn-cta {
                width: 100%;
                max-width: 280px;
                justify-content: center;
                padding: 15px 25px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .hero {
                min-height: 50vh;
                padding: 1rem 0;
                background-position: center 25%;
            }

            .hero-title {
                font-size: 1.9rem;
            }

            .hero-subtitle {
                font-size: 1.4rem;
            }

            .hero-content {
                padding: 15px 10px;
            }

            .hero-logo img {
                height: 70px;
                width: 70px;
                padding: 8px;
            }

            .section {
                padding: 40px 0;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .section-subtitle {
                font-size: 0.95rem;
            }

            .mission-card {
                padding: 1.5rem 1rem;
            }

            .mission-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }

            .mission-title {
                font-size: 1.3rem;
            }

            .mission-description {
                font-size: 0.95rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.8rem;
            }

            .stat-item {
                padding: 1rem 0.8rem;
            }

            .stat-number {
                font-size: 1.8rem;
            }

            .stat-label {
                font-size: 0.85rem;
            }

            .leadership-grid {
                gap: 1rem;
            }

            .leader-card {
                max-width: 350px;
            }

            .leader-image {
                height: 240px;
            }

            .leader-content {
                padding: 1rem;
            }

            .leader-name {
                font-size: 1.2rem;
            }

            .leader-title {
                font-size: 0.9rem;
            }

            .leader-bio {
                font-size: 0.85rem;
            }

            .leader-contact a {
                width: 40px;
                height: 40px;
            }

            .testimonials-grid {
                gap: 1rem;
            }

            .testimonial-card {
                padding: 1.5rem 1rem;
            }

            .testimonial-quote {
                font-size: 1rem;
            }

            .author-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .author-name {
                font-size: 0.95rem;
            }

            .author-role {
                font-size: 0.85rem;
            }

            .cta-section {
                padding: 60px 0;
            }

            .cta-title {
                font-size: 1.8rem;
            }

            .cta-subtitle {
                font-size: 1.1rem;
            }

            .btn-cta {
                max-width: 250px;
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
        /* Story Section Styles */
        .story-card {
            background: var(--snow-white);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(125, 211, 252, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .story-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .story-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-divine);
        }

        .story-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 2rem;
            text-align: center;
            font-family: 'Great Vibes', cursive;
        }

        .story-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--midnight-blue);
            margin-bottom: 1.5rem;
            text-align: justify;
        }

        .story-text strong {
            color: var(--heavenly-gold);
            font-weight: 600;
        }

        .branches-info {
            background: var(--gradient-heaven);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem 0;
            border: 1px solid var(--ice-blue);
        }

        .branches-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--midnight-blue);
            margin-bottom: 1rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }

        .branches-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .branches-list li {
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(125, 211, 252, 0.2);
            color: var(--ocean-blue);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .branches-list li:last-child {
            border-bottom: none;
        }

        .branches-list li:hover {
            color: var(--midnight-blue);
            transform: translateX(10px);
        }

        .branches-list li i {
            color: var(--heavenly-gold);
        }

        /* Story Image Styles */
        .story-image-container {
            position: relative;
            padding: 2rem;
        }

        .story-frame {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-divine);
            background: var(--snow-white);
            padding: 1rem;
            transition: all 0.5s ease;
        }

        .story-frame:hover {
            transform: translateY(-10px) rotate(1deg);
            box-shadow: var(--shadow-heavenly);
        }

        .story-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            object-position: center;
            border-radius: 15px;
            transition: all 0.5s ease;
            filter: brightness(0.9) contrast(1.1);
        }

        .story-frame:hover .story-image {
            transform: scale(1.05);
            filter: brightness(1) contrast(1.2);
        }

        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.9), transparent);
            padding: 2rem;
            border-radius: 0 0 15px 15px;
            opacity: 0;
            transition: all 0.5s ease;
        }

        .story-frame:hover .image-overlay {
            opacity: 1;
        }

        .overlay-content {
            text-align: center;
            color: var(--snow-white);
        }

        .overlay-icon {
            font-size: 2rem;
            color: var(--heavenly-gold);
            margin-bottom: 0.5rem;
            animation: iconPulse 2s infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .overlay-text {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-family: 'Playfair Display', serif;
        }

        .overlay-date {
            font-size: 0.9rem;
            opacity: 0.8;
            color: var(--divine-light);
        }

        .story-quote {
            background: var(--gradient-divine);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .story-quote::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: quoteShimmer 8s infinite;
        }

        @keyframes quoteShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .quote-icon {
            font-size: 2rem;
            color: var(--snow-white);
            opacity: 0.6;
            margin-bottom: 1rem;
        }

        .quote-text {
            font-size: 1.1rem;
            font-style: italic;
            color: var(--snow-white);
            margin: 0;
            font-family: 'Playfair Display', serif;
            position: relative;
            z-index: 1;
        }

        /* Mobile Responsive for Story Section */
        @media (max-width: 768px) {
            .story-card {
                padding: 2rem 1.5rem;
                margin-bottom: 2rem;
            }

            .story-title {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }

            .story-text {
                font-size: 1rem;
            }

            .branches-info {
                padding: 1.5rem;
                margin: 1.5rem 0;
            }

            .branches-title {
                font-size: 1.2rem;
            }

            .branches-list li {
                font-size: 0.9rem;
            }

            .story-image-container {
                padding: 1rem;
            }

            .story-image {
                height: 300px;
            }

            .image-overlay {
                padding: 1.5rem;
            }

            .overlay-text {
                font-size: 1rem;
            }

            .overlay-date {
                font-size: 0.8rem;
            }

            .story-quote {
                padding: 1.5rem;
                margin-top: 1.5rem;
            }

            .quote-text {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .story-card {
                padding: 1.5rem 1rem;
            }

            .story-title {
                font-size: 1.6rem;
            }

            .story-text {
                font-size: 0.95rem;
            }

            .story-image {
                height: 250px;
            }

            .branches-info {
                padding: 1rem;
            }

            .story-quote {
                padding: 1rem;
            }
        }

        .footer {
    background: #0f172a;
    color: white;
    text-align: center;
    padding: 40px 20px;
}

.footer a {
    color: #38bdf8;
    text-decoration: none;
    margin: 0 10px;
}

.footer a:hover {
    color: #fbbf24;
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
                    <li class="nav-item"><a class="nav-link active" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <!-- Divine Particles -->
        <div class="hero-particles" id="heroParticles"></div>
        
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
            <h1 class="hero-title">About Our Church</h1>
            <p class="hero-subtitle">Our Journey of Faith and Service</p>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="section section-heaven">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Mission & Vision</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Guided by divine purpose and committed to serving our community</p>
            
            <div class="row">
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-cross"></i>
                        </div>
                        <h3 class="mission-title">Our Mission</h3>
                        <p class="mission-description">
                            To spread the Gospel of Jesus Christ, make disciples of all nations, and demonstrate God's love through service, compassion, and community transformation. We are called to be a beacon of hope and light in Iganga and beyond.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="mission-title">Our Vision</h3>
                        <p class="mission-description">
                            To be a vibrant, growing church that impacts generations with the love of Christ, empowering believers to fulfill their divine calling, and transforming communities through the power of the Holy Spirit and the Word of God.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Humble Beginning</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">From a grass-thatched house to a thriving ministry</p>
            
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="story-card">
                        <div class="story-content">
                            <h3 class="story-title font-divine">A Journey of Faith</h3>
                            <p class="story-text">
                                On July 27th, 2022, <strong>Salem Dominion Ministries</strong> began as a divine vision in the heart of <strong> Apostle Musasizi Faty</strong>. What started in a simple grass-thatched house with just <strong>5 faithful members</strong> has blossomed into a thriving spiritual family of <strong>500+ members</strong> both locally and abroad.
                            </p>
                            <p class="story-text">
                                Through unwavering faith, persistent prayer, and the generous hearts of God's people, we've witnessed miraculous growth. From those humble beginnings under a grass roof, God has enabled us to acquire our own land and establish three branches across Iganga Municipality.
                            </p>
                            <div class="branches-info">
                                <h4 class="branches-title">Our Branches</h4>
                                <ul class="branches-list">
                                    <li><i class="fas fa-map-marker-alt me-2"></i><strong>Bulanga Branch</strong> - Luuka District</li>
                                    <li><i class="fas fa-map-marker-alt me-2"></i><strong>Kaliro Branch</strong> - Kaliro District Town</li>
                                    <li><i class="fas fa-map-marker-alt me-2"></i><strong>Idudi Branch</strong> - Bugweri District</li>
                                </ul>
                            </div>
                            <p class="story-text">
                                Every step of this journey testifies to God's faithfulness and the power of unity in Christ. From that first gathering in a grass house to becoming a beacon of hope across districts, we remain committed to our calling: <strong>"Spreading the Gospel, Transforming Lives."</strong>
                            </p>
                            <div class="text-center mt-4">
                                <a href="donate.php" class="btn-cta btn-primary">
                                    <i class="fas fa-heart me-2"></i>Support Our Mission
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="story-image-container">
                        <div class="story-frame">
                            <img src="assets/hat.jpeg" alt="Grass-thatched house where Salem Dominion Ministries began" class="story-image">
                            <div class="image-overlay">
                                <div class="overlay-content">
                                    <i class="fas fa-church overlay-icon"></i>
                                    <p class="overlay-text">Where It All Began</p>
                                    <p class="overlay-date">July 27, 2022</p>
                                </div>
                            </div>
                        </div>
                        <div class="story-quote">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <p class="quote-text">From humble beginnings, God builds mighty testimonies</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Impact</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Numbers that reflect God's faithfulness</p>
            
            <div class="stats-grid">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-number" id="years-of-ministry">4+</div>
                    <div class="stat-label">Years of Ministry</div>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-number"><?php echo number_format($stats['members']); ?>+</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-number"><?php echo $stats['ministries']; ?>+</div>
                    <div class="stat-label">Ministries</div>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="500">
                    <div class="stat-number"><?php echo $stats['events']; ?>+</div>
                    <div class="stat-label">Events Hosted</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Leadership Section -->
    <section class="section section-heaven">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Leadership</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Meet our dedicated team of servant leaders</p>
            
            <div class="leadership-grid">
                <!-- Only Pastor Faty -->
                <div class="leader-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="leader-content">
                        <h3 class="leader-name">Pastor Faty Musasizi</h3>
                        <p class="leader-bio">Senior Pastor and Founder of Salem Dominion Ministries, with over 20 years of dedicated service to God's kingdom.</p>
                        <div class="leader-contact">
                            <a href="mailto:pastor@salem-dominion-ministries.com" class="contact-btn">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <a href="tel:+256753244480" class="contact-btn">
                                <i class="fas fa-phone"></i>
                                </a>
                            <a href="https://wa.me/256753244480" class="contact-btn whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i>
                                </a>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </section>
            
            <!-- View All Leadership Button -->
            <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="500">
                <a href="leadership.php" class="btn-cta btn-primary">
                    <i class="fas fa-users me-2"></i>View Full Leadership Team
                </a>
            </div>
        </div>
    </section>

    <!-- Premium Donate CTA for About Page -->
    <div class="text-center py-5" data-aos="zoom-in">
        <a href="donate.php" class="btn-cta btn-primary shadow-lg">
            <i class="fas fa-heart me-2"></i>Support Our Mission
        </a>
    </div>

    <!-- Testimonials Section -->
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Testimonies</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">What people are saying about our ministry</p>
            
            <div class="testimonials-grid">
                <?php if ($testimonials && count($testimonials) > 0): ?>
                    <?php foreach ((array)$testimonials as $testimonial): ?>
                        <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                            <p class="testimonial-quote">
                                <?php echo safe_html($testimonial['content']); ?>
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar">
                                    <?php echo strtoupper(substr(safe_html($testimonial['name']), 0, 1)); ?>
                                </div>
                                <div class="author-info">
                                    <div class="author-name"><?php echo safe_html($testimonial['name']); ?></div>
                                    <div class="author-role"><?php echo safe_html($testimonial['role'] ?? 'Church Member'); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Sample Testimonials -->
                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="200">
                        <p class="testimonial-quote">
                            Salem Dominion Ministries transformed my life. The teaching is biblical, the worship is powerful, and the community is like family. I've grown so much spiritually since joining.
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">J</div>
                            <div class="author-info">
                                <div class="author-name">John Mukasa</div>
                                <div class="author-role">Church Member</div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="300">
                        <p class="testimonial-quote">
                            The youth ministry is amazing! My teenagers love coming to church and have grown so much in their faith. The leadership genuinely cares about each young person.
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">M</div>
                            <div class="author-info">
                                <div class="author-name">Mary Nakato</div>
                                <div class="author-role">Parent</div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-card" data-aos="fade-up" data-aos-delay="400">
                        <p class="testimonial-quote">
                            Apostle Faty's teachings have opened my eyes to deeper truths in God's Word. The prayer meetings are powerful, and I've experienced breakthrough in my life.
                        </p>
                        <div class="testimonial-author">
                            <div class="author-avatar">S</div>
                            <div class="author-info">
                                <div class="author-name">Samuel Kiggundu</div>
                                <div class="author-role">Business Owner</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <!-- Divine Particles -->
        <div class="hero-particles" id="ctaParticles"></div>
        
        <div class="cta-content">
            <h2 class="cta-title" data-aos="fade-up">Join Our Family</h2>
            <p class="cta-subtitle" data-aos="fade-up" data-aos-delay="100">Become part of our growing community of faith</p>
            
            <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                <a href="contact.php" class="btn-cta btn-primary">
                    <i class="fas fa-phone"></i> Get Connected
                </a>
                <a href="ministries.php" class="btn-cta btn-outline">
                    <i class="fas fa-hands-helping"></i> Join a Ministry
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
                            <a href="https://Salemdominionministries.com" target="_blank" class="text-white me-3">
                                <i class="fas fa-globe fa-lg"></i>
                            </a>
                            <a href="https://youtube.com/@musasizifaty?si=BxEArdVKNKVSac3X" target="_blank" class="text-white me-3">
                                <i class="fab fa-youtube fa-lg"></i>
                            </a>
                            <a href="https://www.tiktok.com/@salem1dominionchurch?_r=1&_t=ZS-95E1n40LieS" target="_blank" class="text-white me-3">
                                <i class="fab fa-tiktok fa-lg"></i>
                            </a>
                            <a href="https://www.facebook.com/share/1CoCEmvnBB/" target="_blank" class="text-white">
                                <i class="fab fa-facebook fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="text-white mb-3">Quick Links</h5>
                        <ul class="list-unstyled">
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
                        <ul class="list-unstyled">
                            <li><a href="prophetic-school.php" class="text-white-50 text-decoration-none">Prophetic School</a></li>
                            <li><a href="book_pastor_call.php" class="text-white-50 text-decoration-none">Book Pastor Call</a></li>
                            <li><a href="children_ministry.php" class="text-white-50 text-decoration-none">Children Ministry</a></li>
                            <li><a href="donate.php" class="text-white-50 text-decoration-none">Give & Donate</a></li>
                            <li><a href="testimonials.php" class="text-white-50 text-decoration-none">Testimonials</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5 class="text-white mb-3">Contact Info</h5>
                        <ul class="list-unstyled">
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
        </div>
    </footer>

    <style>
        /* Footer Styles */
        .footer {
            background: #0f172a;
            color: white;
            text-align: center;
            padding: 40px 20px;
        }

        .footer a {
            color: #38bdf8;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer a:hover {
            color: #fbbf24;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS with safety check
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1200,
                once: true,
                offset: 100,
                easing: 'ease-in-out'
            });
        }

        // Create divine particles
        function createParticles() {
            const particlesContainer = document.getElementById('heroParticles');
            const ctaParticles = document.getElementById('ctaParticles');
            const particleCount = 15;
            
            if (particlesContainer) {
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 20 + 's';
                    particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                    particlesContainer.appendChild(particle);
                }
            }
            
            if (ctaParticles) {
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 20 + 's';
                    particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                    ctaParticles.appendChild(particle);
                }
            }
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Initialize particles
        createParticles();

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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

        // Enhanced parallax effect for hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroContent = document.querySelector('.hero-content');
            const heroSection = document.querySelector('.hero');
            
            if (heroContent && window.innerWidth > 768) {
                heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
                heroContent.style.opacity = Math.max(0.3, 1 - (scrolled / 800));
            }
            
            if (heroSection && window.innerWidth > 1200) {
                heroSection.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });

        // Auto-calculate Years of Ministry from 2022 to current year
        function updateYearsOfMinistry() {
            const startYear = 2022;
            const currentYear = new Date().getFullYear();
            const yearsOfService = currentYear - startYear;
            
            const yearsElement = document.getElementById('years-of-ministry');
            if (yearsElement) {
                yearsElement.textContent = yearsOfService + '+';
            }
        }

        // Update years of ministry on page load
        updateYearsOfMinistry();

        // Update years of ministry at midnight to ensure accuracy
        function scheduleMidnightUpdate() {
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(0, 0, 0, 0);
            
            const msUntilMidnight = tomorrow - now;
            
            setTimeout(() => {
                updateYearsOfMinistry();
                scheduleMidnightUpdate(); // Schedule next update
            }, msUntilMidnight);
        }

        // Start midnight update scheduler
        scheduleMidnightUpdate();
    </script>
</body>
</html>
