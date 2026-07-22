<?php
/**
 * Salem Dominion Ministries - User Registration Page
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

$currentPage = 'register';
$pageTitle   = 'Register - ' . CHURCH_NAME;
$csrfToken   = csrfToken();
$flash       = getFlash();

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
    max-width: 500px;
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
.auth-form .form-row {
    display: flex;
    gap: 16px;
}
.auth-form .form-row .form-group { flex: 1; }
.auth-form .form-group {
    margin-bottom: 18px;
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
.auth-form input[type="tel"],
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
.auth-form .password-requirements {
    font-size: 0.78rem;
    color: var(--gray-400);
    margin-top: 6px;
}
.auth-form .password-requirements span.met { color: #16a34a; }
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
    margin-top: 8px;
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
.auth-login-link {
    text-align: center;
    font-size: 0.9rem;
    color: var(--gray-500);
}
.auth-login-link a {
    color: var(--ocean);
    font-weight: 700;
    text-decoration: none;
}
.auth-login-link a:hover { text-decoration: underline; }
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
    .auth-form .form-row { flex-direction: column; gap: 0; }
}
</style>

<main class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="<?php echo LOGO_URL; ?>" alt="<?php echo CHURCH_NAME; ?>">
            <h2>Join Our Family</h2>
            <p>Create your account today</p>
        </div>

        <?php if ($flash): ?>
            <div class="auth-alert <?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
                <i class="fas <?php echo $flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div id="registerAlert" class="auth-alert" style="display:none;"></div>

        <form class="auth-form" id="registerForm" onsubmit="return handleRegister(event)">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <div class="input-wrap">
                        <input type="text" id="first_name" name="first_name" placeholder="John" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <div class="input-wrap">
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="input-wrap">
                    <input type="tel" id="phone" name="phone" placeholder="+256 700 000000">
                    <i class="fas fa-phone"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password" oninput="checkPasswordStrength(this.value)">
                    <i class="fas fa-lock"></i>
                    <button type="button" class="toggle-pass" onclick="togglePassword(this)" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-requirements" id="passReqs">
                    <span id="req-length"><i class="fas fa-circle" style="font-size:6px;vertical-align:middle;"></i> At least 8 characters</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required minlength="8" autocomplete="new-password">
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <button type="submit" class="auth-btn" id="registerBtn">
                <span class="btn-text"><i class="fas fa-user-plus"></i> Create Account</span>
                <span class="spinner"></span>
            </button>
        </form>

        <div class="auth-divider">or</div>

        <p class="auth-login-link">
            Already have an account? <a href="login.php">Sign in</a>
        </p>
    </div>
</main>

<script>
function togglePassword(btn) {
    var input = btn.parentElement.querySelector('input');
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkPasswordStrength(pw) {
    var el = document.getElementById('req-length');
    if (pw.length >= 8) {
        el.innerHTML = '<i class="fas fa-check-circle" style="color:#16a34a;vertical-align:middle;"></i> At least 8 characters';
        el.classList.add('met');
    } else {
        el.innerHTML = '<i class="fas fa-circle" style="font-size:6px;vertical-align:middle;"></i> At least 8 characters';
        el.classList.remove('met');
    }
}

function showAuthAlert(type, message) {
    var el = document.getElementById('registerAlert');
    el.className = 'auth-alert ' + type;
    el.innerHTML = '<i class="fas ' + (type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i> ' + message;
    el.style.display = 'flex';
}

function handleRegister(e) {
    e.preventDefault();
    var btn = document.getElementById('registerBtn');
    var form = e.target;

    var pass = form.querySelector('#password').value;
    var confirm = form.querySelector('#confirm_password').value;

    if (pass.length < 8) {
        showAuthAlert('error', 'Password must be at least 8 characters long.');
        return false;
    }
    if (pass !== confirm) {
        showAuthAlert('error', 'Passwords do not match.');
        return false;
    }

    btn.classList.add('loading');
    btn.disabled = true;
    document.getElementById('registerAlert').style.display = 'none';

    var formData = new FormData(form);
    var data = {};
    formData.forEach(function(v, k) { data[k] = v; });

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php?action=register', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.classList.remove('loading');
            btn.disabled = false;
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showAuthAlert('success', res.message || 'Account created!');
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 1200);
                } else {
                    showAuthAlert('error', res.message || 'Registration failed. Please try again.');
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
