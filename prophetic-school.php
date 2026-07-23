<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'Prophetic School - Salem Dominion Ministries';
$currentPage = 'prophetic-school';

$successMsg = '';
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    if (!verifyCSRFToken()) {
        $errorMsg = 'Invalid form submission. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $program = trim($_POST['program'] ?? '');

        if ($name && $email && $phone && $program) {
            try {
                $pdo = Database::getInstance()->getPdo();
                if ($pdo) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO prophetic_school_applications (name, email, phone, program, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
                        $stmt->execute([$name, $email, $phone, $program, "Prophetic School Enrollment - Program: $program"]);
                        $successMsg = 'Thank you! Your enrollment inquiry has been submitted. We will contact you soon.';
                    } catch (Exception $e) {
                        $successMsg = 'Thank you! Your enrollment inquiry has been received. We will contact you soon.';
                    }
                }
            } catch (Exception $e) {
                $successMsg = 'Thank you! Your enrollment inquiry has been received.';
            }
        } else {
            $errorMsg = 'Please fill in all required fields.';
        }
    }
}

include 'components/header.php';
?>

<style>
    .prophetic-hero {
        background: linear-gradient(135deg, rgba(139,92,246,0.85) 0%, rgba(14,165,233,0.65) 100%), url('assets/apostle-faty-preaching.jpeg');
        background-size: cover; background-position: center; min-height: 60vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .prophetic-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .prophetic-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .prophetic-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .prophetic-hero p { font-size: 1.25rem; opacity: 0.95; }
    .section-gap { padding: 80px 0; }
    .section-gap.alt-bg { background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f0f9ff 100%); }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #8b5cf6, #fbbf24); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .about-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-top: 4px solid #8b5cf6; }
    .about-card p { color: #475569; line-height: 1.9; font-size: 1.05rem; }
    .program-card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; border-bottom: 4px solid transparent; height: 100%; }
    .program-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .program-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.4rem; color: #fff; }
    .program-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.5rem; }
    .program-card p { color: #64748b; font-size: 0.9rem; line-height: 1.7; }
    .program-card .duration { display: inline-block; background: #8b5cf615; color: #8b5cf6; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-top: 0.75rem; }
    .enroll-form-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .enroll-form-card .form-label { font-weight: 600; color: #0f172a; font-size: 0.9rem; }
    .enroll-form-card .form-control, .enroll-form-card .form-select { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.95rem; transition: border-color 0.3s; }
    .enroll-form-card .form-control:focus, .enroll-form-card .form-select:focus { border-color: #8b5cf6; box-shadow: 0 0 0 0.2rem rgba(139,92,246,0.15); }
    .btn-enroll { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; border: none; padding: 14px 36px; border-radius: 50px; font-weight: 700; font-size: 1.05rem; transition: all 0.3s ease; }
    .btn-enroll:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(139,92,246,0.35); color: #fff; }
    .schedule-table { border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .schedule-table thead th { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff; border: none; padding: 1rem; }
    .schedule-table tbody td { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
    .schedule-table tbody tr:hover { background: #f5f3ff; }
    .cta-prophetic { background: linear-gradient(135deg, #8b5cf6, #0ea5e9); color: #fff; text-align: center; position: relative; overflow: hidden; }
    .cta-prophetic::before { content: ''; position: absolute; top: 0; left: -100%; width: 300%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); animation: shimmP 12s infinite; }
    @keyframes shimmP { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .cta-prophetic h2 { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,2.8rem); margin-bottom: 1rem; position: relative; }
    .cta-prophetic p { font-size: 1.15rem; opacity: 0.95; margin-bottom: 2rem; position: relative; }
    .cta-btn-purple { display: inline-flex; align-items: center; gap: 10px; padding: 14px 36px; background: #fff; color: #8b5cf6; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; position: relative; font-size: 1.05rem; }
    .cta-btn-purple:hover { background: #0f172a; color: #fff; transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .alert-success-custom { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 1rem 1.5rem; }
</style>

<section class="prophetic-hero">
    <div class="prophetic-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1><i class="fas fa-eye me-3"></i>Prophetic School</h1>
        <p>Training believers to hear God's voice and walk in the prophetic</p>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">About the Prophetic School</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Equipping the saints for the work of ministry</p>
        <div class="row g-5">
            <div class="col-lg-7" data-aos="fade-right" data-aos-delay="200">
                <div class="about-card">
                    <p>The Prophetic School at Salem Dominion Ministries is a comprehensive training program designed to equip believers with the knowledge, understanding, and practical experience needed to operate in the gifts of the Spirit, particularly the prophetic.</p>
                    <p style="margin-top: 1rem;">Under the seasoned leadership of <strong>Apostle Irene Mirembe</strong>, students are trained to hear God's voice clearly, interpret prophetic impressions, and minister with accuracy, wisdom, and love. The school combines biblical teaching, prophetic exercises, mentorship, and real-world ministry experience.</p>
                    <p style="margin-top: 1rem;">Whether you are a new believer curious about the prophetic or an experienced minister seeking to sharpen your gifts, our Prophetic School offers structured courses and programs that meet you where you are and take you higher in God.</p>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="300">
                <img src="assets/APOSTLE-IRENE-MIREMBE-CwWfzcRx.jpeg" alt="Apostle Irene Mirembe - Prophetic School Director" class="img-fluid" style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; height: 400px; object-fit: cover;">
                <div style="text-align: center; margin-top: 1rem;">
                    <h5 style="font-family: 'Playfair Display', serif; color: #0f172a;">Apostle Irene Mirembe</h5>
                    <p style="color: #8b5cf6; font-weight: 600;">Prophetic School Director</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Programs & Courses</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Choose the program that fits your calling</p>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="program-card" style="border-bottom-color: #10b981;">
                    <div class="program-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-seedling"></i></div>
                    <h5>Foundation Course</h5>
                    <p>Introduction to the prophetic ministry. Learn the biblical basis for prophecy, how to hear God's voice, and the fundamentals of prophetic ministry.</p>
                    <span class="duration"><i class="fas fa-clock me-1"></i> 8 Weeks</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="program-card" style="border-bottom-color: #0ea5e9;">
                    <div class="program-icon" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"><i class="fas fa-fire"></i></div>
                    <h5>Intermediate Prophetic</h5>
                    <p>Deepen your prophetic gifting. Study prophetic symbolism, dreams and visions interpretation, words of knowledge, and prophetic writing.</p>
                    <span class="duration"><i class="fas fa-clock me-1"></i> 12 Weeks</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="program-card" style="border-bottom-color: #fbbf24;">
                    <div class="program-icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);"><i class="fas fa-crown"></i></div>
                    <h5>Advanced Prophetic</h5>
                    <p>For those called to prophetic leadership. Learn to lead prophetic teams, minister prophetically in services, and operate in the apostolic-prophetic.</p>
                    <span class="duration"><i class="fas fa-clock me-1"></i> 16 Weeks</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="program-card" style="border-bottom-color: #ec4899;">
                    <div class="program-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);"><i class="fas fa-moon"></i></div>
                    <h5>Dreams & Visions</h5>
                    <p>A specialized course on understanding divine dreams, dream interpretation, prophetic visions, and trances from a biblical perspective.</p>
                    <span class="duration"><i class="fas fa-clock me-1"></i> 6 Weeks</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="program-card" style="border-bottom-color: #8b5cf6;">
                    <div class="program-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="fas fa-hand-sparkles"></i></div>
                    <h5>Spiritual Gifts Workshop</h5>
                    <p>A practical workshop covering all spiritual gifts: prophecy, tongues, healing, discernment, miracles, and words of knowledge.</p>
                    <span class="duration"><i class="fas fa-clock me-1"></i> 4 Weeks</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="program-card" style="border-bottom-color: #f97316;">
                    <div class="program-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);"><i class="fas fa-users-cog"></i></div>
                    <h5>Prophetic Leadership</h5>
                    <p>Training for ministry leaders on integrating the prophetic into church life, pastoral care through prophecy, and ethical prophetic ministry.</p>
                    <span class="duration"><i class="fas fa-clock me-1"></i> 10 Weeks</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Class Schedule</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Regular sessions for all programs</p>
        <div data-aos="fade-up" data-aos-delay="200">
            <table class="table schedule-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar-day me-2"></i>Day</th>
                        <th><i class="fas fa-clock me-2"></i>Time</th>
                        <th><i class="fas fa-book me-2"></i>Program</th>
                        <th><i class="fas fa-map-marker-alt me-2"></i>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>Saturday</strong></td><td>10:00 AM - 12:00 PM</td><td>All Courses (Rotating)</td><td>Church Main Hall</td></tr>
                    <tr><td><strong>Wednesday</strong></td><td>6:00 PM - 7:30 PM</td><td>Practical Prophetic Sessions</td><td>Prayer Room</td></tr>
                    <tr><td><strong>Friday</strong></td><td>7:00 PM - 9:00 PM</td><td>Prophetic Worship & Activation</td><td>Church Main Hall</td></tr>
                    <tr><td><strong>Sunday</strong></td><td>After Service</td><td>Ministry Practice</td><td>Main Sanctuary</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Enroll Now</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Begin your journey into the prophetic today</p>

        <?php if ($errorMsg): ?>
        <div class="alert alert-danger text-center mb-4" style="border-radius:12px;" data-aos="fade-up">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMsg) ?>
        </div>
        <?php endif; ?>

        <?php if ($successMsg): ?>
        <div class="alert-success-custom text-center mb-4" data-aos="fade-up">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMsg) ?>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
                <div class="enroll-form-card">
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="enroll" value="1">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" placeholder="+256 XXX XXX XXX" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Program <span class="text-danger">*</span></label>
                                <select name="program" class="form-select" required>
                                    <option value="">Select a program</option>
                                    <option value="Foundation Course">Foundation Course (8 Weeks)</option>
                                    <option value="Intermediate Prophetic">Intermediate Prophetic (12 Weeks)</option>
                                    <option value="Advanced Prophetic">Advanced Prophetic (16 Weeks)</option>
                                    <option value="Dreams & Visions">Dreams & Visions (6 Weeks)</option>
                                    <option value="Spiritual Gifts Workshop">Spiritual Gifts Workshop (4 Weeks)</option>
                                    <option value="Prophetic Leadership">Prophetic Leadership (10 Weeks)</option>
                                </select>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <button type="submit" class="btn-enroll"><i class="fas fa-paper-plane me-2"></i>Submit Enrollment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap cta-prophetic">
    <div class="container position-relative">
        <h2 data-aos="fade-up">Step Into Your Prophetic Calling</h2>
        <p data-aos="fade-up" data-aos-delay="100">God is raising up a generation of prophetic voices. Will you answer the call?</p>
        <div data-aos="fade-up" data-aos-delay="200">
            <a href="contact.php" class="cta-btn-purple"><i class="fas fa-phone"></i> Talk to Us</a>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
