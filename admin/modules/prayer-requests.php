<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'mark_answered':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('prayer_requests', [
                    'status' => 'answered',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'updated', 'prayer_requests', $_SESSION['admin_id'], "Marked prayer request ID {$id} as answered");
            }
            if ($isAjax) jsonSuccess([], 'Prayer request marked as answered');
            setFlash('success', 'Prayer request marked as answered');
            redirect(BASE_URL . '/admin/modules/prayer-requests.php');
            break;

        case 'archive':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('prayer_requests', [
                    'status' => 'archived',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'archived', 'prayer_requests', $_SESSION['admin_id'], "Archived prayer request ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Prayer request archived');
            setFlash('success', 'Prayer request archived');
            redirect(BASE_URL . '/admin/modules/prayer-requests.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->delete('prayer_requests', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'prayer_requests', $_SESSION['admin_id'], "Deleted prayer request ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Prayer request deleted');
            setFlash('success', 'Prayer request deleted');
            redirect(BASE_URL . '/admin/modules/prayer-requests.php');
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
    $where .= " AND (name LIKE ? OR email LIKE ? OR request LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
if ($filterStatus && in_array($filterStatus, ['pending', 'praying', 'answered', 'archived'])) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagination = paginate('prayer_requests', $db, $perPage, $page, $where, $params);
$requests = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$counts = [
    'all' => $db->count('prayer_requests'),
    'pending' => $db->count('prayer_requests', "status = 'pending'"),
    'praying' => $db->count('prayer_requests', "status = 'praying'"),
    'answered' => $db->count('prayer_requests', "status = 'answered'"),
    'archived' => $db->count('prayer_requests', "status = 'archived'"),
];

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-praying-hands me-2"></i>Prayer Requests</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm text-center <?= !$filterStatus ? 'border-primary' : '' ?>">
            <a href="?status=" class="text-decoration-none p-3">
                <h4 class="mb-0 text-primary"><?= $counts['all'] ?></h4>
                <small class="text-muted">All</small>
            </a>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm text-center <?= $filterStatus === 'pending' ? 'border-warning' : '' ?>">
            <a href="?status=pending" class="text-decoration-none p-3">
                <h4 class="mb-0 text-warning"><?= $counts['pending'] ?></h4>
                <small class="text-muted">Pending</small>
            </a>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm text-center <?= $filterStatus === 'praying' ? 'border-info' : '' ?>">
            <a href="?status=praying" class="text-decoration-none p-3">
                <h4 class="mb-0 text-info"><?= $counts['praying'] ?></h4>
                <small class="text-muted">Praying</small>
            </a>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm text-center <?= $filterStatus === 'answered' ? 'border-success' : '' ?>">
            <a href="?status=answered" class="text-decoration-none p-3">
                <h4 class="mb-0 text-success"><?= $counts['answered'] ?></h4>
                <small class="text-muted">Answered</small>
            </a>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm text-center <?= $filterStatus === 'archived' ? 'border-secondary' : '' ?>">
            <a href="?status=archived" class="text-decoration-none p-3">
                <h4 class="mb-0 text-secondary"><?= $counts['archived'] ?></h4>
                <small class="text-muted">Archived</small>
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or request..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i> Search</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/prayer-requests.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5">
                <i class="fas fa-praying-hands fa-3x text-muted mb-3"></i>
                <p class="text-muted">No prayer requests found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Request</th>
                            <th>Status</th>
                            <th>Private</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $item): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($item['name'] ?: 'Anonymous') ?></strong>
                                <?php if ($item['email']): ?>
                                    <br><small class="text-muted"><?= sanitize($item['email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width:300px;" title="<?= sanitize($item['request'] ?? $item['prayer_request'] ?? '') ?>">
                                    <?= sanitize(truncate($item['request'] ?? $item['prayer_request'] ?? '', 80)) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusMap = ['pending' => 'warning', 'praying' => 'info', 'answered' => 'success', 'archived' => 'secondary'];
                                $cls = $statusMap[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= ucfirst($item['status']) ?></span>
                            </td>
                            <td><?= ($item['is_anonymous'] ?? ($item['is_private'] ?? 0)) ? '<i class="fas fa-lock text-muted"></i>' : '<i class="fas fa-globe text-muted"></i>' ?></td>
                            <td><small><?= timeAgo($item['created_at']) ?></small></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="View Details" onclick="viewPrayerRequest(<?= $item['id'] ?>, '<?= sanitize(addslashes($item['name'] ?: 'Anonymous')) ?>', '<?= sanitize(addslashes($item['email'] ?? '')) ?>', '<?= sanitize(addslashes($item['request'] ?? $item['prayer_request'] ?? '')) ?>', '<?= $item['status'] ?>', '<?= $item['is_anonymous'] ?? ($item['is_private'] ?? 0) ?>')"><i class="fas fa-eye"></i></button>
                                    <?php if ($item['status'] !== 'answered'): ?>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="mark_answered">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success" title="Mark Answered"><i class="fas fa-check"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($item['status'] !== 'archived'): ?>
                                    <form method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="archive">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-secondary" title="Archive"><i class="fas fa-archive"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this prayer request?')">
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filterStatus) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filterStatus) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filterStatus) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($requests) ?> of <?= $total ?> prayer requests</small>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="prayerRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-praying-hands me-2"></i>Prayer Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <p id="prName"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <p id="prEmail"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Request</label>
                    <p id="prRequest" class="bg-light p-3 rounded"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <p id="prStatus"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewPrayerRequest(id, name, email, request, status, isPrivate) {
    document.getElementById('prName').textContent = name + (isPrivate == 1 ? ' (Private)' : '');
    document.getElementById('prEmail').textContent = email || 'Not provided';
    document.getElementById('prRequest').textContent = request;
    document.getElementById('prStatus').innerHTML = '<span class="badge bg-' + ({pending:'warning',praying:'info',answered:'success',archived:'secondary'}[status] || 'secondary') + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
    new bootstrap.Modal(document.getElementById('prayerRequestModal')).show();
}
</script>
