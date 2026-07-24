<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->delete('newsletter_subscribers', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'subscribers', $_SESSION['admin_id'], "Deleted subscriber ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Subscriber deleted');
            setFlash('success', 'Subscriber deleted');
            redirect(BASE_URL . '/admin/dashboard.php?section=subscribers');
            break;

        case 'delete_multiple':
            $ids = $_POST['subscriber_ids'] ?? [];
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $db->query("DELETE FROM newsletter_subscribers WHERE id IN ({$placeholders})", $ids);
                logActivity($db, 'deleted', 'subscribers', $_SESSION['admin_id'], "Deleted " . count($ids) . " subscribers");
            }
            setFlash('success', 'Selected subscribers deleted');
            redirect(BASE_URL . '/admin/dashboard.php?section=subscribers');
            break;

        case 'export':
            $search = trim($_POST['search'] ?? '');
            $where = '1=1';
            $params = [];
            if ($search) {
                $where .= " AND (email LIKE ? OR name LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            $allSubscribers = $db->fetchAll("SELECT * FROM newsletter_subscribers WHERE {$where} ORDER BY created_at DESC", $params);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Subscribed Date']);
            foreach ($allSubscribers as $sub) {
                fputcsv($output, [
                    $sub['id'],
                    $sub['name'] ?? '',
                    $sub['email'],
                    $sub['phone'] ?? '',
                    $sub['status'] ?? 'active',
                    $sub['created_at'],
                ]);
            }
            fclose($output);
            logActivity($db, 'exported', 'subscribers', $_SESSION['admin_id'], "Exported " . count($allSubscribers) . " subscribers");
            exit;
            break;
    }
}

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($search) {
    $where .= " AND (email LIKE ? OR name LIKE ? OR phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$pagination = paginate('newsletter_subscribers', $db, $perPage, $page, $where, $params, 'created_at DESC');
$subscribers = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$totalCount = $db->count('newsletter_subscribers');
$activeCount = $db->count('newsletter_subscribers', "status = 'active'");

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Newsletter Subscribers</h4>
    <div>
        <form method="POST" class="d-inline" id="exportForm">
            <input type="hidden" name="action" value="export">
            <input type="hidden" name="search" id="export_search" value="<?= sanitize($search) ?>">
            <button type="submit" class="btn btn-success"><i class="fas fa-download me-1"></i> Export CSV</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-primary mb-0"><?= $totalCount ?></h3>
                <small class="text-muted">Total Subscribers</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success mb-0"><?= $activeCount ?></h3>
                <small class="text-muted">Active Subscribers</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-search me-1"></i> Search</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/subscribers.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($subscribers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">No subscribers found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleAll(this)">
                                </div>
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Subscribed</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $item): ?>
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input subscriber-check" name="ids[]" value="<?= $item['id'] ?>">
                                </div>
                            </td>
                            <td><strong><?= sanitize($item['name'] ?: 'N/A') ?></strong></td>
                            <td><small><?= sanitize($item['email']) ?></small></td>
                            <td><small><?= sanitize($item['phone'] ?? 'N/A') ?></small></td>
                            <td>
                                <span class="badge bg-<?= ($item['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($item['status'] ?? 'active') ?>
                                </span>
                            </td>
                            <td><small><?= timeAgo($item['created_at']) ?></small></td>
                            <td class="text-end">
                                <form method="POST" class="d-inline" onsubmit="return confirm('Remove this subscriber?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteSelected()" id="deleteSelectedBtn" style="display:none">
                        <i class="fas fa-trash me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
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
            </div>

            <small class="text-muted">Showing <?= count($subscribers) ?> of <?= $total ?> subscribers</small>
        <?php endif; ?>
    </div>
</div>

<form method="POST" id="bulkDeleteForm" style="display:none">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="delete_multiple">
    <div id="bulkIds"></div>
</form>

<script>
function toggleAll(el) {
    document.querySelectorAll('.subscriber-check').forEach(cb => { cb.checked = el.checked; });
    updateSelectedCount();
}

document.querySelectorAll('.subscriber-check').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const checked = document.querySelectorAll('.subscriber-check:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('deleteSelectedBtn').style.display = checked > 0 ? 'inline-block' : 'none';
}

function deleteSelected() {
    const ids = [];
    document.querySelectorAll('.subscriber-check:checked').forEach(cb => { ids.push(cb.value); });
    if (ids.length === 0) return;
    if (!confirm('Delete ' + ids.length + ' subscriber(s)?')) return;

    let html = '';
    ids.forEach(id => { html += '<input type="hidden" name="subscriber_ids[]" value="' + id + '">'; });
    document.getElementById('bulkIds').innerHTML = html;
    document.getElementById('bulkDeleteForm').submit();
}
</script>
