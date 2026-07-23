<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'confirm':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('donations', [
                    'status' => 'confirmed',
                    'confirmed_by' => $_SESSION['admin_id'],
                    'confirmed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'confirmed', 'donations', $_SESSION['admin_id'], "Confirmed donation ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Donation confirmed');
            setFlash('success', 'Donation confirmed');
            redirect(BASE_URL . '/admin/modules/donations.php');
            break;

        case 'reject':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('donations', [
                    'status' => 'rejected',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'rejected', 'donations', $_SESSION['admin_id'], "Rejected donation ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Donation rejected');
            setFlash('success', 'Donation rejected');
            redirect(BASE_URL . '/admin/modules/donations.php');
            break;
    }
}

$filterStatus = $_GET['status'] ?? '';
$filterType = $_GET['type'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($filterStatus && in_array($filterStatus, ['pending', 'confirmed', 'rejected', 'completed'])) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}
if ($filterType) {
    $where .= " AND donation_type = ?";
    $params[] = $filterType;
}

$pagination = paginate('donations', $db, $perPage, $page, $where, $params);
$donations = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$stats = $db->fetch("SELECT COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_amount, COALESCE(SUM(CASE WHEN status = 'confirmed' OR status = 'completed' THEN amount ELSE 0 END), 0) as confirmed_amount, COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount FROM donations") ?: ['total_count' => 0, 'total_amount' => 0, 'confirmed_amount' => 0, 'pending_amount' => 0];

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-donate me-2"></i>Donations Management</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-0">Total Donations</h6>
                        <h3 class="mb-0"><?= $stats['total_count'] ?></h3>
                    </div>
                    <i class="fas fa-hand-holding-heart fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-0">Confirmed</h6>
                        <h3 class="mb-0"><?= number_format($stats['confirmed_amount'], 0) ?></h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-0">Pending</h6>
                        <h3 class="mb-0"><?= number_format($stats['pending_amount'], 0) ?></h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="mb-0">Total Value</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_amount'], 0) ?></h3>
                    </div>
                    <i class="fas fa-coins fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="tithe" <?= $filterType === 'tithe' ? 'selected' : '' ?>>Tithe</option>
                    <option value="offering" <?= $filterType === 'offering' ? 'selected' : '' ?>>Offering</option>
                    <option value="general" <?= $filterType === 'general' ? 'selected' : '' ?>>General</option>
                    <option value="building_fund" <?= $filterType === 'building_fund' ? 'selected' : '' ?>>Building Fund</option>
                    <option value="missions" <?= $filterType === 'missions' ? 'selected' : '' ?>>Missions</option>
                    <option value="children_ministry" <?= $filterType === 'children_ministry' ? 'selected' : '' ?>>Children Ministry</option>
                    <option value="special" <?= $filterType === 'special' ? 'selected' : '' ?>>Special</option>
                    <option value="benevolence" <?= $filterType === 'benevolence' ? 'selected' : '' ?>>Benevolence</option>
                    <option value="other" <?= $filterType === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/donations.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($donations)): ?>
            <div class="text-center py-5">
                <i class="fas fa-donate fa-3x text-muted mb-3"></i>
                <p class="text-muted">No donations found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Donor</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $item): ?>
                        <tr>
                            <td><small><?= $item['id'] ?></small></td>
                            <td>
                                <strong><?= sanitize($item['donor_name'] ?: $item['name'] ?: 'Anonymous') ?></strong>
                                <?php if (!empty($item['donor_email'] ?: $item['email'])): ?>
                                    <br><small class="text-muted"><?= sanitize($item['donor_email'] ?? $item['email'] ?? '') ?></small>
                                <?php endif; ?>
                            </td>
                            <td><strong class="text-success"><?= number_format($item['amount'], 2) ?></strong></td>
                            <td><span class="badge bg-info"><?= sanitize(ucfirst(str_replace('_', ' ', $item['donation_type'] ?? $item['type'] ?? 'other'))) ?></span></td>
                            <td><small><?= sanitize(ucfirst($item['payment_method'] ?? $item['method'] ?? 'N/A')) ?></small></td>
                            <td>
                                <?php
                                $statusMap = ['pending' => 'warning', 'confirmed' => 'success', 'completed' => 'success', 'rejected' => 'danger'];
                                $cls = $statusMap[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= ucfirst($item['status']) ?></span>
                            </td>
                            <td><small><?= formatDate($item['created_at']) ?></small></td>
                            <td class="text-end">
                                <?php if ($item['status'] === 'pending'): ?>
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="confirm">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success" title="Confirm"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Reject this donation?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($filterStatus) ?>&type=<?= urlencode($filterType) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filterStatus) ?>&type=<?= urlencode($filterType) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($filterStatus) ?>&type=<?= urlencode($filterType) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($donations) ?> of <?= $total ?> donations</small>
        <?php endif; ?>
    </div>
</div>
