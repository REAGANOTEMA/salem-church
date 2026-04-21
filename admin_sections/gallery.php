<?php
// Get global database connection
$conn = $GLOBALS['admin_db_connection'] ?? null;

// Get admin logo configuration
require_once 'logo_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_gallery':
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                $media_type = $_POST['media_type'] ?? '';
                $media_file = $_FILES['media_file'] ?? null;
                
                if (!empty($title) && !empty($media_file)) {
                    $target_dir = '../uploads/';
                    $target_file = $target_dir . basename($media_file['name']);
                    $upload_ok = 1;
                    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    
                    if ($media_type === 'image') {
                        $check = getimagesize($media_file['tmp_name']);
                        if ($check !== false) {
                            $upload_ok = 1;
                        } else {
                            $error = 'File is not an image.';
                            $upload_ok = 0;
                        }
                    } elseif ($media_type === 'video') {
                        if ($image_file_type !== 'mp4' && $image_file_type !== 'webm') {
                            $error = 'Only MP4 and WebM video files are allowed.';
                            $upload_ok = 0;
                        }
                    } elseif ($media_type === 'audio') {
                        if ($image_file_type !== 'mp3' && $image_file_type !== 'wav') {
                            $error = 'Only MP3 and WAV audio files are allowed.';
                            $upload_ok = 0;
                        }
                    }
                    
                    if ($upload_ok === 1) {
                        if (move_uploaded_file($media_file['tmp_name'], $target_file)) {
                            $stmt = $conn->prepare("INSERT INTO gallery (title, description, category, file_type, file_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                            if ($stmt) {
                                $stmt->bind_param("ssssss", $title, $description, $category, $media_type, $target_file);
                                $stmt->execute();
                                $success = "Gallery item added successfully!";
                            }
                        } else {
                            $error = 'Failed to upload file.';
                        }
                    }
                }
                break;
                
            case 'edit_gallery':
                $id = $_POST['id'] ?? '';
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $category = $_POST['category'] ?? '';
                $media_type = $_POST['media_type'] ?? '';
                $media_file = $_FILES['media_file'] ?? null;
                
                if (!empty($id) && !empty($title)) {
                    if ($media_file) {
                        $target_dir = '../uploads/';
                        $target_file = $target_dir . basename($media_file['name']);
                        $upload_ok = 1;
                        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                        
                        if ($media_type === 'image') {
                            $check = getimagesize($media_file['tmp_name']);
                            if ($check !== false) {
                                $upload_ok = 1;
                            } else {
                                $error = 'File is not an image.';
                                $upload_ok = 0;
                            }
                        } elseif ($media_type === 'video') {
                            if ($image_file_type !== 'mp4' && $image_file_type !== 'webm') {
                                $error = 'Only MP4 and WebM video files are allowed.';
                                $upload_ok = 0;
                            }
                        } elseif ($media_type === 'audio') {
                            if ($image_file_type !== 'mp3' && $image_file_type !== 'wav') {
                                $error = 'Only MP3 and WAV audio files are allowed.';
                                $upload_ok = 0;
                            }
                        }
                        
                        if ($upload_ok === 1) {
                            if (move_uploaded_file($media_file['tmp_name'], $target_file)) {
                                $stmt = $conn->prepare("UPDATE gallery SET title=?, description=?, category=?, file_type=?, file_url=? WHERE id=?");
                                if ($stmt) {
                                    $stmt->bind_param("sssssi", $title, $description, $category, $media_type, $target_file, $id);
                                    $stmt->execute();
                                    $success = "Gallery item updated successfully!";
                                }
                            } else {
                                $error = 'Failed to upload file.';
                            }
                        }
                    } else {
                        $stmt = $conn->prepare("UPDATE gallery SET title=?, description=?, category=? WHERE id=?");
                        if ($stmt) {
                            $stmt->bind_param("sssi", $title, $description, $category, $id);
                            $stmt->execute();
                            $success = "Gallery item updated successfully!";
                        }
                    }
                }
                break;
                
            case 'delete_gallery':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM gallery WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Gallery item deleted successfully!";
                    }
                }
                break;
        }
    }
}

// Get gallery items from database
$gallery_items = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 20");
        if ($result) {
            $gallery_items = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch gallery items: ' . $e->getMessage();
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
    <h1 class="page-title"><?php echo getAdminLogoImg(30, 30, 'margin-right: 10px'); ?>Gallery Management</h1>
    <p class="page-subtitle">Upload and manage multimedia content for the church gallery</p>
</div>

<!-- Upload New Media Form -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-upload"></i>
        Upload New Media
    </h2>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_gallery">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="title" class="form-label">Media Title *</label>
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
                        <option value="youth">Youth Activities</option>
                        <option value="outreach">Community Outreach</option>
                        <option value="worship">Worship Team</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="media_type" class="form-label">Media Type</label>
                    <select id="media_type" name="media_type" class="form-control">
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                        <option value="audio">Audio</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="media_file" class="form-label">Media File *</label>
                    <input type="file" id="media_file" name="media_file" class="form-control" accept="image/*,video/*,audio/*" required>
                    <small class="text-muted">Upload images (JPG, PNG, GIF), videos (MP4, WebM), or audio (MP3, WAV)</small>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description *</label>
            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
        </div>
        
        <button type="submit" class="btn-submit">
            <i class="fas fa-cloud-upload-alt"></i> Upload Media
        </button>
    </form>
</div>

<!-- Gallery Grid -->
<div class="gallery-grid">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-th"></i>
            Media Gallery
        </h3>
        <span class="badge bg-primary"><?php echo count($gallery_items); ?> Total</span>
    </div>
    
    <?php if (empty($gallery_items)): ?>
        <div class="empty-state">
            <i class="fas fa-images fa-3x mb-3"></i>
            <h4>No Media Found</h4>
            <p>Start by uploading your first media file using the form above.</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid-container">
            <?php foreach ($gallery_items as $item): ?>
                <div class="gallery-item">
                    <div class="media-preview">
                        <?php if ($item['file_type'] === 'image'): ?>
                            <img src="<?php echo htmlspecialchars($item['file_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="img-fluid">
                        <?php elseif ($item['file_type'] === 'video'): ?>
                            <video class="video-preview" controls>
                                <source src="<?php echo htmlspecialchars($item['file_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <audio class="audio-preview" controls>
                                <source src="<?php echo htmlspecialchars($item['file_url']); ?>" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        <?php endif; ?>
                    </div>
                    
                    <div class="media-info">
                        <h5 class="media-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="media-description"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</p>
                        
                        <div class="media-meta">
                            <span class="badge bg-<?php 
                                echo match($item['file_type']) {
                                    'image' => 'info',
                                    'video' => 'danger',
                                    'audio' => 'success',
                                    default => 'warning'
                                }; 
                            ?>">
                                <i class="fas fa-<?php 
                                    echo match($item['file_type']) {
                                        'image' => 'image',
                                        'video' => 'video',
                                        'audio' => 'music',
                                        default => 'file'
                                    }; 
                                ?>"></i>
                                <?php echo ucfirst($item['file_type']); ?>
                            </span>
                            
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($item['category']); ?></span>
                            
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                            </small>
                        </div>
                        
                        <div class="media-actions">
                            <button class="btn-action btn-view" onclick="viewMedia(<?php echo $item['id']; ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn-action btn-edit" onclick="editMedia(<?php echo $item['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_item">
                                <input type="hidden" name="item_type" value="gallery">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this media?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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

.gallery-grid {
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

.gallery-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
}

.gallery-item {
    background: #f8fafc;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.media-preview {
    width: 100%;
    height: 200px;
    overflow: hidden;
    position: relative;
}

.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-preview, .audio-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-info {
    padding: 1rem;
}

.media-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}

.media-description {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.media-meta {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.media-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
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
    margin-right: 4px;
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

@media (max-width: 768px) {
    .form-section {
        padding: 1rem;
    }
    
    .gallery-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        padding: 1rem;
    }
    
    .media-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .gallery-grid-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function viewMedia(id) {
    // Implement view media functionality
    alert('View media ID: ' + id);
}

function editMedia(id) {
    // Implement edit media functionality
    alert('Edit media ID: ' + id);
}
</script>
