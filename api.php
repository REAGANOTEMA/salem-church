<?php
/**
 * Salem Dominion Ministries - Backend API
 * Handles User Authentication, supreme admin logic for Reagan Otema, and Blog Comments.
 */

header("Access-Control-Allow-Origin: *");
header("Content-Security-Policy: default-src " . CSP_DEFAULT_SRC . ";" .
       "script-src " . CSP_SCRIPT_SRC . ";" .
       "style-src " . CSP_STYLE_SRC . ";" .
       "font-src " . CSP_FONT_SRC . ";" .
       "connect-src " . CSP_CONNECT_SRC . ";" .
       "img-src 'self' data:;" // Allow images from self and data URIs
);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

// --- Database Connection ---
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
    $conn->set_charset(DB_CHARSET);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Database connection failed']));
    }
    return $conn;
}

// --- Activity Logger ---
function logActivity($db, $userId, $action, $table, $recordId = null) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $agent = $_SERVER['HTTP_USER_AGENT'];
    // Ensure the action string is not too long for the 'action' column
    $action = substr($action, 0, 255); 
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $userId, $action, $table, $recordId, $ip, $agent);
    $stmt->execute();
}

// --- Response Helper ---
function sendResponse($success, $data = [], $message = '', $code = 200) {
    http_response_code($code);
    $response = ['success' => $success];
    if (!empty($message)) $response['message'] = $message;
    if (!empty($data)) $response = array_merge($response, $data);
    echo json_encode($response);
    exit;
}

// --- JWT Simple Implementation ---
function createJWT($user) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode([
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'exp' => time() + SESSION_LIFETIME
    ]));
    $signature = hash_hmac('sha256', "$header.$payload", JWT_SECRET);
    return "$header.$payload.$signature";
}

function getAuth() {
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    if (isset($headers['authorization'])) {
        $token = str_replace('Bearer ', '', $headers['authorization']);
        $parts = explode('.', $token);
        if (count($parts) != 3) return null;
        if (hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", JWT_SECRET) !== $parts[2]) return null;
        $payload = json_decode(base64_decode($parts[1]), true);
        return ($payload && $payload['exp'] > time()) ? $payload : null;
    }
    return null;
}

// --- Request Handling ---
$request_uri = $_SERVER['PATH_INFO'] ?? '';
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);
$auth = getAuth();
$db = getDB();

// AUTHENTICATION
if (strpos($request_uri, '/auth') !== false || $action === 'login' || $action === 'register') {
    if ($action === 'login' || strpos($request_uri, '/login') !== false) {
        $email = $input['email'] ?? '';
        $pass = $input['password'] ?? '';
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res && password_verify($pass, $res['password_hash'])) {
            unset($res['password_hash']);
            $db->query("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = " . $res['id']);
            sendResponse(true, ['user' => $res, 'token' => createJWT($res)], 'Login successful');
        }
        sendResponse(false, [], 'Invalid email or password', 401);
    }

    if ($action === 'register' || strpos($request_uri, '/register') !== false) {
        $fname = $input['firstName'] ?? '';
        $lname = $input['lastName'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $pass = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?, 'member')");
        $stmt->bind_param("sssss", $fname, $lname, $email, $phone, $pass);
        if ($stmt->execute()) {
            $id = $db->insert_id;
            $u = ['id' => $id, 'email' => $email, 'role' => 'member'];
            sendResponse(true, ['token' => createJWT($u)], 'Account created');
        }
        sendResponse(false, [], 'Registration failed', 400);
    }

    if ($action === 'verify' || strpos($request_uri, '/verify') !== false) {
        if ($auth) {
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, role, avatar_url FROM users WHERE id = ?");
            $stmt->bind_param("i", $auth['id']);
            $stmt->execute();
            sendResponse(true, ['user' => $stmt->get_result()->fetch_assoc()]);
        }
        sendResponse(false, [], 'Unauthorized', 401);
    }

    // Forgot Password Logic
    if ($action === 'forgot-password' && $method === 'POST') {
        $email = $input['email'] ?? '';
        $user = $db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db->insert("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)", [$user['id'], $token, $expires]);
            
            $resetLink = APP_URL . "/reset_password.php?token=" . $token;
            $subject = "Password Reset - Salem Dominion Ministries";
            $message = "Please click the link below to reset your password: \n\n" . $resetLink;
            
            if (MAIL_ENABLED) {
                mail($email, $subject, $message, "From: " . MAIL_FROM);
            }
            sendResponse(true, [], 'Reset link sent to email');
        }
        sendResponse(false, [], 'Email not found', 404);
    }

    // Reset Password Execution
    if ($action === 'reset-password' && $method === 'POST') {
        $token = $input['token'] ?? '';
        $newPass = password_hash($input['password'] ?? '', PASSWORD_DEFAULT);
        $reset = $db->selectOne("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0", [$token]);
        
        if ($reset) {
            $db->update("UPDATE users SET password_hash = ? WHERE id = ?", [$newPass, $reset['user_id']]);
            $db->update("UPDATE password_resets SET used = 1 WHERE token = ?", [$token]);
            sendResponse(true, [], 'Password updated successfully');
        }
        sendResponse(false, [], 'Invalid or expired token', 400);
    }
}

// DONATIONS
if ($action === 'confirm-donation' && $method === 'POST') {
    if (!$auth || $auth['role'] !== 'admin') {
        sendResponse(false, [], 'Admin access required', 403);
    }

    $donationId = $input['donation_id'] ?? 0;
    if ($donationId > 0) {
        $donation = $db->selectOne("SELECT * FROM donations WHERE id = ?", [$donationId]);
        if (!$donation) {
            sendResponse(false, [], 'Donation not found', 404);
        }
        if ($donation['status'] === 'completed') {
            sendResponse(false, [], 'Donation already confirmed', 400);
        }

        $result = $db->update(
            "UPDATE donations SET status = 'completed', processed_by = ? WHERE id = ?",
            [$auth['id'], $donationId]
        );

        if ($result) {
            logActivity($db, $auth['id'], "Confirmed payment for donation ID " . $donationId, "donations", $donationId);
            sendResponse(true, [], 'Donation confirmed successfully');
        } else {
            sendResponse(false, [], 'Failed to confirm donation', 500);
        }
    }
    sendResponse(false, [], 'Invalid donation ID', 400);
}

// COMMENTS
if (strpos($request_uri, '/comments') !== false) {
    if ($method === 'GET') {
        $postId = $_GET['postId'] ?? 0;
        $res = $db->query("SELECT c.*, u.first_name, u.last_name, u.avatar_url FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ".intval($postId)." AND c.status = 'approved' ORDER BY c.created_at DESC");
        sendResponse(true, ['data' => $res]);
    }
    if ($method === 'POST') {
        if (!$auth) sendResponse(false, [], 'Login required', 401);
        $postId = $input['postId'] ?? 0;
        $content = $input['commentContent'] ?? '';
        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, comment_content, status) VALUES (?, ?, ?, 'approved')");
        $stmt->bind_param("iis", $postId, $auth['id'], $content);
        if ($stmt->execute()) sendResponse(true, [], 'Comment posted');
        sendResponse(false, [], 'Failed to post comment', 500);
    }
}

// SERMON MANAGEMENT
if (strpos($request_uri, '/sermons') !== false) {
    if ($method === 'GET' && isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'get_sermon':
                $sermonId = intval($_GET['id']);
                $stmt = $db->prepare("SELECT * FROM sermons WHERE id = ?");
                $stmt->bind_param("i", $sermonId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($sermon = $result->fetch_assoc()) {
                    sendResponse(true, ['sermon' => $sermon]);
                } else {
                    sendResponse(false, [], 'Sermon not found', 404);
                }
                break;
                
            case 'get_sermon_detail':
                $sermonId = intval($_GET['id']);
                $stmt = $db->prepare("SELECT s.*, u.first_name, u.last_name FROM sermons s LEFT JOIN users u ON s.created_by = u.id WHERE s.id = ?");
                $stmt->bind_param("i", $sermonId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($sermon = $result->fetch_assoc()) {
                    // Generate HTML content
                    $content = "
                        <div class='sermon-detail'>
                            <div class='sermon-header mb-4'>
                                <h2 class='sermon-title'>" . htmlspecialchars($sermon['title']) . "</h2>
                                <div class='sermon-meta'>
                                    <span class='sermon-category badge bg-primary'>" . htmlspecialchars($sermon['category']) . "</span>
                                    <span class='sermon-date'>" . date('F j, Y', strtotime($sermon['sermon_date'])) . "</span>
                                    <span class='sermon-views'><i class='fas fa-eye'></i> " . number_format($sermon['views'] ?? 0) . " views</span>
                                </div>
                            </div>
                            <div class='media-player mb-4'>
                                " . ($sermon['media_type'] === 'video' && $sermon['media_url'] ? 
                                    "<video class='video-player' controls><source src='" . htmlspecialchars($sermon['media_url']) . "' type='video/mp4'></video>" :
                                    ($sermon['media_type'] === 'audio' && $sermon['media_url'] ?
                                    "<div class='audio-player'>
                                        <h3><i class='fas fa-microphone-alt'></i> Audio Sermon</h3>
                                        <audio controls style='width: 100%; margin-top: 1rem;'>
                                            <source src='" . htmlspecialchars($sermon['media_url']) . "' type='audio/mpeg'>
                                        </audio>
                                    </div>" :
                                    "<div class='text-center text-white py-5'>
                                        <i class='fas fa-microphone' style='font-size: 3rem; margin-bottom: 1rem; display: block;'></i>
                                        <h3>Sermon Available</h3>
                                        <p>Join us for this powerful message</p>
                                    </div>")
                                . "
                            </div>
                            <div class='sermon-content'>
                                " . nl2br(htmlspecialchars($sermon['description'] ?? '')) . "
                                " . ($sermon['sermon_text'] ? "<div class='sermon-text mt-4'><h4>Scripture & Notes</h4><p>" . nl2br(htmlspecialchars($sermon['sermon_text'])) . "</p></div>" : '') . "
                            </div>
                            <div class='comments-section'>
                                <div class='comments-header'>
                                    <h3 class='comments-title'>Comments</h3>
                                    <span class='comment-count'>0 comments</span>
                                </div>
                                <div class='comment-form'>
                                    <textarea class='comment-input' placeholder='Share your thoughts on this sermon...' rows='3'></textarea>
                                    <div class='comment-actions'>
                                        <button class='btn-comment' onclick='addSermonComment(" . $sermonId . ")'>Post Comment</button>
                                    </div>
                                </div>
                                <div class='comment-list'>
                                    <p class='text-center text-muted'>Loading comments...</p>
                                </div>
                            </div>
                        </div>
                    ";
                    sendResponse(true, ['content' => $content]);
                } else {
                    sendResponse(false, [], 'Sermon not found', 404);
                }
                break;
                
            case 'get_sermon_reactions':
                $sermonId = intval($_GET['sermon_id']);
                $stmt = $db->prepare("SELECT reaction_type, COUNT(*) as count FROM sermon_reactions WHERE sermon_id = ? GROUP BY reaction_type");
                $stmt->bind_param("i", $sermonId);
                $stmt->execute();
                $result = $stmt->get_result();
                $reactions = [];
                while ($row = $result->fetch_assoc()) {
                    $reactions[$row['reaction_type']] = $row['count'];
                }
                sendResponse(true, ['reactions' => $reactions]);
                break;
                
            case 'get_sermon_comments':
                $sermonId = intval($_GET['sermon_id']);
                $stmt = $db->prepare("SELECT c.*, u.first_name, u.last_name FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ? AND c.post_type = 'sermon' ORDER BY c.created_at DESC");
                $stmt->bind_param("i", $sermonId);
                $stmt->execute();
                $result = $stmt->get_result();
                $comments = [];
                while ($row = $result->fetch_assoc()) {
                    $comments[] = $row;
                }
                sendResponse(true, ['comments' => $comments]);
                break;
                
            case 'download_sermon':
                $sermonId = intval($_GET['id']);
                $stmt = $db->prepare("SELECT media_url, title FROM sermons WHERE id = ? AND media_url IS NOT NULL");
                $stmt->bind_param("i", $sermonId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($sermon = $result->fetch_assoc()) {
                    sendResponse(true, ['download_url' => $sermon['media_url'], 'title' => $sermon['title']]);
                } else {
                    sendResponse(false, [], 'Download not available', 404);
                }
                break;
        }
    }
    
    if ($method === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add_sermon':
                if (!$auth || $auth['role'] !== 'admin') {
                    sendResponse(false, [], 'Unauthorized', 403);
                }
                
                $title = trim($_POST['title']);
                $sermonDate = $_POST['sermon_date'];
                $sermonSeries = trim($_POST['sermon_series']);
                $category = $_POST['category'];
                $duration = intval($_POST['duration']);
                $description = trim($_POST['description']);
                $sermonText = trim($_POST['sermon_text']);
                $mediaType = $_POST['media_type'];
                $status = $_POST['status'];
                $sermonId = intval($_POST['sermon_id']);
                
                if (empty($title) || empty($sermonDate)) {
                    sendResponse(false, [], 'Title and sermon date are required', 400);
                }
                
                // Handle file upload
                $mediaUrl = null;
                if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === 0) {
                    $uploadDir = 'uploads/sermons/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = time() . '_' . basename($_FILES['media_file']['name']);
                    $targetPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['media_file']['tmp_name'], $targetPath)) {
                        $mediaUrl = $targetPath;
                    }
                }
                
                if ($sermonId > 0) {
                    $stmt = $db->prepare("UPDATE sermons SET title = ?, sermon_date = ?, sermon_series = ?, category = ?, duration = ?, description = ?, sermon_text = ?, media_type = ?, media_url = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("sssissssssi", $title, $sermonDate, $sermonSeries, $category, $duration, $description, $sermonText, $mediaType, $mediaUrl, $status, $sermonId);
                } else {
                    $stmt = $db->prepare("INSERT INTO sermons (title, sermon_date, sermon_series, category, duration, description, sermon_text, media_type, media_url, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("sssisssssssi", $title, $sermonDate, $sermonSeries, $category, $duration, $description, $sermonText, $mediaType, $mediaUrl, $status, $auth['id']);
                }
                
                if ($stmt->execute()) {
                    sendResponse(true, [], 'Sermon saved successfully');
                } else {
                    sendResponse(false, [], 'Failed to save sermon', 500);
                }
                break;
                
            case 'delete_sermon':
                if (!$auth || $auth['role'] !== 'admin') {
                    sendResponse(false, [], 'Unauthorized', 403);
                }
                
                $sermonId = intval($_GET['id']);
                $stmt = $db->prepare("DELETE FROM sermons WHERE id = ?");
                $stmt->bind_param("i", $sermonId);
                if ($stmt->execute()) {
                    sendResponse(true, [], 'Sermon deleted successfully');
                } else {
                    sendResponse(false, [], 'Failed to delete sermon', 500);
                }
                break;
        }
    }
}

// NEWS MANAGEMENT
if (strpos($request_uri, '/news') !== false) {
    if ($method === 'GET' && isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'get_news':
                $newsId = intval($_GET['id']);
                $stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
                $stmt->bind_param("i", $newsId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($news = $result->fetch_assoc()) {
                    sendResponse(true, ['news' => $news]);
                } else {
                    sendResponse(false, [], 'News not found', 404);
                }
                break;
                
            case 'get_news_detail':
                $newsId = intval($_GET['id']);
                $stmt = $db->prepare("SELECT n.*, u.first_name, u.last_name FROM news n LEFT JOIN users u ON n.author_id = u.id WHERE n.id = ?");
                $stmt->bind_param("i", $newsId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($news = $result->fetch_assoc()) {
                    // Generate HTML content
                    $content = "
                        <div class='news-detail'>
                            <div class='news-header mb-4'>
                                <h2 class='news-title'>" . htmlspecialchars($news['title']) . "</h2>
                                <div class='news-meta'>
                                    <span class='news-category badge bg-primary'>" . htmlspecialchars($news['category']) . "</span>
                                    <span class='news-date'>" . date('F j, Y', strtotime($news['created_at'])) . "</span>
                                    <span class='news-views'><i class='fas fa-eye'></i> " . number_format($news['views'] ?? 0) . " views</span>
                                </div>
                            </div>
                            <div class='news-content'>
                                " . nl2br(htmlspecialchars($news['content'])) . "
                            </div>
                            <div class='comments-section'>
                                <div class='comments-header'>
                                    <h3 class='comments-title'>Comments</h3>
                                    <span class='comment-count'>0 comments</span>
                                </div>
                                <div class='comment-form'>
                                    <textarea class='comment-input' placeholder='Write a comment...' rows='3'></textarea>
                                    <div class='comment-actions'>
                                        <button class='btn-comment' onclick='addComment(" . $newsId . ")'>Post Comment</button>
                                    </div>
                                </div>
                                <div class='comment-list'>
                                    <p class='text-center text-muted'>Loading comments...</p>
                                </div>
                            </div>
                        </div>
                    ";
                    sendResponse(true, ['content' => $content]);
                } else {
                    sendResponse(false, [], 'News not found', 404);
                }
                break;
                
            case 'get_reactions':
                $newsId = intval($_GET['news_id']);
                $stmt = $db->prepare("SELECT reaction_type, COUNT(*) as count FROM news_reactions WHERE news_id = ? GROUP BY reaction_type");
                $stmt->bind_param("i", $newsId);
                $stmt->execute();
                $result = $stmt->get_result();
                $reactions = [];
                while ($row = $result->fetch_assoc()) {
                    $reactions[$row['reaction_type']] = $row['count'];
                }
                sendResponse(true, ['reactions' => $reactions]);
                break;
                
            case 'get_comments':
                $newsId = intval($_GET['news_id']);
                $stmt = $db->prepare("SELECT c.*, u.first_name, u.last_name FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ? AND c.post_type = 'news' ORDER BY c.created_at DESC");
                $stmt->bind_param("i", $newsId);
                $stmt->execute();
                $result = $stmt->get_result();
                $comments = [];
                while ($row = $result->fetch_assoc()) {
                    $comments[] = $row;
                }
                sendResponse(true, ['comments' => $comments]);
                break;
        }
    }
    
    if ($method === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add_news':
                if (!$auth || $auth['role'] !== 'admin') {
                    sendResponse(false, [], 'Unauthorized', 403);
                }
                
                $title = trim($_POST['title']);
                $category = $_POST['category'];
                $excerpt = trim($_POST['excerpt']);
                $content = trim($_POST['content']);
                $status = $_POST['status'];
                $newsId = intval($_POST['news_id']);
                
                if (empty($title) || empty($content)) {
                    sendResponse(false, [], 'Title and content are required', 400);
                }
                
                if ($newsId > 0) {
                    $stmt = $db->prepare("UPDATE news SET title = ?, category = ?, excerpt = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("sssssi", $title, $category, $excerpt, $content, $status, $newsId);
                } else {
                    $stmt = $db->prepare("INSERT INTO news (title, category, excerpt, content, status, author_id, created_at, published_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("sssssi", $title, $category, $excerpt, $content, $status, $auth['id']);
                }
                
                if ($stmt->execute()) {
                    sendResponse(true, [], 'News saved successfully');
                } else {
                    sendResponse(false, [], 'Failed to save news', 500);
                }
                break;
                
            case 'delete_news':
                if (!$auth || $auth['role'] !== 'admin') {
                    sendResponse(false, [], 'Unauthorized', 403);
                }
                
                $newsId = intval($_GET['id']);
                $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
                $stmt->bind_param("i", $newsId);
                if ($stmt->execute()) {
                    sendResponse(true, [], 'News deleted successfully');
                } else {
                    sendResponse(false, [], 'Failed to delete news', 500);
                }
                break;
        }
    }
}

// SUPREME ADMIN LOGIC: Reagan Otema makes anyone an admin
if ($action === 'grant_admin' && $method === 'POST') {
    // Reagan Otema supreme check
    if (!$auth || $auth['email'] !== 'reaganotemas@gmail.com') {
        sendResponse(false, [], 'Supreme developer access required', 403);
    }
    
    $targetUserId = $input['userId'] ?? 0;
    if ($targetUserId > 0) {
        $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->bind_param("i", $targetUserId);
        if ($stmt->execute()) {
            sendResponse(true, [], 'User promoted to admin successfully');
        }
    }
    sendResponse(false, [], 'Invalid user ID', 400);
}

$db->close();
sendResponse(false, [], 'Endpoint not found', 404);
?>