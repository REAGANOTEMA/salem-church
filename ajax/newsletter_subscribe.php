<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid email is required.']);
    exit;
}

$db = Database::getInstance();

$existing = $db->fetch("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$email]);

if ($existing) {
    if ($existing['is_active']) {
        echo json_encode(['success' => true, 'message' => 'You are already subscribed. Thank you!']);
        exit;
    }
    $db->update('newsletter_subscribers', [
        'is_active' => 1,
        'status' => 'active',
        'unsubscribed_at' => null,
    ], 'id = ?', [$existing['id']]);
    echo json_encode(['success' => true, 'message' => 'Welcome back! You have been resubscribed.']);
    exit;
}

$db->insert('newsletter_subscribers', [
    'email' => $email,
    'is_active' => 1,
    'status' => 'active',
    'subscribed_at' => date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s'),
]);

echo json_encode(['success' => true, 'message' => 'Successfully subscribed! Thank you for joining our family.']);
