<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'Our Ministries - Salem Dominion Ministries';
$currentPage = 'ministries';

$ministries = [];
$categories = [];

try {
    $pdo = Database::getInstance()->getPdo();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT category FROM ministries WHERE is_active = 1 AND category IS NOT NULL ORDER BY category");
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT m.*, l.name as leader_name FROM ministries m LEFT JOIN leadership l ON m.leader_id = l.id WHERE m.is_active = 1 ORDER BY m.category ASC, m.name ASC");
            $ministries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
} catch (Exception $e) {
    error_log("Ministries page DB error: " . $e->getMessage());
}

$defaultMinistries = [
    ["name" => "Children's Ministry", "category" => "Children", "description" => "Nurturing the next generation with the love of God through age-appropriate Bible stories, worship, games, and character building. Our children's ministry creates a safe and fun environment where kids encounter God.", "leader" => "Sister Halima", "meeting_schedule" => "Sundays 9:00 AM - 10:30 AM", "icon" => "fa-child"],
    ["name" => "Youth Ministry", "category" => "Youth", "description" => "Empowering young people to live bold, purposeful lives for Christ. We address real-life issues through the lens of faith, building a generation of leaders who are on fire for God.", "leader" => "Youth Pastors", "meeting_schedule" => "Fridays 5:00 PM - 7:00 PM", "icon" => "fa-graduation-cap"],
    ["name" => "Women's Ministry", "category" => "Women", "description" => "A sisterhood of women encouraging one another in faith, motherhood, and purpose. We provide mentorship, counseling, prayer, and fellowship to strengthen women in their walk with God.", "leader" => "Pastor Joyce Nabulya", "meeting_schedule" => "Saturdays 2:00 PM - 4:00 PM", "icon" => "fa-venus"],
    ["name" => "Men's Fellowship", "category" => "Men", "description" => "Building godly men who lead with integrity, serve their families, and impact their communities. We focus on spiritual growth, accountability, and leadership development.", "leader" => "Men's Leadership", "meeting_schedule" => "Saturdays 7:00 AM - 9:00 AM", "icon" => "fa-mars"],
    ["name" => "Worship & Music", "category" => "Worship", "description" => "Leading the congregation into God's presence through powerful, spirit-led worship. Our worship team is dedicated to creating an atmosphere where heaven touches earth.", "leader" => "Pastor Jotham Bright Mulinde", "meeting_schedule" => "Wednesdays 6:00 PM - 8:00 PM", "icon" => "fa-music"],
    ["name" => "Prayer Ministry", "category" => "Prayer", "description" => "The engine room of our ministry. We hold fervent prayer sessions for individuals, families, the nation, and the world. Join us in standing in the gap through intercession.", "leader" => "Prayer Warriors", "meeting_schedule" => "Tuesdays & Thursdays 5:00 AM & 6:00 PM", "icon" => "fa-hands-praying"],
    ["name" => "Community Outreach", "category" => "Outreach", "description" => "Extending God's love beyond our church walls through community service, hospital visits, prison ministry, food distribution, and evangelism across Iganga and beyond.", "leader" => "Outreach Team", "meeting_schedule" => "Monthly - Last Saturday", "icon" => "fa-hand-holding-heart"],
    ["name" => "Prophetic School", "category" => "Outreach", "description" => "Training believers to hear from God, operate in spiritual gifts, and walk in the prophetic. Our Prophetic School equips ministers with tools for effective spiritual ministry.", "leader" => "Apostle Irene Mirembe", "meeting_schedule" => "Saturdays 10:00 AM - 12:00 PM", "icon" => "fa-eye"],
];

$displayMinistries = !empty($ministries) ? $ministries : $defaultMinistries;

function getMinistryIcon($category) {
    $icons = [
        "children" => "fa-child",
        "youth" => "fa-graduation-cap",
        "women" => "fa-venus",
        "men" => "fa-mars",
        "worship" => "fa-music",
        "prayer" => "fa-hands-praying",
        "outreach" => "fa-hand-holding-heart",
    ];
    return $icons[strtolower($category)] ?? "fa-church";
}

function getMinistryColor($category) {
    $colors = [
        "children" => "#10b981",
        "youth" => "#8b5cf6",
        "women" => "#ec4899",
        "men" => "#3b82f6",
        "worship" => "#fbbf24",
        "prayer" => "#0ea5e9",
        "outreach" => "#f97316",
    ];
    return $colors[strtolower($category)] ?? "#0f172a";
}

include 'components/header.php';
?>

<style>
    .ministries-hero {
        background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(14,165,233,0.65) 100%), url('assets/praise-worship-team.jpeg');
        background-size: cover; background-position: center; min-height: 55vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .ministries-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .ministries-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .ministries-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .ministries-hero p { font-size: 1.2rem; opacity: 0.9; }
    .section-gap { padding: 80px 0; }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #fbbf24, #0ea5e9); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .filter-btn { display: inline-block; padding: 8px 20px; border-radius: 50px; border: 2px solid #e2e8f0; background: #fff; color: #475569; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.3s ease; margin: 4px; }
    .filter-btn:hover, .filter-btn.active { background: linear-gradient(135deg, #0ea5e9, #38bdf8); color: #fff; border-color: #0ea5e9; box-shadow: 0 4px 15px rgba(14,165,233,0.3); }
    .ministry-card { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.4s ease; height: 100%; border-bottom: 4px solid transparent; }
    .ministry-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); }
    .ministry-card-top { padding: 2rem 2rem 1rem; text-align: center; }
    .ministry-icon-wrap { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.8rem; color: #fff; }
    .ministry-card-top h5 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: #0f172a; margin-bottom: 0.5rem; }
    .ministry-category-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #fff; }
    .ministry-card-body { padding: 0 2rem 1.5rem; }
    .ministry-card-body p { color: #64748b; font-size: 0.92rem; line-height: 1.7; margin-bottom: 1.25rem; }
    .ministry-meta { display: flex; flex-direction: column; gap: 0.6rem; }
    .ministry-meta-item { display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: #475569; }
    .ministry-meta-item i { color: #0ea5e9; width: 16px; text-align: center; font-size: 0.8rem; }
    .cta-ministries { background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff; text-align: center; position: relative; overflow: hidden; }
    .cta-ministries::before { content: ''; position: absolute; top: 0; left: -100%; width: 300%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent); animation: shimm 12s infinite; }
    @keyframes shimm { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .cta-ministries h2 { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,2.8rem); margin-bottom: 1rem; position: relative; }
    .cta-ministries p { font-size: 1.15rem; opacity: 0.9; margin-bottom: 2rem; position: relative; }
    .cta-btn-gold { display: inline-flex; align-items: center; gap: 10px; padding: 14px 36px; background: #fbbf24; color: #0f172a; border-radius: 50px; font-weight: 700; text-decoration: none; transition: all 0.3s ease; position: relative; font-size: 1.05rem; }
    .cta-btn-gold:hover { background: #fff; transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
</style>

<section class="ministries-hero">
    <div class="ministries-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1>Our Ministries</h1>
        <p>Find your place to serve, grow, and make a difference</p>
    </div>
</section>

<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Get Involved</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">Explore our ministries and discover where God is calling you</p>

        <div class="text-center mb-5" data-aos="fade-up" data-aos-delay="150">
            <button class="filter-btn active" onclick="filterMinistries('all', this)">All Ministries</button>
            <?php foreach ($categories as $cat): ?>
                <button class="filter-btn" onclick="filterMinistries('<?= htmlspecialchars(strtolower($cat)) ?>', this)"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <button class="filter-btn" onclick="filterMinistries('children', this)">Children</button>
                <button class="filter-btn" onclick="filterMinistries('youth', this)">Youth</button>
                <button class="filter-btn" onclick="filterMinistries('women', this)">Women</button>
                <button class="filter-btn" onclick="filterMinistries('men', this)">Men</button>
                <button class="filter-btn" onclick="filterMinistries('worship', this)">Worship</button>
                <button class="filter-btn" onclick="filterMinistries('prayer', this)">Prayer</button>
                <button class="filter-btn" onclick="filterMinistries('outreach', this)">Outreach</button>
            <?php endif; ?>
        </div>

        <div class="row g-4" id="ministriesGrid">
            <?php foreach ($displayMinistries as $i => $ministry):
                $name = htmlspecialchars($ministry['name'] ?? '');
                $category = htmlspecialchars($ministry['category'] ?? '');
                $catLower = strtolower($ministry['category'] ?? '');
                $description = htmlspecialchars($ministry['description'] ?? '');
                $leader = htmlspecialchars($ministry['leader'] ?? $ministry['leader_name'] ?? 'Ministry Team');
                $schedule = htmlspecialchars($ministry['meeting_schedule'] ?? $ministry['schedule'] ?? '');
                $icon = $ministry['icon'] ?? getMinistryIcon($catLower);
                $color = getMinistryColor($catLower);
            ?>
            <div class="col-lg-4 col-md-6 ministry-item" data-category="<?= htmlspecialchars($catLower) ?>" data-aos="fade-up" data-aos-delay="<?= ($i % 3 + 1) * 100 ?>">
                <div class="ministry-card" style="border-bottom-color: <?= $color ?>;">
                    <div class="ministry-card-top">
                        <div class="ministry-icon-wrap" style="background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>dd);">
                            <i class="fas <?= $icon ?>"></i>
                        </div>
                        <h5><?= $name ?></h5>
                        <span class="ministry-category-badge" style="background: <?= $color ?>80;"><?= $category ?></span>
                    </div>
                    <div class="ministry-card-body">
                        <p><?= $description ?></p>
                        <div class="ministry-meta">
                            <div class="ministry-meta-item"><i class="fas fa-user"></i> <?= $leader ?></div>
                            <?php if ($schedule): ?>
                            <div class="ministry-meta-item"><i class="fas fa-clock"></i> <?= $schedule ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-gap cta-ministries">
    <div class="container position-relative">
        <h2 data-aos="fade-up">Join a Ministry Today</h2>
        <p data-aos="fade-up" data-aos-delay="100">Every member has a role. Find yours and start making an eternal impact.</p>
        <div data-aos="fade-up" data-aos-delay="200">
            <a href="contact.php" class="cta-btn-gold"><i class="fas fa-envelope"></i> Contact Us to Join</a>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>

<script>
function filterMinistries(category, btn) {
    document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.ministry-item').forEach(function(card) {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
