<?php
// LOGIN PAGE - Salem Dominion Ministries - Professional & Mobile Responsive
require_once 'db_connection.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = createDatabaseConnection();

// Initialize variables
$errors = [];
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $errors[] = 'Please enter both username and password';
    } else {
        try {
            if ($conn) {
                // Check admin users first
                $admin_stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
                if ($admin_stmt) {
                    $admin_stmt->bind_param("s", $username);
                    $admin_stmt->execute();
                    $admin_result = $admin_stmt->get_result();
                    
                    if ($admin_result->num_rows > 0) {
                        $admin = $admin_result->fetch_assoc();
                        
                        if (password_verify($password, $admin['password'])) {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_user_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            $_SESSION['admin_role'] = $admin['role'];
                            
                            // Update last login
                            $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                            if ($update_stmt) {
                                $update_stmt->bind_param("i", $admin['id']);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                            
                            header('Location: admin_dashboard.php');
                            exit;
                        }
                    }
                    
                    $admin_stmt->close();
                }
                
                // Check regular users (using email as username)
                $user_stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
                if ($user_stmt) {
                    $user_stmt->bind_param("s", $username);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    
                    if ($user_result->num_rows > 0) {
                        $user = $user_result->fetch_assoc();
                        
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['user_logged_in'] = true;
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_role'] = 'user';
                            
                            // Update last login
                            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                            if ($update_stmt) {
                                $update_stmt->bind_param("i", $user['id']);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                            
                            header('Location: dashboard.php');
                            exit;
                        }
                    }
                    
                    $user_stmt->close();
                }
                
                $errors[] = 'Invalid username or password';
            }
        } catch (Exception $e) {
            $errors[] = 'Login failed. Please try again.';
        }
    }
}

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
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    <title>Login | Salem Dominion Ministries</title>
    <meta name="description" content="Login to your Salem Dominion Ministries account">
    <link rel="icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <noscript>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    </noscript>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <noscript>
        <link href="https://stackpath.bootstrapcdn.com/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </noscript>
    <!-- Mobile Responsive CSS -->
    <link href="assets/mobile-responsive.css" rel="stylesheet">
    <noscript>
        <style>
            /* Fallback basic styles if mobile-responsive.css fails */
            .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
            .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
            .col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0 15px; }
            .col-lg-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0 15px; }
            .col-lg-3 { flex: 0 0 25%; max-width: 25%; padding: 0 15px; }
            .text-center { text-align: center; }
            .mb-4 { margin-bottom: 1.5rem; }
            .mt-3 { margin-top: 1rem; }
            .me-3 { margin-right: 1rem; }
            @media (max-width: 768px) {
                .col-md-6, .col-lg-4, .col-lg-3 { flex: 0 0 100%; max-width: 100%; }
            }
        </style>
    </noscript>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&family=Great+Vibes&display=swap" rel="stylesheet">
    <noscript>
        <style>
            /* Fallback fonts if Google Fonts fail */
            body { font-family: Arial, sans-serif; }
            h1, h2, h3, h4, h5, h6 { font-family: Georgia, serif; }
            .font-divine { font-family: cursive; color: #fbbf24; }
        </style>
    </noscript>
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <noscript>
        <style>
            /* Fallback animations if AOS fails */
            [data-aos] { opacity: 1; transform: none; }
        </style>
    </noscript>
    
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
            min-height: 100vh;
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

        /* Section Styles */
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

        /* Section Titles */
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

        /* Navigation */
        /* Navigation Styles - Matching Index Page */
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(90deg, var(--ocean-blue), var(--sky-blue));
            color: white;
        }
        
        .navbar-brand img {
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar-brand img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .navbar-nav .nav-link {
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            transform: translateY(-2px);
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

        /* Hero Section - Mindblowing */
        .hero-section {
            background: var(--gradient-ocean);
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
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
            position: relative;
            display: inline-block;
        }

        .hero-logo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 40px rgba(251, 191, 36, 0.4);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            animation: logoGlow 3s ease-in-out infinite alternate;
        }

        @keyframes logoGlow {
            0% { 
                box-shadow: 0 0 40px rgba(251, 191, 36, 0.4);
                transform: scale(1);
            }
            100% { 
                box-shadow: 0 0 60px rgba(251, 191, 36, 0.6);
                transform: scale(1.05);
            }
        }

        .hero-logo img:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 80px rgba(251, 191, 36, 0.8);
            border-color: rgba(251, 191, 36, 0.4);
        }

        .hero-logo::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 50%;
            background: linear-gradient(45deg, transparent, rgba(251, 191, 36, 0.1), transparent);
            animation: logoRotate 8s linear infinite;
            z-index: -1;
        }

        @keyframes logoRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        /* Login Section */
        .login-section {
            padding: 80px 0;
            position: relative;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: var(--shadow-divine);
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .login-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-heavenly);
            border-color: var(--heavenly-gold);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(125, 211, 252, 0.2);
            color: var(--midnight-blue);
            border-radius: 15px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            color: var(--midnight-blue);
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(15, 23, 42, 0.5);
        }

        .form-label {
            color: var(--midnight-blue);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        /* Login Specific Styles */
        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--midnight-blue);
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .login-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--gradient-divine);
            border-radius: 2px;
        }

        .login-link {
            color: var(--heavenly-gold);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link:hover {
            color: var(--ocean-blue);
            text-decoration: none;
            transform: translateY(-2px);
        }

        .login-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient-divine);
            transition: all 0.3s ease;
        }

        .login-link:hover::after {
            width: 100%;
        }

        .btn-login {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            width: 100%;
            justify-content: center;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(251, 191, 36, 0.4);
            color: var(--snow-white);
            text-decoration: none;
        }

        .btn-outline-login {
            background: transparent;
            color: var(--heavenly-gold);
            border: 2px solid var(--heavenly-gold);
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .btn-outline-login:hover {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: var(--gradient-heaven);
            position: relative;
        }

        .feature-card {
            background: var(--snow-white);
            border-radius: 25px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(125, 211, 252, 0.2);
            box-shadow: var(--shadow-soft);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .feature-card:hover {
            transform: translateY(-15px);
            border-color: var(--heavenly-gold);
            box-shadow: var(--shadow-divine);
        }

        .feature-icon {
            width: 90px;
            height: 90px;
            background: var(--gradient-ocean);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
            color: var(--snow-white);
            transition: all 0.5s ease;
            position: relative;
            box-shadow: var(--shadow-soft);
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-heavenly);
        }

        .feature-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1.5rem;
        }

        .feature-description {
            color: var(--ocean-blue);
            line-height: 1.8;
            font-size: 1.1rem;
        }

        /* Footer 3D Effects */
        .footer {
            background: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 100%);
            color: var(--snow-white);
            padding: 80px 0 20px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05), transparent);
            animation: footerShimmer 20s infinite;
        }

        @keyframes footerShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
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

        /* Responsive Design - Mobile First */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .hero-section {
                min-height: 50vh;
                padding: 4rem 0;
            }

            .hero-title {
                font-size: 2.2rem;
                margin-bottom: 1rem;
            }

            .hero-subtitle {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .login-section {
                padding: 60px 0;
            }

            .login-card {
                padding: 2rem;
                margin: 0 1rem;
                max-width: 100%;
            }

            .login-title {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .navbar-brand img {
                height: 40px;
            }

            .features-section {
                padding: 60px 0;
            }

            .feature-card {
                margin-bottom: 2rem;
                padding: 2rem;
            }

            .feature-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }

            .feature-title {
                font-size: 1.3rem;
            }

            .feature-description {
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .login-link {
                font-size: 0.9rem;
            }
        }

        /* Extra Small Devices */
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 1.3rem;
            }

            .login-card {
                padding: 1.5rem;
                margin: 0 0.5rem;
            }

            .login-title {
                font-size: 1.6rem;
                margin-bottom: 1.2rem;
            }

            .form-control {
                padding: 12px 15px;
                font-size: 0.9rem;
            }

            .btn-login {
                padding: 12px 20px;
                font-size: 0.9rem;
            }

            .btn-outline-login {
                padding: 10px 15px;
                font-size: 0.85rem;
            }

            .navbar-brand span {
                display: none;
            }

            .navbar-brand img {
                margin-right: 0;
            }

            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .feature-title {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .login-link {
                font-size: 0.85rem;
            }
        }

        /* Tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .login-card {
                max-width: 450px;
            }

            .hero-section {
                min-height: 55vh;
            }

            .login-title {
                font-size: 1.9rem;
            }

            .section-title {
                font-size: 2.2rem;
            }
        }

        /* 3D Footer Effects - Matching Index Page */
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

        /* Footer Logo Styles */
        .footer-logo {
            transition: all 0.3s ease;
        }
        
        .footer-logo:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.4);
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
            
            .footer-logo {
                max-height: 60px !important;
            }
        }

        /* Alert Styles */
        .alert {
            border-radius: 15px;
            border: none;
            backdrop-filter: blur(20px);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Remember Me Checkbox */
        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--heavenly-gold);
            border-color: var(--heavenly-gold);
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
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Divine Particles -->
        <div class="hero-particles" id="heroParticles"></div>
        
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
            <div class="hero-logo">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries">
            </div>
            <h1 class="hero-title">Welcome Back</h1>
            <p class="hero-subtitle">Login to access your account and continue your spiritual journey</p>
        </div>
    </section>

    <!-- Login Form -->
    <section class="section section-light">
        <div class="container">
            <div class="login-card">
                <h2 class="login-title">Member Login</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-4">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-1"><?= safe_html($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="username" class="form-control" required value="<?= safe_html($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                    <div class="text-center">
                        <a href="#" class="login-link me-3">Forgot Password?</a>
                        <a href="register.php" class="login-link">Create Account</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section section-heaven">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Why Login?</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3 class="feature-title">Personal Dashboard</h3>
                        <p class="feature-description">Access your personalized dashboard with your profile and activities</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                        <h3 class="feature-title">Save Content</h3>
                        <p class="feature-description">Bookmark your favorite sermons, events, and resources</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h3 class="feature-title">Stay Updated</h3>
                        <p class="feature-description">Receive notifications about upcoming events and new content</p>
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
                            <a href="<?php echo CHURCH_WEBSITE; ?>" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fas fa-globe fa-lg"></i>
                            </a>
                            <a href="https://youtube.com/@musasizifaty?si=BxEArdVKNKVSac3X" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fab fa-youtube fa-lg"></i>
                            </a>
                            <a href="https://www.tiktok.com/@salem1dominionchurch?_r=1&__t=ZS-95E1n40LieS" target="_blank" class="text-white me-3 social-icon-3d">
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
    <script>
        // Fallback if Bootstrap JS fails
        if (typeof bootstrap === 'undefined') {
            // Basic Bootstrap functionality fallback
            window.bootstrap = {
                Dropdown: function(element) {
                    element.addEventListener('click', function(e) {
                        e.preventDefault();
                        const menu = element.nextElementSibling;
                        if (menu) {
                            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                        }
                    });
                }
            };
            
            // Initialize dropdowns
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(element) {
                new bootstrap.Dropdown(element);
            });
        }
    </script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS with fallback
        try {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 1200,
                    once: true,
                    offset: 100,
                    easing: 'ease-in-out'
                });
            }
        } catch (e) {
            console.log('AOS initialization failed, using fallback');
            // Fallback: make all elements visible
            document.querySelectorAll('[data-aos]').forEach(function(element) {
                element.style.opacity = '1';
                element.style.transform = 'none';
            });
        }
    </script>
    
    <script>

        // Create divine particles
        function createParticles() {
            const particlesContainer = document.getElementById('heroParticles');
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

        // Add parallax effect to hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
                heroContent.style.opacity = 1 - (scrolled / 600);
            }
        });
    </script>
</body>
</html>
