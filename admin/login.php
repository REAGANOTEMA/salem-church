<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $result = auth()->adminLogin($username, $password);
            if ($result['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= CHURCH_NAME ?></title>
    <link rel="icon" href="<?= LOGO_URL ?>" type="image/jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 40%, rgba(14, 165, 233, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 70% 60%, rgba(56, 189, 248, 0.06) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.04) 0%, transparent 60%);
            animation: bgPulse 15s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes bgPulse {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-2%, -1%) scale(1.02); }
            66% { transform: translate(1%, 2%) scale(0.98); }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.1);
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
            background: linear-gradient(90deg, #0ea5e9, #38bdf8, #0ea5e9);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { background-position: 200% 0; }
            50% { background-position: -200% 0; }
        }

        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .login-logo {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: cover;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(14, 165, 233, 0.2);
            border: 3px solid #f0f9ff;
        }

        .login-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }

        .login-subtitle {
            font-size: 14px;
            color: #64748b;
            font-weight: 400;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shakeIn 0.4s ease;
        }

        @keyframes shakeIn {
            0% { transform: translateX(-10px); opacity: 0; }
            25% { transform: translateX(6px); }
            50% { transform: translateX(-4px); }
            75% { transform: translateX(2px); }
            100% { transform: translateX(0); opacity: 1; }
        }

        .error-message i { font-size: 16px; flex-shrink: 0; }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 16px;
            z-index: 1;
            transition: color 0.2s;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input::placeholder { color: #94a3b8; }

        .form-input:focus {
            border-color: #0ea5e9;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
        }

        .form-input:focus ~ .input-icon,
        .form-group:hover .input-icon { color: #0ea5e9; }

        .password-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color 0.2s;
            z-index: 1;
        }

        .password-toggle:hover { color: #64748b; }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13px;
            color: #475569;
            user-select: none;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 2px solid #cbd5e1;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            background: #fff;
        }

        .remember-me input[type="checkbox"]:checked {
            background: #0ea5e9;
            border-color: #0ea5e9;
        }

        .remember-me input[type="checkbox"]:checked::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 9px;
            color: #fff;
        }

        .forgot-link {
            font-size: 13px;
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover { color: #0284c7; }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.35);
        }

        .login-btn:active { transform: translateY(0); }

        .login-btn.loading {
            pointer-events: none;
            opacity: 0.85;
        }

        .login-btn .btn-text { transition: opacity 0.2s; }
        .login-btn.loading .btn-text { opacity: 0; }

        .login-btn .spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        .login-btn.loading .spinner { display: block; }

        @keyframes spin { to { transform: translate(-50%, -50%) rotate(360deg); } }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 28px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover { color: #0ea5e9; }
        .back-link i { font-size: 12px; }

        .particles {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(56, 189, 248, 0.15);
            border-radius: 50%;
            animation: float linear infinite;
        }

        @keyframes float {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        @media (max-width: 480px) {
            .login-container { padding: 16px; }
            .login-card { padding: 36px 24px; border-radius: 16px; }
            .login-title { font-size: 20px; }
            .login-logo { width: 60px; height: 60px; }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="<?= LOGO_URL ?>" alt="<?= CHURCH_NAME ?>" class="login-logo"
                     onerror="this.src='<?= LOGO_FALLBACK ?>'">
                <h1 class="login-title">Admin Panel</h1>
                <p class="login-subtitle">Sign in to manage <?= CHURCH_NAME ?></p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrf ?>">

                <div class="form-group">
                    <label class="form-label" for="username">Username or Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="username" name="username" class="form-input"
                               placeholder="Enter your username or email"
                               value="<?= sanitize($_POST['username'] ?? '') ?>"
                               required autocomplete="username" autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-input"
                               placeholder="Enter your password"
                               required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a href="<?= BASE_URL ?>" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <a href="<?= BASE_URL ?>" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to website
            </a>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('loginBtn').classList.add('loading');
        });

        (function() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (8 + Math.random() * 12) + 's';
                p.style.animationDelay = Math.random() * 10 + 's';
                p.style.width = p.style.height = (2 + Math.random() * 4) + 'px';
                container.appendChild(p);
            }
        })();
    </script>
</body>
</html>
