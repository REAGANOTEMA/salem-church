<?php
/**
 * Salem Dominion Ministries - User Login Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    redirect(BASE_URL . '/index.php');
}

$currentPage  = 'login';
$pageTitle    = 'Login - ' . CHURCH_NAME;
$csrfToken    = csrfToken();
$flash        = getFlash();

require_once __DIR__ . '/components/header.php';
?>
<style>
.auth-page {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, rgba(15,23,42,0.03) 0%, rgba(14,165,233,0.05) 100%);
}
.auth-card {
    width: 100%;
    max-width: 440px;
    background: var(--white);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.04);
    padding: 48px 40px;
    animation: authSlideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes authSlideUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}
.auth-logo {
    text-align: center;
    margin-bottom: 32px;
}
.auth-logo img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 12px;
    border: 3px solid rgba(14,165,233,0.2);
}
.auth-logo h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    color: var(--midnight);
    margin: 0 0 4px;
}
.auth-logo p {
    font-size: 0.88rem;
    color: var(--gray-500);
    margin: 0;
}
.auth-form .form-group {
    margin-bottom: 20px;
}
.auth-form label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--gray-700);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.auth-form .input-wrap {
    position: relative;
}
.auth-form .input-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-400);
    font-size: 0.9rem;
    transition: color 0.3s;
}
.auth-form input[type="text"],
.auth-form input[type="email"],
.auth-form input[type="password"] {
    width: 100%;
    padding: 14px 14px 14px 42px;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    font-size: 0.95rem;
    font-family: 'Montserrat', sans-serif;
    color: var(--gray-700);
    background: var(--white);
    transition: all 0.3s;
    outline: none;
}
.auth-form input:focus {
    border-color: var(--ocean);
    box-shadow: 0 0 0 4px rgba(14,165,233,0.1);
}
.auth-form input:focus + i,
.auth-form input:focus ~ i {
    color: var(--ocean);
}
.auth-form .toggle-pass {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-400);
    cursor: pointer;
    padding: 4px;
    font-size: 0.9rem;
}
.auth-form .toggle-pass:hover { color: var(--ocean); }
.auth-form .form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}
.auth-form .remember-check {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: var(--gray-600);
    cursor: pointer;
}
.auth-form .remember-check input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: var(--ocean);
    cursor: pointer;
}
.auth-form .forgot-link {
    font-size: 0.85rem;
    color: var(--ocean);
    font-weight: 600;
    text-decoration: none;
}
.auth-form .forgot-link:hover { text-decoration: underline; }
.auth-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, var(--ocean), #0284c7);
    color: var(--white);
    font-size: 1rem;
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.auth-btn:hover {
    background: linear-gradient(135deg, #0284c7, var(--midnight));
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(14,165,233,0.35);
}
.auth-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.auth-btn .spinner {
    display: none;
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
.auth-btn.loading .spinner { display: inline-block; }
.auth-btn.loading .btn-text { display: none; }
@keyframes spin { to { transform: rotate(360deg); } }
.auth-divider {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 24px 0;
    color: var(--gray-400);
    font-size: 0.82rem;
}
.auth-divider::before, .auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--gray-200);
}
.auth-register-link {
    text-align: center;
    font-size: 0.9rem;
    color: var(--gray-500);
}
.auth-register-link a {
    color: var(--ocean);
    font-weight: 700;
    text-decoration: none;
}
.auth-register-link a:hover { text-decoration: underline; }
.auth-alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 0.88rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: authSlideUp 0.4s ease;
}
.auth-alert.error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
.auth-alert.success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
@media (max-width: 480px) {
    .auth-card { padding: 32px 24px; border-radius: 20px; }
}
</style>

<main class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?php echo LOGO_URL; ?>" alt="<?php echo CHURCH_NAME; ?>">
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
        </div>

        <?php if ($flash): ?>
            <div class="auth-alert <?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div id="loginAlert" class="auth-alert" style="display:none;"></div>

        <form class="auth-form" id="loginForm" onsubmit="return handleLogin(event)">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    <i class="fas fa-lock"></i>
                    <button type="button" class="toggle-pass" onclick="togglePassword(this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-check">
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>
            </div>

            <button type="submit" class="auth-btn" id="loginBtn">
                <span class="btn-text"><i class="fas fa-right-to-bracket"></i> Sign In</span>
                <span class="spinner"></span>
            </button>
        </form>

        <div class="auth-divider">or</div>

        <p class="auth-register-link">
            Don't have an account? <a href="register.php">Create one now</a>
        </p>
    </div>
</main>

<script>
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function showAuthAlert(type, message) {
    var el = document.getElementById('loginAlert');
    el.className = 'auth-alert ' + type;
    el.innerHTML = '<i class="fas ' + (type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i> ' + message;
    el.style.display = 'flex';
}

function handleLogin(e) {
    e.preventDefault();
    var btn = document.getElementById('loginBtn');
    var form = e.target;
    btn.classList.add('loading');
    btn.disabled = true;
    document.getElementById('loginAlert').style.display = 'none';

    var formData = new FormData(form);
    var data = {};
    formData.forEach(function(v, k) { data[k] = v; });

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php?action=login', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.classList.remove('loading');
            btn.disabled = false;
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showAuthAlert('success', res.message || 'Login successful!');
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 800);
                } else {
                    showAuthAlert('error', res.message || 'Login failed. Please try again.');
                }
            } catch(ex) {
                showAuthAlert('error', 'An error occurred. Please try again.');
            }
        }
    };
    xhr.onerror = function() {
        btn.classList.remove('loading');
        btn.disabled = false;
        showAuthAlert('error', 'Network error. Please check your connection.');
    };
    xhr.send(JSON.stringify(data));
    return false;
}
</script>

<?php require_once __DIR__ . '/components/footer.php'; ?>
