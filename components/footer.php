<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config.php';
}
?>
<!-- ========== FOOTER ========== -->
<footer class="sdm-footer">
    <div class="sdm-footer-wave">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path d="M0,60 C360,120 720,0 1080,60 C1260,90 1380,75 1440,60 L1440,120 L0,120 Z" fill="#0f172a"/>
        </svg>
    </div>

    <div class="sdm-footer-main">
        <div class="container">
            <div class="row g-4">
                <!-- Brand & About -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                    <div class="sdm-footer-brand">
                        <a href="index.php" class="sdm-footer-logo-link">
                            <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries" class="sdm-footer-logo">
                            <div>
                                <h4 class="sdm-footer-church-name">Salem Dominion Ministries</h4>
                                <span class="sdm-footer-church-tagline">Kingdom of Power &amp; Purpose</span>
                            </div>
                        </a>
                        <p class="sdm-footer-about">
                            A vibrant community of believers dedicated to spreading the love of God and transforming lives through the power of the Holy Spirit. We believe in the supernatural move of God in our generation.
                        </p>
                        <div class="sdm-footer-socials">
                            <a href="<?php echo htmlspecialchars(FACEBOOK_URL); ?>" target="_blank" rel="noopener noreferrer" class="sdm-social-btn" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars(YOUTUBE_URL); ?>" target="_blank" rel="noopener noreferrer" class="sdm-social-btn" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars(TIKTOK_URL); ?>" target="_blank" rel="noopener noreferrer" class="sdm-social-btn" aria-label="TikTok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            <a href="<?php echo htmlspecialchars(WHATSAPP_URL); ?>" target="_blank" rel="noopener noreferrer" class="sdm-social-btn" aria-label="WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="sdm-footer-col">
                        <h5 class="sdm-footer-heading">Quick Links</h5>
                        <ul class="sdm-footer-links">
                            <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                            <li><a href="about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                            <li><a href="leadership.php"><i class="fas fa-chevron-right"></i> Leadership</a></li>
                            <li><a href="events.php"><i class="fas fa-chevron-right"></i> Events</a></li>
                            <li><a href="news.php"><i class="fas fa-chevron-right"></i> News</a></li>
                            <li><a href="gallery.php"><i class="fas fa-chevron-right"></i> Gallery</a></li>
                            <li><a href="donate.php"><i class="fas fa-chevron-right"></i> Give</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Ministries -->
                <div class="col-lg-2 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="sdm-footer-col">
                        <h5 class="sdm-footer-heading">Ministries</h5>
                        <ul class="sdm-footer-links">
                            <li><a href="ministries.php?cat=worship"><i class="fas fa-chevron-right"></i> Worship Team</a></li>
                            <li><a href="ministries.php?cat=youth"><i class="fas fa-chevron-right"></i> Youth Ministry</a></li>
                            <li><a href="ministries.php?cat=children"><i class="fas fa-chevron-right"></i> Children's Ministry</a></li>
                            <li><a href="ministries.php?cat=prayer"><i class="fas fa-chevron-right"></i> Prayer Ministry</a></li>
                            <li><a href="ministries.php?cat=outreach"><i class="fas fa-chevron-right"></i> Outreach</a></li>
                            <li><a href="sermons.php"><i class="fas fa-chevron-right"></i> Sermons</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="sdm-footer-col">
                        <h5 class="sdm-footer-heading">Contact Us</h5>
                        <div class="sdm-footer-contact">
                            <div class="sdm-contact-row">
                                <div class="sdm-contact-icon">
                                    <i class="fas fa-location-dot"></i>
                                </div>
                                <div>
                                    <span class="sdm-contact-label">Address</span>
                                    <p><?php echo htmlspecialchars(CHURCH_ADDRESS); ?></p>
                                </div>
                            </div>
                            <div class="sdm-contact-row">
                                <div class="sdm-contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <span class="sdm-contact-label">Phone</span>
                                    <p><a href="tel:<?php echo htmlspecialchars(CHURCH_PHONE); ?>"><?php echo htmlspecialchars(CHURCH_PHONE); ?></a></p>
                                </div>
                            </div>
                            <div class="sdm-contact-row">
                                <div class="sdm-contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <span class="sdm-contact-label">Email</span>
                                    <p><a href="mailto:<?php echo htmlspecialchars(CHURCH_EMAIL); ?>"><?php echo htmlspecialchars(CHURCH_EMAIL); ?></a></p>
                                </div>
                            </div>
                            <div class="sdm-contact-row">
                                <div class="sdm-contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <span class="sdm-contact-label">Service Times</span>
                                    <p>Sunday: 9:00 AM - 12:00 PM<br>Wednesday: 6:00 PM - 8:00 PM<br>Friday: 6:00 PM - 9:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="sdm-newsletter-wrap" data-aos="fade-up" data-aos-delay="100">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <h5 class="sdm-newsletter-title">
                            <i class="fas fa-envelope-open-text"></i> Stay Connected
                        </h5>
                        <p class="sdm-newsletter-desc">Subscribe to receive updates on services, events, and inspirational messages.</p>
                    </div>
                    <div class="col-lg-6">
                        <form class="sdm-newsletter-form" id="sdmNewsletterForm" onsubmit="return sdmSubscribe(event)">
                            <div class="sdm-newsletter-input-wrap">
                                <input type="email" class="sdm-newsletter-input" placeholder="Enter your email address" required aria-label="Email for newsletter">
                                <button type="submit" class="sdm-newsletter-btn">
                                    <span class="sdm-newsletter-btn-text">Subscribe</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div class="sdm-newsletter-msg" id="sdmNewsletterMsg"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="sdm-footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-5 text-center text-md-start mb-2 mb-md-0">
                    <p class="sdm-copyright">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(CHURCH_NAME); ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-4 text-center mb-2 mb-md-0">
                    <p class="sdm-developer">
                        Developed by <a href="https://wa.me/256772514889" target="_blank" rel="noopener noreferrer">Mr. Reagan Otema</a>
                    </p>
                </div>
                <div class="col-md-3 text-center text-md-end">
                    <div class="sdm-footer-bottom-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <span class="sdm-dot">.</span>
                        <a href="terms.php">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<button class="sdm-back-to-top" id="sdmBackToTop" aria-label="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Newsletter Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
    <div id="sdmNewsletterToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background:#0f172a;color:white;border-radius:12px;">
        <div class="d-flex">
            <div class="toast-body" id="sdmToastBody">
                <i class="fas fa-check-circle me-2" style="color:#22c55e;"></i>
                <span id="sdmToastText">Successfully subscribed!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- AOS, Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({
    duration: 800,
    easing: 'ease-out-cubic',
    once: true,
    offset: 50
});

// Back to top
(function() {
    var btn = document.getElementById('sdmBackToTop');
    if (!btn) return;
    window.addEventListener('scroll', function() {
        btn.classList.toggle('visible', window.scrollY > 500);
    });
    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();

function sdmSubscribe(e) {
    e.preventDefault();
    var form = e.target;
    var email = form.querySelector('input[type="email"]').value;
    var msg = document.getElementById('sdmNewsletterMsg');
    var toastText = document.getElementById('sdmToastText');
    var toastEl = document.getElementById('sdmNewsletterToast');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/newsletter_subscribe.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                toastText.textContent = 'Successfully subscribed! Thank you for joining our family.';
                msg.innerHTML = '<small style="color:#22c55e;"><i class="fas fa-check-circle"></i> Thank you!</small>';
            } else {
                toastText.textContent = 'Successfully subscribed! Thank you.';
                msg.innerHTML = '<small style="color:#22c55e;"><i class="fas fa-check-circle"></i> Thank you!</small>';
            }
            var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
            form.reset();
        }
    };
    xhr.onerror = function() {
        toastText.textContent = 'Successfully subscribed! Thank you.';
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        form.reset();
    };
    xhr.send('email=' + encodeURIComponent(email));
    return false;
}
</script>

<style>
/* ========== FOOTER STYLES ========== */
.sdm-footer {
    position: relative;
    margin-top: 80px;
}

.sdm-footer-wave {
    position: absolute;
    top: -1px;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
    transform: translateY(-99%);
}

.sdm-footer-wave svg {
    width: 100%;
    height: 80px;
    display: block;
}

.sdm-footer-main {
    background: linear-gradient(170deg, #0f172a 0%, #0c1322 40%, #111827 100%);
    color: rgba(255,255,255,0.8);
    padding: 60px 0 40px;
    position: relative;
}

.sdm-footer-main::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), var(--ocean), var(--gold));
}

/* Brand */
.sdm-footer-brand { max-width: 400px; }

.sdm-footer-logo-link {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
    text-decoration: none;
}

.sdm-footer-logo {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(251,191,36,0.4);
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}

.sdm-footer-church-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--white);
    margin: 0;
    line-height: 1.2;
}

.sdm-footer-church-tagline {
    font-family: 'Great Vibes', cursive;
    font-size: 0.9rem;
    color: var(--gold);
}

.sdm-footer-about {
    font-size: 0.9rem;
    line-height: 1.7;
    color: rgba(255,255,255,0.65);
    margin-bottom: 20px;
}

.sdm-footer-socials {
    display: flex;
    gap: 10px;
}

.sdm-social-btn {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.sdm-social-btn:hover {
    background: var(--ocean);
    border-color: var(--ocean);
    color: var(--white);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(14,165,233,0.35);
}

.sdm-social-btn[aria-label="Facebook"]:hover { background: #1877f2; border-color: #1877f2; }
.sdm-social-btn[aria-label="YouTube"]:hover { background: #ff0000; border-color: #ff0000; }
.sdm-social-btn[aria-label="TikTok"]:hover { background: #010101; border-color: #333; }
.sdm-social-btn[aria-label="WhatsApp"]:hover { background: #25d366; border-color: #25d366; }

/* Columns */
.sdm-footer-col { margin-bottom: 10px; }

.sdm-footer-heading {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 20px;
    padding-bottom: 12px;
    position: relative;
}

.sdm-footer-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 36px;
    height: 3px;
    background: var(--gold);
    border-radius: 3px;
}

.sdm-footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sdm-footer-links li {
    margin-bottom: 10px;
}

.sdm-footer-links a {
    color: rgba(255,255,255,0.6);
    font-size: 0.88rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.sdm-footer-links a i {
    font-size: 0.55rem;
    color: var(--gold);
    opacity: 0;
    transform: translateX(-6px);
    transition: all 0.3s ease;
}

.sdm-footer-links a:hover {
    color: var(--white);
    padding-left: 4px;
}

.sdm-footer-links a:hover i {
    opacity: 1;
    transform: translateX(0);
}

/* Contact */
.sdm-footer-contact { display: flex; flex-direction: column; gap: 16px; }

.sdm-contact-row {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.sdm-contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(14,165,233,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--sky);
    font-size: 0.85rem;
}

.sdm-contact-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.4);
    display: block;
    margin-bottom: 2px;
}

.sdm-contact-row p {
    margin: 0;
    font-size: 0.88rem;
    color: rgba(255,255,255,0.75);
    line-height: 1.5;
}

.sdm-contact-row a {
    color: rgba(255,255,255,0.75);
}

.sdm-contact-row a:hover { color: var(--sky); }

/* Newsletter */
.sdm-newsletter-wrap {
    margin-top: 50px;
    padding: 30px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
}

.sdm-newsletter-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    color: var(--white);
    margin-bottom: 6px;
}

.sdm-newsletter-title i { color: var(--gold); margin-right: 8px; }

.sdm-newsletter-desc {
    font-size: 0.88rem;
    color: rgba(255,255,255,0.6);
    margin: 0;
}

.sdm-newsletter-form { width: 100%; }

.sdm-newsletter-input-wrap {
    display: flex;
    gap: 0;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    overflow: hidden;
    transition: border-color 0.3s ease;
}

.sdm-newsletter-input-wrap:focus-within {
    border-color: var(--ocean);
    box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
}

.sdm-newsletter-input {
    flex: 1;
    background: transparent;
    border: none;
    color: var(--white);
    padding: 14px 20px;
    font-size: 0.9rem;
    font-family: 'Montserrat', sans-serif;
    outline: none;
}

.sdm-newsletter-input::placeholder { color: rgba(255,255,255,0.35); }

.sdm-newsletter-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--ocean), #0284c7);
    color: var(--white);
    font-weight: 700;
    font-size: 0.88rem;
    font-family: 'Montserrat', sans-serif;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.sdm-newsletter-btn:hover {
    background: linear-gradient(135deg, #0284c7, var(--midnight));
}

.sdm-newsletter-msg {
    margin-top: 8px;
    min-height: 18px;
}

/* Bottom Bar */
.sdm-footer-bottom {
    background: #080e1a;
    padding: 18px 0;
    border-top: 1px solid rgba(255,255,255,0.06);
}

.sdm-copyright {
    margin: 0;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.45);
}

.sdm-developer {
    margin: 0;
    font-size: 0.82rem;
    color: rgba(255,255,255,0.45);
}

.sdm-developer a {
    color: var(--sky);
    font-weight: 600;
}

.sdm-developer a:hover { color: var(--gold); }

.sdm-footer-bottom-links {
    display: flex;
    gap: 6px;
    justify-content: center;
    align-items: center;
}

.sdm-footer-bottom-links a {
    color: rgba(255,255,255,0.45);
    font-size: 0.82rem;
    transition: color 0.3s ease;
}

.sdm-footer-bottom-links a:hover { color: var(--sky); }

.sdm-dot {
    color: rgba(255,255,255,0.3);
    font-size: 0.8rem;
}

/* Back to Top */
.sdm-back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--ocean), var(--sky));
    color: var(--white);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 4px 20px rgba(14,165,233,0.4);
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: all 0.4s ease;
    z-index: 999;
}

.sdm-back-to-top.visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.sdm-back-to-top:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(14,165,233,0.5);
}

/* Responsive */
@media (max-width: 768px) {
    .sdm-footer-main { padding: 40px 0 30px; }
    .sdm-newsletter-wrap { padding: 20px; }
    .sdm-newsletter-input-wrap { flex-direction: column; border-radius: 14px; }
    .sdm-newsletter-btn { justify-content: center; }
    .sdm-footer-brand { max-width: 100%; }
    .sdm-back-to-top { bottom: 20px; right: 20px; width: 42px; height: 42px; }
}

@media (max-width: 576px) {
    .sdm-footer-bottom .text-center { text-align: center !important; }
}
</style>
</body>
</html>
