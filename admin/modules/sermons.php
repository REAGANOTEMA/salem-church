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
            $description = trim($_POST['description'] ?? '');
            $preacher = trim($_POST['preacher'] ?? '');
            $sermon_date = $_POST['sermon_date'] ?? '';
            $category = trim($_POST['category'] ?? '');
            $series = trim($_POST['series'] ?? '');
            $media_type = trim($_POST['media_type'] ?? '');
            $media_url = trim($_POST['media_url'] ?? '');
            $audio_url = trim($_POST['audio_url'] ?? '');
            $pdf_url = trim($_POST['pdf_url'] ?? '');
            $scripture = trim($_POST['scripture'] ?? '');
            $duration = trim($_POST['duration'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (empty($title)) {
                if ($isAjax) jsonError('Title is required');
                setFlash('error', 'Title is required');
                redirect(BASE_URL . '/admin/modules/sermons.php?action=create');
            }

            $thumbnail = '';
            if (!empty($_FILES['thumbnail']['name'])) {
                $uploaded = uploadFile($_FILES['thumbnail'], 'sermons/thumbnails', ALLOWED_IMAGE_TYPES);
                if ($uploaded) $thumbnail = $uploaded;
            }

            $id = $db->insert('sermons', [
                'title' => $title,
                'slug' => slugify($title),
                'description' => $description,
                'preacher' => $preacher,
                'sermon_date' => $sermon_date,
                'category' => $category,
                'series' => $series,
                'media_type' => $media_type,
                'media_url' => $media_url,
                'audio_url' => $audio_url,
                'pdf_url' => $pdf_url,
                'scripture' => $scripture,
                'duration' => $duration,
                'thumbnail' => $thumbnail,
                'status' => $status,
                'is_featured' => $is_featured,
                'uploaded_by' => $_SESSION['admin_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'sermons', $_SESSION['admin_id'], "Created sermon: {$title}");
            if ($isAjax) jsonSuccess(['id' => $id], 'Sermon created successfully');
            setFlash('success', 'Sermon created successfully');
            redirect(BASE_URL . '/admin/modules/sermons.php');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $preacher = trim($_POST['preacher'] ?? '');
            $sermon_date = $_POST['sermon_date'] ?? '';
            $category = trim($_POST['category'] ?? '');
            $series = trim($_POST['series'] ?? '');
            $media_type = trim($_POST['media_type'] ?? '');
            $media_url = trim($_POST['media_url'] ?? '');
            $audio_url = trim($_POST['audio_url'] ?? '');
            $pdf_url = trim($_POST['pdf_url'] ?? '');
            $scripture = trim($_POST['scripture'] ?? '');
            $duration = trim($_POST['duration'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived']) ? $_POST['status'] : 'draft';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            if (empty($id) || empty($title)) {
                if ($isAjax) jsonError('Title is required');
                setFlash('error', 'Title is required');
                redirect(BASE_URL . '/admin/modules/sermons.php');
            }

            $updateData = [
                'title' => $title,
                'slug' => slugify($title),
                'description' => $description,
                'preacher' => $preacher,
                'sermon_date' => $sermon_date,
                'category' => $category,
                'series' => $series,
                'media_type' => $media_type,
                'media_url' => $media_url,
                'audio_url' => $audio_url,
                'pdf_url' => $pdf_url,
                'scripture' => $scripture,
                'duration' => $duration,
                'status' => $status,
                'is_featured' => $is_featured,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['thumbnail']['name'])) {
                $uploaded = uploadFile($_FILES['thumbnail'], 'sermons/thumbnails', ALLOWED_IMAGE_TYPES);
                if ($uploaded) {
                    $old = $db->fetch("SELECT thumbnail FROM sermons WHERE id = ?", [$id]);
                    if ($old && $old['thumbnail']) deleteFile($old['thumbnail']);
                    $updateData['thumbnail'] = $uploaded;
                }
            }

            $db->update('sermons', $updateData, 'id = ?', [$id]);
            logActivity($db, 'updated', 'sermons', $_SESSION['admin_id'], "Updated sermon ID: {$id}");
            if ($isAjax) jsonSuccess([], 'Sermon updated successfully');
            setFlash('success', 'Sermon updated successfully');
            redirect(BASE_URL . '/admin/modules/sermons.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $old = $db->fetch("SELECT thumbnail FROM sermons WHERE id = ?", [$id]);
                if ($old && $old['thumbnail']) deleteFile($old['thumbnail']);
                $db->delete('sermons', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'sermons', $_SESSION['admin_id'], "Deleted sermon ID: {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Sermon deleted successfully');
            setFlash('success', 'Sermon deleted successfully');
            redirect(BASE_URL . '/admin/modules/sermons.php');
            break;
    }
}

$search = trim($_GET['search'] ?? '');
$filterCategory = $_GET['category'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (title LIKE ? OR preacher LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($filterCategory) {
    $where .= " AND category = ?";
    $params[] = $filterCategory;
}

$pagination = paginate('sermons', $db, $perPage, $page, $where, $params);
$sermons = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$categories = $db->fetchAll("SELECT DISTINCT category FROM sermons WHERE category != '' ORDER BY category");
$editSermon = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editSermon = $db->fetch("SELECT * FROM sermons WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-bible me-2"></i>Sermon Management</h4>
    <button class="btn btn-primary" onclick="showCreateSermonModal()"><i class="fas fa-plus me-1"></i> Add Sermon</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="list">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search sermons..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= sanitize($cat['category']) ?>" <?= $filterCategory === $cat['category'] ? 'selected' : '' ?>><?= sanitize($cat['category']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/sermons.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($editSermon): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Sermon</h5></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editSermon['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($editSermon['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= sanitize($editSermon['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preacher</label>
                            <input type="text" name="preacher" class="form-control" value="<?= sanitize($editSermon['preacher']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sermon Date</label>
                            <input type="date" name="sermon_date" class="form-control" value="<?= $editSermon['sermon_date'] ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="<?= sanitize($editSermon['category']) ?>" list="categoryList">
                            <datalist id="categoryList">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= sanitize($cat['category']) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Series</label>
                            <input type="text" name="series" class="form-control" value="<?= sanitize($editSermon['series']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Scripture</label>
                            <input type="text" name="scripture" class="form-control" value="<?= sanitize($editSermon['scripture']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Media Type</label>
                            <select name="media_type" class="form-select">
                                <option value="">None</option>
                                <option value="youtube" <?= $editSermon['media_type'] === 'youtube' ? 'selected' : '' ?>>YouTube</option>
                                <option value="video" <?= $editSermon['media_type'] === 'video' ? 'selected' : '' ?>>Video File</option>
                                <option value="audio" <?= $editSermon['media_type'] === 'audio' ? 'selected' : '' ?>>Audio</option>
                                <option value="podcast" <?= $editSermon['media_type'] === 'podcast' ? 'selected' : '' ?>>Podcast</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Media URL</label>
                            <input type="url" name="media_url" class="form-control" value="<?= sanitize($editSermon['media_url']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" name="duration" class="form-control" placeholder="e.g. 45:00" value="<?= sanitize($editSermon['duration']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Audio URL</label>
                            <input type="url" name="audio_url" class="form-control" value="<?= sanitize($editSermon['audio_url']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PDF URL</label>
                            <input type="url" name="pdf_url" class="form-control" value="<?= sanitize($editSermon['pdf_url']) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $editSermon['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $editSermon['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="archived" <?= $editSermon['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        <?php if ($editSermon['thumbnail']): ?>
                            <div class="mt-2">
                                <img src="<?= BASE_URL . '/' . $editSermon['thumbnail'] ?>" class="img-thumbnail" style="max-height:100px">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="edit_feat" <?= $editSermon['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="edit_feat">Featured Sermon</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update Sermon</button>
                        <a href="<?= BASE_URL ?>/admin/modules/sermons.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($sermons)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bible fa-3x text-muted mb-3"></i>
                <p class="text-muted">No sermons found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sermon</th>
                            <th>Preacher</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Media</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sermons as $item): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize(truncate($item['title'], 40)) ?></strong>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge bg-warning ms-1"><i class="fas fa-star"></i></span>
                                <?php endif; ?>
                                <?php if ($item['scripture']): ?>
                                    <br><small class="text-muted"><i class="fas fa-book-open me-1"></i><?= sanitize($item['scripture']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small><?= sanitize($item['preacher'] ?: 'N/A') ?></small></td>
                            <td><small><?= $item['sermon_date'] ? formatDate($item['sermon_date']) : 'N/A' ?></small></td>
                            <td><span class="badge bg-info"><?= sanitize($item['category'] ?: 'General') ?></span></td>
                            <td>
                                <?php if ($item['media_type']): ?>
                                    <span class="badge bg-secondary"><i class="fas fa-<?= $item['media_type'] === 'youtube' ? 'youtube' : ($item['media_type'] === 'audio' ? 'headphones' : 'video') ?>"></i> <?= ucfirst($item['media_type']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = ['published' => 'success', 'draft' => 'warning', 'archived' => 'secondary'];
                                $cls = $statusClass[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= ucfirst($item['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sermon?')">
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($filterCategory) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($sermons) ?> of <?= $total ?> sermons</small>
        <?php endif; ?>
    </div>
</div>

<script>
function showCreateSermonModal() {
    const existing = document.getElementById('createSermonModal');
    if (existing) { new bootstrap.Modal(existing).show(); return; }
    const html = `
    <div class="modal fade" id="createSermonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Sermon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preacher</label>
                                <input type="text" name="preacher" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sermon Date</label>
                                <input type="date" name="sermon_date" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" list="catList">
                                <datalist id="catList">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= sanitize($cat['category']) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Series</label>
                                <input type="text" name="series" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Scripture</label>
                                <input type="text" name="scripture" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Media Type</label>
                                <select name="media_type" class="form-select">
                                    <option value="">None</option>
                                    <option value="youtube">YouTube</option>
                                    <option value="video">Video File</option>
                                    <option value="audio">Audio</option>
                                    <option value="podcast">Podcast</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Media URL</label>
                                <input type="url" name="media_url" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g. 45:00">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Audio URL</label>
                                <input type="url" name="audio_url" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">PDF URL</label>
                                <input type="url" name="pdf_url" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Thumbnail</label>
                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="c_sfeat">
                            <label class="form-check-label" for="c_sfeat">Featured Sermon</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Sermon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    new bootstrap.Modal(document.getElementById('createSermonModal')).show();
}
</script>
