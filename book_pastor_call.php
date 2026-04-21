<?php
// Buffer output to catch any accidental output
ob_start();

// Include required files with error handling (removed login system)
try {
    require_once 'config.php';
    require_once 'db_connection.php';
} catch (Exception $e) {
    // Silent error handling
}

// Check if user is logged in (optional - allow both members and guests)
$is_logged_in = false;
$user_email = '';

// Initialize database connection
$conn = getConnection();

// Get pastor availability and existing bookings
try {
    $existing_bookings = [];
    $pastor = ['name' => 'Apostle Faty Musasizi', 'phone' => '+256753244480', 'email' => 'apostle@salemdominionministries.com'];
    $availability = [];
    
    if ($conn) {
        // Get existing bookings for the next 30 days from correct table
        $stmt = $conn->prepare("SELECT date, start_time, end_time FROM pastor_bookings WHERE status != 'cancelled' AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $existing_bookings = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // Get pastor info
        $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'pastor' AND is_active = 1 LIMIT 1");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $pastor_data = $result->fetch_assoc();
            if ($pastor_data) {
                $pastor = $pastor_data;
            }
            $stmt->close();
        }
        
        // Check if availability exists, if not, insert the schedule
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM pastor_booking_availability WHERE is_active = 1");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            $availability_count = $result->fetch_assoc()['count'];
            $stmt->close();
            
            if ($availability_count == 0) {
                // Clear existing availability and insert new schedule
                $stmt = $conn->prepare("DELETE FROM pastor_booking_availability WHERE is_active = 1");
                if ($stmt) {
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Insert pastor availability based on specified schedule
                $availability_schedule = [
                    // Monday - Full day (9 AM - 6 PM)
                    ['day' => 'monday', 'start' => '09:00:00', 'end' => '18:00:00'],
                    ['day' => 'monday', 'start' => '09:00:00', 'end' => '18:00:00'], // Second slot for flexibility
                    
                    // Tuesday - Full day (9 AM - 6 PM)  
                    ['day' => 'tuesday', 'start' => '09:00:00', 'end' => '18:00:00'],
                    ['day' => 'tuesday', 'start' => '09:00:00', 'end' => '18:00:00'],
                    
                    // Wednesday - 9 AM to 3 PM and 9 PM to midnight
                    ['day' => 'wednesday', 'start' => '09:00:00', 'end' => '15:00:00'],
                    ['day' => 'wednesday', 'start' => '21:00:00', 'end' => '23:59:59'],
                    
                    // Thursday - Full day (9 AM - 6 PM)
                    ['day' => 'thursday', 'start' => '09:00:00', 'end' => '18:00:00'],
                    ['day' => 'thursday', 'start' => '09:00:00', 'end' => '18:00:00'],
                    
                    // Friday - 9 AM to 3 PM and 9 PM to midnight
                    ['day' => 'friday', 'start' => '09:00:00', 'end' => '15:00:00'],
                    ['day' => 'friday', 'start' => '21:00:00', 'end' => '23:59:59'],
                    
                    // Saturday - Unavailable
                    // Sunday - Unavailable (Church services)
                ];
                
                // Insert the availability schedule
                $insert_availability = $conn->prepare("INSERT INTO pastor_booking_availability (pastor_id, day_of_week, start_time, end_time, is_available, booking_duration_minutes, max_bookings_per_day, is_active) VALUES (?, ?, ?, ?, 1, 30, 8, 1)");
                $pastor_id = $pastor['id'] ?? 2; // Default pastor ID
                
                foreach ($availability_schedule as $slot) {
                    $insert_availability->bind_param("isss", $pastor_id, $slot['day'], $slot['start'], $slot['end']);
                    $insert_availability->execute();
                }
                $insert_availability->close();
            }
            
            // Get current availability for display
            $stmt = $conn->prepare("SELECT * FROM pastor_booking_availability WHERE is_active = 1 ORDER BY day_of_week, start_time");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $availability = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        }
        $conn->close();
    }
    
} catch (Exception $e) {
    $availability = [];
    $existing_bookings = [];
    $pastor = ['name' => 'Apostle Faty Musasizi', 'phone' => '+256753244480', 'email' => 'apostle@salemdominionministries.com'];
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = safe_html($_POST['name'] ?? '');
    $email = safe_html($_POST['email'] ?? '');
    $phone = safe_html($_POST['phone'] ?? '');
    $subject = safe_html($_POST['subject'] ?? '');
    $date = safe_html($_POST['date'] ?? '');
    $time = safe_html($_POST['time'] ?? '');
    $duration = safe_html($_POST['duration'] ?? '30');
    $message = safe_html($_POST['message'] ?? '');
    $booking_type = safe_html($_POST['booking_type'] ?? 'general');
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($date) || empty($time)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strtotime($date . ' ' . $time) <= strtotime('now')) {
        $error = "Please select a future date and time.";
    } else {
        try {
            // Reconnect to database for form submission
            $conn = getConnection();
            if ($conn) {
                // Check if slot is available
                $check_slot = $conn->prepare("SELECT id FROM pastor_bookings WHERE date = ? AND start_time = ? AND status != 'cancelled'");
                $check_slot->bind_param("ss", $date, $time);
                $check_slot->execute();
                
                if ($check_slot->get_result()->num_rows > 0) {
                    $error = "This time slot is already booked. Please select another time.";
                } else {
                    // Calculate end time
                    $end_time = date('H:i:s', strtotime($time) + ($duration * 60));
                    
                    // Insert booking with correct field names
                    $insert = $conn->prepare("INSERT INTO pastor_bookings (pastor_id, client_name, client_email, client_phone, booking_date, start_time, end_time, duration_minutes, booking_type, subject, description, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                    $pastor_id = $pastor['id'] ?? 2; // Default to pastor ID 2 if not found
                    $insert->bind_param("sissssississ", $pastor_id, $name, $email, $phone, $date, $time, $end_time, $duration, $booking_type, $subject, $message);
                    
                    if ($insert->execute()) {
                        $booking_id = $conn->insert_id;
                        $insert->close();
                    
                        // Update booking reference with actual ID
                        $booking_reference = 'BK' . date('Y') . str_pad($booking_id, 6, '0', STR_PAD_LEFT);
                        
                        // Send WhatsApp notification to pastor
                        $whatsapp_message = "*NEW PASTORAL CALL REQUEST*\n\n";
                        $whatsapp_message .= "*Name:* $name\n";
                        $whatsapp_message .= "*Email:* $email\n";
                        $whatsapp_message .= "*Phone:* $phone\n";
                        $whatsapp_message .= "*Subject:* $subject\n";
                        $whatsapp_message .= "*Date:* " . date('l, F j, Y', strtotime($date)) . "\n";
                        $whatsapp_message .= "*Time:* " . date('g:i A', strtotime($time)) . "\n";
                        $whatsapp_message .= "*Duration:* $duration minutes\n";
                        $whatsapp_message .= "*Type:* $booking_type\n";
                        $whatsapp_message .= "*Message:* $message\n\n";
                        $whatsapp_message .= "*Please confirm this booking.*\n";
                        $whatsapp_message .= "*Booking ID:* #$booking_id";
                        
                        // Send WhatsApp via API (you'll need to implement this)
                        $whatsapp_sent = sendWhatsAppMessage($pastor['phone'], $whatsapp_message);
                        
                        // Send confirmation email to user
                        $user_subject = "Pastoral Call Request Confirmed - Salem Dominion Ministries";
                        $user_message = "Dear $name,\n\nYour pastoral call request has been received:\n\nDate: " . date('l, F j, Y', strtotime($date)) . "\nTime: " . date('g:i A', strtotime($time)) . "\nDuration: $duration minutes\nSubject: $subject\n\nWe will contact you shortly to confirm the appointment.\n\nGod bless you!\nSalem Dominion Ministries\n\nPastor: " . $pastor['name'] . "\nPhone: " . $pastor['phone'] . "\nEmail: " . $pastor['email'];
                        
                        mail($email, $user_subject, $user_message, "From: noreply@salemdominionministries.com");
                        
                        $success = "Your pastoral call request has been submitted successfully! We will contact you soon to confirm your appointment.";
                        
                        // Clear form
                        $name = $email = $phone = $subject = $message = '';
                    } else {
                        $error = "Failed to submit your request. Please try again.";
                    }
                    $insert->close();
                }
                $check_slot->close();
                $conn->close();
            }
        } catch (Exception $e) {
            $error = "An error occurred. Please try again.";
            error_log($e->getMessage());
        }
    }
}

// WhatsApp message function (placeholder - implement with actual WhatsApp API)
function sendWhatsAppMessage($phone, $message) {
    // This is a placeholder - implement with actual WhatsApp Business API
    // For now, we'll just log the message
    error_log("WhatsApp to $phone: $message");
    return true;
}

// Clean any buffered output
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src <?php echo CSP_DEFAULT_SRC; ?>; script-src <?php echo CSP_SCRIPT_SRC; ?>; style-src <?php echo CSP_STYLE_SRC; ?>; font-src <?php echo CSP_FONT_SRC; ?>; connect-src <?php echo CSP_CONNECT_SRC; ?>; img-src 'self' data: https:;">
    <title>Schedule Pastoral Call | Salem Dominion Ministries</title>
    <meta name="description" content="Schedule a pastoral call for prayer, counseling, or spiritual guidance with Apostle Faty Musasizi">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            position: relative;
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

        /* Booking Container */
        .booking-container {
            padding: 120px 0 80px;
            min-height: 100vh;
        }

        .booking-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .booking-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .booking-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .booking-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        /* Form Styles */
        .form-label {
            color: var(--heavenly-gold);
            font-weight: 500;
            margin-bottom: 0.5rem;
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

        /* Calendar Styles */
        .calendar-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--heavenly-gold);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .calendar-day:hover:not(.disabled):not(.booked) {
            background: rgba(251, 191, 36, 0.2);
            transform: scale(1.05);
        }

        .calendar-day.selected {
            background: var(--gradient-divine);
            color: var(--snow-white);
        }

        .calendar-day.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .calendar-day.booked {
            background: rgba(239, 68, 68, 0.3);
            cursor: not-allowed;
        }

        .calendar-day.today {
            border: 2px solid var(--heavenly-gold);
        }

        /* Time Slots */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .time-slot {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .time-slot:hover:not(.disabled):not(.booked) {
            background: rgba(251, 191, 36, 0.2);
            transform: translateY(-2px);
        }

        .time-slot.selected {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
        }

        .time-slot.disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .time-slot.booked {
            background: rgba(239, 68, 68, 0.3);
            cursor: not-allowed;
        }

        /* Buttons */
        .btn-gold {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            width: 100%;
        }

        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(251, 191, 36, 0.4);
        }

        .btn-gold:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Alert Styles */
        .alert-success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #86efac;
            border-radius: 15px;
            padding: 1rem 1.5rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            border-radius: 15px;
            padding: 1rem 1.5rem;
        }

        /* Pastor Info */
        .pastor-info {
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .pastor-info h4 {
            color: var(--heavenly-gold);
            margin-bottom: 0.5rem;
        }

        .pastor-info p {
            margin: 0.25rem 0;
            opacity: 0.8;
        }

        /* Calendly Booking Section */
        .calendly-booking-section {
            margin: 2rem 0;
        }

        .calendly-info-card {
            background: rgba(251, 191, 36, 0.1);
            border: 2px solid rgba(251, 191, 36, 0.3);
            border-radius: 25px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .calendly-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(251, 191, 36, 0.3);
        }

        .calendly-header h3 {
            color: var(--heavenly-gold);
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .calendly-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .calendly-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            text-align: left;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: rgba(251, 191, 36, 0.1);
            transform: translateY(-2px);
        }

        .feature-item i {
            color: var(--heavenly-gold);
            font-size: 1.5rem;
            margin-right: 1rem;
            min-width: 30px;
        }

        .feature-item strong {
            color: var(--snow-white);
            font-size: 1.1rem;
            display: block;
            margin-bottom: 0.25rem;
        }

        .feature-item span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .calendly-action {
            text-align: center;
        }

        .btn-calendly-primary {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 20px 50px;
            font-size: 1.3rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);
        }

        .btn-calendly-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.4);
            color: var(--snow-white);
        }

        .btn-calendly-primary i {
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .calendly-secondary {
            margin-top: 1rem;
        }

        .calendly-secondary small {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Alternative Contact */
        .alternative-contact {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .alternative-contact p {
            color: var(--heavenly-gold);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .btn-contact-alt {
            background: rgba(14, 165, 233, 0.2);
            border: 2px solid rgba(14, 165, 233, 0.5);
            color: var(--snow-white);
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn-contact-alt:hover {
            background: rgba(14, 165, 233, 0.3);
            border-color: var(--ocean-blue);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.3);
            color: var(--snow-white);
        }

        .btn-contact-alt i {
            font-size: 1.2rem;
            margin-right: 0.75rem;
        }
        @media (max-width: 768px) {
            .booking-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .booking-title {
                font-size: 2rem;
            }

            .time-slots {
                grid-template-columns: repeat(2, 1fr);
            }

            .calendar-grid {
                gap: 0.25rem;
            }

            .calendar-day {
                font-size: 0.8rem;
            }
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--heavenly-gold);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-church me-2"></i>Salem Dominion Ministries
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="leadership.php">Leadership</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_pastor_call.php" class="text-warning">Book Call</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Booking Container -->
    <div class="booking-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="booking-card">
                        <!-- Pastor Info -->
                        <div class="pastor-info">
                            <h4><i class="fas fa-user-tie me-2"></i><?php echo safe_html($pastor['name']); ?></h4>
                            <p><i class="fas fa-phone me-2"></i><?php echo safe_html($pastor['phone']); ?></p>
                            <p><i class="fas fa-envelope me-2"></i><?php echo safe_html($pastor['email']); ?></p>
                            <div class="mt-3">
                                <a href="https://calendly.com/musasfaty24/30min" target="_blank" class="btn btn-sm btn-outline-light w-100">
                                    <i class="fas fa-calendar-check me-2"></i>Book Directly on Calendly
                                </a>
                            </div>
                        </div>

                        <!-- Booking Header -->
                        <div class="booking-header">
                            <h2 class="booking-title">Schedule Pastoral Call</h2>
                            <p class="booking-subtitle">Book a personal meeting with Apostle Faty Musasizi for prayer, counseling, or spiritual guidance</p>
                        </div>

                        <!-- Success/Error Messages -->
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Calendly Booking Section -->
                        <div class="calendly-booking-section">
                            <div class="row justify-content-center mb-4">
                                <div class="col-lg-8">
                                    <div class="calendly-info-card">
                                        <div class="calendly-header">
                                            <h3><i class="fas fa-calendar-check me-2"></i>Book Your Appointment</h3>
                                            <p>Choose your preferred date and time directly from our calendar</p>
                                        </div>
                                        
                                        <div class="calendly-features">
                                            <div class="feature-item">
                                                <i class="fas fa-clock me-2"></i>
                                                <div>
                                                    <strong>30-Minute Sessions</strong>
                                                    <span>Personal one-on-one time with the pastor</span>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item">
                                                <i class="fas fa-video me-2"></i>
                                                <div>
                                                    <strong>Video/Phone Available</strong>
                                                    <span>Connect via video call or phone</span>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item">
                                                <i class="fas fa-bell me-2"></i>
                                                <div>
                                                    <strong>Instant Reminders</strong>
                                                    <span>Automatic email and calendar reminders</span>
                                                </div>
                                            </div>
                                            
                                            <div class="feature-item">
                                                <i class="fas fa-mobile-alt me-2"></i>
                                                <div>
                                                    <strong>Mobile Friendly</strong>
                                                    <span>Book from any device</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="calendly-action">
                                            <a href="https://calendly.com/musasfaty24/30min" target="_blank" class="btn-calendly-primary">
                                                <i class="fas fa-calendar-plus me-2"></i>
                                                <span>Book Now on Calendly</span>
                                            </a>
                                            
                                            <div class="calendly-secondary">
                                                <small class="text-muted">
                                                    <i class="fas fa-external-link-alt me-1"></i>
                                                    Opens in new window • Secure booking platform
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <!-- Alternative Contact Info -->
                                    <div class="col-md-6">
                                        <a href="mailto:apostle@salemdominionministries.com" class="btn-contact-alt">
                                            <i class="fas fa-envelope me-2"></i>
                                            Send Email
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script nonce="<?php echo CSP_NONCE; ?>" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script nonce="<?php echo CSP_NONCE; ?>">
        // Calendar functionality
        class BookingCalendar {
            constructor() {
                this.selectedDate = null;
                this.selectedTime = null;
                this.bookedSlots = <?php echo json_encode($existing_bookings); ?>;
                this.init();
            }

            init() {
                this.generateCalendar();
                this.attachEventListeners();
            }

            generateCalendar() {
                const calendar = document.getElementById('calendar');
                const today = new Date();
                const currentMonth = today.getMonth();
                const currentYear = today.getFullYear();
                
                // Get first day of month
                const firstDay = new Date(currentYear, currentMonth, 1);
                const lastDay = new Date(currentYear, currentMonth + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDayOfWeek = firstDay.getDay();

                // Clear calendar
                calendar.innerHTML = '';

                // Add day headers
                const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                dayHeaders.forEach(day => {
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'calendar-day-header';
                    dayHeader.textContent = day;
                    dayHeader.style.fontWeight = 'bold';
                    dayHeader.style.color = 'var(--heavenly-gold)';
                    calendar.appendChild(dayHeader);
                });

                // Add empty cells for days before month starts
                for (let i = 0; i < startingDayOfWeek; i++) {
                    const emptyDay = document.createElement('div');
                    calendar.appendChild(emptyDay);
                }

                // Add days of the month
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day';
                    dayElement.textContent = day;
                    
                    const currentDate = new Date(currentYear, currentMonth, day);
                    const dateString = this.formatDate(currentDate);
                    
                    // Check if day is in the past
                    if (currentDate < today.setHours(0,0,0,0)) {
                        dayElement.classList.add('disabled');
                    }
                    
                    // Check if day is today
                    if (currentDate.toDateString() === new Date().toDateString()) {
                        dayElement.classList.add('today');
                    }
                    
                    // Check if day has bookings
                    const hasBookings = this.bookedSlots.some(booking => booking.date === dateString);
                    if (hasBookings) {
                        dayElement.classList.add('booked');
                        dayElement.title = 'Some slots already booked';
                    }
                    
                    dayElement.addEventListener('click', () => this.selectDate(currentDate, dayElement));
                    calendar.appendChild(dayElement);
                }
            }

            selectDate(date, element) {
                if (element.classList.contains('disabled')) return;
                
                // Remove previous selection
                document.querySelectorAll('.calendar-day.selected').forEach(el => el.classList.remove('selected'));
                
                // Add selection to clicked date
                element.classList.add('selected');
                this.selectedDate = date;
                
                // Update hidden input
                document.getElementById('selectedDate').value = this.formatDate(date);
                document.getElementById('selectedDateDisplay').textContent = date.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                
                // Update time slots based on selected date
                this.updateTimeSlots(date);
                
                document.querySelectorAll('.time-slot.selected').forEach(el => el.classList.remove('selected'));
            }

            updateTimeSlots(date) {
                const dateString = this.formatDate(date);
                const timeSlots = document.querySelectorAll('.time-slot');
                const dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
                
                // Define pastor availability based on the schedule
                const pastorAvailability = {
                    'monday': ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                    'tuesday': ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                    'wednesday': ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '21:00', '22:00', '23:00'],
                    'thursday': ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                    'friday': ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '21:00', '22:00', '23:00'],
                    'saturday': [], // Unavailable
                    'sunday': [] // Unavailable (Church services)
                };
                
                const availableTimes = pastorAvailability[dayOfWeek] || [];
                
                timeSlots.forEach(slot => {
                    slot.classList.remove('disabled', 'booked');
                    const slotTime = slot.dataset.time;
                    
                    // Check if this time slot is in pastor's availability
                    const isAvailable = availableTimes.includes(slotTime);
                    
                    // Check if this time slot is booked
                    const isBooked = this.bookedSlots.some(booking => 
                        booking.date === dateString && booking.start_time === slotTime + ':00'
                    );
                    
                    if (!isAvailable || isBooked) {
                        slot.classList.add('disabled');
                        slot.title = isBooked ? 'Already booked' : 'Pastor not available';
                    } else {
                        slot.title = 'Available';
                    }
                });
            }

            selectTime(time, element) {
                if (element.classList.contains('disabled') || element.classList.contains('booked')) return;
                
                // Remove previous selection
                document.querySelectorAll('.time-slot.selected').forEach(el => el.classList.remove('selected'));
                
                // Add selection to clicked time
                element.classList.add('selected');
                this.selectedTime = time;
                
                // Update hidden input
                document.getElementById('selectedTime').value = time;
            }

            formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            attachEventListeners() {
                // Time slot selection
                document.querySelectorAll('.time-slot').forEach(slot => {
                    slot.addEventListener('click', () => this.selectTime(slot.dataset.time, slot));
                });

                // Form submission
                document.getElementById('bookingForm').addEventListener('submit', (e) => {
                    if (!this.selectedDate || !this.selectedTime) {
                        e.preventDefault();
                        alert('Please select both date and time for your appointment.');
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner me-2"></span>Processing...';
                });
            }
        }

        // Initialize calendar when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new BookingCalendar();
        });
    </script>
</body>
</html>