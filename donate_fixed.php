<?php
/**
 * Salem Dominion Ministries - Donate Page (Fixed for Hosting)
 * Works perfectly when zipped and hosted
 */

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/db_connection.php';

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['donor_name'] ?? '');
    $email = trim($_POST['donor_email'] ?? '');
    $phone = trim($_POST['donor_phone'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $type = $_POST['donation_type'] ?? '';
    $method = $_POST['payment_method'] ?? '';
    
    // Validation
    if (empty($name)) {
        $errors[] = "Please enter your full name";
    }
    if (empty($phone)) {
        $errors[] = "Please enter your phone number";
    }
    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $errors[] = "Please enter a valid donation amount";
    }
    if (empty($type)) {
        $errors[] = "Please select a donation category";
    }
    if (empty($method)) {
        $errors[] = "Please select a payment method";
    }
    
    // If no errors, process donation
    if (empty($errors)) {
        // Format donation type for display
        $type_display = ucfirst(str_replace('_', ' ', $type));
        $method_display = ucfirst(str_replace('_', ' ', $method));
        
        // Initialize database connection
        try {
            $conn = getConnection();
            
            if ($conn) {
                // Check if donations table exists
                $table_check = $conn->query("SHOW TABLES LIKE 'donations'");
                if ($table_check && $table_check->num_rows > 0) {
                    // Insert into database
                    $sql = "INSERT INTO donations (donor_name, donor_email, donor_phone, amount, donation_type, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("ssssss", $name, $email, $phone, $amount, $type, $method);
                        if ($stmt->execute()) {
                            $donation_id = $conn->insert_id;
                            $success = true;
                            $stmt->close();
                        }
                        $stmt->close();
                    }
                } else {
                    // Table doesn't exist, still proceed with WhatsApp
                    $success = true;
                }
                $conn->close();
            }
        } catch (Exception $e) {
            // Database failed, still proceed with WhatsApp
            $success = true;
        }
        
        // Always redirect to WhatsApp with donation details
        $message = "🙏 Praise God Pastor! I want to give a donation to Salem Dominion Ministries.%0A%0A" .
                  "👤 Name: $name%0A" .
                  "📧 Email: " . (!empty($email) ? $email : "Not provided") . "%0A" .
                  "📱 Phone: $phone%0A" .
                  "💰 Amount: UGX " . number_format($amount) . "%0A" .
                  "🎁 Category: $type_display%0A" .
                  "💳 Payment Method: $method_display%0A" .
                  ($success && isset($donation_id) ? "📋 Donation ID: #$donation_id%0A%0A" : "%0A%0A") .
                  "🙏 May God bless you as you continue serving His kingdom!%0A" .
                  "🤝 Looking forward to connecting with you.";
        
        // Redirect to WhatsApp
        header("Location: https://wa.me/256753244480?text=" . urlencode($message));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give & Donate - Salem Dominion Ministries</title>
    <link rel="icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --sky-blue: #38bdf8;
            --heavenly-gold: #fbbf24;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            line-height: 1.6;
            color: var(--midnight-blue);
            background: linear-gradient(135deg, var(--pearl-white) 0%, rgba(255, 255, 255, 0.9) 100%);
            min-height: 100vh;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(251, 191, 36, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(56, 189, 248, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
            padding: 1rem 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--midnight-blue) !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
            border-radius: 50%;
            background: var(--snow-white);
            padding: 5px;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            transition: all 0.5s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.5);
        }

        .navbar-nav .nav-link {
            color: var(--midnight-blue) !important;
            font-weight: 500;
            font-size: 0.95rem;
            margin: 0 12px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--heavenly-gold) !important;
            font-weight: 600;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 50%, var(--sky-blue) 100%);
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
            text-align: center;
            color: var(--snow-white);
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

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: titleGlow 4s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% { text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); }
            100% { text-shadow: 0 4px 30px rgba(251, 191, 36, 0.4); }
        }

        .hero-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: subtitleFloat 6s ease-in-out infinite;
        }

        @keyframes subtitleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Donation Form Section */
        .donation-section {
            padding: 80px 0;
            position: relative;
        }

        .donation-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
            backdrop-filter: blur(25px);
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 
                0 20px 60px rgba(15, 23, 42, 0.1),
                0 0 120px rgba(251, 191, 36, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            position: relative;
            overflow: hidden;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .donation-card::before {
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

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .donation-card:hover {
            transform: translateY(-10px);
            box-shadow: 
                0 30px 80px rgba(15, 23, 42, 0.15),
                0 0 150px rgba(251, 191, 36, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1rem;
            text-align: center;
        }

        .form-subtitle {
            text-align: center;
            color: var(--ocean-blue);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--midnight-blue);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            border: 2px solid rgba(14, 165, 233, 0.2);
            border-radius: 15px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 0 0.2rem rgba(251, 191, 36, 0.25);
            background: rgba(255, 255, 255, 0.95);
            outline: none;
        }

        .btn-donate {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            padding: 18px 32px;
            font-size: 1.2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 10px 30px rgba(37, 211, 102, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-donate::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-donate:hover::before {
            left: 100%;
        }

        .btn-donate:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 
                0 15px 40px rgba(251, 191, 36, 0.4),
                inset 0 2px 0 rgba(255, 255, 255, 0.5);
            color: var(--midnight-blue);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 100%);
            color: var(--snow-white);
            padding: 60px 0 30px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
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

        .footer-title {
            color: var(--heavenly-gold);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .footer-link:hover {
            color: var(--heavenly-gold);
            transform: translateX(5px);
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--snow-white);
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .social-icon:hover {
            background: var(--heavenly-gold);
            color: var(--midnight-blue);
            transform: translateY(-5px) rotate(360deg);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }

            .donation-section {
                padding: 60px 0;
            }

            .donation-card {
                padding: 2rem;
                margin: 0 1rem;
            }

            .form-title {
                font-size: 2rem;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }

            .navbar-brand img {
                height: 35px;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1.5rem;
            }

            .donation-card {
                padding: 1.5rem;
                margin: 0 0.5rem;
            }

            .form-title {
                font-size: 1.8rem;
            }

            .btn-donate {
                padding: 14px 24px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries">
                Salem Dominion
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="leadership.php">Leadership</a></li>
                    <li class="nav-item"><a class="nav-link" href="sermons.php">Sermons</a></li>
                    <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link active" href="donate_fixed.php">Give</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Generous Giving</h1>
            <p class="hero-subtitle">"Every man shall give as he is able, according to the blessing of the LORD."</p>
            <p class="lead">- Deuteronomy 16:17</p>
        </div>
    </section>

    <!-- Donation Form Section -->
    <section class="donation-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <div class="donation-card">
                        <h2 class="form-title">Plant Your Seed</h2>
                        <p class="form-subtitle">Your generosity helps us spread the Gospel and transform lives</p>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="donationForm">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="donor_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['donor_name'] ?? ''); ?>" 
                                           required placeholder="Enter your full name">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="donor_email" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['donor_email'] ?? ''); ?>" 
                                           placeholder="your.email@example.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">WhatsApp Phone *</label>
                                    <input type="tel" name="donor_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['donor_phone'] ?? ''); ?>" 
                                           required placeholder="+256 7XX XXX XXX">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Donation Amount (UGX) *</label>
                                    <input type="number" name="amount" class="form-control" 
                                           value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" 
                                           required placeholder="50000" min="1000" step="1000">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giving Category *</label>
                                    <select name="donation_type" class="form-select" required>
                                        <option value="">Select category</option>
                                        <option value="tithe" <?php echo (($_POST['donation_type'] ?? '') === 'tithe') ? 'selected' : ''; ?>>Tithe</option>
                                        <option value="offering" <?php echo (($_POST['donation_type'] ?? '') === 'offering') ? 'selected' : ''; ?>>Sunday Offering</option>
                                        <option value="building_fund" <?php echo (($_POST['donation_type'] ?? '') === 'building_fund') ? 'selected' : ''; ?>>Building Fund</option>
                                        <option value="missions" <?php echo (($_POST['donation_type'] ?? '') === 'missions') ? 'selected' : ''; ?>>Missions & Outreach</option>
                                        <option value="children_ministry" <?php echo (($_POST['donation_type'] ?? '') === 'children_ministry') ? 'selected' : ''; ?>>Children Ministry</option>
                                        <option value="special" <?php echo (($_POST['donation_type'] ?? '') === 'special') ? 'selected' : ''; ?>>Special Offering</option>
                                        <option value="general" <?php echo (($_POST['donation_type'] ?? '') === 'general') ? 'selected' : ''; ?>>General Offering</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Preferred Payment Method *</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select payment method</option>
                                    <option value="mobile_money" <?php echo (($_POST['payment_method'] ?? '') === 'mobile_money') ? 'selected' : ''; ?>>Mobile Money (MTN/Airtel)</option>
                                    <option value="bank_transfer" <?php echo (($_POST['payment_method'] ?? '') === 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                    <option value="cash" <?php echo (($_POST['payment_method'] ?? '') === 'cash') ? 'selected' : ''; ?>>In-Person / Cash</option>
                                    <option value="online" <?php echo (($_POST['payment_method'] ?? '') === 'online') ? 'selected' : ''; ?>>Online Payment</option>
                                    <option value="card" <?php echo (($_POST['payment_method'] ?? '') === 'card') ? 'selected' : ''; ?>>Card Payment</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-donate w-100 py-3">
                                <i class="fab fa-whatsapp me-2"></i>
                                Send Donation via WhatsApp
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Your information is secure and will only be used for donation processing
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="footer-title">Salem Dominion Ministries</h5>
                    <p class="mb-3">Transforming lives through the power of God's Word and the love of Christ.</p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/salemdominionministries" target="_blank" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.youtube.com/@salemdominionministries" target="_blank" class="social-icon">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.tiktok.com/@salemdominionministries" target="_blank" class="social-icon">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="https://wa.me/256753244480" target="_blank" class="social-icon">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="footer-title">Quick Links</h5>
                    <div class="d-flex flex-column">
                        <a href="about.php" class="footer-link">About Us</a>
                        <a href="leadership.php" class="footer-link">Leadership</a>
                        <a href="sermons.php" class="footer-link">Sermons</a>
                        <a href="events.php" class="footer-link">Events</a>
                        <a href="contact.php" class="footer-link">Contact</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 mb-4">
                    <h5 class="footer-title">Contact Info</h5>
                    <div class="d-flex flex-column">
                        <a href="tel:+256753244480" class="footer-link">
                            <i class="fas fa-phone me-2"></i>+256 753 244 480
                        </a>
                        <a href="mailto:info@salem-dominion-ministries.org" class="footer-link">
                            <i class="fas fa-envelope me-2"></i>info@salem-dominion-ministries.org
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fas fa-map-marker-alt me-2"></i>Nampirika, Iganga District, Uganda
                        </a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="footer-link me-3">Privacy Policy</a>
                    <a href="terms.php" class="footer-link">Terms of Service</a>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <small class="text-muted">
                        <i class="fas fa-code me-2"></i>Designed & Developed by Mr. Reagan Otema | 
                        <a href="https://wa.me/256772514889" target="_blank" class="text-warning text-decoration-none">
                            <i class="fab fa-whatsapp me-1"></i>+256 772 514 889
                        </a>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <script>
        // Form validation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            const amount = document.querySelector('input[name="amount"]').value;
            
            // Amount validation only
            if (amount < 1000) {
                e.preventDefault();
                alert('Minimum donation amount is UGX 1,000');
                return false;
            }
            
            // Allow form submission
            return true;
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '0.5rem 0';
                navbar.style.boxShadow = '0 8px 32px rgba(15, 23, 42, 0.15)';
            } else {
                navbar.style.padding = '1rem 0';
                navbar.style.boxShadow = '0 4px 20px rgba(15, 23, 42, 0.08)';
            }
        });
    </script>
</body>
</html>
