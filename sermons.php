<?php
$pageTitle = 'Sermons | Salem Dominion Ministries';
$currentPage = 'sermons';
$pageDescription = 'Watch and listen to powerful sermons from Apostle Faty Musasizi and guest speakers at Salem Dominion Ministries.';

require_once 'config.php';
require_once 'db_connection.php';

$conn = createDatabaseConnection();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;
$category_filter = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$series_filter = trim($_GET['series'] ?? '');
$view_mode = $_GET['view'] ?? 'grid';

$sermons = [];
$featured_sermon = null;
$total_sermons = 0;
$total_pages = 1;
$categories = [];
$series_list = [];

try {
    if ($conn) {
        $where = "WHERE status = 'published'";
        $params = [];
        $types = '';

        if ($category_filter) {
            $where .= " AND category = ?";
            $params[] = $category_filter;
            $types .= 's';
        }
        if ($search) {
            $where .= " AND (title LIKE ? OR preacher LIKE ? OR description LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        }
        if ($series_filter) {
            $where .= " AND series_name = ?";
            $params[] = $series_filter;
            $types .= 's';
        }

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM sermons {$where}");
        if ($countStmt) {
            if (!empty($params)) $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $total_sermons = $countStmt->get_result()->fetch_row()[0];
            $total_pages = max(1, ceil($total_sermons / $per_page));
            $page = min($page, $total_pages);
            $countStmt->close();
        }

        $query = "SELECT * FROM sermons {$where} ORDER BY sermon_date DESC, created_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $finalParams = array_merge($params, [$per_page, $offset]);
            $finalTypes = $types . 'ii';
            $stmt->bind_param($finalTypes, ...$finalParams);
            $stmt->execute();
            $sermons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        if ($page === 1 && empty($search) && empty($category_filter) && empty($series_filter)) {
            $featStmt = $conn->prepare("SELECT * FROM sermons WHERE status = 'published' AND is_featured = 1 ORDER BY sermon_date DESC LIMIT 1");
            if ($featStmt) {
                $featStmt->execute();
                $featured_sermon = $featStmt->get_result()->fetch_assoc();
                $featStmt->close();
            }
            if (!$featured_sermon && !empty($sermons)) {
                $featured_sermon = $sermons[0];
                $sermons = array_slice($sermons, 1);
            }
        }

        $catStmt = $conn->prepare("SELECT DISTINCT category FROM sermons WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category");
        if ($catStmt) {
            $catStmt->execute();
            $categories = $catStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $catStmt->close();
        }

        $serStmt = $conn->prepare("SELECT DISTINCT series_name FROM sermons WHERE status = 'published' AND series_name IS NOT NULL AND series_name != '' ORDER BY series_name");
        if ($serStmt) {
            $serStmt->execute();
            $series_list = $serStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $serStmt->close();
        }
    }
} catch (Exception $e) {
    error_log("Sermons page error: " . $e->getMessage());
}

include 'components/header.php';
?>

<style>
.sermons-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(14,165,233,0.75)), url('assets/hero-worship-CWyaH0tr.jpg') center/cover no-repeat;
    padding: 100px 0 60px;
    color: #fff;
    text-align: center;
}
.sermons-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; }
.sermons-hero p { font-family: 'Montserrat', sans-serif; font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 15px auto 0; }
.sermons-hero .scripture { font-style: italic; opacity: 0.8; margin-top: 20px; font-size: 0.95rem; }
.sermons-hero .scripture strong { color: #fbbf24; }
.filter-bar { background: #fff; border-radius: 16px; padding: 20px 30px; margin-top: -40px; position: relative; z-index: 10; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.filter-bar .form-control, .filter-bar .form-select { border-radius: 10px; border: 2px solid #e2e8f0; padding: 10px 16px; font-family: 'Montserrat', sans-serif; }
.filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
.btn-gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 600; border: none; border-radius: 10px; padding: 10px 24px; font-family: 'Montserrat', sans-serif; }
.btn-gold:hover { background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; transform: translateY(-2px); box-shadow: 0 5px 20px rgba(251,191,36,0.3); }
.featured-sermon { background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 20px; overflow: hidden; color: #fff; margin-bottom: 50px; }
.featured-sermon .video-wrap { position: relative; padding-top: 56.25%; background: #000; }
.featured-sermon .video-wrap iframe, .featured-sermon .video-wrap video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.featured-sermon .info { padding: 30px; }
.featured-sermon .info h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; }
.badge-category { background: rgba(251,191,36,0.15); color: #fbbf24; font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.sermon-card { background: #fff; border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.4s cubic-bezier(0.4,0,0.2,1); height: 100%; }
.sermon-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
.sermon-card .thumb-wrap { position: relative; overflow: hidden; height: 200px; background: #f1f5f9; }
.sermon-card .thumb-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.sermon-card:hover .thumb-wrap img { transform: scale(1.08); }
.sermon-card .play-btn { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 56px; height: 56px; background: rgba(251,191,36,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0f172a; font-size: 1.2rem; opacity: 0; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(251,191,36,0.4); }
.sermon-card:hover .play-btn { opacity: 1; }
.sermon-card .duration-badge { position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.75); color: #fff; font-size: 0.75rem; padding: 3px 8px; border-radius: 6px; font-family: 'Montserrat', sans-serif; }
.sermon-card .card-body { padding: 20px; }
.sermon-card .card-title { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.sermon-card .preacher { color: #0ea5e9; font-weight: 600; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; }
.sermon-card .sermon-meta { color: #64748b; font-size: 0.8rem; display: flex; align-items: center; gap: 12px; margin-top: 8px; font-family: 'Montserrat', sans-serif; }
.sermon-card .sermon-meta i { color: #fbbf24; }
.empty-state { text-align: center; padding: 80px 20px; }
.empty-state img { max-width: 400px; border-radius: 20px; margin-bottom: 30px; }
.empty-state h3 { font-family: 'Playfair Display', serif; color: #0f172a; }
.empty-state p { color: #64748b; }
.pagination .page-link { border-radius: 10px; margin: 0 3px; border: 2px solid #e2e8f0; color: #0f172a; font-family: 'Montserrat', sans-serif; font-weight: 500; }
.pagination .page-item.active .page-link { background: linear-gradient(135deg, #0ea5e9, #0284c7); border-color: #0ea5e9; color: #fff; }
.pagination .page-link:hover { background: #f1f5f9; border-color: #0ea5e9; color: #0ea5e9; }
.series-tag { background: rgba(14,165,233,0.1); color: #0ea5e9; font-size: 0.7rem; padding: 2px 8px; border-radius: 6px; font-weight: 600; }
@media(max-width:768px) { .sermons-hero h1 { font-size: 2rem; } .filter-bar { padding: 15px; } }
</style>

<section class="sermons-hero" data-aos="fade-in">
    <div class="container">
        <h1 data-aos="fade-up">Sermons</h1>
        <p data-aos="fade-up" data-delay="100">Be fed by the Word of God through powerful messages from our pulpit</p>
        <div class="scripture" data-aos="fade-up" data-delay="200">
            <i class="fas fa-book-bible me-2"></i>
            "Preach the word; be prepared in season and out of season." &mdash; <strong>2 Timothy 4:2</strong>
        </div>
    </div>
</section>

<section style="padding: 0 0 60px;">
    <div class="container">
        <div class="filter-bar" data-aos="fade-up">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark" style="font-family:'Montserrat',sans-serif;font-size:0.85rem;">
                        <i class="fas fa-search me-1 text-primary"></i>Search Sermons
                    </label>
                    <input type="text" name="search" class="form-control" placeholder="Search by title, preacher, or topic..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark" style="font-family:'Montserrat',sans-serif;font-size:0.85rem;">
                        <i class="fas fa-filter me-1 text-primary"></i>Category
                    </label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category_filter === $cat['category'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($cat['category'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark" style="font-family:'Montserrat',sans-serif;font-size:0.85rem;">
                        <i class="fas fa-layer-group me-1 text-primary"></i>Series
                    </label>
                    <select name="series" class="form-select">
                        <option value="">All Series</option>
                        <?php foreach ($series_list as $ser): ?>
                            <option value="<?= htmlspecialchars($ser['series_name']) ?>" <?= $series_filter === $ser['series_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ser['series_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-gold w-100">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <?php if ($featured_sermon): ?>
        <div class="featured-sermon mt-5" data-aos="fade-up">
            <div class="row g-0">
                <div class="col-lg-7">
                    <div class="video-wrap">
                        <?php
                        $ytId = extractYouTubeId($featured_sermon['video_url'] ?? '');
                        if ($ytId): ?>
                            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>" frameborder="0" allowfullscreen loading="lazy"></iframe>
                        <?php elseif (!empty($featured_sermon['video_url'])): ?>
                            <video controls poster="<?= htmlspecialchars(getYouTubeThumbnail($featured_sermon['video_url'] ?? '')) ?>">
                                <source src="<?= htmlspecialchars($featured_sermon['video_url']) ?>" type="video/mp4">
                            </video>
                        <?php else: ?>
                            <div style="display:flex;align-items:center;justify-content:center;height:100%;background:linear-gradient(135deg,#0ea5e9,#0f172a);">
                                <i class="fas fa-play-circle" style="font-size:4rem;color:#fbbf24;opacity:0.6;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="info d-flex flex-column justify-content-center h-100">
                        <span class="badge-category mb-2">Featured Sermon</span>
                        <h3><?= htmlspecialchars($featured_sermon['title']) ?></h3>
                        <p class="mb-2" style="color:#94a3b8;">
                            <i class="fas fa-user-tie me-1 text-warning"></i> <?= htmlspecialchars($featured_sermon['preacher'] ?? 'Apostle Faty Musasizi') ?>
                        </p>
                        <p class="mb-2" style="color:#94a3b8;">
                            <i class="fas fa-calendar me-1 text-warning"></i> <?= formatDate($featured_sermon['sermon_date'] ?? $featured_sermon['created_at'], 'F j, Y') ?>
                        </p>
                        <?php if (!empty($featured_sermon['category'])): ?>
                            <span class="badge-category mt-2" style="width:fit-content;"><?= htmlspecialchars(ucfirst($featured_sermon['category'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($featured_sermon['description'])): ?>
                            <p class="mt-3" style="color:#cbd5e1;font-size:0.9rem;line-height:1.7;">
                                <?= htmlspecialchars(truncate($featured_sermon['description'], 200)) ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($featured_sermon['audio_url'])): ?>
                        <div class="mt-3">
                            <audio controls style="width:100%;height:40px;">
                                <source src="<?= htmlspecialchars($featured_sermon['audio_url']) ?>" type="audio/mpeg">
                            </audio>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($sermons)): ?>
        <div class="row g-4 mt-2">
            <?php foreach ($sermons as $idx => $sermon):
                $thumb = !empty($sermon['thumbnail']) ? htmlspecialchars($sermon['thumbnail']) : ((!empty($sermon['video_url']) && extractYouTubeId($sermon['video_url'])) ? 'https://img.youtube.com/vi/' . extractYouTubeId($sermon['video_url']) . '/mqdefault.jpg' : 'assets/church-choir-worship.jpeg');
                $sYtId = extractYouTubeId($sermon['video_url'] ?? '');
                $hasVideo = $sYtId || !empty($sermon['video_url']);
                $hasAudio = !empty($sermon['audio_url']);
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-delay="<?= ($idx % 3) * 100 ?>">
                <div class="sermon-card">
                    <div class="thumb-wrap" style="cursor:pointer;" onclick="openSermonModal(<?= htmlspecialchars(json_encode([
                        'title' => $sermon['title'],
                        'preacher' => $sermon['preacher'] ?? 'Apostle Faty Musasizi',
                        'date' => formatDate($sermon['sermon_date'] ?? $sermon['created_at'], 'F j, Y'),
                        'description' => $sermon['description'] ?? '',
                        'video_url' => $sermon['video_url'] ?? '',
                        'audio_url' => $sermon['audio_url'] ?? '',
                        'category' => $sermon['category'] ?? '',
                        'series' => $sermon['series_name'] ?? '',
                        'duration' => $sermon['duration'] ?? '',
                        'yt_id' => $sYtId,
                    ])) ?>)">
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($sermon['title']) ?>" loading="lazy">
                        <?php if ($hasVideo || $hasAudio): ?>
                        <div class="play-btn">
                            <i class="fas <?= $hasVideo ? 'fa-play' : 'fa-headphones' ?>"></i>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($sermon['duration'])): ?>
                        <span class="duration-badge"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($sermon['duration']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <?php if (!empty($sermon['category'])): ?>
                                <span class="badge-category"><?= htmlspecialchars(ucfirst($sermon['category'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($sermon['series_name'])): ?>
                                <span class="series-tag"><?= htmlspecialchars($sermon['series_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars($sermon['title']) ?></h5>
                        <p class="preacher"><i class="fas fa-user-tie me-1"></i> <?= htmlspecialchars($sermon['preacher'] ?? 'Apostle Faty Musasizi') ?></p>
                        <div class="sermon-meta">
                            <span><i class="fas fa-calendar me-1"></i> <?= formatDate($sermon['sermon_date'] ?? $sermon['created_at']) ?></span>
                            <?php if (!empty($sermon['duration'])): ?>
                                <span><i class="fas fa-clock me-1"></i> <?= htmlspecialchars($sermon['duration']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($sermon['audio_url'])): ?>
                        <div class="mt-3">
                            <audio controls preload="none" style="width:100%;height:36px;">
                                <source src="<?= htmlspecialchars($sermon['audio_url']) ?>" type="audio/mpeg">
                            </audio>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center" data-aos="fade-up">
            <ul class="pagination">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search) ?>&series=<?= urlencode($series_filter) ?>"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search) ?>&series=<?= urlencode($series_filter) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search) ?>&series=<?= urlencode($series_filter) ?>"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state mt-5" data-aos="fade-up">
            <img src="assets/hero-worship-CWyaH0tr.jpg" alt="Worship" style="max-width:400px;border-radius:20px;">
            <h3 class="mt-4">Sermons Coming Soon</h3>
            <p>We are preparing powerful messages for your spiritual growth. Check back soon for new sermons!</p>
            <a href="index.php" class="btn btn-gold mt-3"><i class="fas fa-home me-2"></i>Return Home</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="modal fade" id="sermonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
            <div class="modal-header bg-dark text-white border-0">
                <h5 class="modal-title" id="sermonModalTitle" style="font-family:'Playfair Display',serif;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="sermonModalBody">
            </div>
            <div class="modal-footer bg-dark border-0 text-white">
                <small id="sermonModalMeta" class="me-auto" style="font-family:'Montserrat',sans-serif;color:#94a3b8;"></small>
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openSermonModal(data) {
    document.getElementById('sermonModalTitle').textContent = data.title;
    let html = '';
    if (data.yt_id) {
        html = '<div style="position:relative;padding-top:56.25%;"><iframe src="https://www.youtube.com/embed/' + data.yt_id + '?autoplay=1" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe></div>';
    } else if (data.audio_url) {
        html = '<div class="p-4" style="background:linear-gradient(135deg,#0f172a,#1e293b);min-height:200px;display:flex;align-items:center;justify-content:center;flex-direction:column;">' +
               '<i class="fas fa-headphones" style="font-size:3rem;color:#fbbf24;margin-bottom:20px;"></i>' +
               '<audio controls autoplay style="width:90%;"><source src="' + data.audio_url + '" type="audio/mpeg"></audio></div>';
    } else {
        html = '<div class="p-5 text-center" style="background:linear-gradient(135deg,#0f172a,#1e293b);">' +
               '<i class="fas fa-play-circle" style="font-size:4rem;color:#fbbf24;"></i>' +
               '<p class="text-white mt-3">Media not available</p></div>';
    }
    if (data.description) {
        html += '<div class="p-4"><p style="color:#475569;line-height:1.8;">' + data.description + '</p></div>';
    }
    document.getElementById('sermonModalBody').innerHTML = html;
    let meta = data.preacher + ' &middot; ' + data.date;
    if (data.category) meta += ' &middot; ' + data.category;
    if (data.series) meta += ' &middot; Series: ' + data.series;
    document.getElementById('sermonModalMeta').innerHTML = meta;
    new bootstrap.Modal(document.getElementById('sermonModal')).show();
}
</script>

<?php include 'components/footer.php'; ?>
