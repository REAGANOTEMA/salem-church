<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'About Us - Salem Dominion Ministries';
$currentPage = 'about';

$stats = ['ministries' => 6, 'members' => 500, 'events' => 50, 'years' => 4];
$leaders = [];
$testimonials = [];

try {
    $pdo = Database::getInstance()->getPdo();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as c FROM ministries WHERE is_active = 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['ministries'] = $row['c'] ?? 6;
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT COUNT(*) as c FROM users WHERE is_active = 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['members'] = $row['c'] ?? 500;
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT COUNT(*) as c FROM events WHERE status IN ('completed','upcoming')");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['events'] = $row['c'] ?? 50;
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT * FROM leadership WHERE position LIKE '%Senior Pastor%' OR position LIKE '%Apostle%' ORDER BY id ASC LIMIT 1");
            $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 3");
            $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
} catch (Exception $e) {
    error_log("About page DB error: " . $e->getMessage());
}

include 'components/header.php';
?>

<style>
    .about-hero {
        background: linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(14,165,233,0.6) 100%), url('assets/ourmembers.jpeg');
        background-size: cover; background-position: center; min-height: 60vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .about-hero::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px;
        background: linear-gradient(to top, #fff, transparent);
    }
    .about-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .about-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,4rem); font-weight: 900; margin-bottom: 1rem; }
    .about-hero p { font-size: 1.3rem; opacity: 0.9; font-weight: 300; }
    .section-gap { padding: 80px 0; }
    .section-gap.alt { background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%); }
    .section-title-custom {
        font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700;
        color: #0f172a; text-align: center; margin-bottom: 0.5rem;
    }
    .section-title-custom::after {
        content: ''; display: block; width: 80px; height: 4px;
        background: linear-gradient(90deg, #fbbf24, #0ea5e9); margin: 15px auto 0; border-radius: 2px;
    }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .story-card {
        background: #fff; border-radius: 20px; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        border-top: 4px solid #fbbf24;
    }
    .story-card p { line-height: 1.9; color: #334155; font-size: 1.05rem; }
    .vision-mission-card {
        background: #fff; border-radius: 20px; padding: 2.5rem; text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06); transition: transform 0.4s ease, box-shadow 0.4s ease;
        position: relative; overflow: hidden;
    }
    .vision-mission-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
    .vision-mission-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, #0ea5e9, #fbbf24);
    }
    .vm-icon {
        width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.5rem; font-size: 2rem; color: #fff;
    }
    .vm-icon.vision { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
    .vm-icon.mission { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .vm-icon.mission i, .vm-icon.vision i { color: #fff; }
    .vision-mission-card h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #0f172a; margin-bottom: 1rem; }
    .vision-mission-card p { line-height: 1.8; color: #475569; }
    .value-card {
        background: #fff; border-radius: 16px; padding: 2rem; text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.4s ease;
        border-bottom: 3px solid transparent; height: 100%;
    }
    .value-card:hover { transform: translateY(-5px); border-bottom-color: #fbbf24; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .value-icon {
        width: 65px; height: 65px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem; font-size: 1.5rem;
    }
    .value-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.75rem; }
    .value-card p { color: #64748b; font-size: 0.95rem; line-height: 1.7; }
    .pastor-section { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color: #fff; }
    .pastor-image-frame {
        position: relative; border-radius: 20px; overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3); max-width: 400px; margin: 0 auto;
    }
    .pastor-image-frame img { width: 100%; height: auto; display: block; }
    .pastor-image-frame::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 40%;
        background: linear-gradient(to top, rgba(15,23,42,0.8), transparent);
    }
    .pastor-info h3 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.5rem; }
    .pastor-info .pastor-role { color: #fbbf24; font-weight: 600; font-size: 1.1rem; margin-bottom: 1.5rem; }
    .pastor-info p { line-height: 1.9; color: rgba(255,255,255,0.85); }
    .timeline-item { position: relative; padding-left: 40px; padding-bottom: 2rem; }
    .timeline-item::before {
        content: ''; position: absolute; left: 12px; top: 0; bottom: 0; width: 2px; background: #e2e8f0;
    }
    .timeline-item:last-child::before { display: none; }
    .timeline-dot {
        position: absolute; left: 0; top: 5px; width: 26px; height: 26px;
        border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.7rem;
    }
    .timeline-item h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.25rem; }
    .timeline-item p { color: #64748b; margin: 0; }
    .cta-about {
        background: linear-gradient(135deg, #0f172a 0%, #0ea5e9 100%);
        color: #fff; text-align: center; position: relative; overflow: hidden;
    }
    .cta-about::before {
        content: ''; position: absolute; top: 0; left: -100%; width: 300%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
        animation: shimmerCta 12s infinite;
    }
    @keyframes shimmerCta { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .cta-about h2 { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); margin-bottom: 1rem; position: relative; }
    .cta-about p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; position: relative; }
    .cta-btn {
        display: inline-flex; align-items: center; gap: 10px; padding: 14px 36px;
        background: #fbbf24; color: #0f172a; border-radius: 50px; font-weight: 700;
        text-decoration: none; transition: all 0.3s ease; position: relative; font-size: 1.05rem;
    }
    .cta-btn:hover { background: #fff; color: #0f172a; transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .branch-card {
        background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04); border-left: 4px solid #fbbf24;
        transition: transform 0.3s ease;
    }
    .branch-card:hover { transform: translateX(5px); }
    .branch-card h6 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 0.25rem; }
    .branch-card small { color: #64748b; }
</style>

<!-- Hero -->
<section class="about-hero">
    <div class="about-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1>About Our Church</h1>
        <p>Our Journey of Faith, Love, and Divine Purpose</p>
    </div>
</section>

<!-- Church History -->
<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Our Story</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">From humble beginnings to a thriving ministry</p>
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                <div class="story-card">
                    <p>
                        On <strong>July 27th, 2022</strong>, Salem Dominion Ministries was born from a divine vision given to <strong>Apostle Faty Musasizi</strong>. What began in a simple grass-thatched house with just <strong>5 faithful members</strong> has blossomed into a vibrant spiritual family of over <strong>500 members</strong> both locally and across the globe.
                    </p>
                    <p>
                        Through unwavering faith, persistent prayer, and the generous hearts of God's people, we have witnessed miraculous growth that only God could orchestrate. From those early days of gathering under a grass roof, God has enabled us to acquire our own land and establish multiple branches across the region.
                    </p>
                    <p>
                        Every step of this journey testifies to God's faithfulness and the power of unity in Christ. We remain committed to our calling: <strong>"Spreading the Gospel, Transforming Lives"</strong> — touching hearts, healing families, and restoring communities through the power of the Holy Spirit.
                    </p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                <div class="story-card" style="padding: 0; overflow: hidden; border-top: none;">
                    <img src="assets/hat.jpeg" alt="Where Salem Dominion Ministries began" style="width: 100%; height: 420px; object-fit: cover;">
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-6">
                        <div class="branch-card">
                            <h6><i class="fas fa-map-marker-alt text-warning me-2"></i>Bulanga Branch</h6>
                            <small>Luuka District</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="branch-card">
                            <h6><i class="fas fa-map-marker-alt text-warning me-2"></i>Kaliro Branch</h6>
                            <small>Kaliro District Town</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="branch-card">
                            <h6><i class="fas fa-map-marker-alt text-warning me-2"></i>Idudi Branch</h6>
                            <small>Bugweri District</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="branch-card">
                            <h6><i class="fas fa-map-marker-alt text-warning me-2"></i>Main Campus</h6>
                            <small>Nampirika, Iganga</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision & Mission -->
<section class="section-gap alt">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Vision & Mission</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Guided by divine purpose</p>
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                <div class="vision-mission-card">
                    <div class="vm-icon vision"><i class="fas fa-eye"></i></div>
                    <h3>Our Vision</h3>
                    <p>To be a transformative ministry that empowers individuals to fulfill their God-given purpose through the power of the Holy Spirit</p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
                <div class="vision-mission-card">
                    <div class="vm-icon mission"><i class="fas fa-cross"></i></div>
                    <h3>Our Mission</h3>
                    <p>To spread the Gospel of Jesus Christ, nurture believers in faith, and serve our community with love and compassion</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Core Values</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">The pillars that guide everything we do</p>
        <div class="row g-4">
            <?php
            $values = [
                ['icon' => 'fa-book-bible', 'title' => 'Faith', 'desc' => 'We stand firmly on the Word of God, trusting in His promises and walking by faith, not by sight.', 'color' => '#0ea5e9'],
                ['icon' => 'fa-heart', 'title' => 'Love', 'desc' => 'We demonstrate the unconditional love of Christ through compassion, acceptance, and service to all.', 'color' => '#ef4444'],
                ['icon' => 'fa-star', 'title' => 'Excellence', 'desc' => 'We pursue excellence in all we do, honoring God with our best efforts in worship, service, and daily life.', 'color' => '#fbbf24'],
                ['icon' => 'fa-people-group', 'title' => 'Community', 'desc' => 'We build authentic relationships, creating a family where everyone belongs, grows, and thrives together.', 'color' => '#10b981'],
                ['icon' => 'fa-hands-praying', 'title' => 'Prayer', 'desc' => 'Prayer is the foundation of our ministry. We seek God first in all things through fervent and persistent prayer.', 'color' => '#8b5cf6'],
            ];
            foreach ($values as $i => $v):
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 100 ?>">
                <div class="value-card">
                    <div class="value-icon" style="background: <?= $v['color'] ?>20; color: <?= $v['color'] ?>;">
                        <i class="fas <?= $v['icon'] ?>"></i>
                    </div>
                    <h5><?= htmlspecialchars($v['title']) ?></h5>
                    <p><?= htmlspecialchars($v['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Our Pastor -->
<section class="section-gap pastor-section">
    <div class="container">
        <h2 class="section-title-custom text-white" data-aos="fade-up" style="color: #fff !important;">Our Senior Pastor</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100" style="color: rgba(255,255,255,0.7) !important;">Led by a shepherd after God's own heart</p>
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right" data-aos-delay="200">
                <div class="pastor-image-frame">
                    <img src="assets/apostle-faty-preaching.jpeg" alt="Apostle Faty Musasizi">
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left" data-aos-delay="300">
                <div class="pastor-info">
                    <h3>Apostle Faty Musasizi</h3>
                    <p class="pastor-role">Senior Pastor & Founder</p>
                    <p>Apostle Faty Musasizi is a passionate vessel of God called to minister the Gospel with power, authority, and compassion. With a deep love for God's people and an unwavering commitment to the Great Commission, Apostle Faty founded Salem Dominion Ministries in 2022 with a vision to transform lives and communities.</p>
                    <p style="margin-top: 1rem;">Her ministry is marked by profound prophetic insight, healing miracles, and a heart for the broken and hurting. Through her leadership, Salem Dominion Ministries has grown from a small gathering of five believers into a thriving multi-branch ministry impacting hundreds of lives across Uganda and beyond.</p>
                    <p style="margin-top: 1rem;">Under her apostolic leadership, the church has established branches in Bulanga, Kaliro, and Idudi, and continues to expand its reach through children's ministry, a prophetic school, community outreach, and digital ministry. She is a mother to many and a spiritual father to the nations.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline / Milestones -->
<section class="section-gap alt">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Our Journey</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Key milestones in the life of our ministry</p>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php
                $milestones = [
                    ['year' => 'July 2022', 'title' => 'Ministry Founded', 'desc' => 'Salem Dominion Ministries was established with 5 faithful members in a grass-thatched house.'],
                    ['year' => '2023', 'title' => 'Rapid Growth', 'desc' => 'The ministry grew to over 200 members, with worship services drawing believers from across the district.'],
                    ['year' => '2023', 'title' => 'Land Acquisition', 'desc' => 'By God\'s provision, the church acquired its own land for a permanent worship center.'],
                    ['year' => '2024', 'title' => 'Branch Expansion', 'desc' => 'Branches were established in Bulanga (Luuka), Kaliro, and Idudi (Bugweri) Districts.'],
                    ['year' => '2024', 'title' => 'Children & Youth Ministry', 'desc' => 'Launched dedicated children\'s ministry and prophetic school to disciple the next generation.'],
                    ['year' => '2025', 'title' => '500+ Members', 'desc' => 'The ministry family grew to over 500 members, with global online reach and impact.'],
                ];
                foreach ($milestones as $i => $m):
                ?>
                <div class="timeline-item" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 80 ?>">
                    <div class="timeline-dot"><i class="fas fa-check"></i></div>
                    <h5><?= htmlspecialchars($m['title']) ?> <span style="color: #fbbf24; font-size: 0.85rem; font-weight: 400;">— <?= htmlspecialchars($m['year']) ?></span></h5>
                    <p><?= htmlspecialchars($m['desc']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="section-gap" style="background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff;">
    <div class="container">
        <div class="row text-center g-4">
            <?php
            $statItems = [
                ['num' => $stats['ministries'], 'label' => 'Active Ministries', 'icon' => 'fa-hands-helping'],
                ['num' => $stats['members'], 'label' => 'Church Members', 'icon' => 'fa-users'],
                ['num' => $stats['events'], 'label' => 'Events Hosted', 'icon' => 'fa-calendar-check'],
                ['num' => $stats['years'], 'label' => 'Years of Ministry', 'icon' => 'fa-cross'],
            ];
            foreach ($statItems as $i => $s):
            ?>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i + 1) * 100 ?>">
                <div style="padding: 2rem;">
                    <i class="fas <?= $s['icon'] ?>" style="font-size: 2.5rem; color: #fbbf24; margin-bottom: 1rem; display: block;"></i>
                    <div style="font-size: 3rem; font-weight: 900; font-family: 'Playfair Display', serif; color: #fbbf24;"><?= (int)$s['num'] ?>+</div>
                    <div style="font-size: 1.1rem; opacity: 0.85; margin-top: 0.5rem;"><?= htmlspecialchars($s['label']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-gap cta-about">
    <div class="container position-relative">
        <h2 data-aos="fade-up">Join Our Church Family</h2>
        <p data-aos="fade-up" data-aos-delay="100">Whether you're seeking a spiritual home, need prayer, or want to grow in faith — you belong here.</p>
        <div data-aos="fade-up" data-aos-delay="200">
            <a href="contact.php" class="cta-btn me-3 mb-3"><i class="fas fa-envelope"></i> Get In Touch</a>
            <a href="donate.php" class="cta-btn mb-3" style="background: transparent; border: 2px solid #fff; color: #fff;"><i class="fas fa-heart"></i> Give Today</a>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
