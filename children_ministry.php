<?php
require_once __DIR__ . '/db_connection.php';

$conn = createDatabaseConnection();

// Initialize variables
$children_programs = [];
$errors = [];

try {
    if ($conn) {
        // Check if children_programs table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'children_programs'");
        if ($table_check && $table_check->num_rows > 0) {
            // Get children ministry programs if database table exists
            $stmt = $conn->prepare("SELECT * FROM children_programs WHERE is_active = 1 ORDER BY program_name ASC");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $children_programs = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        } else {
            // Table doesn't exist, use empty array
            $children_programs = [];
        }
        $conn->close();
    }
} catch (Exception $e) {
    error_log("Children ministry page error: " . $e->getMessage());
    $errors[] = "Unable to load programs data at this time.";
    $children_programs = [];
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
    <title>Children's Ministry | Salem Dominion Ministries</title>
    <meta name="description" content="Raising a generation for Christ through our children's ministry programs">
    <link rel="icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
            --glass: rgba(255, 255, 255, 0.05);
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
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 1rem 0;
            transition: all 0.5s ease;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--heavenly-gold) !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .navbar-brand img {
            height: 45px;
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
            color: var(--snow-white) !important;
            font-weight: 400;
            margin: 0 8px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--heavenly-gold) !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gradient-divine);
            transition: all 0.4s ease;
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.8)), url('assets/children-celebrating-Z18oVWUU.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 120px 0 80px;
            text-align: center;
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
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.1), transparent);
            animation: heroShimmer 15s infinite;
        }

        @keyframes heroShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: titleGlow 4s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% { text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); }
            100% { text-shadow: 0 4px 30px rgba(251, 191, 36, 0.4); }
        }

        .hero-subtitle {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(2rem, 4vw, 3rem);
            color: var(--snow-white);
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: subtitleFloat 6s ease-in-out infinite;
        }

        @keyframes subtitleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Glass Cards */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-divine);
        }

        .glass-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            border-color: var(--heavenly-gold);
        }

        .child-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 15px;
            border: 3px solid var(--heavenly-gold);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .glass-card:hover .child-img {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.4);
        }

        .img-caption {
            font-family: 'Playfair Display', serif;
            color: var(--heavenly-gold);
            margin-top: 1.5rem;
            font-style: italic;
            font-size: 1.3rem;
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            background: var(--gradient-divine);
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="90" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--snow-white);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 2;
        }

        /* Buttons */
        .btn-donate {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .btn-donate::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-donate:hover::before {
            left: 100%;
        }

        .btn-donate:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(220, 38, 38, 0.5);
            color: var(--snow-white);
            text-decoration: none;
        }

        .btn-contact {
            background: transparent;
            color: var(--snow-white);
            border: 2px solid var(--snow-white);
            border-radius: 50px;
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .btn-contact:hover {
            background: var(--snow-white);
            color: var(--midnight-blue);
            transform: translateY(-5px) scale(1.05);
            text-decoration: none;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: var(--snow-white);
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
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.5rem;
            }
            
            .glass-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .child-img {
                height: 200px;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .navbar-brand img {
                height: 35px;
            }
            
            .cta-section {
                padding: 60px 0;
            }
            
            .btn-donate, .btn-contact {
                padding: 12px 25px;
                font-size: 1rem;
                margin: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Professional Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo">
                <span>Salem Dominion Ministries</span>
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
                            <li><a class="dropdown-item active" href="children_ministry.php">Children Ministry</a></li>
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

    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title" data-aos="zoom-in">Children's Ministry</h1>
            <p class="hero-subtitle" data-aos="fade-up">Raising a Generation for Christ</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="glass-card text-center">
                        <img src="assets/children-with-books-Cc2LmxDu.jpeg" class="child-img" alt="Learning">
                        <p class="img-caption">Seeds of Wisdom</p>
                        <p class="small opacity-75">Nurturing young minds with the unchanging truth of God's Word through interactive study.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="glass-card text-center">
                        <img src="assets/children-celebrating-Z18oVWUU.jpeg" class="child-img" alt="Joy">
                        <p class="img-caption">Heavens of Joy</p>
                        <p class="small opacity-75">Celebrating the pure, contagious laughter that fills our sanctuary every Sunday morning.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="glass-card text-center">
                        <img src="assets/children-eating-withpastor-Bagnofdx.jpeg" class="child-img" alt="Fellowship">
                        <p class="img-caption">Divine Fellowship</p>
                        <p class="small opacity-75">Building bonds over shared meals and spiritual nourishment with our leadership.</p>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-right">
                    <div class="glass-card">
                        <img src="assets/children-food-20X1VRUx.jpeg" class="child-img" alt="Care">
                        <p class="img-caption">Grace in Abundance</p>
                        <p class="small opacity-75">Ensuring every child is cared for, showing the practical love of Jesus through service.</p>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-left">
                    <div class="glass-card">
                        <img src="assets/a-kid-showing-how-kindness-isgood-BBxs16el.jpeg" class="child-img" alt="Kindness">
                        <p class="img-caption">Hearts of Gold</p>
                        <p class="small opacity-75">Teaching the power of kindness and character, reflecting the image of our Creator.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section with Donation Buttons -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title" data-aos="zoom-in">Support Our Children's Ministry</h2>
            <p class="cta-subtitle" data-aos="fade-up">
                Your generous donations help us provide quality education, meals, and spiritual guidance to children in our community.
            </p>
            
            <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                <a href="donate.php" class="btn-donate">
                    <i class="fas fa-heart"></i> Donate Now
                </a>
                <a href="contact.php" class="btn-contact">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
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

        /* Enhanced Mobile Responsive Design */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .hero-subtitle {
                font-size: 2.5rem;
            }
            
            .glass-card {
                padding: 2rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 2rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
            
            .navbar-brand img {
                height: 40px;
            }
            
            .glass-card {
                padding: 1.8rem;
            }
            
            .program-card {
                margin-bottom: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }
            
            .hero-title {
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            
            .hero-subtitle {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .navbar {
                padding: 0.5rem 0;
            }
            
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .navbar-brand img {
                height: 35px;
            }
            
            .navbar-nav .nav-link {
                font-size: 0.9rem;
                margin: 0 5px;
            }
            
            .glass-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .program-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .program-card {
                padding: 1.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .section-subtitle {
                font-size: 1rem;
            }
            
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
        
        @media (max-width: 576px) {
            .hero-section {
                padding: 60px 0 40px;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }
            
            .section-title {
                font-size: 1.6rem;
            }
            
            .section-subtitle {
                font-size: 0.9rem;
            }
            
            .glass-card {
                padding: 1.2rem;
                margin-bottom: 1rem;
            }
            
            .program-card {
                padding: 1.2rem;
            }
            
            .program-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .program-title {
                font-size: 1.2rem;
            }
            
            .program-description {
                font-size: 14px;
            }
            
            .btn-program {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            .navbar-brand img {
                height: 30px;
            }
        }
        
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 1.4rem;
            }
            
            .glass-card {
                padding: 1rem;
            }
            
            .program-card {
                padding: 1rem;
            }
            
            .program-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
            }
            
            .program-title {
                font-size: 1.1rem;
            }
            
            .program-description {
                font-size: 13px;
            }
            
            .btn-program {
                padding: 8px 16px;
                font-size: 0.85rem;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .navbar-brand img {
                height: 25px;
            }
        }
        
        @media (max-width: 360px) {
            .hero-title {
                font-size: 1.3rem;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
            
            .glass-card {
                padding: 0.8rem;
            }
            
            .btn-program {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
        
        /* Touch-friendly adjustments */
        @media (hover: none) and (pointer: coarse) {
            .btn-program {
                min-height: 44px;
                min-width: 44px;
            }
            
            .glass-card {
                touch-action: manipulation;
            }
            
            .program-card {
                touch-action: manipulation;
            }
        }
        
        /* Landscape mobile adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .hero-section {
                padding: 40px 0;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }
        }
    </style>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 1000, once: true });</script>
</body>
</html>