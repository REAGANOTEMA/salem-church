<?php
/**
 * Salem Dominion Ministries - Homepage
 */
require_once __DIR__ . '/config.php';

$currentPage = 'home';
$pageTitle = 'Salem Dominion Ministries - Divine Worship Experience';

$admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$hero_images = [
    'assets/hero1.jpeg',
    'assets/hero2.jpeg',
    'assets/hero3.jpeg',
    'assets/hero4.jpeg',
    'assets/hero5.jpeg',
    'assets/hero-choir-6lo-hX_h.jpg',
    'assets/hero-community-CDAgPtPb.jpg',
    'assets/hero-worship-CWyaH0tr.jpg'
];

$sermons = [];
$events = [];
$news = [];
$ministries = [];
$gallery_images = [];
$testimonials = [];
$verse_text = 'For the kingdom of God is not a matter of talk but of power.';
$verse_ref = '1 Corinthians 4:20';
$stats = ['members' => 500, 'years' => 10, 'sermons' => 200, 'events' => 50];
$is_live = false;
$youtube_channel = YOUTUBE_URL;

$pdo = null;
try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    try {
        $stmt = $pdo->query("SELECT * FROM sermons WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        $sermons = $stmt->fetchAll();
    } catch (Exception $e) { }

    try {
        $stmt = $pdo->query("SELECT * FROM events WHERE status = 'upcoming' ORDER BY event_date ASC LIMIT 3");
        $events = $stmt->fetchAll();
    } catch (Exception $e) { }

    try {
        $stmt = $pdo->query("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        $news = $stmt->fetchAll();
    } catch (Exception $e) { }

    try {
        $stmt = $pdo->query("SELECT * FROM ministries WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 6");
        $ministries = $stmt->fetchAll();
    } catch (Exception $e) { }

    try {
        $stmt = $pdo->query("SELECT * FROM gallery WHERE status = 'published' ORDER BY created_at DESC LIMIT 8");
        $gallery_images = $stmt->fetchAll();
    } catch (Exception $e) { }

    try {
        $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5");
        $testimonials = $stmt->fetchAll();
    } catch (Exception $e) { }

    try {
        $stmt = $pdo->query("SELECT verse_text, reference FROM bible_verses ORDER BY RAND() LIMIT 1");
        $v = $stmt->fetch();
        if ($v) {
            $verse_text = $v['verse_text'];
            $verse_ref = $v['reference'];
        }
    } catch (Exception $e) { }

    try {
        $membersDsn = 'mysql:host=' . MEMBERS_DB_HOST . ';port=' . DB_PORT . ';dbname=' . MEMBERS_DB_NAME . ';charset=' . DB_CHARSET;
        $membersPdo = new PDO($membersDsn, MEMBERS_DB_USER, MEMBERS_DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $stmt = $membersPdo->query("SELECT COUNT(*) as cnt FROM users WHERE is_active = 1");
        $r = $stmt->fetch();
        if ($r && $r['cnt'] > 0) $stats['members'] = (int)$r['cnt'];
    } catch (Exception $e) { }

} catch (Exception $e) {
    $pdo = null;
}

include __DIR__ . '/components/header.php';
?>

<!-- ==================== HERO ==================== -->
<section class="sdm-hero" id="hero">
    <div class="sdm-hero-slides">
        <?php foreach ($hero_images as $idx => $img): ?>
        <div class="sdm-hero-slide <?php echo $idx === 0 ? 'active' : ''; ?>" style="background-image:url('<?php echo htmlspecialchars($img); ?>');"></div>
        <?php endforeach; ?>
    </div>
    <div class="sdm-hero-overlay"></div>
    <div class="sdm-hero-particles" id="heroParticles"></div>

    <?php if ($is_live): ?>
    <div class="sdm-live-badge" data-aos="fade-down">
        <span class="sdm-live-dot"></span> LIVE NOW
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="sdm-hero-content">
            <div class="sdm-hero-logo-float" data-aos="zoom-in" data-aos-delay="200">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo">
            </div>
            <p class="sdm-hero-greeting" data-aos="fade-up" data-aos-delay="300">Welcome to</p>
            <h1 class="sdm-hero-title" data-aos="fade-up" data-aos-delay="400">
                Salem Dominion<br>
                <span class="sdm-hero-title-accent">Ministries</span>
            </h1>
            <p class="sdm-hero-tagline" data-aos="fade-up" data-aos-delay="500">
                Empowering lives through the Word of God<br class="d-none d-md-block">
                and the Power of the Holy Spirit
            </p>
            <p class="sdm-hero-scripture" data-aos="fade-up" data-aos-delay="600">
                <i class="fas fa-quote-left"></i>
                <?php echo htmlspecialchars($verse_text); ?>
                <span class="sdm-hero-scripture-ref">- <?php echo htmlspecialchars($verse_ref); ?></span>
            </p>
            <div class="sdm-hero-buttons" data-aos="fade-up" data-aos-delay="700">
                <a href="sermons.php" class="sdm-btn sdm-btn-gold">
                    <i class="fas fa-play-circle"></i> Watch Sermons
                </a>
                <a href="events.php" class="sdm-btn sdm-btn-outline">
                    <i class="fas fa-calendar-days"></i> Upcoming Events
                </a>
                <a href="donate.php" class="sdm-btn sdm-btn-outline-gold">
                    <i class="fas fa-heart"></i> Give Now
                </a>
            </div>
        </div>
    </div>

    <div class="sdm-hero-scroll">
        <a href="#about">
            <span class="sdm-hero-scroll-text">Scroll Down</span>
            <span class="sdm-hero-scroll-line"></span>
        </a>
    </div>
</section>

<!-- ==================== ABOUT ==================== -->
<section class="sdm-section sdm-about" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="sdm-about-image-wrap">
                    <img src="assets/apostle-faty-preaching.jpeg" alt="Apostle Faty Musasizi preaching the Word of God" class="sdm-about-img">
                    <div class="sdm-about-image-badge">
                        <span class="sdm-about-badge-number"><?php echo $stats['years']; ?>+</span>
                        <span class="sdm-about-badge-text">Years of Ministry</span>
                    </div>
                    <div class="sdm-about-image-deco"></div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="sdm-section-label"><span>About Our Church</span></div>
                <h2 class="sdm-section-title">A Church Rooted in <span class="sdm-highlight">Faith</span>, Growing in <span class="sdm-highlight">Love</span></h2>
                <p class="sdm-about-lead">
                    <?php echo htmlspecialchars(CHURCH_NAME); ?> is a vibrant, spirit-filled church under the apostolic leadership of <strong>Apostle Faty Musasizi</strong>. We are committed to raising a generation of believers who walk in power, purpose, and divine authority.
                </p>
                <p>
                    Since our founding, we have been a beacon of hope in <?php echo htmlspecialchars(CHURCH_ADDRESS); ?>, impacting lives through worship, teaching, healing, and community transformation.
                </p>
                <div class="sdm-about-features">
                    <div class="sdm-about-feature">
                        <div class="sdm-about-feature-icon"><i class="fas fa-book-bible"></i></div>
                        <div>
                            <strong>Biblical Teaching</strong>
                            <span>Sound doctrine &amp; prophetic insight</span>
                        </div>
                    </div>
                    <div class="sdm-about-feature">
                        <div class="sdm-about-feature-icon"><i class="fas fa-hands-praying"></i></div>
                        <div>
                            <strong>Powerful Worship</strong>
                            <span>Spirit-led praise &amp; worship</span>
                        </div>
                    </div>
                    <div class="sdm-about-feature">
                        <div class="sdm-about-feature-icon"><i class="fas fa-hand-holding-heart"></i></div>
                        <div>
                            <strong>Community Impact</strong>
                            <span>Serving &amp; uplifting our community</span>
                        </div>
                    </div>
                </div>
                <a href="about.php" class="sdm-btn sdm-btn-primary">
                    Learn More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==================== STATS ==================== -->
<section class="sdm-stats" id="stats">
    <div class="sdm-stats-bg" style="background-image:url('assets/ourmembers.jpeg');"></div>
    <div class="sdm-stats-overlay"></div>
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="0">
                <div class="sdm-stat-item">
                    <div class="sdm-stat-icon"><i class="fas fa-users"></i></div>
                    <div class="sdm-stat-number" data-target="<?php echo $stats['members']; ?>">0</div>
                    <div class="sdm-stat-label">Members</div>
                </div>
            </div>
            <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="sdm-stat-item">
                    <div class="sdm-stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="sdm-stat-number" data-target="<?php echo $stats['years']; ?>">0</div>
                    <div class="sdm-stat-label">Years of Ministry</div>
                </div>
            </div>
            <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="sdm-stat-item">
                    <div class="sdm-stat-icon"><i class="fas fa-microphone-lines"></i></div>
                    <div class="sdm-stat-number" data-target="<?php echo $stats['sermons']; ?>">0</div>
                    <div class="sdm-stat-label">Sermons Preached</div>
                </div>
            </div>
            <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="sdm-stat-item">
                    <div class="sdm-stat-icon"><i class="fas fa-calendar-star"></i></div>
                    <div class="sdm-stat-number" data-target="<?php echo $stats['events']; ?>">0</div>
                    <div class="sdm-stat-label">Events Held</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== LATEST SERMONS ==================== -->
<section class="sdm-section sdm-sermons-section" id="sermons">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>From the Pulpit</span></div>
            <h2 class="sdm-section-title">Latest <span class="sdm-highlight">Sermons</span></h2>
            <p class="sdm-section-desc">Be inspired by the transformative Word of God delivered by Apostle Faty Musasizi</p>
        </div>

        <div class="row g-4 mt-4">
            <?php if (!empty($sermons)): ?>
                <?php foreach ($sermons as $idx => $sermon): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                    <div class="sdm-sermon-card">
                        <div class="sdm-sermon-thumb">
                            <img src="assets/pastor-faty-healing.jpeg" alt="<?php echo htmlspecialchars($sermon['title'] ?? 'Sermon'); ?>" loading="lazy">
                            <div class="sdm-sermon-overlay">
                                <a href="sermons.php?id=<?php echo $sermon['id'] ?? 0; ?>" class="sdm-sermon-play">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                            <?php if ($idx === 0): ?>
                            <span class="sdm-sermon-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="sdm-sermon-body">
                            <div class="sdm-sermon-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo isset($sermon['created_at']) ? date('M d, Y', strtotime($sermon['created_at'])) : 'Recent'; ?></span>
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($sermon['category'] ?? 'Sermon'); ?></span>
                            </div>
                            <h4 class="sdm-sermon-title"><?php echo htmlspecialchars($sermon['title'] ?? 'Untitled Sermon'); ?></h4>
                            <p class="sdm-sermon-desc"><?php echo htmlspecialchars(substr($sermon['description'] ?? $sermon['summary'] ?? 'Be blessed by this powerful sermon.', 0, 120)); ?>...</p>
                            <a href="sermons.php?id=<?php echo $sermon['id'] ?? 0; ?>" class="sdm-sermon-link">
                                Watch Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $default_sermons = [
                    ['title' => 'Walking in Divine Power', 'desc' => 'Apostle Faty reveals the keys to walking in supernatural power and authority as believers in this end time.', 'cat' => 'Sunday Service', 'date' => 'This Week'],
                    ['title' => 'The Anointing Breaks Every Yoke', 'desc' => 'Discover how the anointing of the Holy Spirit destroys bondage and sets the captive free.', 'cat' => 'Healing Service', 'date' => 'Recent'],
                    ['title' => 'Kingdom Wealth Principles', 'desc' => 'Learn biblical principles for financial breakthrough and kingdom prosperity.', 'cat' => 'Teaching', 'date' => 'Archive']
                ];
                foreach ($default_sermons as $idx => $s): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                    <div class="sdm-sermon-card">
                        <div class="sdm-sermon-thumb">
                            <img src="assets/apostle-faty-preaching.jpeg" alt="<?php echo htmlspecialchars($s['title']); ?>" loading="lazy">
                            <div class="sdm-sermon-overlay">
                                <a href="<?php echo htmlspecialchars($youtube_channel); ?>" target="_blank" class="sdm-sermon-play">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                            <?php if ($idx === 0): ?>
                            <span class="sdm-sermon-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="sdm-sermon-body">
                            <div class="sdm-sermon-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo $s['date']; ?></span>
                                <span><i class="fas fa-tag"></i> <?php echo $s['cat']; ?></span>
                            </div>
                            <h4 class="sdm-sermon-title"><?php echo htmlspecialchars($s['title']); ?></h4>
                            <p class="sdm-sermon-desc"><?php echo htmlspecialchars($s['desc']); ?></p>
                            <a href="<?php echo htmlspecialchars($youtube_channel); ?>" target="_blank" class="sdm-sermon-link">
                                Watch Now <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="sermons.php" class="sdm-btn sdm-btn-primary">
                View All Sermons <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ==================== UPCOMING EVENTS ==================== -->
<section class="sdm-section sdm-events-section" id="events">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>Join Us</span></div>
            <h2 class="sdm-section-title">Upcoming <span class="sdm-highlight">Events</span></h2>
            <p class="sdm-section-desc">Don't miss out on our upcoming services, conferences, and community gatherings</p>
        </div>

        <?php if (empty($events)): ?>
        <div class="sdm-next-event-card mt-5" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <img src="assets/hero-worship-CWyaH0tr.jpg" alt="Next church service" class="sdm-next-event-img">
                </div>
                <div class="col-md-7">
                    <div class="sdm-next-event-info">
                        <span class="sdm-next-event-tag"><i class="fas fa-calendar-star"></i> Next Service</span>
                        <h3 class="sdm-next-event-title">Sunday Worship Service</h3>
                        <p class="sdm-next-event-desc">Join us for an powerful time of worship, prayer, and the Word of God. Everyone is welcome!</p>
                        <div class="sdm-next-event-details">
                            <div><i class="fas fa-clock"></i> Sunday, 9:00 AM - 12:00 PM</div>
                            <div><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars(CHURCH_ADDRESS); ?></div>
                        </div>
                        <div class="sdm-countdown" id="sdmCountdown" data-next-sunday="1">
                            <div class="sdm-countdown-item">
                                <span class="sdm-countdown-num" id="cdDays">00</span>
                                <span class="sdm-countdown-label">Days</span>
                            </div>
                            <div class="sdm-countdown-item">
                                <span class="sdm-countdown-num" id="cdHours">00</span>
                                <span class="sdm-countdown-label">Hours</span>
                            </div>
                            <div class="sdm-countdown-item">
                                <span class="sdm-countdown-num" id="cdMins">00</span>
                                <span class="sdm-countdown-label">Min</span>
                            </div>
                            <div class="sdm-countdown-item">
                                <span class="sdm-countdown-num" id="cdSecs">00</span>
                                <span class="sdm-countdown-label">Sec</span>
                            </div>
                        </div>
                        <a href="contact.php" class="sdm-btn sdm-btn-primary mt-3">
                            <i class="fas fa-calendar-check"></i> Register Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4 mt-4">
            <?php foreach ($events as $idx => $event): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                <div class="sdm-event-card">
                    <div class="sdm-event-date-box">
                        <span class="sdm-event-day"><?php echo isset($event['event_date']) ? date('d', strtotime($event['event_date'])) : '01'; ?></span>
                        <span class="sdm-event-month"><?php echo isset($event['event_date']) ? date('M', strtotime($event['event_date'])) : 'Jan'; ?></span>
                    </div>
                    <div class="sdm-event-body">
                        <h4 class="sdm-event-title"><?php echo htmlspecialchars($event['title'] ?? 'Church Event'); ?></h4>
                        <div class="sdm-event-meta">
                            <span><i class="fas fa-clock"></i> <?php echo isset($event['event_date']) ? date('h:i A', strtotime($event['event_date'])) : '9:00 AM'; ?></span>
                            <span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($event['location'] ?? CHURCH_ADDRESS); ?></span>
                        </div>
                        <p class="sdm-event-desc"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 100)); ?></p>
                        <a href="events.php?id=<?php echo $event['id'] ?? 0; ?>" class="sdm-sermon-link">
                            Register Now <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="events.php" class="sdm-btn sdm-btn-outline">
                View All Events <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ==================== YOUTUBE LIVE ==================== -->
<section class="sdm-section sdm-youtube-section" id="youtube-live">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>Watch Live</span></div>
            <h2 class="sdm-section-title">Join Us <span class="sdm-highlight">Online</span></h2>
        </div>

        <div class="sdm-youtube-card mt-5" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="sdm-youtube-player">
                        <?php if ($is_live): ?>
                        <div class="sdm-live-badge-inline">
                            <span class="sdm-live-dot"></span> LIVE NOW
                        </div>
                        <iframe src="https://www.youtube.com/embed/live_stream?channel=UC_CHANNEL_ID" allowfullscreen loading="lazy" title="Live stream"></iframe>
                        <?php else: ?>
                        <div class="sdm-youtube-placeholder">
                            <img src="assets/praise-worship-team.jpg" alt="Worship team performing" class="sdm-youtube-thumb" loading="lazy">
                            <div class="sdm-youtube-placeholder-content">
                                <div class="sdm-youtube-play-icon">
                                    <a href="<?php echo htmlspecialchars($youtube_channel); ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                </div>
                                <p>Watch our latest services on YouTube</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="sdm-youtube-info">
                        <h3>Never Miss a <span class="sdm-highlight">Service</span></h3>
                        <p>Subscribe to our YouTube channel to watch live services, sermons, and worship sessions. Turn on notifications so you never miss a word from God.</p>
                        <div class="sdm-youtube-schedule">
                            <h5><i class="fas fa-calendar-days"></i> Service Schedule</h5>
                            <div class="sdm-schedule-item">
                                <span class="sdm-schedule-day">Sunday</span>
                                <span class="sdm-schedule-time">9:00 AM</span>
                            </div>
                            <div class="sdm-schedule-item">
                                <span class="sdm-schedule-day">Wednesday</span>
                                <span class="sdm-schedule-time">6:00 PM</span>
                            </div>
                            <div class="sdm-schedule-item">
                                <span class="sdm-schedule-day">Friday</span>
                                <span class="sdm-schedule-time">6:00 PM</span>
                            </div>
                        </div>
                        <a href="<?php echo htmlspecialchars($youtube_channel); ?>" target="_blank" rel="noopener noreferrer" class="sdm-btn sdm-btn-youtube mt-3">
                            <i class="fab fa-youtube"></i> Subscribe on YouTube
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== LATEST NEWS ==================== -->
<section class="sdm-section sdm-news-section" id="news">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>Stay Updated</span></div>
            <h2 class="sdm-section-title">Latest <span class="sdm-highlight">News</span></h2>
            <p class="sdm-section-desc">Catch up on the latest happenings at Salem Dominion Ministries</p>
        </div>

        <div class="row g-4 mt-4">
            <?php if (!empty($news)): ?>
                <?php foreach ($news as $idx => $item): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                    <div class="sdm-news-card">
                        <div class="sdm-news-thumb">
                            <img src="assets/church-choir-worship.jpeg" alt="<?php echo htmlspecialchars($item['title'] ?? 'News'); ?>" loading="lazy">
                            <span class="sdm-news-date-tag"><?php echo isset($item['created_at']) ? date('M d', strtotime($item['created_at'])) : 'Recent'; ?></span>
                        </div>
                        <div class="sdm-news-body">
                            <div class="sdm-news-category"><?php echo htmlspecialchars($item['category'] ?? 'Church News'); ?></div>
                            <h4 class="sdm-news-title"><?php echo htmlspecialchars($item['title'] ?? 'Latest News'); ?></h4>
                            <p class="sdm-news-desc"><?php echo htmlspecialchars(substr($item['content'] ?? $item['summary'] ?? '', 0, 120)); ?>...</p>
                            <a href="news.php?id=<?php echo $item['id'] ?? 0; ?>" class="sdm-sermon-link">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $default_news = [
                    ['title' => 'Annual Church Conference Announced', 'desc' => 'We are excited to announce our upcoming annual church conference. Prepare for an encounter with God like never before.', 'cat' => 'Events', 'date' => 'Coming Soon'],
                    ['title' => 'Community Outreach Program', 'desc' => 'Salem Dominion Ministries launches a new community outreach program to support families in need across Iganga District.', 'cat' => 'Outreach', 'date' => 'New'],
                    ['title' => 'New Prayer Center Opening', 'desc' => 'A brand new prayer center is being inaugurated to provide a dedicated space for 24/7 prayer and worship.', 'cat' => 'Announcement', 'date' => 'Featured']
                ];
                foreach ($default_news as $idx => $n): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                    <div class="sdm-news-card">
                        <div class="sdm-news-thumb">
                            <img src="assets/community-<?php echo ($idx + 1); ?>.jpg" alt="<?php echo htmlspecialchars($n['title']); ?>" loading="lazy" onerror="this.src='assets/hero-community-CDAgPtPb.jpg'">
                            <span class="sdm-news-date-tag"><?php echo $n['date']; ?></span>
                        </div>
                        <div class="sdm-news-body">
                            <div class="sdm-news-category"><?php echo $n['cat']; ?></div>
                            <h4 class="sdm-news-title"><?php echo htmlspecialchars($n['title']); ?></h4>
                            <p class="sdm-news-desc"><?php echo htmlspecialchars($n['desc']); ?></p>
                            <a href="news.php" class="sdm-sermon-link">
                                Read More <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="news.php" class="sdm-btn sdm-btn-outline">
                View All News <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ==================== MINISTRIES ==================== -->
<section class="sdm-section sdm-ministries-section" id="ministries">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>Get Involved</span></div>
            <h2 class="sdm-section-title">Our <span class="sdm-highlight">Ministries</span></h2>
            <p class="sdm-section-desc">Discover a place to serve, grow, and make a difference in the Kingdom of God</p>
        </div>

        <div class="row g-4 mt-4">
            <?php
            $ministry_icons = [
                'worship' => 'fa-music',
                'youth' => 'fa-people-group',
                'children' => 'fa-child',
                'prayer' => 'fa-person-praying',
                'outreach' => 'fa-hand-holding-heart',
                'teaching' => 'fa-book-open',
                'missions' => 'fa-globe',
                'counseling' => 'fa-comments',
            ];
            $ministry_colors = ['#0ea5e9', '#fbbf24', '#22c55e', '#a855f7', '#f43f5e', '#06b6d4', '#eab308', '#ec4899'];
            $ministry_images = [
                'assets/praise-worship-team.jpeg',
                'assets/children-celebrating-Z18oVWUU.jpeg',
                'assets/children-with-books-Cc2LmxDu.jpeg',
                'assets/support-children-now-Dqa2JhXn.jpeg',
                'assets/ourmembers.jpeg',
                'assets/church-choir-worship.jpeg'
            ];

            if (!empty($ministries)):
                foreach ($ministries as $idx => $m):
                    $slug = strtolower($m['slug'] ?? $m['name'] ?? '');
                    $icon = $ministry_icons[$slug] ?? 'fa-church';
                    $color = $ministry_colors[$idx % count($ministry_colors)];
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                <div class="sdm-ministry-card">
                    <div class="sdm-ministry-icon" style="background:<?php echo $color; ?>20;color:<?php echo $color; ?>">
                        <i class="fas <?php echo htmlspecialchars($icon); ?>"></i>
                    </div>
                    <h4 class="sdm-ministry-name"><?php echo htmlspecialchars($m['name'] ?? 'Ministry'); ?></h4>
                    <p class="sdm-ministry-desc"><?php echo htmlspecialchars($m['description'] ?? 'Join this ministry and be a part of what God is doing.'); ?></p>
                    <a href="ministries.php?cat=<?php echo htmlspecialchars($slug); ?>" class="sdm-sermon-link">
                        Learn More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php
                endforeach;
            else:
                $default_ministries = [
                    ['icon' => 'fa-music', 'name' => 'Worship Ministry', 'desc' => 'Leading the congregation into powerful, spirit-led worship through music, dance, and creative arts.', 'color' => '#0ea5e9'],
                    ['icon' => 'fa-people-group', 'name' => 'Youth Ministry', 'desc' => 'Empowering the next generation to walk in purpose, faith, and leadership through relevant teaching and fellowship.', 'color' => '#fbbf24'],
                    ['icon' => 'fa-child', 'name' => "Children's Ministry", 'desc' => 'Building a strong spiritual foundation for our children through age-appropriate teaching, fun activities, and love.', 'color' => '#22c55e'],
                    ['icon' => 'fa-person-praying', 'name' => 'Prayer Ministry', 'desc' => 'Interceding for the church, community, and nation through dedicated prayer and fasting.', 'color' => '#a855f7'],
                    ['icon' => 'fa-hand-holding-heart', 'name' => 'Outreach Ministry', 'desc' => 'Extending the love of God beyond our walls through community service, evangelism, and charitable works.', 'color' => '#f43f5e'],
                    ['icon' => 'fa-book-open', 'name' => 'Bible Study', 'desc' => 'Deepening understanding of God\'s Word through systematic study, discussion, and application.', 'color' => '#06b6d4']
                ];
                foreach ($default_ministries as $idx => $m): ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                    <div class="sdm-ministry-card">
                        <div class="sdm-ministry-icon" style="background:<?php echo $m['color']; ?>20;color:<?php echo $m['color']; ?>">
                            <i class="fas <?php echo $m['icon']; ?>"></i>
                        </div>
                        <h4 class="sdm-ministry-name"><?php echo $m['name']; ?></h4>
                        <p class="sdm-ministry-desc"><?php echo $m['desc']; ?></p>
                        <a href="ministries.php" class="sdm-sermon-link">
                            Learn More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==================== GALLERY PREVIEW ==================== -->
<section class="sdm-section sdm-gallery-section" id="gallery">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>Capturing Moments</span></div>
            <h2 class="sdm-section-title">Photo <span class="sdm-highlight">Gallery</span></h2>
            <p class="sdm-section-desc">Moments of worship, fellowship, and community at Salem Dominion Ministries</p>
        </div>

        <div class="sdm-gallery-grid mt-4">
            <?php
            $gallery_defaults = [
                ['src' => 'assets/church-choir-worship.jpeg', 'alt' => 'Church choir worshiping'],
                ['src' => 'assets/praise-worship-team.jpeg', 'alt' => 'Praise and worship team'],
                ['src' => 'assets/children-celebrating-Z18oVWUU.jpeg', 'alt' => 'Children celebrating at church'],
                ['src' => 'assets/apostle-faty-preaching.jpeg', 'alt' => 'Apostle Faty Musasizi preaching'],
                ['src' => 'assets/hero-worship-CWyaH0tr.jpg', 'alt' => 'Worship service'],
                ['src' => 'assets/children-with-books-Cc2LmxDu.jpeg', 'alt' => 'Children with books'],
                ['src' => 'assets/ourmembers.jpeg', 'alt' => 'Our church members'],
                ['src' => 'assets/hero-community-CDAgPtPb.jpg', 'alt' => 'Community gathering'],
            ];

            $gallery_items = !empty($gallery_images) ? $gallery_images : $gallery_defaults;
            $gallery_sizes = ['sdm-gallery-item-wide', '', '', 'sdm-gallery-item-wide', '', '', '', 'sdm-gallery-item-tall'];

            foreach ($gallery_items as $idx => $img):
                $src = !empty($img['file_url']) ? htmlspecialchars($img['file_url']) : htmlspecialchars($gallery_defaults[$idx % count($gallery_defaults)]['src']);
                $alt = !empty($img['title']) ? htmlspecialchars($img['title']) : htmlspecialchars($gallery_defaults[$idx % count($gallery_defaults)]['alt']);
                $size_class = $gallery_sizes[$idx % count($gallery_sizes)];
            ?>
            <div class="sdm-gallery-item <?php echo $size_class; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($idx % 4) * 80; ?>">
                <img src="<?php echo $src; ?>" alt="<?php echo $alt; ?>" loading="lazy">
                <div class="sdm-gallery-hover">
                    <i class="fas fa-expand"></i>
                    <span><?php echo $alt; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="gallery.php" class="sdm-btn sdm-btn-primary">
                View Full Gallery <i class="fas fa-images"></i>
            </a>
        </div>
    </div>
</section>

<!-- ==================== TESTIMONIALS ==================== -->
<section class="sdm-section sdm-testimonials-section" id="testimonials">
    <div class="container">
        <div class="text-center" data-aos="fade-up">
            <div class="sdm-section-label"><span>Stories of Transformation</span></div>
            <h2 class="sdm-section-title">What People <span class="sdm-highlight">Say</span></h2>
            <p class="sdm-section-desc">Hear from our church members about how God has touched their lives</p>
        </div>

        <div class="sdm-testimonials-carousel mt-5" id="sdmTestimonialsCarousel" data-aos="fade-up">
            <?php
            $testimonial_defaults = [
                ['name' => 'Sarah Namukasa', 'role' => 'Church Member', 'text' => 'Salem Dominion Ministries has been a life-changing experience. The teachings of Apostle Faty have transformed my understanding of God\'s Word and I have experienced supernatural breakthroughs in my life.', 'rating' => 5],
                ['name' => 'David Okello', 'role' => 'Youth Leader', 'text' => 'The youth ministry here is incredible. I have grown spiritually and developed leadership skills that I never knew I had. This church truly empowers the next generation.', 'rating' => 5],
                ['name' => 'Grace Nakamya', 'role' => 'Worship Leader', 'text' => 'Being part of the worship team at Salem Dominion has deepened my relationship with God. The atmosphere of worship here is genuinely supernatural.', 'rating' => 5],
                ['name' => 'Peter Muwonge', 'role' => 'Church Elder', 'text' => 'I have been part of many churches, but Salem Dominion is special. The anointing upon Apostle Faty is real, and the fruits are visible in the lives of the congregation.', 'rating' => 5],
                ['name' => 'Esther Nabirye', 'role' => 'Prayer Warrior', 'text' => 'The prayer ministry here has taught me the power of consistent intercession. I have seen God answer prayers in miraculous ways since joining this ministry.', 'rating' => 5]
            ];

            $test_items = !empty($testimonials) ? $testimonials : $testimonial_defaults;
            $avatar_colors = ['#0ea5e9', '#fbbf24', '#22c55e', '#a855f7', '#f43f5e'];

            foreach ($test_items as $idx => $t):
                $name = htmlspecialchars($t['name'] ?? $t['author_name'] ?? 'Member');
                $role = htmlspecialchars($t['role'] ?? $t['occupation'] ?? 'Church Member');
                $text = htmlspecialchars($t['text'] ?? $t['content'] ?? '');
                $rating = (int)($t['rating'] ?? 5);
                $initials = implode('', array_map(function($w) { return strtoupper($w[0]); }, explode(' ', $name)));
                $avatar_color = $avatar_colors[$idx % count($avatar_colors)];
            ?>
            <div class="sdm-testimonial-card <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo $idx; ?>">
                <div class="sdm-testimonial-stars">
                    <?php for ($s = 0; $s < $rating; $s++): ?>
                    <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <p class="sdm-testimonial-text">"<?php echo $text; ?>"</p>
                <div class="sdm-testimonial-author">
                    <div class="sdm-testimonial-avatar" style="background:<?php echo $avatar_color; ?>">
                        <?php echo $initials; ?>
                    </div>
                    <div>
                        <strong><?php echo $name; ?></strong>
                        <span><?php echo $role; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="sdm-testimonial-nav">
                <button class="sdm-testimonial-prev" id="sdmTestPrev" aria-label="Previous testimonial"><i class="fas fa-chevron-left"></i></button>
                <div class="sdm-testimonial-dots" id="sdmTestDots"></div>
                <button class="sdm-testimonial-next" id="sdmTestNext" aria-label="Next testimonial"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- ==================== BIBLE VERSE ==================== -->
<section class="sdm-bible-verse" id="bible-verse">
    <div class="container">
        <div class="sdm-bible-verse-card" data-aos="zoom-in">
            <div class="sdm-bible-verse-deco"><i class="fas fa-book-bible"></i></div>
            <blockquote>
                <p class="sdm-bible-verse-text" id="sdmVerseText">"<?php echo htmlspecialchars($verse_text); ?>"</p>
                <cite class="sdm-bible-verse-ref" id="sdmVerseRef">- <?php echo htmlspecialchars($verse_ref); ?></cite>
            </blockquote>
            <button class="sdm-btn sdm-btn-sm sdm-btn-gold-outline" id="sdmNewVerse" title="Get a new verse">
                <i class="fas fa-refresh"></i> New Verse
            </button>
        </div>
    </div>
</section>

<!-- ==================== CALL TO ACTION ==================== -->
<section class="sdm-cta" id="cta">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="sdm-cta-card sdm-cta-prayer">
                    <div class="sdm-cta-icon"><i class="fas fa-hands-praying"></i></div>
                    <h4>Prayer Request</h4>
                    <p>Submit your prayer needs and let us stand with you in faith. Our prayer team intercedes daily for every request.</p>
                    <a href="prayer-request.php" class="sdm-btn sdm-btn-white">
                        <i class="fas fa-paper-plane"></i> Submit Prayer Request
                    </a>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="sdm-cta-card sdm-cta-donate">
                    <div class="sdm-cta-icon"><i class="fas fa-hand-holding-dollar"></i></div>
                    <h4>Give a Donation</h4>
                    <p>Your generous giving supports our ministry, community outreach, and the spread of the Gospel across Uganda and beyond.</p>
                    <a href="donate.php" class="sdm-btn sdm-btn-white">
                        <i class="fas fa-heart"></i> Give Now
                    </a>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="sdm-cta-card sdm-cta-book">
                    <div class="sdm-cta-icon"><i class="fas fa-calendar-check"></i></div>
                    <h4>Book Apostle Faty</h4>
                    <p>Invite Apostle Faty Musasizi to your event, conference, or crusade for a powerful, life-transforming ministry experience.</p>
                    <a href="book-pastor.php" class="sdm-btn sdm-btn-white">
                        <i class="fas fa-phone"></i> Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== NEWSLETTER CTA ==================== -->
<section class="sdm-newsletter-cta" id="newsletter-cta">
    <div class="container">
        <div class="sdm-newsletter-cta-inner" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h3>Stay Connected with <span class="sdm-highlight">Salem Dominion</span></h3>
                    <p>Subscribe to our newsletter for weekly devotionals, event updates, and inspiring messages from Apostle Faty.</p>
                </div>
                <div class="col-lg-6">
                    <form class="sdm-cta-newsletter-form" onsubmit="return sdmCtaSubscribe(event)">
                        <div class="sdm-cta-newsletter-wrap">
                            <input type="email" placeholder="Your email address" required aria-label="Email for newsletter">
                            <button type="submit"><i class="fas fa-paper-plane"></i> Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function sdmCtaSubscribe(e) {
    e.preventDefault();
    var input = e.target.querySelector('input[type="email"]');
    var val = input.value;
    var toastEl = document.getElementById('sdmNewsletterToast');
    var toastText = document.getElementById('sdmToastText');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/newsletter_subscribe.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                toastText.textContent = res.message || 'Successfully subscribed!';
            } catch(ex) {
                toastText.textContent = 'Successfully subscribed! Thank you.';
            }
            var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
            input.value = '';
        }
    };
    xhr.onerror = function() {
        toastText.textContent = 'Subscription failed. Please try again.';
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    };
    xhr.send('email=' + encodeURIComponent(val));
    return false;
}
</script>

<?php include __DIR__ . '/components/footer.php'; ?>

<!-- ==================== HOMEPAGE STYLES ==================== -->
<style>
/* ===== HERO ===== */
.sdm-hero {
    position: relative;
    height: 100vh;
    min-height: 650px;
    max-height: 1000px;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: var(--midnight);
}

.sdm-hero-slides {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.sdm-hero-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1.5s ease-in-out;
    transform: scale(1.05);
}

.sdm-hero-slide.active {
    opacity: 1;
    transform: scale(1);
    animation: sdmHeroZoom 12s ease-in-out infinite alternate;
}

@keyframes sdmHeroZoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.08); }
}

.sdm-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(15,23,42,0.55) 0%, rgba(15,23,42,0.75) 50%, rgba(15,23,42,0.92) 100%);
    z-index: 1;
}

.sdm-hero-particles {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
}

.sdm-live-badge {
    position: absolute;
    top: 100px;
    left: 30px;
    z-index: 10;
    background: #ef4444;
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: sdmLivePulse 2s ease-in-out infinite;
}

.sdm-live-dot {
    width: 10px;
    height: 10px;
    background: white;
    border-radius: 50%;
    animation: sdmLivePulseDot 1s ease-in-out infinite;
}

@keyframes sdmLivePulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
    50% { box-shadow: 0 0 0 12px rgba(239,68,68,0); }
}

@keyframes sdmLivePulseDot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

.sdm-hero-content {
    position: relative;
    z-index: 5;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.sdm-hero-logo-float {
    margin-bottom: 24px;
}

.sdm-hero-logo-float img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(251,191,36,0.5);
    box-shadow: 0 8px 40px rgba(0,0,0,0.3);
    animation: sdmFloat 4s ease-in-out infinite;
}

@keyframes sdmFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-12px); }
}

.sdm-hero-greeting {
    font-family: 'Great Vibes', cursive;
    font-size: 1.6rem;
    color: var(--gold);
    margin-bottom: 4px;
}

.sdm-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 900;
    color: var(--white);
    line-height: 1.1;
    margin-bottom: 20px;
}

.sdm-hero-title-accent {
    background: linear-gradient(135deg, var(--gold), #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sdm-hero-tagline {
    font-size: 1.15rem;
    color: rgba(255,255,255,0.85);
    font-weight: 400;
    margin-bottom: 20px;
    line-height: 1.6;
}

.sdm-hero-scripture {
    font-style: italic;
    font-size: 0.95rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.sdm-hero-scripture i { color: var(--gold); margin-right: 6px; font-size: 0.8rem; }

.sdm-hero-scripture-ref {
    display: block;
    margin-top: 4px;
    color: var(--gold);
    font-weight: 600;
    font-style: normal;
}

.sdm-hero-buttons {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}

.sdm-hero-scroll {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    text-align: center;
}

.sdm-hero-scroll a {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.sdm-hero-scroll-text {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 2px;
}

.sdm-hero-scroll-line {
    width: 1px;
    height: 40px;
    background: linear-gradient(to bottom, var(--gold), transparent);
    animation: sdmScrollBounce 2s ease-in-out infinite;
}

@keyframes sdmScrollBounce {
    0%, 100% { opacity: 1; transform: scaleY(1); }
    50% { opacity: 0.4; transform: scaleY(0.6); }
}

/* ===== BUTTONS ===== */
.sdm-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    border-radius: 12px;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.35s ease;
    border: none;
    text-decoration: none;
    white-space: nowrap;
}

.sdm-btn-primary {
    background: linear-gradient(135deg, var(--ocean), #0284c7);
    color: white;
    box-shadow: 0 4px 16px rgba(14,165,233,0.3);
}

.sdm-btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(14,165,233,0.4);
    color: white;
}

.sdm-btn-gold {
    background: linear-gradient(135deg, var(--gold), #f59e0b);
    color: var(--midnight);
    box-shadow: 0 4px 16px rgba(251,191,36,0.3);
}

.sdm-btn-gold:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(251,191,36,0.4);
    color: var(--midnight);
}

.sdm-btn-outline {
    background: rgba(255,255,255,0.08);
    color: white;
    border: 2px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(4px);
}

.sdm-btn-outline:hover {
    background: var(--white);
    color: var(--midnight);
    border-color: var(--white);
    transform: translateY(-3px);
}

.sdm-btn-outline-gold {
    background: transparent;
    color: var(--gold);
    border: 2px solid var(--gold);
}

.sdm-btn-outline-gold:hover {
    background: var(--gold);
    color: var(--midnight);
    transform: translateY(-3px);
}

.sdm-btn-white {
    background: white;
    color: var(--midnight);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.sdm-btn-white:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    color: var(--ocean);
}

.sdm-btn-sm {
    padding: 10px 22px;
    font-size: 0.85rem;
    border-radius: 10px;
}

.sdm-btn-gold-outline {
    background: transparent;
    color: var(--gold);
    border: 2px solid rgba(251,191,36,0.4);
}

.sdm-btn-gold-outline:hover {
    background: var(--gold);
    color: var(--midnight);
}

.sdm-btn-youtube {
    background: #ff0000;
    color: white;
}

.sdm-btn-youtube:hover {
    background: #cc0000;
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(255,0,0,0.3);
    color: white;
}

/* ===== SECTION UTILITIES ===== */
.sdm-section {
    padding: 100px 0;
    position: relative;
}

.sdm-section-label {
    margin-bottom: 12px;
}

.sdm-section-label span {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--ocean);
    background: rgba(14,165,233,0.08);
    padding: 6px 18px;
    border-radius: 20px;
}

.sdm-section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 800;
    color: var(--midnight);
    margin-bottom: 14px;
    line-height: 1.2;
}

.sdm-highlight {
    background: linear-gradient(135deg, var(--ocean), var(--sky));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sdm-section-desc {
    font-size: 1.05rem;
    color: var(--gray-500);
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.7;
}

/* ===== ABOUT ===== */
.sdm-about { background: var(--white); }

.sdm-about-image-wrap {
    position: relative;
}

.sdm-about-img {
    width: 100%;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12);
    object-fit: cover;
    aspect-ratio: 4/5;
}

.sdm-about-image-badge {
    position: absolute;
    bottom: -20px;
    right: -20px;
    background: linear-gradient(135deg, var(--gold), #f59e0b);
    color: var(--midnight);
    padding: 20px 28px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(251,191,36,0.35);
}

.sdm-about-badge-number {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 900;
    line-height: 1;
}

.sdm-about-badge-text {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sdm-about-image-deco {
    position: absolute;
    top: -20px;
    left: -20px;
    width: 120px;
    height: 120px;
    border: 3px solid var(--gold);
    border-radius: 24px;
    opacity: 0.2;
    z-index: -1;
}

.sdm-about-lead {
    font-size: 1.1rem;
    color: var(--gray-700);
    line-height: 1.8;
    margin-bottom: 12px;
}

.sdm-about-features {
    margin: 28px 0;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sdm-about-feature {
    display: flex;
    align-items: center;
    gap: 14px;
}

.sdm-about-feature-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(14,165,233,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ocean);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.sdm-about-feature strong {
    display: block;
    font-size: 0.92rem;
    color: var(--midnight);
    margin-bottom: 2px;
}

.sdm-about-feature span {
    font-size: 0.82rem;
    color: var(--gray-500);
}

/* ===== STATS ===== */
.sdm-stats {
    position: relative;
    padding: 80px 0;
    overflow: hidden;
}

.sdm-stats-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

.sdm-stats-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(15,23,42,0.92), rgba(14,165,233,0.85));
}

.sdm-stats .container { position: relative; z-index: 2; }

.sdm-stat-item {
    text-align: center;
    color: white;
    padding: 20px;
}

.sdm-stat-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.4rem;
    color: var(--gold);
}

.sdm-stat-number {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 4px;
}

.sdm-stat-label {
    font-size: 0.88rem;
    font-weight: 500;
    color: rgba(255,255,255,0.8);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ===== SERMONS ===== */
.sdm-sermons-section { background: var(--pearl); }

.sdm-sermon-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.4s ease;
    height: 100%;
}

.sdm-sermon-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.12);
}

.sdm-sermon-thumb {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/10;
}

.sdm-sermon-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.sdm-sermon-card:hover .sdm-sermon-thumb img {
    transform: scale(1.08);
}

.sdm-sermon-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15,23,42,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.sdm-sermon-card:hover .sdm-sermon-overlay { opacity: 1; }

.sdm-sermon-play {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--gold);
    color: var(--midnight);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transform: scale(0.8);
    transition: all 0.4s ease;
    text-decoration: none;
}

.sdm-sermon-card:hover .sdm-sermon-play { transform: scale(1); }

.sdm-sermon-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: var(--gold);
    color: var(--midnight);
    padding: 4px 14px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.sdm-sermon-body { padding: 22px; }

.sdm-sermon-meta {
    display: flex;
    gap: 14px;
    margin-bottom: 10px;
}

.sdm-sermon-meta span {
    font-size: 0.78rem;
    color: var(--gray-400);
}

.sdm-sermon-meta i {
    margin-right: 4px;
    color: var(--ocean);
    font-size: 0.7rem;
}

.sdm-sermon-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--midnight);
    margin-bottom: 8px;
    line-height: 1.3;
}

.sdm-sermon-desc {
    font-size: 0.88rem;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 14px;
}

.sdm-sermon-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--ocean);
    text-decoration: none;
    transition: all 0.3s ease;
}

.sdm-sermon-link i { transition: transform 0.3s ease; }
.sdm-sermon-link:hover { color: var(--sky); }
.sdm-sermon-link:hover i { transform: translateX(4px); }

/* ===== EVENTS ===== */
.sdm-events-section { background: white; }

.sdm-next-event-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.sdm-next-event-img {
    width: 100%;
    height: 100%;
    min-height: 300px;
    object-fit: cover;
}

.sdm-next-event-info { padding: 36px; }

.sdm-next-event-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(14,165,233,0.1);
    color: var(--ocean);
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    margin-bottom: 14px;
}

.sdm-next-event-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--midnight);
    margin-bottom: 12px;
}

.sdm-next-event-desc {
    color: var(--gray-500);
    line-height: 1.7;
    margin-bottom: 18px;
}

.sdm-next-event-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 20px;
}

.sdm-next-event-details i {
    color: var(--ocean);
    width: 18px;
    margin-right: 8px;
}

.sdm-next-event-details div {
    font-size: 0.9rem;
    color: var(--gray-600);
}

.sdm-countdown {
    display: flex;
    gap: 12px;
}

.sdm-countdown-item {
    text-align: center;
    background: var(--pearl);
    padding: 12px 16px;
    border-radius: 12px;
    min-width: 70px;
}

.sdm-countdown-num {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    font-weight: 900;
    color: var(--ocean);
    line-height: 1;
}

.sdm-countdown-label {
    display: block;
    font-size: 0.7rem;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 4px;
}

.sdm-event-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.4s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.sdm-event-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.1);
}

.sdm-event-date-box {
    background: linear-gradient(135deg, var(--ocean), var(--sky));
    color: white;
    text-align: center;
    padding: 20px;
}

.sdm-event-day {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 900;
    line-height: 1;
}

.sdm-event-month {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sdm-event-body { padding: 22px; flex: 1; display: flex; flex-direction: column; }

.sdm-event-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    color: var(--midnight);
    margin-bottom: 10px;
}

.sdm-event-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.sdm-event-meta span {
    font-size: 0.82rem;
    color: var(--gray-400);
}

.sdm-event-meta i {
    color: var(--ocean);
    width: 16px;
    margin-right: 4px;
    font-size: 0.75rem;
}

.sdm-event-desc {
    font-size: 0.88rem;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 14px;
    flex: 1;
}

/* ===== YOUTUBE ===== */
.sdm-youtube-section { background: var(--pearl); }

.sdm-youtube-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
}

.sdm-youtube-player {
    position: relative;
    width: 100%;
    border-radius: 24px 0 0 24px;
    overflow: hidden;
}

.sdm-youtube-player iframe {
    width: 100%;
    aspect-ratio: 16/9;
    border: none;
}

.sdm-live-badge-inline {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 5;
    background: #ef4444;
    color: white;
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    animation: sdmLivePulse 2s infinite;
}

.sdm-youtube-placeholder {
    position: relative;
    aspect-ratio: 16/9;
}

.sdm-youtube-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sdm-youtube-placeholder-content {
    position: absolute;
    inset: 0;
    background: rgba(15,23,42,0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
}

.sdm-youtube-placeholder-content p {
    margin-top: 16px;
    font-size: 1rem;
    font-weight: 500;
}

.sdm-youtube-play-icon a {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #ff0000;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.sdm-youtube-play-icon a:hover {
    transform: scale(1.1);
    box-shadow: 0 0 40px rgba(255,0,0,0.4);
}

.sdm-youtube-info { padding: 30px; }

.sdm-youtube-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    margin-bottom: 14px;
}

.sdm-youtube-info > p {
    color: var(--gray-500);
    line-height: 1.7;
    margin-bottom: 24px;
}

.sdm-youtube-schedule {
    background: var(--pearl);
    border-radius: 16px;
    padding: 20px;
}

.sdm-youtube-schedule h5 {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sdm-youtube-schedule h5 i { color: var(--gold); }

.sdm-schedule-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--gray-200);
    font-size: 0.88rem;
}

.sdm-schedule-item:last-child { border-bottom: none; }

.sdm-schedule-day { color: var(--gray-600); font-weight: 600; }
.sdm-schedule-time { color: var(--ocean); font-weight: 700; }

/* ===== NEWS ===== */
.sdm-news-section { background: white; }

.sdm-news-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.4s ease;
    height: 100%;
}

.sdm-news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.1);
}

.sdm-news-thumb {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/10;
}

.sdm-news-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.sdm-news-card:hover .sdm-news-thumb img { transform: scale(1.08); }

.sdm-news-date-tag {
    position: absolute;
    top: 14px;
    left: 14px;
    background: var(--ocean);
    color: white;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
}

.sdm-news-body { padding: 22px; }

.sdm-news-category {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--ocean);
    margin-bottom: 8px;
}

.sdm-news-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    color: var(--midnight);
    margin-bottom: 8px;
    line-height: 1.3;
}

.sdm-news-desc {
    font-size: 0.88rem;
    color: var(--gray-500);
    line-height: 1.6;
    margin-bottom: 14px;
}

/* ===== MINISTRIES ===== */
.sdm-ministries-section { background: var(--pearl); }

.sdm-ministry-card {
    background: white;
    border-radius: 20px;
    padding: 32px 28px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    transition: all 0.4s ease;
    height: 100%;
}

.sdm-ministry-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.1);
}

.sdm-ministry-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 20px;
    transition: all 0.4s ease;
}

.sdm-ministry-card:hover .sdm-ministry-icon {
    transform: scale(1.1) rotate(5deg);
}

.sdm-ministry-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    color: var(--midnight);
    margin-bottom: 10px;
}

.sdm-ministry-desc {
    font-size: 0.88rem;
    color: var(--gray-500);
    line-height: 1.7;
    margin-bottom: 18px;
}

/* ===== GALLERY ===== */
.sdm-gallery-section { background: white; }

.sdm-gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 200px;
    gap: 12px;
}

.sdm-gallery-item {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
}

.sdm-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.sdm-gallery-item:hover img { transform: scale(1.1); }

.sdm-gallery-item-wide { grid-column: span 2; }
.sdm-gallery-item-tall { grid-row: span 2; }

.sdm-gallery-hover {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(15,23,42,0.85) 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.4s ease;
    color: white;
    gap: 8px;
}

.sdm-gallery-item:hover .sdm-gallery-hover { opacity: 1; }

.sdm-gallery-hover i { font-size: 1.5rem; color: var(--gold); }

.sdm-gallery-hover span {
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
}

/* ===== TESTIMONIALS ===== */
.sdm-testimonials-section {
    background: linear-gradient(180deg, var(--pearl) 0%, white 100%);
}

.sdm-testimonials-carousel {
    position: relative;
    max-width: 700px;
    margin: 0 auto;
    overflow: hidden;
}

.sdm-testimonial-card {
    display: none;
    background: white;
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
    text-align: center;
}

.sdm-testimonial-card.active { display: block; }

.sdm-testimonial-stars {
    margin-bottom: 18px;
    color: var(--gold);
    font-size: 1rem;
}

.sdm-testimonial-text {
    font-size: 1.05rem;
    color: var(--gray-600);
    line-height: 1.8;
    font-style: italic;
    margin-bottom: 24px;
}

.sdm-testimonial-author {
    display: flex;
    align-items: center;
    gap: 14px;
    justify-content: center;
}

.sdm-testimonial-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.85rem;
}

.sdm-testimonial-author strong {
    display: block;
    font-size: 0.95rem;
    color: var(--midnight);
}

.sdm-testimonial-author span {
    font-size: 0.82rem;
    color: var(--gray-400);
}

.sdm-testimonial-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 28px;
}

.sdm-testimonial-prev,
.sdm-testimonial-next {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--gray-200);
    background: white;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.sdm-testimonial-prev:hover,
.sdm-testimonial-next:hover {
    background: var(--ocean);
    border-color: var(--ocean);
    color: white;
}

.sdm-testimonial-dots {
    display: flex;
    gap: 8px;
}

.sdm-testimonial-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--gray-300);
    cursor: pointer;
    transition: all 0.3s ease;
}

.sdm-testimonial-dot.active {
    background: var(--ocean);
    width: 28px;
    border-radius: 5px;
}

/* ===== BIBLE VERSE ===== */
.sdm-bible-verse {
    padding: 80px 0;
    background: linear-gradient(135deg, var(--midnight) 0%, #1a2744 100%);
    position: relative;
    overflow: hidden;
}

.sdm-bible-verse::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
}

.sdm-bible-verse-card {
    text-align: center;
    max-width: 700px;
    margin: 0 auto;
    padding: 40px;
}

.sdm-bible-verse-deco {
    font-size: 3rem;
    color: var(--gold);
    margin-bottom: 20px;
    opacity: 0.8;
}

.sdm-bible-verse-text {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.3rem, 3vw, 1.8rem);
    font-style: italic;
    color: var(--white);
    line-height: 1.6;
    margin-bottom: 18px;
}

.sdm-bible-verse-ref {
    display: block;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--gold);
    font-style: normal;
    margin-bottom: 24px;
}

/* ===== CTA ===== */
.sdm-cta {
    padding: 100px 0;
    background: white;
}

.sdm-cta-card {
    border-radius: 24px;
    padding: 40px 32px;
    text-align: center;
    color: white;
    transition: all 0.4s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.sdm-cta-card:hover { transform: translateY(-8px); }

.sdm-cta-prayer { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.sdm-cta-donate { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: var(--midnight); }
.sdm-cta-book { background: linear-gradient(135deg, #22c55e, #16a34a); }

.sdm-cta-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin-bottom: 20px;
}

.sdm-cta-card h4 {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    margin-bottom: 12px;
    color: inherit;
}

.sdm-cta-card p {
    font-size: 0.9rem;
    line-height: 1.7;
    opacity: 0.9;
    margin-bottom: 24px;
    flex: 1;
}

/* ===== NEWSLETTER CTA ===== */
.sdm-newsletter-cta {
    padding: 80px 0;
    background: var(--pearl);
}

.sdm-newsletter-cta-inner {
    background: linear-gradient(135deg, var(--midnight) 0%, #1a2744 100%);
    border-radius: 28px;
    padding: 50px;
    color: white;
}

.sdm-newsletter-cta-inner h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: white;
    margin-bottom: 10px;
}

.sdm-newsletter-cta-inner p {
    color: rgba(255,255,255,0.7);
    line-height: 1.7;
}

.sdm-cta-newsletter-wrap {
    display: flex;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 14px;
    overflow: hidden;
}

.sdm-cta-newsletter-wrap input {
    flex: 1;
    background: transparent;
    border: none;
    color: white;
    padding: 16px 20px;
    font-size: 0.95rem;
    font-family: 'Montserrat', sans-serif;
    outline: none;
}

.sdm-cta-newsletter-wrap input::placeholder { color: rgba(255,255,255,0.4); }

.sdm-cta-newsletter-wrap button {
    background: var(--gold);
    color: var(--midnight);
    padding: 16px 28px;
    font-weight: 700;
    font-size: 0.9rem;
    font-family: 'Montserrat', sans-serif;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: background 0.3s ease;
}

.sdm-cta-newsletter-wrap button:hover { background: #f59e0b; }

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .sdm-hero { min-height: 600px; }
    .sdm-gallery-grid {
        grid-template-columns: repeat(2, 1fr);
        grid-auto-rows: 180px;
    }
    .sdm-youtube-player { border-radius: 24px 24px 0 0; }
    .sdm-about-image-badge { bottom: auto; top: 20px; right: 20px; }
    .sdm-about-image-deco { display: none; }
    .sdm-stats-bg { background-attachment: scroll; }
}

@media (max-width: 768px) {
    .sdm-section { padding: 70px 0; }
    .sdm-hero-title { font-size: 2.2rem; }
    .sdm-hero-tagline { font-size: 0.95rem; }
    .sdm-hero-logo-float img { width: 80px; height: 80px; }
    .sdm-gallery-grid {
        grid-template-columns: repeat(2, 1fr);
        grid-auto-rows: 150px;
    }
    .sdm-gallery-item-wide { grid-column: span 1; }
    .sdm-gallery-item-tall { grid-row: span 1; }
    .sdm-countdown { gap: 8px; }
    .sdm-countdown-item { min-width: 58px; padding: 10px 12px; }
    .sdm-countdown-num { font-size: 1.3rem; }
    .sdm-newsletter-cta-inner { padding: 30px 20px; }
    .sdm-cta-newsletter-wrap { flex-direction: column; }
    .sdm-cta-newsletter-wrap button { justify-content: center; }
    .sdm-next-event-info { padding: 24px; }
    .sdm-youtube-info { padding: 20px; }
}

@media (max-width: 576px) {
    .sdm-hero { min-height: 550px; }
    .sdm-hero-greeting { font-size: 1.2rem; }
    .sdm-hero-buttons { flex-direction: column; align-items: center; }
    .sdm-hero-buttons .sdm-btn { width: 100%; max-width: 280px; justify-content: center; }
    .sdm-gallery-grid {
        grid-template-columns: 1fr;
        grid-auto-rows: 220px;
    }
    .sdm-testimonial-card { padding: 24px; }
    .sdm-stat-number { font-size: 2rem; }
    .sdm-cta-card { padding: 28px 20px; }
}
</style>

<!-- ==================== HOMEPAGE SCRIPTS ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* Hero Slider */
    var slides = document.querySelectorAll('.sdm-hero-slide');
    var current = 0;
    if (slides.length > 1) {
        setInterval(function() {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 6000);
    }

    /* Stats Counter */
    var counters = document.querySelectorAll('.sdm-stat-number');
    var statsObserved = false;
    if (counters.length > 0) {
        var statsSection = document.getElementById('stats');
        var statsObserver = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting && !statsObserved) {
                statsObserved = true;
                counters.forEach(function(counter) {
                    var target = parseInt(counter.getAttribute('data-target'), 10);
                    var duration = 2000;
                    var start = 0;
                    var startTime = null;
                    function animate(timestamp) {
                        if (!startTime) startTime = timestamp;
                        var progress = Math.min((timestamp - startTime) / duration, 1);
                        var eased = 1 - Math.pow(1 - progress, 3);
                        counter.textContent = Math.floor(eased * target) + '+';
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            counter.textContent = target + '+';
                        }
                    }
                    requestAnimationFrame(animate);
                });
            }
        }, { threshold: 0.3 });
        statsObserver.observe(statsSection);
    }

    /* Countdown Timer */
    var cdEl = document.getElementById('sdmCountdown');
    if (cdEl) {
        var nextSunday = new Date();
        nextSunday.setDate(nextSunday.getDate() + ((7 - nextSunday.getDay()) % 7 || 7));
        nextSunday.setHours(9, 0, 0, 0);
        function updateCountdown() {
            var now = new Date();
            var diff = nextSunday - now;
            if (diff <= 0) {
                document.getElementById('cdDays').textContent = '00';
                document.getElementById('cdHours').textContent = '00';
                document.getElementById('cdMins').textContent = '00';
                document.getElementById('cdSecs').textContent = '00';
                return;
            }
            var d = Math.floor(diff / (1000 * 60 * 60 * 24));
            var h = Math.floor((diff / (1000 * 60 * 60)) % 24);
            var m = Math.floor((diff / (1000 * 60)) % 60);
            var s = Math.floor((diff / 1000) % 60);
            document.getElementById('cdDays').textContent = d < 10 ? '0' + d : d;
            document.getElementById('cdHours').textContent = h < 10 ? '0' + h : h;
            document.getElementById('cdMins').textContent = m < 10 ? '0' + m : m;
            document.getElementById('cdSecs').textContent = s < 10 ? '0' + s : s;
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    /* Testimonials Carousel */
    var testCards = document.querySelectorAll('.sdm-testimonial-card');
    var testDots = document.getElementById('sdmTestDots');
    var testIdx = 0;
    if (testCards.length > 0 && testDots) {
        testCards.forEach(function(_, i) {
            var dot = document.createElement('div');
            dot.className = 'sdm-testimonial-dot' + (i === 0 ? ' active' : '');
            dot.addEventListener('click', function() { showTestimonial(i); });
            testDots.appendChild(dot);
        });

        function showTestimonial(idx) {
            testCards[testIdx].classList.remove('active');
            testDots.children[testIdx].classList.remove('active');
            testIdx = idx;
            testCards[testIdx].classList.add('active');
            testDots.children[testIdx].classList.add('active');
        }

        document.getElementById('sdmTestPrev').addEventListener('click', function() {
            showTestimonial(testIdx === 0 ? testCards.length - 1 : testIdx - 1);
        });

        document.getElementById('sdmTestNext').addEventListener('click', function() {
            showTestimonial((testIdx + 1) % testCards.length);
        });

        setInterval(function() {
            showTestimonial((testIdx + 1) % testCards.length);
        }, 6000);
    }

    /* Bible Verse */
    var defaultVerses = [
        { text: 'For the kingdom of God is not a matter of talk but of power.', ref: '1 Corinthians 4:20' },
        { text: 'I can do all things through Christ who strengthens me.', ref: 'Philippians 4:13' },
        { text: 'The Lord is my shepherd; I shall not want.', ref: 'Psalm 23:1' },
        { text: 'Trust in the Lord with all your heart, and lean not on your own understanding.', ref: 'Proverbs 3:5' },
        { text: 'For God has not given us a spirit of fear, but of power and of love and of a sound mind.', ref: '2 Timothy 1:7' },
        { text: 'Be strong and courageous. Do not be afraid; do not be discouraged, for the Lord your God will be with you wherever you go.', ref: 'Joshua 1:9' },
        { text: 'And we know that in all things God works for the good of those who love him, who have been called according to his purpose.', ref: 'Romans 8:28' },
        { text: 'The Lord will fight for you; you need only to be still.', ref: 'Exodus 14:14' },
        { text: 'But those who hope in the Lord will renew their strength. They will soar on wings like eagles.', ref: 'Isaiah 40:31' },
        { text: 'Come to me, all you who are weary and burdened, and I will give you rest.', ref: 'Matthew 11:28' }
    ];
    var verseBtn = document.getElementById('sdmNewVerse');
    if (verseBtn) {
        verseBtn.addEventListener('click', function() {
            var v = defaultVerses[Math.floor(Math.random() * defaultVerses.length)];
            document.getElementById('sdmVerseText').textContent = '"' + v.text + '"';
            document.getElementById('sdmVerseRef').textContent = '- ' + v.ref;
        });
    }

    /* Hero Particles */
    var particleContainer = document.getElementById('heroParticles');
    if (particleContainer) {
        for (var i = 0; i < 30; i++) {
            var p = document.createElement('div');
            p.style.cssText = 'position:absolute;width:' + (Math.random() * 4 + 2) + 'px;height:' + (Math.random() * 4 + 2) + 'px;background:rgba(251,191,36,' + (Math.random() * 0.3 + 0.1) + ');border-radius:50%;top:' + (Math.random() * 100) + '%;left:' + (Math.random() * 100) + '%;animation:sdmParticleFloat ' + (Math.random() * 8 + 6) + 's ease-in-out infinite ' + (Math.random() * 4) + 's;';
            particleContainer.appendChild(p);
        }
        var style = document.createElement('style');
        style.textContent = '@keyframes sdmParticleFloat { 0%,100%{transform:translateY(0) translateX(0);opacity:0.3} 25%{transform:translateY(-30px) translateX(10px);opacity:0.7} 50%{transform:translateY(-20px) translateX(-15px);opacity:0.5} 75%{transform:translateY(-40px) translateX(5px);opacity:0.8} }';
        document.head.appendChild(style);
    }
});
</script>
