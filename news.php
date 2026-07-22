<?php
$pageTitle = 'News | Salem Dominion Ministries';
$currentPage = 'news';
$pageDescription = 'Stay updated with the latest news, announcements, and happenings at Salem Dominion Ministries.';

require_once 'config.php';
require_once 'db_connection.php';

$conn = createDatabaseConnection();

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
    if ($conn) {
        $where = "WHERE n.status = 'published'";
        $params = [];
        $types = '';

        if ($category_filter) {
            $where .= " AND n.category = ?";
            $params[] = $category_filter;
            $types .= 's';
        }
        if ($search) {
            $where .= " AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)";
            $sp = "%{$search}%";
            $params[] = $sp;
            $params[] = $sp;
            $params[] = $sp;
            $types .= 'sss';
        }

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM news n {$where}");
        if ($countStmt) {
            if (!empty($params)) $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $total_news = $countStmt->get_result()->fetch_row()[0];
            $total_pages = max(1, ceil($total_news / $per_page));
            $page = min($page, $total_pages);
            $countStmt->close();
        }

        $query = "SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as author_name FROM news n LEFT JOIN users u ON n.author_id = u.id {$where} ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $fp = array_merge($params, [$per_page, $offset]);
            $ft = $types . 'ii';
            $stmt->bind_param($ft, ...$fp);
            $stmt->execute();
            $news_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }

        if ($page === 1 && empty($search) && empty($category_filter)) {
            $featStmt = $conn->prepare("SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as author_name FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE n.status = 'published' AND n.is_featured = 1 ORDER BY n.created_at DESC LIMIT 1");
            if ($featStmt) {
                $featStmt->execute();
                $featured_news = $featStmt->get_result()->fetch_assoc();
                $featStmt->close();
            }
            if (!$featured_news && !empty($news_items)) {
                $featured_news = $news_items[0];
                $news_items = array_slice($news_items, 1);
            }
        }

        $catStmt = $conn->prepare("SELECT DISTINCT category, COUNT(*) as count FROM news WHERE status = 'published' AND category IS NOT NULL AND category != '' GROUP BY category ORDER BY count DESC");
        if ($catStmt) {
            $catStmt->execute();
            $categories = $catStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $catStmt->close();
        }

        $recStmt = $conn->prepare("SELECT n.id, n.title, n.created_at, n.category, n.featured_image FROM news n WHERE n.status = 'published' ORDER BY n.created_at DESC LIMIT 5");
        if ($recStmt) {
            $recStmt->execute();
            $recent_news = $recStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $recStmt->close();
        }
    }
} catch (Exception $e) {
    error_log("News page error: " . $e->getMessage());
}

include 'components/header.php';
?>

<style>
.news-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(14,165,233,0.75)), url('assets/hero1-5.jpeg') center/cover no-repeat;
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

@media(max-width:768px) { .news-hero h1 { font-size: 2rem; } .filter-bar { padding: 15px; } .featured-news-card .news-img { height: 250px; } .featured-news-card .news-body h3 { font-size: 1.3rem; } }
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
                            'title' => $featured_news['title'],
                            'content' => $featured_news['content'] ?? $featured_news['excerpt'] ?? '',
                            'date' => formatDate($featured_news['created_at'], 'F j, Y'),
                            'author' => !empty($featured_news['author_name']) ? $featured_news['author_name'] : 'Admin',
                            'category' => $featured_news['category'] ?? '',
                            'views' => $featured_news['views_count'] ?? 0,
                        ])) ?>;return false;" style="text-decoration:none;color:inherit;"><?= htmlspecialchars($featured_news['title']) ?></a></h3>
                        <p><?= htmlspecialchars(truncate($featured_news['content'] ?? $featured_news['excerpt'] ?? '', 400)) ?></p>
                        <div class="d-flex align-items-center gap-3 mt-3" style="color:#94a3b8;font-family:'Montserrat',sans-serif;font-size:0.85rem;">
                            <span><i class="fas fa-user me-1 text-primary"></i> <?= htmlspecialchars(!empty($featured_news['author_name']) ? $featured_news['author_name'] : 'Admin') ?></span>
                            <span><i class="fas fa-calendar me-1 text-primary"></i> <?= formatDate($featured_news['created_at']) ?></span>
                            <span><i class="fas fa-eye me-1 text-primary"></i> <?= $featured_news['views_count'] ?? 0 ?> views</span>
                        </div>
                        <div class="mt-3">
                            <a href="#" class="btn btn-gold btn-sm" onclick="showNewsDetail(<?= htmlspecialchars(json_encode([
                                'title' => $featured_news['title'],
                                'content' => $featured_news['content'] ?? $featured_news['excerpt'] ?? '',
                                'date' => formatDate($featured_news['created_at'], 'F j, Y'),
                                'author' => !empty($featured_news['author_name']) ? $featured_news['author_name'] : 'Admin',
                                'category' => $featured_news['category'] ?? '',
                                'views' => $featured_news['views_count'] ?? 0,
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
                                    'title' => $news['title'],
                                    'content' => $news['content'] ?? $news['excerpt'] ?? '',
                                    'date' => formatDate($news['created_at'], 'F j, Y'),
                                    'author' => !empty($news['author_name']) ? $news['author_name'] : 'Admin',
                                    'category' => $news['category'] ?? '',
                                    'views' => $news['views_count'] ?? 0,
                                ])) ?>);return false;" style="text-decoration:none;color:inherit;"><?= htmlspecialchars($news['title']) ?></a></h5>
                                <p class="excerpt"><?= htmlspecialchars(truncate($news['content'] ?? $news['excerpt'] ?? '', 150)) ?></p>
                                <div class="news-meta">
                                    <span><i class="fas fa-user"></i> <?= htmlspecialchars(!empty($news['author_name']) ? $news['author_name'] : 'Admin') ?></span>
                                    <span><i class="fas fa-calendar"></i> <?= formatDate($news['created_at']) ?></span>
                                    <span><i class="fas fa-eye"></i> <?= $news['views_count'] ?? 0 ?></span>
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
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showNewsDetail(data) {
    document.getElementById('newsDetailTitle').textContent = data.title;
    let meta = '';
    if (data.author) meta += '<i class="fas fa-user me-1"></i> ' + data.author;
    if (data.date) meta += ' &middot; <i class="fas fa-calendar me-1"></i> ' + data.date;
    if (data.category) meta += ' &middot; <span class="badge-category">' + data.category + '</span>';
    if (data.views) meta += ' &middot; <i class="fas fa-eye me-1"></i> ' + data.views + ' views';
    document.getElementById('newsDetailMeta').innerHTML = meta;
    document.getElementById('newsDetailContent').innerHTML = data.content ? '<p>' + data.content.replace(/\n/g, '</p><p>') + '</p>' : '<p style="color:#94a3b8;">Full article content will be available soon.</p>';
    new bootstrap.Modal(document.getElementById('newsDetailModal')).show();
}
function subscribe(e) {
    e.preventDefault();
    alert('Thank you for subscribing! You will receive updates from Salem Dominion Ministries.');
    e.target.reset();
}
</script>

<?php include 'components/footer.php'; ?>
