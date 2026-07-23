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
            $name = trim($_POST['name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $order_position = (int)($_POST['order_position'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name)) {
                if ($isAjax) jsonError('Name is required');
                setFlash('error', 'Name is required');
                redirect(BASE_URL . '/admin/modules/leadership.php');
            }

            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $uploaded = uploadFile($_FILES['image'], 'leadership', ALLOWED_IMAGE_TYPES);
                if ($uploaded) $image = $uploaded;
            }

            $id = $db->insert('leadership', [
                'name' => $name,
                'title' => $title,
                'bio' => $bio,
                'email' => $email,
                'phone' => $phone,
                'image_url' => $image,
                'order_position' => $order_position,
                'is_active' => $is_active,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'leadership', $_SESSION['admin_id'], "Created leader: {$name}");
            if ($isAjax) jsonSuccess(['id' => $id], 'Leader added');
            setFlash('success', 'Leader added successfully');
            redirect(BASE_URL . '/admin/modules/leadership.php');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $order_position = (int)($_POST['order_position'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($id) || empty($name)) {
                if ($isAjax) jsonError('Name is required');
                setFlash('error', 'Name is required');
                redirect(BASE_URL . '/admin/modules/leadership.php');
            }

            $updateData = [
                'name' => $name,
                'title' => $title,
                'bio' => $bio,
                'email' => $email,
                'phone' => $phone,
                'order_position' => $order_position,
                'is_active' => $is_active,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['image']['name'])) {
                $uploaded = uploadFile($_FILES['image'], 'leadership', ALLOWED_IMAGE_TYPES);
                if ($uploaded) {
                    $old = $db->fetch("SELECT image_url FROM leadership WHERE id = ?", [$id]);
                    if ($old && $old['image_url']) deleteFile($old['image_url']);
                    $updateData['image_url'] = $uploaded;
                }
            }

            $db->update('leadership', $updateData, 'id = ?', [$id]);
            logActivity($db, 'updated', 'leadership', $_SESSION['admin_id'], "Updated leader ID {$id}");
            if ($isAjax) jsonSuccess([], 'Leader updated');
            setFlash('success', 'Leader updated');
            redirect(BASE_URL . '/admin/modules/leadership.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $old = $db->fetch("SELECT image_url FROM leadership WHERE id = ?", [$id]);
                if ($old && $old['image_url']) deleteFile($old['image_url']);
                $db->delete('leadership', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'leadership', $_SESSION['admin_id'], "Deleted leader ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Leader deleted');
            setFlash('success', 'Leader deleted');
            redirect(BASE_URL . '/admin/modules/leadership.php');
            break;

        case 'move':
            $id = (int)($_POST['id'] ?? 0);
            $direction = $_POST['direction'] ?? 'up';
            if ($id) {
                $current = $db->fetch("SELECT id, order_position FROM leadership WHERE id = ?", [$id]);
                if ($current) {
                    $newPos = $direction === 'up' ? $current['order_position'] - 1 : $current['order_position'] + 1;
                    $swap = $db->fetch("SELECT id FROM leadership WHERE order_position = ? AND id != ?", [$newPos, $id]);
                    if ($swap) {
                        $db->update('leadership', ['order_position' => $current['order_position'], 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$swap['id']]);
                    }
                    $db->update('leadership', ['order_position' => $newPos, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
                }
            }
            if ($isAjax) jsonSuccess([], 'Position updated');
            setFlash('success', 'Position updated');
            redirect(BASE_URL . '/admin/modules/leadership.php');
            break;
    }
}

$leaders = $db->fetchAll("SELECT * FROM leadership ORDER BY order_position ASC, created_at DESC");

$editLeader = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editLeader = $db->fetch("SELECT * FROM leadership WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Leadership Team</h4>
    <button class="btn btn-primary" onclick="showCreateLeaderModal()"><i class="fas fa-plus me-1"></i> Add Leader</button>
</div>

<?php if ($editLeader): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Leader</h5></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editLeader['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" required value="<?= sanitize($editLeader['name']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Title/Position</label>
                            <input type="text" name="title" class="form-control" value="<?= sanitize($editLeader['title']) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="4"><?= sanitize($editLeader['bio']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= sanitize($editLeader['email']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($editLeader['phone']) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($editLeader['image_url']): ?>
                            <div class="mt-2 text-center">
                                <img src="<?= BASE_URL . '/' . $editLeader['image_url'] ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="order_position" class="form-control" value="<?= $editLeader['order_position'] ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="el_active" <?= $editLeader['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="el_active">Active</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update</button>
                        <a href="<?= BASE_URL ?>/admin/modules/leadership.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (empty($leaders)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">No leaders found. Add your first leader!</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($leaders as $idx => $leader): ?>
        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <?php if ($leader['image_url']): ?>
                        <img src="<?= BASE_URL . '/' . $leader['image_url'] ?>" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width:100px;height:100px;font-size:36px;">
                            <?= strtoupper(substr($leader['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h5 class="mb-0"><?= sanitize($leader['name']) ?></h5>
                    <?php if ($leader['title']): ?>
                        <p class="text-muted mb-1"><?= sanitize($leader['title']) ?></p>
                    <?php endif; ?>
                    <?php if ($leader['bio']): ?>
                        <p class="small text-muted"><?= sanitize(truncate($leader['bio'], 100)) ?></p>
                    <?php endif; ?>
                    <?php if ($leader['email']): ?>
                        <p class="small mb-0"><i class="fas fa-envelope me-1"></i><?= sanitize($leader['email']) ?></p>
                    <?php endif; ?>
                    <?php if ($leader['phone']): ?>
                        <p class="small mb-0"><i class="fas fa-phone me-1"></i><?= sanitize($leader['phone']) ?></p>
                    <?php endif; ?>
                    <span class="badge bg-<?= $leader['is_active'] ? 'success' : 'secondary' ?> mt-2"><?= $leader['is_active'] ? 'Active' : 'Inactive' ?></span>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="btn-group btn-group-sm">
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="move">
                                <input type="hidden" name="id" value="<?= $leader['id'] ?>">
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="btn btn-outline-secondary" title="Move Up" <?= $idx === 0 ? 'disabled' : '' ?>><i class="fas fa-arrow-up"></i></button>
                            </form>
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="move">
                                <input type="hidden" name="id" value="<?= $leader['id'] ?>">
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="btn btn-outline-secondary" title="Move Down" <?= $idx === count($leaders) - 1 ? 'disabled' : '' ?>><i class="fas fa-arrow-down"></i></button>
                            </form>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="?action=edit&id=<?= $leader['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this leader?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $leader['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal fade" id="createLeaderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Leader</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title/Position</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Senior Pastor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="order_position" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="c_lactive" checked>
                                <label class="form-check-label" for="c_lactive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Add Leader</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateLeaderModal() {
    new bootstrap.Modal(document.getElementById('createLeaderModal')).show();
}
</script>
