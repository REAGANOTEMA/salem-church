<?php
// Get global database connection
$conn = $GLOBALS['admin_db_connection'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_news':
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $category = $_POST['category'] ?? '';
                $author = $_POST['author'] ?? 'Admin';
                
                if (!empty($title) && !empty($content)) {
                    $stmt = $conn->prepare("INSERT INTO news (title, content, category, author, created_at) VALUES (?, ?, ?, ?, NOW())");
                    if ($stmt) {
                        $stmt->bind_param("ssss", $title, $content, $category, $author);
                        $stmt->execute();
                        $success = "News article added successfully!";
                    }
                }
                break;
                
            case 'edit_news':
                $id = $_POST['id'] ?? '';
                $title = $_POST['title'] ?? '';
                $content = $_POST['content'] ?? '';
                $category = $_POST['category'] ?? '';
                $author = $_POST['author'] ?? 'Admin';
                
                if (!empty($id) && !empty($title)) {
                    $stmt = $conn->prepare("UPDATE news SET title=?, content=?, category=?, author=? WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("ssssi", $title, $content, $category, $author, $id);
                        $stmt->execute();
                        $success = "News article updated successfully!";
                    }
                }
                break;
                
            case 'delete_news':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM news WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "News article deleted successfully!";
                    }
                }
                break;
        }
    }
}

// Get news articles from database
$news_articles = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 20");
        if ($result) {
            $news_articles = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch news articles: ' . $e->getMessage();
    }
}
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="content-header">
    <h1 class="page-title"><?php echo getLogoImg(30, 30, 'margin-right: 10px'); ?>News Management</h1>
    <p class="page-subtitle">Create and manage news articles and announcements</p>
</div>

<!-- Add New News Form -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-plus-circle"></i>
        Add New Article
    </h2>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_news">
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="title" class="form-label">Article Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="announcement">Announcement</option>
                        <option value="event-update">Event Update</option>
                        <option value="sermon-series">Sermon Series</option>
                        <option value="community-news">Community News</option>
                        <option value="testimony">Testimony</option>
                        <option value="outreach">Outreach</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="excerpt" class="form-label">Excerpt</label>
            <input type="text" id="excerpt" name="excerpt" class="form-control" placeholder="Brief summary of the article">
            <small class="text-muted">A short summary that will appear in news listings</small>
        </div>
        
        <div class="form-group">
            <label for="featured_image" class="form-label">Featured Image</label>
            <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
            <small class="text-muted">Upload a featured image for the article (JPG, PNG, GIF)</small>
        </div>
        
        <div class="form-group">
            <label for="content" class="form-label">Article Content *</label>
            <textarea id="content" name="content" class="form-control" rows="8" required></textarea>
            <small class="text-muted">Write the full article content here</small>
        </div>
        
        <button type="submit" class="btn-submit">
            <i class="fas fa-newspaper"></i> Publish Article
        </button>
    </form>
</div>

<!-- Existing News Articles -->
<div class="data-table">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i>
            Published Articles
        </h3>
        <span class="badge bg-primary"><?php echo count($news_articles); ?> Total</span>
    </div>
    
    <?php if (empty($news_articles)): ?>
        <div class="empty-state">
            <i class="fas fa-newspaper fa-3x mb-3"></i>
            <h4>No Articles Found</h4>
            <p>Start by creating your first news article using the form above.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news_articles as $article): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                                <?php if (!empty($article['excerpt'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($article['excerpt'], 0, 100)); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($article['category']); ?></span>
                            </td>
                            <td>
                                <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $article['status'] === 'published' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($article['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewArticle(<?php echo $article['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editArticle(<?php echo $article['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="item_type" value="news">
                                        <input type="hidden" name="item_id" value="<?php echo $article['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this article?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-title {
    color: #0f172a;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-title i {
    color: #fbbf24;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #0f172a;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.btn-submit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
}

.data-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table {
    margin: 0;
}

.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #0f172a;
    border: none;
    padding: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.9rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-color: #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
}

.btn-view {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #0f172a;
}

.btn-edit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    margin-right: 6px;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state i {
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.bg-primary { background: #0ea5e9; }
.bg-info { background: #3b82f6; }
.bg-success { background: #10b981; }
.bg-warning { background: #f59e0b; }
.bg-danger { background: #ef4444; }

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .form-section {
        padding: 1rem;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function viewArticle(id) {
    // Implement view article functionality
    alert('View article ID: ' + id);
}

function editArticle(id) {
    // Implement edit article functionality
    alert('Edit article ID: ' + id);
}
</script>
