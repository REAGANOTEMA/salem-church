<?php
// Get global database connection
$conn = $GLOBALS['admin_db_connection'] ?? null;

// Get users from database
$users = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 20");
        if ($result) {
            $users = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch users: ' . $e->getMessage();
    }
}
?>

<div class="content-header">
    <h1 class="page-title"><?php echo getLogoImg(30, 30, 'margin-right: 10px'); ?>Users Management</h1>
    <p class="page-subtitle">Manage user accounts and permissions</p>
</div>

<!-- Users Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count($users); ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['is_active'] == 1)); ?></div>
        <div class="stat-label">Active Users</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['is_active'] == 0)); ?></div>
        <div class="stat-label">Inactive Users</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <?php echo getLogoImg(40, 40); ?>
        </div>
        <div class="stat-number"><?php 
            echo count(array_filter($users, fn($u) => 
                strtotime($u['created_at']) > strtotime('-30 days')
            )); 
        ?></div>
        <div class="stat-label">New This Month</div>
    </div>
</div>

<!-- Users Table -->
<div class="data-table">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i>
            Registered Users
        </h3>
        <span class="badge bg-primary"><?php echo count($users); ?> Total</span>
    </div>
    
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <i class="fas fa-users fa-3x mb-3"></i>
            <h4>No Users Found</h4>
            <p>No users have registered on the platform yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    <?php if (!empty($user['church_role'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($user['church_role']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-email">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="user-phone">
                                    <?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'warning'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if ($user['is_active']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="deactivate_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-action btn-deactivate" onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                <i class="fas fa-user-slash"></i> Deactivate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="activate_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-action btn-activate">
                                                <i class="fas fa-user-check"></i> Activate
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
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

.btn-edit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-deactivate {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-activate {
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

.user-info {
    display: flex;
    flex-direction: column;
}

.user-email {
    color: #64748b;
    font-size: 0.875rem;
}

.user-phone {
    color: #64748b;
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

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
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
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function viewUser(id) {
    // Implement view user functionality
    alert('View user ID: ' + id);
}

function editUser(id) {
    // Implement edit user functionality
    alert('Edit user ID: ' + id);
}
</script>
