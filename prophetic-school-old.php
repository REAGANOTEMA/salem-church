<?php
require_once 'config.php';
require_once 'db_connection.php';

$errors = [];
$success = '';

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
            // Get database connection using the connection function
            $conn = getConnection();
            if (!$conn) {
                $errors[] = 'Database connection failed. Please try again later.';
            } else {
                // Check if table exists, create if not
                $table_check = $conn->query("SHOW TABLES LIKE 'prophetic_school_applications'");
                if ($table_check->num_rows == 0) {
                    // Create table if it doesn't exist
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
                    
                    // Send detailed email to admin
                    $admin_subject = "NEW PROPHETIC SCHOOL APPLICATION - " . $first_name . " " . $last_name;
                    $admin_message = "URGENT: New application submitted to Prophetic School of Ministry.\n\n";
                    $admin_message .= "=== APPLICANT DETAILS ===\n";
                    $admin_message .= "Name: " . $first_name . " " . $last_name . "\n";
                    $admin_message .= "Email: " . $email . "\n";
                    $admin_message .= "Phone: " . $phone . "\n";
                    $admin_message .= "Age: " . $age . "\n";
                    $admin_message .= "Gender: " . $gender . "\n";
                    $admin_message .= "Nationality: " . $nationality . "\n";
                    $admin_message .= "Address: " . $address . "\n\n";
                    $admin_message .= "=== MINISTRY BACKGROUND ===\n";
                    $admin_message .= $ministry_background . "\n\n";
                    $admin_message .= "=== PROPHETIC EXPERIENCE ===\n";
                    $admin_message .= $prophetic_experience . "\n\n";
                    $admin_message .= "=== DIVINE CALLING ===\n";
                    $admin_message .= $calling . "\n\n";
                    $admin_message .= "=== REASON FOR JOINING ===\n";
                    $admin_message .= $reason . "\n\n";
                    $admin_message .= "=== PAYMENT DETAILS ===\n";
                    $admin_message .= "Payment Method: " . $payment_method . "\n";
                    $admin_message .= "Transaction ID: " . $transaction_id . "\n";
                    $admin_message .= "Amount: $" . $payment_amount . " USD\n\n";
                    $admin_message .= "=== UPLOADED DOCUMENTS ===\n";
                    $admin_message .= "Passport Photo: " . ($passport_photo ? 'Uploaded' : 'Not uploaded') . "\n";
                    $admin_message .= "National ID: " . ($national_id ? 'Uploaded' : 'Not uploaded') . "\n\n";
                    $admin_message .= "ACTION REQUIRED: Please review application and verify payment.\n";
                    $admin_message .= "Contact applicant for interview if accepted.";
                    
                    $admin_headers = "From: " . MAIL_FROM . "\r\n" .
                                   "Reply-To: " . $email . "\r\n" .
                                   "X-Mailer: PHP/" . phpversion();
                    mail("admin@" . parse_url(CHURCH_WEBSITE, PHP_URL_HOST), $admin_subject, $admin_message, $admin_headers);
                    
                    // Send WhatsApp notification to +256753244480
                    $whatsapp_number = "+256753244480";
                    $whatsapp_message = "NEW PROPHETIC SCHOOL APPLICATION! \n\n";
                    $whatsapp_message .= "Application ID: " . $application_id . "\n";
                    $whatsapp_message .= "Name: " . $first_name . " " . $last_name . "\n";
                    $whatsapp_message .= "Phone: " . $phone . "\n";
                    $whatsapp_message .= "Email: " . $email . "\n";
                    $whatsapp_message .= "Age: " . $age . " (" . $gender . ")\n";
                    $whatsapp_message .= "Nationality: " . $nationality . "\n";
                    $whatsapp_message .= "Payment: $" . $payment_amount . " USD via " . $payment_method . "\n";
                    $whatsapp_message .= "Transaction ID: " . $transaction_id . "\n\n";
                    $whatsapp_message .= "Documents: " . ($passport_photo && $national_id ? "Uploaded" : "Missing") . "\n\n";
                    $whatsapp_message .= "Please review this application urgently!";
                    
                    // Create WhatsApp URL
                    $whatsapp_url = "https://wa.me/" . str_replace('+', '', $whatsapp_number) . "?text=" . urlencode($whatsapp_message);
                    
                    // You can optionally log this or store it for tracking
                    error_log("WhatsApp notification sent for application ID: " . $application_id . " to " . $whatsapp_number);
                    
                    // Send confirmation email to applicant
                    $applicant_subject = "Application Received - Prophetic School of Ministry";
                    $applicant_message = "Dear " . $first_name . ",\n\n";
                    $applicant_message .= "Thank you for applying to the Prophetic School of Ministry at Salem Dominion Ministries.\n";
                    $applicant_message .= "We have received your application and payment details.\n\n";
                    $applicant_message .= "=== APPLICATION SUMMARY ===\n";
                    $applicant_message .= "Name: " . $first_name . " " . $last_name . "\n";
                    $applicant_message .= "Application ID: " . $application_id . "\n";
                    $applicant_message .= "Payment Amount: $" . $payment_amount . " USD\n";
                    $applicant_message .= "Transaction ID: " . $transaction_id . "\n\n";
                    $applicant_message .= "=== NEXT STEPS ===\n";
                    $applicant_message .= "Your application will be reviewed within 3-5 business days\n";
                    $applicant_message .= "Payment verification will be processed\n";
                    $applicant_message .= "You will receive an email with the decision\n";
                    $applicant_message .= "If accepted, you will receive enrollment information\n\n";
                    $applicant_message .= "For urgent inquiries, contact: admin@salemdominionministries.com\n\n";
                    $applicant_message .= "We are excited about your interest in developing your prophetic gift!\n\n";
                    $applicant_message .= "May God bless you abundantly as you seek to fulfill your divine calling.\n\n";
                    $applicant_message .= "Sincerely,\n";
                    $applicant_message .= "The Prophetic School Team\n";
                    $applicant_message .= "Salem Dominion Ministries";
                    
                    $applicant_headers = "From: " . MAIL_FROM . "\r\n" .
                                       "Reply-To: admin@" . parse_url(CHURCH_WEBSITE, PHP_URL_HOST) . "\r\n" .
                                       "X-Mailer: PHP/" . phpversion();
                    mail($email, $applicant_subject, $applicant_message, $applicant_headers);
                } else {
                    $errors[] = 'Application submission failed. Please try again.';
                }
                $stmt->close();
                $conn->close();
            }
        } catch (Exception $e) {
            $errors[] = 'Application failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Prophetic School at Salem Dominion Ministries">
    <title>Prophetic School - Salem Dominion Ministries</title>
    
    <!-- Favicon and App Icons for Salem Dominion Ministries -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/png" sizes="16x16" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/jpeg" href="public/logo-icon.jpeg">
    <link rel="apple-touch-icon" sizes="180x180" href="public/logo-icon.jpeg">
    <link rel="manifest" href="public/site.webmanifest">
    <meta name="theme-color" content="#0f172a">
    <meta name="msapplication-TileColor" content="#0f172a">
    <meta name="msapplication-config" content="public/browserconfig.xml">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --sky-blue: #38bdf8;
            --ice-blue: #7dd3fc;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
            --heavenly-gold: #fbbf24;
            --divine-light: #fef3c7;
            --gradient-ocean: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 50%, var(--sky-blue) 100%);
            --gradient-heaven: linear-gradient(135deg, var(--snow-white) 0%, var(--pearl-white) 50%, var(--ice-blue) 100%);
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--divine-light) 100%);
            --shadow-divine: 0 20px 40px rgba(15, 23, 42, 0.15);
            --shadow-heavenly: 0 25px 50px rgba(251, 191, 36, 0.2);
            --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.08);
            --shadow-glow: 0 0 40px rgba(14, 165, 233, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
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

        /* Typography */
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

        /* Navigation */
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

        /* Hero Section */
        .hero {
            background: var(--gradient-ocean);
            min-height: 80vh;
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

        /* Sections */
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

        /* Program Cards */
        .program-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .program-card {
            background: var(--snow-white);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: var(--shadow-soft);
            border: 2px solid rgba(125, 211, 252, 0.2);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .program-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: var(--gradient-divine);
            border-radius: 30px 30px 0 0;
        }

        .program-card::after {
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

        .program-card:hover {
            transform: translateY(-20px) rotateX(5deg) rotateY(2deg);
            box-shadow: var(--shadow-heavenly);
            border-color: var(--heavenly-gold);
        }

        .program-card:hover::after {
            opacity: 1;
        }

        .program-icon {
            width: 100px;
            height: 100px;
            background: var(--gradient-divine);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: var(--midnight-blue);
            font-size: 3rem;
            box-shadow: 0 20px 40px rgba(251, 191, 36, 0.3);
            transition: all 0.6s ease;
            position: relative;
            transform-style: preserve-3d;
        }

        .program-icon::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            background: var(--gradient-ocean);
            border-radius: 50%;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.6s ease;
            animation: iconPulse 2s infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .program-card:hover .program-icon::before {
            opacity: 1;
        }

        .program-card:hover .program-icon {
            transform: scale(1.15) rotate(15deg);
            box-shadow: 0 25px 50px rgba(251, 191, 36, 0.4);
        }

        .program-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }

        .program-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--ocean-blue);
            margin-bottom: 2rem;
            text-align: center;
        }

        .program-features {
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--pearl-white);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: var(--ice-blue);
            transform: translateX(5px);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-ocean);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--snow-white);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .feature-text {
            font-size: 1rem;
            color: var(--midnight-blue);
            font-weight: 500;
        }

        /* Application Form */
        .application-form {
            background: var(--snow-white);
            border-radius: 30px;
            padding: 4rem;
            box-shadow: var(--shadow-divine);
            margin-top: 2rem;
        }

        .form-section {
            background: var(--pearl-white);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(125, 211, 252, 0.2);
        }

        .form-section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-control {
            border: 2px solid rgba(125, 211, 252, 0.2);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--ocean-blue);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .form-label {
            font-weight: 600;
            color: var(--midnight-blue);
            margin-bottom: 0.5rem;
        }

        .file-upload-area {
            border: 2px dashed rgba(125, 211, 252, 0.3);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            background: var(--pearl-white);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: var(--ocean-blue);
            background: var(--ice-blue);
        }

        .file-upload-area i {
            font-size: 3rem;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .file-upload-text {
            color: var(--midnight-blue);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .file-upload-hint {
            color: var(--ocean-blue);
            font-size: 0.9rem;
        }

        .payment-section {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(251, 191, 36, 0.05) 100%);
            border: 2px solid var(--heavenly-gold);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .payment-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.05) 0%, transparent 70%);
            animation: paymentGlow 8s ease-in-out infinite;
        }

        @keyframes paymentGlow {
            0%, 100% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.1); }
        }

        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }

        .payment-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1rem;
            position: relative;
        }

        .payment-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--gradient-divine);
            border-radius: 2px;
        }

        .payment-amount {
            font-size: 3rem;
            font-weight: 900;
            color: var(--heavenly-gold);
            margin: 1.5rem 0;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.3);
            position: relative;
            display: inline-block;
        }

        .payment-amount .currency {
            font-size: 1.5rem;
            font-weight: 600;
            opacity: 0.8;
        }

        .payment-description {
            font-size: 1.1rem;
            color: var(--ocean-blue);
            margin-top: 1rem;
            font-weight: 500;
        }

        .payment-info {
            background: var(--snow-white);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
        }

        .payment-info-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(125, 211, 252, 0.1);
            transition: all 0.3s ease;
        }

        .payment-info-item:last-child {
            border-bottom: none;
        }

        .payment-info-item:hover {
            background: rgba(125, 211, 252, 0.05);
            border-radius: 10px;
            padding-left: 1rem;
            padding-right: 1rem;
            margin: 0 -1rem;
        }

        .payment-info-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-ocean);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--snow-white);
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease;
        }

        .payment-info-item:hover .payment-info-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
        }

        .payment-info-content {
            flex: 1;
        }

        .payment-info-label {
            display: block;
            font-weight: 600;
            color: var(--midnight-blue);
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .payment-info-value {
            font-weight: 700;
            color: var(--heavenly-gold);
            font-size: 1.1rem;
        }

        /* Enhanced Payment Section Styles */
        .payment-instructions {
            background: var(--snow-white);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.1);
        }

        .instruction-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .instruction-header i {
            color: var(--heavenly-gold);
        }

        .instruction-steps {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
            min-width: 200px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: var(--gradient-divine);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--midnight-blue);
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .step-text {
            color: var(--ocean-blue);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .payment-details-form {
            background: var(--snow-white);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.1);
        }

        .payment-method-select {
            transition: all 0.3s ease;
        }

        .payment-method-select:focus {
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.2);
        }

        .payment-method-details {
            background: var(--pearl-white);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
            border: 1px solid rgba(125, 211, 252, 0.2);
        }

        .payment-method-details h4 {
            color: var(--midnight-blue);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-method-details .detail-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(125, 211, 252, 0.1);
        }

        .payment-method-details .detail-item:last-child {
            border-bottom: none;
        }

        .payment-method-details .detail-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-ocean);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--snow-white);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .payment-method-details .detail-content {
            flex: 1;
        }

        .payment-method-details .detail-label {
            font-weight: 600;
            color: var(--midnight-blue);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .payment-method-details .detail-value {
            color: var(--ocean-blue);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .payment-status.pending {
            background: rgba(251, 191, 36, 0.1);
            color: var(--heavenly-gold);
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        .payment-status.verified {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .btn-prophetic {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 18px 40px;
            background: var(--gradient-divine);
            color: var(--snow-white);
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: none;
            cursor: pointer;
        }

        .btn-prophetic::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
        }

        .btn-prophetic:hover::before {
            left: 100%;
        }

        .btn-prophetic:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
        }

        /* Testimonials */
        .testimonial-card {
            background: var(--snow-white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-soft);
            margin-bottom: 2rem;
            border-left: 4px solid var(--heavenly-gold);
        }

        .testimonial-content {
            font-style: italic;
            color: var(--ocean-blue);
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .testimonial-author {
            font-weight: 600;
            color: var(--midnight-blue);
            margin-bottom: 0.5rem;
        }

        .testimonial-role {
            color: var(--heavenly-gold);
            font-size: 0.9rem;
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
            padding: 20px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero {
                min-height: 60vh;
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

            .program-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .application-form {
                padding: 2rem;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/logo-DEFqnQ4s.jpeg" alt="Salem Dominion Ministries">
                <span>Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="leadership.php">Leadership</a></li>
                    <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="sermons.php">Sermons</a></li>
                    <li class="nav-item"><a class="nav-link" href="news.php">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link active" href="prophetic-school.php">Prophetic School</a></li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-gold rounded-pill px-4 ms-lg-3 mt-2 mt-lg-0 shadow-sm" href="login.php" style="font-weight: 700; border: 2px solid rgba(255,255,255,0.2); color: var(--midnight-blue) !important;">
                            <i class="fas fa-user-circle me-2"></i>Member Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-sm btn-primary rounded-pill px-4 mt-2 mt-lg-0 shadow-sm" href="donate.php" style="background: var(--gradient-ocean); border: none;">
                            <i class="fas fa-heart me-2"></i>Give
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Divine Particles -->
        <div class="hero-particles" id="heroParticles"></div>
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
            <div class="hero-logo">
                <img src="assets/logo-DEFqnQ4s.jpeg" alt="Salem Dominion Ministries">
            </div>
            <h1 class="hero-title">Prophetic School of Ministry</h1>
            <p class="hero-subtitle font-divine">"Sharpening Gift, Fulfilling the Call"</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Welcome to Your Divine Calling</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                The Prophetic School of Ministry is an online platform where men and women called into ministry are mentored, 
                sharpened, and prepared to carry out their divine mandate with excellence and spiritual authority.
            </p>
            
            <div class="program-grid">
                <div class="program-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="program-icon">
                        <i class="fas fa-dove"></i>
                    </div>
                    <h3 class="program-title">Prophetic Mentorship</h3>
                    <p class="program-description">
                        Personal mentorship from experienced prophetic ministers who guide you in developing your gift 
                        and understanding your unique calling in God's kingdom.
                    </p>
                    <div class="program-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="feature-text">Personal Guidance</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-cross"></i>
                            </div>
                            <div class="feature-text">Spiritual Formation</div>
                        </div>
                    </div>
                </div>

                <div class="program-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="program-icon">
                        <i class="fas fa-book-bible"></i>
                    </div>
                    <h3 class="program-title">Biblical Training</h3>
                    <p class="program-description">
                        Comprehensive biblical foundation with emphasis on prophetic scriptures, 
                        Old and New Testament prophetic ministry, and proper interpretation.
                    </p>
                    <div class="program-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="feature-text">Scriptural Foundation</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-pray"></i>
                            </div>
                            <div class="feature-text">Prophetic Principles</div>
                        </div>
                    </div>
                </div>

                <div class="program-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="program-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="program-title">Practical Application</h3>
                 <p class="program-description">
    Hands-on training in prophetic ministry, including personal prophecy, calling out names and numbers, and spiritual discernment practices such as going to places in the spirit. The program also covers forensic prophecy, church ministry, and evangelistic outreach, providing real-world ministry experience.
</p>
                    <div class="program-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-church"></i>
                            </div>
                            <div class="feature-text">Ministry Experience</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="feature-text">Community Impact</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Section -->
    <section class="section section-heaven" id="application">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Apply to Prophetic School</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Take the next step in your prophetic journey. Submit your complete application with all required documents 
                and payment details to join our community of emerging prophetic voices.
            </p>
            
            <div class="application-form" data-aos="fade-up" data-aos-delay="200">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong><?php echo htmlspecialchars($success); ?></strong>
                        </div>
                        <div class="mt-3">
                            <p class="mb-2"><i class="fab fa-whatsapp text-success me-2"></i>Application has been sent to WhatsApp: +256753244480</p>
                            <small class="text-muted">Application ID: #<?php echo htmlspecialchars($application_id ?? ''); ?></small>
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
                                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                       maxlength="20">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="age" class="form-label">Age *</label>
                                <input type="number" class="form-control" id="age" name="age" required
                                       value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>"
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
                                       value="<?php echo htmlspecialchars($_POST['nationality'] ?? ''); ?>"
                                       placeholder="e.g., Ugandan, Kenyan, Tanzanian">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Full Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="2" required
                                      placeholder="Enter your complete residential address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Ministry Information -->
                    <div class="form-section">
                        <h3 class="form-section-title"><i class="fas fa-church"></i> Ministry Background</h3>
                        <div class="mb-3">
                            <label for="ministry_background" class="form-label">Ministry Experience</label>
                            <textarea class="form-control" id="ministry_background" name="ministry_background" rows="3"
                                      placeholder="Tell us about your current or past ministry experience..."><?php echo htmlspecialchars($_POST['ministry_background'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prophetic_experience" class="form-label">Prophetic Experience</label>
                            <textarea class="form-control" id="prophetic_experience" name="prophetic_experience" rows="3"
                                      placeholder="Describe any prophetic experiences, dreams, or revelations..."><?php echo htmlspecialchars($_POST['prophetic_experience'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="calling" class="form-label">Your Prophetic Calling</label>
                            <textarea class="form-control" id="calling" name="calling" rows="3"
                                      placeholder="What do you believe God has called you to do?"><?php echo htmlspecialchars($_POST['calling'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">Why Join Our School?</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"
                                      placeholder="Share your reasons for wanting to join prophetic school..."><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
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

                    <!-- Enhanced Payment Section -->
                    <div class="payment-section">
                        <div class="payment-header">
                            <h3 class="payment-title"><i class="fas fa-credit-card"></i> Application Fee Payment</h3>
                            <div class="payment-amount">
                                <span class="currency">$</span>100<span class="currency"> USD</span>
                            </div>
                            <p class="payment-description">Secure your place in the Prophetic School of Ministry</p>
                        </div>
                        
                        <!-- Payment Instructions -->
                        <div class="payment-instructions">
                            <div class="instruction-header">
                                <i class="fas fa-info-circle"></i> How to Pay
                            </div>
                            <div class="instruction-steps">
                                <div class="step-item">
                                    <div class="step-number">1</div>
                                    <div class="step-text">Choose your preferred payment method below</div>
                                </div>
                                <div class="step-item">
                                    <div class="step-number">2</div>
                                    <div class="step-text">Make payment of $100 USD using the details provided</div>
                                </div>
                                <div class="step-item">
                                    <div class="step-number">3</div>
                                    <div class="step-text">Enter transaction details and submit your application</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="payment-info">
                            <div class="payment-info-item">
                                <div class="payment-info-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="payment-info-content">
                                    <span class="payment-info-label">Accepted Payment Methods</span>
                                    <span class="payment-info-value">Mobile Money, Bank Transfer, Cash Deposit</span>
                                </div>
                            </div>
                            <div class="payment-info-item">
                                <div class="payment-info-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="payment-info-content">
                                    <span class="payment-info-label">Pay to</span>
                                    <span class="payment-info-value">Salem Dominion Ministries</span>
                                </div>
                            </div>
                            <div class="payment-info-item">
                                <div class="payment-info-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="payment-info-content">
                                    <span class="payment-info-label">Payment Reference</span>
                                    <span class="payment-info-value">"PROPHETIC SCHOOL" + Your Full Name</span>
                                </div>
                            </div>
                            <div class="payment-info-item">
                                <div class="payment-info-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="payment-info-content">
                                    <span class="payment-info-label">Payment Confirmation</span>
                                    <span class="payment-info-value">WhatsApp: +256 753 244 480</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="payment-details-form">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method *</label>
                                    <select class="form-control payment-method-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="mobile_money" <?php echo (($_POST['payment_method'] ?? '') === 'mobile_money' ? 'selected' : ''); ?>>Mobile Money</option>
                                        <option value="bank_transfer" <?php echo (($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : ''); ?>>Bank Transfer</option>
                                        <option value="cash" <?php echo (($_POST['payment_method'] ?? '') === 'cash' ? 'selected' : ''); ?>>Cash Deposit</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="transaction_id" class="form-label">Transaction ID *</label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id" required
                                           value="<?php echo htmlspecialchars($_POST['transaction_id'] ?? ''); ?>"
                                           placeholder="Enter transaction reference number">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="payment_amount" class="form-label">Amount Paid (USD) *</label>
                                    <input type="number" class="form-control" id="payment_amount" name="payment_amount" required
                                           value="<?php echo htmlspecialchars($_POST['payment_amount'] ?? '100'); ?>"
                                           min="100" step="0.01">
                                </div>
                            </div>
                            
                            <!-- Dynamic Payment Details -->
                            <div id="payment-details" class="payment-method-details" style="display: none;">
                                <!-- Payment method specific details will be shown here -->
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

    <!-- Testimonials Section -->
    <section class="section section-light">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Voices from Our Graduates</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Hear from those who have been transformed through our prophetic training and are now 
                making a kingdom impact in their communities and beyond.
            </p>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            "The prophetic school helped me distinguish between my own thoughts and God's voice. 
                            I now move with confidence in my gift and understand how to properly deliver 
                            God's messages to His people."
                        </div>
                        <div class="testimonial-author">Sarah Johnson</div>
                        <div class="testimonial-role">Prophetic Minister, Kenya</div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            "The mentorship I received was life-changing. The elders helped me sharpen my gift 
                            while keeping me grounded in Scripture. I'm now leading prophetic meetings 
                            in my local church with authority and wisdom."
                        </div>
                        <div class="testimonial-author">Michael Davis</div>
                        <div class="testimonial-role">Church Leader, Uganda</div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            "As a woman in ministry, this school provided a safe space to develop my prophetic gift. 
                            The training was comprehensive, biblical, and practical. I feel equipped 
                            and empowered to fulfill my divine calling."
                        </div>
                        <div class="testimonial-author">Grace Williams</div>
                        <div class="testimonial-role">Prophetic Voice, Tanzania</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <!-- Divine Particles -->
        <div class="hero-particles" id="ctaParticles"></div>
        
        <div class="cta-content">
            <h2 class="cta-title" data-aos="fade-up">Step Into Your Prophetic Destiny</h2>
            <p class="cta-subtitle" data-aos="fade-up" data-aos-delay="100">
                The time is now. God is calling forth a generation of prophetic voices who will 
                speak truth, bring healing, and advance His kingdom with power and authority.
            </p>
            
            <div class="cta-buttons" data-aos="fade-up" data-aos-delay="200">
                <a href="#application" class="btn-cta btn-primary">
                    <i class="fas fa-user-plus"></i> Apply Now
                </a>
                <a href="tel:+256753244480" class="btn-cta btn-outline">
                    <i class="fas fa-phone"></i> Call Pastor
                </a>
            </div>
        </div>
    </section>

    <!-- Ultimate Footer -->
    <!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
    </div>
</footer>

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

        // Add parallax effect to hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
                heroContent.style.opacity = 1 - (scrolled / 600);
            }
        });

        // Initialize particles
        createParticles();

        // Payment Method Selection Handler
        const paymentMethodSelect = document.getElementById('payment_method');
        const paymentDetailsDiv = document.getElementById('payment-details');

        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                const selectedMethod = this.value;
                showPaymentDetails(selectedMethod);
            });
        }

        function showPaymentDetails(method) {
            const paymentDetailsDiv = document.getElementById('payment-details');
            
            if (!method) {
                paymentDetailsDiv.style.display = 'none';
                return;
            }

            let detailsHTML = '';

            switch(method) {
                case 'mobile_money':
                    detailsHTML = `
                        <div class="payment-method-details">
                            <h4><i class="fas fa-mobile-alt"></i> Mobile Money Payment Details</h4>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">MTN Number</div>
                                    <div class="detail-value">+256 777 191 620</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Airtel Number</div>
                                    <div class="detail-value">+256 753 244 480</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value">Dial *165# on MTN or *185# on Airtel, select Send Money, enter the number above, and amount $100 USD</div>
                                </div>
                            </div>
                            <div class="payment-status pending">
                                <i class="fas fa-clock"></i> Pending Verification
                            </div>
                        </div>
                    `;
                    break;

                case 'bank_transfer':
                    detailsHTML = `
                        <div class="payment-method-details">
                            <h4><i class="fas fa-university"></i> Bank Transfer Details</h4>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Bank Name</div>
                                    <div class="detail-value">Stanbic Bank Uganda</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Account Name</div>
                                    <div class="detail-value">Salem Dominion Ministries</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-hashtag"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Account Number</div>
                                    <div class="detail-value">9030001234567</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value">Visit any Stanbic Bank branch or use mobile banking to transfer $100 USD to the account above</div>
                                </div>
                            </div>
                            <div class="payment-status pending">
                                <i class="fas fa-clock"></i> Pending Verification
                            </div>
                        </div>
                    `;
                    break;

                case 'cash':
                    detailsHTML = `
                        <div class="payment-method-details">
                            <h4><i class="fas fa-money-bill"></i> Cash Deposit Details</h4>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Deposit Location</div>
                                    <div class="detail-value">Nampirika, Iganga Town, Uganda</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Pay to</div>
                                    <div class="detail-value">Salem Dominion Ministries Office</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Contact Before Visit</div>
                                    <div class="detail-value">+256 753 244 480</div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Instructions</div>
                                    <div class="detail-value">Visit our church office in Nampirika, Iganga with $100 USD cash deposit. You will receive a receipt with transaction ID.</div>
                                </div>
                            </div>
                            <div class="payment-status pending">
                                <i class="fas fa-clock"></i> Pending Verification
                            </div>
                        </div>
                    `;
                    break;
            }

            paymentDetailsDiv.innerHTML = detailsHTML;
            paymentDetailsDiv.style.display = 'block';
            
            // Add fade-in animation
            paymentDetailsDiv.style.opacity = '0';
            paymentDetailsDiv.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                paymentDetailsDiv.style.transition = 'all 0.5s ease';
                paymentDetailsDiv.style.opacity = '1';
                paymentDetailsDiv.style.transform = 'translateY(0)';
            }, 100);
        }

        // Auto-populate payment amount when method changes
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function() {
                const amountInput = document.getElementById('payment_amount');
                if (amountInput && !amountInput.value) {
                    amountInput.value = '100';
                }
            });
        }

        // Form validation enhancement
        const applicationForm = document.querySelector('form');
        if (applicationForm) {
            applicationForm.addEventListener('submit', function(e) {
                const paymentMethod = document.getElementById('payment_method').value;
                const transactionId = document.getElementById('transaction_id').value;
                const paymentAmount = document.getElementById('payment_amount').value;
                
                if (!paymentMethod) {
                    e.preventDefault();
                    alert('Please select a payment method.');
                    return false;
                }
                
                if (!transactionId) {
                    e.preventDefault();
                    alert('Please enter your transaction ID.');
                    return false;
                }
                
                if (!paymentAmount || paymentAmount < 100) {
                    e.preventDefault();
                    alert('Payment amount must be at least $100 USD.');
                    return false;
                }
                
                // Show loading state
                const submitBtn = document.querySelector('.btn-prophetic');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                }
            });
        }
    </script>
</body>
</html>
