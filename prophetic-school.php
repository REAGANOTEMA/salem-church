<?php
require_once 'config.php';
require_once 'db_connection.php';

$conn = getConnection();

// Initialize variables
$errors = [];
$success = '';
$application_id = '';

// Create uploads directory if it doesn't exist
$uploads_dir = 'uploads/prophetic_school/';
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $ministry_background = trim($_POST['ministry_background'] ?? '');
    $prophetic_experience = trim($_POST['prophetic_experience'] ?? '');
    $calling = trim($_POST['calling'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $payment_amount = trim($_POST['payment_amount'] ?? '');
    
    // File upload variables
    $passport_photo = '';
    $national_id = '';
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Upload passport photo
    if (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] == 0) {
        $file_info = $_FILES['passport_photo'];
        if (in_array($file_info['type'], $allowed_types) && $file_info['size'] <= $max_size) {
            $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
            $filename = 'passport_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($file_info['tmp_name'], $uploads_dir . $filename);
            $passport_photo = $filename;
        } else {
            $errors[] = 'Passport photo must be JPG/PNG and less than 5MB.';
        }
    }

    // Upload national ID
    if (isset($_FILES['national_id']) && $_FILES['national_id']['error'] == 0) {
        $file_info = $_FILES['national_id'];
        if (in_array($file_info['type'], $allowed_types) && $file_info['size'] <= $max_size) {
            $ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
            $filename = 'national_id_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($file_info['tmp_name'], $uploads_dir . $filename);
            $national_id = $filename;
        } else {
            $errors[] = 'National ID must be JPG/PNG/PDF and less than 5MB.';
        }
    }

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($age) || empty($gender) || empty($nationality) || empty($address)) {
        $errors[] = 'Please fill in all required fields.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!is_numeric($age) || $age < 18 || $age > 100) {
        $errors[] = 'Please enter a valid age between 18 and 100.';
    }

    if (!in_array($gender, ['male', 'female'])) {
        $errors[] = 'Please select a valid gender.';
    }

    if (strlen($phone) > 20) {
        $errors[] = 'Phone number is too long. Maximum 20 characters allowed.';
    }

    if (empty($payment_method) || empty($transaction_id) || empty($payment_amount)) {
        $errors[] = 'Please complete all payment details.';
    }

    if (!is_numeric($payment_amount) || $payment_amount < 100) {
        $errors[] = 'Payment amount must be at least $100 USD.';
    }

    if (empty($errors)) {
        try {
            $conn = getConnection();
            if (!$conn) {
                $errors[] = 'Database connection failed. Please try again later.';
            } else {
                // Check if table exists, create if not
                $table_check = $conn->query("SHOW TABLES LIKE 'prophetic_school_applications'");
                if ($table_check->num_rows == 0) {
                    $create_table = "CREATE TABLE prophetic_school_applications (
                        id int NOT NULL AUTO_INCREMENT,
                        first_name varchar(100) NOT NULL,
                        last_name varchar(100) NOT NULL,
                        email varchar(255) NOT NULL,
                        phone varchar(20) DEFAULT NULL,
                        age int NOT NULL,
                        gender enum('male','female') NOT NULL,
                        nationality varchar(100) NOT NULL,
                        address text NOT NULL,
                        ministry_background text,
                        prophetic_experience text,
                        calling text,
                        reason text,
                        passport_photo varchar(255) DEFAULT NULL,
                        national_id varchar(255) DEFAULT NULL,
                        payment_method varchar(50) NOT NULL,
                        transaction_id varchar(100) NOT NULL,
                        payment_amount decimal(10,2) NOT NULL,
                        payment_status enum('pending','verified','completed') DEFAULT 'pending',
                        status enum('pending','reviewing','accepted','rejected') DEFAULT 'pending',
                        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                        updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
                    
                    if (!$conn->query($create_table)) {
                        $errors[] = 'Database setup failed. Please contact administrator.';
                    }
                }

                $stmt = $conn->prepare("INSERT INTO prophetic_school_applications (first_name, last_name, email, phone, age, gender, nationality, address, ministry_background, prophetic_experience, calling, reason, passport_photo, national_id, payment_method, transaction_id, payment_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssissssssssssdss", $first_name, $last_name, $email, $phone, $age, $gender, $nationality, $address, $ministry_background, $prophetic_experience, $calling, $reason, $passport_photo, $national_id, $payment_method, $transaction_id, $payment_amount);
                
                if ($stmt->execute()) {
                    $success = 'Application submitted successfully! We will contact you within 3-5 business days.';
                    $application_id = $stmt->insert_id;
                    $stmt->close();
                } else {
                    $errors[] = 'Failed to submit application. Please try again.';
                }
                $conn->close();
            }
        } catch (Exception $e) {
            error_log("Prophetic school application error: " . $e->getMessage());
            $errors[] = 'An error occurred. Please try again later.';
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
    <title>Prophetic School | Salem Dominion Ministries</title>
    <meta name="description" content="Join our Prophetic School of Ministry and develop your prophetic gifts">
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
            background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.8)), url('assets/prophetic-school-hero.jpg');
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

        /* Application Section */
        .application-section {
            padding: 80px 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(14, 165, 233, 0.1));
        }

        .form-container {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .form-section {
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-label {
            color: var(--snow-white);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        /* File Upload Area */
        .file-upload-area {
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .file-upload-area:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--heavenly-gold);
            transform: translateY(-2px);
        }

        .file-upload-area i {
            font-size: 3rem;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .file-upload-text {
            color: var(--snow-white);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .file-upload-hint {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Payment Section */
        .payment-section {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(251, 191, 36, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 2.5rem;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .payment-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .payment-amount {
            font-size: 3rem;
            font-weight: 700;
            color: var(--snow-white);
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.5);
        }

        .currency {
            font-size: 1.5rem;
            color: var(--heavenly-gold);
        }

        /* Buttons */
        .btn-prophetic {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-prophetic::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-prophetic:hover::before {
            left: 100%;
        }

        .btn-prophetic:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.5);
            color: var(--snow-white);
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
            
            .form-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .navbar-brand img {
                height: 35px;
            }
            
            .btn-prophetic {
                padding: 12px 25px;
                font-size: 1rem;
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
                            <li><a class="dropdown-item" href="children_ministry.php">Children Ministry</a></li>
                            <li><a class="dropdown-item active" href="prophetic-school.php">Prophetic School</a></li>
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
            <h1 class="hero-title" data-aos="zoom-in">Prophetic School of Ministry</h1>
            <p class="hero-subtitle" data-aos="fade-up">Step Into Your Prophetic Destiny</p>
        </div>
    </section>

    <!-- Application Section -->
    <section class="application-section" id="application">
        <div class="container">
            <div class="form-container" data-aos="fade-up">
                <h2 class="text-center mb-4" style="color: var(--heavenly-gold); font-family: 'Playfair Display', serif; font-size: 2.5rem;">
                    <i class="fas fa-graduation-cap me-3"></i>Application Form
                </h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-warning" role="alert">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please correct the following errors:</strong>
                        </div>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo safe_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong><?php echo safe_html($success); ?></strong>
                        </div>
                        <div class="mt-3">
                            <p class="mb-2"><i class="fab fa-whatsapp text-success me-2"></i>Application has been sent to WhatsApp: +256753244480</p>
                            <small class="text-muted">Application ID: #<?php echo safe_html($application_id ?? ''); ?></small>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                       value="<?php echo safe_html($_POST['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="<?php echo safe_html($_POST['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo safe_html($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo safe_html($_POST['phone'] ?? ''); ?>"
                                       maxlength="20">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="age" class="form-label">Age *</label>
                                <input type="number" class="form-control" id="age" name="age" required
                                       value="<?php echo safe_html($_POST['age'] ?? ''); ?>"
                                       min="18" max="100">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="gender" class="form-label">Gender *</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php echo (($_POST['gender'] ?? '') === 'male' ? 'selected' : ''); ?>>Male</option>
                                    <option value="female" <?php echo (($_POST['gender'] ?? '') === 'female' ? 'selected' : ''); ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nationality" class="form-label">Nationality *</label>
                                <input type="text" class="form-control" id="nationality" name="nationality" required
                                       value="<?php echo safe_html($_POST['nationality'] ?? ''); ?>"
                                       placeholder="e.g., Ugandan, Kenyan, Tanzanian">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Full Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required
                                      placeholder="Enter your complete residential address"><?php echo safe_html($_POST['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Ministry Information -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-church"></i> Ministry Background</h3>
                        <div class="mb-3">
                            <label for="ministry_background" class="form-label">Ministry Experience</label>
                            <textarea class="form-control" id="ministry_background" name="ministry_background" rows="3"
                                      placeholder="Tell us about your current or past ministry experience..."><?php echo safe_html($_POST['ministry_background'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prophetic_experience" class="form-label">Prophetic Experience</label>
                            <textarea class="form-control" id="prophetic_experience" name="prophetic_experience" rows="3"
                                      placeholder="Describe any prophetic experiences, dreams, or revelations..."><?php echo safe_html($_POST['prophetic_experience'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="calling" class="form-label">Your Prophetic Calling</label>
                            <textarea class="form-control" id="calling" name="calling" rows="3"
                                      placeholder="What do you believe God has called you to do?"><?php echo safe_html($_POST['calling'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Why Join Our School?</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"
                                      placeholder="Share your reasons for wanting to join prophetic school..."><?php echo safe_html($_POST['reason'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Document Uploads -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-file-upload"></i> Required Documents</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Passport Photo *</label>
                                <div class="file-upload-area">
                                    <i class="fas fa-camera"></i>
                                    <div class="file-upload-text">Click to upload passport photo</div>
                                    <div class="file-upload-hint">JPG/PNG format, Max 5MB</div>
                                    <input type="file" class="form-control d-none" id="passport_photo" name="passport_photo" 
                                           accept="image/jpeg,image/jpg,image/png" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">National ID *</label>
                                <div class="file-upload-area">
                                    <i class="fas fa-id-card"></i>
                                    <div class="file-upload-text">Click to upload national ID</div>
                                    <div class="file-upload-hint">JPG/PNG/PDF format, Max 5MB</div>
                                    <input type="file" class="form-control d-none" id="national_id" name="national_id" 
                                           accept="image/jpeg,image/jpg,image/png,application/pdf" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="payment-section">
                        <div class="payment-header">
                            <h3 class="payment-title"><i class="fas fa-credit-card"></i> Application Fee Payment</h3>
                            <div class="payment-amount">
                                <span class="currency">$</span>100<span class="currency"> USD</span>
                            </div>
                            <p class="text-white-50">Secure your place in the Prophetic School of Ministry</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="mobile_money" <?php echo (($_POST['payment_method'] ?? '') === 'mobile_money' ? 'selected' : ''); ?>>Mobile Money</option>
                                    <option value="bank_transfer" <?php echo (($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : ''); ?>>Bank Transfer</option>
                                    <option value="cash" <?php echo (($_POST['payment_method'] ?? '') === 'cash' ? 'selected' : ''); ?>>Cash Deposit</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="transaction_id" class="form-label">Transaction ID *</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" required
                                       value="<?php echo safe_html($_POST['transaction_id'] ?? ''); ?>"
                                       placeholder="Enter transaction reference number">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="payment_amount" class="form-label">Amount Paid (USD) *</label>
                                <input type="number" class="form-control" id="payment_amount" name="payment_amount" required
                                       value="<?php echo safe_html($_POST['payment_amount'] ?? '100'); ?>"
                                       min="100" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn-prophetic">
                            <i class="fas fa-paper-plane"></i> Submit Complete Application
                        </button>
                    </div>
                </form>
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

        // File upload handling
        document.querySelectorAll('.file-upload-area').forEach(area => {
            area.addEventListener('click', function() {
                const fileInput = this.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.click();
                }
            });
        });

        // Handle file selection
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0]?.name || '';
                const uploadArea = this.closest('.file-upload-area');
                if (uploadArea && fileName) {
                    const textElement = uploadArea.querySelector('.file-upload-text');
                    if (textElement) {
                        textElement.textContent = fileName;
                        textElement.style.color = 'var(--heavenly-gold)';
                    }
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

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
    </script>
</body>
</html>
