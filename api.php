<?php
/**
 * Salem Dominion Ministries - AJAX API Handler
 * Clean, routed API with CSRF protection and PDO prepared statements
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function apiSuccess(array $data = [], string $message = 'Success'): void {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}

function apiError(string $message, int $code = 400): void {
    jsonResponse(['success' => false, 'message' => $message], $code);
}

function apiInput(): array {
    $input = json_decode(file_get_contents('php://input'), true);
    if (is_array($input)) {
        return $input;
    }
    return [];
}

function apiCsrfVerify(): bool {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }
    return verifyCSRFToken();
}

function requireCsrf(): void {
    if (!apiCsrfVerify()) {
        apiError('Invalid security token. Please refresh and try again.', 403);
    }
}

function requireAuth(): array {
    if (empty($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        apiError('Please log in to continue.', 401);
    }
    $user = Database::getNamed('members')->fetch(
        "SELECT id, first_name, last_name, email, phone, role FROM users WHERE id = ? AND is_active = 1",
        [$_SESSION['user_id']]
    );
    if (!$user) {
        session_destroy();
        apiError('Session expired. Please log in again.', 401);
    }
    return $user;
}

$db = Database::getInstance();        // Website DB (content tables)
$dbMembers = Database::getNamed('members'); // Members DB (users)

switch ($action) {

    // ──────────────────────────────────────────
    // NEWSLETTER SUBSCRIBE
    // ──────────────────────────────────────────
    case 'newsletter_subscribe':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }

        $existing = $db->fetch("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$email]);
        if ($existing) {
            if (!$existing['is_active']) {
                $db->update('newsletter_subscribers', ['is_active' => 1, 'unsubscribed_at' => null], 'id = ?', [$existing['id']]);
                apiSuccess([], 'Welcome back! You have been resubscribed.');
            }
            apiSuccess([], 'You are already subscribed. Thank you!');
        }

        $db->insert('newsletter_subscribers', [
            'email'        => $email,
            'is_active'    => 1,
            'status'       => 'active',
            'subscribed_at' => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        apiSuccess([], 'Thank you for subscribing! God bless you.');
        break;

    // ──────────────────────────────────────────
    // NEWSLETTER UNSUBSCRIBE
    // ──────────────────────────────────────────
    case 'newsletter_unsubscribe':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $email = trim($_POST['email'] ?? '');
        if (empty($email) || !validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }

        $subscriber = $db->fetch("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$email]);
        if (!$subscriber || !$subscriber['is_active']) {
            apiError('Email not found or already unsubscribed.');
        }

        $db->update('newsletter_subscribers', [
            'is_active'       => 0,
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$subscriber['id']]);

        apiSuccess([], 'You have been unsubscribed successfully.');
        break;

    // ──────────────────────────────────────────
    // CONTACT SUBMIT
    // ──────────────────────────────────────────
    case 'contact_submit':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data    = apiInput();
        $name    = trim($data['name'] ?? $_POST['name'] ?? '');
        $email   = trim($data['email'] ?? $_POST['email'] ?? '');
        $phone   = trim($data['phone'] ?? $_POST['phone'] ?? '');
        $subject = trim($data['subject'] ?? $_POST['subject'] ?? '');
        $message = trim($data['message'] ?? $_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            apiError('Name, email, and message are required.');
        }
        if (!validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }
        if (strlen($name) > 200) {
            apiError('Name is too long.');
        }
        if (strlen($message) > 5000) {
            apiError('Message is too long. Please keep it under 5000 characters.');
        }

        $db->insert('contact_messages', [
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'subject'    => $subject ?: 'Contact Form Submission',
            'message'    => $message,
            'status'     => 'unread',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        apiSuccess([], 'Your message has been sent successfully. We will get back to you soon!');
        break;

    // ──────────────────────────────────────────
    // PRAYER REQUEST SUBMIT
    // ──────────────────────────────────────────
    case 'prayer_submit':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data         = apiInput();
        $name         = trim($data['name'] ?? $_POST['name'] ?? '');
        $email        = trim($data['email'] ?? $_POST['email'] ?? '');
        $phone        = trim($data['phone'] ?? $_POST['phone'] ?? '');
        $request_text = trim($data['request_text'] ?? $_POST['request_text'] ?? '');
        $is_urgent    = isset($data['is_urgent']) ? intval($data['is_urgent']) : intval($_POST['is_urgent'] ?? 0);
        $is_anonymous = isset($data['is_anonymous']) ? intval($data['is_anonymous']) : intval($_POST['is_anonymous'] ?? 0);

        if (empty($request_text)) {
            apiError('Please enter your prayer request.');
        }
        if (!$is_anonymous && empty($name)) {
            apiError('Please provide your name, or submit anonymously.');
        }
        if (!empty($email) && !validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }
        if (strlen($request_text) > 5000) {
            apiError('Prayer request is too long. Please keep it under 5000 characters.');
        }

        $db->insert('prayer_requests', [
            'name'         => $is_anonymous ? 'Anonymous' : $name,
            'email'        => $email,
            'phone'        => $phone,
            'request_text' => $request_text,
            'is_urgent'    => $is_urgent ? 1 : 0,
            'is_anonymous' => $is_anonymous ? 1 : 0,
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        apiSuccess([], 'Your prayer request has been submitted. We are standing with you in prayer!');
        break;

    // ──────────────────────────────────────────
    // TESTIMONIAL SUBMIT
    // ──────────────────────────────────────────
    case 'testimonial_submit':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data        = apiInput();
        $name        = trim($data['name'] ?? $_POST['name'] ?? '');
        $email       = trim($data['email'] ?? $_POST['email'] ?? '');
        $occupation  = trim($data['occupation'] ?? $_POST['occupation'] ?? '');
        $testimonial = trim($data['testimonial'] ?? $_POST['testimonial'] ?? '');
        $rating      = intval($data['rating'] ?? $_POST['rating'] ?? 5);

        if (empty($name) || empty($testimonial)) {
            apiError('Name and testimonial are required.');
        }
        if (!empty($email) && !validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }
        if (strlen($testimonial) > 2000) {
            apiError('Testimonial is too long. Please keep it under 2000 characters.');
        }

        $db->insert('testimonials', [
            'name'        => $name,
            'email'       => $email,
            'occupation'  => $occupation,
            'testimonial' => $testimonial,
            'rating'      => $rating,
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        apiSuccess([], 'Thank you for sharing your testimony! It will be published after review.');
        break;

    // ──────────────────────────────────────────
    // DONATION SUBMIT
    // ──────────────────────────────────────────
    case 'donation_submit':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data           = apiInput();
        $donor_name     = trim($data['donor_name'] ?? $_POST['donor_name'] ?? '');
        $donor_email    = trim($data['donor_email'] ?? $_POST['donor_email'] ?? '');
        $donor_phone    = trim($data['donor_phone'] ?? $_POST['donor_phone'] ?? '');
        $amount         = floatval($data['amount'] ?? $_POST['amount'] ?? 0);
        $donation_type  = trim($data['donation_type'] ?? $_POST['donation_type'] ?? 'general');
        $payment_method = trim($data['payment_method'] ?? $_POST['payment_method'] ?? 'cash');
        $notes          = trim($data['notes'] ?? $data['note'] ?? $_POST['notes'] ?? $_POST['note'] ?? '');

        $valid_payment_methods = ['mobile_money', 'bank_transfer', 'cash', 'online', 'card'];
        if (!in_array($payment_method, $valid_payment_methods)) {
            $payment_method = 'cash';
        }

        if (empty($donor_name) || $amount <= 0) {
            apiError('Donor name and a valid donation amount are required.');
        }
        if (!empty($donor_email) && !validateEmail($donor_email)) {
            apiError('Please provide a valid email address.');
        }
        if ($amount > 1000000000) {
            apiError('Donation amount is too large.');
        }

        $reference = 'DON-' . strtoupper(bin2hex(random_bytes(6))) . '-' . date('ymd');

        $donationId = $db->insert('donations', [
            'donor_name'     => $donor_name,
            'donor_email'    => $donor_email,
            'donor_phone'    => $donor_phone,
            'amount'         => $amount,
            'donation_type'  => $donation_type,
            'payment_method' => $payment_method,
            'transaction_id' => $reference,
            'status'         => 'pending',
            'notes'          => $notes,
            'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        apiSuccess([
            'donation_id' => $donationId,
            'reference'   => $reference,
        ], 'Thank you for your generous donation! Reference: ' . $reference);
        break;

    // ──────────────────────────────────────────
    // BOOK PASTOR
    // ──────────────────────────────────────────
    case 'book_pastor':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data           = apiInput();
        $client_name    = trim($data['name'] ?? $data['client_name'] ?? $_POST['name'] ?? '');
        $client_email   = trim($data['email'] ?? $data['client_email'] ?? $_POST['email'] ?? '');
        $client_phone   = trim($data['phone'] ?? $data['client_phone'] ?? $_POST['phone'] ?? '');
        $booking_date   = $data['booking_date'] ?? $_POST['booking_date'] ?? '';
        $start_time     = $data['start_time'] ?? $_POST['start_time'] ?? '';
        $booking_type   = trim($data['booking_type'] ?? $_POST['booking_type'] ?? 'counseling');
        $subject        = trim($data['subject'] ?? $_POST['subject'] ?? '');
        $description    = trim($data['description'] ?? $_POST['description'] ?? '');

        if (empty($client_name) || empty($client_email) || empty($booking_date) || empty($start_time)) {
            apiError('Name, email, booking date, and time are required.');
        }
        if (!validateEmail($client_email)) {
            apiError('Please provide a valid email address.');
        }
        if (!empty($client_phone) && !validatePhone($client_phone)) {
            apiError('Please provide a valid phone number.');
        }
        if (strtotime($booking_date) === false) {
            apiError('Please provide a valid booking date.');
        }
        if (strtotime($booking_date) < strtotime('today')) {
            apiError('Booking date cannot be in the past.');
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $start_time)) {
            apiError('Please provide a valid time (HH:MM).');
        }

        $pastor = $db->fetch("SELECT id FROM leadership WHERE title LIKE '%Pastor%' AND is_active = 1 LIMIT 1");
        $pastorId = $pastor ? $pastor['id'] : 1;
        $duration = intval($data['duration_minutes'] ?? 30);
        $end_hour = intval(substr($start_time, 0, 2)) + intval($duration / 60);
        $end_min  = intval(substr($start_time, 3, 2)) + ($duration % 60);
        if ($end_min >= 60) { $end_hour++; $end_min -= 60; }
        $end_time = str_pad($end_hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($end_min, 2, '0', STR_PAD_LEFT);

        $conflict = $db->fetch(
            "SELECT id FROM pastor_bookings WHERE booking_date = ? AND start_time = ? AND status NOT IN ('cancelled','rejected')",
            [$booking_date, $start_time]
        );
        if ($conflict) {
            apiError('This time slot is already booked. Please choose a different time.');
        }

        $confCode = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $db->insert('pastor_bookings', [
            'pastor_id'         => $pastorId,
            'client_name'       => $client_name,
            'client_email'      => $client_email,
            'client_phone'      => $client_phone,
            'booking_date'      => $booking_date,
            'start_time'        => $start_time,
            'end_time'          => $end_time,
            'duration_minutes'  => $duration,
            'booking_type'      => $booking_type,
            'subject'           => $subject,
            'description'       => $description,
            'status'            => 'pending',
            'confirmation_code' => $confCode,
            'ip_address'        => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        apiSuccess(['confirmation_code' => $confCode], 'Your booking has been submitted successfully. Confirmation code: ' . $confCode);
        break;

    // ──────────────────────────────────────────
    // PROPHETIC SCHOOL APPLICATION
    // ──────────────────────────────────────────
    case 'prophetic_apply':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data    = apiInput();
        $name    = trim($data['name'] ?? $_POST['name'] ?? '');
        $email   = trim($data['email'] ?? $_POST['email'] ?? '');
        $phone   = trim($data['phone'] ?? $_POST['phone'] ?? '');
        $program = trim($data['program'] ?? $_POST['program'] ?? '');
        $message = trim($data['message'] ?? $_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($program)) {
            apiError('Name, email, and program selection are required.');
        }
        if (!validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }

        $existing = $db->fetch(
            "SELECT id FROM prophetic_school_applications WHERE email = ? AND program = ?",
            [$email, $program]
        );
        if ($existing) {
            apiError('You have already applied for this program.');
        }

        $db->insert('prophetic_school_applications', [
            'name'       => $name,
            'email'      => $email,
            'phone'      => $phone,
            'program'    => $program,
            'message'    => $message,
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        apiSuccess([], 'Your application has been submitted successfully! We will contact you soon.');
        break;

    // ──────────────────────────────────────────
    // GET LIVE STATUS
    // ──────────────────────────────────────────
    case 'get_live_status':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $live = $db->fetch(
            "SELECT is_live, youtube_url, title FROM youtube_live WHERE is_enabled = 1 ORDER BY id DESC LIMIT 1"
        );

        apiSuccess([
            'data' => $live ? [
                'is_live'     => (bool)$live['is_live'],
                'youtube_url' => $live['youtube_url'],
                'title'       => $live['title'],
            ] : [
                'is_live'     => false,
                'youtube_url' => '',
                'title'       => '',
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // GET SERMONS
    // ──────────────────────────────────────────
    case 'get_sermons':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $category = $_GET['category'] ?? '';
        $search   = $_GET['search'] ?? '';
        $page     = max(1, intval($_GET['page'] ?? 1));
        $limit    = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
        $offset   = ($page - 1) * $limit;

        $where  = "status = 'published'";
        $params = [];

        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        if (!empty($search)) {
            $where .= " AND (title LIKE ? OR description LIKE ? OR series LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $total = $db->count('sermons', $where, $params);
        $totalPages = max(1, ceil($total / $limit));

        $params[] = $limit;
        $params[] = $offset;
        $sermons = $db->fetchAll(
            "SELECT id, title, sermon_date, series, category, duration, description, media_type, media_url, views, created_at FROM sermons WHERE {$where} ORDER BY sermon_date DESC LIMIT ? OFFSET ?",
            $params
        );

        apiSuccess([
            'data' => $sermons,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // GET EVENTS
    // ──────────────────────────────────────────
    case 'get_events':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $status = $_GET['status'] ?? '';
        $page   = max(1, intval($_GET['page'] ?? 1));
        $limit  = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
        $offset = ($page - 1) * $limit;

        $where  = "1=1";
        $params = [];

        if (!empty($status)) {
            $where .= " AND status = ?";
            $params[] = $status;
        } else {
            $where .= " AND (status = 'upcoming' OR status = 'ongoing')";
        }

        $total = $db->count('events', $where, $params);
        $totalPages = max(1, ceil($total / $limit));

        $params[] = $limit;
        $params[] = $offset;
        $events = $db->fetchAll(
            "SELECT id, title, description, event_date, end_date, event_time, location, category, status, banner_image, is_recurring, created_at FROM events WHERE {$where} ORDER BY event_date ASC LIMIT ? OFFSET ?",
            $params
        );

        apiSuccess([
            'data' => $events,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // GET NEWS
    // ──────────────────────────────────────────
    case 'get_news':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $category = $_GET['category'] ?? '';
        $search   = $_GET['search'] ?? '';
        $page     = max(1, intval($_GET['page'] ?? 1));
        $limit    = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
        $offset   = ($page - 1) * $limit;

        $where  = "status = 'published'";
        $params = [];

        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        if (!empty($search)) {
            $where .= " AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $total = $db->count('news', $where, $params);
        $totalPages = max(1, ceil($total / $limit));

        $params[] = $limit;
        $params[] = $offset;
        $articles = $db->fetchAll(
            "SELECT id, title, category, excerpt, content, featured_image, views, created_at FROM news WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );

        apiSuccess([
            'data' => $articles,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // GET GALLERY
    // ──────────────────────────────────────────
    case 'get_gallery':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $album    = $_GET['album'] ?? '';
        $category = $_GET['category'] ?? '';
        $page     = max(1, intval($_GET['page'] ?? 1));
        $limit    = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
        $offset   = ($page - 1) * $limit;

        $where  = "1=1";
        $params = [];

        if (!empty($album)) {
            $where .= " AND album = ?";
            $params[] = $album;
        }
        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }

        $total = $db->count('gallery', $where, $params);
        $totalPages = max(1, ceil($total / $limit));

        $params[] = $limit;
        $params[] = $offset;
        $items = $db->fetchAll(
            "SELECT id, title, file_url, file_type, album, category, description, created_at FROM gallery WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            $params
        );

        apiSuccess([
            'data' => $items,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // SEARCH (across sermons, events, news)
    // ──────────────────────────────────────────
    case 'search':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $query = trim($_GET['query'] ?? '');
        if (empty($query)) {
            apiError('Please provide a search query.');
        }

        $term = "%{$query}%";

        $sermons = $db->fetchAll(
            "SELECT id, title, sermon_date, category, 'sermon' AS type FROM sermons WHERE status = 'published' AND (title LIKE ? OR description LIKE ?) ORDER BY sermon_date DESC LIMIT 10",
            [$term, $term]
        );

        $events = $db->fetchAll(
            "SELECT id, title, event_date, category, 'event' AS type FROM events WHERE (status = 'upcoming' OR status = 'ongoing') AND (title LIKE ? OR description LIKE ?) ORDER BY event_date ASC LIMIT 10",
            [$term, $term]
        );

        $news = $db->fetchAll(
            "SELECT id, title, created_at, category, 'news' AS type FROM news WHERE status = 'published' AND (title LIKE ? OR content LIKE ?) ORDER BY created_at DESC LIMIT 10",
            [$term, $term]
        );

        apiSuccess([
            'data' => [
                'sermons' => $sermons,
                'events'  => $events,
                'news'    => $news,
            ],
            'total' => count($sermons) + count($events) + count($news),
        ]);
        break;

    // ──────────────────────────────────────────
    // GET TESTIMONIALS (approved only)
    // ──────────────────────────────────────────
    case 'get_testimonials':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $page  = max(1, intval($_GET['page'] ?? 1));
        $limit = intval($_GET['limit'] ?? ITEMS_PER_PAGE);
        $offset = ($page - 1) * $limit;

        $total = $db->count('testimonials', "status = 'approved'");
        $totalPages = max(1, ceil($total / $limit));

        $testimonials = $db->fetchAll(
            "SELECT id, name, occupation, testimonial, rating, created_at FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        apiSuccess([
            'data' => $testimonials,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // GET BIBLE VERSE
    // ──────────────────────────────────────────
    case 'get_bible_verse':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $verse = $db->fetch("SELECT id, verse_text, reference FROM bible_verses ORDER BY RAND() LIMIT 1");

        if (!$verse) {
            $verse = [
                'verse_text' => 'For I know the plans I have for you, declares the Lord, plans to prosper you and not to harm you, plans to give you hope and a future.',
                'reference'  => 'Jeremiah 29:11',
            ];
        }

        apiSuccess(['data' => $verse]);
        break;

    // ──────────────────────────────────────────
    // USER LOGIN
    // ──────────────────────────────────────────
    case 'login':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data     = apiInput();
        $email    = trim($data['email'] ?? $_POST['email'] ?? '');
        $password = $data['password'] ?? $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            apiError('Email and password are required.');
        }
        if (!validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }

        $user = $dbMembers->fetch(
            "SELECT id, first_name, last_name, email, phone, role, password, is_active FROM users WHERE email = ?",
            [$email]
        );

        if (!$user) {
            apiError('Invalid email or password.');
        }
        if (!$user['is_active']) {
            apiError('Your account has been deactivated. Please contact the administrator.');
        }
        if (!password_verify($password, $user['password'])) {
            apiError('Invalid email or password.');
        }

        $dbMembers->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        session_regenerate_id(true);
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_name']      = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['user_role']      = $user['role'];
        $_SESSION['user_login_time'] = time();

        unset($user['password']);

        apiSuccess(['user' => $user], 'Login successful. Welcome back!');
        break;

    // ──────────────────────────────────────────
    // USER REGISTER
    // ──────────────────────────────────────────
    case 'register':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data            = apiInput();
        $first_name      = trim($data['first_name'] ?? $_POST['first_name'] ?? '');
        $last_name       = trim($data['last_name'] ?? $_POST['last_name'] ?? '');
        $email           = trim($data['email'] ?? $_POST['email'] ?? '');
        $phone           = trim($data['phone'] ?? $_POST['phone'] ?? '');
        $password        = $data['password'] ?? $_POST['password'] ?? '';
        $confirm_password = $data['confirm_password'] ?? $_POST['confirm_password'] ?? '';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            apiError('First name, last name, email, and password are required.');
        }
        if (!validateEmail($email)) {
            apiError('Please provide a valid email address.');
        }
        if (strlen($password) < 8) {
            apiError('Password must be at least 8 characters long.');
        }
        if ($password !== $confirm_password) {
            apiError('Passwords do not match.');
        }
        if (!empty($phone) && !validatePhone($phone)) {
            apiError('Please provide a valid phone number.');
        }

        $existing = $dbMembers->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            apiError('An account with this email already exists.');
        }

        $userId = $dbMembers->insert('users', [
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'phone'         => $phone,
            'password'      => password_hash($password, HASH_ALGO, ['cost' => HASH_COST]),
            'role'          => 'member',
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'last_login'    => date('Y-m-d H:i:s'),
        ]);

        session_regenerate_id(true);
        $_SESSION['user_logged_in']  = true;
        $_SESSION['user_id']         = $userId;
        $_SESSION['user_name']       = $first_name . ' ' . $last_name;
        $_SESSION['user_email']      = $email;
        $_SESSION['user_role']       = 'member';
        $_SESSION['user_login_time'] = time();

        apiSuccess([
            'user' => [
                'id'         => $userId,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email,
                'phone'      => $phone,
                'role'       => 'member',
            ],
        ], 'Registration successful! Welcome to Salem Dominion Ministries.');
        break;

    // ──────────────────────────────────────────
    // USER LOGOUT
    // ──────────────────────────────────────────
    case 'logout':
        if ($method !== 'POST') apiError('Method not allowed', 405);

        $userId = $_SESSION['user_id'] ?? 0;
        if ($userId) {
            logActivity($db, 'logout', 'auth', $userId, 'User logged out');
        }

        session_destroy();
        apiSuccess([], 'Logged out successfully.');
        break;

    // ──────────────────────────────────────────
    // GET USER PROFILE
    // ──────────────────────────────────────────
    case 'get_profile':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $user = requireAuth();

        apiSuccess(['user' => $user]);
        break;

    // ──────────────────────────────────────────
    // UPDATE PROFILE
    // ──────────────────────────────────────────
    case 'update_profile':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();
        $currentUser = requireAuth();

        $data       = apiInput();
        $first_name = trim($data['first_name'] ?? '');
        $last_name  = trim($data['last_name'] ?? '');
        $phone      = trim($data['phone'] ?? '');

        if (empty($first_name) || empty($last_name)) {
            apiError('First name and last name are required.');
        }

        $dbMembers->update('users', [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'phone'      => $phone,
        ], 'id = ?', [$currentUser['id']]);

        $_SESSION['user_name'] = $first_name . ' ' . $last_name;

        apiSuccess([], 'Profile updated successfully.');
        break;

    // ──────────────────────────────────────────
    // ADD COMMENT
    // ──────────────────────────────────────────
    case 'add_comment':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $user = null;
        if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $user = $dbMembers->fetch(
                "SELECT id, first_name, last_name, email FROM users WHERE id = ? AND is_active = 1",
                [$_SESSION['user_id']]
            );
        }

        $data         = apiInput();
        $content_type = $data['content_type'] ?? $_POST['content_type'] ?? '';
        $content_id   = intval($data['content_id'] ?? $_POST['content_id'] ?? 0);
        $comment_text = trim($data['comment'] ?? $_POST['comment'] ?? '');
        $parent_id    = intval($data['parent_id'] ?? $_POST['parent_id'] ?? 0);

        if (!in_array($content_type, ['sermon', 'news', 'event', 'gallery'])) {
            apiError('Invalid content type.');
        }
        if ($content_id <= 0) {
            apiError('Invalid content ID.');
        }
        if (empty($comment_text)) {
            apiError('Please write a comment.');
        }
        if (strlen($comment_text) > 2000) {
            apiError('Comment is too long. Maximum 2000 characters.');
        }

        $commentId = $db->insert('comments', [
            'content_type' => $content_type,
            'content_id'   => $content_id,
            'user_id'      => $user ? $user['id'] : null,
            'user_name'    => $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Guest',
            'user_email'   => $user ? $user['email'] : null,
            'comment'      => $comment_text,
            'status'       => 'approved',
            'parent_id'    => $parent_id > 0 ? $parent_id : null,
            'ip_address'   => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $saved = $db->fetch("SELECT id, content_type, content_id, user_name, comment, status, parent_id, created_at FROM comments WHERE id = ?", [$commentId]);

        apiSuccess(['comment' => $saved], 'Comment posted successfully!');
        break;

    // ──────────────────────────────────────────
    // GET COMMENTS
    // ──────────────────────────────────────────
    case 'get_comments':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $content_type = $_GET['content_type'] ?? '';
        $content_id   = intval($_GET['content_id'] ?? 0);
        $page         = max(1, intval($_GET['page'] ?? 1));
        $limit        = min(50, max(1, intval($_GET['limit'] ?? 20)));
        $offset       = ($page - 1) * $limit;

        if (!in_array($content_type, ['sermon', 'news', 'event', 'gallery'])) {
            apiError('Invalid content type.');
        }
        if ($content_id <= 0) {
            apiError('Invalid content ID.');
        }

        $total = $db->count('comments', "content_type = ? AND content_id = ? AND status = 'approved' AND (parent_id IS NULL OR parent_id = 0)", [$content_type, $content_id]);
        $totalPages = max(1, ceil($total / $limit));

        $comments = $db->fetchAll(
            "SELECT id, user_name, comment, parent_id, created_at FROM comments WHERE content_type = ? AND content_id = ? AND status = 'approved' AND (parent_id IS NULL OR parent_id = 0) ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$content_type, $content_id, $limit, $offset]
        );

        $totalAll = $db->count('comments', "content_type = ? AND content_id = ? AND status = 'approved'", [$content_type, $content_id]);

        apiSuccess([
            'data' => $comments,
            'total' => $totalAll,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $limit,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
        ]);
        break;

    // ──────────────────────────────────────────
    // TOGGLE LIKE
    // ──────────────────────────────────────────
    case 'toggle_like':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data         = apiInput();
        $content_type = $data['content_type'] ?? $_POST['content_type'] ?? '';
        $content_id   = intval($data['content_id'] ?? $_POST['content_id'] ?? 0);
        $visitor_hash = $data['visitor_hash'] ?? $_POST['visitor_hash'] ?? '';

        if (!in_array($content_type, ['sermon', 'news', 'event', 'gallery'])) {
            apiError('Invalid content type.');
        }
        if ($content_id <= 0) {
            apiError('Invalid content ID.');
        }

        $user_id = null;
        if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $user_id = $_SESSION['user_id'] ?? null;
        }

        if (!$user_id && empty($visitor_hash)) {
            apiError('Unable to process like. Please try again.');
        }

        $existing = null;
        if ($user_id) {
            $existing = $db->fetch(
                "SELECT id FROM likes WHERE content_type = ? AND content_id = ? AND user_id = ?",
                [$content_type, $content_id, $user_id]
            );
        } elseif ($visitor_hash) {
            $visitor_hash = hash('sha256', $visitor_hash);
            $existing = $db->fetch(
                "SELECT id FROM likes WHERE content_type = ? AND content_id = ? AND visitor_hash = ?",
                [$content_type, $content_id, $visitor_hash]
            );
        }

        if ($existing) {
            $db->delete('likes', 'id = ?', [$existing['id']]);
            $liked = false;
        } else {
            $db->insert('likes', [
                'content_type' => $content_type,
                'content_id'   => $content_id,
                'user_id'      => $user_id,
                'visitor_hash' => $user_id ? null : ($visitor_hash ? hash('sha256', $visitor_hash) : null),
            ]);
            $liked = true;
        }

        $likeCount = $db->count('likes', "content_type = ? AND content_id = ?", [$content_type, $content_id]);

        apiSuccess([
            'liked'  => $liked,
            'count'  => $likeCount,
        ]);
        break;

    // ──────────────────────────────────────────
    // GET INTERACTION COUNTS
    // ──────────────────────────────────────────
    case 'get_counts':
        if ($method !== 'GET') apiError('Method not allowed', 405);

        $content_type = $_GET['content_type'] ?? '';
        $content_id   = intval($_GET['content_id'] ?? 0);

        if (!in_array($content_type, ['sermon', 'news', 'event', 'gallery'])) {
            apiError('Invalid content type.');
        }
        if ($content_id <= 0) {
            apiError('Invalid content ID.');
        }

        $likeCount   = $db->count('likes', "content_type = ? AND content_id = ?", [$content_type, $content_id]);
        $commentCount = $db->count('comments', "content_type = ? AND content_id = ? AND status = 'approved'", [$content_type, $content_id]);
        $shareCount  = $db->count('shares', "content_type = ? AND content_id = ?", [$content_type, $content_id]);

        $userLiked = false;
        if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $liked = $db->fetch(
                "SELECT id FROM likes WHERE content_type = ? AND content_id = ? AND user_id = ?",
                [$content_type, $content_id, $_SESSION['user_id']]
            );
            $userLiked = (bool)$liked;
        }

        apiSuccess([
            'likes'   => $likeCount,
            'comments' => $commentCount,
            'shares'  => $shareCount,
            'user_liked' => $userLiked,
        ]);
        break;

    // ──────────────────────────────────────────
    // RECORD SHARE
    // ──────────────────────────────────────────
    case 'record_share':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $data           = apiInput();
        $content_type   = $data['content_type'] ?? $_POST['content_type'] ?? '';
        $content_id     = intval($data['content_id'] ?? $_POST['content_id'] ?? 0);
        $share_platform = trim($data['platform'] ?? $_POST['platform'] ?? 'link');

        if (!in_array($content_type, ['sermon', 'news', 'event', 'gallery'])) {
            apiError('Invalid content type.');
        }
        if ($content_id <= 0) {
            apiError('Invalid content ID.');
        }

        $user_id = null;
        if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $user_id = $_SESSION['user_id'] ?? null;
        }

        $db->insert('shares', [
            'content_type'   => $content_type,
            'content_id'     => $content_id,
            'user_id'        => $user_id,
            'share_platform' => $share_platform,
            'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $shareCount = $db->count('shares', "content_type = ? AND content_id = ?", [$content_type, $content_id]);

        apiSuccess(['count' => $shareCount], 'Share recorded!');
        break;

    // ──────────────────────────────────────────
    // DELETE COMMENT (own comment only)
    // ──────────────────────────────────────────
    case 'delete_comment':
        if ($method !== 'POST') apiError('Method not allowed', 405);
        requireCsrf();

        $user = null;
        if (!empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $user = $_SESSION['user_id'];
        }
        if (!$user) {
            apiError('Please log in to delete comments.', 401);
        }

        $data        = apiInput();
        $comment_id  = intval($data['comment_id'] ?? $_POST['comment_id'] ?? 0);

        if ($comment_id <= 0) {
            apiError('Invalid comment ID.');
        }

        $comment = $db->fetch("SELECT id, user_id FROM comments WHERE id = ?", [$comment_id]);
        if (!$comment) {
            apiError('Comment not found.');
        }
        if ($comment['user_id'] != $user) {
            apiError('You can only delete your own comments.', 403);
        }

        $db->update('comments', ['status' => 'deleted'], 'id = ?', [$comment_id]);

        apiSuccess([], 'Comment deleted.');
        break;

    // ──────────────────────────────────────────
    // DEFAULT
    // ──────────────────────────────────────────
    default:
        apiError('Invalid or unsupported action.', 404);
        break;
}
