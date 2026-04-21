<?php
// TESTIMONIALS PAGE - Salem Dominion Ministries - Professional & Mobile Responsive
require_once 'config.php';
require_once 'db_connection.php';

$conn = getConnection();

// Initialize variables
$errors = [];
$success = '';
$featured_testimonials = [];
$all_testimonials = [];

// Handle testimonial submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $testimonial = trim($_POST['testimonial'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);

    if (empty($name) || empty($testimonial)) {
        $errors[] = 'Please fill in all required fields.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            if ($conn) {
                $stmt = $conn->prepare("INSERT INTO testimonials (name, email, occupation, testimonial, rating, is_approved) VALUES (?, ?, ?, ?, ?, FALSE)");
                if ($stmt) {
                    $stmt->bind_param("ssssii", $name, $email, $occupation, $testimonial, $rating, $is_approved);
                    $is_approved = 0;
                    $stmt->execute();
                    $stmt->close();
                    $success = 'Thank you for sharing your testimony! It will be reviewed and published soon.';
                }
            }
        } catch (Exception $e) {
            $errors[] = 'Database error occurred. Please try again.';
        }
    }
}

try {
    if ($conn) {
        // Get featured testimonials
        $featured_stmt = $conn->prepare("SELECT * FROM testimonials WHERE is_approved = 1 AND is_featured = 1 ORDER BY rating DESC, created_at DESC LIMIT 6");
        if ($featured_stmt) {
            $featured_stmt->execute();
            $featured_result = $featured_stmt->get_result();
            $featured_testimonials = $featured_result->fetch_all(MYSQLI_ASSOC);
            $featured_stmt->close();
        }
        
        // Get all approved testimonials
        $all_stmt = $conn->prepare("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC");
        if ($all_stmt) {
            $all_stmt->execute();
            $all_result = $all_stmt->get_result();
            $all_testimonials = $all_result->fetch_all(MYSQLI_ASSOC);
            $all_stmt->close();
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    $featured_testimonials = [];
    $all_testimonials = [];
}

// Calculate stats
$total_testimonials = count($all_testimonials) ?? 0;
$average_rating = 0;
if ($total_testimonials > 0) {
    $rating_sum = 0;
    foreach ($all_testimonials as $t) {
        $rating_sum += $t['rating'];
    }
    $average_rating = round($rating_sum / $total_testimonials, 1);
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}

// Helper function to format date
function format_testimonial_date($date) {
    return date('F j, Y', strtotime($date));
}

// Helper function to render star rating
function render_star_rating($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials | Salem Dominion Ministries</title>
    <meta name="description" content="Read inspiring testimonies of God's faithfulness at Salem Dominion Ministries">
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
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1.2;
            color: var(--midnight-blue);
        }

        .font-divine {
            font-family: 'Great Vibes', cursive;
            color: var(--heavenly-gold);
        }

        /* Navigation - Iconic */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-soft);
            padding: 1rem 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1000;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: var(--shadow-divine);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--midnight-blue) !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .navbar-brand img {
            height: 50px;
            width: auto;
            border-radius: 50%;
            background: var(--gradient-heaven);
            padding: 8px;
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.3);
            transition: all 0.5s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 40px rgba(251, 191, 36, 0.5);
        }

        .navbar-nav .nav-link {
            color: var(--midnight-blue) !important;
            font-weight: 400;
            font-size: 0.95rem;
            margin: 0 12px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--ocean-blue) !important;
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
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        /* Hero Section - Mindblowing */
        .hero {
            background: var(--gradient-ocean);
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
            height: 120px;
            width: auto;
            border-radius: 50%;
            background: var(--snow-white);
            padding: 15px;
            box-shadow: 0 0 50px rgba(251, 191, 36, 0.4);
            transition: all 0.5s ease;
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

        /* Stats Section */
        .stats-bar {
            background: var(--gradient-heaven);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 4rem;
            text-align: center;
            border: 1px solid var(--ice-blue);
        }

        .stats-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 3rem;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--heavenly-gold);
            font-family: 'Playfair Display', serif;
        }

        .stat-label {
            color: var(--midnight-blue);
            font-weight: 600;
        }

        /* Testimonial Cards */
        .testimonial-card {
            background: var(--snow-white);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(125, 211, 252, 0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            height: 100%;
        }

        .testimonial-card:hover {
            transform: translateY(-15px);
            box-shadow: var(--shadow-divine);
        }

        .testimonial-content {
            padding: 2rem;
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient-divine);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--snow-white);
            font-size: 1.5rem;
            font-weight: 700;
            margin-right: 1rem;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
        }

        .testimonial-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .testimonial-info {
            flex: 1;
        }

        .testimonial-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 0.3rem;
        }

        .testimonial-occupation {
            color: var(--ocean-blue);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .testimonial-rating {
            margin-bottom: 1rem;
        }

        .rating-stars {
            color: var(--heavenly-gold);
            font-size: 1.1rem;
        }

        .testimonial-quote {
            position: relative;
            padding-left: 2rem;
            font-style: italic;
            color: var(--midnight-blue);
            line-height: 1.6;
            font-size: 1rem;
        }

        .testimonial-quote::before {
            content: '\201C';
            position: absolute;
            left: 0;
            top: -10px;
            font-size: 3rem;
            color: var(--heavenly-gold);
            opacity: 0.3;
            font-family: Georgia, serif;
        }

        .testimonial-date {
            text-align: right;
            color: var(--gray-medium);
            font-size: 0.8rem;
            margin-top: 1rem;
        }

        /* Form Styles */
        .testimonial-form {
            background: var(--gradient-heaven);
            padding: 3rem;
            border-radius: 25px;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--ice-blue);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--midnight-blue);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--ice-blue);
            border-radius: 15px;
            background: var(--snow-white);
            color: var(--midnight-blue);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--ocean-blue);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .btn-testimonial {
            background: var(--gradient-ocean);
            color: var(--snow-white);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-testimonial:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
            color: var(--snow-white);
        }

        /* Alert Styles */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Featured Badge */
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gradient-divine);
            color: var(--snow-white);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero {
                min-height: 50vh;
                padding: 2rem 0;
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

            .testimonial-form {
                padding: 2rem;
            }

            .stats-content {
                gap: 1.5rem;
            }

            .testimonial-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1.6rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .testimonial-form {
                padding: 1.5rem;
            }

            .stats-content {
                gap: 1rem;
            }

            .stat-number {
                font-size: 2rem;
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
                            <li><a class="dropdown-item" href="testimonials.php" class="active">Testimonials</a></li>
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
    <section class="hero">
        <!-- Divine Particles -->
        <div class="hero-particles" id="heroParticles"></div>
        
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
            <div class="mb-4">
                <span class="font-divine" style="font-size: 4rem;">💝</span>
            </div>
            <h1 class="hero-title">Testimonies</h1>
            <p class="hero-subtitle">"Come and hear, all you who fear God; let me tell you what he has done for me." - Psalm 66:16</p>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section section-heaven">
        <div class="container">
            <div class="stats-bar" data-aos="fade-up">
                <div class="stats-content">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo number_format($total_testimonials); ?></div>
                        <div class="stat-label">Total Testimonies</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $average_rating; ?></div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">⭐</div>
                        <div class="stat-label">5 Star Reviews</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">💝</div>
                        <div class="stat-label">God's Faithfulness</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Testimonials -->
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Featured Testimonies</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Stories of God's amazing grace and transformation</p>
            
            <div class="row g-4">
                <?php if (!empty($featured_testimonials)): ?>
                    <?php foreach ($featured_testimonials as $t): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo (200 + (array_search($t, $featured_testimonials) * 100)); ?>">
                            <div class="testimonial-card">
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i> Featured
                                </div>
                                <div class="testimonial-content">
                                    <div class="testimonial-header">
                                        <?php if ($t['photo_url']): ?>
                                            <div class="testimonial-avatar">
                                                <img src="<?php echo htmlspecialchars($t['photo_url']); ?>" alt="<?php echo htmlspecialchars($t['name']); ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="testimonial-avatar">
                                                <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="testimonial-info">
                                            <div class="testimonial-name"><?php echo htmlspecialchars($t['name']); ?></div>
                                            <?php if ($t['occupation']): ?>
                                                <div class="testimonial-occupation"><?php echo htmlspecialchars($t['occupation']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="testimonial-rating">
                                        <div class="rating-stars">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i < $t['rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="testimonial-quote">
                                        <?php echo nl2br(htmlspecialchars($t['testimonial'])); ?>
                                    </div>
                                    <div class="testimonial-date">
                                        <?php echo date('M j, Y', strtotime($t['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5" data-aos="fade-up">
                        <div class="testimonial-avatar mx-auto mb-3">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <h4 class="text-muted">No featured testimonies yet</h4>
                        <p class="text-muted">Be the first to share your testimony!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Submit Testimonial Form & All Testimonials -->
    <section class="section section-heaven">
        <div class="container">
            <div class="row">
                <!-- Submit Testimonial Form -->
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-form" data-aos="fade-up">
                        <h3 class="section-title" style="font-size: 1.8rem; margin-bottom: 2rem;">Share Your Testimony</h3>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?php echo safe_html($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo safe_html($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="occupation" class="form-label">Occupation</label>
                                <input type="text" class="form-control" id="occupation" name="occupation"
                                       value="<?php echo safe_html($_POST['occupation'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-control" id="rating" name="rating">
                                    <option value="5" <?php echo (($_POST['rating'] ?? 5) == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 Stars)</option>
                                    <option value="4" <?php echo (($_POST['rating'] ?? 5) == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 Stars)</option>
                                    <option value="3" <?php echo (($_POST['rating'] ?? 5) == 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3 Stars)</option>
                                    <option value="2" <?php echo (($_POST['rating'] ?? 5) == 2) ? 'selected' : ''; ?>>⭐⭐ (2 Stars)</option>
                                    <option value="1" <?php echo (($_POST['rating'] ?? 5) == 1) ? 'selected' : ''; ?>>⭐ (1 Star)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="testimonial" class="form-label">Your Testimony *</label>
                                <textarea class="form-control" id="testimonial" name="testimonial" rows="5" required
                                          placeholder="Share how God has worked in your life..."><?php echo safe_html($_POST['testimonial'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn-testimonial w-100">
                                <i class="fas fa-paper-plane"></i> Submit Testimony
                            </button>
                        </form>
                    </div>
                </div>

                <!-- All Testimonials List -->
                <div class="col-lg-8">
                    <h3 class="section-title" style="font-size: 1.8rem; margin-bottom: 2rem;" data-aos="fade-up">All Testimonies</h3>
                    <?php if (!empty($all_testimonials)): ?>
                        <div class="row g-4">
                            <?php foreach ($all_testimonials as $t): ?>
                                <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?php echo (100 + (array_search($t, $all_testimonials) * 50)); ?>">
                                    <div class="testimonial-card">
                                        <div class="testimonial-content">
                                            <div class="testimonial-header">
                                                <?php if ($t['photo_url']): ?>
                                                    <div class="testimonial-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                                        <img src="<?php echo htmlspecialchars($t['photo_url']); ?>" alt="<?php echo htmlspecialchars($t['name']); ?>">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="testimonial-avatar" style="width: 40px; height: 40px; font-size: 1rem;">
                                                        <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="testimonial-info">
                                                    <div class="testimonial-name" style="font-size: 0.9rem;"><?php echo htmlspecialchars($t['name']); ?></div>
                                                    <div class="testimonial-rating" style="font-size: 0.7rem; margin-bottom: 0.5rem;">
                                                        <div class="rating-stars">
                                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                                <i class="fas fa-star<?php echo $i < $t['rating'] ? '' : '-o'; ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="testimonial-quote" style="font-size: 0.9rem;">
                                                <?php echo nl2br(htmlspecialchars(substr($t['testimonial'], 0, 200))); ?><?php echo strlen($t['testimonial']) > 200 ? '...' : ''; ?>
                                            </div>
                                            <div class="testimonial-date">
                                                <?php echo date('M j, Y', strtotime($t['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5" data-aos="fade-up">
                            <div class="testimonial-avatar mx-auto mb-3">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <h4 class="text-muted">No testimonies available yet</h4>
                            <p class="text-muted">Be the first to share your testimony!</p>
                        </div>
                    <?php endif; ?>
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
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script nonce="<?php echo CSP_NONCE; ?>">
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

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>