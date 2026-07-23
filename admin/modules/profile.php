<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getNamed('admin');
$admin = currentAdmin();
$adminData = $db->fetch("SELECT * FROM admin_users WHERE id = ?", [$admin['id']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'update_profile':
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (empty($full_name) || empty($email)) {
                setFlash('error', 'Name and email are required');
                redirect(BASE_URL . '/admin/modules/profile.php');
            }

            if (!validateEmail($email)) {
                setFlash('error', 'Invalid email address');
                redirect(BASE_URL . '/admin/modules/profile.php');
            }

            $existing = $db->fetch("SELECT id FROM admin_users WHERE email = ? AND id != ?", [$email, $admin['id']]);
            if ($existing) {
                setFlash('error', 'Email already in use');
                redirect(BASE_URL . '/admin/modules/profile.php');
            }

            $updateData = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['avatar']['name'])) {
                $uploaded = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES);
                if ($uploaded) {
                    if ($adminData && !empty($adminData['avatar'])) deleteFile($adminData['avatar']);
                    $updateData['avatar'] = $uploaded;
                }
            }

            $db->update('admin_users', $updateData, 'id = ?', [$admin['id']]);

            $_SESSION['admin_name'] = $full_name;
            $_SESSION['admin_email'] = $email;

            logActivity($db, 'updated', 'profile', $admin['id'], 'Updated profile');
            setFlash('success', 'Profile updated successfully');
            redirect(BASE_URL . '/admin/modules/profile.php');
            break;

        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password) || empty($new_password)) {
                setFlash('error', 'All password fields are required');
                redirect(BASE_URL . '/admin/modules/profile.php');
            }

            if ($new_password !== $confirm_password) {
                setFlash('error', 'New passwords do not match');
                redirect(BASE_URL . '/admin/modules/profile.php');
            }

            if (strlen($new_password) < 8) {
                setFlash('error', 'Password must be at least 8 characters');
                redirect(BASE_URL . '/admin/modules/profile.php');
            }

            $result = auth()->changePassword($admin['id'], $current_password, $new_password);
            if ($result['success']) {
                logActivity($db, 'updated', 'profile', $admin['id'], 'Changed password');
                setFlash('success', $result['message']);
            } else {
                setFlash('error', $result['message']);
            }
            redirect(BASE_URL . '/admin/modules/profile.php');
            break;
    }
}

$loginLogs = $db->fetchAll("SELECT * FROM admin_login_logs WHERE admin_id = ? ORDER BY login_time DESC LIMIT 20", [$admin['id']]);

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Profile Information</h5></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required value="<?= sanitize($adminData['full_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?= sanitize($adminData['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= sanitize($adminData['phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= sanitize($adminData['username'] ?? '') ?>" disabled>
                                <div class="form-text">Username cannot be changed</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?= ucwords(str_replace('_', ' ', $adminData['role'] ?? '')) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <?php if (!empty($adminData['avatar'])): ?>
                                <img src="<?= BASE_URL . '/' . $adminData['avatar'] ?>" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width:120px;height:120px;font-size:48px;">
                                    <?= strtoupper(substr($adminData['full_name'] ?? 'A', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Change Avatar</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h5></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="8">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-key me-1"></i> Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Info</h5></div>
            <div class="card-body">
                <p class="mb-2"><strong>Member since:</strong> <?= $adminData['created_at'] ? formatDate($adminData['created_at']) : 'N/A' ?></p>
                <p class="mb-2"><strong>Last login:</strong> <?= $adminData['last_login'] ? timeAgo($adminData['last_login']) : 'Never' ?></p>
                <p class="mb-0"><strong>Login attempts:</strong> <?= $adminData['login_attempts'] ?? 0 ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Login History</h5></div>
            <div class="card-body p-0">
                <?php if (empty($loginLogs)): ?>
                    <p class="text-muted p-3 mb-0">No login history yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($loginLogs as $log): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?= $log['status'] === 'success' ? 'success' : 'danger' ?>"><?= $log['status'] ?></span>
                                    <small class="text-muted ms-2"><?= $log['ip_address'] ? 'from ' . $log['ip_address'] : '' ?></small>
                                </div>
                                <small class="text-muted"><?= timeAgo($log['login_time']) ?></small>
                            </div>
                            <?php if (!empty($log['failure_reason'])): ?>
                                <small class="text-danger"><?= sanitize($log['failure_reason']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
