<?php
// EDIT SERMON PAGE - Salem Dominion Ministries
// Edit existing sermons

session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get sermon ID
$sermon_id = intval($_GET['id'] ?? 0);
if ($sermon_id <= 0) {
    header('Location: admin_dashboard.php?section=sermons');
    exit;
}

// Initialize variables
$success = '';
$error = '';
$sermon = null;

// Get sermon data
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM sermons WHERE id = ?");
        $stmt->bind_param("i", $sermon_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $sermon = $result->fetch_assoc();
        $stmt->close();
        
        if (!$sermon) {
            $error = "Sermon not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading sermon: " . $e->getMessage();
    }
} else {
    $error = "Database connection failed.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sermon) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_sermon') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sermon_date = $_POST['sermon_date'];
        $category = $_POST['category'];
        $media_type = $_POST['media_type'];
        $sermon_series = trim($_POST['sermon_series'] ?? '');
        
        // Validate media_type
        if (!in_array($media_type, ['video', 'audio', 'text'])) {
            $media_type = 'video';
        }
        
        if (empty($title) || empty($description) || empty($sermon_date)) {
            $error = 'Please fill in all required fields.';
        } else {
            if ($conn) {
                try {
                    // Handle media file upload if provided
                    $media_url = $sermon['media_url']; // Keep existing media URL
                    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['media_file'];
                        $allowed_types = [
                            'video' => ['video/mp4', 'video/webm', 'video/ogg'],
                            'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3']
                        ];
                        
                        if (isset($allowed_types[$media_type]) && in_array($file['type'], $allowed_types[$media_type])) {
                            $upload_dir = "uploads/sermons/{$media_type}/";
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            // Delete old media file if exists
                            if (!empty($sermon['media_url']) && file_exists($sermon['media_url'])) {
                                unlink($sermon['media_url']);
                            }
                            
                            $filename = uniqid() . '_' . basename($file['name']);
                            $filepath = $upload_dir . $filename;
                            
                            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                                $media_url = $filepath;
                            }
                        }
                    }
                    
                    $stmt = $conn->prepare("UPDATE sermons SET title = ?, description = ?, sermon_date = ?, category = ?, media_type = ?, media_url = ?, sermon_series = ? WHERE id = ?");
                    $stmt->bind_param("sssssssi", $title, $description, $sermon_date, $category, $media_type, $media_url, $sermon_series, $sermon_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $success = 'Sermon updated successfully!';
                    
                    // Refresh sermon data
                    $stmt = $conn->prepare("SELECT * FROM sermons WHERE id = ?");
                    $stmt->bind_param("i", $sermon_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $sermon = $result->fetch_assoc();
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $error = 'Failed to update sermon: ' . $e->getMessage();
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
    <title>Edit Sermon - Salem Dominion Ministries</title>
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
        <a href="admin_dashboard.php?section=sermons" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Sermons
        </a>

        <div class="page-header">
            <h1 class="page-title">Edit Sermon</h1>
            <p>Update sermon information and media</p>
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

        <?php if ($sermon): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_sermon">
                
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="title">Sermon Title *</label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($sermon['title']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="sermon_date">Sermon Date *</label>
                                <input type="date" id="sermon_date" name="sermon_date" class="form-control" 
                                       value="<?php echo htmlspecialchars($sermon['sermon_date']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="category">Category</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="general" <?php echo $sermon['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                                    <option value="faith" <?php echo $sermon['category'] === 'faith' ? 'selected' : ''; ?>>Faith</option>
                                    <option value="love" <?php echo $sermon['category'] === 'love' ? 'selected' : ''; ?>>Love</option>
                                    <option value="prayer" <?php echo $sermon['category'] === 'prayer' ? 'selected' : ''; ?>>Prayer</option>
                                    <option value="healing" <?php echo $sermon['category'] === 'healing' ? 'selected' : ''; ?>>Healing</option>
                                    <option value="prosperity" <?php echo $sermon['category'] === 'prosperity' ? 'selected' : ''; ?>>Prosperity</option>
                                    <option value="deliverance" <?php echo $sermon['category'] === 'deliverance' ? 'selected' : ''; ?>>Deliverance</option>
                                    <option value="prophecy" <?php echo $sermon['category'] === 'prophecy' ? 'selected' : ''; ?>>Prophecy</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="sermon_series">Sermon Series</label>
                                <input type="text" id="sermon_series" name="sermon_series" class="form-control" 
                                       value="<?php echo htmlspecialchars($sermon['sermon_series'] ?? ''); ?>" 
                                       placeholder="Enter sermon series (optional)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($sermon['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="media_type">Media Type</label>
                                <select id="media_type" name="media_type" class="form-control" required>
                                    <option value="">Select media type</option>
                                    <option value="video" <?php echo $sermon['media_type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="audio" <?php echo $sermon['media_type'] === 'audio' ? 'selected' : ''; ?>>Audio</option>
                                    <option value="text" <?php echo $sermon['media_type'] === 'text' ? 'selected' : ''; ?>>Text Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="media_file">Replace Media File</label>
                                <input type="file" id="media_file" name="media_file" class="form-control" 
                                       accept="video/*,audio/*">
                                <small class="text-muted">Leave empty to keep current media. Supports MP4, WebM, MP3, WAV files (max 100MB)</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($sermon['media_url'])): ?>
                        <div class="current-media">
                            <h6 style="color: var(--heavenly-gold); margin-bottom: 1rem;">Current Media</h6>
                            <div class="d-flex align-items-center">
                                <span class="badge" style="background: var(--heavenly-gold); color: var(--midnight-blue); margin-right: 1rem;">
                                    <i class="fas fa-<?php echo $sermon['media_type'] === 'video' ? 'video' : 'music'; ?> me-1"></i>
                                    <?php echo ucfirst($sermon['media_type']); ?>
                                </span>
                                <small class="text-muted"><?php echo htmlspecialchars($sermon['media_url']); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>
                    Update Sermon
                </button>
            </form>
        <?php else: ?>
            <div class="text-center" style="color: var(--snow-white); padding: 3rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Sermon not found or unable to load.</p>
                <a href="admin_dashboard.php?section=sermons" class="btn-back">Back to Sermons</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
