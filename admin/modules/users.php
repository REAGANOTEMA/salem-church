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
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role = $_POST['role'] ?? 'volunteer';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            $validRoles = ['super_admin', 'admin', 'editor', 'media_team', 'pastor', 'secretary', 'finance', 'volunteer'];
            if (!in_array($role, $validRoles)) $role = 'volunteer';

            if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
                if ($isAjax) jsonError('Username, password, full name, and email are required');
                setFlash('error', 'Username, password, full name, and email are required');
                redirect(BASE_URL . '/admin/modules/users.php?action=create');
            }

            if (strlen($password) < 8) {
                if ($isAjax) jsonError('Password must be at least 8 characters');
                setFlash('error', 'Password must be at least 8 characters');
                redirect(BASE_URL . '/admin/modules/users.php?action=create');
            }

            if (!validateEmail($email)) {
                if ($isAjax) jsonError('Invalid email address');
                setFlash('error', 'Invalid email address');
                redirect(BASE_URL . '/admin/modules/users.php?action=create');
            }

            $existing = $db->fetch("SELECT id FROM admin_users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existing) {
                if ($isAjax) jsonError('Username or email already exists');
                setFlash('error', 'Username or email already exists');
                redirect(BASE_URL . '/admin/modules/users.php?action=create');
            }

            $id = $db->insert('admin_users', [
                'username' => $username,
                'password' => password_hash($password, HASH_ALGO, ['cost' => HASH_COST]),
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'is_active' => $is_active,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'users', $_SESSION['admin_id'], "Created admin user: {$username}");
            if ($isAjax) jsonSuccess(['id' => $id], 'Admin user created');
            setFlash('success', 'Admin user created successfully');
            redirect(BASE_URL . '/admin/modules/users.php');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role = $_POST['role'] ?? 'volunteer';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $username = trim($_POST['username'] ?? '');

            if (empty($id) || empty($full_name) || empty($email)) {
                if ($isAjax) jsonError('Full name and email are required');
                setFlash('error', 'Full name and email are required');
                redirect(BASE_URL . '/admin/modules/users.php');
            }

            $validRoles = ['super_admin', 'admin', 'editor', 'media_team', 'pastor', 'secretary', 'finance', 'volunteer'];
            if (!in_array($role, $validRoles)) $role = 'volunteer';

            $existing = $db->fetch("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?", [$username, $email, $id]);
            if ($existing) {
                if ($isAjax) jsonError('Username or email already exists');
                setFlash('error', 'Username or email already exists');
                redirect(BASE_URL . '/admin/modules/users.php');
            }

            $updateData = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'is_active' => $is_active,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($username) $updateData['username'] = $username;

            $db->update('admin_users', $updateData, 'id = ?', [$id]);
            logActivity($db, 'updated', 'users', $_SESSION['admin_id'], "Updated admin user ID {$id}");
            if ($isAjax) jsonSuccess([], 'Admin user updated');
            setFlash('success', 'Admin user updated');
            redirect(BASE_URL . '/admin/modules/users.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id && $id != $_SESSION['admin_id']) {
                $db->delete('admin_users', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'users', $_SESSION['admin_id'], "Deleted admin user ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Admin user deleted');
            setFlash('success', 'Admin user deleted');
            redirect(BASE_URL . '/admin/modules/users.php');
            break;

        case 'reset_password':
            $id = (int)($_POST['id'] ?? 0);
            $newPassword = $_POST['new_password'] ?? '';

            if (empty($id) || empty($newPassword)) {
                if ($isAjax) jsonError('User ID and new password are required');
                setFlash('error', 'User ID and new password are required');
                redirect(BASE_URL . '/admin/modules/users.php');
            }

            if (strlen($newPassword) < 8) {
                if ($isAjax) jsonError('Password must be at least 8 characters');
                setFlash('error', 'Password must be at least 8 characters');
                redirect(BASE_URL . '/admin/modules/users.php');
            }

            $db->update('admin_users', [
                'password' => password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$id]);

            logActivity($db, 'updated', 'users', $_SESSION['admin_id'], "Reset password for admin user ID {$id}");
            if ($isAjax) jsonSuccess([], 'Password reset successfully');
            setFlash('success', 'Password reset successfully');
            redirect(BASE_URL . '/admin/modules/users.php');
            break;
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$pagination = paginate('admin_users', $db, $perPage, $page, $where, $params);
$users = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$editUser = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editUser = $db->fetch("SELECT * FROM admin_users WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i>Admin Users</h4>
    <button class="btn btn-primary" onclick="showCreateUserModal()"><i class="fas fa-plus me-1"></i> Add Admin</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, username, or email..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i> Search</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/users.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($editUser): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Admin User</h5></div>
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required value="<?= sanitize($editUser['username']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required value="<?= sanitize($editUser['full_name']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= sanitize($editUser['email']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($editUser['phone']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <?php foreach (['super_admin' => 'Super Admin', 'admin' => 'Admin', 'editor' => 'Editor', 'media_team' => 'Media Team', 'pastor' => 'Pastor', 'secretary' => 'Secretary', 'finance' => 'Finance', 'volunteer' => 'Volunteer'] as $val => $lbl): ?>
                                <option value="<?= $val ?>" <?= $editUser['role'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="edit_active" <?= $editUser['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="edit_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php if ($editUser['avatar']): ?>
                        <div class="text-center mb-3">
                            <img src="<?= BASE_URL . '/' . $editUser['avatar'] ?>" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                        </div>
                    <?php endif; ?>
                    <p class="small text-muted"><strong>Created:</strong> <?= formatDate($editUser['created_at']) ?></p>
                    <p class="small text-muted"><strong>Last Login:</strong> <?= $editUser['last_login'] ? timeAgo($editUser['last_login']) : 'Never' ?></p>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update User</button>
                        <a href="<?= BASE_URL ?>/admin/modules/users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                <p class="text-muted">No admin users found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-size:14px;">
                                        <?= strtoupper(substr($item['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= sanitize($item['full_name']) ?></strong>
                                        <br><small class="text-muted"><?= sanitize($item['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><code><?= sanitize($item['username']) ?></code></td>
                            <td>
                                <?php
                                $roleColors = ['super_admin' => 'danger', 'admin' => 'primary', 'editor' => 'info', 'media_team' => 'warning', 'pastor' => 'success', 'secretary' => 'secondary', 'finance' => 'success', 'volunteer' => 'light'];
                                $rColor = $roleColors[$item['role']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $rColor ?>"><?= ucwords(str_replace('_', ' ', $item['role'])) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $item['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><small><?= $item['last_login'] ? timeAgo($item['last_login']) : 'Never' ?></small></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <button type="button" class="btn btn-outline-warning" title="Reset Password" onclick="showResetPasswordModal(<?= $item['id'] ?>, '<?= sanitize(addslashes($item['username'])) ?>')"><i class="fas fa-key"></i></button>
                                    <?php if ($item['id'] != $_SESSION['admin_id']): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this admin user?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <?php endif; ?>
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

            <small class="text-muted">Showing <?= count($users) ?> of <?= $total ?> users</small>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="volunteer">Volunteer</option>
                            <option value="editor">Editor</option>
                            <option value="media_team">Media Team</option>
                            <option value="pastor">Pastor</option>
                            <option value="secretary">Secretary</option>
                            <option value="finance">Finance</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="c_active" checked>
                        <label class="form-check-label" for="c_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="id" id="reset_user_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Reset password for <strong id="reset_user_name"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8">
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-key me-1"></i> Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateUserModal() {
    new bootstrap.Modal(document.getElementById('createUserModal')).show();
}

function showResetPasswordModal(id, name) {
    document.getElementById('reset_user_id').value = id;
    document.getElementById('reset_user_name').textContent = name;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}
</script>
