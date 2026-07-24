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
            $description = trim($_POST['description'] ?? '');
            $leader_name = trim($_POST['leader_name'] ?? '');
            $leader_email = trim($_POST['leader_email'] ?? '');
            $leader_phone = trim($_POST['leader_phone'] ?? '');
            $meeting_day = trim($_POST['meeting_day'] ?? '');
            $meeting_time = trim($_POST['meeting_time'] ?? '');
            $meeting_location = trim($_POST['meeting_location'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $sort_order = (int)($_POST['sort_order'] ?? 0);

            if (empty($name)) {
                if ($isAjax) jsonError('Ministry name is required');
                setFlash('error', 'Ministry name is required');
                redirect(BASE_URL . '/admin/modules/ministries.php');
            }

            $image = '';
            if (!empty($_FILES['image']['name'])) {
                $uploaded = uploadFile($_FILES['image'], 'ministries', ALLOWED_IMAGE_TYPES);
                if ($uploaded) $image = $uploaded;
            }

            $validCategories = ['children','youth','men','women','outreach','worship','prayer','other'];
            $category = in_array($category, $validCategories) ? $category : 'other';

            $id = $db->insert('ministries', [
                'name' => $name,
                'slug' => slugify($name),
                'description' => $description,
                'leader_name' => $leader_name,
                'leader_email' => $leader_email,
                'leader_phone' => $leader_phone,
                'meeting_day' => $meeting_day,
                'meeting_time' => $meeting_time,
                'meeting_location' => $meeting_location,
                'category' => $category,
                'image_url' => $image,
                'is_active' => $is_active,
                'sort_order' => $sort_order,
                'created_by' => $_SESSION['admin_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'ministries', $_SESSION['admin_id'], "Created ministry: {$name}");
            if ($isAjax) jsonSuccess(['id' => $id], 'Ministry created');
            setFlash('success', 'Ministry created successfully');
            redirect(BASE_URL . '/admin/modules/ministries.php');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $leader_name = trim($_POST['leader_name'] ?? '');
            $leader_email = trim($_POST['leader_email'] ?? '');
            $leader_phone = trim($_POST['leader_phone'] ?? '');
            $meeting_day = trim($_POST['meeting_day'] ?? '');
            $meeting_time = trim($_POST['meeting_time'] ?? '');
            $meeting_location = trim($_POST['meeting_location'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $sort_order = (int)($_POST['sort_order'] ?? 0);

            if (empty($id) || empty($name)) {
                if ($isAjax) jsonError('Ministry name is required');
                setFlash('error', 'Ministry name is required');
                redirect(BASE_URL . '/admin/modules/ministries.php');
            }

            $validCategories = ['children','youth','men','women','outreach','worship','prayer','other'];
            $category = in_array($category, $validCategories) ? $category : 'other';

            $updateData = [
                'name' => $name,
                'slug' => slugify($name),
                'description' => $description,
                'leader_name' => $leader_name,
                'leader_email' => $leader_email,
                'leader_phone' => $leader_phone,
                'meeting_day' => $meeting_day,
                'meeting_time' => $meeting_time,
                'meeting_location' => $meeting_location,
                'category' => $category,
                'is_active' => $is_active,
                'sort_order' => $sort_order,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['image']['name'])) {
                $uploaded = uploadFile($_FILES['image'], 'ministries', ALLOWED_IMAGE_TYPES);
                if ($uploaded) {
                    $old = $db->fetch("SELECT image_url FROM ministries WHERE id = ?", [$id]);
                    if ($old && $old['image_url']) deleteFile($old['image_url']);
                    $updateData['image_url'] = $uploaded;
                }
            }

            $db->update('ministries', $updateData, 'id = ?', [$id]);
            logActivity($db, 'updated', 'ministries', $_SESSION['admin_id'], "Updated ministry ID {$id}");
            if ($isAjax) jsonSuccess([], 'Ministry updated');
            setFlash('success', 'Ministry updated');
            redirect(BASE_URL . '/admin/modules/ministries.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $old = $db->fetch("SELECT image_url FROM ministries WHERE id = ?", [$id]);
                if ($old && $old['image_url']) deleteFile($old['image_url']);
                $db->delete('ministries', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'ministries', $_SESSION['admin_id'], "Deleted ministry ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Ministry deleted');
            setFlash('success', 'Ministry deleted');
            redirect(BASE_URL . '/admin/modules/ministries.php');
            break;
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];
if ($search) {
    $where .= " AND (name LIKE ? OR description LIKE ? OR leader_name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$pagination = paginate('ministries', $db, $perPage, $page, $where, $params);
$ministries = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$editMinistry = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editMinistry = $db->fetch("SELECT * FROM ministries WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-hands-helping me-2"></i>Church Ministries</h4>
    <button class="btn btn-primary" onclick="showCreateMinistryModal()"><i class="fas fa-plus me-1"></i> Add Ministry</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search ministries..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search"></i> Search</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/ministries.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($editMinistry): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Ministry</h5></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editMinistry['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Ministry Name *</label>
                        <input type="text" name="name" class="form-control" required value="<?= sanitize($editMinistry['name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= sanitize($editMinistry['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leader Name</label>
                            <input type="text" name="leader_name" class="form-control" value="<?= sanitize($editMinistry['leader_name']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leader Email</label>
                            <input type="email" name="leader_email" class="form-control" value="<?= sanitize($editMinistry['leader_email']) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leader Phone</label>
                            <input type="text" name="leader_phone" class="form-control" value="<?= sanitize($editMinistry['leader_phone']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Day</label>
                            <select name="meeting_day" class="form-select">
                                <option value="">Select Day</option>
                                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                                <option value="<?= $day ?>" <?= $editMinistry['meeting_day'] === $day ? 'selected' : '' ?>><?= $day ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Time</label>
                            <input type="time" name="meeting_time" class="form-control" value="<?= $editMinistry['meeting_time'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Location</label>
                            <input type="text" name="meeting_location" class="form-control" value="<?= sanitize($editMinistry['meeting_location']) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($editMinistry['image_url']): ?>
                            <div class="mt-2"><img src="<?= BASE_URL . '/' . $editMinistry['image_url'] ?>" class="img-thumbnail" style="max-height:120px"></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <?php foreach (['children'=>'Children','youth'=>'Youth','men'=>'Men','women'=>'Women','outreach'=>'Outreach','worship'=>'Worship','prayer'=>'Prayer','other'=>'Other'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($editMinistry['category'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $editMinistry['sort_order'] ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="em_active" <?= $editMinistry['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="em_active">Active</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update Ministry</button>
                        <a href="<?= BASE_URL ?>/admin/modules/ministries.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($ministries)): ?>
            <div class="text-center py-5">
                <i class="fas fa-hands-helping fa-3x text-muted mb-3"></i>
                <p class="text-muted">No ministries found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ministry</th>
                            <th>Leader</th>
                            <th>Meeting</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ministries as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?= BASE_URL . '/' . $item['image_url'] ?>" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded bg-info text-white d-flex align-items-center justify-content-center me-2" style="width:40px;height:40px;font-size:14px;"><i class="fas fa-hands-helping"></i></div>
                                    <?php endif; ?>
                                    <strong><?= sanitize(truncate($item['name'], 30)) ?></strong>
                                </div>
                            </td>
                            <td><small><?= sanitize($item['leader_name'] ?: 'N/A') ?></small></td>
                            <td>
                                <?php if ($item['meeting_day']): ?>
                                    <small><i class="fas fa-calendar me-1"></i><?= $item['meeting_day'] ?></small>
                                    <?php if ($item['meeting_time']): ?>
                                        <br><small><i class="fas fa-clock me-1"></i><?= date('g:i A', strtotime($item['meeting_time'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small class="text-muted">Not set</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= sanitize($item['category'] ?: 'General') ?></span></td>
                            <td><span class="badge bg-<?= $item['is_active'] ? 'success' : 'secondary' ?>"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this ministry?')">
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($ministries) ?> of <?= $total ?> ministries</small>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createMinistryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Ministry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ministry Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leader Name</label>
                            <input type="text" name="leader_name" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leader Email</label>
                            <input type="email" name="leader_email" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Leader Phone</label>
                            <input type="text" name="leader_phone" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Day</label>
                            <select name="meeting_day" class="form-select">
                                <option value="">Select Day</option>
                                <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Time</label>
                            <input type="time" name="meeting_time" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Meeting Location</label>
                            <input type="text" name="meeting_location" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <?php foreach (['children'=>'Children','youth'=>'Youth','men'=>'Men','women'=>'Women','outreach'=>'Outreach','worship'=>'Worship','prayer'=>'Prayer','other'=>'Other'] as $val => $lbl): ?>
                                <option value="<?= $val ?>"><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="c_mactive" checked>
                        <label class="form-check-label" for="c_mactive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Ministry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateMinistryModal() {
    new bootstrap.Modal(document.getElementById('createMinistryModal')).show();
}
</script>
