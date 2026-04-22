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
            case 'approve_testimonial':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("UPDATE testimonials SET status='approved', approved_at=NOW(), approved_by=1 WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Testimonial approved successfully!";
                    }
                }
                break;
                
            case 'reject_testimonial':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("UPDATE testimonials SET status='rejected' WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Testimonial rejected successfully!";
                    }
                }
                break;
                
            case 'delete_testimonial':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Testimonial deleted successfully!";
                    }
                }
                break;
        }
    }
}

// Get testimonials from database
$testimonials = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT * FROM testimonials ORDER BY submitted_at DESC LIMIT 20");
        if ($result) {
            $testimonials = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch testimonials: ' . $e->getMessage();
    }
}
?>

<div class="content-header">
    <h1 class="page-title"><?php echo getAdminLogoImg(30, 30, 'margin-right: 10px'); ?>Testimonial Management</h1>
    <p class="page-subtitle">Review and approve user testimonials for the church website</p>
</div>

<!-- Testimonials Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count($testimonials); ?></div>
        <div class="stat-label">Total Testimonials</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($testimonials, fn($t) => $t['status'] == 'approved')); ?></div>
        <div class="stat-label">Approved</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getAdminLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($testimonials, fn($t) => $t['status'] == 'pending')); ?></div>
        <div class="stat-label">Pending</div>
    </div>
</div>

<!-- Testimonials Table -->
<div class="data-table">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i>
            User Testimonials
        </h3>
        <span class="badge bg-primary"><?php echo count($testimonials); ?> Total</span>
    </div>
    
    <?php if (empty($testimonials)): ?>
        <div class="empty-state">
            <i class="fas fa-comments fa-3x mb-3"></i>
            <h4>No Testimonials Found</h4>
            <p>No user testimonials have been submitted yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Author</th>
                        <th>Testimonial</th>
                        <th>Rating</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($testimonial['email']); ?></small>
                            </td>
                            <td>
                                <div class="testimonial-text">
                                    <?php echo htmlspecialchars(substr($testimonial['message'], 0, 150)); ?>...
                                </div>
                            </td>
                            <td>
                                <?php if ($testimonial['rating']): ?>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                        <small class="text-muted">(<?php echo $testimonial['rating']; ?>/5)</small>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Rating</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($testimonial['created_at'])); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $testimonial['is_approved'] ? 'success' : 'warning'; ?>">
                                    <?php echo $testimonial['is_approved'] ? 'Approved' : 'Pending'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewTestimonial(<?php echo $testimonial['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <?php if (!$testimonial['is_approved']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve_testimonial">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <button type="submit" class="btn-action btn-approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_testimonial">
                                        <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this testimonial?')">
                                            <i class="fas fa-trash"></i> Delete
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

.btn-approve {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    margin-right: 6px;
}

.testimonial-text {
    max-width: 300px;
    line-height: 1.4;
}

.rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.rating i {
    font-size: 0.875rem;
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
.bg-secondary { background: #64748b; }

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

.text-warning { color: #f59e0b; }

.table-responsive {
    overflow-x: auto;
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
    
    .testimonial-text {
        max-width: 200px;
    }
}
</style>

<script>
function viewTestimonial(id) {
    // Implement view testimonial functionality
    alert('View testimonial ID: ' + id);
}
</script>
