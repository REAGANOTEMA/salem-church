<?php
session_start();
require_once './db_connection.php';
require_once './config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$conn = createDatabaseConnection();
$user = null;

if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
    } catch (Exception $e) {
        $error = 'Failed to load user profile: ' . $e->getMessage();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $church_role = trim($_POST['church_role'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            
            if (empty($name) || empty($email)) {
                $error = 'Name and email are required.';
            } else {
                if ($conn) {
                    try {
                        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, church_role = ?, bio = ? WHERE id = ?");
                        $stmt->bind_param("sssssi", $name, $email, $phone, $church_role, $bio, $user_id);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Update session
                        $_SESSION['user_name'] = $name;
                        
                        $success = 'Profile updated successfully!';
                        
                        // Reload user data
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $stmt->close();
                    } catch (Exception $e) {
                        $error = 'Failed to update profile: ' . $e->getMessage();
                    }
                }
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = 'All password fields are required.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match.';
            } elseif (strlen($new_password) < 8) {
                $error = 'New password must be at least 8 characters.';
            } else {
                if ($conn) {
                    try {
                        // Verify current password
                        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $current_user = $result->fetch_assoc();
                        $stmt->close();
                        
                        if ($current_user && password_verify($current_password, $current_user['password'])) {
                            // Update password
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $stmt->bind_param("si", $hashed_password, $user_id);
                            $stmt->execute();
                            $stmt->close();
                            
                            $success = 'Password changed successfully!';
                        } else {
                            $error = 'Current password is incorrect.';
                        }
                    } catch (Exception $e) {
                        $error = 'Failed to change password: ' . $e->getMessage();
                    }
                }
            }
            break;
            
        case 'upload_avatar':
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (in_array($file['type'], $allowed_types)) {
                    $upload_config = getUploadConfig();
                    $upload_dir = $upload_config['upload_path'] . 'avatars/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $filename = uniqid() . '_' . basename($file['name']);
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        if ($conn) {
                            try {
                                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                                $stmt->bind_param("si", $filepath, $user_id);
                                $stmt->execute();
                                $stmt->close();
                                
                                $success = 'Avatar uploaded successfully!';
                                
                                // Reload user data
                                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $user = $result->fetch_assoc();
                                $stmt->close();
                            } catch (Exception $e) {
                                $error = 'Failed to update avatar: ' . $e->getMessage();
                            }
                        }
                    }
                } else {
                    $error = 'Invalid file type. Please upload JPG, PNG, GIF, or WebP images.';
                }
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?php echo CHURCH_NAME; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo CHURCH_DESCRIPTION; ?>">
    <meta name="keywords" content="Salem Dominion Ministries, church, Christian, worship, Pastor Faty Musasizi, user profile">
    <meta name="author" content="<?php echo CHURCH_NAME; ?>">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    
    <!-- Open Graph Meta Tags for Social Media -->
    <meta property="og:title" content="User Profile - <?php echo CHURCH_NAME; ?>">
    <meta property="og:description" content="<?php echo CHURCH_DESCRIPTION; ?>">
    <meta property="og:image" content="<?php echo CHURCH_WEBSITE; ?>/public/logo-icon.jpeg">
    <meta property="og:url" content="<?php echo CHURCH_WEBSITE; ?>/user_profile.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo CHURCH_NAME; ?>">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="User Profile - <?php echo CHURCH_NAME; ?>">
    <meta name="twitter:description" content="<?php echo CHURCH_DESCRIPTION; ?>">
    <meta name="twitter:image" content="<?php echo CHURCH_WEBSITE; ?>/public/logo-icon.jpeg">
    <meta name="twitter:site" content="@<?php echo str_replace([' ', '.'], ['', ''], strtolower(CHURCH_NAME)); ?>">
    
    <!-- Favicon and Apple Touch Icon -->
    <link rel="icon" type="image/jpeg" href="<?php echo CHURCH_LOGO; ?>">
    <link rel="apple-touch-icon" href="<?php echo CHURCH_LOGO; ?>">
    <link rel="shortcut icon" href="<?php echo CHURCH_LOGO; ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo CHURCH_WEBSITE; ?>/user_profile.php">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a;
            --secondary-color: #0ea5e9;
            --accent-color: #fbbf24;
            --light-color: #f8fafc;
            --success-color: #10b981;
            --danger-color: #ef4444;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-color);
            color: var(--primary-color);
            line-height: 1.6;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 1rem;
            object-fit: cover;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .profile-role {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .profile-container {
            max-width: 800px;
            margin: -2rem auto 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .profile-tabs {
            display: flex;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-button {
            flex: 1;
            padding: 1rem;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab-button:hover {
            background: #e5e7eb;
        }

        .tab-button.active {
            background: var(--secondary-color);
            color: white;
        }

        .tab-content {
            display: none;
            padding: 2rem;
        }

        .tab-content.active {
            display: block;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-title i {
            color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary-color);
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
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #0284c7);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7, var(--secondary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: var(--success-color);
            color: white;
        }

        .alert-danger {
            background: var(--danger-color);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--primary-color);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .profile-container {
                margin: -1rem 1rem 1rem;
            }
            
            .profile-tabs {
                flex-direction: column;
            }
            
            .tab-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://via.placeholder.com/120x120/0ea5e9/ffffff?text=User'; ?>" 
             alt="Profile Avatar" class="profile-avatar">
        <h2 class="profile-name"><img src="<?php echo CHURCH_LOGO; ?>" alt="Salem Dominion Ministries" style="width: 25px; height: 25px; margin-right: 8px;"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h2>
        <p class="profile-role"><?php echo htmlspecialchars($user['church_role'] ?? 'Church Member'); ?></p>
    </div>

    <div class="profile-container">
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="profile-tabs">
            <button class="tab-button active" onclick="showTab('profile')">
                <i class="fas fa-user"></i> Profile
            </button>
            <button class="tab-button" onclick="showTab('security')">
                <i class="fas fa-lock"></i> Security
            </button>
            <button class="tab-button" onclick="showTab('activity')">
                <i class="fas fa-chart-line"></i> Activity
            </button>
        </div>

        <!-- Profile Tab -->
        <div id="profile-tab" class="tab-content active">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-section">
                    <h3 class="form-title">
                        <i class="fas fa-user-edit"></i>
                        Personal Information
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="church_role" class="form-label">Church Role</label>
                                <select id="church_role" name="church_role" class="form-control">
                                    <option value="">Select Role</option>
                                    <option value="Pastor" <?php echo ($user['church_role'] ?? '') === 'Pastor' ? 'selected' : ''; ?>>Pastor</option>
                                    <option value="Elder" <?php echo ($user['church_role'] ?? '') === 'Elder' ? 'selected' : ''; ?>>Elder</option>
                                    <option value="Deacon" <?php echo ($user['church_role'] ?? '') === 'Deacon' ? 'selected' : ''; ?>>Deacon</option>
                                    <option value="Youth Leader" <?php echo ($user['church_role'] ?? '') === 'Youth Leader' ? 'selected' : ''; ?>>Youth Leader</option>
                                    <option value="Worship Leader" <?php echo ($user['church_role'] ?? '') === 'Worship Leader' ? 'selected' : ''; ?>>Worship Leader</option>
                                    <option value="Member" <?php echo ($user['church_role'] ?? '') === 'Member' ? 'selected' : ''; ?>>Member</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Tab -->
        <div id="security-tab" class="tab-content">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-section">
                    <h3 class="form-title">
                        <i class="fas fa-key"></i>
                        Change Password
                    </h3>
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password *</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                </div>
            </form>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_avatar">
                
                <div class="form-section">
                    <h3 class="form-title">
                        <i class="fas fa-image"></i>
                        Profile Picture
                    </h3>
                    
                    <div class="form-group">
                        <label for="avatar" class="form-label">Upload Avatar</label>
                        <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*">
                        <small class="text-muted">Upload JPG, PNG, GIF, or WebP image (Max 5MB)</small>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Upload Avatar
                    </button>
                </div>
            </form>
        </div>

        <!-- Activity Tab -->
        <div id="activity-tab" class="tab-content">
            <div class="form-section">
                <h3 class="form-title">
                    <i class="fas fa-chart-line"></i>
                    Your Activity
                </h3>
                
                <?php if ($conn): ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM sermon_reactions WHERE user_id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                echo $result->fetch_assoc()['count'];
                                $stmt->close();
                                ?>
                            </div>
                            <div class="stat-label">Sermon Reactions</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_reactions WHERE user_id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                echo $result->fetch_assoc()['count'];
                                $stmt->close();
                                ?>
                            </div>
                            <div class="stat-label">Gallery Reactions</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM comments WHERE user_id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                echo $result->fetch_assoc()['count'];
                                $stmt->close();
                                ?>
                            </div>
                            <div class="stat-label">Comments</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE sender_id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                echo $result->fetch_assoc()['count'];
                                $stmt->close();
                                ?>
                            </div>
                            <div class="stat-label">Messages Sent</div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Activity statistics are not available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => button.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
