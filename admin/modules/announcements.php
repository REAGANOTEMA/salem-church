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
            $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'expired']) ? $_POST['status'] : 'active';

            if (empty($title) || empty($content)) {
                if ($isAjax) jsonError('Title and content are required');
                setFlash('error', 'Title and content are required');
                redirect(BASE_URL . '/admin/dashboard.php?section=announcements');
            }

            $id = $db->insert('announcements', [
                'title' => $title,
                'content' => $content,
                'is_pinned' => $is_pinned,
                'start_date' => $start_date ?: date('Y-m-d'),
                'end_date' => $end_date,
                'status' => $status,
                'created_by' => $_SESSION['admin_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'announcements', $_SESSION['admin_id'], "Created announcement: {$title}");
            if ($isAjax) jsonSuccess(['id' => $id], 'Announcement created');
            setFlash('success', 'Announcement created');
            redirect(BASE_URL . '/admin/dashboard.php?section=announcements');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'expired']) ? $_POST['status'] : 'active';

            if (empty($id) || empty($title) || empty($content)) {
                if ($isAjax) jsonError('Title and content are required');
                setFlash('error', 'Title and content are required');
                redirect(BASE_URL . '/admin/dashboard.php?section=announcements');
            }

            $db->update('announcements', [
                'title' => $title,
                'content' => $content,
                'is_pinned' => $is_pinned,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);

            logActivity($db, 'updated', 'announcements', $_SESSION['admin_id'], "Updated announcement ID {$id}");
            if ($isAjax) jsonSuccess([], 'Announcement updated');
            setFlash('success', 'Announcement updated');
            redirect(BASE_URL . '/admin/dashboard.php?section=announcements');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->delete('announcements', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'announcements', $_SESSION['admin_id'], "Deleted announcement ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Announcement deleted');
            setFlash('success', 'Announcement deleted');
            redirect(BASE_URL . '/admin/dashboard.php?section=announcements');
            break;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;
$pagination = paginate('announcements', $db, $perPage, $page);
$announcements = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$editAnnouncement = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editAnnouncement = $db->fetch("SELECT * FROM announcements WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h4>
    <button class="btn btn-primary" onclick="showCreateAnnouncementModal()"><i class="fas fa-plus me-1"></i> Add Announcement</button>
</div>

<?php if ($editAnnouncement): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Announcement</h5></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editAnnouncement['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($editAnnouncement['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-control" rows="6" required><?= sanitize($editAnnouncement['content']) ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $editAnnouncement['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $editAnnouncement['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="expired" <?= $editAnnouncement['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $editAnnouncement['start_date'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $editAnnouncement['end_date'] ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_pinned" class="form-check-input" id="ea_pinned" <?= $editAnnouncement['is_pinned'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ea_pinned">Pin to Top</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update</button>
                        <a href="<?= BASE_URL ?>/admin/modules/announcements.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($announcements)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <p class="text-muted">No announcements found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th>Pinned</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $item): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($item['title']) ?></strong>
                            </td>
                            <td><small><?= sanitize(truncate($item['content'], 80)) ?></small></td>
                            <td>
                                <small>
                                    <i class="fas fa-calendar me-1"></i><?= $item['start_date'] ? formatDate($item['start_date']) : 'N/A' ?>
                                    <?php if ($item['end_date']): ?>
                                        <br><i class="fas fa-arrow-right me-1"></i><?= formatDate($item['end_date']) ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-<?= ['active'=>'success','inactive'=>'secondary','expired'=>'danger'][$item['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </td>
                            <td><?= $item['is_pinned'] ? '<i class="fas fa-thumbtack text-primary"></i>' : '<i class="fas fa-thumbtack text-muted"></i>' ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this announcement?')">
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($announcements) ?> of <?= $total ?> announcements</small>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createAnnouncementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-bullhorn me-2"></i>New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_pinned" class="form-check-input" id="c_apinned">
                        <label class="form-check-label" for="c_apinned">Pin to Top</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateAnnouncementModal() {
    new bootstrap.Modal(document.getElementById('createAnnouncementModal')).show();
}
</script>
