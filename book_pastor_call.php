<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'Book a Pastor Call - Salem Dominion Ministries';
$currentPage = 'book_pastor_call';

$successMsg = '';
$errorMsg = '';
$availableSlots = [];

try {
    $pdo = Database::getInstance()->getPdo();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM pastor_booking_availability WHERE is_active = 1 ORDER BY day_of_week ASC, start_time ASC");
            $availableSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_call'])) {
            if (!verifyCSRFToken()) {
                $errorMsg = 'Invalid form submission. Please try again.';
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $date = trim($_POST['date'] ?? '');
                $time = trim($_POST['time'] ?? '');
                $type = trim($_POST['booking_type'] ?? '');
                $subject = trim($_POST['subject'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if ($name && $email && $phone && $date && $time && $type) {
                    try {
                        $pastor = $pdo->query("SELECT id FROM leadership WHERE title LIKE '%Pastor%' AND is_active = 1 LIMIT 1")->fetch();
                        $pastorId = $pastor ? $pastor['id'] : 1;
                        $endHour = intval(substr($time, 0, 2)) + 1;
                        $endTime = str_pad($endHour, 2, '0', STR_PAD_LEFT) . substr($time, 2);
                        $confirmCode = 'PASTOR-' . strtoupper(bin2hex(random_bytes(5))) . '-' . date('ymd');
                        $stmt = $pdo->prepare("INSERT INTO pastor_bookings (pastor_id, client_name, client_email, client_phone, booking_date, start_time, end_time, booking_type, subject, description, status, confirmation_code, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())");
                        $stmt->execute([$pastorId, $name, $email, $phone, $date, $time, $endTime, $type, $subject, $description, $confirmCode, $_SERVER['REMOTE_ADDR'] ?? '']);
                        $successMsg = 'Your appointment has been booked successfully! Confirmation code: ' . $confirmCode;
                    } catch (Exception $e) {
                        $successMsg = 'Your booking has been received. We will contact you to confirm your appointment.';
                    }
                } else {
                    $errorMsg = 'Please fill in all required fields.';
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Book Pastor Call page error: " . $e->getMessage());
}

if (empty($availableSlots)) {
    $availableSlots = [
        ['day_of_week' => 'Monday', 'start_time' => '09:00', 'end_time' => '12:00'],
        ['day_of_week' => 'Wednesday', 'start_time' => '14:00', 'end_time' => '17:00'],
        ['day_of_week' => 'Friday', 'start_time' => '09:00', 'end_time' => '12:00'],
        ['day_of_week' => 'Saturday', 'start_time' => '10:00', 'end_time' => '13:00'],
    ];
}

include 'components/header.php';
?>

<style>
    .booking-hero {
        background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(16,185,129,0.55) 100%), url('assets/pastor-faty-healing.jpeg');
        background-size: cover; background-position: center; min-height: 60vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .booking-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .booking-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .booking-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .booking-hero p { font-size: 1.25rem; opacity: 0.95; }
    .section-gap { padding: 80px 0; }
    .section-gap.alt-bg { background: linear-gradient(135deg, #f0fdf4, #ecfdf5, #f0f9ff); }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #10b981, #0ea5e9); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .about-booking-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-top: 4px solid #10b981; }
    .about-booking-card p { color: #475569; line-height: 1.9; font-size: 1.05rem; }
    .booking-type-card { background: #fff; border-radius: 16px; padding: 1.5rem; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; border-bottom: 3px solid transparent; height: 100%; }
    .booking-type-card:hover { transform: translateY(-5px); border-bottom-color: #10b981; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .booking-type-icon { width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.3rem; color: #fff; }
    .booking-type-card h6 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.4rem; font-size: 1rem; }
    .booking-type-card p { color: #64748b; font-size: 0.82rem; line-height: 1.5; margin: 0; }
    .booking-form-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .booking-form-card .form-label { font-weight: 600; color: #0f172a; font-size: 0.9rem; }
    .booking-form-card .form-control, .booking-form-card .form-select, .booking-form-card textarea { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.95rem; transition: border-color 0.3s; }
    .booking-form-card .form-control:focus, .booking-form-card .form-select:focus, .booking-form-card textarea:focus { border-color: #10b981; box-shadow: 0 0 0 0.2rem rgba(16,185,129,0.15); }
    .btn-book { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 14px 40px; border-radius: 50px; font-weight: 700; font-size: 1.1rem; transition: all 0.3s ease; }
    .btn-book:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16,185,129,0.35); color: #fff; }
    .slots-card { background: #fff; border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
    .slots-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 1rem; }
    .slot-item { display: flex; align-items: center; gap: 10px; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9; }
    .slot-item:last-child { border-bottom: none; }
    .slot-item i { color: #10b981; width: 20px; text-align: center; }
    .slot-item .day { font-weight: 600; color: #0f172a; min-width: 100px; }
    .slot-item .time { color: #64748b; font-size: 0.9rem; }
    .scripture-card { background: linear-gradient(135deg, #0f172a, #1e3a5f); border-radius: 20px; padding: 2.5rem; color: #fff; text-align: center; }
    .scripture-card blockquote { font-style: italic; line-height: 1.9; font-size: 1.15rem; color: rgba(255,255,255,0.9); }
    .scripture-card .ref { color: #fbbf24; font-weight: 600; margin-top: 1rem; font-size: 1rem; }
    .alert-success-custom { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 1rem 1.5rem; }
    .confirmation-section { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 2px solid #bbf7d0; border-radius: 20px; padding: 3rem; text-align: center; }
    .confirmation-section i { font-size: 4rem; color: #10b981; margin-bottom: 1rem; }
    .confirmation-section h3 { font-family: 'Playfair Display', serif; color: #166534; margin-bottom: 1rem; }
    .confirmation-section p { color: #166534; line-height: 1.7; }
</style>

<section class="booking-hero">
    <div class="booking-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1><i class="fas fa-phone-alt me-3"></i>Book a Pastor Call</h1>
        <p>Schedule a personal consultation with our pastoral team</p>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Pastoral Consultation</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">We are here to listen, pray, and guide you</p>
        <div class="row align-items-center g-5">
            <div class="col-lg-7" data-aos="fade-right" data-aos-delay="200">
                <div class="about-booking-card">
                    <p>At Salem Dominion Ministries, we believe in the power of personal pastoral care. Our pastors are available to provide spiritual guidance, prayer, counseling, and support for whatever season of life you are in.</p>
                    <p style="margin-top: 1rem;">Whether you need prayer for healing, deliverance, prophetic direction, marriage counseling, or simply someone to talk to - our pastoral team is ready to serve you with compassion, confidentiality, and the love of Christ.</p>
                    <p style="margin-top: 1rem;">Apostle Faty Musasizi and the pastoral team are committed to walking alongside you through every challenge and celebration. Every consultation is treated with the utmost care and prayerful attention.</p>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="300">
                <div class="slots-card">
                    <h5><i class="fas fa-clock me-2 text-success"></i>Available Time Slots</h5>
                    <?php foreach ($availableSlots as $slot): ?>
                    <div class="slot-item">
                        <i class="fas fa-calendar-day"></i>
                        <span class="day"><?= htmlspecialchars($slot['day_of_week'] ?? '') ?></span>
                        <span class="time"><?= htmlspecialchars(date('g:i A', strtotime($slot['start_time'] ?? ''))) ?> - <?= htmlspecialchars(date('g:i A', strtotime($slot['end_time'] ?? ''))) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Booking Types</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Choose the type of consultation that fits your need</p>
        <div class="row g-3">
            <?php
            $bookingTypes = [
                ['icon' => 'fa-comments', 'name' => 'General', 'desc' => 'General spiritual guidance and pastoral care', 'color' => '#0ea5e9'],
                ['icon' => 'fa-heart-crack', 'name' => 'Counseling', 'desc' => 'Marriage, family, and personal counseling', 'color' => '#ec4899'],
                ['icon' => 'fa-hands-praying', 'name' => 'Prayer', 'desc' => 'Dedicated prayer and intercession session', 'color' => '#8b5cf6'],
                ['icon' => 'fa-shield-halved', 'name' => 'Deliverance', 'desc' => 'Spiritual freedom and deliverance ministry', 'color' => '#ef4444'],
                ['icon' => 'fa-hand-holding-medical', 'name' => 'Healing', 'desc' => 'Prayer for physical, emotional, and spiritual healing', 'color' => '#10b981'],
                ['icon' => 'fa-eye', 'name' => 'Prophecy', 'desc' => 'Prophetic word and divine direction', 'color' => '#fbbf24'],
            ];
            foreach ($bookingTypes as $i => $bt):
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 80 ?>">
                <div class="booking-type-card">
                    <div class="booking-type-icon" style="background: linear-gradient(135deg, <?= $bt['color'] ?>, <?= $bt['color'] ?>cc);"><i class="fas <?= $bt['icon'] ?>"></i></div>
                    <h6><?= htmlspecialchars($bt['name']) ?></h6>
                    <p><?= htmlspecialchars($bt['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Book Your Appointment</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Fill out the form below and we will confirm your appointment</p>

        <?php if ($errorMsg): ?>
        <div class="alert alert-danger text-center mb-4" style="border-radius:12px;" data-aos="fade-up">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($errorMsg) ?>
        </div>
        <?php endif; ?>

        <?php if ($successMsg): ?>
        <div class="confirmation-section mb-5" data-aos="fade-up">
            <i class="fas fa-check-circle"></i>
            <h3>Booking Received!</h3>
            <p><?= htmlspecialchars($successMsg) ?></p>
            <p style="margin-top: 1rem;"><strong>What happens next?</strong></p>
            <p>Our pastoral team will review your request and contact you within 24 hours to confirm your appointment. Please keep your phone accessible.</p>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
                <div class="booking-form-card">
                    <form method="POST" action="book_pastor_call.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="book_call" value="1">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Your full name" required>
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
                                <label class="form-label">Booking Type <span class="text-danger">*</span></label>
                                <select name="booking_type" class="form-select" required>
                                    <option value="">Select type</option>
                                    <option value="general">General Consultation</option>
                                    <option value="counseling">Counseling</option>
                                    <option value="prayer">Prayer Session</option>
                                    <option value="deliverance">Deliverance</option>
                                    <option value="healing">Healing Prayer</option>
                                    <option value="prophecy">Prophetic Direction</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Preferred Time <span class="text-danger">*</span></label>
                                <select name="time" class="form-select" required>
                                    <option value="">Select time</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="Brief subject of your visit">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Tell us more about what you'd like to discuss or pray about..."></textarea>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <button type="submit" class="btn-book"><i class="fas fa-calendar-check me-2"></i>Book Appointment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-gap" style="background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff;">
    <div class="container">
        <div class="scripture-card" data-aos="fade-up">
            <i class="fas fa-book-bible" style="font-size: 2rem; color: #fbbf24; margin-bottom: 1rem;"></i>
            <blockquote>"Is any one of you sick? He should call the elders of the church to pray over him and anoint him with oil in the name of the Lord. And the prayer offered in faith will make the sick person well; the Lord will raise him up."</blockquote>
            <p class="ref">- James 5:14-15 (NIV)</p>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
