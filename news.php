<?php
// NEWS PAGE - Salem Dominion Ministries - Professional & Mobile Responsive
require_once 'db_connection.php';
require_once 'config.php';

$conn = createDatabaseConnection();

// Handle comment submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $content_type = 'news';
    $content_id = intval($_POST['content_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($comment)) $errors[] = 'Comment is required';
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address';
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO comments (content_type, content_id, user_id, name, email, comment) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("siisss", $content_type, $content_id, $user_id, $name, $email, $comment);
                $stmt->execute();
                $success = "Comment submitted successfully! It will be reviewed and published soon.";
            }
        } catch (Exception $e) {
            $errors[] = "Failed to submit comment. Please try again.";
        }
    }
}

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
        
        // Get comments for each news item
        foreach ($news_items as &$news) {
            $comment_stmt = $conn->prepare("SELECT * FROM comments WHERE content_type = 'news' AND content_id = ? AND status = 'approved' ORDER BY created_at DESC");
            if ($comment_stmt) {
                $comment_stmt->bind_param("i", $news['id']);
                $comment_stmt->execute();
                $comment_result = $comment_stmt->get_result();
                $news['comments'] = $comment_result->fetch_all(MYSQLI_ASSOC);
                $news['comment_count'] = count($news['comments']);
                $comment_stmt->close();
            } else {
                $news['comments'] = [];
                $news['comment_count'] = 0;
            }
        }
        
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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'get_news_detail':
            $news_id = intval($_POST['news_id']);
            try {
                $conn = createDatabaseConnection();
                $stmt = $conn->prepare("SELECT * FROM news WHERE id = ? AND status = 'published'");
                $stmt->bind_param('i', $news_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $news = $result->fetch_assoc();
                $stmt->close();
                $conn->close();
                
                if ($news) {
                    echo json_encode(['success' => true, 'news' => $news]);
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
                $conn = createDatabaseConnection();
                $stmt = $conn->prepare("INSERT INTO news (title, content, excerpt, category, status, created_at) VALUES (?, ?, ?, ?, 'published', NOW())");
                $stmt->bind_param('ssss', $title, $content, $excerpt, $category);
                $stmt->execute();
                $stmt->close();
                $conn->close();
                
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
                $conn = createDatabaseConnection();
                $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
                $stmt->bind_param('i', $news_id);
                $stmt->execute();
                $stmt->close();
                $conn->close();
                
                echo json_encode(['success' => true, 'message' => 'News deleted successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src <?php echo CSP_DEFAULT_SRC; ?>; script-src <?php echo CSP_SCRIPT_SRC; ?>; style-src <?php echo CSP_STYLE_SRC; ?>; font-src <?php echo CSP_FONT_SRC; ?>; img-src <?php echo CSP_IMG_SRC; ?>; connect-src <?php echo CSP_CONNECT_SRC; ?>">
    <title>News & Updates | Salem Dominion Ministries</title>
    <!-- Search Engine and Social Media Meta Tags -->
    <meta name="description" content="Latest news and updates from Salem Dominion Ministries">
    <meta name="keywords" content="Salem Dominion Ministries, news, updates, church, worship, Iganga, Uganda, Apostle Faty Musasizi, Christian, ministry">
    <meta name="author" content="Salem Dominion Ministries">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="News & Updates - Salem Dominion Ministries">
    <meta property="og:description" content="Latest news and updates from Salem Dominion Ministries">
    <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/logo-icon.jpeg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="Salem Dominion Ministries Logo">
    <meta property="og:site_name" content="Salem Dominion Ministries">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="News & Updates - Salem Dominion Ministries">
    <meta name="twitter:description" content="Latest news and updates from Salem Dominion Ministries">
    <meta name="twitter:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/public/logo-icon.jpeg">
    <meta name="twitter:image:alt" content="Salem Dominion Ministries Logo">
    
    <!-- Favicon - Church Logo Only -->
    <link rel="icon" href="public/logo-icon.jpeg">
    <link rel="shortcut icon" href="public/logo-icon.jpeg">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="public/site.webmanifest">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Salem Ministries">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&family=Great+Vibes&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--ocean-blue) 100%);
            --gradient-ocean: linear-gradient(135deg, var(--midnight-blue) 0%, var(--ocean-blue) 100%);
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

        /* Navigation Styles - Matching Index Page */
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(90deg, var(--ocean-blue), var(--sky-blue));
            color: white;
        }
        
        .navbar-brand img {
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar-brand img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .navbar-nav .nav-link {
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            transform: translateY(-2px);
        }
        
        /* Mobile Navigation Improvements */
        @media (max-width: 768px) {
            .navbar-nav {
                background: rgba(0,0,0,0.9);
                padding: 1rem;
                border-radius: 10px;
                margin-top: 1rem;
            }
            
            .navbar-nav .nav-item {
                margin-bottom: 0.5rem;
            }
            
            .dropdown-menu {
                background: rgba(0,0,0,0.8);
                border: 1px solid rgba(255,255,255,0.1);
            }
            
            .dropdown-item {
                color: white !important;
                padding: 0.5rem 1rem;
            }
        }

        /* Hero Section */
        .hero-section {
            background: var(--gradient-ocean);
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
    </style>
</head>
<body>
    <!-- Professional Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries Logo" class="me-2" style="height: 40px; width: auto; border-radius: 50%; object-fit: cover;">
                <span class="fw-bold">Salem Dominion Ministries</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
                    <li class="nav-item"><a class="nav-link" href="leadership.php"><i class="fas fa-users me-1"></i> Leadership</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="ministriesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-hands-helping me-1"></i> Ministries
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="ministries.php">All Ministries</a></li>
                            <li><a class="dropdown-item" href="children_ministry.php">Children Ministry</a></li>
                            <li><a class="dropdown-item" href="prophetic-school.php">Prophetic School</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="mediaDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-play-circle me-1"></i> Media
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="sermons.php">Sermons</a></li>
                            <li><a class="dropdown-item" href="gallery.php">Gallery</a></li>
                            <li><a class="dropdown-item" href="news.php" class="active">News & Updates</a></li>
                            <li><a class="dropdown-item" href="testimonials.php">Testimonials</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="events.php"><i class="fas fa-calendar-alt me-1"></i> Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_pastor_call.php"><i class="fas fa-phone-alt me-1"></i> Book Pastor</a></li>
                    <li class="nav-item"><a class="nav-link" href="donate.php"><i class="fas fa-heart me-1"></i> Donate</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact</a></li>
                    <?php 
                    // Check if admin is logged in
                    session_start();
                    $admin_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
                    $admin_name = $_SESSION['admin_name'] ?? 'Admin';
                    ?>
                    <?php if ($admin_logged_in): ?>
                        <!-- Admin Logged In -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-warning btn-sm text-dark" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-shield-alt me-1"></i> <?php echo htmlspecialchars($admin_name); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Admin Panel</h6></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=sermons">
                                    <i class="fas fa-book me-2"></i>Manage Sermons
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=events">
                                    <i class="fas fa-calendar me-2"></i>Manage Events
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=news">
                                    <i class="fas fa-newspaper me-2"></i>Manage News
                                </a></li>
                                <li><a class="dropdown-item" href="admin_dashboard.php?section=users">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Not Logged In -->
                        <li class="nav-item ms-2">
                            <a href="login.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-user me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item ms-1">
                            <a href="register.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                        <li class="nav-item ms-1">
                            <a href="admin_login.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-shield-alt me-1"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container" data-aos="fade-up">
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

    <!-- Admin Actions -->
    <?php if ($isAdmin): ?>
    <section class="admin-section">
        <div class="container">
            <div class="text-center mb-4">
                <button class="btn btn-warning me-2" onclick="openAddNewsModal()">
                    <i class="fas fa-plus me-2"></i>Add News
                </button>
                <button class="btn btn-info" onclick="manageNews()">
                    <i class="fas fa-cog me-2"></i>Manage News
                </button>
            </div>
        </div>
    </section>
    <?php endif; ?>

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
                    <div class="featured-news" data-aos="fade-up">
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
                            <button class="btn btn-news" onclick="showNewsDetail(<?= $featured_item['id'] ?>)">
                                <i class="fas fa-book-open me-2"></i>Read Full Story
                            </button>
                            <button class="btn btn-news">
                                <i class="fas fa-share-alt me-2"></i>Share
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Regular News Grid -->
                <div class="row g-4">
                    <?php foreach ($regular_items as $news): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= array_search($news, $regular_items) * 100 ?>">
                            <div class="news-card">
                                <div class="news-meta">
                                    <span class="news-category"><?= safe_html($news['category'] ?? 'General') ?></span>
                                    <span><i class="fas fa-calendar me-2"></i><?= format_news_date($news['created_at']) ?></span>
                                </div>
                                <h3 class="news-title"><?= safe_html($news['title']) ?></h3>
                                <p class="news-excerpt"><?= safe_html($news['excerpt'] ?? truncate_text($news['content'], 150)) ?></p>
                                <div class="news-actions">
                                    <button class="btn btn-news" onclick="showNewsDetail(<?= $news['id'] ?>)">
                                        <i class="fas fa-book-open me-2"></i>Read More
                                    </button>
                                    <button class="btn btn-news" onclick="toggleNewsComments(<?= $news['id'] ?>)">
                                        <i class="fas fa-comments me-2"></i>Comments (<?= $news['comment_count'] ?>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" data-aos="fade-up">
                    <i class="fas fa-newspaper"></i>
                    <h3>No News Found</h3>
                    <p>Check back soon for the latest updates and announcements.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Comments Section -->
    <section class="comments-section" style="display: none;" id="news-comments-section">
        <div class="container">
            <div class="comment-container">
                <h3 class="comments-title">
                    <i class="fas fa-comments me-2"></i>Comments
                </h3>
                
                <!-- Existing Comments -->
                <div id="existing-news-comments">
                    <!-- Comments will be loaded here dynamically -->
                </div>
                
                <!-- Add Comment Form -->
                <div class="comment-form">
                    <h4>Leave a Comment</h4>
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="news-comment-form">
                        <input type="hidden" name="action" value="add_comment">
                        <input type="hidden" name="content_id" id="news-comment-content-id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" name="name" id="news-comment-name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="news-comment-email" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment" class="form-label">Comment *</label>
                            <textarea name="comment" id="news-comment-text" class="form-control" rows="4" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-news">
                            <i class="fas fa-paper-plane me-2"></i>Submit Comment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <?php if (!empty($categories)): ?>
        <section class="categories-section">
            <div class="container">
                <h2 class="section-title">Browse by Category</h2>
                <div class="row g-3">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6" data-aos="fade-up">
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h4>Salem Dominion Ministries</h4>
                        <p>Empowering lives through the Word of God and the Power of the Holy Spirit.</p>
                        <div class="mt-3">
                            <a href="<?php echo CHURCH_WEBSITE; ?>" target="_blank" class="text-white me-3 social-icon">
                                <i class="fas fa-globe fa-lg"></i>
                            </a>
                            <a href="https://youtube.com/@musasizifaty?si=BxEArdVKNKVSac3X" target="_blank" class="text-white me-3 social-icon">
                                <i class="fab fa-youtube fa-lg"></i>
                            </a>
                            <a href="https://www.tiktok.com/@salem1dominionchurch?_r=1&_t=ZS-95E1n40LieS" target="_blank" class="text-white me-3 social-icon">
                                <i class="fab fa-tiktok fa-lg"></i>
                            </a>
                            <a href="https://www.facebook.com/share/1CoCEmvnBB/" target="_blank" class="text-white social-icon">
                                <i class="fab fa-facebook fa-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="about.php" class="text-white-50 text-decoration-none">About Us</a></li>
                            <li><a href="leadership.php" class="text-white-50 text-decoration-none">Leadership</a></li>
                            <li><a href="sermons.php" class="text-white-50 text-decoration-none">Sermons</a></li>
                            <li><a href="events.php" class="text-white-50 text-decoration-none">Events</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5>Services</h5>
                        <ul class="list-unstyled footer-links">
                            <li><a href="prophetic-school.php" class="text-white-50 text-decoration-none">Prophetic School</a></li>
                            <li><a href="book_pastor_call.php" class="text-white-50 text-decoration-none">Book Pastor Call</a></li>
                            <li><a href="donate.php" class="text-white-50 text-decoration-none">Give & Donate</a></li>
                            <li><a href="testimonials.php" class="text-white-50 text-decoration-none">Testimonials</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h5>Contact Info</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2 text-warning"></i>
                                <span class="text-white-50">Nampirika, Iganga District, Uganda</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-phone me-2 text-warning"></i>
                                <span class="text-white-50">+256 753 244 480</span>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-warning"></i>
                                <span class="text-white-50">info@salem-dominion-ministries.org</span>
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
        </div>
    </footer>

    <!-- News Detail Modal -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
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
                <div class="modal-header bg-success text-white">
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
                    <button type="button" class="btn btn-success" onclick="addNews()">Add News</button>
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
        // Initialize AOS
        AOS.init({
            duration: 1200,
            once: true,
            offset: 100
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
                            <span class="badge bg-primary me-2">${data.news.category || 'General'}</span>
                            <i class="far fa-calendar me-2"></i>
                            ${new Date(data.news.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
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
            console.log('News page loaded successfully at ' + new Date().toLocaleString());
        });
        
        // Store news data for comments
        const newsData = <?php echo json_encode($news_items); ?>;
        
        function toggleNewsComments(newsId) {
            const commentsSection = document.getElementById('news-comments-section');
            const existingComments = document.getElementById('existing-news-comments');
            const contentIdInput = document.getElementById('news-comment-content-id');
            
            // Set the content ID for the form
            contentIdInput.value = newsId;
            
            // Find the news data
            const news = newsData.find(n => n.id == newsId);
            
            if (news) {
                // Display existing comments
                existingComments.innerHTML = '';
                
                if (news.comments && news.comments.length > 0) {
                    news.comments.forEach(comment => {
                        const commentHtml = `
                            <div class="comment-item">
                                <div class="comment-header">
                                    <strong>${comment.name}</strong>
                                    <span class="comment-date">${new Date(comment.created_at).toLocaleDateString()}</span>
                                </div>
                                <div class="comment-content">${comment.comment}</div>
                            </div>
                        `;
                        existingComments.innerHTML += commentHtml;
                    });
                } else {
                    existingComments.innerHTML = '<p class="text-muted">No comments yet. Be the first to comment!</p>';
                }
                
                // Show the comments section
                commentsSection.style.display = 'block';
                commentsSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Add comment styles
        const commentStyles = `
            .comments-section {
                background: rgba(255, 255, 255, 0.02);
                padding: 60px 0;
                margin-top: 40px;
            }
            
            .comment-container {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(251, 191, 36, 0.3);
                border-radius: 20px;
                padding: 2rem;
                max-width: 800px;
                margin: 0 auto;
            }
            
            .comments-title {
                color: var(--heavenly-gold);
                font-family: 'Playfair Display', serif;
                font-size: 2rem;
                margin-bottom: 2rem;
                text-align: center;
            }
            
            .comment-item {
                background: rgba(255, 255, 255, 0.05);
                border-radius: 15px;
                padding: 1.5rem;
                margin-bottom: 1rem;
                border-left: 4px solid var(--heavenly-gold);
            }
            
            .comment-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 0.5rem;
            }
            
            .comment-date {
                color: rgba(255, 255, 255, 0.6);
                font-size: 0.9rem;
            }
            
            .comment-content {
                line-height: 1.6;
            }
            
            .comment-form {
                margin-top: 2rem;
                padding-top: 2rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .comment-form h4 {
                color: var(--heavenly-gold);
                margin-bottom: 1.5rem;
            }
        `;
        
        // Add styles to head
        const styleSheet = document.createElement('style');
        styleSheet.textContent = commentStyles;
        document.head.appendChild(styleSheet);
        
    </script>
</body>
</html>
