<?php
$pageTitle = 'Contact Us | Salem Dominion Ministries';
$currentPage = 'contact';
$pageDescription = 'Get in touch with Salem Dominion Ministries. Send us a message, prayer request, or visit us at Nampirika, Iganga District, Uganda.';

require_once 'config.php';
require_once 'db_connection.php';

$pdo = Database::getInstance()->getPdo();

$errors = [];
$success = '';
$prayer_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken()) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $form_type = $_POST['form_type'] ?? 'contact';
        if ($form_type === 'prayer') {
            $p_name = trim($_POST['p_name'] ?? '');
            $p_email = trim($_POST['p_email'] ?? '');
            $p_phone = trim($_POST['p_phone'] ?? '');
            $p_request = trim($_POST['p_request'] ?? '');

            if (empty($p_name)) $errors[] = 'Name is required for prayer request.';
            if (empty($p_request)) $errors[] = 'Prayer request message is required.';

            if (empty($errors)) {
                try {
                    if ($pdo) {
                        $stmt = $pdo->prepare("INSERT INTO prayer_requests (name, email, phone, request_text, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
                        if ($stmt) {
                            $stmt->execute([$p_name, $p_email, $p_phone, $p_request]);
                            $prayer_success = 'Your prayer request has been received. Our team will be praying for you.';
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = 'Failed to submit prayer request. Please try again.';
                    error_log("Prayer request error: " . $e->getMessage());
                }
            }
        } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (empty($name)) $errors[] = 'Name is required.';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
            if (empty($subject)) $errors[] = 'Subject is required.';
            if (empty($message)) $errors[] = 'Message is required.';

            if (empty($errors)) {
                try {
                    if ($pdo) {
                        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'unread', NOW())");
                        if ($stmt) {
                            $stmt->execute([$name, $email, $phone, $subject, $message]);
                            $success = 'Thank you for contacting us! We will get back to you soon.';
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = 'Failed to send message. Please try again.';
                    error_log("Contact form error: " . $e->getMessage());
                }
            }
        }
    }
}

include 'components/header.php';
?>

<style>
.contact-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(14,165,233,0.75)), url('assets/hero-worship-CWyaH0tr.jpg') center/cover no-repeat;
    padding: 100px 0 60px;
    color: #fff;
    text-align: center;
}
.contact-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; }
.contact-hero p { font-family: 'Montserrat', sans-serif; font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 15px auto 0; }
.contact-hero .scripture { font-style: italic; opacity: 0.8; margin-top: 20px; font-size: 0.95rem; }
.contact-hero .scripture strong { color: #fbbf24; }

.contact-info-card { background: #fff; border-radius: 16px; padding: 30px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.4s ease; height: 100%; border: 2px solid transparent; }
.contact-info-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); border-color: rgba(14,165,233,0.3); }
.contact-info-card .icon-wrap { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.5rem; }
.contact-info-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 8px; }
.contact-info-card p { color: #64748b; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; margin: 0; line-height: 1.6; }
.contact-info-card a { text-decoration: none; transition: color 0.3s; }

.form-card { background: #fff; border-radius: 20px; padding: 35px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
.form-card h3 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 25px; }
.form-card .form-control, .form-card .form-select { border-radius: 10px; border: 2px solid #e2e8f0; padding: 12px 16px; font-family: 'Montserrat', sans-serif; transition: all 0.3s; }
.form-card .form-control:focus, .form-card .form-select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
.form-card textarea.form-control { min-height: 120px; resize: vertical; }
.form-card .form-label { font-weight: 600; color: #334155; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; }

.btn-gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 600; border: none; border-radius: 10px; padding: 12px 28px; font-family: 'Montserrat', sans-serif; }
.btn-gold:hover { background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; transform: translateY(-2px); box-shadow: 0 5px 20px rgba(251,191,36,0.3); }
.btn-blue { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; font-weight: 600; border: none; border-radius: 10px; padding: 12px 28px; font-family: 'Montserrat', sans-serif; }
.btn-blue:hover { background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; transform: translateY(-2px); }

.map-wrap { border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 4px solid #fff; }
.map-wrap iframe { width: 100%; height: 400px; border: none; display: block; border-radius: 16px; }
@media(max-width:768px) { .map-wrap iframe { height: 250px; } }

.prayer-card { background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 20px; padding: 35px; color: #fff; }
.prayer-card h3 { font-family: 'Playfair Display', serif; color: #fbbf24; margin-bottom: 20px; }
.prayer-card .form-control { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 10px; padding: 12px 16px; font-family: 'Montserrat', sans-serif; }
.prayer-card .form-control::placeholder { color: rgba(255,255,255,0.5); }
.prayer-card .form-control:focus { background: rgba(255,255,255,0.15); border-color: #fbbf24; box-shadow: 0 0 0 3px rgba(251,191,36,0.2); color: #fff; }
.prayer-card .form-label { color: #cbd5e1; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; }
.prayer-card textarea.form-control { min-height: 100px; resize: vertical; }

.social-links-wrap { display: flex; gap: 12px; flex-wrap: wrap; }
.social-link-btn { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; text-decoration: none; transition: all 0.3s ease; }
.social-link-btn:hover { transform: translateY(-4px); color: #fff; }

.office-hours { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.office-hours h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 15px; }
.office-hours .hours-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; color: #475569; }
.office-hours .hours-item:last-child { border-bottom: none; }

@media(max-width:768px) { .contact-hero h1 { font-size: 2rem; } .form-card, .prayer-card { padding: 25px; } }
</style>

<section class="contact-hero" data-aos="fade-in">
    <div class="container">
        <h1 data-aos="fade-up">Contact Us</h1>
        <p data-aos="fade-up" data-delay="100">We would love to hear from you. Reach out to us anytime.</p>
        <div class="scripture" data-aos="fade-up" data-delay="200">
            <i class="fas fa-hands me-2"></i>
            "Come to me, all you who are weary and burdened." &mdash; <strong>Matthew 11:28</strong>
        </div>
    </div>
</section>

<section style="padding: 60px 0;">
    <div class="container">

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" data-aos="fade-up" style="border-radius:12px;">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php foreach ($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" data-aos="fade-up" style="border-radius:12px;">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($prayer_success): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" data-aos="fade-up" style="border-radius:12px;background:rgba(16,185,129,0.1);border-color:rgba(16,185,129,0.3);color:#065f46;">
            <i class="fas fa-hands-praying me-2"></i><?= htmlspecialchars($prayer_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-delay="0">
                <div class="contact-info-card">
                    <div class="icon-wrap" style="background:rgba(14,165,233,0.1);color:#0ea5e9;"><i class="fas fa-map-marker-alt"></i></div>
                    <h5>Address</h5>
                    <p><?= CHURCH_ADDRESS ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-delay="100">
                <div class="contact-info-card">
                    <div class="icon-wrap" style="background:rgba(251,191,36,0.15);color:#d97706;"><i class="fas fa-phone"></i></div>
                    <h5>Phone</h5>
                    <p><a href="tel:<?= CHURCH_PHONE ?>" style="color:#64748b;"><?= CHURCH_PHONE ?></a></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-delay="200">
                <div class="contact-info-card">
                    <div class="icon-wrap" style="background:rgba(14,165,233,0.1);color:#0ea5e9;"><i class="fas fa-envelope"></i></div>
                    <h5>Email</h5>
                    <p><a href="mailto:<?= CHURCH_EMAIL ?>" style="color:#64748b;"><?= CHURCH_EMAIL ?></a></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-delay="300">
                <div class="contact-info-card">
                    <div class="icon-wrap" style="background:rgba(251,191,36,0.15);color:#d97706;"><i class="fas fa-clock"></i></div>
                    <h5>Service Times</h5>
                    <p>Sunday 9:00 AM - 12:00 PM</p>
                </div>
            </div>
        </div>

        <div class="row g-5 mb-5">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="form-card">
                    <h3><i class="fas fa-paper-plane me-2 text-primary"></i>Send Us a Message</h3>
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="form_type" value="contact">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="Your name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+256 XXX XXX XXX" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject *</label>
                                <select name="subject" class="form-select" required>
                                    <option value="">Select subject</option>
                                    <option value="General Inquiry" <?= ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                    <option value="Service Information" <?= ($_POST['subject'] ?? '') === 'Service Information' ? 'selected' : '' ?>>Service Information</option>
                                    <option value="Ministry Involvement" <?= ($_POST['subject'] ?? '') === 'Ministry Involvement' ? 'selected' : '' ?>>Ministry Involvement</option>
                                    <option value="Event Details" <?= ($_POST['subject'] ?? '') === 'Event Details' ? 'selected' : '' ?>>Event Details</option>
                                    <option value="Partnership" <?= ($_POST['subject'] ?? '') === 'Partnership' ? 'selected' : '' ?>>Partnership</option>
                                    <option value="Other" <?= ($_POST['subject'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" placeholder="Write your message here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-gold"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5" data-aos="fade-left">
                <div class="office-hours mb-4">
                    <h5><i class="fas fa-clock me-2 text-primary"></i>Office Hours</h5>
                    <div class="hours-item"><span>Monday - Friday</span><span class="fw-semibold">8:00 AM - 5:00 PM</span></div>
                    <div class="hours-item"><span>Saturday</span><span class="fw-semibold">9:00 AM - 1:00 PM</span></div>
                    <div class="hours-item"><span>Sunday</span><span class="fw-semibold text-primary">9:00 AM - 12:00 PM</span></div>
                    <div class="hours-item"><span>Public Holidays</span><span class="fw-semibold text-muted">Closed</span></div>
                </div>

                <div class="mb-4" data-aos="fade-left" data-delay="100">
                    <h5 style="font-family:'Playfair Display',serif;color:#0f172a;margin-bottom:15px;"><i class="fas fa-share-alt me-2 text-primary"></i>Connect With Us</h5>
                    <div class="social-links-wrap">
                        <a href="<?= FACEBOOK_URL ?>" target="_blank" class="social-link-btn" style="background:linear-gradient(135deg,#1877f2,#0d65d9);"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= YOUTUBE_URL ?>" target="_blank" class="social-link-btn" style="background:linear-gradient(135deg,#ff0000,#cc0000);"><i class="fab fa-youtube"></i></a>
                        <a href="<?= TIKTOK_URL ?>" target="_blank" class="social-link-btn" style="background:linear-gradient(135deg,#010101,#333);"><i class="fab fa-tiktok"></i></a>
                        <a href="<?= WHATSAPP_URL ?>" target="_blank" class="social-link-btn" style="background:linear-gradient(135deg,#25d366,#128c7e);"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <div data-aos="fade-left" data-delay="200">
                    <a href="<?= WHATSAPP_URL ?>" target="_blank" class="btn btn-gold w-100 mb-3" style="border-radius:12px;">
                        <i class="fab fa-whatsapp me-2" style="font-size:1.2rem;"></i>Chat on WhatsApp
                    </a>
                    <a href="tel:<?= CHURCH_PHONE ?>" class="btn btn-blue w-100" style="border-radius:12px;">
                        <i class="fas fa-phone me-2"></i>Call Us Now
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-5 mb-5">
            <div class="col-lg-8" data-aos="fade-up">
                <h3 style="font-family:'Playfair Display',serif;color:#0f172a;margin-bottom:20px;"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Find Us</h3>
                <div class="map-wrap">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63371.27845397887!2d33.48!3d0.61!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x177db5e4b26e3b1b%3A0x1!2sIganga%2C+Uganda!5e0!3m2!1sen!2sug!4v1700000000000" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <div class="col-lg-4" data-aos="fade-left">
                <div class="prayer-card">
                    <h3><i class="fas fa-hands-praying me-2"></i>Prayer Request</h3>
                    <p style="color:#94a3b8;font-size:0.9rem;margin-bottom:20px;">Share your prayer needs with us. We believe in the power of prayer.</p>
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="form_type" value="prayer">
                        <div class="mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="p_name" class="form-control" placeholder="Name" required value="<?= htmlspecialchars($_POST['p_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email (Optional)</label>
                            <input type="email" name="p_email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($_POST['p_email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone (Optional)</label>
                            <input type="tel" name="p_phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($_POST['p_phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prayer Request *</label>
                            <textarea name="p_request" class="form-control" placeholder="Share your prayer request..." required><?= htmlspecialchars($_POST['p_request'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-gold w-100" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);"><i class="fas fa-hands-praying me-2"></i>Submit Prayer Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
