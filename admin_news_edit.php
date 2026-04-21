<?php
require_once 'config.php';\nrequire_once 'db_connection.php';

$articleId = $_GET['id'] ?? 0;
$article = null;
$errors = [];
$success = '';

if ($articleId > 0) {
    try {
        $article = $db->selectOne("SELECT * FROM news WHERE id = ?", [$articleId]);
        if (!$article) {
            $errors[] = "News article not found.";
            $articleId = 0; // Reset to add new if not found
        }
    } catch (Exception $e) {
        $errors[] = "Error fetching news article: " . $e->getMessage();
        $articleId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isBreaking = isset($_POST['is_breaking']) ? 1 : 0;
    $publishedAt = ($status === 'published' && empty($article['published_at'])) ? date('Y-m-d H:i:s') : ($article['published_at'] ?? null);

    if (empty($title) || empty($slug) || empty($content)) {
        $errors[] = 'Title, slug, and content are required.';
    }

    if (empty($errors)) {
        try {
            if ($articleId > 0) {
                // Update existing article
                $db->update(
                    "UPDATE news SET title = ?, slug = ?, excerpt = ?, content = ?, category = ?, status = ?, is_featured = ?, is_breaking = ?, published_at = ? WHERE id = ?",
                    [$title, $slug, $excerpt, $content, $category, $status, $isFeatured, $isBreaking, $publishedAt, $articleId]
                );
                // logActivity($db, $_SESSION['user_id'], "Updated news article: " . $title, "news", $articleId);
                $success = 'News article updated successfully!';
            } else {
                // Create new article
                $id = $db->insert(
                    "INSERT INTO news (title, slug, excerpt, content, author_id, category, status, is_featured, is_breaking, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$title, $slug, $excerpt, $content, 1, $category, $status, $isFeatured, $isBreaking, $publishedAt]
                );
                // logActivity($db, $_SESSION['user_id'], "Created news article: " . $title, "news", $id);
                $success = 'News article created successfully!';
                header("Location: admin_news_edit.php?id=" . $id . "&success=" . urlencode($success));
                exit;
            }
            // Refresh article data after update
            $article = $db->selectOne("SELECT * FROM news WHERE id = ?", [$articleId]);
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Default values for form
$formTitle = $article['title'] ?? '';
$formSlug = $article['slug'] ?? '';
$formExcerpt = $article['excerpt'] ?? '';
$formContent = $article['content'] ?? '';
$formCategory = $article['category'] ?? '';
$formStatus = $article['status'] ?? 'draft';
$formIsFeatured = $article['is_featured'] ?? 0;
$formIsBreaking = $article['is_breaking'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $articleId > 0 ? 'Edit News Article' : 'Add New News Article'; ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 50px; margin-bottom: 50px; }
        .card { border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-label { font-weight: bold; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
    </style>
</head>
<body>
    <!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">
            <i class="fas fa-cog me-2"></i>Admin Panel
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
            <a class="nav-link" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="card-title mb-0"><?php echo $articleId > 0 ? 'Edit News Article' : 'Add New News Article'; ?></h1>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($formTitle); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug (URL-friendly name)</label>
                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($formSlug); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Excerpt</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($formExcerpt); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($formContent); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($formCategory); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft" <?php echo $formStatus === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $formStatus === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $formStatus === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" <?php echo $formIsFeatured ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Article</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_breaking" name="is_breaking" <?php echo $formIsBreaking ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_breaking">Breaking News</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $articleId > 0 ? 'Update Article' : 'Create Article'; ?></button>
                    <a href="news.php" class="btn btn-secondary">Back to News</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Salem Dominion Ministries. All rights reserved.</p>
    </div>
</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>