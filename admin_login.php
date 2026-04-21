<?php
// Clean Admin Login - Works on Both Localhost and Hosting
// Enhanced session handling for mobile compatibility

// Set secure session parameters for mobile
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 0 for HTTP, 1 for HTTPS
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/db_connection.php';

// Initialize variables
$error = '';
$success = '';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Clear any existing session data
    session_unset();
    session_regenerate_id(true);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // First check database connection
            if (!$conn) {
                $error = 'Database connection failed. Please try again later.';
                error_log("Admin login: No database connection on " . ($_SERVER['HTTP_HOST'] ?? 'unknown'));
            } else {
                // Check admin users in database
                $admin_stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
                if ($admin_stmt) {
                    $admin_stmt->bind_param("s", $username);
                    $admin_stmt->execute();
                    $admin_result = $admin_stmt->get_result();
                    
                    if ($admin_result->num_rows > 0) {
                        $admin = $admin_result->fetch_assoc();
                        
                        // Debug: Log password verification attempt
                        error_log("Login attempt for username: " . $username . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                        
                        if (password_verify($password, $admin['password_hash'])) {
                            // Set secure session variables
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_user_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            $_SESSION['admin_name'] = $admin['full_name'];
                            $_SESSION['admin_role'] = $admin['role'];
                            $_SESSION['login_time'] = time();
                            $_SESSION['session_id'] = session_id();
                            
                            // Update last login
                            $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                            if ($update_stmt) {
                                $update_stmt->bind_param("i", $admin['id']);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                            
                            // Log successful login
                            error_log("Admin login successful for: " . $username . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                            
                            // Clear any output buffers
                            if (ob_get_level()) {
                                ob_end_clean();
                            }
                            
                            header('Location: admin_dashboard.php');
                            exit;
                        } else {
                            // Log failed password verification
                            error_log("Admin login failed: Invalid password for username: " . $username . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                            $error = 'Invalid credentials. Please try again.';
                        }
                        
                        $admin_stmt->close();
                    } else {
                        // Log user not found
                        error_log("Admin login failed: User not found - username: " . $username . " from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                        $error = 'Invalid credentials. Please try again.';
                    }
                } else {
                    $error = 'Database query failed. Please try again later.';
                    error_log("Admin login: Query preparation failed for username: " . $username);
                }
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
            error_log("Admin login exception: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    <meta name="description" content="Admin Login - Salem Dominion Ministries">
    <title>Admin Login - Salem Dominion Ministries</title>
    
    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/jpeg" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="//fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --sky-blue: #38bdf8;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
            --heavenly-gold: #fbbf24;
            --divine-light: #fef3c7;
            --shadow-divine: 0 20px 40px rgba(15, 23, 42, 0.15);
            --shadow-heavenly: 0 25px 50px rgba(251, 191, 36, 0.2);
            --gradient-ocean: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 50%, var(--sky-blue) 100%);
            --gradient-heaven: linear-gradient(135deg, var(--snow-white) 0%, var(--pearl-white) 50%, var(--sky-blue) 100%);
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
            background: var(--gradient-heaven);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

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

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: var(--snow-white);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: var(--shadow-heavenly);
            border: 1px solid rgba(251, 191, 36, 0.2);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
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

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--gradient-heaven);
            padding: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.3);
            transition: all 0.5s ease;
        }

        .login-logo:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: 0 0 40px rgba(251, 191, 36, 0.5);
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--midnight-blue);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-family: 'Great Vibes', cursive;
            color: var(--heavenly-gold);
            font-size: 1.2rem;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--midnight-blue);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--pearl-white);
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--pearl-white);
            color: var(--midnight-blue);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--ocean-blue);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
            background: var(--snow-white);
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ocean-blue);
            font-size: 1.1rem;
        }

        .form-control.with-icon {
            padding-left: 50px;
        }

        .btn-login {
            width: 100%;
            padding: 18px;
            background: var(--gradient-ocean);
            color: var(--snow-white);
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: relative;
            overflow: hidden;
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
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.3);
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
        }

        .alert-error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .alert-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link a {
            color: var(--ocean-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: var(--heavenly-gold);
            transform: translateY(-2px);
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
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

        /* Enhanced Mobile Responsive Design */
        @media (max-width: 1200px) {
            .login-card {
                max-width: 400px;
                padding: 2.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                max-width: 380px;
                padding: 2.2rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
            
            .login-subtitle {
                font-size: 1.1rem;
            }
            
            .login-logo {
                width: 70px;
                height: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 10px;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .login-card {
                padding: 2rem;
                margin: 20px;
                border-radius: 20px;
                max-width: 95%;
            }
            
            .login-title {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .login-subtitle {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .login-logo {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }
            
            .form-control {
                padding: 14px 45px 14px 15px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn-login {
                padding: 16px;
                font-size: 1rem;
            }
            
            .input-icon {
                left: 15px;
                font-size: 1rem;
                top: 17px;
            }
            
            .form-control.with-icon {
                padding-left: 45px;
            }
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 5px;
            }
            
            .login-card {
                padding: 1.5rem;
                margin: 10px;
                border-radius: 15px;
                max-width: 98%;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
            
            .login-subtitle {
                font-size: 0.9rem;
            }
            
            .login-logo {
                width: 50px;
                height: 50px;
            }
            
            .form-control {
                padding: 12px 40px 12px 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn-login {
                padding: 14px;
                font-size: 0.95rem;
            }
            
            .input-icon {
                left: 12px;
                font-size: 0.9rem;
                top: 15px;
            }
            
            .form-control.with-icon {
                padding-left: 38px;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            .alert {
                padding: 0.8rem 1rem;
                font-size: 0.9rem;
            }

            .login-header {
                margin-bottom: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 3px;
            }
            
            .login-card {
                padding: 1.2rem;
                margin: 5px;
                border-radius: 12px;
            }
            
            .login-title {
                font-size: 1.2rem;
            }
            
            .login-subtitle {
                font-size: 0.85rem;
            }
            
            .login-logo {
                width: 45px;
                height: 45px;
                margin-bottom: 0.8rem;
            }
            
            .form-control {
                padding: 10px 35px 10px 10px;
                font-size: 16px;
            }
            
            .btn-login {
                padding: 12px;
                font-size: 0.9rem;
            }
            
            .input-icon {
                left: 10px;
                font-size: 0.85rem;
                top: 13px;
            }
            
            .form-control.with-icon {
                padding-left: 33px;
            }
            
            .form-group {
                margin-bottom: 0.8rem;
            }
            
            .alert {
                padding: 0.7rem 0.8rem;
                font-size: 0.85rem;
            }

            .back-link {
                margin-top: 1.5rem;
            }
        }

        /* Mobile-specific fixes for touch devices */
        @media (hover: none) and (pointer: coarse) {
            .login-logo:hover {
                transform: none;
            }

            .btn-login:hover {
                transform: none;
            }

            .back-link a:hover {
                transform: none;
            }

            .login-card {
                box-shadow: var(--shadow-heavenly);
            }
        }

        /* Additional mobile optimizations */
        @media (max-width: 360px) {
            .login-card {
                padding: 1rem;
                margin: 2px;
            }
            
            .login-title {
                font-size: 1.1rem;
            }
            
            .login-subtitle {
                font-size: 0.8rem;
            }
            
            .login-logo {
                width: 40px;
                height: 40px;
            }
            
            .form-control {
                padding: 8px 30px 8px 8px;
                font-size: 15px;
            }
            
            .btn-login {
                padding: 10px;
                font-size: 0.85rem;
            }
            
            .input-icon {
                left: 8px;
                font-size: 0.8rem;
                top: 11px;
            }
            
            .form-control.with-icon {
                padding-left: 28px;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 1.2rem;
                margin: 5px;
            }
            
            .login-title {
                font-size: 1.2rem;
            }
            
            .login-subtitle {
                font-size: 0.85rem;
            }
            
            .login-logo {
                width: 45px;
                height: 45px;
            }
            
            .form-control {
                padding: 8px 35px 8px 10px;
                font-size: 12px;
            }
            
            .btn-login {
                padding: 10px;
                font-size: 0.85rem;
            }
            
            .input-icon {
                left: 10px;
                font-size: 0.8rem;
            }
            
            .form-control.with-icon {
                padding-left: 30px;
            }
            
            .back-link {
                margin-top: 1rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 360px) {
            .login-card {
                padding: 1rem;
            }
            
            .login-title {
                font-size: 1.1rem;
            }
            
            .btn-login {
                padding: 8px;
                font-size: 0.8rem;
            }
        }
        
        /* Touch-friendly adjustments */
        @media (hover: none) and (pointer: coarse) {
            .btn-login {
                min-height: 44px;
                min-width: 44px;
            }
            
            .form-control {
                min-height: 44px;
            }
            
            .login-card {
                touch-action: manipulation;
            }
        }
        
        /* Landscape mobile adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .login-container {
                min-height: auto;
                padding: 20px 10px;
            }
            
            .login-card {
                margin: 0;
                max-width: 500px;
            }
        }
        
        /* Tablet devices */
        @media (min-width: 769px) and (max-width: 1024px) {
            .login-card {
                max-width: 450px;
                padding: 2.5rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
            
            .form-control {
                font-size: 0.95rem;
                padding: 14px 18px;
            }
        }
        
        /* Large desktop screens */
        @media (min-width: 1025px) {
            .login-card {
                max-width: 500px;
                padding: 3rem;
            }
            
            .login-title {
                font-size: 2.2rem;
            }
        }
        
        /* Extra small phones */
        @media (max-width: 320px) {
            .login-card {
                padding: 1rem;
                margin: 2px;
                border-radius: 15px;
            }
            
            .login-title {
                font-size: 1.1rem;
            }
            
            .login-subtitle {
                font-size: 0.8rem;
            }
            
            .form-control {
                padding: 10px 30px 10px 12px;
                font-size: 11px;
            }
            
            .btn-login {
                padding: 8px;
                font-size: 0.8rem;
            }
            
            .input-icon {
                left: 8px;
                font-size: 0.7rem;
            }
            
            .form-control.with-icon {
                padding-left: 25px;
            }
        }
        
        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .form-control {
                min-height: 44px; /* iOS touch target size */
                font-size: 16px; /* Prevent zoom on iOS */
            }
            
            .btn-login {
                min-height: 44px; /* iOS touch target size */
                font-size: 16px; /* Prevent zoom on iOS */
            }
        }
    </style>
</head>
<body>
    <!-- Particles Background -->
    <div class="particles" id="particles"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries" class="login-logo">
                <h1 class="login-title">Admin Portal</h1>
                <p class="login-subtitle">Pastor Faty Musasizi</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm" novalidate>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div style="position: relative;">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control with-icon" 
                               placeholder="Enter your username" required autocomplete="username" 
                               autocapitalize="none" spellcheck="false">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control with-icon" 
                               placeholder="Enter your password" required autocomplete="current-password"
                               autocapitalize="none" spellcheck="false">
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <span class="btn-text">Sign In to Admin Panel</span>
                </button>
            </form>
            
            <div class="back-link">
                <a href="index.php">
                    <i class="fas fa-arrow-left me-2"></i>
                    Back to Website
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Enhanced form validation and mobile compatibility
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            
            // Mobile-friendly form submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Disable button to prevent double submission
                    if (loginBtn) {
                        loginBtn.disabled = true;
                        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i><span class="btn-text">Signing In...</span>';
                    }
                    
                    // Basic validation
                    const username = usernameInput.value.trim();
                    const password = passwordInput.value.trim();
                    
                    if (!username || !password) {
                        if (loginBtn) {
                            loginBtn.disabled = false;
                            loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i><span class="btn-text">Sign In to Admin Panel</span>';
                        }
                        return;
                    }
                    
                    // Submit form
                    const formData = new FormData(form);
                    fetch('', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Parse response and redirect or show error
                        if (html.includes('admin_dashboard.php')) {
                            window.location.href = 'admin_dashboard.php';
                        } else {
                            // Show error message
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'alert alert-error';
                            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Invalid credentials. Please try again.';
                            form.parentNode.insertBefore(errorDiv, form);
                            
                            if (loginBtn) {
                                loginBtn.disabled = false;
                                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i><span class="btn-text">Sign In to Admin Panel</span>';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Login error:', error);
                        if (loginBtn) {
                            loginBtn.disabled = false;
                            loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i><span class="btn-text">Sign In to Admin Panel</span>';
                        }
                    });
                });
            }
            
            // Mobile-friendly input handling
            [usernameInput, passwordInput].forEach(input => {
                if (input) {
                    // Prevent zoom on mobile
                    input.addEventListener('wheel', function(e) {
                        if (e.ctrlKey) {
                            e.preventDefault();
                        }
                    });
                    
                    // Auto-focus on mobile
                    input.addEventListener('focus', function() {
                        this.select();
                    });
                }
            });
        });
        
        // Create particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;
            
            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 20 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize particles
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
        });
        
        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
