<?php
$pageTitle = 'Gallery | Salem Dominion Ministries';
$currentPage = 'gallery';
$pageDescription = 'Browse photos and videos from worship services, events, and community life at Salem Dominion Ministries.';

require_once 'config.php';
require_once 'db_connection.php';

$conn = createDatabaseConnection();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;
$category_filter = trim($_GET['category'] ?? '');
$album_filter = trim($_GET['album'] ?? '');

$gallery_items = [];
$total_items = 0;
$total_pages = 1;
$categories = [];
$albums = [];
$show_db = false;

$default_gallery = [
    ['src' => 'assets/church-choir-worship.jpeg', 'title' => 'Church Choir Worship', 'category' => 'Worship'],
    ['src' => 'assets/praise-worship-team.jpeg', 'title' => 'Praise & Worship Team', 'category' => 'Worship'],
    ['src' => 'assets/apostle-faty-preaching.jpeg', 'title' => 'Apostle Faty Preaching', 'category' => 'Preaching'],
    ['src' => 'assets/ourmembers.jpeg', 'title' => 'Our Members', 'category' => 'Community'],
    ['src' => 'assets/children-celebrating-Z18oVWUU.jpeg', 'title' => 'Children Celebrating', 'category' => 'Children'],
    ['src' => 'assets/kids-supports-are-welcome.jpeg', 'title' => 'Kids - Support Is Welcome', 'category' => 'Children'],
    ['src' => 'assets/support-children-now-Dqa2JhXn.jpeg', 'title' => 'Support Children Now', 'category' => 'Children'],
];

try {
    if ($conn) {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'gallery'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $show_db = true;

            $catStmt = $conn->prepare("SELECT DISTINCT category, COUNT(*) as count FROM gallery WHERE status = 'published' AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY category");
            if ($catStmt) {
                $catStmt->execute();
                $categories = $catStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $catStmt->close();
            }

            $albStmt = $conn->prepare("SELECT DISTINCT album_name, COUNT(*) as count FROM gallery WHERE status = 'published' AND album_name IS NOT NULL AND album_name != '' GROUP BY album_name ORDER BY album_name");
            if ($albStmt) {
                $albStmt->execute();
                $albums = $albStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $albStmt->close();
            }

            $where = "WHERE g.status = 'published'";
            $params = [];
            $types = '';
            if ($category_filter) {
                $where .= " AND g.category = ?";
                $params[] = $category_filter;
                $types .= 's';
            }
            if ($album_filter) {
                $where .= " AND g.album_name = ?";
                $params[] = $album_filter;
                $types .= 's';
            }

            $countStmt = $conn->prepare("SELECT COUNT(*) FROM gallery g {$where}");
            if ($countStmt) {
                if (!empty($params)) $countStmt->bind_param($types, ...$params);
                $countStmt->execute();
                $total_items = $countStmt->get_result()->fetch_row()[0];
                $total_pages = max(1, ceil($total_items / $per_page));
                $page = min($page, $total_pages);
                $countStmt->close();
            }

            $query = "SELECT g.*, CONCAT(u.first_name, ' ', u.last_name) as uploader_name FROM gallery g LEFT JOIN users u ON g.uploaded_by = u.id {$where} ORDER BY g.created_at DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $fp = array_merge($params, [$per_page, $offset]);
                $ft = $types . 'ii';
                $stmt->bind_param($ft, ...$fp);
                $stmt->execute();
                $gallery_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        }
    }
} catch (Exception $e) {
    error_log("Gallery page error: " . $e->getMessage());
}

include 'components/header.php';
?>

<style>
.gallery-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(14,165,233,0.75)), url('assets/hero-choir-6lo-hX_h.jpg') center/cover no-repeat;
    padding: 100px 0 60px;
    color: #fff;
    text-align: center;
}
.gallery-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; }
.gallery-hero p { font-family: 'Montserrat', sans-serif; font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 15px auto 0; }

.album-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 30px; }
.album-tab { border: 2px solid #e2e8f0; background: #fff; padding: 8px 20px; border-radius: 30px; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.album-tab:hover { border-color: #0ea5e9; color: #0ea5e9; }
.album-tab.active { background: linear-gradient(135deg, #0ea5e9, #0284c7); border-color: #0ea5e9; color: #fff; box-shadow: 0 4px 15px rgba(14,165,233,0.3); }
.album-tab .count { background: rgba(255,255,255,0.2); padding: 1px 8px; border-radius: 10px; font-size: 0.7rem; }

.masonry-grid { columns: 3; column-gap: 16px; }
.masonry-item { break-inside: avoid; margin-bottom: 16px; border-radius: 14px; overflow: hidden; position: relative; cursor: pointer; }
.masonry-item img { width: 100%; display: block; transition: transform 0.5s ease; }
.masonry-item:hover img { transform: scale(1.05); }
.masonry-item .overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px 16px 16px; background: linear-gradient(transparent, rgba(0,0,0,0.85)); color: #fff; opacity: 0; transition: opacity 0.3s ease; }
.masonry-item:hover .overlay { opacity: 1; }
.masonry-item .overlay h6 { font-family: 'Montserrat', sans-serif; font-size: 0.85rem; margin: 0; font-weight: 600; }
.masonry-item .overlay small { font-size: 0.7rem; color: #cbd5e1; }
.masonry-item .view-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%) scale(0); width: 50px; height: 50px; background: rgba(251,191,36,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0f172a; font-size: 1.1rem; transition: all 0.3s ease; }
.masonry-item:hover .view-btn { transform: translate(-50%,-50%) scale(1); }
.masonry-item .video-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 60px; height: 60px; background: rgba(251,191,36,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0f172a; font-size: 1.3rem; box-shadow: 0 4px 20px rgba(251,191,36,0.4); }
.masonry-item .type-badge { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-family: 'Montserrat', sans-serif; }

.lightbox { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 9999; align-items: center; justify-content: center; }
.lightbox.active { display: flex; }
.lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
.lightbox .close-lb { position: absolute; top: 20px; right: 20px; color: #fff; font-size: 2rem; cursor: pointer; background: none; border: none; z-index: 10; }
.lightbox .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); color: #fff; font-size: 2rem; cursor: pointer; background: rgba(255,255,255,0.1); border: none; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: background 0.3s; }
.lightbox .nav-btn:hover { background: rgba(255,255,255,0.2); }
.lightbox .nav-btn.prev { left: 20px; }
.lightbox .nav-btn.next { right: 20px; }
.lightbox .lb-title { position: absolute; bottom: 30px; color: #fff; font-family: 'Montserrat', sans-serif; font-size: 0.95rem; text-align: center; max-width: 80vw; }
.lightbox .lb-counter { position: absolute; top: 20px; left: 20px; color: rgba(255,255,255,0.7); font-family: 'Montserrat', sans-serif; font-size: 0.85rem; }

.btn-gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 600; border: none; border-radius: 10px; padding: 10px 24px; font-family: 'Montserrat', sans-serif; }
.btn-gold:hover { background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; transform: translateY(-2px); }
.empty-state { text-align: center; padding: 80px 20px; }
.empty-state h3 { font-family: 'Playfair Display', serif; color: #0f172a; }

@media(max-width:992px) { .masonry-grid { columns: 2; } }
@media(max-width:576px) { .masonry-grid { columns: 1; } .gallery-hero h1 { font-size: 2rem; } }
</style>

<section class="gallery-hero" data-aos="fade-in">
    <div class="container">
        <h1 data-aos="fade-up">Gallery</h1>
        <p data-aos="fade-up" data-delay="100">Visual memories from our worship, events, and community life</p>
    </div>
</section>

<section style="padding: 40px 0 60px;">
    <div class="container">
        <div class="album-tabs" data-aos="fade-up">
            <a href="?album=&category=" class="album-tab <?= empty($album_filter) && empty($category_filter) ? 'active' : '' ?>">
                <i class="fas fa-images"></i> All
            </a>
            <?php if ($show_db && !empty($albums)): ?>
                <?php foreach ($albums as $alb): ?>
                <a href="?album=<?= urlencode($alb['album_name']) ?>" class="album-tab <?= $album_filter === $alb['album_name'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($alb['album_name']) ?>
                    <span class="count"><?= $alb['count'] ?></span>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $defaultCats = array_unique(array_column($default_gallery, 'category'));
                foreach ($defaultCats as $dc): ?>
                <a href="?category=<?= urlencode($dc) ?>" class="album-tab <?= $category_filter === $dc ? 'active' : '' ?>">
                    <?= htmlspecialchars($dc) ?>
                    <span class="count"><?= count(array_filter($default_gallery, fn($g) => $g['category'] === $dc)) ?></span>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($show_db && !empty($gallery_items)): ?>
        <div class="masonry-grid" data-aos="fade-up">
            <?php foreach ($gallery_items as $idx => $item):
                $isVideo = in_array(strtolower(pathinfo($item['file_path'] ?? '', PATHINFO_EXTENSION)), ['mp4', 'webm', 'ogg']);
            ?>
            <div class="masonry-item" onclick="openLightbox(<?= $idx ?>)" data-title="<?= htmlspecialchars($item['title'] ?? '') ?>" data-src="<?= htmlspecialchars($item['file_path'] ?? '') ?>">
                <?php if ($isVideo): ?>
                    <video src="<?= htmlspecialchars($item['file_path']) ?>" preload="metadata" style="width:100%;display:block;" muted></video>
                    <div class="video-overlay"><i class="fas fa-play"></i></div>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($item['file_path'] ?? '') ?>" alt="<?= htmlspecialchars($item['title'] ?? 'Gallery image') ?>" loading="lazy">
                <?php endif; ?>
                <div class="view-btn"><i class="fas fa-expand"></i></div>
                <?php if ($item['type'] ?? '' === 'video'): ?>
                    <span class="type-badge"><i class="fas fa-video me-1"></i>Video</span>
                <?php endif; ?>
                <div class="overlay">
                    <h6><?= htmlspecialchars($item['title'] ?? '') ?></h6>
                    <?php if (!empty($item['category'])): ?>
                        <small><?= htmlspecialchars($item['category']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-center mt-5">
            <div class="d-flex gap-2">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&album=<?= urlencode($album_filter) ?>&category=<?= urlencode($category_filter) ?>" class="btn btn-gold btn-sm"><i class="fas fa-chevron-left me-1"></i>Prev</a>
                <?php endif; ?>
                <span class="btn btn-sm btn-outline-secondary" style="pointer-events:none;">Page <?= $page ?> of <?= $total_pages ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&album=<?= urlencode($album_filter) ?>&category=<?= urlencode($category_filter) ?>" class="btn btn-gold btn-sm">Next<i class="fas fa-chevron-right ms-1"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="masonry-grid" data-aos="fade-up">
            <?php
            $displayGallery = $default_gallery;
            if (!empty($category_filter)) {
                $displayGallery = array_filter($displayGallery, fn($g) => $g['category'] === $category_filter);
            }
            foreach ($displayGallery as $idx => $item): ?>
            <div class="masonry-item" onclick="openLightbox(<?= $idx ?>)" data-title="<?= htmlspecialchars($item['title']) ?>" data-src="<?= htmlspecialchars($item['src']) ?>">
                <img src="<?= htmlspecialchars($item['src']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" loading="lazy">
                <div class="view-btn"><i class="fas fa-expand"></i></div>
                <div class="overlay">
                    <h6><?= htmlspecialchars($item['title']) ?></h6>
                    <small><?= htmlspecialchars($item['category']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!$show_db && empty($displayGallery)): ?>
        <div class="empty-state" data-aos="fade-up">
            <div style="font-size:5rem;color:#cbd5e1;margin-bottom:20px;"><i class="fas fa-images"></i></div>
            <h3>Gallery Coming Soon</h3>
            <p style="color:#64748b;">We are curating beautiful moments from our ministry. Check back soon!</p>
            <a href="index.php" class="btn btn-gold mt-3"><i class="fas fa-home me-2"></i>Return Home</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="lightbox" id="lightbox" onclick="closeLightbox(event)">
    <span class="lb-counter" id="lbCounter"></span>
    <button class="close-lb" onclick="closeLightbox(event)">&times;</button>
    <button class="nav-btn prev" onclick="navLightbox(-1, event)"><i class="fas fa-chevron-left"></i></button>
    <img id="lbImage" src="" alt="">
    <div class="lb-title" id="lbTitle"></div>
    <button class="nav-btn next" onclick="navLightbox(1, event)"><i class="fas fa-chevron-right"></i></button>
</div>

<script>
const allItems = [];
document.querySelectorAll('.masonry-item').forEach(item => {
    allItems.push({
        src: item.getAttribute('data-src'),
        title: item.getAttribute('data-title')
    });
});
let currentLbIndex = 0;

function openLightbox(idx) {
    currentLbIndex = idx;
    updateLightbox();
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
    if (e.target === document.getElementById('lightbox') || e.target.closest('.close-lb')) {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }
}

function navLightbox(dir, e) {
    e.stopPropagation();
    currentLbIndex = (currentLbIndex + dir + allItems.length) % allItems.length;
    updateLightbox();
}

function updateLightbox() {
    const item = allItems[currentLbIndex];
    if (!item) return;
    const img = document.getElementById('lbImage');
    if (item.src.match(/\.(mp4|webm|ogg)$/i)) {
        img.style.display = 'none';
        let vid = document.getElementById('lbVideo');
        if (!vid) {
            vid = document.createElement('video');
            vid.id = 'lbVideo';
            vid.controls = true;
            vid.autoplay = true;
            vid.style.maxWidth = '90vw';
            vid.style.maxHeight = '85vh';
            vid.style.borderRadius = '8px';
            img.parentNode.insertBefore(vid, img.nextSibling);
        }
        vid.src = item.src;
        vid.style.display = 'block';
    } else {
        let vid = document.getElementById('lbVideo');
        if (vid) vid.style.display = 'none';
        img.src = item.src;
        img.style.display = 'block';
    }
    document.getElementById('lbTitle').textContent = item.title;
    document.getElementById('lbCounter').textContent = (currentLbIndex + 1) + ' / ' + allItems.length;
}

document.addEventListener('keydown', function(e) {
    if (!document.getElementById('lightbox').classList.contains('active')) return;
    if (e.key === 'Escape') { document.getElementById('lightbox').classList.remove('active'); document.body.style.overflow = ''; }
    if (e.key === 'ArrowLeft') navLightbox(-1, e);
    if (e.key === 'ArrowRight') navLightbox(1, e);
});
</script>

<?php include 'components/footer.php'; ?>
