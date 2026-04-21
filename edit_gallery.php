<?php
// EDIT GALLERY PAGE - Salem Dominion Ministries
// Edit existing gallery items

session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get gallery ID
$gallery_id = intval($_GET['id'] ?? 0);
if ($gallery_id <= 0) {
    header('Location: admin_dashboard.php?section=gallery');
    exit;
}

// Initialize variables
$success = '';
$error = '';
$gallery = null;

// Get gallery data
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $gallery_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $gallery = $result->fetch_assoc();
        $stmt->close();
        
        if (!$gallery) {
            $error = "Gallery item not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading gallery item: " . $e->getMessage();
    }
} else {
    $error = "Database connection failed.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $gallery) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_gallery') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category'] ?? '');
        $media_type = $_POST['media_type'];
        
        // Validate media_type
        if (!in_array($media_type, ['image', 'video', 'audio'])) {
            $media_type = 'image';
        }
        
        if (empty($title) || empty($description)) {
            $error = 'Please fill in all required fields.';
        } else {
            if ($conn) {
                try {
                    // Handle media file upload if provided
                    $file_url = $gallery['file_url']; // Keep existing file URL
                    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['media_file'];
                        $allowed_types = [
                            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                            'video' => ['video/mp4', 'video/webm', 'video/ogg'],
                            'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3']
                        ];
                        
                        if (isset($allowed_types[$media_type]) && in_array($file['type'], $allowed_types[$media_type])) {
                            $upload_dir = "uploads/gallery/{$media_type}/";
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            // Delete old file if exists
                            if (!empty($gallery['file_url']) && file_exists($gallery['file_url'])) {
                                unlink($gallery['file_url']);
                            }
                            
                            $filename = uniqid() . '_' . basename($file['name']);
                            $filepath = $upload_dir . $filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $file_url = $filepath;
                            }
                        }
                    }
                    
                    $stmt = $conn->prepare("UPDATE gallery SET title = ?, description = ?, file_url = ?, file_type = ?, category = ? WHERE id = ?");
                    $stmt->bind_param("sssssi", $title, $description, $file_url, $media_type, $category, $gallery_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $success = 'Gallery item updated successfully!';
                    
                    // Refresh gallery data
                    $stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
                    $stmt->bind_param("i", $gallery_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $gallery = $result->fetch_assoc();
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $error = 'Failed to update gallery item: ' . $e->getMessage();
                }
            } else {
                $error = 'Database connection required.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gallery Item - Salem Dominion Ministries</title>
    <link rel="icon" href="public/logo-icon.jpeg">
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --heavenly-gold: #fbbf24;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .edit-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--snow-white);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--heavenly-gold);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: var(--snow-white);
        }

        .form-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            color: var(--snow-white);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.25);
            color: var(--snow-white);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--heavenly-gold), #f59e0b);
            color: var(--midnight-blue);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .current-media {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            text-align: center;
        }

        .current-media img,
        .current-media video,
        .current-media audio {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 1rem;
                margin: 0;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <a href="admin_dashboard.php?section=gallery" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Gallery
        </a>

        <div class="page-header">
            <h1 class="page-title">Edit Gallery Item</h1>
            <p>Update media content and information</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($gallery): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_gallery">
                
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="title">Media Title *</label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($gallery['title']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="media_type">Media Type *</label>
                                <select id="media_type" name="media_type" class="form-control" required>
                                    <option value="image" <?php echo $gallery['file_type'] === 'image' ? 'selected' : ''; ?>>Image</option>
                                    <option value="video" <?php echo $gallery['file_type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="audio" <?php echo $gallery['file_type'] === 'audio' ? 'selected' : ''; ?>>Audio</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="category">Category</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="general" <?php echo $gallery['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                                    <option value="service" <?php echo $gallery['category'] === 'service' ? 'selected' : ''; ?>>Service</option>
                                    <option value="event" <?php echo $gallery['category'] === 'event' ? 'selected' : ''; ?>>Event</option>
                                    <option value="outreach" <?php echo $gallery['category'] === 'outreach' ? 'selected' : ''; ?>>Outreach</option>
                                    <option value="ministry" <?php echo $gallery['category'] === 'ministry' ? 'selected' : ''; ?>>Ministry</option>
                                    <option value="youth" <?php echo $gallery['category'] === 'youth' ? 'selected' : ''; ?>>Youth</option>
                                    <option value="children" <?php echo $gallery['category'] === 'children' ? 'selected' : ''; ?>>Children</option>
                                    <option value="worship" <?php echo $gallery['category'] === 'worship' ? 'selected' : ''; ?>>Worship</option>
                                    <option value="sermon" <?php echo $gallery['category'] === 'sermon' ? 'selected' : ''; ?>>Sermon</option>
                                    <option value="fellowship" <?php echo $gallery['category'] === 'fellowship' ? 'selected' : ''; ?>>Fellowship</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="media_file">Replace Media File</label>
                                <input type="file" id="media_file" name="media_file" class="form-control" 
                                       accept="image/*,video/*,audio/*">
                                <small class="text-muted">Leave empty to keep current media</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($gallery['description']); ?></textarea>
                    </div>
                    
                    <?php if (!empty($gallery['file_url'])): ?>
                        <div class="current-media">
                            <h6 style="color: var(--heavenly-gold); margin-bottom: 1rem;">Current Media</h6>
                            <?php if ($gallery['file_type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($gallery['file_url']); ?>" alt="Gallery media">
                            <?php elseif ($gallery['file_type'] === 'video'): ?>
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($gallery['file_url']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php elseif ($gallery['file_type'] === 'audio'): ?>
                                <audio controls>
                                    <source src="<?php echo htmlspecialchars($gallery['file_url']); ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            <?php endif; ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($gallery['file_url']); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>
                    Update Gallery Item
                </button>
            </form>
        <?php else: ?>
            <div class="text-center" style="color: var(--snow-white); padding: 3rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Gallery item not found or unable to load.</p>
                <a href="admin_dashboard.php?section=gallery" class="btn-back">Back to Gallery</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
