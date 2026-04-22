<?php
// USER MESSAGING SYSTEM - Salem Dominion Ministries
require_once 'config.php';
require_once 'db_connection.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$conn = createDatabaseConnection();
$user_id = $_SESSION['user_id'];
$user_info = null;

// Get user information
if ($conn) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($user_stmt) {
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_result->num_rows > 0) {
            $user_info = $user_result->fetch_assoc();
        }
        $user_stmt->close();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send_message':
            $recipient_id = $_POST['recipient_id'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            $message_type = $_POST['message_type'] ?? 'user_to_user';
            
            if (!empty($recipient_id) && !empty($subject) && !empty($message)) {
                $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, subject, message, message_type, status, created_at) VALUES (?, ?, ?, ?, ?, 'unread', NOW())");
                if ($stmt) {
                    $stmt->bind_param("iisss", $user_id, $recipient_id, $subject, $message, $message_type);
                    $stmt->execute();
                    $success = "Message sent successfully!";
                }
            } else {
                $error = "Please fill in all required fields.";
            }
            break;
            
        case 'mark_read':
            $message_id = $_POST['message_id'] ?? '';
            if (!empty($message_id)) {
                $stmt = $conn->prepare("UPDATE messages SET status='read' WHERE id=? AND recipient_id=?");
                if ($stmt) {
                    $stmt->bind_param("ii", $message_id, $user_id);
                    $stmt->execute();
                }
            }
            break;
            
        case 'delete_message':
            $message_id = $_POST['message_id'] ?? '';
            if (!empty($message_id)) {
                $stmt = $conn->prepare("DELETE FROM messages WHERE id=? AND (sender_id=? OR recipient_id=?)");
                if ($stmt) {
                    $stmt->bind_param("iii", $message_id, $user_id, $user_id);
                    $stmt->execute();
                    $success = "Message deleted successfully!";
                }
            }
            break;
            
        case 'reply_message':
            $parent_id = $_POST['parent_id'] ?? '';
            $recipient_id = $_POST['recipient_id'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            
            if (!empty($parent_id) && !empty($recipient_id) && !empty($subject) && !empty($message)) {
                $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, subject, message, message_type, status, parent_message_id, created_at) VALUES (?, ?, ?, ?, 'user_to_user', 'unread', ?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("iissi", $user_id, $recipient_id, $subject, $message, $parent_id);
                    $stmt->execute();
                    $success = "Reply sent successfully!";
                }
            } else {
                $error = "Please fill in all required fields.";
            }
            break;
    }
}

// Get messages for current user
$received_messages = [];
$sent_messages = [];
$all_users = [];

if ($conn) {
    // Get received messages
    $stmt = $conn->prepare("SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name, u.email as sender_email 
                           FROM messages m 
                           LEFT JOIN users u ON m.sender_id = u.id 
                           WHERE m.recipient_id = ? 
                           ORDER BY m.created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $received_messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    // Get sent messages
    $stmt = $conn->prepare("SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as recipient_name, u.email as recipient_email 
                           FROM messages m 
                           LEFT JOIN users u ON m.recipient_id = u.id 
                           WHERE m.sender_id = ? 
                           ORDER BY m.created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $sent_messages = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    // Get all users for messaging (excluding current user)
    $stmt = $conn->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as full_name, email FROM users WHERE id != ? ORDER BY first_name, last_name");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $all_users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Helper function for safe HTML output
function safe_html($string, $default = '') {
    return htmlspecialchars($string ?? $default, ENT_QUOTES, 'UTF-8');
}

// Helper function to format date
function format_message_date($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Salem Dominion Ministries</title>
    <meta name="description" content="Send and receive messages with other church members">
    <link rel="icon" href="public/logo-icon.jpeg">
    <link rel="shortcut icon" href="public/logo-icon.jpeg">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --midnight-blue: #0f172a;
            --heavenly-gold: #fbbf24;
            --ocean-blue: #0ea5e9;
            --snow-white: #ffffff;
            --gradient-divine: linear-gradient(135deg, var(--heavenly-gold) 0%, var(--ocean-blue) 100%);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--midnight-blue);
            color: var(--snow-white);
            min-height: 100vh;
        }

        .message-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .message-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .message-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .message-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--snow-white);
            padding: 15px 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            font-weight: 500;
        }

        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .tab-btn.active {
            border-bottom-color: var(--heavenly-gold);
            color: var(--heavenly-gold);
        }

        .message-list {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .message-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--heavenly-gold);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .message-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .message-item.unread {
            border-left-color: var(--ocean-blue);
            background: rgba(14, 165, 233, 0.1);
        }

        .message-subject {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--heavenly-gold);
        }

        .message-preview {
            opacity: 0.8;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-meta {
            font-size: 0.8rem;
            opacity: 0.6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .compose-btn {
            background: var(--gradient-divine);
            color: var(--snow-white);
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .compose-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(251, 191, 36, 0.4);
        }

        .modal-content {
            background: var(--midnight-blue);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header {
            background: var(--gradient-divine);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--snow-white);
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 0 0.2rem rgba(251, 191, 36, 0.25);
            color: var(--snow-white);
        }

        .btn-primary {
            background: var(--gradient-divine);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--ocean-blue) 0%, var(--heavenly-gold) 100%);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            opacity: 0.6;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--heavenly-gold);
            margin-bottom: 20px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .message-detail {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .message-full-subject {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--heavenly-gold);
            margin-bottom: 15px;
        }

        .message-full-content {
            line-height: 1.6;
            white-space: pre-wrap;
            margin-bottom: 20px;
        }

        .message-full-meta {
            font-size: 0.9rem;
            opacity: 0.7;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries" style="height: 40px; margin-right: 10px;">
                Salem Dominion Ministries
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="messages.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="message-container">
        <!-- Header -->
        <div class="message-header">
            <h1 class="mb-3">
                <i class="fas fa-envelope me-3"></i>Messages
            </h1>
            <p class="mb-0 opacity-80">Communicate with other church members and administrators</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Message Stats -->
        <div class="message-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($received_messages); ?></div>
                <div class="stat-label">Received Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($received_messages, fn($m) => $m['status'] == 'unread')); ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($sent_messages); ?></div>
                <div class="stat-label">Sent Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($all_users); ?></div>
                <div class="stat-label">Available Contacts</div>
            </div>
        </div>

        <!-- Compose Button -->
        <button class="compose-btn" data-bs-toggle="modal" data-bs-target="#composeModal">
            <i class="fas fa-pen me-2"></i>Compose New Message
        </button>

        <!-- Message Tabs -->
        <div class="message-tabs">
            <button class="tab-btn active" onclick="showTab('received')">
                <i class="fas fa-inbox me-2"></i>Received
            </button>
            <button class="tab-btn" onclick="showTab('sent')">
                <i class="fas fa-paper-plane me-2"></i>Sent
            </button>
        </div>

        <!-- Received Messages -->
        <div id="received-tab" class="tab-content active">
            <div class="message-list">
                <?php if (empty($received_messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>No received messages</h4>
                        <p>You haven't received any messages yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($received_messages as $message): ?>
                        <div class="message-item <?php echo $message['status'] == 'unread' ? 'unread' : ''; ?>" 
                             onclick="viewMessage(<?php echo $message['id']; ?>, 'received')">
                            <div class="message-subject">
                                <?php echo safe_html($message['subject']); ?>
                                <?php if ($message['status'] == 'unread'): ?>
                                    <span class="badge bg-primary ms-2">New</span>
                                <?php endif; ?>
                            </div>
                            <div class="message-preview">
                                From: <?php echo safe_html($message['sender_name'] ?? 'Unknown'); ?>
                            </div>
                            <div class="message-meta">
                                <span><i class="fas fa-user me-1"></i><?php echo safe_html($message['sender_name'] ?? 'Unknown'); ?></span>
                                <span><i class="fas fa-clock me-1"></i><?php echo format_message_date($message['created_at']); ?></span>
                            </div>
                        </div>
                        
                        <!-- Message Detail (hidden by default) -->
                        <div id="message-detail-<?php echo $message['id']; ?>" class="message-detail" style="display: none;">
                            <div class="message-full-subject"><?php echo safe_html($message['subject']); ?></div>
                            <div class="message-full-content"><?php echo safe_html($message['message']); ?></div>
                            <div class="message-full-meta">
                                <div><strong>From:</strong> <?php echo safe_html($message['sender_name'] ?? 'Unknown'); ?></div>
                                <div><strong>Email:</strong> <?php echo safe_html($message['sender_email'] ?? 'N/A'); ?></div>
                                <div><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></div>
                                <div><strong>Type:</strong> <?php echo safe_html($message['message_type']); ?></div>
                            </div>
                            <div class="action-buttons">
                                <?php if ($message['status'] == 'unread'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-check me-1"></i>Mark as Read
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-primary" onclick="showReplyModal(<?php echo $message['id']; ?>, '<?php echo addslashes($message['sender_name']); ?>', '<?php echo addslashes($message['sender_id']); ?>', '<?php echo addslashes('Re: ' . $message['subject']); ?>')">
                                    <i class="fas fa-reply me-1"></i>Reply
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sent Messages -->
        <div id="sent-tab" class="tab-content">
            <div class="message-list">
                <?php if (empty($sent_messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-paper-plane"></i>
                        <h4>No sent messages</h4>
                        <p>You haven't sent any messages yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($sent_messages as $message): ?>
                        <div class="message-item" onclick="viewMessage(<?php echo $message['id']; ?>, 'sent')">
                            <div class="message-subject">
                                <?php echo safe_html($message['subject']); ?>
                            </div>
                            <div class="message-preview">
                                To: <?php echo safe_html($message['recipient_name'] ?? 'Unknown'); ?>
                            </div>
                            <div class="message-meta">
                                <span><i class="fas fa-user me-1"></i><?php echo safe_html($message['recipient_name'] ?? 'Unknown'); ?></span>
                                <span><i class="fas fa-clock me-1"></i><?php echo format_message_date($message['created_at']); ?></span>
                            </div>
                        </div>
                        
                        <!-- Message Detail (hidden by default) -->
                        <div id="message-detail-<?php echo $message['id']; ?>" class="message-detail" style="display: none;">
                            <div class="message-full-subject"><?php echo safe_html($message['subject']); ?></div>
                            <div class="message-full-content"><?php echo safe_html($message['message']); ?></div>
                            <div class="message-full-meta">
                                <div><strong>To:</strong> <?php echo safe_html($message['recipient_name'] ?? 'Unknown'); ?></div>
                                <div><strong>Email:</strong> <?php echo safe_html($message['recipient_email'] ?? 'N/A'); ?></div>
                                <div><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></div>
                                <div><strong>Status:</strong> <?php echo safe_html($message['status']); ?></div>
                            </div>
                            <div class="action-buttons">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Compose Message Modal -->
    <div class="modal fade" id="composeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-pen me-2"></i>Compose New Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="send_message">
                        
                        <div class="mb-3">
                            <label for="recipient_id" class="form-label">Recipient</label>
                            <select class="form-select" id="recipient_id" name="recipient_id" required>
                                <option value="">Select a recipient...</option>
                                <?php foreach ($all_users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo safe_html($user['full_name']); ?> (<?php echo safe_html($user['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message_type" class="form-label">Message Type</label>
                            <select class="form-select" id="message_type" name="message_type">
                                <option value="user_to_user">User to User</option>
                                <option value="user_to_admin">User to Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reply Message Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-reply me-2"></i>Reply to Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reply_message">
                        <input type="hidden" id="reply_parent_id" name="parent_id">
                        <input type="hidden" id="reply_recipient_id" name="recipient_id">
                        
                        <div class="mb-3">
                            <label for="reply_subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="reply_subject" name="subject" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reply_message" class="form-label">Message</label>
                            <textarea class="form-control" id="reply_message" name="message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        function viewMessage(messageId, type) {
            const detailElement = document.getElementById('message-detail-' + messageId);
            
            if (detailElement.style.display === 'none') {
                detailElement.style.display = 'block';
                
                // Mark as read if it's unread
                if (type === 'received') {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="mark_read">
                        <input type="hidden" name="message_id" value="${messageId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            } else {
                detailElement.style.display = 'none';
            }
        }
        
        function showReplyModal(messageId, senderName, senderId, subject) {
            document.getElementById('reply_parent_id').value = messageId;
            document.getElementById('reply_recipient_id').value = senderId;
            document.getElementById('reply_subject').value = subject;
            document.getElementById('reply_message').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('replyModal'));
            modal.show();
        }
        
        // Auto-refresh messages every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

<?php
// Close connection
if ($conn) {
    $conn->close();
}
?>
