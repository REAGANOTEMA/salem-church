<?php
require_once 'config.php';
require_once 'db_connection.php';

$pageTitle = 'Leadership - Salem Dominion Ministries';
$currentPage = 'leadership';

$leaders = [];
$positions = [];

try {
    $pdo = Database::getInstance()->getPdo();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT position FROM leadership WHERE is_active = 1 ORDER BY position");
            $positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SELECT * FROM leadership WHERE is_active = 1 ORDER BY position ASC, name ASC");
            $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
} catch (Exception $e) {
    error_log("Leadership page DB error: " . $e->getMessage());
}

$defaultLeaders = [
    ['name' => 'Apostle Faty Musasizi', 'position' => 'Senior Pastor & Founder', 'photo' => 'assets/apostle-faty-preaching.jpeg', 'email' => 'info@salem-dominion-ministries.com', 'phone' => '+256 753 244 480', 'bio' => 'Apostle Faty Musasizi is the founder and Senior Pastor of Salem Dominion Ministries. Called by God with a powerful apostolic and prophetic mantle, she has led the ministry from its inception with just 5 members to over 500 members across multiple branches. Her ministry is marked by healing miracles, prophetic accuracy, and a deep passion for souls.'],
    ['name' => 'Pastor Jonathan Ngobi', 'position' => 'Associate Pastor', 'photo' => 'assets/pastor-jonathan-Ngobi-B-Ezegv1.jpeg', 'email' => '', 'phone' => '', 'bio' => 'Pastor Jonathan Ngobi serves faithfully as an Associate Pastor at Salem Dominion Ministries, supporting the vision of the house with dedication and spiritual leadership. He is known for his strong prayer life and commitment to discipleship.'],
    ['name' => 'Pastor Jotham Bright Mulinde', 'position' => 'Worship & Music Director', 'photo' => 'assets/pastor-jotham-Bright-Mulinde-Ca8YLs3V.jpeg', 'email' => '', 'phone' => '', 'bio' => 'Pastor Jotham Bright Mulinde leads the worship and music ministry at Salem Dominion Ministries. His anointed worship leads the congregation into powerful encounters with God. He is passionate about creating an atmosphere of worship that draws people closer to God.'],
    ['name' => 'Pastor Joyce Nabulya', 'position' => "Women's Ministry Leader", 'photo' => 'assets/PASTOR-NABULYA-JOYCE-BdB4SkbM.jpeg', 'email' => '', 'phone' => '', 'bio' => "Pastor Joyce Nabulya leads the Women's Ministry, nurturing and empowering women to walk in their God-given identity and purpose. She provides mentorship, counseling, and spiritual guidance to women of all ages."],
    ['name' => 'Apostle Irene Mirembe', 'position' => 'Prophetic Ministry Leader', 'photo' => 'assets/APOSTLE-IRENE-MIREMBE-CwWfzcRx.jpeg', 'email' => '', 'phone' => '', 'bio' => 'Apostle Irene Mirembe oversees the Prophetic School and prophetic ministry at Salem Dominion Ministries. With a strong prophetic gifting, she trains and equips believers to hear and operate in the gifts of the Spirit.'],
    ['name' => 'General Pastor', 'position' => 'General Pastor', 'photo' => 'assets/general-pastor.jpeg', 'email' => '', 'phone' => '', 'bio' => 'The General Pastor provides pastoral oversight and spiritual covering for the ministry. With years of experience in ministry, they provide wisdom, counsel, and apostolic guidance to the leadership team and congregation.'],
];

$displayLeaders = !empty($leaders) ? $leaders : $defaultLeaders;

include 'components/header.php';
?>

<style>
    .leadership-hero {
        background: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(14,165,233,0.65) 100%), url('assets/church-choir-worship.jpeg');
        background-size: cover; background-position: center; min-height: 55vh;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .leadership-hero::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, #fff, transparent); }
    .leadership-hero-content { position: relative; z-index: 2; text-align: center; color: #fff; padding: 0 20px; }
    .leadership-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2.5rem,6vw,3.5rem); font-weight: 900; }
    .leadership-hero p { font-size: 1.2rem; opacity: 0.9; }
    .section-gap { padding: 80px 0; }
    .section-title-custom { font-family: 'Playfair Display', serif; font-size: clamp(2rem,5vw,3rem); font-weight: 700; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
    .section-title-custom::after { content: ''; display: block; width: 80px; height: 4px; background: linear-gradient(90deg, #fbbf24, #0ea5e9); margin: 15px auto 0; border-radius: 2px; }
    .section-subtitle-custom { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
    .filter-btn {
        display: inline-block; padding: 8px 20px; border-radius: 50px; border: 2px solid #e2e8f0;
        background: #fff; color: #475569; font-size: 0.9rem; font-weight: 500; cursor: pointer;
        transition: all 0.3s ease; margin: 4px;
    }
    .filter-btn:hover, .filter-btn.active {
        background: linear-gradient(135deg, #0ea5e9, #38bdf8); color: #fff; border-color: #0ea5e9;
        box-shadow: 0 4px 15px rgba(14,165,233,0.3);
    }
    .leader-card {
        background: #fff; border-radius: 20px; overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.4s ease; position: relative;
    }
    .leader-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
    .leader-card-img { position: relative; overflow: hidden; height: 320px; }
    .leader-card-img img { width: 100%; height: 100%; object-fit: cover; object-position: top; transition: transform 0.5s ease; }
    .leader-card:hover .leader-card-img img { transform: scale(1.05); }
    .leader-card-img::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 50%;
        background: linear-gradient(to top, rgba(15,23,42,0.9), transparent);
    }
    .leader-card-badge {
        position: absolute; top: 15px; right: 15px; z-index: 2;
        background: rgba(15,23,42,0.75); color: #fbbf24; padding: 5px 14px;
        border-radius: 25px; font-size: 0.75rem; font-weight: 600; backdrop-filter: blur(10px);
    }
    .leader-card-body { padding: 1.5rem; text-align: center; }
    .leader-card-body h5 { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: #0f172a; margin-bottom: 0.25rem; }
    .leader-card-body .role { color: #fbbf24; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.75rem; }
    .leader-card-body .bio-excerpt { color: #64748b; font-size: 0.9rem; line-height: 1.6; margin-bottom: 1rem; }
    .leader-card-actions { display: flex; justify-content: center; gap: 10px; }
    .leader-card-actions a {
        width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        text-decoration: none; transition: all 0.3s ease; font-size: 0.85rem;
    }
    .leader-card-actions .btn-email { background: #0ea5e9; color: #fff; }
    .leader-card-actions .btn-email:hover { background: #0284c7; transform: translateY(-2px); }
    .leader-card-actions .btn-phone { background: #10b981; color: #fff; }
    .leader-card-actions .btn-phone:hover { background: #059669; transform: translateY(-2px); }
    .leader-card-actions .btn-bio { background: #fbbf24; color: #0f172a; }
    .leader-card-actions .btn-bio:hover { background: #f59e0b; transform: translateY(-2px); }
    .modal-leader-img { width: 100%; max-height: 350px; object-fit: cover; border-radius: 12px; }
    .modal-leader-name { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #0f172a; }
    .modal-leader-role { color: #fbbf24; font-weight: 600; }
    .modal-leader-bio { color: #475569; line-height: 1.8; }
</style>

<!-- Hero -->
<section class="leadership-hero">
    <div class="leadership-hero-content" data-aos="fade-up" data-aos-duration="1200">
        <h1>Our Leadership</h1>
        <p>Spirit-filled leaders guiding God's people with wisdom and love</p>
    </div>
</section>

<!-- Leaders Grid -->
<section class="section-gap">
    <div class="container">
        <h2 class="section-title-custom" data-aos="fade-up">Meet Our Leaders</h2>
        <p class="section-subtitle-custom" data-aos="fade-up" data-aos-delay="100">The dedicated team serving Salem Dominion Ministries</p>

        <!-- Filter Buttons -->
        <div class="text-center mb-5" data-aos="fade-up" data-aos-delay="150">
            <button class="filter-btn active" onclick="filterLeaders('all', this)">All Leaders</button>
            <?php foreach ($positions as $pos): ?>
                <button class="filter-btn" onclick="filterLeaders('<?= htmlspecialchars(strtolower($pos)) ?>', this)"><?= htmlspecialchars($pos) ?></button>
            <?php endforeach; ?>
            <?php if (empty($positions)): ?>
                <button class="filter-btn" onclick="filterLeaders('senior pastor', this)">Senior Pastor</button>
                <button class="filter-btn" onclick="filterLeaders('associate pastor', this)">Associate Pastor</button>
                <button class="filter-btn" onclick="filterLeaders('worship', this)">Worship</button>
                <button class="filter-btn" onclick="filterLeaders('women', this)">Women's Ministry</button>
                <button class="filter-btn" onclick="filterLeaders('prophetic', this)">Prophetic</button>
            <?php endif; ?>
        </div>

        <!-- Leaders Grid -->
        <div class="row g-4" id="leadersGrid">
            <?php foreach ($displayLeaders as $i => $leader):
                $name = htmlspecialchars($leader['name'] ?? '');
                $position = htmlspecialchars($leader['position'] ?? '');
                $photo = htmlspecialchars($leader['photo'] ?? 'assets/general-pastor.jpeg');
                $email = htmlspecialchars($leader['email'] ?? '');
                $phone = htmlspecialchars($leader['phone'] ?? '');
                $bio = htmlspecialchars($leader['bio'] ?? $leader['description'] ?? 'A faithful servant of God dedicated to the growth and wellbeing of Salem Dominion Ministries and its members.');
                $posLower = strtolower($leader['position'] ?? '');
            ?>
            <div class="col-lg-4 col-md-6 leader-item" data-position="<?= htmlspecialchars($posLower) ?>" data-aos="fade-up" data-aos-delay="<?= ($i % 3 + 1) * 100 ?>">
                <div class="leader-card">
                    <div class="leader-card-img">
                        <img src="<?= $photo ?>" alt="<?= $name ?>">
                        <span class="leader-card-badge"><i class="fas fa-cross me-1"></i> <?= $position ?></span>
                    </div>
                    <div class="leader-card-body">
                        <h5><?= $name ?></h5>
                        <p class="role"><?= $position ?></p>
                        <p class="bio-excerpt"><?= truncate($leader['bio'] ?? $leader['description'] ?? '', 120) ?></p>
                        <div class="leader-card-actions">
                            <?php if ($email): ?>
                                <a href="mailto:<?= $email ?>" class="btn-email" title="Email"><i class="fas fa-envelope"></i></a>
                            <?php endif; ?>
                            <?php if ($phone): ?>
                                <a href="tel:<?= $phone ?>" class="btn-phone" title="Call"><i class="fas fa-phone"></i></a>
                            <?php endif; ?>
                            <button class="btn-bio" title="View Bio" onclick="showBioModal('<?= $name ?>', '<?= $position ?>', '<?= $photo ?>', '<?= addslashes($leader['bio'] ?? $leader['description'] ?? '') ?>')"><i class="fas fa-user"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Bio Modal -->
<div class="modal fade" id="bioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff; border: none; padding: 1.5rem;">
                <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Leader Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4 text-center">
                        <img id="modalPhoto" src="" alt="" class="modal-leader-img">
                    </div>
                    <div class="col-md-8">
                        <h3 id="modalName" class="modal-leader-name"></h3>
                        <p id="modalRole" class="modal-leader-role mb-3"></p>
                        <p id="modalBio" class="modal-leader-bio"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>

<script>
function filterLeaders(position, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.leader-item').forEach(card => {
        if (position === 'all' || card.dataset.position.includes(position)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

function showBioModal(name, role, photo, bio) {
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalRole').textContent = role;
    document.getElementById('modalPhoto').src = photo;
    document.getElementById('modalPhoto').alt = name;
    document.getElementById('modalBio').textContent = bio;
    var modal = new bootstrap.Modal(document.getElementById('bioModal'));
    modal.show();
}
</script>
