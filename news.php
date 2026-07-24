<?php
$pageTitle = 'News | Salem Dominion Ministries';
$currentPage = 'news';
$pageDescription = 'Stay updated with the latest news, announcements, and happenings at Salem Dominion Ministries.';

require_once 'config.php';
require_once 'db_connection.php';

$pdo = Database::getInstance()->getPdo();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;
$category_filter = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');

$news_items = [];
$featured_news = null;
$total_news = 0;
$total_pages = 1;
$categories = [];
$recent_news = [];

try {
    if ($pdo) {
        $where = "WHERE n.status = 'published'";
        $params = [];

        if ($category_filter) {
            $where .= " AND n.category = ?";
            $params[] = $category_filter;
        }
        if ($search) {
            $where .= " AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)";
            $sp = "%{$search}%";
            $params[] = $sp;
            $params[] = $sp;
            $params[] = $sp;
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM news n {$where}");
        if ($countStmt) {
            $countStmt->execute($params);
            $total_news = $countStmt->fetchColumn();
            $total_pages = max(1, ceil($total_news / $per_page));
            $page = min($page, $total_pages);
        }

        $query = "SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as author_name FROM news n LEFT JOIN salemdominionmin_members.users u ON n.author_id = u.id {$where} ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($query);
        if ($stmt) {
            $fp = array_merge($params, [$per_page, $offset]);
            $stmt->execute($fp);
            $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($page === 1 && empty($search) && empty($category_filter)) {
            $featStmt = $pdo->prepare("SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as author_name FROM news n LEFT JOIN salemdominionmin_members.users u ON n.author_id = u.id WHERE n.status = 'published' AND n.is_featured = 1 ORDER BY n.created_at DESC LIMIT 1");
            if ($featStmt) {
                $featStmt->execute();
                $featured_news = $featStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            if (!$featured_news && !empty($news_items)) {
                $featured_news = $news_items[0];
                $news_items = array_slice($news_items, 1);
            }
        }

        $catStmt = $pdo->prepare("SELECT DISTINCT category, COUNT(*) as count FROM news WHERE status = 'published' AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY count DESC");
        if ($catStmt) {
            $catStmt->execute();
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $recStmt = $pdo->prepare("SELECT n.id, n.title, n.created_at, n.category, n.featured_image FROM news n WHERE n.status = 'published' ORDER BY n.created_at DESC LIMIT 5");
        if ($recStmt) {
            $recStmt->execute();
            $recent_news = $recStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    error_log("News page error: " . $e->getMessage());
}

include 'components/header.php';
?>

<style>
.news-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(14,165,233,0.75)), url('assets/hero1.jpeg') center/cover no-repeat;
    padding: 100px 0 60px;
    color: #fff;
    text-align: center;
}
.news-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; }
.news-hero p { font-family: 'Montserrat', sans-serif; font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 15px auto 0; }

.filter-bar { background: #fff; border-radius: 16px; padding: 20px 30px; margin-top: -40px; position: relative; z-index: 10; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.filter-bar .form-control, .filter-bar .form-select { border-radius: 10px; border: 2px solid #e2e8f0; padding: 10px 16px; font-family: 'Montserrat', sans-serif; }
.filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
.btn-gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 600; border: none; border-radius: 10px; padding: 10px 24px; font-family: 'Montserrat', sans-serif; }
.btn-gold:hover { background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; transform: translateY(-2px); }

.featured-news-card { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); margin-bottom: 50px; border: none; }
.featured-news-card .news-img { height: 400px; background: linear-gradient(135deg, #0ea5e9, #0f172a); position: relative; overflow: hidden; }
.featured-news-card .news-img img { width: 100%; height: 100%; object-fit: cover; }
.featured-news-card .featured-badge { position: absolute; top: 20px; left: 20px; background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; padding: 6px 16px; border-radius: 8px; font-weight: 700; font-size: 0.75rem; font-family: 'Montserrat', sans-serif; text-transform: uppercase; letter-spacing: 1px; }
.featured-news-card .news-body { padding: 30px; }
.featured-news-card .news-body h3 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #0f172a; margin-bottom: 12px; }
.featured-news-card .news-body h3:hover { color: #0ea5e9; }
.featured-news-card .news-body p { color: #475569; line-height: 1.8; }

.news-card { background: #fff; border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.4s cubic-bezier(0.4,0,0.2,1); height: 100%; display: flex; flex-direction: column; }
.news-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
.news-card .news-thumb { height: 200px; background: linear-gradient(135deg, #0ea5e9, #0f172a); position: relative; overflow: hidden; }
.news-card .news-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.news-card:hover .news-thumb img { transform: scale(1.08); }
.news-card .card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
.news-card .card-title { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.news-card .card-title:hover { color: #0ea5e9; }
.news-card .excerpt { color: #64748b; font-size: 0.85rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; flex: 1; }
.news-card .news-meta { color: #94a3b8; font-size: 0.8rem; display: flex; align-items: center; gap: 10px; margin-top: 10px; font-family: 'Montserrat', sans-serif; flex-wrap: wrap; }
.news-card .news-meta i { color: #0ea5e9; }
.badge-category { background: rgba(251,191,36,0.15); color: #d97706; font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Montserrat', sans-serif; }

.sidebar-card { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.sidebar-card h5 { font-family: 'Playfair Display', serif; color: #0f172a; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #f1f5f9; }
.sidebar-card .cat-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; color: #475569; cursor: pointer; transition: all 0.3s ease; text-decoration: none; }
.sidebar-card .cat-item:hover { color: #0ea5e9; padding-left: 5px; }
.sidebar-card .cat-item .count { background: #f1f5f9; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; color: #64748b; }

.sidebar-recent-item { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: all 0.3s ease; }
.sidebar-recent-item:hover { padding-left: 5px; }
.sidebar-recent-item .recent-thumb { width: 60px; height: 60px; border-radius: 10px; background: linear-gradient(135deg, #0ea5e9, #0f172a); overflow: hidden; flex-shrink: 0; }
.sidebar-recent-item .recent-thumb img { width: 100%; height: 100%; object-fit: cover; }
.sidebar-recent-item .recent-info h6 { font-family: 'Montserrat', sans-serif; font-size: 0.8rem; color: #0f172a; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
.sidebar-recent-item .recent-info small { color: #94a3b8; font-size: 0.7rem; }

.news-detail-modal .modal-content { border-radius: 16px; border: none; overflow: hidden; }
.news-detail-modal .modal-header { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; border: none; }
.news-detail-modal .modal-body { padding: 30px; max-height: 70vh; overflow-y: auto; }
.news-detail-modal .modal-body h2 { font-family: 'Playfair Display', serif; color: #0f172a; }
.news-detail-modal .modal-body .content { line-height: 1.9; color: #475569; }

.empty-state { text-align: center; padding: 80px 20px; }
.empty-state h3 { font-family: 'Playfair Display', serif; color: #0f172a; }

.pagination .page-link { border-radius: 10px; margin: 0 3px; border: 2px solid #e2e8f0; color: #0f172a; font-family: 'Montserrat', sans-serif; }
.pagination .page-item.active .page-link { background: linear-gradient(135deg, #0ea5e9, #0284c7); border-color: #0ea5e9; color: #fff; }

@media(max-width:768px) { .news-hero h1 { font-size: 2rem; } .filter-bar { padding: 15px; } .featured-news-card .news-img { height: 220px; } .featured-news-card .news-body h3 { font-size: 1.3rem; } }
@media(max-width:480px) { .featured-news-card .news-img { height: 180px; } }

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
.sdm-load-more { display: block; width: 100%; padding: 10px; border: 2px dashed #e2e8f0; border-radius: 10px; background: transparent; color: #64748b; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s; text-align: center; }
.sdm-load-more:hover { border-color: #0ea5e9; color: #0ea5e9; }
.sdm-copied-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #0f172a; color: #fff; padding: 10px 24px; border-radius: 10px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; z-index: 9999; animation: sdmToastIn 0.3s ease; }
@keyframes sdmToastIn { from{opacity:0;transform:translateX(-50%) translateY(10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
@media(max-width:480px) { .sdm-actions { gap: 8px; } .sdm-action-btn { padding: 7px 12px; font-size: 0.8rem; } }
</style>

<section class="news-hero" data-aos="fade-in">
    <div class="container">
        <h1 data-aos="fade-up">News & Updates</h1>
        <p data-aos="fade-up" data-delay="100">Stay connected with everything happening at Salem Dominion Ministries</p>
    </div>
</section>

<section style="padding: 0 0 60px;">
    <div class="container">

        <div class="filter-bar" data-aos="fade-up">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-dark" style="font-family:'Montserrat',sans-serif;font-size:0.85rem;"><i class="fas fa-search me-1 text-primary"></i>Search News</label>
                    <input type="text" name="search" class="form-control" placeholder="Search articles..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark" style="font-family:'Montserrat',sans-serif;font-size:0.85rem;"><i class="fas fa-filter me-1 text-primary"></i>Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category_filter === $cat['category'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($cat['category'])) ?> (<?= $cat['count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-gold w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>

        <div class="row mt-5">
            <div class="col-lg-8">
                <?php if ($featured_news): ?>
                <div class="featured-news-card" data-aos="fade-up">
                    <div class="news-img">
                        <?php if (!empty($featured_news['featured_image'])): ?>
                            <img src="<?= htmlspecialchars($featured_news['featured_image']) ?>" alt="<?= htmlspecialchars($featured_news['title']) ?>">
                        <?php else: ?>
                            <div style="display:flex;align-items:center;justify-content:center;height:100%;"><i class="fas fa-newspaper" style="font-size:4rem;color:rgba(255,255,255,0.2);"></i></div>
                        <?php endif; ?>
                        <span class="featured-badge"><i class="fas fa-star me-1"></i>Featured</span>
                    </div>
                    <div class="news-body">
                        <div class="mb-2">
                            <?php if (!empty($featured_news['category'])): ?>
                                <span class="badge-category"><?= htmlspecialchars(ucfirst($featured_news['category'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><a href="#" onclick="showNewsDetail(<?= htmlspecialchars(json_encode([
                            'id' => $featured_news['id'],
                            'title' => $featured_news['title'],
                            'content' => $featured_news['content'] ?? $featured_news['excerpt'] ?? '',
                            'date' => formatDate($featured_news['created_at'], 'F j, Y'),
                            'author' => !empty($featured_news['author_name']) ? $featured_news['author_name'] : 'Admin',
                            'category' => $featured_news['category'] ?? '',
                            'views' => $featured_news['views'] ?? 0,
                        ])) ?>);return false;" style="text-decoration:none;color:inherit;"><?= htmlspecialchars($featured_news['title']) ?></a></h3>
                        <p><?= htmlspecialchars(truncate($featured_news['content'] ?? $featured_news['excerpt'] ?? '', 400)) ?></p>
                        <div class="d-flex align-items-center gap-3 mt-3" style="color:#94a3b8;font-family:'Montserrat',sans-serif;font-size:0.85rem;">
                            <span><i class="fas fa-user me-1 text-primary"></i> <?= htmlspecialchars(!empty($featured_news['author_name']) ? $featured_news['author_name'] : 'Admin') ?></span>
                            <span><i class="fas fa-calendar me-1 text-primary"></i> <?= formatDate($featured_news['created_at']) ?></span>
                            <span><i class="fas fa-eye me-1 text-primary"></i> <?= $featured_news['views'] ?? 0 ?> views</span>
                        </div>
                        <div class="mt-3">
                            <a href="#" class="btn btn-gold btn-sm" onclick="showNewsDetail(<?= htmlspecialchars(json_encode([
                                'id' => $featured_news['id'],
                                'title' => $featured_news['title'],
                                'content' => $featured_news['content'] ?? $featured_news['excerpt'] ?? '',
                                'date' => formatDate($featured_news['created_at'], 'F j, Y'),
                                'author' => !empty($featured_news['author_name']) ? $featured_news['author_name'] : 'Admin',
                                'category' => $featured_news['category'] ?? '',
                                'views' => $featured_news['views'] ?? 0,
                            ])) ?>);return false;"><i class="fas fa-book-open me-2"></i>Read More</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($news_items)): ?>
                <div class="row g-4">
                    <?php foreach ($news_items as $idx => $news): ?>
                    <div class="col-md-6" data-aos="fade-up" data-delay="<?= ($idx % 2) * 100 ?>">
                        <div class="news-card">
                            <div class="news-thumb">
                                <?php if (!empty($news['featured_image'])): ?>
                                    <img src="<?= htmlspecialchars($news['featured_image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div style="display:flex;align-items:center;justify-content:center;height:100%;"><i class="fas fa-newspaper" style="font-size:2.5rem;color:rgba(255,255,255,0.2);"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($news['category'])): ?>
                                    <span class="badge-category mb-2"><?= htmlspecialchars(ucfirst($news['category'])) ?></span>
                                <?php endif; ?>
                                <h5 class="card-title"><a href="#" onclick="showNewsDetail(<?= htmlspecialchars(json_encode([
                                    'id' => $news['id'],
                                    'title' => $news['title'],
                                    'content' => $news['content'] ?? $news['excerpt'] ?? '',
                                    'date' => formatDate($news['created_at'], 'F j, Y'),
                                    'author' => !empty($news['author_name']) ? $news['author_name'] : 'Admin',
                                    'category' => $news['category'] ?? '',
                                    'views' => $news['views'] ?? 0,
                                ])) ?>);return false;" style="text-decoration:none;color:inherit;"><?= htmlspecialchars($news['title']) ?></a></h5>
                                <p class="excerpt"><?= htmlspecialchars(truncate($news['content'] ?? $news['excerpt'] ?? '', 150)) ?></p>
                                <div class="news-meta">
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars(!empty($news['author_name']) ? $news['author_name'] : 'Admin') ?></span>
                                    <span><i class="fas fa-calendar"></i> <?= formatDate($news['created_at']) ?></span>
                                    <span><i class="fas fa-eye"></i> <?= $news['views'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav class="mt-5 d-flex justify-content-center" data-aos="fade-up">
                    <ul class="pagination">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&category=<?= urlencode($category_filter) ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <div class="empty-state" data-aos="fade-up">
                    <div style="font-size:5rem;color:#cbd5e1;margin-bottom:20px;"><i class="fas fa-newspaper"></i></div>
                    <h3>No News Yet</h3>
                    <p style="color:#64748b;">Stay tuned for updates from Salem Dominion Ministries.</p>
                    <a href="index.php" class="btn btn-gold mt-3"><i class="fas fa-home me-2"></i>Return Home</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="sidebar-card mb-4" data-aos="fade-left">
                    <h5><i class="fas fa-folder-open me-2 text-primary"></i>Categories</h5>
                    <a href="?category=" class="cat-item <?= empty($category_filter) ? 'text-primary fw-bold' : '' ?>">
                        <span><i class="fas fa-list me-2"></i>All Categories</span>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="?category=<?= urlencode($cat['category']) ?>" class="cat-item <?= $category_filter === $cat['category'] ? 'text-primary fw-bold' : '' ?>">
                        <span><i class="fas fa-tag me-2"></i><?= htmlspecialchars(ucfirst($cat['category'])) ?></span>
                        <span class="count"><?= $cat['count'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="sidebar-card mb-4" data-aos="fade-left" data-delay="100">
                    <h5><i class="fas fa-clock me-2 text-primary"></i>Recent News</h5>
                    <?php foreach ($recent_news as $rn): ?>
                    <a href="?#" class="sidebar-recent-item" onclick="showNewsDetail(<?= htmlspecialchars(json_encode([
                        'id' => $rn['id'],
                        'title' => $rn['title'],
                        'content' => '',
                        'date' => formatDate($rn['created_at'], 'F j, Y'),
                        'author' => 'Admin',
                        'category' => $rn['category'] ?? '',
                        'views' => 0,
                    ])) ?>);return false;">
                        <div class="recent-thumb">
                            <?php if (!empty($rn['featured_image'])): ?>
                                <img src="<?= htmlspecialchars($rn['featured_image']) ?>" alt="<?= htmlspecialchars($rn['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <div style="display:flex;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,0.3);"><i class="fas fa-newspaper"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="recent-info">
                            <h6><?= htmlspecialchars(truncate($rn['title'], 60)) ?></h6>
                            <small><i class="fas fa-calendar me-1"></i><?= formatDate($rn['created_at']) ?></small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="sidebar-card" data-aos="fade-left" data-delay="200" style="background:linear-gradient(135deg, #0f172a, #1e293b);color:#fff;">
                    <h5 style="color:#fbbf24;border-bottom-color:rgba(255,255,255,0.1);"><i class="fas fa-envelope me-2"></i>Stay Updated</h5>
                    <p style="color:#94a3b8;font-size:0.9rem;">Subscribe to receive the latest news directly in your inbox.</p>
                    <form onsubmit="event.preventDefault();subscribe(event);">
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your email address" required style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;">
                        </div>
                        <button type="submit" class="btn btn-gold w-100"><i class="fas fa-paper-plane me-2"></i>Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade news-detail-modal" id="newsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newsDetailTitle" style="font-family:'Playfair Display',serif;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3" style="color:#94a3b8;font-family:'Montserrat',sans-serif;font-size:0.85rem;">
                    <span id="newsDetailMeta"></span>
                </div>
                <div id="newsDetailContent" class="content"></div>
                <div class="sdm-interactions">
                    <div class="sdm-actions">
                        <button class="sdm-action-btn sdm-like-btn"><i class="far fa-heart"></i> <span class="like-count sdm-like-count">0</span></button>
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
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var sdmCurrentNews = { type: 'news', id: 0, csrf: '<?= $csrfToken ?>', loggedIn: <?= !empty($_SESSION['user_logged_in']) ? 'true' : 'false' ?> };
function sdmHeaders() { return { 'Content-Type': 'application/json', 'X-CSRF-Token': sdmCurrentNews.csrf }; }
function sdmVisitorHash() { var h = localStorage.getItem('sdm_vh'); if (!h) { h = 'v_' + Math.random().toString(36).substring(2) + Date.now().toString(36); localStorage.setItem('sdm_vh', h); } return h; }
function sdmEscapeHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
function sdmTimeAgo(dt) { var diff = Math.floor(Date.now()/1000) - Math.floor(new Date(dt).getTime()/1000); if (diff < 60) return 'Just now'; if (diff < 3600) return Math.floor(diff/60) + 'm ago'; if (diff < 86400) return Math.floor(diff/3600) + 'h ago'; if (diff < 604800) return Math.floor(diff/86400) + 'd ago'; return new Date(dt).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }

var sdmCommentsPage = 1, sdmLoadingComments = false;
function sdmLoadComments(page) {
    if (sdmLoadingComments) return;
    sdmLoadingComments = true;
    var s = sdmCurrentNews;
    fetch('api.php?action=get_comments&content_type=' + s.type + '&content_id=' + s.id + '&page=' + page + '&limit=10')
    .then(function(r){return r.json();}).then(function(res) {
        sdmLoadingComments = false;
        var list = document.querySelector('#newsDetailModal .sdm-comments-list');
        var hdr = document.querySelector('#newsDetailModal .sdm-comments-header');
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
            var del = (sdmCurrentNews.loggedIn && c.user_id == <?= $_SESSION['user_id'] ?? 0 ?>) ? '<button class="sdm-comment-delete comment-delete" data-id="'+c.id+'"><i class="fas fa-trash-alt me-1"></i>Delete</button>' : '';
            list.insertAdjacentHTML('beforeend', '<div class="sdm-comment-item"><div class="avatar">'+ini+'</div><div class="comment-body"><div><span class="comment-author">'+sdmEscapeHtml(c.user_name)+'</span><span class="comment-time">'+sdmTimeAgo(c.created_at)+'</span></div><div class="comment-text">'+sdmEscapeHtml(c.comment)+'</div>'+del+'</div></div>');
        });
        var mb = list.parentElement.querySelector('.sdm-load-more');
        if (mb) { mb.style.display = (page >= (res.pagination?res.pagination.total_pages:1)) ? 'none' : 'block'; }
    }).catch(function(){ sdmLoadingComments = false; });
}
function sdmRefreshCounts() {
    var s = sdmCurrentNews;
    fetch('api.php?action=get_counts&content_type=' + s.type + '&content_id=' + s.id)
    .then(function(r){return r.json();}).then(function(res) {
        if (!res.success) return;
        var wrap = document.querySelector('#newsDetailModal .sdm-interactions');
        if (!wrap) return;
        var lk = wrap.querySelector('.sdm-like-count'); if(lk) lk.textContent = res.likes||0;
        var cm = wrap.querySelector('.sdm-comment-count'); if(cm) cm.textContent = res.comments||0;
        var sh = wrap.querySelector('.sdm-share-count'); if(sh) sh.textContent = res.shares||0;
        if (res.user_liked) { var btn = wrap.querySelector('.sdm-like-btn'); if(btn){btn.classList.add('liked');btn.querySelector('i').className='fas fa-heart';} }
    });
}
function sdmResetInteractions() {
    sdmCommentsPage = 1;
    var wrap = document.querySelector('#newsDetailModal .sdm-interactions');
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
    if (e.target.closest('.sdm-like-btn') && e.target.closest('#newsDetailModal')) {
        e.preventDefault();
        var s = sdmCurrentNews;
        fetch('api.php?action=toggle_like', {method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:s.type,content_id:s.id,visitor_hash:sdmVisitorHash()})})
        .then(function(r){return r.json();}).then(function(res){
            if(!res.success){if(res.message)alert(res.message);return;}
            var wrap = document.querySelector('#newsDetailModal .sdm-interactions');
            var ct = wrap.querySelector('.like-count'); if(ct) ct.textContent=res.count||0;
            var btn = wrap.querySelector('.sdm-like-btn');
            btn.classList.toggle('liked',res.liked);
            btn.querySelector('i').className = res.liked ? 'fas fa-heart' : 'far fa-heart';
        });
        return;
    }
    if (e.target.closest('.sdm-toggle-comments') && e.target.closest('#newsDetailModal')) {
        e.preventDefault();
        var sec = document.querySelector('#newsDetailModal .sdm-comments-section');
        if(sec){ sec.style.display = sec.style.display==='none'?'block':'none'; if(sec.style.display!=='none' && sec.dataset.loaded!=='1'){sdmLoadComments(1);sec.dataset.loaded='1';} }
        return;
    }
    if (e.target.closest('.sdm-share-btn') && e.target.closest('#newsDetailModal')) {
        e.preventDefault();
        var dd = e.target.closest('.sdm-share-menu').querySelector('.sdm-share-dropdown');
        document.querySelectorAll('#newsDetailModal .sdm-share-dropdown.show').forEach(function(d){if(d!==dd)d.classList.remove('show');});
        dd.classList.toggle('show');
        return;
    }
    if (!e.target.closest('.sdm-share-dropdown') && !e.target.closest('.sdm-share-btn')) { document.querySelectorAll('#newsDetailModal .sdm-share-dropdown.show').forEach(function(d){d.classList.remove('show');}); }
    if (e.target.closest('.sdm-share-link') && e.target.closest('#newsDetailModal')) {
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
        fetch('api.php?action=record_share',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:sdmCurrentNews.type,content_id:sdmCurrentNews.id,platform:platform})})
        .then(function(r){return r.json();}).then(function(res){if(res.success){var sh=document.querySelector('#newsDetailModal .sdm-share-count');if(sh)sh.textContent=res.count||0;}});
        document.querySelectorAll('#newsDetailModal .sdm-share-dropdown.show').forEach(function(d){d.classList.remove('show');});
        return;
    }
    if (e.target.closest('.sdm-comment-delete') && e.target.closest('#newsDetailModal')) {
        e.preventDefault();
        if(!confirm('Delete this comment?'))return;
        var cid = e.target.closest('.sdm-comment-delete').dataset.id;
        fetch('api.php?action=delete_comment',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({comment_id:cid})})
        .then(function(r){return r.json();}).then(function(res){if(res.success){sdmLoadComments(1);sdmRefreshCounts();}else{alert(res.message||'Failed.');}});
        return;
    }
    if (e.target.closest('.sdm-load-more') && e.target.closest('#newsDetailModal')) { e.preventDefault(); sdmCommentsPage++; sdmLoadComments(sdmCommentsPage); return; }
});
document.addEventListener('submit', function(e) {
    var form = e.target.closest('#newsDetailModal .sdm-comment-form-form');
    if (!form) return;
    e.preventDefault();
    if (!sdmCurrentNews.loggedIn) { alert('Please log in to comment.'); return; }
    var ta = form.querySelector('textarea'), btn = form.querySelector('.btn-post'), text = ta.value.trim();
    if (!text) return;
    btn.disabled = true;
    fetch('api.php?action=add_comment',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:sdmCurrentNews.type,content_id:sdmCurrentNews.id,comment:text})})
    .then(function(r){return r.json();}).then(function(res){btn.disabled=false;if(!res.success){alert(res.message||'Failed.');return;}ta.value='';var ct=form.querySelector('.char-count');if(ct)ct.textContent='0/2000';sdmLoadComments(1);sdmRefreshCounts();}).catch(function(){btn.disabled=false;});
});
document.addEventListener('input', function(e) { if(e.target.closest('#newsDetailModal .sdm-comment-form textarea')){var ct=e.target.closest('.sdm-comment-form').querySelector('.char-count');if(ct)ct.textContent=e.target.value.length+'/2000';} });
function sdmShowCopied(){var t=document.createElement('div');t.className='sdm-copied-toast';t.textContent='Link copied!';document.body.appendChild(t);setTimeout(function(){t.remove();},2000);}

function showNewsDetail(data) {
    sdmResetInteractions();
    if (data.id) sdmCurrentNews.id = data.id;
    document.getElementById('newsDetailTitle').textContent = data.title;
    var meta = '';
    if (data.author) meta += '<i class="fas fa-user me-1"></i> ' + data.author;
    if (data.date) meta += ' &middot; <i class="fas fa-calendar me-1"></i> ' + data.date;
    if (data.category) meta += ' &middot; <span class="badge-category">' + data.category + '</span>';
    if (data.views) meta += ' &middot; <i class="fas fa-eye me-1"></i> ' + data.views + ' views';
    document.getElementById('newsDetailMeta').innerHTML = meta;
    document.getElementById('newsDetailContent').innerHTML = data.content ? '<p>' + data.content.replace(/\n/g, '</p><p>') + '</p>' : '<p style="color:#94a3b8;">Full article content will be available soon.</p>';
    new bootstrap.Modal(document.getElementById('newsDetailModal')).show();
    sdmRefreshCounts();
}
function subscribe(e) {
    e.preventDefault();
    var input = e.target.querySelector('input[type="email"], input');
    if (!input) return false;
    var email = input.value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/newsletter_subscribe.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                alert(res.message || 'Successfully subscribed!');
            } catch(ex) {
                alert('Successfully subscribed!');
            }
            e.target.reset();
        }
    };
    xhr.onerror = function() {
        alert('Subscription failed. Please try again.');
    };
    xhr.send('email=' + encodeURIComponent(email));
    return false;
}
</script>

<?php include 'components/footer.php'; ?>
