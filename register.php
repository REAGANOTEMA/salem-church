<?php
// USER REGISTRATION - Salem Dominion Ministries - Professional & Mobile Responsive
require_once 'db_connection.php';

$conn = getConnection();
if (!$conn) {
    // Database connection failed - show detailed error for debugging
    $errors[] = 'Database connection failed. Please check your database credentials.';
    
    // Debug information (remove in production)
    if (isset($_SERVER['HTTP_HOST'])) {
        error_log("Registration DB failed on host: " . $_SERVER['HTTP_HOST']);
        error_log("Server name: " . ($_SERVER['SERVER_NAME'] ?? 'not set'));
        error_log("Server addr: " . ($_SERVER['SERVER_ADDR'] ?? 'not set'));
    }
}

// Initialize variables
$errors = [];
$success = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($first_name) || empty($last_name)) {
        $errors[] = 'First name and last name are required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($mobile)) {
        $errors[] = 'Mobile number is required';
    }
    
    if (empty($country)) {
        $errors[] = 'Country is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            if ($conn) {
                // Check if email already exists
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                if ($check_stmt) {
                    $check_stmt->bind_param("s", $email);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        $errors[] = 'Email already registered. Please use a different email or login.';
                    }
                    
                    $check_stmt->close();
                } else {
                    $errors[] = 'Database preparation error. Please try again.';
                }
                
                // If no errors, register the user
                if (empty($errors)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Generate username from email
                    $username = strtolower(str_replace(['@', '.'], '', $email)) . rand(100, 999);
                    
                    $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, password, phone, country, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'user', 1, NOW())");
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("sssssss", $first_name, $last_name, $email, $username, $hashed_password, $mobile, $country);
                        
                        if ($insert_stmt->execute()) {
                            $success = 'Registration successful! You can now login to your account.';
                            
                            // Clear form
                            $_POST = [];
                        } else {
                            $errors[] = 'Registration failed. Please try again.';
                        }
                        
                        $insert_stmt->close();
                    } else {
                        $errors[] = 'Database preparation error. Please try again.';
                    }
                }
                
                $conn->close();
            } else {
                $errors[] = 'Database connection failed. Please try again later.';
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = 'Database error occurred. Please try again.';
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
    <title>Register | Salem Dominion Ministries</title>
    <meta name="description" content="Join our community at Salem Dominion Ministries - Create your account today">
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
            .text-center { text-align: center; }
            .mb-4 { margin-bottom: 1.5rem; }
            .mt-3 { margin-top: 1rem; }
            @media (max-width: 768px) {
                .col-md-6, .col-lg-4 { flex: 0 0 100%; max-width: 100%; }
            }
        </style>
    </noscript>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <noscript>
        <style>
            /* Fallback fonts if Google Fonts fail */
            body { font-family: Arial, sans-serif; }
            h1, h2, h3, h4, h5, h6 { font-family: Georgia, serif; }
        </style>
    </noscript>
    
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
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/register-hero.jpg');
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

        /* Registration Section */
        .registration-section {
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

        .registration-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .btn-register {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-register:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
            text-decoration: none;
        }

        .btn-outline-register {
            background: transparent;
            color: var(--heavenly-gold);
            border: 2px solid var(--heavenly-gold);
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-outline-register:hover {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
            text-decoration: none;
        }

        /* Benefits Section */
        .benefits-section {
            padding: 60px 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .benefit-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            border-color: var(--heavenly-gold);
        }

        .benefit-icon {
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

        .benefit-card:hover .benefit-icon {
            transform: scale(1.1);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
        }

        .benefit-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .benefit-description {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
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

        /* Enhanced Mobile Responsive */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .registration-card {
                max-width: 600px;
                padding: 2.5rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 0 60px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .registration-section {
                padding: 60px 0;
            }
            
            .registration-card {
                padding: 2rem;
                margin: 0 1rem;
                max-width: 100%;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .navbar-brand img {
                width: 30px;
                height: 30px;
            }
            
            .form-row {
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .registration-card {
                padding: 1.5rem;
                margin: 0 0.5rem;
            }
            
            .form-control, .form-select {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .btn-register, .btn-outline-register {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .navbar-brand {
                font-size: 1.2rem;
            }
            
            .navbar-brand img {
                width: 25px;
                height: 25px;
            }
            
            .password-strength {
                margin-top: 0.3rem;
                height: 4px;
            }
            
            .form-check-label {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.85rem;
            }
            
            .section-title {
                font-size: 1.6rem;
            }
            
            .registration-card {
                padding: 1rem;
                margin: 0 0.25rem;
            }
            
            .form-control, .form-select {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
            
            .btn-register, .btn-outline-register {
                padding: 8px 16px;
                font-size: 0.85rem;
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .navbar-brand span {
                display: none;
            }
            
            .navbar-brand img {
                margin-right: 0;
            }
            
            .form-label {
                font-size: 0.85rem;
            }
            
            .small {
                font-size: 0.75rem;
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

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries">
                <span>Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="sermons.php">Sermons</a></li>
                    <li class="nav-item"><a class="nav-link" href="news.php">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="prophetic-school.php">Prophetic School</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_pastor_call.php" class="text-warning">Book Call</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Join Our Community</h1>
            <p class="hero-subtitle">Create your account and become part of our growing family</p>
        </div>
    </section>

    <!-- Registration Form -->
    <section class="registration-section">
        <div class="container">
            <div class="registration-card">
                <h2 class="section-title">Create Account</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-4">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-1"><?= safe_html($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success mb-4">
                        <?= safe_html($success) ?>
                        <div class="mt-3">
                            <a href="login.php" class="btn btn-register">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Now
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required value="<?= safe_html($_POST['first_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required value="<?= safe_html($_POST['last_name'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required value="<?= safe_html($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number *</label>
                                <input type="tel" name="mobile" class="form-control" required value="<?= safe_html($_POST['mobile'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country *</label>
                                <select name="country" class="form-select" required>
                                    <option value="">Select Country</option>
                                    <option value="Uganda" <?= ($_POST['country'] ?? '') === 'Uganda' ? 'selected' : '' ?>>Uganda</option>
                                    <option value="Kenya" <?= ($_POST['country'] ?? '') === 'Kenya' ? 'selected' : '' ?>>Kenya</option>
                                    <option value="Tanzania" <?= ($_POST['country'] ?? '') === 'Tanzania' ? 'selected' : '' ?>>Tanzania</option>
                                    <option value="Rwanda" <?= ($_POST['country'] ?? '') === 'Rwanda' ? 'selected' : '' ?>>Rwanda</option>
                                    <option value="Other" <?= ($_POST['country'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strength-bar"></div>
                                </div>
                                <small class="text-white-50">Password must be at least 6 characters long</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label text-white-50" for="terms">
                                        I agree to the <a href="#" class="text-warning">Terms of Service</a> and <a href="#" class="text-warning">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-register">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                                <a href="login.php" class="btn btn-outline-register ms-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Already have an account?
                                </a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <h2 class="section-title">Why Join Us?</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-bible"></i>
                        </div>
                        <h3 class="benefit-title">Spiritual Growth</h3>
                        <p class="benefit-description">Access to sermons, teachings, and spiritual resources to deepen your faith</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="benefit-title">Community</h3>
                        <p class="benefit-description">Connect with fellow believers and participate in church activities</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <h3 class="benefit-title">Events & Updates</h3>
                        <p class="benefit-description">Stay informed about upcoming events and church news</p>
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
                            <a href="<?php echo CHURCH_WEBSITE; ?>" target="_blank" class="text-white me-3 social-icon-3d">
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
    
    <script>
        // Password strength checker
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strength-bar');
            
            if (passwordInput && strengthBar) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    if (password.length >= 6) strength++;
                    if (password.length >= 10) strength++;
                    if (/[A-Z]/.test(password)) strength++;
                    if (/[0-9]/.test(password)) strength++;
                    if (/[^A-Za-z0-9]/.test(password)) strength++;
                    
                    strengthBar.className = 'password-strength-bar';
                    
                    if (strength <= 2) {
                        strengthBar.classList.add('strength-weak');
                    } else if (strength <= 3) {
                        strengthBar.classList.add('strength-medium');
                    } else {
                        strengthBar.classList.add('strength-strong');
                    }
                });
            }
        });
    </script>
</body>
</html>
