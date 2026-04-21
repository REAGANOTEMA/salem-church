<?php
// Get global database connection
$conn = $GLOBALS['admin_db_connection'] ?? null;

// Get admin logo configuration
require_once 'logo_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_sermon':
                $title = $_POST['title'] ?? '';
                $category = $_POST['category'] ?? '';
                $description = $_POST['description'] ?? '';
                $content = $_POST['content'] ?? '';
                $video_url = $_POST['video_url'] ?? '';
                $audio_url = $_POST['audio_url'] ?? '';
                
                if (!empty($title)) {
                    $stmt = $conn->prepare("INSERT INTO sermons (title, category, description, content, video_url, audio_url, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    if ($stmt) {
                        $stmt->bind_param("ssssss", $title, $category, $description, $content, $video_url, $audio_url);
                        $stmt->execute();
                        $success = "Sermon added successfully!";
                    }
                }
                break;
                
            case 'edit_sermon':
                $id = $_POST['id'] ?? '';
                $title = $_POST['title'] ?? '';
                $category = $_POST['category'] ?? '';
                $description = $_POST['description'] ?? '';
                $content = $_POST['content'] ?? '';
                $video_url = $_POST['video_url'] ?? '';
                $audio_url = $_POST['audio_url'] ?? '';
                
                if (!empty($id) && !empty($title)) {
                    $stmt = $conn->prepare("UPDATE sermons SET title=?, category=?, description=?, content=?, video_url=?, audio_url=? WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("ssssssi", $title, $category, $description, $content, $video_url, $audio_url, $id);
                        $stmt->execute();
                        $success = "Sermon updated successfully!";
                    }
                }
                break;
                
            case 'delete_sermon':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM sermons WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Sermon deleted successfully!";
                    }
                }
                break;
        }
    }
}

// Get sermons from database
$sermons = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT * FROM sermons ORDER BY created_at DESC LIMIT 20");
        if ($result) {
            $sermons = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch sermons: ' . $e->getMessage();
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
    <h1 class="page-title"><?php echo getAdminLogoImg(30, 30, 'margin-right: 10px'); ?>Sermon Management</h1>
    <p class="page-subtitle">Upload and manage sermons with video, audio, and text content</p>
</div>

<!-- Add New Sermon Form -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-plus-circle"></i>
        Add New Sermon
    </h2>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_sermon">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="title" class="form-label">Sermon Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="sunday-service">Sunday Service</option>
                        <option value="bible-study">Bible Study</option>
                        <option value="conference">Conference</option>
                        <option value="special-event">Special Event</option>
                        <option value="youth">Youth Service</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="sermon_date" class="form-label">Sermon Date *</label>
                    <input type="date" id="sermon_date" name="sermon_date" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="media_type" class="form-label">Media Type</label>
                    <select id="media_type" name="media_type" class="form-control">
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="sermon_series" class="form-label">Sermon Series</label>
            <input type="text" id="sermon_series" name="sermon_series" class="form-control" placeholder="e.g., The Book of Romans">
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Sermon Description *</label>
            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="media_file" class="form-label">Media File</label>
            <input type="file" id="media_file" name="media_file" class="form-control" accept="video/*,audio/*">
            <small class="text-muted">Upload video (MP4, WebM) or audio (MP3, WAV) files</small>
        </div>
        
        <button type="submit" class="btn-submit">
            <i class="fas fa-upload"></i> Upload Sermon
        </button>
    </form>
</div>

<!-- Existing Sermons -->
<div class="data-table">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i>
            Existing Sermons
        </h3>
        <span class="badge bg-primary"><?php echo count($sermons); ?> Total</span>
    </div>
    
    <?php if (empty($sermons)): ?>
        <div class="empty-state">
            <i class="fas fa-microphone-alt fa-3x mb-3"></i>
            <h4>No Sermons Found</h4>
            <p>Start by uploading your first sermon using the form above.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Media Type</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sermons as $sermon): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($sermon['title']); ?></strong>
                                <?php if (!empty($sermon['sermon_series'])): ?>
                                    <br><small class="text-muted">Series: <?php echo htmlspecialchars($sermon['sermon_series']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($sermon['category']); ?></span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($sermon['sermon_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $sermon['media_type'] === 'video' ? 'danger' : 'success'; ?>">
                                    <i class="fas fa-<?php echo $sermon['media_type'] === 'video' ? 'video' : 'music'; ?>"></i>
                                    <?php echo ucfirst($sermon['media_type']); ?>
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-eye"></i> <?php echo number_format($sermon['views'] ?? 0); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $sermon['status'] === 'published' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($sermon['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewSermon(<?php echo $sermon['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editSermon(<?php echo $sermon['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="item_type" value="sermon">
                                        <input type="hidden" name="item_id" value="<?php echo $sermon['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this sermon?')">
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
.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-title {
    color: #0f172a;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-title i {
    color: #fbbf24;
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

.btn-submit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
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

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    margin-right: 6px;
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
    .form-section {
        padding: 1rem;
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
</style>

<script>
function viewSermon(id) {
    // Implement view sermon functionality
    alert('View sermon ID: ' + id);
}

function editSermon(id) {
    // Implement edit sermon functionality
    alert('Edit sermon ID: ' + id);
}
</script>
