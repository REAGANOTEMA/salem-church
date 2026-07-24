<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'mark_read':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('contact_messages', [
                    'is_read' => 1,
                    'read_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);
                logActivity($db, 'updated', 'contact_messages', $_SESSION['admin_id'], "Marked message ID {$id} as read");
            }
            if ($isAjax) jsonSuccess([], 'Message marked as read');
            setFlash('success', 'Message marked as read');
            redirect(BASE_URL . '/admin/dashboard.php?section=contact-messages');
            break;

        case 'mark_unread':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->update('contact_messages', [
                    'is_read' => 0,
                    'read_at' => null,
                ], 'id = ?', [$id]);
            }
            if ($isAjax) jsonSuccess([], 'Message marked as unread');
            setFlash('success', 'Message marked as unread');
            redirect(BASE_URL . '/admin/dashboard.php?section=contact-messages');
            break;

        case 'reply':
            $id = (int)($_POST['id'] ?? 0);
            $reply_message = trim($_POST['reply_message'] ?? '');

            if (empty($id) || empty($reply_message)) {
                if ($isAjax) jsonError('Reply message is required');
                setFlash('error', 'Reply message is required');
                redirect(BASE_URL . '/admin/dashboard.php?section=contact-messages');
            }

            $msg = $db->fetch("SELECT * FROM contact_messages WHERE id = ?", [$id]);
            if ($msg) {
                $admin = currentAdmin();
                $db->update('contact_messages', [
                    'reply_message' => $reply_message,
                    'replied_by' => $admin['id'] ?? 0,
                    'replied_at' => date('Y-m-d H:i:s'),
                    'is_read' => 1,
                    'read_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$id]);

                if (!empty($msg['email']) && validateEmail($msg['email'])) {
                    $subject = 'Re: ' . ($msg['subject'] ?? 'Your message to ' . CHURCH_NAME);
                    $body = "Dear {$msg['name']},\n\n";
                    $body .= $reply_message . "\n\n";
                    $body .= "---\n";
                    $body .= CHURCH_NAME . "\n";
                    $body .= CHURCH_EMAIL . "\n";
                    @mail($msg['email'], $subject, $body);
                }

                logActivity($db, 'replied', 'contact_messages', $_SESSION['admin_id'], "Replied to message ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Reply sent successfully');
            setFlash('success', 'Reply sent successfully');
            redirect(BASE_URL . '/admin/dashboard.php?section=contact-messages');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $db->delete('contact_messages', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'contact_messages', $_SESSION['admin_id'], "Deleted message ID {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Message deleted');
            setFlash('success', 'Message deleted');
            redirect(BASE_URL . '/admin/dashboard.php?section=contact-messages');
            break;
    }
}

$filterRead = $_GET['read'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($filterRead === 'read') {
    $where .= " AND is_read = 1";
} elseif ($filterRead === 'unread') {
    $where .= " AND is_read = 0";
}

$pagination = paginate('contact_messages', $db, $perPage, $page, $where, $params);
$messages = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$unreadCount = $db->count('contact_messages', "is_read = 0");

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Messages</h4>
    <span class="badge bg-danger fs-6"><?= $unreadCount ?> unread</span>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="btn-group">
            <a href="?read=" class="btn btn-outline-primary <?= $filterRead === '' ? 'active' : '' ?>">All (<?= $db->count('contact_messages') ?>)</a>
            <a href="?read=unread" class="btn btn-outline-primary <?= $filterRead === 'unread' ? 'active' : '' ?>">Unread (<?= $unreadCount ?>)</a>
            <a href="?read=read" class="btn btn-outline-primary <?= $filterRead === 'read' ? 'active' : '' ?>">Read</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($messages)): ?>
            <div class="text-center py-5">
                <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                <p class="text-muted">No messages found.</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($messages as $item): ?>
                <div class="list-group-item list-group-item-action <?= !$item['is_read'] ? 'border-start border-primary border-3' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 <?= !$item['is_read'] ? 'fw-bold' : '' ?>">
                                    <?= sanitize($item['name'] ?: 'Unknown') ?>
                                    <?php if (!$item['is_read']): ?>
                                        <span class="badge bg-primary ms-2">New</span>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted"><?= timeAgo($item['created_at']) ?></small>
                            </div>
                            <p class="mb-1 text-muted small">
                                <i class="fas fa-envelope me-1"></i><?= sanitize($item['email'] ?: 'No email') ?>
                                <?php if (!empty($item['phone'])): ?>
                                    <span class="ms-2"><i class="fas fa-phone me-1"></i><?= sanitize($item['phone']) ?></span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($item['subject'])): ?>
                                <p class="mb-1"><strong>Subject:</strong> <?= sanitize($item['subject']) ?></p>
                            <?php endif; ?>
                            <p class="mb-2"><?= sanitize(truncate($item['message'] ?? $item['body'] ?? '', 150)) ?></p>

                            <?php if (!empty($item['reply_message'])): ?>
                                <div class="bg-success bg-opacity-10 p-2 rounded mb-2">
                                    <small><strong><i class="fas fa-reply me-1"></i>Your Reply:</strong> <?= sanitize(truncate($item['reply_message'], 100)) ?></small>
                                    <?php if (!empty($item['replied_at'])): ?>
                                        <br><small class="text-muted">Sent <?= timeAgo($item['replied_at']) ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="btn-group btn-group-sm mt-1">
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="<?= $item['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-<?= $item['is_read'] ? 'secondary' : 'primary' ?>">
                                        <i class="fas fa-<?= $item['is_read'] ? 'envelope' : 'envelope-open' ?> me-1"></i>
                                        <?= $item['is_read'] ? 'Mark Unread' : 'Mark Read' ?>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="showReplyModal(<?= $item['id'] ?>, '<?= sanitize(addslashes($item['email'] ?? '')) ?>', '<?= sanitize(addslashes($item['name'] ?? '')) ?>')">
                                    <i class="fas fa-reply me-1"></i> Reply
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this message permanently?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&read=<?= urlencode($filterRead) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&read=<?= urlencode($filterRead) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&read=<?= urlencode($filterRead) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($messages) ?> of <?= $total ?> messages</small>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="replyForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="id" id="reply_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-reply me-2"></i>Reply to <span id="reply_to_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small"><i class="fas fa-envelope me-1"></i> <span id="reply_to_email"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Your Reply *</label>
                        <textarea name="reply_message" class="form-control" rows="6" required placeholder="Type your reply here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showReplyModal(id, email, name) {
    document.getElementById('reply_id').value = id;
    document.getElementById('reply_to_email').textContent = email || 'No email';
    document.getElementById('reply_to_name').textContent = name || 'User';
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}
</script>
