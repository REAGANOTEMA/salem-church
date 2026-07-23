<?php
/**
 * Salem Dominion Ministries - Admin AJAX Handler
 * Handles all admin CRUD operations via POST with CSRF verification
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isAdminLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please log in to continue.'], 401);
}

if (!verifyCSRFToken()) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token. Please refresh the page.'], 403);
}

$admin    = currentAdmin();
$db       = Database::getInstance();         // Website DB (content tables)
$dbAdmin  = Database::getNamed('admin');     // Admin DB (admin_users, activity_logs, etc.)
$dbMembers = Database::getNamed('members');  // Members DB (users)
$action   = $_POST['action'] ?? $_GET['action'] ?? '';

function adminSuccess(array $data = [], string $message = 'Success'): void {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}

function adminError(string $message, int $code = 400): void {
    jsonResponse(['success' => false, 'message' => $message], $code);
}

function adminInput(): array {
    return is_array($_POST) ? $_POST : [];
}

function requiredFields(array $data, array $fields): void {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            adminError(ucfirst(str_replace('_', ' ', $field)) . ' is required.');
        }
    }
}

switch ($action) {

    // ═══ NEWS ═══
    case 'add_news':
    case 'edit_news':
        $data = adminInput();
        requiredFields($data, ['title', 'content']);
        $newsData = [
            'title'      => trim($data['title']),
            'category'   => trim($data['category'] ?? 'general'),
            'excerpt'    => trim($data['excerpt'] ?? ''),
            'content'    => trim($data['content']),
            'status'     => trim($data['status'] ?? 'draft'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $img = uploadFile($_FILES['image'], 'news', ALLOWED_IMAGE_TYPES);
            if ($img) $newsData['image_url'] = $img;
        }
        if ($action === 'edit_news') {
            $newsId = intval($data['id'] ?? 0);
            if (!$newsId) adminError('Invalid news ID.');
            $db->update('news', $newsData, 'id = ?', [$newsId]);
            logActivity($db, 'updated_news', 'news', $admin['id'], "Updated news #{$newsId}");
            adminSuccess([], 'News article updated successfully.');
        } else {
            $newsData['author_id']   = $admin['id'];
            $newsData['created_at']  = date('Y-m-d H:i:s');
            $newsData['published_at'] = $newsData['status'] === 'published' ? date('Y-m-d H:i:s') : null;
            $id = $db->insert('news', $newsData);
            logActivity($db, 'added_news', 'news', $admin['id'], "Added news #{$id}");
            adminSuccess(['id' => $id], 'News article created successfully.');
        }
        break;

    case 'delete_news':
        $newsId = intval($_POST['id'] ?? 0);
        if (!$newsId) adminError('Invalid news ID.');
        $news = $db->fetch("SELECT image_url FROM news WHERE id = ?", [$newsId]);
        if ($news && !empty($news['image_url'])) deleteFile($news['image_url']);
        $db->delete('news', 'id = ?', [$newsId]);
        logActivity($db, 'deleted_news', 'news', $admin['id'], "Deleted news #{$newsId}");
        adminSuccess([], 'News article deleted successfully.');
        break;

    // ═══ EVENTS ═══
    case 'add_event':
    case 'edit_event':
        $data = adminInput();
        requiredFields($data, ['title', 'event_date']);
        $eventData = [
            'title'        => trim($data['title']),
            'description'  => trim($data['description'] ?? ''),
            'event_date'   => $data['event_date'],
            'end_date'     => $data['end_date'] ?? null,
            'event_time'   => trim($data['event_time'] ?? ''),
            'location'     => trim($data['location'] ?? ''),
            'category'     => trim($data['category'] ?? 'general'),
            'status'       => trim($data['status'] ?? 'draft'),
            'is_recurring' => intval($data['is_recurring'] ?? 0),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $img = uploadFile($_FILES['image'], 'events', ALLOWED_IMAGE_TYPES);
            if ($img) $eventData['image_url'] = $img;
        }
        if ($action === 'edit_event') {
            $eventId = intval($data['id'] ?? 0);
            if (!$eventId) adminError('Invalid event ID.');
            $db->update('events', $eventData, 'id = ?', [$eventId]);
            logActivity($db, 'updated_event', 'events', $admin['id'], "Updated event #{$eventId}");
            adminSuccess([], 'Event updated successfully.');
        } else {
            $eventData['author_id']  = $admin['id'];
            $eventData['created_at'] = date('Y-m-d H:i:s');
            $id = $db->insert('events', $eventData);
            logActivity($db, 'added_event', 'events', $admin['id'], "Added event #{$id}");
            adminSuccess(['id' => $id], 'Event created successfully.');
        }
        break;

    case 'delete_event':
        $eventId = intval($_POST['id'] ?? 0);
        if (!$eventId) adminError('Invalid event ID.');
        $event = $db->fetch("SELECT image_url FROM events WHERE id = ?", [$eventId]);
        if ($event && !empty($event['image_url'])) deleteFile($event['image_url']);
        $db->delete('events', 'id = ?', [$eventId]);
        logActivity($db, 'deleted_event', 'events', $admin['id'], "Deleted event #{$eventId}");
        adminSuccess([], 'Event deleted successfully.');
        break;

    // ═══ SERMONS ═══
    case 'add_sermon':
    case 'edit_sermon':
        $data = adminInput();
        requiredFields($data, ['title', 'sermon_date']);
        $sermonData = [
            'title'         => trim($data['title']),
            'sermon_date'   => $data['sermon_date'],
            'sermon_series' => trim($data['sermon_series'] ?? ''),
            'category'      => trim($data['category'] ?? 'general'),
            'duration'      => intval($data['duration'] ?? 0),
            'description'   => trim($data['description'] ?? ''),
            'sermon_text'   => trim($data['sermon_text'] ?? ''),
            'media_type'    => trim($data['media_type'] ?? 'none'),
            'status'        => trim($data['status'] ?? 'draft'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $type = $sermonData['media_type'];
            if ($type === 'video') {
                $media = uploadFile($_FILES['media_file'], 'sermons/video', ALLOWED_VIDEO_TYPES);
            } elseif ($type === 'audio') {
                $media = uploadFile($_FILES['media_file'], 'sermons/audio', ALLOWED_AUDIO_TYPES);
            } else {
                $media = uploadFile($_FILES['media_file'], 'sermons/video', array_merge(ALLOWED_VIDEO_TYPES, ALLOWED_AUDIO_TYPES));
            }
            if ($media) $sermonData['media_url'] = $media;
        }
        if ($action === 'edit_sermon') {
            $sermonId = intval($data['id'] ?? 0);
            if (!$sermonId) adminError('Invalid sermon ID.');
            $db->update('sermons', $sermonData, 'id = ?', [$sermonId]);
            logActivity($db, 'updated_sermon', 'sermons', $admin['id'], "Updated sermon #{$sermonId}");
            adminSuccess([], 'Sermon updated successfully.');
        } else {
            $sermonData['created_by'] = $admin['id'];
            $sermonData['created_at'] = date('Y-m-d H:i:s');
            $id = $db->insert('sermons', $sermonData);
            logActivity($db, 'added_sermon', 'sermons', $admin['id'], "Added sermon #{$id}");
            adminSuccess(['id' => $id], 'Sermon added successfully.');
        }
        break;

    case 'delete_sermon':
        $sermonId = intval($_POST['id'] ?? 0);
        if (!$sermonId) adminError('Invalid sermon ID.');
        $sermon = $db->fetch("SELECT media_url FROM sermons WHERE id = ?", [$sermonId]);
        if ($sermon && !empty($sermon['media_url'])) deleteFile($sermon['media_url']);
        $db->delete('sermons', 'id = ?', [$sermonId]);
        logActivity($db, 'deleted_sermon', 'sermons', $admin['id'], "Deleted sermon #{$sermonId}");
        adminSuccess([], 'Sermon deleted successfully.');
        break;

    // ═══ GALLERY ═══
    case 'upload_gallery':
        $data = adminInput();
        $album    = trim($data['album'] ?? 'general');
        $category = trim($data['category'] ?? 'image');
        $title    = trim($data['title'] ?? '');
        if (!isset($_FILES['gallery_file']) || $_FILES['gallery_file']['error'] !== UPLOAD_ERR_OK) {
            adminError('Please select a file to upload.');
        }
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['gallery_file']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
            $fileType = 'image'; $subDir = 'gallery/image'; $allowed = ALLOWED_IMAGE_TYPES;
        } elseif (in_array($mimeType, ALLOWED_VIDEO_TYPES)) {
            $fileType = 'video'; $subDir = 'gallery/video'; $allowed = ALLOWED_VIDEO_TYPES;
        } elseif (in_array($mimeType, ALLOWED_AUDIO_TYPES)) {
            $fileType = 'audio'; $subDir = 'gallery/audio'; $allowed = ALLOWED_AUDIO_TYPES;
        } else {
            adminError('File type not allowed.');
        }
        $fileUrl = uploadFile($_FILES['gallery_file'], $subDir, $allowed);
        if (!$fileUrl) adminError('File upload failed. Please try again.');
        $id = $db->insert('gallery', [
            'title'       => $title ?: basename($_FILES['gallery_file']['name']),
            'file_url'    => $fileUrl,
            'file_type'   => $fileType,
            'album'       => $album,
            'category'    => $category,
            'description' => trim($data['description'] ?? ''),
            'uploaded_by' => $admin['id'],
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        logActivity($db, 'uploaded_gallery', 'gallery', $admin['id'], "Uploaded gallery item #{$id}");
        adminSuccess(['id' => $id, 'file_url' => $fileUrl], 'File uploaded successfully.');
        break;

    case 'delete_gallery':
        $galleryId = intval($_POST['id'] ?? 0);
        if (!$galleryId) adminError('Invalid gallery item ID.');
        $item = $db->fetch("SELECT file_url FROM gallery WHERE id = ?", [$galleryId]);
        if ($item && !empty($item['file_url'])) deleteFile($item['file_url']);
        $db->delete('gallery', 'id = ?', [$galleryId]);
        logActivity($db, 'deleted_gallery', 'gallery', $admin['id'], "Deleted gallery item #{$galleryId}");
        adminSuccess([], 'Gallery item deleted successfully.');
        break;

    // ═══ YOUTUBE LIVE ═══
    case 'save_live':
        $data = adminInput();
        $youtubeUrl = trim($data['youtube_url'] ?? '');
        $title      = trim($data['title'] ?? '');
        $existing = $db->fetch("SELECT id FROM youtube_live WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        if ($existing) {
            $db->update('youtube_live', ['youtube_url' => $youtubeUrl, 'title' => $title, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$existing['id']]);
        } else {
            $db->insert('youtube_live', ['youtube_url' => $youtubeUrl, 'title' => $title, 'is_live' => 0, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        }
        logActivity($db, 'updated_live', 'live', $admin['id'], 'Updated YouTube Live settings');
        adminSuccess([], 'Live settings saved successfully.');
        break;

    case 'toggle_live':
        $data = adminInput();
        $isLive = intval($data['is_live'] ?? 0);
        $existing = $db->fetch("SELECT id FROM youtube_live WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        if ($existing) {
            $db->update('youtube_live', ['is_live' => $isLive, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$existing['id']]);
        }
        logActivity($db, $isLive ? 'went_live' : 'went_offline', 'live', $admin['id'], $isLive ? 'Started live stream' : 'Stopped live stream');
        adminSuccess([], $isLive ? 'You are now live!' : 'Live stream ended.');
        break;

    // ═══ SETTINGS ═══
    case 'update_settings':
        $data = adminInput();
        $settingsModule = $data['settings_module'] ?? 'general';
        $allowedKeys = [
            'church_name', 'church_phone', 'church_email', 'church_address',
            'church_website', 'youtube_url', 'facebook_url', 'tiktok_url',
            'whatsapp_url', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass',
            'smtp_from_email', 'smtp_from_name', 'site_title', 'site_description',
            'maintenance_mode', 'registration_enabled', 'items_per_page',
        ];
        $saved = 0;
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedKeys) && $key !== 'action' && $key !== 'settings_module' && $key !== 'csrf_token') {
                setSetting($key, trim($value));
                $saved++;
            }
        }
        logActivity($db, 'updated_settings', 'settings', $admin['id'], "Updated settings module: {$settingsModule} ({$saved} fields)");
        adminSuccess([], 'Settings saved successfully.');
        break;

    // ═══ LEADERSHIP ═══
    case 'add_leader':
    case 'edit_leader':
        $data = adminInput();
        requiredFields($data, ['name', 'position']);
        $leaderData = [
            'name'        => trim($data['name']),
            'position'    => trim($data['position']),
            'description' => trim($data['description'] ?? ''),
            'email'       => trim($data['email'] ?? ''),
            'phone'       => trim($data['phone'] ?? ''),
            'sort_order'  => intval($data['sort_order'] ?? 0),
            'is_active'   => intval($data['is_active'] ?? 1),
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photo = uploadFile($_FILES['photo'], 'avatars', ALLOWED_IMAGE_TYPES);
            if ($photo) $leaderData['photo_url'] = $photo;
        }
        if ($action === 'edit_leader') {
            $leaderId = intval($data['id'] ?? 0);
            if (!$leaderId) adminError('Invalid leader ID.');
            $db->update('leaders', $leaderData, 'id = ?', [$leaderId]);
            logActivity($db, 'updated_leader', 'leadership', $admin['id'], "Updated leader #{$leaderId}");
            adminSuccess([], 'Leader updated successfully.');
        } else {
            $leaderData['created_at'] = date('Y-m-d H:i:s');
            $id = $db->insert('leaders', $leaderData);
            logActivity($db, 'added_leader', 'leadership', $admin['id'], "Added leader #{$id}");
            adminSuccess(['id' => $id], 'Leader added successfully.');
        }
        break;

    case 'delete_leader':
        $leaderId = intval($_POST['id'] ?? 0);
        if (!$leaderId) adminError('Invalid leader ID.');
        $leader = $db->fetch("SELECT photo_url FROM leaders WHERE id = ?", [$leaderId]);
        if ($leader && !empty($leader['photo_url'])) deleteFile($leader['photo_url']);
        $db->delete('leaders', 'id = ?', [$leaderId]);
        logActivity($db, 'deleted_leader', 'leadership', $admin['id'], "Deleted leader #{$leaderId}");
        adminSuccess([], 'Leader removed successfully.');
        break;

    // ═══ MINISTRIES ═══
    case 'add_ministry':
    case 'edit_ministry':
        $data = adminInput();
        requiredFields($data, ['name']);
        $ministryData = [
            'name'         => trim($data['name']),
            'category'     => trim($data['category'] ?? 'general'),
            'description'  => trim($data['description'] ?? ''),
            'leader_name'  => trim($data['leader_name'] ?? ''),
            'leader_email' => trim($data['leader_email'] ?? ''),
            'leader_phone' => trim($data['leader_phone'] ?? ''),
            'meeting_time' => trim($data['meeting_time'] ?? ''),
            'sort_order'   => intval($data['sort_order'] ?? 0),
            'is_active'    => intval($data['is_active'] ?? 1),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $img = uploadFile($_FILES['image'], 'ministries', ALLOWED_IMAGE_TYPES);
            if ($img) $ministryData['image_url'] = $img;
        }
        if ($action === 'edit_ministry') {
            $ministryId = intval($data['id'] ?? 0);
            if (!$ministryId) adminError('Invalid ministry ID.');
            $db->update('ministries', $ministryData, 'id = ?', [$ministryId]);
            logActivity($db, 'updated_ministry', 'ministries', $admin['id'], "Updated ministry #{$ministryId}");
            adminSuccess([], 'Ministry updated successfully.');
        } else {
            $ministryData['created_at'] = date('Y-m-d H:i:s');
            $id = $db->insert('ministries', $ministryData);
            logActivity($db, 'added_ministry', 'ministries', $admin['id'], "Added ministry #{$id}");
            adminSuccess(['id' => $id], 'Ministry added successfully.');
        }
        break;

    case 'delete_ministry':
        $ministryId = intval($_POST['id'] ?? 0);
        if (!$ministryId) adminError('Invalid ministry ID.');
        $ministry = $db->fetch("SELECT image_url FROM ministries WHERE id = ?", [$ministryId]);
        if ($ministry && !empty($ministry['image_url'])) deleteFile($ministry['image_url']);
        $db->delete('ministries', 'id = ?', [$ministryId]);
        logActivity($db, 'deleted_ministry', 'ministries', $admin['id'], "Deleted ministry #{$ministryId}");
        adminSuccess([], 'Ministry deleted successfully.');
        break;

    // ═══ TESTIMONIALS ═══
    case 'approve_testimonial':
        $testId = intval($_POST['id'] ?? 0);
        if (!$testId) adminError('Invalid testimonial ID.');
        $db->update('testimonials', ['status' => 'approved', 'approved_by' => $admin['id'], 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$testId]);
        logActivity($db, 'approved_testimonial', 'testimonials', $admin['id'], "Approved testimonial #{$testId}");
        adminSuccess([], 'Testimonial approved and published.');
        break;

    case 'reject_testimonial':
        $testId = intval($_POST['id'] ?? 0);
        if (!$testId) adminError('Invalid testimonial ID.');
        $db->update('testimonials', ['status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$testId]);
        logActivity($db, 'rejected_testimonial', 'testimonials', $admin['id'], "Rejected testimonial #{$testId}");
        adminSuccess([], 'Testimonial rejected.');
        break;

    case 'delete_testimonial':
        $testId = intval($_POST['id'] ?? 0);
        if (!$testId) adminError('Invalid testimonial ID.');
        $db->delete('testimonials', 'id = ?', [$testId]);
        logActivity($db, 'deleted_testimonial', 'testimonials', $admin['id'], "Deleted testimonial #{$testId}");
        adminSuccess([], 'Testimonial deleted.');
        break;

    // ═══ USERS ═══
    case 'add_user':
    case 'edit_user':
        $data = adminInput();
        requiredFields($data, ['first_name', 'last_name', 'email']);
        $userData = [
            'first_name' => trim($data['first_name']),
            'last_name'  => trim($data['last_name']),
            'email'      => trim($data['email']),
            'phone'      => trim($data['phone'] ?? ''),
            'role'       => trim($data['role'] ?? 'member'),
            'is_active'  => intval($data['is_active'] ?? 1),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (!validateEmail($userData['email'])) adminError('Please provide a valid email address.');
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES);
            if ($avatar) $userData['avatar_url'] = $avatar;
        }
        if ($action === 'edit_user') {
            $userId = intval($data['id'] ?? 0);
            if (!$userId) adminError('Invalid user ID.');
            $existing = $dbMembers->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$userData['email'], $userId]);
            if ($existing) adminError('A user with this email already exists.');
            if (!empty($data['password']) && strlen($data['password']) >= 8) {
                $userData['password_hash'] = password_hash($data['password'], HASH_ALGO, ['cost' => HASH_COST]);
            }
            $dbMembers->update('users', $userData, 'id = ?', [$userId]);
            logActivity($db, 'updated_user', 'users', $admin['id'], "Updated user #{$userId}");
            adminSuccess([], 'User updated successfully.');
        } else {
            $existing = $dbMembers->fetch("SELECT id FROM users WHERE email = ?", [$userData['email']]);
            if ($existing) adminError('A user with this email already exists.');
            if (empty($data['password']) || strlen($data['password']) < 8) adminError('Password must be at least 8 characters.');
            $userData['password_hash'] = password_hash($data['password'], HASH_ALGO, ['cost' => HASH_COST]);
            $userData['created_at']    = date('Y-m-d H:i:s');
            $id = $dbMembers->insert('users', $userData);
            logActivity($db, 'added_user', 'users', $admin['id'], "Added user #{$id}");
            adminSuccess(['id' => $id], 'User created successfully.');
        }
        break;

    case 'delete_user':
        $userId = intval($_POST['id'] ?? 0);
        if (!$userId) adminError('Invalid user ID.');
        if ($userId === $admin['id']) adminError('You cannot delete your own account.');
        $user = $dbMembers->fetch("SELECT avatar_url FROM users WHERE id = ?", [$userId]);
        if ($user && !empty($user['avatar_url'])) deleteFile($user['avatar_url']);
        $dbMembers->delete('users', 'id = ?', [$userId]);
        logActivity($db, 'deleted_user', 'users', $admin['id'], "Deleted user #{$userId}");
        adminSuccess([], 'User deleted successfully.');
        break;

    // ═══ DONATIONS ═══
    case 'confirm_donation':
        $donationId = intval($_POST['id'] ?? 0);
        if (!$donationId) adminError('Invalid donation ID.');
        $donation = $db->fetch("SELECT status FROM donations WHERE id = ?", [$donationId]);
        if (!$donation) adminError('Donation not found.');
        if ($donation['status'] === 'completed') adminError('Donation already confirmed.');
        $db->update('donations', ['status' => 'completed', 'processed_by' => $admin['id'], 'processed_at' => date('Y-m-d H:i:s')], 'id = ?', [$donationId]);
        logActivity($db, 'confirmed_donation', 'donations', $admin['id'], "Confirmed donation #{$donationId}");
        adminSuccess([], 'Donation confirmed successfully.');
        break;

    case 'reject_donation':
        $donationId = intval($_POST['id'] ?? 0);
        if (!$donationId) adminError('Invalid donation ID.');
        $db->update('donations', ['status' => 'rejected', 'processed_by' => $admin['id'], 'processed_at' => date('Y-m-d H:i:s')], 'id = ?', [$donationId]);
        logActivity($db, 'rejected_donation', 'donations', $admin['id'], "Rejected donation #{$donationId}");
        adminSuccess([], 'Donation rejected.');
        break;

    // ═══ PRAYER REQUESTS ═══
    case 'mark_prayer_answered':
        $prayerId = intval($_POST['id'] ?? 0);
        if (!$prayerId) adminError('Invalid prayer request ID.');
        $db->update('prayer_requests', ['is_answered' => 1, 'answered_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$prayerId]);
        logActivity($db, 'marked_prayer_answered', 'prayer_requests', $admin['id'], "Marked prayer #{$prayerId} as answered");
        adminSuccess([], 'Prayer request marked as answered.');
        break;

    case 'archive_prayer':
        $prayerId = intval($_POST['id'] ?? 0);
        if (!$prayerId) adminError('Invalid prayer request ID.');
        $db->update('prayer_requests', ['status' => 'archived', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$prayerId]);
        logActivity($db, 'archived_prayer', 'prayer_requests', $admin['id'], "Archived prayer #{$prayerId}");
        adminSuccess([], 'Prayer request archived.');
        break;

    case 'delete_prayer':
        $prayerId = intval($_POST['id'] ?? 0);
        if (!$prayerId) adminError('Invalid prayer request ID.');
        $db->delete('prayer_requests', 'id = ?', [$prayerId]);
        logActivity($db, 'deleted_prayer', 'prayer_requests', $admin['id'], "Deleted prayer #{$prayerId}");
        adminSuccess([], 'Prayer request deleted.');
        break;

    // ═══ CONTACT MESSAGES ═══
    case 'delete_message':
        $messageId = intval($_POST['id'] ?? 0);
        if (!$messageId) adminError('Invalid message ID.');
        $db->delete('contact_messages', 'id = ?', [$messageId]);
        logActivity($db, 'deleted_message', 'contact_messages', $admin['id'], "Deleted message #{$messageId}");
        adminSuccess([], 'Message deleted.');
        break;

    case 'mark_message_read':
        $messageId = intval($_POST['id'] ?? 0);
        if (!$messageId) adminError('Invalid message ID.');
        $db->update('contact_messages', ['status' => 'read', 'read_at' => date('Y-m-d H:i:s'), 'read_by' => $admin['id']], 'id = ?', [$messageId]);
        logActivity($db, 'read_message', 'contact_messages', $admin['id'], "Read message #{$messageId}");
        adminSuccess([], 'Message marked as read.');
        break;

    // ═══ NEWSLETTER SUBSCRIBERS ═══
    case 'subscribe_newsletter':
        $data  = adminInput();
        $email = trim($data['email'] ?? '');
        if (empty($email) || !validateEmail($email)) adminError('Please provide a valid email address.');
        $existing = $db->fetch("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$email]);
        if ($existing) {
            if (!$existing['is_active']) {
                $db->update('newsletter_subscribers', ['is_active' => 1, 'unsubscribed_at' => null], 'id = ?', [$existing['id']]);
                adminSuccess([], 'Subscriber reactivated.');
            }
            adminError('This email is already subscribed.');
        }
        $db->insert('newsletter_subscribers', ['email' => $email, 'is_active' => 1, 'subscribed_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')]);
        logActivity($db, 'added_subscriber', 'subscribers', $admin['id'], "Added subscriber: {$email}");
        adminSuccess([], 'Subscriber added successfully.');
        break;

    case 'delete_subscriber':
        $subId = intval($_POST['id'] ?? 0);
        if (!$subId) adminError('Invalid subscriber ID.');
        $db->delete('newsletter_subscribers', 'id = ?', [$subId]);
        logActivity($db, 'deleted_subscriber', 'subscribers', $admin['id'], "Deleted subscriber #{$subId}");
        adminSuccess([], 'Subscriber removed.');
        break;

    // ═══ ANNOUNCEMENTS ═══
    case 'add_announcement':
    case 'edit_announcement':
        $data = adminInput();
        requiredFields($data, ['title', 'content']);
        $annData = [
            'title'      => trim($data['title']),
            'content'    => trim($data['content']),
            'priority'   => trim($data['priority'] ?? 'normal'),
            'start_date' => $data['start_date'] ?? date('Y-m-d'),
            'end_date'   => $data['end_date'] ?? null,
            'is_active'  => intval($data['is_active'] ?? 1),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($action === 'edit_announcement') {
            $annId = intval($data['id'] ?? 0);
            if (!$annId) adminError('Invalid announcement ID.');
            $db->update('announcements', $annData, 'id = ?', [$annId]);
            logActivity($db, 'updated_announcement', 'announcements', $admin['id'], "Updated announcement #{$annId}");
            adminSuccess([], 'Announcement updated successfully.');
        } else {
            $annData['created_by'] = $admin['id'];
            $annData['created_at'] = date('Y-m-d H:i:s');
            $id = $db->insert('announcements', $annData);
            logActivity($db, 'added_announcement', 'announcements', $admin['id'], "Added announcement #{$id}");
            adminSuccess(['id' => $id], 'Announcement created successfully.');
        }
        break;

    case 'delete_announcement':
        $annId = intval($_POST['id'] ?? 0);
        if (!$annId) adminError('Invalid announcement ID.');
        $db->delete('announcements', 'id = ?', [$annId]);
        logActivity($db, 'deleted_announcement', 'announcements', $admin['id'], "Deleted announcement #{$annId}");
        adminSuccess([], 'Announcement deleted.');
        break;

    // ═══ PASTOR BOOKINGS ═══
    case 'confirm_booking':
        $bookingId = intval($_POST['id'] ?? 0);
        if (!$bookingId) adminError('Invalid booking ID.');
        $db->update('pastor_bookings', ['status' => 'confirmed', 'confirmed_by' => $admin['id'], 'confirmed_at' => date('Y-m-d H:i:s')], 'id = ?', [$bookingId]);
        logActivity($db, 'confirmed_booking', 'pastor_bookings', $admin['id'], "Confirmed booking #{$bookingId}");
        adminSuccess([], 'Booking confirmed.');
        break;

    case 'cancel_booking':
        $bookingId = intval($_POST['id'] ?? 0);
        if (!$bookingId) adminError('Invalid booking ID.');
        $reason = trim($_POST['reason'] ?? '');
        $db->update('pastor_bookings', ['status' => 'cancelled', 'cancel_reason' => $reason, 'cancelled_by' => $admin['id'], 'cancelled_at' => date('Y-m-d H:i:s')], 'id = ?', [$bookingId]);
        logActivity($db, 'cancelled_booking', 'pastor_bookings', $admin['id'], "Cancelled booking #{$bookingId}");
        adminSuccess([], 'Booking cancelled.');
        break;

    // ═══ PROPHETIC SCHOOL APPLICATIONS ═══
    case 'approve_application':
        $appId = intval($_POST['id'] ?? 0);
        if (!$appId) adminError('Invalid application ID.');
        $db->update('prophetic_school_applications', ['status' => 'approved', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$appId]);
        logActivity($db, 'approved_application', 'prophetic_school', $admin['id'], "Approved application #{$appId}");
        adminSuccess([], 'Application approved.');
        break;

    case 'reject_application':
        $appId = intval($_POST['id'] ?? 0);
        if (!$appId) adminError('Invalid application ID.');
        $db->update('prophetic_school_applications', ['status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$appId]);
        logActivity($db, 'rejected_application', 'prophetic_school', $admin['id'], "Rejected application #{$appId}");
        adminSuccess([], 'Application rejected.');
        break;

    case 'delete_application':
        $appId = intval($_POST['id'] ?? 0);
        if (!$appId) adminError('Invalid application ID.');
        $db->delete('prophetic_school_applications', 'id = ?', [$appId]);
        logActivity($db, 'deleted_application', 'prophetic_school', $admin['id'], "Deleted application #{$appId}");
        adminSuccess([], 'Application deleted.');
        break;

    // ═══ ADMIN PROFILE ═══
    case 'update_admin_profile':
        $data = adminInput();
        $profileData = [
            'full_name'  => trim($data['full_name'] ?? ''),
            'email'      => trim($data['email'] ?? ''),
            'phone'      => trim($data['phone'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (empty($profileData['full_name']) || empty($profileData['email'])) adminError('Name and email are required.');
        if (!validateEmail($profileData['email'])) adminError('Please provide a valid email address.');
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatar = uploadFile($_FILES['avatar'], 'avatars', ALLOWED_IMAGE_TYPES);
            if ($avatar) $profileData['avatar_url'] = $avatar;
        }
        $dbAdmin->update('admin_users', $profileData, 'id = ?', [$admin['id']]);
        $_SESSION['admin_name']  = $profileData['full_name'];
        $_SESSION['admin_email'] = $profileData['email'];
        logActivity($db, 'updated_profile', 'profile', $admin['id'], 'Updated admin profile');
        adminSuccess([], 'Profile updated successfully.');
        break;

    case 'change_admin_password':
        $data = adminInput();
        $oldPass     = $data['current_password'] ?? '';
        $newPass     = $data['new_password'] ?? '';
        $confirmPass = $data['confirm_password'] ?? '';
        if (empty($oldPass) || empty($newPass)) adminError('All password fields are required.');
        if ($newPass !== $confirmPass) adminError('New passwords do not match.');
        if (strlen($newPass) < 8) adminError('Password must be at least 8 characters.');
        $result = auth()->changePassword($admin['id'], $oldPass, $newPass);
        if (!$result['success']) adminError($result['message']);
        logActivity($db, 'changed_password', 'profile', $admin['id'], 'Changed admin password');
        adminSuccess([], 'Password changed successfully.');
        break;

    // ═══ BACKUPS ═══
    case 'create_backup':
        $backupDir = UPLOADS_PATH . '/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;
        $tables = $db->fetchAll("SHOW TABLES");
        $sql = "-- Salem Dominion Ministries Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        foreach ($tables as $row) {
            $tableName = $row[0] ?? reset($row);
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $createTable = $db->fetch("SHOW CREATE TABLE `{$tableName}`");
            if ($createTable) {
                $sql .= ($createTable['Create Table'] ?? '') . ";\n\n";
            }
            $rows = $db->fetchAll("SELECT * FROM `{$tableName}`");
            foreach ($rows as $row) {
                $values = array_map(function($v) {
                    return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
                }, array_values($row));
                $columns = array_map(function($c) { return "`{$c}`"; }, array_keys($row));
                $sql .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }
        file_put_contents($filepath, $sql);
        $size = filesize($filepath);
        logActivity($db, 'created_backup', 'backups', $admin['id'], "Created backup: {$filename}");
        adminSuccess(['filename' => $filename, 'size' => $size, 'path' => 'uploads/backups/' . $filename], 'Backup created successfully.');
        break;

    case 'download_backup':
        $filename = trim($_POST['filename'] ?? '');
        if (empty($filename) || strpos($filename, '..') !== false || strpos($filename, '/') !== false) adminError('Invalid backup filename.');
        $filepath = UPLOADS_PATH . '/backups/' . $filename;
        if (!file_exists($filepath)) adminError('Backup file not found.');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;

    // ═══ DEFAULT ═══
    default:
        adminError('Unknown action: ' . $action, 404);
        break;
}
