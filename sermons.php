<?php
$pageTitle = 'Sermons | Salem Dominion Ministries';
$currentPage = 'sermons';
$pageDescription = 'Watch and listen to powerful sermons from Apostle Faty Musasizi and guest speakers at Salem Dominion Ministries.';

require_once 'config.php';
require_once 'db_connection.php';

$pdo = Database::getInstance()->getPdo();

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
    if ($pdo) {
        $where = "WHERE status = 'published'";
        $params = [];

        if ($category_filter) {
            $where .= " AND category = ?";
            $params[] = $category_filter;
        }
        if ($search) {
            $where .= " AND (title LIKE ? OR preacher LIKE ? OR description LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        if ($series_filter) {
            $where .= " AND series = ?";
            $params[] = $series_filter;
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM sermons {$where}");
        if ($countStmt) {
            $countStmt->execute($params);
            $total_sermons = $countStmt->fetchColumn();
            $total_pages = max(1, ceil($total_sermons / $per_page));
            $page = min($page, $total_pages);
        }

        $query = "SELECT * FROM sermons {$where} ORDER BY sermon_date DESC, created_at DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($query);
        if ($stmt) {
            $finalParams = array_merge($params, [$per_page, $offset]);
            $stmt->execute($finalParams);
            $sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($page === 1 && empty($search) && empty($category_filter) && empty($series_filter)) {
            $featStmt = $pdo->prepare("SELECT * FROM sermons WHERE status = 'published' AND is_featured = 1 ORDER BY sermon_date DESC LIMIT 1");
            if ($featStmt) {
                $featStmt->execute();
                $featured_sermon = $featStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            if (!$featured_sermon && !empty($sermons)) {
                $featured_sermon = $sermons[0];
                $sermons = array_slice($sermons, 1);
            }
        }

        $catStmt = $pdo->prepare("SELECT DISTINCT category FROM sermons WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category");
        if ($catStmt) {
            $catStmt->execute();
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $serStmt = $pdo->prepare("SELECT DISTINCT series FROM sermons WHERE status = 'published' AND series IS NOT NULL AND series != '' ORDER BY series");
        if ($serStmt) {
            $serStmt->execute();
            $series_list = $serStmt->fetchAll(PDO::FETCH_ASSOC);
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

.sdm-interactions { border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px; }
.sdm-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sdm-action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 10px;
    background: #fff; color: #475569; font-size: 0.85rem; font-weight: 600;
    font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s;
    text-decoration: none; white-space: nowrap;
}
.sdm-action-btn:hover { border-color: #0ea5e9; color: #0ea5e9; background: rgba(14,165,233,0.05); }
.sdm-action-btn.liked { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }
.sdm-action-btn.liked i { animation: sdmHeartPop 0.3s ease; }
@keyframes sdmHeartPop { 0%{transform:scale(1)} 50%{transform:scale(1.3)} 100%{transform:scale(1)} }
.sdm-action-btn i { font-size: 0.9rem; }
.sdm-share-menu { position: relative; display: inline-block; }
.sdm-share-dropdown {
    display: none; position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%);
    background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    padding: 8px; z-index: 100; min-width: 180px;
}
.sdm-share-dropdown.show { display: block; animation: sdmFadeIn 0.2s ease; }
@keyframes sdmFadeIn { from{opacity:0;transform:translateX(-50%) translateY(4px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
.sdm-share-dropdown a {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    border-radius: 8px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif;
    color: #475569; text-decoration: none; transition: background 0.2s;
}
.sdm-share-dropdown a:hover { background: #f1f5f9; }
.sdm-share-dropdown a i { width: 20px; text-align: center; }
.sdm-share-dropdown .fa-whatsapp { color: #25d366; }
.sdm-share-dropdown .fa-facebook-f { color: #1877f2; }
.sdm-share-dropdown .fa-twitter { color: #1da1f2; }
.sdm-share-dropdown .fa-telegram { color: #0088cc; }
.sdm-share-dropdown .fa-link { color: #64748b; }
.sdm-comments-section { margin-top: 20px; }
.sdm-comments-header { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.sdm-comments-header span { background: #0ea5e9; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-weight: 600; }
.sdm-comment-form { display: flex; gap: 12px; margin-bottom: 20px; }
.sdm-comment-form .avatar {
    width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7);
    display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;
    font-size: 0.85rem; flex-shrink: 0; font-family: 'Montserrat', sans-serif;
}
.sdm-comment-form .form-body { flex: 1; }
.sdm-comment-form textarea {
    width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 12px;
    font-size: 0.9rem; font-family: 'Montserrat', sans-serif; resize: vertical;
    min-height: 60px; transition: border-color 0.3s; outline: none; color: #0f172a;
}
.sdm-comment-form textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.sdm-comment-form .char-count { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 4px; }
.sdm-comment-form .form-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
.sdm-comment-form .btn-post {
    padding: 8px 20px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff;
    border: none; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: 'Montserrat', sans-serif; transition: all 0.3s;
}
.sdm-comment-form .btn-post:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
.sdm-comment-form .btn-post:disabled { opacity: 0.5; cursor: not-allowed; }
.sdm-comment-form .btn-cancel {
    padding: 8px 16px; background: transparent; color: #64748b; border: 2px solid #e2e8f0;
    border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: 'Montserrat', sans-serif;
}
.sdm-comment-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; animation: sdmFadeIn 0.3s ease; }
.sdm-comment-item .avatar {
    width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #fbbf24, #f59e0b);
    display: flex; align-items: center; justify-content: center; color: #0f172a; font-weight: 700;
    font-size: 0.75rem; flex-shrink: 0; font-family: 'Montserrat', sans-serif;
}
.sdm-comment-item .comment-body { flex: 1; }
.sdm-comment-item .comment-author { font-weight: 700; color: #0f172a; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; }
.sdm-comment-item .comment-time { color: #94a3b8; font-size: 0.75rem; margin-left: 8px; }
.sdm-comment-item .comment-text { color: #475569; font-size: 0.9rem; line-height: 1.6; margin-top: 4px; word-wrap: break-word; }
.sdm-comment-item .comment-delete { color: #94a3b8; font-size: 0.75rem; cursor: pointer; margin-top: 4px; border: none; background: none; padding: 0; font-family: 'Montserrat', sans-serif; }
.sdm-comment-item .comment-delete:hover { color: #ef4444; }
.sdm-login-prompt { text-align: center; padding: 16px; background: #f8fafc; border-radius: 12px; color: #64748b; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; }
.sdm-login-prompt a { color: #0ea5e9; font-weight: 700; text-decoration: none; }
.sdm-login-prompt a:hover { text-decoration: underline; }
.sdm-load-more { display: block; width: 100%; padding: 10px; border: 2px dashed #e2e8f0; border-radius: 10px; background: transparent; color: #64748b; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s; text-align: center; }
.sdm-load-more:hover { border-color: #0ea5e9; color: #0ea5e9; }
.sdm-copied-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #0f172a; color: #fff; padding: 10px 24px; border-radius: 10px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; z-index: 9999; animation: sdmToastIn 0.3s ease; }
@keyframes sdmToastIn { from{opacity:0;transform:translateX(-50%) translateY(10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
@media(max-width:480px) { .sdm-actions { gap: 8px; } .sdm-action-btn { padding: 7px 12px; font-size: 0.8rem; } }
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
                        $ytId = ($featured_sermon['media_type'] ?? '') === 'youtube' ? extractYouTubeId($featured_sermon['media_url'] ?? '') : null;
                        if ($ytId): ?>
                            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>" frameborder="0" allowfullscreen loading="lazy"></iframe>
                        <?php elseif (!empty($featured_sermon['media_url']) && ($featured_sermon['media_type'] ?? '') === 'video'): ?>
                            <video controls poster="<?= htmlspecialchars(getYouTubeThumbnail($featured_sermon['media_url'] ?? '')) ?>">
                                <source src="<?= htmlspecialchars($featured_sermon['media_url']) ?>" type="video/mp4">
                            </video>
                        <?php elseif (!empty($featured_sermon['media_url']) && ($featured_sermon['media_type'] ?? '') === 'audio'): ?>
                            <audio controls style="width:100%;">
                                <source src="<?= htmlspecialchars($featured_sermon['media_url']) ?>" type="audio/mpeg">
                            </audio>
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
                $thumb = !empty($sermon['thumbnail']) ? htmlspecialchars($sermon['thumbnail']) : ((!empty($sermon['media_url']) && $sermon['media_type'] === 'youtube' && extractYouTubeId($sermon['media_url'])) ? 'https://img.youtube.com/vi/' . extractYouTubeId($sermon['media_url']) . '/mqdefault.jpg' : 'assets/church-choir-worship.jpeg');
                $sYtId = ($sermon['media_type'] ?? '') === 'youtube' ? extractYouTubeId($sermon['media_url'] ?? '') : null;
                $hasVideo = $sYtId || (!empty($sermon['media_url']) && in_array($sermon['media_type'] ?? '', ['video', 'youtube']));
                $hasAudio = !empty($sermon['audio_url']);
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-delay="<?= ($idx % 3) * 100 ?>">
                <div class="sermon-card">
                    <div class="thumb-wrap" style="cursor:pointer;" onclick="openSermonModal(<?= htmlspecialchars(json_encode([
                        'id' => $sermon['id'],
                        'title' => $sermon['title'],
                        'preacher' => $sermon['preacher'] ?? 'Apostle Faty Musasizi',
                        'date' => formatDate($sermon['sermon_date'] ?? $sermon['created_at'], 'F j, Y'),
                        'description' => $sermon['description'] ?? '',
                        'video_url' => $sermon['media_url'] ?? '',
                        'audio_url' => $sermon['audio_url'] ?? '',
                        'category' => $sermon['category'] ?? '',
                        'series' => $sermon['series'] ?? '',
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
            <div class="modal-body p-0">
                <div id="sermonModalBody"></div>
                <div class="p-4" id="sermonInteractionsWrap">
                    <div class="sdm-interactions">
                        <div class="sdm-actions">
                            <button class="sdm-action-btn sdm-like-btn" data-sdm-type="sermon"><i class="far fa-heart"></i> <span class="like-count sdm-like-count">0</span></button>
                            <button class="sdm-action-btn sdm-toggle-comments"><i class="far fa-comment"></i> <span class="sdm-comment-count">0</span></button>
                            <div class="sdm-share-menu">
                                <button class="sdm-action-btn sdm-share-btn"><i class="fas fa-share-alt"></i> <span class="sdm-share-count">0</span></button>
                                <div class="sdm-share-dropdown">
                                    <a href="#" class="sdm-share-link" data-platform="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                    <a href="#" class="sdm-share-link" data-platform="facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
                                    <a href="#" class="sdm-share-link" data-platform="twitter"><i class="fab fa-twitter"></i> Twitter</a>
                                    <a href="#" class="sdm-share-link" data-platform="telegram"><i class="fab fa-telegram"></i> Telegram</a>
                                    <a href="#" class="sdm-share-link" data-platform="link"><i class="fas fa-link"></i> Copy Link</a>
                                </div>
                            </div>
                        </div>
                        <div class="sdm-comments-section" style="display:none;">
                            <h6 class="sdm-comments-header"><i class="fas fa-comments"></i> Comments <span>0</span></h6>
                            <div class="sdm-comment-form">
                                <div class="avatar"><?= !empty($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'],0,1)) : 'G' ?></div>
                                <div class="form-body">
                                    <form class="sdm-comment-form-form">
                                        <textarea placeholder="<?= !empty($_SESSION['user_logged_in']) ? 'Share your thoughts...' : 'Log in to comment...' ?>" <?= empty($_SESSION['user_logged_in']) ? 'disabled' : '' ?> maxlength="2000"></textarea>
                                        <div class="char-count">0/2000</div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn-post" <?= empty($_SESSION['user_logged_in']) ? 'disabled' : '' ?>>Post Comment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="sdm-comments-list"></div>
                            <button class="sdm-load-more" style="display:none;">Load more comments</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-dark border-0 text-white">
                <small id="sermonModalMeta" class="me-auto" style="font-family:'Montserrat',sans-serif;color:#94a3b8;"></small>
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var sdmCurrentSermon = { type: 'sermon', id: 0, csrf: '<?= $csrfToken ?>', loggedIn: <?= !empty($_SESSION['user_logged_in']) ? 'true' : 'false' ?> };
function sdmHeaders() { return { 'Content-Type': 'application/json', 'X-CSRF-Token': sdmCurrentSermon.csrf }; }
function sdmVisitorHash() { var h = localStorage.getItem('sdm_vh'); if (!h) { h = 'v_' + Math.random().toString(36).substring(2) + Date.now().toString(36); localStorage.setItem('sdm_vh', h); } return h; }
function sdmEscapeHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
function sdmTimeAgo(dt) { var diff = Math.floor(Date.now()/1000) - Math.floor(new Date(dt).getTime()/1000); if (diff < 60) return 'Just now'; if (diff < 3600) return Math.floor(diff/60) + 'm ago'; if (diff < 86400) return Math.floor(diff/3600) + 'h ago'; if (diff < 604800) return Math.floor(diff/86400) + 'd ago'; return new Date(dt).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }

var sdmCommentsPage = 1, sdmLoadingComments = false;
function sdmLoadComments(page) {
    if (sdmLoadingComments) return;
    sdmLoadingComments = true;
    var s = sdmCurrentSermon;
    fetch('api.php?action=get_comments&content_type=' + s.type + '&content_id=' + s.id + '&page=' + page + '&limit=10')
    .then(function(r){return r.json();}).then(function(res) {
        sdmLoadingComments = false;
        var list = document.querySelector('#sermonInteractionsWrap .sdm-comments-list');
        var hdr = document.querySelector('#sermonInteractionsWrap .sdm-comments-header');
        if (!list) return;
        if (page === 1) list.innerHTML = '';
        if (hdr) hdr.innerHTML = '<i class="fas fa-comments"></i> Comments <span>' + (res.total||0) + '</span>';
        if (!res.data || res.data.length === 0) {
            if (page === 1) list.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:16px;font-size:0.85rem;font-family:Montserrat,sans-serif;">No comments yet. Be the first!</p>';
            var mb = list.parentElement.querySelector('.sdm-load-more'); if(mb) mb.style.display='none';
            return;
        }
        res.data.forEach(function(c) {
            var ini = c.user_name.split(' ').map(function(w){return w[0]}).join('').substring(0,2).toUpperCase();
            var del = (sdmCurrentSermon.loggedIn && c.user_id == <?= $_SESSION['user_id'] ?? 0 ?>) ? '<button class="sdm-comment-delete comment-delete" data-id="'+c.id+'"><i class="fas fa-trash-alt me-1"></i>Delete</button>' : '';
            list.insertAdjacentHTML('beforeend', '<div class="sdm-comment-item"><div class="avatar">'+ini+'</div><div class="comment-body"><div><span class="comment-author">'+sdmEscapeHtml(c.user_name)+'</span><span class="comment-time">'+sdmTimeAgo(c.created_at)+'</span></div><div class="comment-text">'+sdmEscapeHtml(c.comment)+'</div>'+del+'</div></div>');
        });
        var mb = list.parentElement.querySelector('.sdm-load-more');
        if (mb) { mb.style.display = (page >= (res.pagination?res.pagination.total_pages:1)) ? 'none' : 'block'; }
    }).catch(function(){ sdmLoadingComments = false; });
}
function sdmRefreshCounts() {
    var s = sdmCurrentSermon;
    fetch('api.php?action=get_counts&content_type=' + s.type + '&content_id=' + s.id)
    .then(function(r){return r.json();}).then(function(res) {
        if (!res.success) return;
        var wrap = document.querySelector('#sermonInteractionsWrap');
        if (!wrap) return;
        var lk = wrap.querySelector('.sdm-like-count'); if(lk) lk.textContent = res.likes||0;
        var cm = wrap.querySelector('.sdm-comment-count'); if(cm) cm.textContent = res.comments||0;
        var sh = wrap.querySelector('.sdm-share-count'); if(sh) sh.textContent = res.shares||0;
        if (res.user_liked) { var btn = wrap.querySelector('.sdm-like-btn'); if(btn){btn.classList.add('liked');btn.querySelector('i').className='fas fa-heart';} }
    });
}
function sdmResetInteractions() {
    sdmCommentsPage = 1;
    var wrap = document.querySelector('#sermonInteractionsWrap');
    if (!wrap) return;
    var lk = wrap.querySelector('.sdm-like-count'); if(lk) lk.textContent='0';
    var cm = wrap.querySelector('.sdm-comment-count'); if(cm) cm.textContent='0';
    var sh = wrap.querySelector('.sdm-share-count'); if(sh) sh.textContent='0';
    var btn = wrap.querySelector('.sdm-like-btn'); if(btn){btn.classList.remove('liked');btn.querySelector('i').className='far fa-heart';}
    var list = wrap.querySelector('.sdm-comments-list'); if(list) list.innerHTML='';
    var sec = wrap.querySelector('.sdm-comments-section'); if(sec) sec.style.display='none';
    var mb = wrap.querySelector('.sdm-load-more'); if(mb) mb.style.display='none';
    var hdr = wrap.querySelector('.sdm-comments-header'); if(hdr) hdr.innerHTML='<i class="fas fa-comments"></i> Comments <span>0</span>';
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.sdm-like-btn')) {
        e.preventDefault();
        var s = sdmCurrentSermon;
        fetch('api.php?action=toggle_like', {method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:s.type,content_id:s.id,visitor_hash:sdmVisitorHash()})})
        .then(function(r){return r.json();}).then(function(res){
            if(!res.success){if(res.message)alert(res.message);return;}
            var wrap = document.querySelector('#sermonInteractionsWrap');
            var ct = wrap.querySelector('.like-count'); if(ct) ct.textContent=res.count||0;
            var btn = wrap.querySelector('.sdm-like-btn');
            btn.classList.toggle('liked',res.liked);
            btn.querySelector('i').className = res.liked ? 'fas fa-heart' : 'far fa-heart';
        });
        return;
    }
    if (e.target.closest('.sdm-toggle-comments')) {
        e.preventDefault();
        var sec = document.querySelector('#sermonInteractionsWrap .sdm-comments-section');
        if(sec){ sec.style.display = sec.style.display==='none'?'block':'none'; if(sec.style.display!=='none' && sec.dataset.loaded!=='1'){sdmLoadComments(1);sec.dataset.loaded='1';} }
        return;
    }
    if (e.target.closest('.sdm-share-btn')) {
        e.preventDefault();
        var dd = e.target.closest('.sdm-share-menu').querySelector('.sdm-share-dropdown');
        document.querySelectorAll('.sdm-share-dropdown.show').forEach(function(d){if(d!==dd)d.classList.remove('show');});
        dd.classList.toggle('show');
        return;
    }
    if (!e.target.closest('.sdm-share-dropdown')) { document.querySelectorAll('.sdm-share-dropdown.show').forEach(function(d){d.classList.remove('show');}); }
    if (e.target.closest('.sdm-share-link')) {
        e.preventDefault();
        var link = e.target.closest('.sdm-share-link');
        var platform = link.dataset.platform;
        var shareText = document.title, shareUrl = window.location.href, url = '';
        if(platform==='whatsapp') url='https://wa.me/?text='+encodeURIComponent(shareText+' '+shareUrl);
        else if(platform==='facebook') url='https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(shareUrl);
        else if(platform==='twitter') url='https://twitter.com/intent/tweet?url='+encodeURIComponent(shareUrl)+'&text='+encodeURIComponent(shareText);
        else if(platform==='telegram') url='https://t.me/share/url?url='+encodeURIComponent(shareUrl)+'&text='+encodeURIComponent(shareText);
        else if(platform==='link'){navigator.clipboard.writeText(shareUrl).then(function(){sdmShowCopied();});}
        if(url) window.open(url,'_blank','width=600,height=400');
        fetch('api.php?action=record_share',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:sdmCurrentSermon.type,content_id:sdmCurrentSermon.id,platform:platform})})
        .then(function(r){return r.json();}).then(function(res){if(res.success){var sh=document.querySelector('#sermonInteractionsWrap .sdm-share-count');if(sh)sh.textContent=res.count||0;}});
        document.querySelectorAll('.sdm-share-dropdown.show').forEach(function(d){d.classList.remove('show');});
        return;
    }
    if (e.target.closest('.sdm-comment-delete')) {
        e.preventDefault();
        if(!confirm('Delete this comment?'))return;
        var cid = e.target.closest('.sdm-comment-delete').dataset.id;
        fetch('api.php?action=delete_comment',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({comment_id:cid})})
        .then(function(r){return r.json();}).then(function(res){if(res.success){sdmLoadComments(1);sdmRefreshCounts();}else{alert(res.message||'Failed.');}});
        return;
    }
    if (e.target.closest('.sdm-load-more')) { e.preventDefault(); sdmCommentsPage++; sdmLoadComments(sdmCommentsPage); return; }
});
document.addEventListener('submit', function(e) {
    var form = e.target.closest('.sdm-comment-form-form');
    if (!form) return;
    e.preventDefault();
    if (!sdmCurrentSermon.loggedIn) { alert('Please log in to comment.'); return; }
    var ta = form.querySelector('textarea'), btn = form.querySelector('.btn-post'), text = ta.value.trim();
    if (!text) return;
    btn.disabled = true;
    fetch('api.php?action=add_comment',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:sdmCurrentSermon.type,content_id:sdmCurrentSermon.id,comment:text})})
    .then(function(r){return r.json();}).then(function(res){btn.disabled=false;if(!res.success){alert(res.message||'Failed.');return;}ta.value='';var ct=form.querySelector('.char-count');if(ct)ct.textContent='0/2000';sdmLoadComments(1);sdmRefreshCounts();}).catch(function(){btn.disabled=false;});
});
document.addEventListener('input', function(e) { if(e.target.closest('.sdm-comment-form textarea')){var ct=e.target.closest('.sdm-comment-form').querySelector('.char-count');if(ct)ct.textContent=e.target.value.length+'/2000';} });
function sdmShowCopied(){var t=document.createElement('div');t.className='sdm-copied-toast';t.textContent='Link copied!';document.body.appendChild(t);setTimeout(function(){t.remove();},2000);}

function openSermonModal(data) {
    document.getElementById('sermonModalTitle').textContent = data.title;
    sdmResetInteractions();
    sdmCurrentSermon.id = data.id;
    var html = '';
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
    var meta = data.preacher + ' &middot; ' + data.date;
    if (data.category) meta += ' &middot; ' + data.category;
    if (data.series) meta += ' &middot; Series: ' + data.series;
    document.getElementById('sermonModalMeta').innerHTML = meta;
    new bootstrap.Modal(document.getElementById('sermonModal')).show();
    sdmRefreshCounts();
}
</script>

<?php include 'components/footer.php'; ?>
