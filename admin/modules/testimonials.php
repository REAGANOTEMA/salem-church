<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'approve':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('testimonials', [
                    'status' => 'approved',
                    'approved_by' => $_SESSION['admin_id'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'approved', 'testimonials', $_SESSION['admin_id'], "Approved testimonial ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Testimonial approved');
            setFlash('success', 'Testimonial approved');
            redirect(BASE_URL . '/admin/modules/testimonials.php');
            break;

        case 'reject':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('testimonials', [
                    'status' => 'rejected',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'rejected', 'testimonials', $_SESSION['admin_id'], "Rejected testimonial ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Testimonial rejected');
            setFlash('success', 'Testimonial rejected');
            redirect(BASE_URL . '/admin/modules/testimonials.php');
            break;

        case 'archive':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('testimonials', [
                    'status' => 'archived',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'archived', 'testimonials', $_SESSION['admin_id'], "Archived testimonial ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Testimonial archived');
            setFlash('success', 'Testimonial archived');
            redirect(BASE_URL . '/admin/modules/testimonials.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->delete('testimonials', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'testimonials', $_SESSION['admin_id'], "Deleted testimonial ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Testimonial deleted');
            setFlash('success', 'Testimonial deleted');
            redirect(BASE_URL . '/admin/modules/testimonials.php');
            break;
    }
}

$filterStatus = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($filterStatus && in_array($filterStatus, ['pending', 'approved', 'rejected', 'archived'])) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagination = paginate('testimonials', $db, $perPage, $page, $where, $params);
$testimonials = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-quote-right me-2"></i>Testimonials</h4>
</div>

<div class="btn-group mb-4">
    <a href="?status=" class="btn btn-outline-primary <?= !$filterStatus ? 'active' : '' ?>">All</a>
    <a href="?status=pending" class="btn btn-outline-warning <?= $filterStatus === 'pending' ? 'active' : '' ?>">Pending</a>
    <a href="?status=approved" class="btn btn-outline-success <?= $filterStatus === 'approved' ? 'active' : '' ?>">Approved</a>
    <a href="?status=rejected" class="btn btn-outline-danger <?= $filterStatus === 'rejected' ? 'active' : '' ?>">Rejected</a>
    <a href="?status=archived" class="btn btn-outline-secondary <?= $filterStatus === 'archived' ? 'active' : '' ?>">Archived</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($testimonials)): ?>
            <div class="text-center py-5">
                <i class="fas fa-quote-right fa-3x text-muted mb-3"></i>
                <p class="text-muted">No testimonials found.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($testimonials as $item): ?>
                <div class="col-md-6">
                    <div class="card h-100 <?= $item['status'] === 'pending' ? 'border-warning' : ($item['status'] === 'approved' ? 'border-success' : '') ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?= sanitize($item['name'] ?: 'Anonymous') ?></strong>
                                    <?php if (!empty($item['church_role'] ?? $item['role'] ?? '')): ?>
                                        <br><small class="text-muted"><?= sanitize($item['church_role'] ?? $item['role'] ?? '') ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-<?= ['pending'=>'warning','approved'=>'success','rejected'=>'danger','archived'=>'secondary'][$item['status']] ?? 'secondary' ?>">
                                    <?= ucfirst($item['status']) ?>
                                </span>
                            </div>
                            <p class="mt-2 mb-0 fst-italic">"<?= sanitize(truncate($item['content'] ?? $item['testimonial'] ?? '', 200)) ?>"</p>
                            <?php if (!empty($item['rating'])): ?>
                                <div class="mt-2">
                                    <?php for ($r = 1; $r <= 5; $r++): ?>
                                        <i class="fas fa-star <?= $r <= $item['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2"><?= timeAgo($item['created_at']) ?></small>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group btn-group-sm">
                                <?php if ($item['status'] !== 'approved'): ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-success" title="Approve"><i class="fas fa-check me-1"></i>Approve</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($item['status'] !== 'rejected'): ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="fas fa-times me-1"></i>Reject</button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-secondary" title="Archive"><i class="fas fa-archive"></i></button>
                                </form>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this testimonial?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
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
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($filterStatus) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filterStatus) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($filterStatus) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($testimonials) ?> of <?= $total ?> testimonials</small>
        <?php endif; ?>
    </div>
</div>
