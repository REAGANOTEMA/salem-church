<?php
// Get database connection - ensure it's properly established
$conn = $GLOBALS['admin_db_connection'] ?? null;
if (!$conn) {
    // Try to create a new connection if global is not available
    require_once '../db_connection.php';
    $conn = createDatabaseConnection();
}

// Get admin logo configuration
require_once 'logo_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("UPDATE messages SET status='read', read_at=NOW() WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Message marked as read!";
                    }
                }
                break;
                
            case 'mark_unread':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("UPDATE messages SET status='unread', read_at=NULL WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Message marked as unread!";
                    }
                }
                break;
                
            case 'delete_message':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM messages WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Message deleted successfully!";
                    }
                }
                break;
                
            case 'send_message':
                $recipient_email = $_POST['recipient_email'] ?? '';
                $subject = $_POST['subject'] ?? '';
                $message_content = $_POST['message'] ?? '';
                
                if (!empty($recipient_email) && !empty($subject) && !empty($message_content)) {
                    $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'sent', NOW())");
                    if ($stmt) {
                        $admin_id = 1; // Assuming admin has ID 1
                        $stmt->bind_param("isss", $admin_id, $recipient_email, $subject, $message_content);
                        $stmt->execute();
                        $success = "Message sent successfully!";
                    }
                }
                break;
        }
    }
}

// Get messages from database
$messages = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name, u.email as sender_email 
                                 FROM messages m 
                                 LEFT JOIN users u ON m.sender_id = u.id 
                                 ORDER BY m.created_at DESC LIMIT 20");
        if ($result) {
            $messages = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch messages: ' . $e->getMessage();
    }
}
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="content-header">
    <h1 class="page-title"><?php echo getAdminLogoImg(30, 30, 'margin-right: 10px'); ?>Message Management</h1>
    <p class="page-subtitle">Manage and respond to user messages and inquiries</p>
</div>

<!-- Messages Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count($messages); ?></div>
        <div class="stat-label">Total Messages</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'read')); ?></div>
        <div class="stat-label">Read</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'unread')); ?></div>
        <div class="stat-label">Unread</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($messages, fn($m) => $m['status'] === 'replied')); ?></div>
        <div class="stat-label">Replied</div>
    </div>
</div>

<!-- Messages Table -->
<div class="data-table">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i>
            User Messages
        </h3>
        <span class="badge bg-primary"><?php echo count($messages); ?> Total</span>
    </div>
    
    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <i class="fas fa-envelope fa-3x mb-3"></i>
            <h4>No Messages Found</h4>
            <p>No user messages have been received yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($message['sender_name'] ?: 'Unknown'); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($message['sender_email']); ?></small>
                            </td>
                            <td>
                                <div class="message-subject">
                                    <?php echo htmlspecialchars($message['subject']); ?>
                                </div>
                                <div class="message-preview">
                                    <?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>...
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($message['created_at'])); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($message['status']) {
                                        'read' => 'success',
                                        'unread' => 'danger',
                                        'replied' => 'info',
                                        default => 'warning'
                                    }; 
                                ?>">
                                    <?php echo ucfirst($message['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewMessage(<?php echo $message['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-reply" onclick="replyToMessage(<?php echo $message['id']; ?>)">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_message_status">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <input type="hidden" name="status" value="read">
                                        <button type="submit" class="btn-action btn-mark-read">
                                            <i class="fas fa-check"></i> Mark Read
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Reply to Message</h3>
            <button class="modal-close" onclick="closeReplyModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="replyForm" method="POST">
                <input type="hidden" name="action" value="reply_message">
                <input type="hidden" name="message_id" id="replyMessageId">
                
                <div class="form-group">
                    <label for="reply" class="form-label">Your Reply *</label>
                    <textarea id="reply" name="reply" class="form-control" rows="6" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeReplyModal()">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    border: 1px solid #e5e7eb;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: #fbbf24;
}

.stat-label {
    color: #0ea5e9;
    font-weight: 500;
}

.data-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table {
    margin: 0;
}

.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #0f172a;
    border: none;
    padding: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.9rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-color: #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
}

.btn-view {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #0f172a;
}

.btn-reply {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
}

.btn-mark-read {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    margin-right: 6px;
}

.message-subject {
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.message-preview {
    color: #64748b;
    font-size: 0.875rem;
    line-height: 1.4;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state i {
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.bg-primary { background: #0ea5e9; }
.bg-info { background: #3b82f6; }
.bg-success { background: #10b981; }
.bg-warning { background: #f59e0b; }
.bg-danger { background: #ef4444; }

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

.table-responsive {
    overflow-x: auto;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #0f172a;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
}

.modal-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #0f172a;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1rem;
}

.btn-cancel {
    background: #64748b;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    background: #475569;
}

.btn-submit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
    
    .modal-content {
        width: 95%;
        margin: 5% auto;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-cancel, .btn-submit {
        width: 100%;
    }
}
</style>

<script>
function viewMessage(id) {
    // Implement view message functionality
    alert('View message ID: ' + id);
}

function replyToMessage(id) {
    document.getElementById('replyMessageId').value = id;
    document.getElementById('replyModal').style.display = 'block';
}

function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
    document.getElementById('replyForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('replyModal');
    if (event.target == modal) {
        closeReplyModal();
    }
}
</script>
