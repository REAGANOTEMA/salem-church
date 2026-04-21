<?php
require_once 'config.php';\n/**
 * Sermon Management API
 * Handles all sermon-related operations for Salem Dominion Ministries
 */

require_once 'session_helper.php';
secure_session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Get current user info
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;
$isAdmin = $userRole === 'admin';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_sermon':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                exit;
            }
            
            // Handle file uploads
            $media_url = null;
            $thumbnail_url = null;
            
            if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                $media_url = uploadFile($_FILES['media_file'], 'sermons');
            }
            
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnail_url = uploadFile($_FILES['thumbnail'], 'thumbnails');
            }
            
            $sermonId = intval($_POST['sermon_id']);
            $title = trim($_POST['title']);
            $sermon_date = $_POST['sermon_date'] ?? date('Y-m-d');
            $sermon_series = $_POST['sermon_series'] ?? null;
            $category = $_POST['category'] ?? '';
            $duration = $_POST['duration'] ?? null;
            $scripture = $_POST['scripture'] ?? null;
            $description = trim($_POST['description']);
            $sermon_text = $_POST['sermon_text'] ?? null;
            $media_type = $_POST['media_type'] ?? 'none';
            $status = $_POST['status'] ?? 'published';
            $featured = $_POST['featured'] ?? '0';
            
            if (empty($title) || empty($description)) {
                echo json_encode(['success' => false, 'message' => 'Title and description are required']);
                exit;
            }
            
            if ($sermonId > 0) {
                // Update existing sermon
                $stmt = $db->prepare("UPDATE sermons SET title = ?, sermon_date = ?, sermon_series = ?, category = ?, duration = ?, scripture = ?, description = ?, sermon_text = ?, media_type = ?, media_url = ?, thumbnail = ?, status = ?, featured = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("sssssssssssssi", $title, $sermon_date, $sermon_series, $category, $duration, $scripture, $description, $sermon_text, $media_type, $media_url, $thumbnail_url, $status, $featured, $sermonId);
            } else {
                // Add new sermon
                $stmt = $db->prepare("INSERT INTO sermons (title, sermon_date, sermon_series, category, duration, scripture, description, sermon_text, media_type, media_url, thumbnail, status, featured, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssssssssssii", $title, $sermon_date, $sermon_series, $category, $duration, $scripture, $description, $sermon_text, $media_type, $media_url, $thumbnail_url, $status, $featured, $userId);
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Sermon saved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save sermon']);
            }
            break;
            
        case 'get_sermon':
            $sermonId = intval($_GET['id']);
            $stmt = $db->prepare("SELECT * FROM sermons WHERE id = ?");
            $stmt->bind_param("i", $sermonId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $sermon = $result->fetch_assoc();
                echo json_encode(['success' => true, 'sermon' => $sermon]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sermon not found']);
            }
            break;
            
        case 'get_all_sermons':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                exit;
            }
            
            $result = $db->query("SELECT * FROM sermons ORDER BY sermon_date DESC, created_at DESC");
            $sermons = [];
            while ($row = $result->fetch_assoc()) {
                $sermons[] = $row;
            }
            echo json_encode(['success' => true, 'sermons' => $sermons]);
            break;
            
        case 'delete_sermon':
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
                exit;
            }
            
            $sermonId = intval($_POST['id'] ?? $_GET['id']);
            
            // Get sermon info to delete files
            $stmt = $db->prepare("SELECT media_url, thumbnail FROM sermons WHERE id = ?");
            $stmt->bind_param("i", $sermonId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $sermon = $result->fetch_assoc();
                
                // Delete media file
                if ($sermon['media_url']) {
                    $filePath = __DIR__ . '/' . $sermon['media_url'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                // Delete thumbnail
                if ($sermon['thumbnail']) {
                    $filePath = __DIR__ . '/' . $sermon['thumbnail'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                
                // Delete from database
                $stmt = $db->prepare("DELETE FROM sermons WHERE id = ?");
                $stmt->bind_param("i", $sermonId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Sermon deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete sermon']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Sermon not found']);
            }
            break;
            
        case 'get_sermon_detail':
            $sermonId = intval($_GET['id']);
            $stmt = $db->prepare("SELECT s.*, u.first_name, u.last_name FROM sermons s LEFT JOIN users u ON s.created_by = u.id WHERE s.id = ?");
            $stmt->bind_param("i", $sermonId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $sermon = $result->fetch_assoc();
                $content = generateSermonPlayerContent($sermon);
                echo json_encode(['success' => true, 'content' => $content]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sermon not found']);
            }
            break;
            
        case 'get_sermon_reactions':
            $sermonId = intval($_GET['id']);
            $stmt = $db->prepare("SELECT reaction_type, COUNT(*) as count FROM sermon_reactions WHERE sermon_id = ? GROUP BY reaction_type");
            $stmt->bind_param("i", $sermonId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reactionCounts = [];
            while ($row = $result->fetch_assoc()) {
                $reactionCounts[$row['reaction_type']] = $row['count'];
            }
            
            echo json_encode(['success' => true, 'reactions' => $reactionCounts]);
            break;
            
        case 'get_sermon_comments':
            $sermonId = intval($_GET['id']);
            $stmt = $db->prepare("SELECT c.*, u.first_name, u.last_name FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ? AND c.post_type = 'sermon' ORDER BY c.created_at DESC");
            $stmt->bind_param("i", $sermonId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = $row;
            }
            
            echo json_encode(['success' => true, 'comments' => $comments]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function uploadFile($file, $subfolder) {
    $uploadDir = 'uploads/' . $subfolder . '/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileName = uniqid() . '_' . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    
    // Validate file type
    $allowedTypes = [
        'video' => ['video/mp4', 'video/webm', 'video/ogg'],
        'audio' => ['audio/mp3', 'audio/wav', 'audio/ogg', 'audio/m4a'],
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    ];
    
    $fileType = mime_content_type($file['tmp_name']);
    $isValid = false;
    
    foreach ($allowedTypes as $types) {
        if (in_array($fileType, $types)) {
            $isValid = true;
            break;
        }
    }
    
    if (!$isValid) {
        throw new Exception('Invalid file type');
    }
    
    // Validate file size (100MB max)
    if ($file['size'] > 100 * 1024 * 1024) {
        throw new Exception('File size must be less than 100MB');
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $filePath;
    } else {
        throw new Exception('Failed to upload file');
    }
}

function generateSermonPlayerContent($sermon) {
    $mediaContent = '';
    
    if ($sermon['media_type'] === 'video' && $sermon['media_url']) {
        $mediaContent = '<div class="ratio ratio-16x9 mb-4">
            <video controls class="w-100">
                <source src="' . htmlspecialchars($sermon['media_url']) . '" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>';
    } elseif ($sermon['media_type'] === 'audio' && $sermon['media_url']) {
        $mediaContent = '<div class="audio-player bg-dark p-4 rounded mb-4">
            <audio controls class="w-100">
                <source src="' . htmlspecialchars($sermon['media_url']) . '" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>';
    }
    
    $scriptureText = $sermon['scripture'] ? '<p class="text-primary fw-bold"><i class="fas fa-bible me-2"></i>' . htmlspecialchars($sermon['scripture']) . '</p>' : '';
    $durationText = $sermon['duration'] ? '<span class="badge bg-info me-2"><i class="fas fa-clock me-1"></i>' . $sermon['duration'] . ' min</span>' : '';
    
    $content = '<div class="sermon-player">
        <h2 class="mb-3">' . htmlspecialchars($sermon['title']) . '</h2>
        <div class="mb-3">
            ' . $scriptureText . '
            <span class="badge bg-primary me-2">' . htmlspecialchars($sermon['category']) . '</span>
            ' . $durationText . '
            <span class="badge bg-secondary"><i class="fas fa-eye me-1"></i>' . number_format($sermon['views'] ?? 0) . ' views</span>
        </div>
        
        ' . $mediaContent . '
        
        <div class="sermon-details">
            <p class="text-muted mb-3">By ' . htmlspecialchars(($sermon['first_name'] ?? 'Apostle') . ' ' . ($sermon['last_name'] ?? 'Faty')) . ' on ' . date('F j, Y', strtotime($sermon['sermon_date'])) . '</p>
            
            <div class="description mb-4">
                <h5>Description</h5>
                <p>' . htmlspecialchars($sermon['description']) . '</p>
            </div>';
    
    if ($sermon['sermon_text']) {
        $content .= '<div class="sermon-notes mb-4">
            <h5>Sermon Notes</h5>
            <div class="alert alert-info">
                <pre class="mb-0">' . htmlspecialchars($sermon['sermon_text']) . '</pre>
            </div>
        </div>';
    }
    
    $content .= '</div>';
    
    return $content;
}
?>
