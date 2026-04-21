<?php
// EDIT NEWS PAGE - Salem Dominion Ministries
// Edit existing news articles

session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get news ID
$news_id = intval($_GET['id'] ?? 0);
if ($news_id <= 0) {
    header('Location: admin_dashboard.php?section=news');
    exit;
}

// Initialize variables
$success = '';
$error = '';
$news = null;

// Get news data
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $news = $result->fetch_assoc();
        $stmt->close();
        
        if (!$news) {
            $error = "News article not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading news article: " . $e->getMessage();
    }
} else {
    $error = "Database connection failed.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $news) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_news') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $category = trim($_POST['category'] ?? '');
        
        if (empty($title) || empty($content)) {
            $error = 'Please fill in all required fields.';
        } else {
            if ($conn) {
                try {
                    // Handle featured image upload if provided
                    $image_url = $news['featured_image']; // Keep existing image URL
                    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['featured_image'];
                        if (strpos($file['type'], 'image/') === 0) {
                            $upload_dir = "uploads/news/";
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            // Delete old image if exists
                            if (!empty($news['featured_image']) && file_exists($news['featured_image'])) {
                                unlink($news['featured_image']);
                            }
                            
                            $filename = uniqid() . '_' . basename($file['name']);
                            $filepath = $upload_dir . $filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $image_url = $filepath;
                            }
                        }
                    }
                    
                    $stmt = $conn->prepare("UPDATE news SET title = ?, content = ?, excerpt = ?, category = ?, featured_image = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $title, $content, $excerpt, $category, $image_url, $news_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $success = 'News article updated successfully!';
                    
                    // Refresh news data
                    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
                    $stmt->bind_param("i", $news_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $news = $result->fetch_assoc();
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $error = 'Failed to update news article: ' . $e->getMessage();
                }
            } else {
                $error = 'Database connection required.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News Article - Salem Dominion Ministries</title>
    <link rel="icon" href="public/logo-icon.jpeg">
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --heavenly-gold: #fbbf24;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .edit-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--snow-white);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--heavenly-gold);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: var(--snow-white);
        }

        .form-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            color: var(--snow-white);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.25);
            color: var(--snow-white);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--heavenly-gold), #f59e0b);
            color: var(--midnight-blue);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .current-image {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
        }

        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 1rem;
                margin: 0;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <a href="admin_dashboard.php?section=news" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to News
        </a>

        <div class="page-header">
            <h1 class="page-title">Edit News Article</h1>
            <p>Update article content and media</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($news): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_news">
                
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label" for="title">Article Title *</label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($news['title']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="category">Category</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="general" <?php echo $news['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                                    <option value="announcement" <?php echo $news['category'] === 'announcement' ? 'selected' : ''; ?>>Announcement</option>
                                    <option value="testimony" <?php echo $news['category'] === 'testimony' ? 'selected' : ''; ?>>Testimony</option>
                                    <option value="ministry" <?php echo $news['category'] === 'ministry' ? 'selected' : ''; ?>>Ministry</option>
                                    <option value="community" <?php echo $news['category'] === 'community' ? 'selected' : ''; ?>>Community</option>
                                    <option value="youth" <?php echo $news['category'] === 'youth' ? 'selected' : ''; ?>>Youth</option>
                                    <option value="outreach" <?php echo $news['category'] === 'outreach' ? 'selected' : ''; ?>>Outreach</option>
                                    <option value="special" <?php echo $news['category'] === 'special' ? 'selected' : ''; ?>>Special</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="excerpt">Excerpt/Summary</label>
                        <input type="text" id="excerpt" name="excerpt" class="form-control" 
                               value="<?php echo htmlspecialchars($news['excerpt'] ?? ''); ?>" 
                               placeholder="Brief summary (optional)">
                        <small class="text-muted">A short summary that will appear in news listings</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="content">Article Content *</label>
                        <textarea id="content" name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($news['content']); ?></textarea>
                        <small class="text-muted">You can use HTML tags for formatting (bold, italic, links, etc.)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="featured_image">Replace Featured Image</label>
                                <input type="file" id="featured_image" name="featured_image" class="form-control" 
                                       accept="image/*">
                                <small class="text-muted">Leave empty to keep current image. Supports JPG, PNG, GIF, WebP (max 5MB)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($news['featured_image'])): ?>
                                <div class="current-image">
                                    <h6 style="color: var(--heavenly-gold); margin-bottom: 1rem;">Current Image</h6>
                                    <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" alt="Featured">
                                    <br><small class="text-muted"><?php echo htmlspecialchars($news['featured_image']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>
                    Update Article
                </button>
            </form>
        <?php else: ?>
            <div class="text-center" style="color: var(--snow-white); padding: 3rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>News article not found or unable to load.</p>
                <a href="admin_dashboard.php?section=news" class="btn-back">Back to News</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
