<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'create':
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (empty($title) || empty($content)) {
                if ($isAjax) jsonError('Title and content are required');
                setFlash('error', 'Title and content are required');
                redirect(BASE_URL . '/admin/dashboard.php?section=news&action=create');
            }

            $featured_image = '';
            if (!empty($_FILES['featured_image']['name'])) {
                $uploaded = uploadFile($_FILES['featured_image'], 'news', ALLOWED_IMAGE_TYPES);
                if ($uploaded) $featured_image = $uploaded;
            }

            $id = $db->insert('news', [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'category' => $category,
                'featured_image' => $featured_image,
                'status' => $status,
                'is_featured' => $is_featured,
                'author_id' => $_SESSION['admin_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'news', $_SESSION['admin_id'], "Created news: {$title}");
            if ($isAjax) jsonSuccess(['id' => $id], 'News created successfully');
            setFlash('success', 'News created successfully');
            redirect(BASE_URL . '/admin/dashboard.php?section=news');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (empty($id) || empty($title) || empty($content)) {
                if ($isAjax) jsonError('All required fields must be filled');
                setFlash('error', 'All required fields must be filled');
                redirect(BASE_URL . '/admin/dashboard.php?section=news');
            }

            $updateData = [
                'title' => $title,
                'content' => $content,
                'excerpt' => $excerpt,
                'category' => $category,
                'status' => $status,
                'is_featured' => $is_featured,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['featured_image']['name'])) {
                $uploaded = uploadFile($_FILES['featured_image'], 'news', ALLOWED_IMAGE_TYPES);
                if ($uploaded) {
                    $old = $db->fetch("SELECT featured_image FROM news WHERE id = ?", [$id]);
                    if ($old && $old['featured_image']) deleteFile($old['featured_image']);
                    $updateData['featured_image'] = $uploaded;
                }
            }

            $db->update('news', $updateData, 'id = ?', [$id]);
            logActivity($db, 'updated', 'news', $_SESSION['admin_id'], "Updated news ID: {$id}");
            if ($isAjax) jsonSuccess([], 'News updated successfully');
            setFlash('success', 'News updated successfully');
            redirect(BASE_URL . '/admin/dashboard.php?section=news');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $old = $db->fetch("SELECT featured_image FROM news WHERE id = ?", [$id]);
                if ($old && $old['featured_image']) deleteFile($old['featured_image']);
                $db->delete('news', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'news', $_SESSION['admin_id'], "Deleted news ID: {$id}");
            }
            if ($isAjax) jsonSuccess([], 'News deleted successfully');
            setFlash('success', 'News deleted successfully');
            redirect(BASE_URL . '/admin/dashboard.php?section=news');
            break;

        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $news = $db->fetch("SELECT status FROM news WHERE id = ?", [$id]);
                if ($news) {
                    $newStatus = $news['status'] === 'published' ? 'draft' : 'published';
                    $db->update('news', ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                    logActivity($db, 'updated', 'news', $_SESSION['admin_id'], "Toggled news ID {$id} to {$newStatus}");
                    if ($isAjax) jsonSuccess(['status' => $newStatus], 'Status updated');
                    setFlash('success', 'Status updated');
                }
            }
            redirect(BASE_URL . '/admin/dashboard.php?section=news');
            break;
    }
}

$search = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($filterStatus && in_array($filterStatus, ['draft', 'published', 'archived'])) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagination = paginate('news', $db, $perPage, $page, $where, $params);
$news = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$editNews = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editNews = $db->fetch("SELECT * FROM news WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-newspaper me-2"></i>News Management</h4>
    <button class="btn btn-primary" onclick="showCreateForm()"><i class="fas fa-plus me-1"></i> Add News</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="list">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search news..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $filterStatus === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $filterStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/news.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($editNews): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit News</h5></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="newsForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editNews['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($editNews['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-control" rows="10" required><?= sanitize($editNews['content']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="3"><?= sanitize($editNews['excerpt']) ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?= sanitize($editNews['category']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $editNews['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $editNews['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="archived" <?= $editNews['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Featured Image</label>
                        <input type="file" name="featured_image" class="form-control" accept="image/*">
                        <?php if ($editNews['featured_image']): ?>
                            <div class="mt-2">
                                <img src="<?= BASE_URL . '/' . $editNews['featured_image'] ?>" class="img-thumbnail" style="max-height:100px">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" <?= $editNews['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Featured</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update News</button>
                        <a href="<?= BASE_URL ?>/admin/modules/news.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($news)): ?>
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <p class="text-muted">No news articles found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $item): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize(truncate($item['title'], 50)) ?></strong>
                                <?php if ($item['excerpt']): ?>
                                    <br><small class="text-muted"><?= sanitize(truncate($item['excerpt'], 80)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info"><?= sanitize($item['category'] ?: 'Uncategorized') ?></span></td>
                            <td>
                                <?php
                                $statusClass = ['published' => 'success', 'draft' => 'warning', 'archived' => 'secondary'];
                                $cls = $statusClass[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= ucfirst($item['status']) ?></span>
                            </td>
                            <td><?= $item['is_featured'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>' ?></td>
                            <td><small><?= timeAgo($item['created_at']) ?></small></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-<?= $item['status'] === 'published' ? 'warning' : 'success' ?>" title="<?= $item['status'] === 'published' ? 'Unpublish' : 'Publish' ?>">
                                            <i class="fas fa-<?= $item['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </form>
                                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this news article?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $filterStatus ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $filterStatus ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $filterStatus ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($news) ?> of <?= $total ?> articles</small>
        <?php endif; ?>
    </div>
</div>

<script>
function showCreateForm() {
    const modal = new bootstrap.Modal(document.getElementById('createNewsModal') || createModal());
    modal.show();
}

function createModal() {
    const modalHtml = `
    <div class="modal fade" id="createNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Create News</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content *</label>
                            <textarea name="content" class="form-control" rows="8" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Excerpt</label>
                            <textarea name="excerpt" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="create_featured">
                            <label class="form-check-label" for="create_featured">Featured Article</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create News</button>
                    </div>
                </form>
            </div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    return document.getElementById('createNewsModal');
}
</script>
