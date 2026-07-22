<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();

$filterAction = $_GET['action_type'] ?? '';
$filterModule = $_GET['module'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($filterAction) {
    $where .= " AND action = ?";
    $params[] = $filterAction;
}
if ($filterModule) {
    $where .= " AND module = ?";
    $params[] = $filterModule;
}
if ($dateFrom) {
    $where .= " AND DATE(created_at) >= ?";
    $params[] = $dateFrom;
}
if ($dateTo) {
    $where .= " AND DATE(created_at) <= ?";
    $params[] = $dateTo;
}

$pagination = paginate('activity_logs', $db, $perPage, $page, $where, $params);
$logs = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$actions = $db->fetchAll("SELECT DISTINCT action FROM activity_logs ORDER BY action");
$modules = $db->fetchAll("SELECT DISTINCT module FROM activity_logs WHERE module != '' ORDER BY module");

$actionColors = [
    'created' => 'success',
    'updated' => 'primary',
    'deleted' => 'danger',
    'approved' => 'success',
    'rejected' => 'danger',
    'archived' => 'secondary',
    'login' => 'info',
    'logout' => 'secondary',
    'confirmed' => 'success',
    'replied' => 'info',
    'exported' => 'warning',
    'uploaded' => 'primary',
];

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Activity Logs</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Action</label>
                <select name="action_type" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?= sanitize($a['action']) ?>" <?= $filterAction === $a['action'] ? 'selected' : '' ?>><?= ucfirst($a['action']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Module</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="">All Modules</option>
                    <?php foreach ($modules as $m): ?>
                    <option value="<?= sanitize($m['module']) ?>" <?= $filterModule === $m['module'] ? 'selected' : '' ?>><?= ucfirst($m['module']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $dateFrom ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= $dateTo ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/activity-logs.php" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <p class="text-muted">No activity logs found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Action</th>
                            <th>Module</th>
                            <th>User ID</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $item): ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= $actionColors[$item['action']] ?? 'secondary' ?>">
                                    <i class="fas fa-<?= $item['action'] === 'created' ? 'plus' : ($item['action'] === 'deleted' ? 'trash' : ($item['action'] === 'updated' ? 'edit' : 'circle')) ?> me-1"></i>
                                    <?= ucfirst($item['action']) ?>
                                </span>
                            </td>
                            <td><span class="text-muted"><?= sanitize(ucfirst($item['module'])) ?></span></td>
                            <td><small><?= $item['user_id'] ?></small></td>
                            <td><small class="text-muted"><?= sanitize(truncate($item['details'] ?? '', 60)) ?></small></td>
                            <td><small class="text-muted"><?= sanitize($item['ip_address']) ?></small></td>
                            <td><small><?= timeAgo($item['created_at']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&action_type=<?= urlencode($filterAction) ?>&module=<?= urlencode($filterModule) ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&action_type=<?= urlencode($filterAction) ?>&module=<?= urlencode($filterModule) ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&action_type=<?= urlencode($filterAction) ?>&module=<?= urlencode($filterModule) ?>&date_from=<?= $dateFrom ?>&date_to=<?= $dateTo ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($logs) ?> of <?= $total ?> activity logs</small>
        <?php endif; ?>
    </div>
</div>
