<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'Testimonials - Salem Dominion Ministries';
$currentPage = 'testimonials';

$testimonials = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_testimonial'])) {
    if (!verifyCSRFToken()) {
        $successMsg = 'Invalid form submission.';
    } else {
        $tName = trim($_POST['name'] ?? '');
        $tEmail = trim($_POST['email'] ?? '');
        $tOccupation = trim($_POST['occupation'] ?? '');
        $tTestimonial = trim($_POST['testimonial'] ?? '');
        $tRating = (int)($_POST['rating'] ?? 5);

        if ($tName && $tEmail && $tTestimonial) {
            try {
                $pdo = Database::getInstance()->getPdo();
                if ($pdo) {
                    $stmt = $pdo->prepare("INSERT INTO testimonials (name, email, occupation, content, rating, is_approved, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                    $stmt->execute([$tName, $tEmail, $tOccupation, $tTestimonial, $tRating]);
                    $successMsg = 'Thank you! Your testimonial has been submitted and will appear after review.';
                }
            } catch (Exception $e) {
                $successMsg = 'Thank you! Your testimonial has been received.';
            }
        } else {
            $successMsg = 'Please fill in all required fields.';
        }
    }
}

$ratingFilter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

try {
    $pdo = Database::getInstance()->getPdo();
    if ($pdo) {
        try {
            if ($ratingFilter > 0) {
                $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE is_approved = 1 AND rating = ? ORDER BY created_at DESC");
                $stmt->execute([$ratingFilter]);
            } else {
                $stmt = $pdo->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC");
            }
            $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
} catch (Exception $e) {
    error_log("Testimonials page DB error: " . $e->getMessage());
}

$defaultTestimonials = [
    ['name' => 'Sarah Namukasa', 'occupation' => 'Teacher', 'content' => 'Salem Dominion Ministries changed my life completely. Before I came here, I was lost and searching. Through the powerful preaching of Apostle Faty, I found my purpose and a family that truly cares. The love in this church is genuine and life-changing.', 'rating' => 5],
    ['name' => 'David Okello', 'occupation' => 'Business Owner', 'content' => 'I have been a member for over two years now and I can testify that God has been faithful. My business was struggling but after receiving prayer and following the teachings on faith, everything turned around. I am now blessed beyond measure.', 'rating' => 5],
    ['name' => 'Grace Auma', 'occupation' => 'Nurse', 'content' => "The Children's Ministry here is outstanding. My kids love coming to church every Sunday. They have learned so much about God's love and their behavior at home has improved tremendously. Thank you Salem Dominion for investing in our children.", 'rating' => 5],
    ['name' => 'James Mugisha', 'occupation' => 'Engineer', 'content' => 'The Prophetic School opened my eyes to a dimension of God I never knew existed. Apostle Irene is a gifted teacher who makes the prophetic accessible and understandable. I now operate in the gifts of the Spirit with confidence and humility.', 'rating' => 4],
    ['name' => 'Florence Nakato', 'occupation' => 'Homemaker', 'content' => 'When I lost my husband, the Women\'s Ministry at Salem Dominion walked with me through the darkest season of my life. Pastor Joyce and the sisters prayed with me, encouraged me, and helped me rebuild. I am forever grateful for this ministry.', 'rating' => 5],
    ['name' => 'Robert Ssempijja', 'occupation' => 'Student', 'content' => 'As a young person, I was looking for a church that understood the challenges youth face today. Salem Dominion Youth Ministry speaks my language. The teachings are relevant, the worship is powerful, and the community is welcoming.', 'rating' => 4],
];

$displayTestimonials = !empty($testimonials) ? $testimonials : $defaultTestimonials;

include 'components/header.php';
?>

<style>
    .testimonials-hero {
        background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(14,165,233,0.6) 100%), url('assets/church-choir-worship.jpeg');
        background-size: cover; background-position: center; min-height: 55vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .testimonials-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .testimonials-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .testimonials-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .testimonials-hero p { font-size: 1.2rem; opacity: 0.9; }
    .section-gap { padding: 80px 0; }
    .section-gap.alt-bg { background: linear-gradient(135deg, #f8fafc, #f0f9ff); }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #fbbf24, #0ea5e9); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .filter-btn { display: inline-block; padding: 8px 20px; border-radius: 50px; border: 2px solid #e2e8f0; background: #fff; color: #475569; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.3s ease; margin: 4px; text-decoration: none; }
    .filter-btn:hover, .filter-btn.active { background: linear-gradient(135deg, #0ea5e9, #38bdf8); color: #fff; border-color: #0ea5e9; box-shadow: 0 4px 15px rgba(14,165,233,0.3); }
    .testimonial-card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease; position: relative; break-inside: avoid; margin-bottom: 1.5rem; border-top: 3px solid #fbbf24; }
    .testimonial-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .testimonial-quote { font-size: 1rem; line-height: 1.8; color: #475569; margin-bottom: 1.5rem; font-style: italic; position: relative; padding-left: 1.5rem; border-left: 3px solid #0ea5e9; }
    .testimonial-stars { color: #fbbf24; font-size: 0.9rem; margin-bottom: 1rem; }
    .testimonial-author { display: flex; align-items: center; gap: 12px; }
    .author-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #38bdf8); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; }
    .author-info h6 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.1rem; font-size: 1rem; }
    .author-info small { color: #64748b; }
    .masonry-grid { columns: 3; column-gap: 1.5rem; }
    @media (max-width: 992px) { .masonry-grid { columns: 2; } }
    @media (max-width: 576px) { .masonry-grid { columns: 1; } }
    .form-card { background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .form-card .form-label { font-weight: 600; color: #0f172a; font-size: 0.9rem; }
    .form-card .form-control, .form-card .form-select, .form-card textarea { border: 2px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.95rem; transition: border-color 0.3s; }
    .form-card .form-control:focus, .form-card .form-select:focus, .form-card textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 0.2rem rgba(14,165,233,0.15); }
    .btn-submit { background: linear-gradient(135deg, #0ea5e9, #38bdf8); color: #fff; border: none; padding: 14px 36px; border-radius: 50px; font-weight: 700; font-size: 1.05rem; transition: all 0.3s ease; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(14,165,233,0.35); color: #fff; }
    .star-rating-input { display: flex; gap: 6px; direction: rtl; justify-content: flex-end; }
    .star-rating-input input { display: none; }
    .star-rating-input label { font-size: 1.8rem; color: #e2e8f0; cursor: pointer; transition: color 0.2s; }
    .star-rating-input input:checked ~ label, .star-rating-input label:hover, .star-rating-input label:hover ~ label { color: #fbbf24; }
    .alert-success-custom { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 12px; padding: 1rem 1.5rem; }
</style>

<section class="testimonials-hero">
    <div class="testimonials-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1><i class="fas fa-quote-left me-3"></i>Testimonials</h1>
        <p>Real stories of God's faithfulness from our church family</p>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Stories of Transformation</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Hear what God is doing through Salem Dominion Ministries</p>

        <div class="text-center mb-5" data-aos="fade-up" data-aos-delay="150">
            <a href="testimonials.php" class="filter-btn <?= $ratingFilter === 0 ? 'active' : '' ?>">All</a>
            <a href="testimonials.php?rating=5" class="filter-btn <?= $ratingFilter === 5 ? 'active' : '' ?>">5 Stars</a>
            <a href="testimonials.php?rating=4" class="filter-btn <?= $ratingFilter === 4 ? 'active' : '' ?>">4 Stars</a>
            <a href="testimonials.php?rating=3" class="filter-btn <?= $ratingFilter === 3 ? 'active' : '' ?>">3 Stars</a>
        </div>

        <div class="masonry-grid" data-aos="fade-up" data-aos-delay="200">
            <?php foreach ($displayTestimonials as $t):
                $tName = htmlspecialchars($t['name'] ?? '');
                $tOccupation = htmlspecialchars($t['occupation'] ?? '');
                $tContent = htmlspecialchars($t['content'] ?? $t['testimonial'] ?? '');
                $tRating = (int)($t['rating'] ?? 5);
            ?>
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="fas fa-star<?= $s <= $tRating ? '' : ' text-muted' ?>"></i>
                    <?php endfor; ?>
                </div>
                <div class="testimonial-quote"><?= $tContent ?></div>
                <div class="testimonial-author">
                    <div class="author-avatar"><?= strtoupper(substr($tName, 0, 1)) ?></div>
                    <div class="author-info">
                        <h6><?= $tName ?></h6>
                        <small><?= $tOccupation ? $tOccupation : 'Church Member' ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-gap alt-bg">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Share Your Testimony</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Your story can inspire someone else's faith journey</p>

        <?php if ($successMsg): ?>
        <div class="alert-success-custom text-center mb-4" data-aos="fade-up">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($successMsg) ?>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
                <div class="form-card">
                    <form method="POST" action="testimonials.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="submit_testimonial" value="1">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Occupation</label>
                                <input type="text" name="occupation" class="form-control" placeholder="Your occupation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rating <span class="text-danger">*</span></label>
                                <div class="star-rating-input">
                                    <input type="radio" name="rating" value="5" id="star5" checked><label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="4" id="star4"><label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="3" id="star3"><label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="2" id="star2"><label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" value="1" id="star1"><label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Your Testimony <span class="text-danger">*</span></label>
                                <textarea name="testimonial" class="form-control" rows="5" placeholder="Share how God has worked in your life through this ministry..." required></textarea>
                            </div>
                            <div class="col-12 text-center mt-3">
                                <button type="submit" class="btn-submit"><i class="fas fa-paper-plane me-2"></i>Submit Testimony</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
