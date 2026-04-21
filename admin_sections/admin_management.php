<?php
// ADMIN MANAGEMENT SECTION - Salem Dominion Ministries
// Add, delete, and promote admin users
?>

<div class="content-header">
    <h1 class="page-title">Admin Management</h1>
    <p class="page-subtitle">Manage admin users and permissions</p>
</div>

<!-- Add New Admin Form -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-user-plus"></i>
        Add New Admin
    </h2>
    <form method="POST" action="admin_dashboard.php">
        <input type="hidden" name="action" value="add_admin">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="admin_username">Username *</label>
                    <input type="text" id="admin_username" name="username" class="form-control" 
                           placeholder="Enter admin username" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="admin_password">Password *</label>
                    <input type="password" id="admin_password" name="password" class="form-control" 
                           placeholder="Enter admin password" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="admin_name">Full Name *</label>
                    <input type="text" id="admin_name" name="full_name" class="form-control" 
                           placeholder="Enter full name" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="admin_role">Role *</label>
                    <select id="admin_role" name="role" class="form-control" required>
                        <option value="">Select role</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="content_admin">Content Admin</option>
                        <option value="moderator">Moderator</option>
                        <option value="assistant">Assistant Admin</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="admin_email">Email Address</label>
            <input type="email" id="admin_email" name="email" class="form-control" 
                   placeholder="Enter email address (optional)">
        </div>
        
        <div class="form-group">
            <label class="form-label" for="admin_permissions">Permissions</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <label style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <input type="checkbox" name="permissions[]" value="sermons" checked style="margin-right: 0.5rem;">
                    <span>Manage Sermons</span>
                </label>
                <label style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <input type="checkbox" name="permissions[]" value="events" checked style="margin-right: 0.5rem;">
                    <span>Manage Events</span>
                </label>
                <label style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <input type="checkbox" name="permissions[]" value="news" checked style="margin-right: 0.5rem;">
                    <span>Manage News</span>
                </label>
                <label style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <input type="checkbox" name="permissions[]" value="gallery" checked style="margin-right: 0.5rem;">
                    <span>Manage Gallery</span>
                </label>
                <label style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <input type="checkbox" name="permissions[]" value="testimonials" checked style="margin-right: 0.5rem;">
                    <span>Manage Testimonials</span>
                </label>
                <label style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <input type="checkbox" name="permissions[]" value="admin_management" style="margin-right: 0.5rem;">
                    <span>Manage Admins</span>
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn-submit">
            <i class="fas fa-user-plus me-2"></i>
            Add Admin User
        </button>
    </form>
</div>

<!-- Existing Admins -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-users-cog"></i>
        Manage Admin Users
    </h2>
    <div class="data-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // For now, show the main admin (Pastor Faty Musasizi)
                // In a real implementation, this would fetch from a database
                $admins = [
                    [
                        'id' => 1,
                        'name' => 'Pastor Faty Musasizi',
                        'username' => 'MusasiziFaty',
                        'role' => 'super_admin',
                        'permissions' => ['sermons', 'events', 'news', 'gallery', 'testimonials', 'admin_management'],
                        'created_at' => '2026-01-01',
                        'last_login' => date('Y-m-d H:i:s'),
                        'status' => 'active'
                    ]
                ];
                
                if (!empty($admins)):
                    foreach ($admins as $admin):
                ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <div style="width: 40px; height: 40px; background: var(--gradient-divine); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                <i class="fas fa-user-shield" style="color: var(--midnight-blue);"></i>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                                <?php if (!empty($admin['email'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($admin['email']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><code><?php echo htmlspecialchars($admin['username']); ?></code></td>
                    <td>
                        <span class="badge" style="background: <?php 
                            echo match($admin['role']) {
                                'super_admin' => 'var(--heavenly-gold)',
                                'content_admin' => 'var(--ocean-blue)',
                                'moderator' => 'var(--gradient-divine)',
                                'assistant' => 'var(--pearl-white)',
                                default => 'var(--pearl-white)'
                            }; 
                        ?>; color: <?php 
                            echo match($admin['role']) {
                                'super_admin' => 'var(--midnight-blue)',
                                'content_admin' => 'white',
                                'moderator' => 'var(--midnight-blue)',
                                'assistant' => 'var(--midnight-blue)',
                                default => 'var(--midnight-blue)'
                            }; 
                        ?>; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem;">
                            <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                            <?php foreach ($admin['permissions'] as $permission): ?>
                                <span class="badge" style="background: var(--ocean-blue); color: white; font-size: 0.7rem;">
                                    <?php echo ucfirst($permission); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                    <td><?php echo date('M j, H:i', strtotime($admin['last_login'])); ?></td>
                    <td>
                        <span class="badge" style="background: #10b981; color: white;">
                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Active
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn-action btn-edit" onclick="editAdmin(<?php echo $admin['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($admin['id'] != 1): // Can't delete the main admin ?>
                                <button type="button" class="btn-action btn-delete" onclick="deleteAdmin(<?php echo $admin['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php
                    endforeach;
                else:
                ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--ocean-blue);">
                        <i class="fas fa-users-cog" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p style="margin: 1rem 0 0;">No admin users found. Add your first admin!</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Admin Roles & Permissions Guide -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-info-circle"></i>
        Admin Roles & Permissions
    </h2>
    <div class="row">
        <div class="col-md-6">
            <h4 style="color: var(--midnight-blue); margin-bottom: 1rem;">Role Hierarchy</h4>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <strong style="color: var(--heavenly-gold);">Super Admin</strong>
                <p style="margin: 0.5rem 0;">Full access to all features including admin management</p>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <strong style="color: var(--ocean-blue);">Content Admin</strong>
                <p style="margin: 0.5rem 0;">Can manage all content (sermons, events, news, gallery)</p>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px; margin-bottom: 0.5rem;">
                <strong style="color: var(--midnight-blue);">Moderator</strong>
                <p style="margin: 0.5rem 0;">Can manage testimonials and moderate content</p>
            </div>
            <div style="background: var(--pearl-white); padding: 1rem; border-radius: 10px;">
                <strong style="color: var(--midnight-blue);">Assistant Admin</strong>
                <p style="margin: 0.5rem 0;">Limited access to specific features</p>
            </div>
        </div>
        
        <div class="col-md-6">
            <h4 style="color: var(--midnight-blue); margin-bottom: 1rem;">Permission Details</h4>
            <div style="display: grid; gap: 0.5rem;">
                <div style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <i class="fas fa-microphone-alt me-2" style="color: var(--ocean-blue);"></i>
                    <span><strong>Sermons:</strong> Upload, edit, delete sermons</span>
                </div>
                <div style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <i class="fas fa-calendar me-2" style="color: var(--ocean-blue);"></i>
                    <span><strong>Events:</strong> Create, edit, delete events</span>
                </div>
                <div style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <i class="fas fa-newspaper me-2" style="color: var(--ocean-blue);"></i>
                    <span><strong>News:</strong> Publish, edit, delete articles</span>
                </div>
                <div style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <i class="fas fa-images me-2" style="color: var(--ocean-blue);"></i>
                    <span><strong>Gallery:</strong> Upload, manage media content</span>
                </div>
                <div style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <i class="fas fa-comments me-2" style="color: var(--ocean-blue);"></i>
                    <span><strong>Testimonials:</strong> Approve, delete testimonials</span>
                </div>
                <div style="display: flex; align-items: center; padding: 0.5rem; background: var(--pearl-white); border-radius: 8px;">
                    <i class="fas fa-users-cog me-2" style="color: var(--heavenly-gold);"></i>
                    <span><strong>Admin Management:</strong> Add, remove admins</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editAdmin(adminId) {
    // In a real implementation, this would open an edit modal
    alert('Edit admin functionality would be implemented here for admin ID: ' + adminId);
}

function deleteAdmin(adminId) {
    if (confirm('Are you sure you want to delete this admin user? This action cannot be undone.')) {
        // In a real implementation, this would submit a delete request
        alert('Delete admin functionality would be implemented here for admin ID: ' + adminId);
    }
}
</script>
