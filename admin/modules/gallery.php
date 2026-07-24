<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'upload':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $album = trim($_POST['album'] ?? '');
            $category = trim($_POST['category'] ?? '');

            if (empty($_FILES['images']['name'][0])) {
                if ($isAjax) jsonError('Please select at least one image');
                setFlash('error', 'Please select at least one image');
                redirect(BASE_URL . '/admin/dashboard.php?section=gallery');
            }

            $uploaded = 0;
            $files = $_FILES['images'];
            $count = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $count; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $uploadedPath = uploadFile($file, 'gallery/image', ALLOWED_IMAGE_TYPES);
                if ($uploadedPath) {
                    $db->insert('gallery', [
                        'title' => $title,
                        'description' => $description,
                        'file_url' => $uploadedPath,
                        'file_type' => 'image',
                        'album' => $album,
                        'category' => $category,
                        'uploaded_by' => $_SESSION['admin_id'],
                        'status' => 'published',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $uploaded++;
                }
            }

            logActivity($db, 'uploaded', 'gallery', $_SESSION['admin_id'], "Uploaded {$uploaded} images to album: {$album}");
            if ($isAjax) jsonSuccess(['count' => $uploaded], "{$uploaded} images uploaded successfully");
            setFlash('success', "{$uploaded} images uploaded successfully");
            redirect(BASE_URL . '/admin/dashboard.php?section=gallery');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $album = trim($_POST['album'] ?? '');
            $category = trim($_POST['category'] ?? '');

            if (empty($id) || empty($title)) {
                if ($isAjax) jsonError('Title is required');
                setFlash('error', 'Title is required');
                redirect(BASE_URL . '/admin/dashboard.php?section=gallery');
            }

            $db->update('gallery', [
                'title' => $title,
                'description' => $description,
                'album' => $album,
                'category' => $category,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);

            logActivity($db, 'updated', 'gallery', $_SESSION['admin_id'], "Updated gallery item ID: {$id}");
            if ($isAjax) jsonSuccess([], 'Gallery item updated');
            setFlash('success', 'Gallery item updated');
            redirect(BASE_URL . '/admin/dashboard.php?section=gallery');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $old = $db->fetch("SELECT file_url FROM gallery WHERE id = ?", [$id]);
                if ($old && $old['file_url']) deleteFile($old['file_url']);
                $db->delete('gallery', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'gallery', $_SESSION['admin_id'], "Deleted gallery item ID: {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Gallery item deleted');
            setFlash('success', 'Gallery item deleted');
            redirect(BASE_URL . '/admin/dashboard.php?section=gallery');
            break;

        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $item = $db->fetch("SELECT status FROM gallery WHERE id = ?", [$id]);
                if ($item) {
                    $newStatus = $item['status'] === 'published' ? 'archived' : 'published';
                    $db->update('gallery', ['status' => $newStatus], 'id = ?', [$id]);
                }
            }
            if ($isAjax) jsonSuccess([], 'Status updated');
            setFlash('success', 'Status updated');
            redirect(BASE_URL . '/admin/dashboard.php?section=gallery');
            break;
    }
}

$filterAlbum = $_GET['album'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;

$where = '1=1';
$params = [];

if ($filterAlbum) {
    $where .= " AND album = ?";
    $params[] = $filterAlbum;
}

$pagination = paginate('gallery', $db, $perPage, $page, $where, $params);
$gallery = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$albums = $db->fetchAll("SELECT DISTINCT album FROM gallery WHERE album != '' ORDER BY album");
$albums = array_column($albums, 'album');

$editItem = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editItem = $db->fetch("SELECT * FROM gallery WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-images me-2"></i>Gallery Management</h4>
    <button class="btn btn-primary" onclick="showUploadModal()"><i class="fas fa-cloud-upload-alt me-1"></i> Upload Images</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="list">
            <div class="col-md-4">
                <label class="form-label">Filter by Album</label>
                <select name="album" class="form-select">
                    <option value="">All Albums</option>
                    <?php foreach ($albums as $alb): ?>
                    <option value="<?= sanitize($alb) ?>" <?= $filterAlbum === $alb ? 'selected' : '' ?>><?= sanitize($alb) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/gallery.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($editItem): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Gallery Item</h5></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($editItem['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= sanitize($editItem['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Album</label>
                            <input type="text" name="album" class="form-control" value="<?= sanitize($editItem['album']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="<?= sanitize($editItem['category']) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <?php if ($editItem['file_url']): ?>
                        <img src="<?= BASE_URL . '/' . $editItem['file_url'] ?>" class="img-fluid rounded mb-3" style="max-height:200px">
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update</button>
                <a href="<?= BASE_URL ?>/admin/modules/gallery.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($gallery)): ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                <p class="text-muted">No gallery items found. Upload some images!</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($gallery as $item): ?>
                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                    <div class="card h-100 gallery-card">
                        <div class="position-relative">
                            <?php if ($item['file_url']): ?>
                                <img src="<?= BASE_URL . '/' . $item['file_url'] ?>" class="card-img-top" style="height:150px; object-fit:cover;" alt="<?= sanitize($item['title']) ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:150px;">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <span class="badge bg-<?= $item['status'] === 'published' ? 'success' : 'secondary' ?> position-absolute top-0 end-0 m-1">
                                <?= $item['status'] === 'published' ? 'Active' : 'Hidden' ?>
                            </span>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-text small mb-1 text-truncate" title="<?= sanitize($item['title']) ?>"><?= sanitize($item['title']) ?></p>
                            <?php if ($item['album']): ?>
                                <p class="card-text"><small class="text-muted"><i class="fas fa-folder me-1"></i><?= sanitize($item['album']) ?></small></p>
                            <?php endif; ?>
                            <div class="btn-group btn-group-sm w-100">
                                <form method="POST" class="d-inline flex-fill">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-<?= $item['status'] === 'published' ? 'warning' : 'success' ?> btn-sm w-100" title="Toggle Status">
                                        <i class="fas fa-<?= $item['status'] === 'published' ? 'eye-slash' : 'eye' ?>"></i>
                                    </button>
                                </form>
                                <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i></a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this image?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&album=<?= urlencode($filterAlbum) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&album=<?= urlencode($filterAlbum) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&album=<?= urlencode($filterAlbum) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($gallery) ?> of <?= $total ?> items</small>
        <?php endif; ?>
    </div>
</div>

<style>
.gallery-card { transition: transform 0.2s; }
.gallery-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
</style>

<script>
function showUploadModal() {
    const existing = document.getElementById('uploadGalleryModal');
    if (existing) { new bootstrap.Modal(existing).show(); return; }
    const html = `
    <div class="modal fade" id="uploadGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="upload">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Images</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Images * (Select multiple)</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Album</label>
                            <input type="text" name="album" class="form-control" placeholder="e.g. Sunday Service, Conference 2025">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Worship, Fellowship">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    new bootstrap.Modal(document.getElementById('uploadGalleryModal')).show();
}
</script>
