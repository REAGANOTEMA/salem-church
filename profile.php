<?php
// ENHANCED USER PROFILE - Salem Dominion Ministries
// Features: Password Change, Profile Image Upload, Professional Design
require_once 'db_connection.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = createDatabaseConnection();
$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Get user information
$user_info = null;
if ($conn) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_result->num_rows > 0) {
            $user_info = $user_result->fetch_assoc();
        }
        $user_stmt->close();
    }
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Only JPEG, PNG, GIF, and WebP images are allowed';
        }
        
        // Validate file size
        if ($file['size'] > $max_size) {
            $errors[] = 'Image size must be less than 5MB';
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $upload_path = 'uploads/profiles/' . $filename;
            
            // Create directory if it doesn't exist
            if (!is_dir('uploads/profiles')) {
                mkdir('uploads/profiles', 0755, true);
            }
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update database
                if ($conn) {
                    $update_stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $filename, $user_id);
                        if ($update_stmt->execute()) {
                            $success = 'Profile image updated successfully!';
                            // Update user_info
                            $user_info['profile_image'] = $filename;
                        } else {
                            $errors[] = 'Failed to update profile image in database';
                        }
                        $update_stmt->close();
                    }
                }
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    } else {
        $errors[] = 'Please select a valid image file';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters long';
    } else {
        // Verify current password
        if ($user_info && password_verify($current_password, $user_info['password_hash'])) {
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            if ($conn) {
                $password_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                if ($password_stmt) {
                    $password_stmt->bind_param("si", $new_password_hash, $user_id);
                    if ($password_stmt->execute()) {
                        $success = 'Password changed successfully!';
                    } else {
                        $errors[] = 'Failed to update password';
                    }
                    $password_stmt->close();
                }
            }
        } else {
            $errors[] = 'Current password is incorrect';
        }
    }
}

// Handle profile information update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    if (empty($first_name) || empty($last_name)) {
        $errors[] = 'First name and last name are required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    } else {
        if ($conn) {
            $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, country = ? WHERE id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $country, $user_id);
                if ($update_stmt->execute()) {
                    $success = 'Profile information updated successfully!';
                    // Update user_info
                    $user_info['first_name'] = $first_name;
                    $user_info['last_name'] = $last_name;
                    $user_info['email'] = $email;
                    $user_info['phone'] = $phone;
                    $user_info['country'] = $country;
                } else {
                    $errors[] = 'Failed to update profile information';
                }
                $update_stmt->close();
            }
        }
    }
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';
    
    if (empty($subject)) {
        $errors[] = 'Message subject is required';
    } elseif (empty($message)) {
        $errors[] = 'Message content is required';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    } else {
        if ($conn) {
            // Get admin recipient ID
            $admin_query = $conn->query("SELECT id FROM admin_users WHERE is_active = 1 LIMIT 1");
            $admin_result = $admin_query->fetch_assoc();
            $admin_id = $admin_result['id'] ?? null;
            
            if ($admin_id) {
                $message_stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, subject, message, message_type, priority) VALUES (?, ?, ?, ?, 'user_to_admin', ?)");
                if ($message_stmt) {
                    $message_stmt->bind_param("iisss", $user_id, $admin_id, $subject, $message, $priority);
                    if ($message_stmt->execute()) {
                        $success = 'Message sent to admin successfully!';
                    } else {
                        $errors[] = 'Failed to send message';
                    }
                    $message_stmt->close();
                }
            } else {
                $errors[] = 'No admin available to receive messages';
            }
        }
    }
}

// Get user's sent messages
$user_messages = [];
if ($conn) {
    $messages_stmt = $conn->prepare("SELECT m.*, a.full_name as admin_name FROM messages m LEFT JOIN admin_users a ON m.recipient_id = a.id WHERE m.sender_id = ? ORDER BY m.created_at DESC");
    if ($messages_stmt) {
        $messages_stmt->bind_param("i", $user_id);
        $messages_stmt->execute();
        $messages_result = $messages_stmt->get_result();
        while ($row = $messages_result->fetch_assoc()) {
            $user_messages[] = $row;
        }
        $messages_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    <meta name="description" content="Enhanced User Profile - Salem Dominion Ministries">
    <title>My Profile - Salem Dominion Ministries</title>
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
            --gradient-spirit: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-warmth: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 50%, #334155 100%);
            color: var(--snow-white);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Enhanced Navigation */
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

        /* Profile Section */
        .profile-section {
            padding: 100px 0 80px;
            min-height: 100vh;
        }

        /* Enhanced Cards */
        .profile-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.2);
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(251, 191, 36, 0.2);
            border-color: rgba(251, 191, 36, 0.3);
        }

        /* Profile Header */
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--gradient-divine);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);
            position: relative;
            overflow: hidden;
            border: 4px solid rgba(251, 191, 36, 0.3);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-avatar .avatar-placeholder {
            font-size: 3rem;
            font-weight: 700;
        }

        .profile-name {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 0.5rem;
        }

        .profile-role {
            color: var(--ocean-blue);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        /* Form Styles */
        .form-label {
            color: var(--heavenly-gold);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--snow-white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
            color: var(--snow-white);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Section Titles */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title i {
            font-size: 1.5rem;
        }

        /* Action Buttons */
        .action-btn {
            background: var(--gradient-divine);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-password {
            background: var(--gradient-spirit);
        }

        .btn-password:hover {
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-image {
            background: var(--gradient-warmth);
        }

        .btn-image:hover {
            box-shadow: 0 10px 25px rgba(240, 147, 251, 0.4);
        }

        /* Alert Styles */
        .alert {
            border-radius: 15px;
            margin-bottom: 1.5rem;
            border: none;
            padding: 1rem 1.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Image Upload Area */
        .image-upload-area {
            border: 2px dashed rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(251, 191, 36, 0.05);
        }

        .image-upload-area:hover {
            border-color: var(--heavenly-gold);
            background: rgba(251, 191, 36, 0.1);
        }

        .image-upload-area i {
            font-size: 3rem;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        /* Tab Navigation */
        .tab-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
        }

        .tab-btn {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            padding: 10px 20px;
            border-radius: 10px 10px 0 0;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab-btn.active {
            background: var(--gradient-divine);
            color: white;
        }

        .tab-btn:hover {
            color: var(--heavenly-gold);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 100%);
            color: var(--snow-white);
            padding: 40px 0 20px;
            margin-top: 80px;
        }

        .footer a {
            color: var(--snow-white);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: var(--heavenly-gold);
        }

        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            .profile-section {
                padding: 80px 0 60px;
            }

            .profile-name {
                font-size: 2rem;
            }

            .profile-card {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .tab-nav {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .tab-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }

            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .profile-name {
                font-size: 1.8rem;
            }

            .profile-card {
                padding: 1.2rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
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
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sermons.php">Sermons</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donate.php">Donate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link logout-btn" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <!-- Profile Header -->
            <div class="profile-header" data-aos="fade-up">
                <div class="profile-avatar">
                    <?php if ($user_info && !empty($user_info['profile_image'])): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($user_info['profile_image']); ?>" alt="Profile Image">
                    <?php else: ?>
                        <span class="avatar-placeholder">
                            <?php echo strtoupper(substr($user_info['first_name'] ?? 'U', 0) . substr($user_info['last_name'] ?? 'U', 0)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars(($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? '')); ?></h1>
                <p class="profile-role">
                    <i class="fas fa-user me-2"></i>
                    <?php echo htmlspecialchars(ucfirst($user_info['role'] ?? 'user')); ?>
                </p>
                <p class="text-muted">
                    <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user_info['email'] ?? ''); ?>
                </p>
                <?php if (!empty($user_info['phone'])): ?>
                <p class="text-muted">
                    <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($user_info['phone']); ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($user_info['country'])): ?>
                <p class="text-muted">
                    <i class="fas fa-globe me-2"></i><?php echo htmlspecialchars($user_info['country']); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Alerts -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" data-aos="fade-down">
                    <?php foreach ($errors as $error): ?>
                        <div><i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" data-aos="fade-down">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Management Tabs -->
            <div class="profile-card" data-aos="fade-up" data-aos-delay="100">
                <div class="tab-nav">
                    <button class="tab-btn active" onclick="showTab('personal')">
                        <i class="fas fa-user me-2"></i>Personal Info
                    </button>
                    <button class="tab-btn" onclick="showTab('image')">
                        <i class="fas fa-camera me-2"></i>Profile Picture
                    </button>
                    <button class="tab-btn" onclick="showTab('password')">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </button>
                    <button class="tab-btn" onclick="showTab('messages')">
                        <i class="fas fa-envelope me-2"></i>Messages
                    </button>
                </div>

                <!-- Personal Information Tab -->
                <div id="personalTab" class="tab-content active">
                    <h2 class="section-title">
                        <i class="fas fa-user-edit"></i>
                        Personal Information
                    </h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                       value="<?php echo htmlspecialchars($user_info['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                       value="<?php echo htmlspecialchars($user_info['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($user_info['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>"
                                       placeholder="+1 (555) 123-4567">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                   value="<?php echo htmlspecialchars($user_info['country'] ?? ''); ?>"
                                   placeholder="United States">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="action-btn">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="dashboard.php" class="action-btn" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Profile Image Tab -->
                <div id="imageTab" class="tab-content">
                    <h2 class="section-title">
                        <i class="fas fa-camera"></i>
                        Profile Picture
                    </h2>
                    
                    <div class="text-center mb-4">
                        <div class="profile-avatar mx-auto" style="width: 200px; height: 200px; font-size: 4rem;">
                            <?php if ($user_info && !empty($user_info['profile_image'])): ?>
                                <img src="uploads/profiles/<?php echo htmlspecialchars($user_info['profile_image']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <span class="avatar-placeholder">
                                    <?php echo strtoupper(substr($user_info['first_name'] ?? 'U', 0) . substr($user_info['last_name'] ?? 'U', 0)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-3">Upload a new profile picture (JPEG, PNG, GIF, WebP - Max 5MB)</p>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_image">
                        
                        <div class="image-upload-area" onclick="document.getElementById('profile_image').click()">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h5>Click to Upload New Image</h5>
                            <p class="mb-0">or drag and drop your image here</p>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        </div>
                        
                        <div id="imagePreview" class="mt-3 text-center" style="display: none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 10px;">
                            <p class="mt-2">Preview of your new profile picture</p>
                        </div>
                        
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="action-btn btn-image">
                                <i class="fas fa-upload me-2"></i>Upload Image
                            </button>
                            <button type="button" class="action-btn" style="background: rgba(255,255,255,0.2);" onclick="resetImageUpload()">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change Tab -->
                <div id="passwordTab" class="tab-content">
                    <h2 class="section-title">
                        <i class="fas fa-lock"></i>
                        Change Password
                    </h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                            <small class="text-muted">Password must be at least 8 characters long</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            For your security, please choose a strong password that includes:
                            <ul class="mb-0 mt-2">
                                <li>At least 8 characters</li>
                                <li>Both uppercase and lowercase letters</li>
                                <li>At least one number</li>
                                <li>At least one special character</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="action-btn btn-password">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                            <button type="button" class="action-btn" style="background: rgba(255,255,255,0.2);" onclick="clearPasswordForm()">
                                <i class="fas fa-times me-2"></i>Clear
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Messages Tab -->
                <div id="messagesTab" class="tab-content">
                    <h2 class="section-title">
                        <i class="fas fa-envelope"></i>
                        Contact Admin
                    </h2>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h4 style="color: var(--heavenly-gold); margin-bottom: 1.5rem;">
                                <i class="fas fa-paper-plane me-2"></i>Send New Message
                            </h4>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="send_message">
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required
                                           placeholder="Enter message subject">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <option value="low">Low</option>
                                        <option value="normal" selected>Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required
                                              placeholder="Type your message here... (minimum 10 characters)"></textarea>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="action-btn" style="background: var(--gradient-spirit);">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                    <button type="button" class="action-btn" style="background: rgba(255,255,255,0.2);" onclick="clearMessageForm()">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-6">
                            <h4 style="color: var(--heavenly-gold); margin-bottom: 1.5rem;">
                                <i class="fas fa-history me-2"></i>Message History
                            </h4>
                            
                            <div class="message-history" style="max-height: 400px; overflow-y: auto;">
                                <?php if (!empty($user_messages)): ?>
                                    <?php foreach ($user_messages as $msg): ?>
                                        <div class="message-item" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 style="color: var(--heavenly-gold); margin: 0; font-weight: 600;">
                                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                                </h6>
                                                <span class="badge" style="
                                                    <?php 
                                                    switch($msg['priority']) {
                                                        case 'urgent': echo 'background: #ef4444;'; break;
                                                        case 'high': echo 'background: #f59e0b;'; break;
                                                        case 'normal': echo 'background: #3b82f6;'; break;
                                                        case 'low': echo 'background: #6b7280;'; break;
                                                    }
                                                    ?>">
                                                    <?php echo ucfirst($msg['priority']); ?>
                                                </span>
                                            </div>
                                            <p style="color: rgba(255,255,255,0.8); margin-bottom: 0.5rem; line-height: 1.4;">
                                                <?php echo htmlspecialchars(substr($msg['message'], 0, 150)); ?>...
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small style="color: rgba(255,255,255,0.6);">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                                                </small>
                                                <span class="badge" style="
                                                    <?php 
                                                    switch($msg['status']) {
                                                        case 'unread': echo 'background: #ef4444;'; break;
                                                        case 'read': echo 'background: #22c55e;'; break;
                                                        case 'replied': echo 'background: #3b82f6;'; break;
                                                    }
                                                    ?>">
                                                    <?php echo ucfirst($msg['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox" style="font-size: 3rem; color: var(--heavenly-gold); opacity: 0.5;"></i>
                                        <p class="mt-3">No messages sent yet. Start a conversation with the admin!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Professional Footer -->
    <footer class="footer">
        <div class="container">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 1000, once: true });</script>
    
    <script>
        // Tab navigation
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + 'Tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        // Image preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Reset image upload
        function resetImageUpload() {
            document.getElementById('profile_image').value = '';
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('previewImg').src = '';
        }
        
        // Clear password form
        function clearPasswordForm() {
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
        }
        
        // Clear message form
        function clearMessageForm() {
            document.getElementById('subject').value = '';
            document.getElementById('message').value = '';
            document.getElementById('priority').value = 'normal';
        }
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Any initialization code
        });
    </script>
</body>
</html>
