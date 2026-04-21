<?php
// NEWS PAGE - Salem Dominion Ministries - Professional & Mobile Responsive
require_once 'db_connection.php';

$conn = getConnection();

// Pagination and filtering
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Initialize variables
$news_items = [];
$total_news = 0;
$total_pages = 1;
$categories = [];
$isAdmin = false;

// Check if user is admin (simple session check)
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $isAdmin = true;
}

try {
    if ($conn) {
        // Get categories
        $categories_stmt = $conn->prepare("SELECT DISTINCT category FROM news WHERE category IS NOT NULL AND category != '' AND status = 'published' ORDER BY category");
        if ($categories_stmt) {
            $categories_stmt->execute();
            $categories_result = $categories_stmt->get_result();
            $categories = $categories_result->fetch_all(MYSQLI_ASSOC);
            $categories_stmt->close();
        }
        
        // Get news with proper error handling
        $query = "SELECT n.*, CONCAT(u.first_name, ' ', u.last_name) as author_name 
                  FROM news n 
                  LEFT JOIN users u ON n.created_by = u.id 
                  WHERE n.status = 'published'";
        
        $params = [];
        $types = '';
        
        if ($category_filter) {
            $query .= " AND n.category = ?";
            $params[] = $category_filter;
            $types .= 's';
        }
        
        if ($search) {
            $query .= " AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'sss';
        }
        
        $query .= " ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $news_items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // Get total count for pagination
        $count_query = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
        $count_params = [];
        $count_types = '';
        
        if ($category_filter) {
            $count_query .= " AND category = ?";
            $count_params[] = $category_filter;
            $count_types .= 's';
        }
        
        if ($search) {
            $count_query .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
            $search_param = "%$search%";
            $count_params[] = $search_param;
            $count_params[] = $search_param;
            $count_params[] = $search_param;
            $count_types .= 'sss';
        }
        
        $count_stmt = $conn->prepare($count_query);
        if ($count_stmt) {
            if (!empty($count_params)) {
                $count_stmt->bind_param($count_types, ...$count_params);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_news = $count_result->fetch_assoc()['total'];
            $count_stmt->close();
        }
        
        $total_pages = ceil($total_news / $per_page);
        
        $conn->close();
    }
} catch (Exception $e) {
    $news_items = [];
    $total_news = 0;
    $total_pages = 1;
    $categories = [];
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}

// Helper function to format date
function format_news_date($date) {
    return date('F j, Y', strtotime($date));
}

// Helper function to truncate text
function truncate_text($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;">
    <title>News & Updates | Salem Dominion Ministries</title>
    <meta name="description" content="Latest news and updates from Salem Dominion Ministries">
    <link rel="icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--ocean-blue) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--midnight-blue);
            color: var(--snow-white);
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: var(--heavenly-gold) !important;
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .navbar-nav .nav-link {
            color: var(--snow-white) !important;
            font-weight: 400;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: var(--heavenly-gold) !important;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('assets/news-hero.jpg');
            background-size: cover;
            background-position: center;
            padding: 120px 0 80px;
            text-align: center;
            position: relative;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
        }

        /* Search and Filter Section */
        .search-section {
            padding: 60px 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .search-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* News Section */
        .news-section {
            padding: 80px 0;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            text-align: center;
            margin-bottom: 3rem;
        }

        .news-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .news-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            border-color: var(--heavenly-gold);
        }

        .news-meta {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .news-category {
            background: var(--gradient-divine);
            color: var(--snow-white);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .news-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .news-excerpt {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .news-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-news {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-news:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.3);
            color: var(--snow-white);
            text-decoration: none;
        }

        /* Featured News */
        .featured-news {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(251, 191, 36, 0.5);
            border-radius: 25px;
            padding: 3rem;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }

        .featured-news::before {
            content: 'FEATURED';
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--gradient-divine);
            color: var(--snow-white);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .featured-news .news-title {
            font-size: 2rem;
        }

        .featured-news .news-excerpt {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Categories Section */
        .categories-section {
            padding: 60px 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .category-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--heavenly-gold);
        }

        .category-card.active {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
        }

        .category-icon {
            font-size: 2rem;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .category-name {
            color: var(--snow-white);
            font-weight: 600;
        }

        /* Pagination */
        .pagination-section {
            padding: 40px 0;
        }

        .pagination .page-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            margin: 0 5px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
        }

        .pagination .page-item.active .page-link {
            background: var(--gradient-divine);
            border-color: var(--heavenly-gold);
            color: var(--snow-white);
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 60px 0 20px;
            margin-top: 80px;
        }
        
        .footer-widget h4 {
            position: relative;
            padding-bottom: 15px;
            color: var(--heavenly-gold);
        }
        
        .footer-widget h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--heavenly-gold);
        }
        
        .footer-links li {
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .footer-links li:hover {
            transform: translateX(5px);
        }
        
        .footer-links a:hover {
            color: var(--heavenly-gold) !important;
        }
        
        .social-icon {
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
            color: var(--heavenly-gold) !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .news-card {
                margin-bottom: 1.5rem;
            }
            
            .featured-news {
                padding: 2rem;
            }
            
            .featured-news .news-title {
                font-size: 1.5rem;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .navbar-brand img {
                width: 30px;
                height: 30px;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.7);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--heavenly-gold);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries">
                <span>Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="sermons.php">Sermons</a></li>
                    <li class="nav-item"><a class="nav-link active" href="news.php">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="prophetic-school.php">Prophetic School</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_pastor_call.php" class="text-warning">Book Call</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">News & Updates</h1>
            <p class="hero-subtitle">Stay informed with the latest happenings at Salem Dominion Ministries</p>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="search-section">
        <div class="container">
            <div class="search-card">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" placeholder="Search news..." value="<?= safe_html($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= safe_html($cat['category']) ?>" <?= $category_filter == $cat['category'] ? 'selected' : '' ?>>
                                        <?= safe_html($cat['category']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-news w-100">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="container">
            <h2 class="section-title">Latest News</h2>
            
            <?php if (!empty($news_items)): ?>
                <?php 
                // Show first item as featured if not searching or filtering
                $featured_item = null;
                $regular_items = $news_items;
                
                if (empty($search) && empty($category_filter) && count($news_items) > 0) {
                    $featured_item = array_shift($regular_items);
                }
                ?>
                
                <?php if ($featured_item): ?>
                    <!-- Featured News -->
                    <div class="featured-news">
                        <h1 class="news-title"><?= safe_html($featured_item['title']) ?></h1>
                        <div class="news-meta">
                            <span class="news-category"><?= safe_html($featured_item['category'] ?? 'General') ?></span>
                            <span><i class="fas fa-calendar me-2"></i><?= format_news_date($featured_item['created_at']) ?></span>
                            <?php if ($featured_item['author_name']): ?>
                                <span><i class="fas fa-user me-2"></i><?= safe_html($featured_item['author_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="news-excerpt"><?= safe_html($featured_item['excerpt'] ?? truncate_text($featured_item['content'], 200)) ?></p>
                        <div class="news-actions">
                            <a href="#" class="btn btn-news">
                                <i class="fas fa-book-open me-2"></i>Read Full Story
                            </a>
                            <a href="#" class="btn btn-news">
                                <i class="fas fa-share-alt me-2"></i>Share
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Regular News Grid -->
                <div class="row g-4">
                    <?php foreach ($regular_items as $news): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="news-card">
                                <div class="news-meta">
                                    <span class="news-category"><?= safe_html($news['category'] ?? 'General') ?></span>
                                    <span><i class="fas fa-calendar me-2"></i><?= format_news_date($news['created_at']) ?></span>
                                </div>
                                <h3 class="news-title"><?= safe_html($news['title']) ?></h3>
                                <p class="news-excerpt"><?= safe_html($news['excerpt'] ?? truncate_text($news['content'], 150)) ?></p>
                                <div class="news-actions">
                                    <a href="#" class="btn btn-news">
                                        <i class="fas fa-book-open me-2"></i>Read More
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3>No News Found</h3>
                    <p>Check back soon for the latest updates and announcements.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categories Section -->
    <?php if (!empty($categories)): ?>
        <section class="categories-section">
            <div class="container">
                <h2 class="section-title">Browse by Category</h2>
                <div class="row g-3">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="category-card <?= $category_filter == $category['category'] ? 'active' : '' ?>" onclick="window.location.href='?category=<?= urlencode($category['category']) ?>'">
                                <div class="category-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <h4 class="category-name"><?= safe_html($category['category']) ?></h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <section class="pagination-section">
            <div class="container">
                <nav aria-label="News pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </section>
    <?php endif; ?>

    <!-- Professional Footer with 3D Effects -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h4 class="text-white mb-3 footer-title-3d">
                            <i class="fas fa-church me-2"></i>Salem Dominion Ministries
                        </h4>
                        <p class="text-white-50">Empowering lives through the Word of God and the Power of the Holy Spirit. Join us in spreading the Gospel and making disciples.</p>
                        <div class="mt-3">
                            <a href="<?php echo CHURCH_WEBSITE; ?>" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fas fa-globe fa-lg"></i>
                            </a>
                            <a href="https://youtube.com/@musasizifaty?si=BxEArdVKNKVSac3X" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fab fa-youtube fa-lg"></i>
                            </a>
                            <a href="https://www.tiktok.com/@salem1dominionchurch?_r=1&_t=ZS-95E1n40LieS" target="_blank" class="text-white me-3 social-icon-3d">
                                <i class="fab fa-tiktok fa-lg"></i>
                            </a>
                            <a href="https://www.facebook.com/share/1CoCEmvnBB/" target="_blank" class="text-white social-icon-3d">
                                <i class="fab fa-facebook fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h5 class="text-white mb-3 footer-title-3d">Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="about.php" class="text-white-50 text-decoration-none footer-link-3d">About Us</a></li>
                            <li><a href="leadership.php" class="text-white-50 text-decoration-none footer-link-3d">Leadership</a></li>
                            <li><a href="sermons.php" class="text-white-50 text-decoration-none footer-link-3d">Sermons</a></li>
                            <li><a href="events.php" class="text-white-50 text-decoration-none footer-link-3d">Events</a></li>
                            <li><a href="ministries.php" class="text-white-50 text-decoration-none footer-link-3d">Ministries</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h5 class="text-white mb-3 footer-title-3d">Services</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="prophetic-school.php" class="text-white-50 text-decoration-none footer-link-3d">Prophetic School</a></li>
                            <li><a href="book_pastor_call.php" class="text-white-50 text-decoration-none footer-link-3d">Book Pastor Call</a></li>
                            <li><a href="children_ministry.php" class="text-white-50 text-decoration-none footer-link-3d">Children Ministry</a></li>
                            <li><a href="donate.php" class="text-white-50 text-decoration-none footer-link-3d">Give & Donate</a></li>
                            <li><a href="testimonials.php" class="text-white-50 text-decoration-none footer-link-3d">Testimonials</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget footer-3d">
                        <h5 class="text-white mb-3 footer-title-3d">Contact Info</h5>
                        <ul class="list-unstyled footer-contact">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                                <span class="text-white-50">Kampala, Uganda</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                <span class="text-white-50">+256 753 244 480</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <span class="text-white-50">info@salem-dominion-ministries.org</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-globe me-2 text-primary"></i>
                                <span class="text-white-50"><?php echo parse_url(CHURCH_WEBSITE, PHP_URL_HOST); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <hr class="bg-white my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white-50 me-3 text-decoration-none">Privacy Policy</a>
                    <a href="terms.php" class="text-white-50 text-decoration-none">Terms of Service</a>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <div class="designer-credit-3d">
                        <p class="text-white-50 mb-2">
                            <i class="fas fa-code me-2"></i>Designed & Developed by
                        </p>
                        <h5 class="designer-name-3d text-warning mb-2">Mr. Reagan Otema</h5>
                        <a href="https://wa.me/256772514889" target="_blank" class="designer-contact-3d">
                            <i class="fab fa-whatsapp me-2"></i>+256 772 514 889
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        /* 3D Footer Effects */
        .footer-3d {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            transform: perspective(1000px) rotateX(0deg);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .footer-3d:hover {
            transform: perspective(1000px) rotateX(-5deg) translateY(-10px);
            box-shadow: 0 20px 60px rgba(251, 191, 36, 0.2);
            border-color: rgba(251, 191, 36, 0.3);
        }

        .footer-title-3d {
            position: relative;
            text-shadow: 0 2px 10px rgba(251, 191, 36, 0.5);
            transform: translateZ(20px);
            transition: all 0.3s ease;
        }

        .footer-title-3d:hover {
            transform: translateZ(30px) scale(1.05);
            text-shadow: 0 4px 20px rgba(251, 191, 36, 0.8);
        }

        .social-icon-3d {
            display: inline-block;
            width: 50px;
            height: 50px;
            line-height: 50px;
            text-align: center;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            transform: translateZ(10px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .social-icon-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.3), transparent);
            transition: all 0.6s ease;
        }

        .social-icon-3d:hover::before {
            left: 100%;
        }

        .social-icon-3d:hover {
            transform: translateZ(30px) rotateY(360deg) scale(1.2);
            background: linear-gradient(135deg, var(--heavenly-gold), var(--ocean-blue));
            border-color: var(--heavenly-gold);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.4);
        }

        .footer-link-3d {
            display: inline-block;
            transform: translateZ(5px);
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-link-3d::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--heavenly-gold);
            transition: all 0.3s ease;
        }

        .footer-link-3d:hover {
            transform: translateZ(15px) translateX(5px);
            color: var(--heavenly-gold) !important;
        }

        .footer-link-3d:hover::after {
            width: 100%;
        }

        .designer-credit-3d {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(14, 165, 233, 0.1));
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            transform: perspective(1000px) rotateX(0deg);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
        }

        .designer-credit-3d::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(251, 191, 36, 0.1), transparent);
            animation: designerShimmer 3s infinite;
        }

        @keyframes designerShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
            100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        }

        .designer-credit-3d:hover {
            transform: perspective(1000px) rotateX(-5deg) translateY(-5px);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.3);
        }

        .designer-name-3d {
            transform: translateZ(20px);
            text-shadow: 0 2px 15px rgba(251, 191, 36, 0.6);
            animation: designerGlow 2s ease-in-out infinite alternate;
        }

        @keyframes designerGlow {
            0% { text-shadow: 0 2px 15px rgba(251, 191, 36, 0.6); }
            100% { text-shadow: 0 4px 25px rgba(251, 191, 36, 0.9); }
        }

        .designer-contact-3d {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            transform: translateZ(15px);
            transition: all 0.4s ease;
            box-shadow: 0 5px 20px rgba(37, 211, 102, 0.3);
        }

        .designer-contact-3d:hover {
            transform: translateZ(25px) scale(1.1);
            box-shadow: 0 10px 30px rgba(37, 211, 102, 0.5);
            text-decoration: none;
            color: white;
        }

        /* Mobile Responsive 3D Effects */
        @media (max-width: 768px) {
            .footer-3d {
                transform: none;
                padding: 1.5rem;
            }
            
            .footer-3d:hover {
                transform: translateY(-5px);
            }
            
            .social-icon-3d {
                width: 40px;
                height: 40px;
                line-height: 40px;
                font-size: 0.9rem;
            }
            
            .social-icon-3d:hover {
                transform: scale(1.1);
            }
            
            .designer-credit-3d {
                padding: 1rem;
                transform: none;
            }
            
            .designer-credit-3d:hover {
                transform: translateY(-3px);
            }
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

// Get categories for filter
try {
    $categories = $conn->query("SELECT DISTINCT category FROM news WHERE category IS NOT NULL AND category != '' ORDER BY category");
} catch (Exception $e) {
    $categories = [];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_news_detail':
            $news_id = intval($_POST['news_id']);
            try {
                $news = $conn->query("SELECT * FROM news WHERE id = ?", [$news_id]);
                if ($news) {
                    echo json_encode(['success' => true, 'news' => $news[0]]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'News not found']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'add_news':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $excerpt = trim($_POST['excerpt']);
            $category = $_POST['category'];
            
            if (empty($title) || empty($content)) {
                echo json_encode(['success' => false, 'message' => 'Title and content are required']);
                exit;
            }
            
            try {
                $conn->query("INSERT INTO news (title, content, excerpt, category, status, created_at) VALUES (?, ?, ?, ?, 'published', NOW())", 
                    [$title, $content, $excerpt, $category]);
                echo json_encode(['success' => true, 'message' => 'News added successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'delete_news':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $news_id = intval($_POST['news_id']);
            try {
                $conn->query("DELETE FROM news WHERE id = ?", [$news_id]);
                echo json_encode(['success' => true, 'message' => 'News deleted successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
    }
}

// Clean buffer
ob_end_clean();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; style-src 'self' https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:;">
    <meta name="description" content="Latest News from Salem Dominion Ministries - Stay updated with our church activities and events">
    <meta name="keywords" content="news, church, events, salem dominion ministries, iganga, uganda">
    <title>News - Salem Dominion Ministries</title>
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/png" sizes="16x16" href="public/logo-icon.jpeg">
    <link rel="icon" type="image/jpeg" href="public/logo-icon.jpeg">
    <link rel="apple-touch-icon" sizes="180x180" href="public/logo-icon.jpeg">
    <link rel="manifest" href="public/site.webmanifest">
    <meta name="theme-color" content="#0f172a">
    <meta name="msapplication-TileColor" content="#0f172a">
    <meta name="msapplication-config" content="public/browserconfig.xml">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Great+Vibes&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a;
            --secondary-color: #0ea5e9;
            --accent-color: #fbbf24;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --gray-light: #e9ecef;
            --gray-medium: #6c757d;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--light-bg);
            color: var(--primary-color);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1.2;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 15s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .hero p {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            margin-bottom: 2rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        /* Search and Filter Section */
        .search-filter-section {
            background: var(--white);
            padding: 40px 0;
            border-bottom: 1px solid var(--gray-light);
            position: sticky;
            top: 76px;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box input {
            padding: 15px 50px 15px 20px;
            border: 2px solid var(--gray-light);
            border-radius: 50px;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--secondary-color);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: var(--white);
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .search-box button:hover {
            background: var(--primary-color);
        }
        
        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        
        .filter-pill {
            padding: 8px 20px;
            border: 2px solid var(--gray-light);
            border-radius: 25px;
            background: var(--white);
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .filter-pill:hover,
        .filter-pill.active {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Admin Actions */
        .admin-actions {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .btn-admin {
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        
        .btn-admin:hover {
            background: var(--primary-color);
            color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        /* News Container */
        .news-container {
            padding: 60px 0;
        }
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .news-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .news-card-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: var(--white);
            padding: 25px;
            position: relative;
        }
        
        .news-card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }
        
        .news-title {
            font-size: 1.4rem;
            margin: 0;
            line-height: 1.3;
        }
        
        .news-category {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .news-content {
            padding: 30px;
        }
        
        .news-meta {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: var(--gray-medium);
            font-size: 14px;
        }
        
        .news-meta i {
            margin-right: 8px;
        }
        
        .news-excerpt {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
            font-size: 15px;
        }
        
        .btn-read-more {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-read-more:hover {
            background: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .btn-read-more i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .btn-read-more:hover i {
            transform: translateX(5px);
        }
        
        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
        }
        
        .page-link {
            padding: 10px 15px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .page-link:hover,
        .page-link.active {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Modal */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            border-radius: 20px 20px 0 0;
            border: none;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-footer {
            border: none;
            padding: 20px 30px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--gray-medium);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: var(--gray-medium);
            font-size: 1.1rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 80px 0 60px;
            }
            
            .search-filter-section {
                padding: 30px 0;
                position: relative;
                top: 0;
            }
            
            .filter-pills {
                justify-content: center;
            }
            
            .news-container {
                padding: 40px 15px;
            }
            
            .news-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .modal-dialog {
                margin: 20px;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .admin-actions {
                margin-bottom: 20px;
            }
            
            .btn-admin {
                display: block;
                margin: 10px auto;
                width: 200px;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .search-box input {
                padding: 12px 45px 12px 15px;
                font-size: 14px;
            }
            
            .search-box button {
                width: 35px;
                height: 35px;
            }
            
            .news-card-header {
                padding: 20px;
            }
            
            .news-title {
                font-size: 1.2rem;
            }
            
            .news-content {
                padding: 20px;
            }
            
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .news-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .news-card:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .news-card:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        .news-card:nth-child(4) {
            animation-delay: 0.3s;
        }
        
        .news-card:nth-child(5) {
            animation-delay: 0.4s;
        }
        
        .news-card:nth-child(6) {
            animation-delay: 0.5s;
        }
    </style>
    <style>
        /* SPIRITUAL DESIGN SYSTEM - Consistent with Homepage */
        :root {
            /* Primary Palette - Divine & Professional */
            --midnight-blue: #0f172a;
            --deep-navy: #1e3a5f;
            --ocean-blue: #0ea5e9;
            --sky-blue: #38bdf8;
            --ice-blue: #7dd3fc;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
            --cream: #fef9f3;
            
            /* Divine Accents - Spiritual Colors */
            --heavenly-gold: #fbbf24;
            --divine-light: #fef3c7;
            --royal-purple: #7c3aed;
            --spirit-glow: #f59e0b;
            --holy-fire: #ef4444;
            --grace-pink: #ec4899;
            
            /* 3D Shadows & Depth */
            --shadow-divine: 0 20px 40px rgba(15, 23, 42, 0.15);
            --shadow-heavenly: 0 25px 50px rgba(251, 191, 36, 0.2);
            --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.08);
            --shadow-3d: 0 30px 60px rgba(15, 23, 42, 0.2), 0 0 0 1px rgba(255,255,255,0.1);
            --shadow-float: 0 40px 80px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(255,255,255,0.15);
            --shadow-glow: 0 0 40px rgba(14, 165, 233, 0.3);
            
            /* Gradients - Spiritual & 3D */
            --gradient-ocean: linear-gradient(135deg, var(--midnight-blue) 0%, var(--deep-navy) 30%, var(--ocean-blue) 60%, var(--sky-blue) 100%);
            --gradient-heaven: linear-gradient(135deg, var(--snow-white) 0%, var(--pearl-white) 50%, var(--ice-blue) 100%);
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--spirit-glow) 50%, var(--divine-light) 100%);
            --gradient-spiritual: linear-gradient(135deg, var(--royal-purple) 0%, var(--ocean-blue) 50%, var(--heavenly-gold) 100%);
            --gradient-fire: linear-gradient(135deg, var(--holy-fire) 0%, var(--spirit-glow) 50%, var(--heavenly-gold) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 300;
            line-height: 1.6;
            color: var(--midnight-blue);
            background: var(--snow-white);
        }

        /* Divine Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(251, 191, 36, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(56, 189, 248, 0.02) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        /* Typography - Iconic */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1.2;
            color: var(--midnight-blue);
        }

        .font-divine {
            font-family: 'Great Vibes', cursive;
            color: var(--heavenly-gold);
        }

        /* Navigation - Iconic */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-soft);
            padding: 1rem 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1000;
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: var(--shadow-divine);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--midnight-blue) !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .navbar-brand img {
            height: 50px;
            width: auto;
            border-radius: 50%;
            background: var(--gradient-heaven);
            padding: 8px;
            box-shadow: 0 0 30px rgba(251, 191, 36, 0.3);
            transition: all 0.5s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 40px rgba(251, 191, 36, 0.5);
        }

        .navbar-nav .nav-link {
            color: var(--midnight-blue) !important;
            font-weight: 400;
            font-size: 0.95rem;
            margin: 0 12px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none !important;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--ocean-blue) !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gradient-divine);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%);
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        /* Hero Section - Enhanced */
        .hero {
            background: var(--gradient-ocean);
            min-height: 50vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: var(--snow-white);
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: titleGlow 4s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% { text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); }
            100% { text-shadow: 0 4px 30px rgba(251, 191, 36, 0.4); }
        }

        .hero-subtitle {
            font-family: 'Great Vibes', cursive;
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.95;
            letter-spacing: 0.05em;
        }

        /* Sections - Iconic Design */
        .section {
            padding: 80px 0;
            position: relative;
        }

        .section-light {
            background: var(--snow-white);
        }

        .section-heaven {
            background: var(--gradient-heaven);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 3.5rem);
            font-weight: 900;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--midnight-blue);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-divine);
            border-radius: 2px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: var(--ocean-blue);
            margin-bottom: 4rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 300;
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: var(--snow-white);
            padding: 40px 0;
            border-bottom: 1px solid rgba(125, 211, 252, 0.2);
            position: sticky;
            top: 76px;
            z-index: 999;
            box-shadow: var(--shadow-soft);
        }
        
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-box input {
            padding: 15px 50px 15px 20px;
            border: 2px solid rgba(125, 211, 252, 0.2);
            border-radius: 50px;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            background: var(--pearl-white);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--ocean-blue);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--gradient-ocean);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: var(--snow-white);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-box button:hover {
            background: var(--gradient-divine);
            transform: translateY(-50%) scale(1.1);
        }
        
        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        
        .filter-pill {
            padding: 8px 20px;
            border: 2px solid rgba(125, 211, 252, 0.2);
            border-radius: 25px;
            background: var(--snow-white);
            color: var(--midnight-blue);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .filter-pill:hover,
        .filter-pill.active {
            background: var(--gradient-ocean);
            border-color: var(--ocean-blue);
            color: var(--snow-white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
        }

        /* Admin Actions */
        .admin-actions {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .btn-admin {
            background: var(--gradient-divine);
            color: var(--midnight-blue);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
            box-shadow: var(--shadow-soft);
        }
        
        .btn-admin:hover {
            background: var(--gradient-ocean);
            color: var(--snow-white);
            transform: translateY(-2px);
            box-shadow: var(--shadow-divine);
        }

        /* News Container */
        .news-container {
            padding: 60px 0;
        }
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .news-card {
            background: var(--snow-white);
            border-radius: 25px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(125, 211, 252, 0.2);
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            transform-style: preserve-3d;
        }
        
        .news-card:hover {
            transform: translateY(-15px) rotateX(5deg) rotateY(2deg);
            box-shadow: var(--shadow-float);
            border-color: var(--heavenly-gold);
        }
        
        .news-card-header {
            background: var(--gradient-spiritual);
            color: var(--snow-white);
            padding: 25px;
            position: relative;
            overflow: hidden;
        }
        
        .news-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-divine);
        }
        
        .news-card-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 15s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .news-title {
            font-size: 1.4rem;
            margin: 0;
            line-height: 1.3;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }
        
        .news-category {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            backdrop-filter: blur(10px);
        }
        
        .news-content {
            padding: 30px;
        }
        
        .news-meta {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: var(--ocean-blue);
            font-size: 14px;
            font-weight: 500;
        }
        
        .news-meta i {
            margin-right: 8px;
            color: var(--heavenly-gold);
        }
        
        .news-excerpt {
            color: var(--deep-navy);
            margin-bottom: 25px;
            line-height: 1.6;
            font-size: 15px;
            font-weight: 300;
        }
        
        .btn-read-more {
            background: var(--gradient-ocean);
            color: var(--snow-white);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            box-shadow: var(--shadow-soft);
        }
        
        .btn-read-more:hover {
            background: var(--gradient-divine);
            color: var(--midnight-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavenly);
        }
        
        .btn-read-more i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .btn-read-more:hover i {
            transform: translateX(5px);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        
        .pagination {
            display: flex;
            gap: 5px;
        }
        
        .page-link {
            padding: 10px 15px;
            border: 2px solid rgba(125, 211, 252, 0.2);
            border-radius: 10px;
            color: var(--midnight-blue);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            background: var(--snow-white);
        }
        
        .page-link:hover,
        .page-link.active {
            background: var(--gradient-ocean);
            border-color: var(--ocean-blue);
            color: var(--snow-white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
        }

        /* Modal */
        .modal-content {
            border-radius: 25px;
            border: none;
            box-shadow: var(--shadow-float);
        }
        
        .modal-header {
            background: var(--gradient-spiritual);
            color: var(--snow-white);
            border-radius: 25px 25px 0 0;
            border: none;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-footer {
            border: none;
            padding: 20px 30px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--ocean-blue);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--midnight-blue);
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: var(--deep-navy);
            font-size: 1.1rem;
        }

        /* Footer - Professional */
        .footer {
            background: var(--midnight-blue);
            color: var(--snow-white);
            padding: 4rem 0 2rem;
            position: relative;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: var(--gradient-divine);
        }

        .footer-content {
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--snow-white);
            text-decoration: none;
            font-weight: 400;
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-links a:hover {
            color: var(--heavenly-gold);
            transform: translateY(-2px);
        }

        .footer-copyright {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.7;
        }

        /* Responsive Design - Mobile First */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .hero {
                min-height: 40vh;
                padding: 4rem 0;
            }

            .hero-title {
                font-size: 2.2rem;
                margin-bottom: 1rem;
            }

            .hero-subtitle {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .section {
                padding: 60px 0;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            .section-subtitle {
                font-size: 1rem;
                margin-bottom: 2.5rem;
            }

            .search-filter-section {
                padding: 30px 0;
                position: relative;
                top: 0;
            }

            .filter-pills {
                justify-content: center;
            }

            .news-container {
                padding: 40px 15px;
            }

            .news-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .news-card {
                margin: 0;
            }

            .news-card-header {
                padding: 20px;
            }

            .news-title {
                font-size: 1.2rem;
            }

            .news-content {
                padding: 20px;
            }

            .modal-dialog {
                margin: 20px;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .navbar-brand img {
                height: 40px;
            }

            .admin-actions {
                margin-bottom: 20px;
            }

            .btn-admin {
                display: block;
                margin: 10px auto;
                width: 200px;
            }

            .footer {
                padding: 3rem 0 1.5rem;
            }

            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Extra Small Devices */
        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 1.3rem;
            }

            .section-title {
                font-size: 1.6rem;
            }

            .search-box input {
                padding: 12px 45px 12px 15px;
                font-size: 14px;
            }

            .search-box button {
                width: 35px;
                height: 35px;
            }

            .news-card-header {
                padding: 15px;
            }

            .news-title {
                font-size: 1.1rem;
            }

            .news-content {
                padding: 15px;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* Tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .news-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .news-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .news-card:nth-child(2) {
            animation-delay: 0.1s;
        }
        
        .news-card:nth-child(3) {
            animation-delay: 0.2s;
        }
        
        .news-card:nth-child(4) {
            animation-delay: 0.3s;
        }
        
        .news-card:nth-child(5) {
            animation-delay: 0.4s;
        }
        
        .news-card:nth-child(6) {
            animation-delay: 0.5s;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries">
                <span>Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="leadership.php">Leadership</a></li>
                    <li class="nav-item"><a class="nav-link" href="ministries.php">Ministries</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="sermons.php">Sermons</a></li>
                    <li class="nav-item"><a class="nav-link active" href="news.php">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="testimonials.php">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="prophetic-school.php">Prophetic School</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_pastor_call.php" class="text-warning">Book Call</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-gold rounded-pill px-4 ms-lg-3 mt-2 mt-lg-0 shadow-sm" href="login.php" style="font-weight: 700; border: 2px solid rgba(255,255,255,0.2); color: var(--midnight-blue) !important;">
                            <i class="fas fa-user-circle me-2"></i>Member Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-sm btn-primary rounded-pill px-4 mt-2 mt-lg-0 shadow-sm" href="donate.php" style="background: var(--gradient-fire); border: none; font-weight: 700;">
                            <i class="fas fa-heart me-2"></i>Give
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
            <div class="mb-4">
                <span class="font-divine" style="font-size: 4rem;">📰</span>
            </div>
            <h1 class="hero-title">Latest News</h1>
            <p class="hero-subtitle">Stay updated with our church activities and events</p>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="search-filter-section">
        <div class="container">
            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Search news..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            
            <div class="filter-pills">
                <span class="text-muted me-3">Filter by:</span>
                <a href="news.php" class="filter-pill <?php echo empty($category_filter) ? 'active' : ''; ?>">
                    All News
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="news.php?category=<?php echo urlencode($category['category']); ?>" 
                       class="filter-pill <?php echo $category_filter === $category['category'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['category']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Admin Actions -->
    <?php if ($isAdmin): ?>
    <div class="container">
        <div class="admin-actions">
            <button class="btn btn-admin" onclick="openAddNewsModal()">
                <i class="fas fa-plus me-2"></i>Add News
            </button>
            <button class="btn btn-admin" onclick="manageNews()">
                <i class="fas fa-cog me-2"></i>Manage News
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- News Container -->
    <section class="news-container">
        <div class="container">
            <?php if ($news_result && count($news_result) > 0): ?>
                <div class="news-grid">
                    <?php foreach ($news_result as $news): ?>
                        <div class="news-card">
                            <div class="news-card-header">
                                <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                                <?php if (!empty($news['category'])): ?>
                                    <span class="news-category"><?php echo htmlspecialchars($news['category']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="news-content">
                                <div class="news-meta">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('F j, Y', strtotime($news['created_at'])); ?>
                                </div>
                                <div class="news-excerpt">
                                    <?php 
                                    $excerpt = $news['excerpt'] ?? '';
                                    if (empty($excerpt)) {
                                        $content = strip_tags($news['content'] ?? '');
                                        $excerpt = substr($content, 0, 150) . '...';
                                    }
                                    echo htmlspecialchars($excerpt);
                                    ?>
                                </div>
                                <button class="btn-read-more" onclick="showNewsDetail(<?php echo $news['id']; ?>)">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="page-link active"><?php echo $i; ?></span>
                            <?php elseif (abs($i - $page) <= 2 || $i == 1 || $i == $total_pages): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif (abs($i - $page) == 3): ?>
                                <span class="page-link">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3>No News Available</h3>
                    <p>Check back soon for the latest updates from our church.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ultimate Footer -->
    <!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
    </div>
</footer>

    <!-- News Detail Modal -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsDetailTitle">News Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="newsDetailContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add News Modal -->
    <?php if ($isAdmin): ?>
    <div class="modal fade" id="addNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add News Article</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addNewsForm">
                        <div class="mb-3">
                            <label for="newsTitle" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="newsTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="newsCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="newsCategory" name="category" placeholder="e.g., Announcements, Events">
                        </div>
                        <div class="mb-3">
                            <label for="newsExcerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="newsExcerpt" name="excerpt" rows="3" placeholder="Brief summary (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="newsContent" class="form-label">Content *</label>
                            <textarea class="form-control" id="newsContent" name="content" rows="8" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addNews()">Add News</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS with safety check
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1200,
                once: true,
                offset: 100,
                easing: 'ease-in-out'
            });
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // News Detail Modal
        function showNewsDetail(newsId) {
            const modal = new bootstrap.Modal(document.getElementById('newsDetailModal'));
            const modalTitle = document.getElementById('newsDetailTitle');
            const modalContent = document.getElementById('newsDetailContent');
            
            modalTitle.textContent = 'Loading...';
            modalContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            modal.show();
            
            // Fetch news details
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_news_detail&news_id=' + newsId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalTitle.textContent = data.news.title;
                    modalContent.innerHTML = `
                        <div class="news-meta mb-3">
                            <i class="far fa-calendar me-2"></i>
                            ${new Date(data.news.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                            ${data.news.category ? '<span class="badge bg-secondary ms-3">' + data.news.category + '</span>' : ''}
                        </div>
                        <div class="news-content">
                            ${data.news.content}
                        </div>
                    `;
                } else {
                    modalTitle.textContent = 'Error';
                    modalContent.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(error => {
                modalTitle.textContent = 'Error';
                modalContent.innerHTML = '<div class="alert alert-danger">Failed to load news details.</div>';
            });
        }
        
        // Add News
        <?php if ($isAdmin): ?>
        function openAddNewsModal() {
            const modal = new bootstrap.Modal(document.getElementById('addNewsModal'));
            document.getElementById('addNewsForm').reset();
            modal.show();
        }
        
        function addNews() {
            const form = document.getElementById('addNewsForm');
            const formData = new FormData(form);
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add_news&' + new URLSearchParams(formData).toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addNewsModal')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: Failed to add news article.');
            });
        }
        
        function manageNews() {
            alert('News management feature coming soon!');
        }
        <?php endif; ?>
        
        // Page load confirmation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Enhanced news page loaded successfully at ' + new Date().toLocaleString());
        });
    </script>
</body>
</html>
