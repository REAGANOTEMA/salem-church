<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$dbAdmin = Database::getNamed('admin');
$admin = currentAdmin();
$section = $_GET['section'] ?? 'dashboard';
$sectionTitle = ucwords(str_replace('-', ' ', $section));

$moduleMap = [
    'dashboard', 'news', 'events', 'sermons', 'gallery', 'youtube-live',
    'announcements', 'prayer-requests', 'contact-messages', 'subscribers',
    'testimonials', 'leadership', 'ministries', 'users', 'donations',
    'settings', 'activity-logs', 'backups', 'profile',
];

if (!in_array($section, $moduleMap) && $section !== 'dashboard') {
    $section = 'not-found';
    $sectionTitle = 'Page Not Found';
}

$sidebarGroups = [
    'MAIN' => [
        ['section' => 'dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'],
    ],
    'CONTENT' => [
        ['section' => 'news', 'icon' => 'fas fa-newspaper', 'label' => 'News'],
        ['section' => 'events', 'icon' => 'fas fa-calendar-alt', 'label' => 'Events'],
        ['section' => 'sermons', 'icon' => 'fas fa-book-open', 'label' => 'Sermons'],
        ['section' => 'gallery', 'icon' => 'fas fa-images', 'label' => 'Gallery'],
        ['section' => 'youtube-live', 'icon' => 'fas fa-video', 'label' => 'YouTube Live'],
        ['section' => 'announcements', 'icon' => 'fas fa-bullhorn', 'label' => 'Announcements'],
    ],
    'ENGAGEMENT' => [
        ['section' => 'prayer-requests', 'icon' => 'fas fa-pray', 'label' => 'Prayer Requests'],
        ['section' => 'contact-messages', 'icon' => 'fas fa-envelope', 'label' => 'Contact Messages'],
        ['section' => 'subscribers', 'icon' => 'fas fa-users', 'label' => 'Subscribers'],
        ['section' => 'testimonials', 'icon' => 'fas fa-quote-left', 'label' => 'Testimonials'],
    ],
    'MINISTRY' => [
        ['section' => 'leadership', 'icon' => 'fas fa-user-tie', 'label' => 'Leadership'],
        ['section' => 'ministries', 'icon' => 'fas fa-hands-helping', 'label' => 'Ministries'],
        ['section' => 'users', 'icon' => 'fas fa-user-shield', 'label' => 'Users'],
    ],
    'FINANCE' => [
        ['section' => 'donations', 'icon' => 'fas fa-hand-holding-usd', 'label' => 'Donations'],
    ],
    'SYSTEM' => [
        ['section' => 'settings', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
        ['section' => 'activity-logs', 'icon' => 'fas fa-history', 'label' => 'Activity Logs'],
        ['section' => 'backups', 'icon' => 'fas fa-database', 'label' => 'Backups'],
        ['section' => 'profile', 'icon' => 'fas fa-user-circle', 'label' => 'Profile'],
    ],
];

// Handle module POST requests BEFORE any HTML output (prevents "headers already sent" errors)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($section, $moduleMap) && $section !== 'dashboard') {
    $moduleFile = __DIR__ . '/modules/' . $section . '.php';
    if (file_exists($moduleFile)) {
        require $moduleFile;
        exit;
    }
}

$stats = [];
if ($section === 'dashboard') {
    try {
        $stats['total_news'] = $db->count('news');
        $stats['published_news'] = $db->count('news', "status = 'published'");
        $stats['total_events'] = $db->count('events');
        $stats['upcoming_events'] = $db->count('events', "status = 'upcoming'");
        $stats['total_sermons'] = $db->count('sermons');
        $stats['total_gallery'] = $db->count('gallery');
        $stats['prayer_requests'] = $db->count('prayer_requests');
        $stats['contact_messages'] = $db->count('contact_messages');
        $stats['subscribers'] = $db->count('newsletter_subscribers');
        $stats['total_donations'] = $db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM donations")->total ?? 0;
    } catch (Exception $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
    }
}

$initials = strtoupper(substr($admin['name'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $sectionTitle ?> - Admin - <?= CHURCH_NAME ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="../public/images/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon-16x16.png">
    <link rel="icon" type="image/svg+xml" href="../public/favicon.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="../public/apple-touch-icon.png">
    <meta name="msapplication-TileImage" content="../public/icons/icon-144x144.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed: 0px;
            --topbar-height: 64px;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-accent: #0ea5e9;
            --bg-page: #f1f5f9;
            --text-sidebar: #94a3b8;
            --text-sidebar-active: #ffffff;
            --border-color: #e2e8f0;
            --card-bg: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.08);
            --radius: 12px;
            --radius-sm: 8px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-page);
            color: #1e293b;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-primary);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .sidebar-header {
            padding: 20px 20px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand { flex: 1; min-width: 0; }

        .sidebar-brand h2 {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.2px;
        }

        .sidebar-brand span {
            font-size: 11px;
            color: var(--text-sidebar);
            font-weight: 400;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .nav-group { margin-bottom: 6px; }

        .nav-group-title {
            padding: 14px 20px 6px;
            font-size: 10px;
            font-weight: 700;
            color: rgba(148, 163, 184, 0.45);
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            color: var(--text-sidebar);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
            margin: 1px 8px;
            border-radius: var(--radius-sm);
        }

        .nav-item:hover {
            color: #e2e8f0;
            background: rgba(255,255,255,0.08);
        }

        .nav-item.active {
            color: var(--text-sidebar-active);
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.25), rgba(56, 189, 248, 0.12));
            font-weight: 600;
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 22px;
            background: var(--bg-accent);
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 8px rgba(14, 165, 233, 0.4);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .nav-item.active i { color: var(--bg-accent); }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
            background: rgba(0,0,0,0.15);
        }

        .sidebar-user { display: flex; align-items: center; gap: 10px; }

        .sidebar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .sidebar-user-info { flex: 1; min-width: 0; }

        .sidebar-user-name {
            font-size: 12px;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 10px;
            color: var(--text-sidebar);
            text-transform: capitalize;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Topbar */
        .topbar {
            position: sticky;
            top: 0;
            height: var(--topbar-height);
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 900;
        }

        .topbar-left { display: flex; align-items: center; gap: 16px; }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: #475569;
            cursor: pointer;
            padding: 8px;
            border-radius: 10px;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
        }

        .sidebar-toggle:hover { background: #f1f5f9; }
        .sidebar-toggle:active { background: #e2e8f0; transform: scale(0.95); }

        .topbar-title {
            font-size: 19px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .topbar-right { display: flex; align-items: center; gap: 8px; }

        .topbar-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 17px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
        }

        .topbar-btn:hover { background: #f1f5f9; color: #334155; }
        .topbar-btn:active { background: #e2e8f0; transform: scale(0.95); }

        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .admin-profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            border-radius: 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
            -webkit-tap-highlight-color: transparent;
        }

        .admin-profile-btn:hover { background: #f1f5f9; }

        .admin-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .admin-name-display {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            text-align: left;
            line-height: 1.2;
        }

        .admin-name-display small {
            display: block;
            font-size: 11px;
            font-weight: 400;
            color: #94a3b8;
            text-transform: capitalize;
        }

        .dropdown-arrow {
            font-size: 10px;
            color: #94a3b8;
            margin-left: 2px;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 200px;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg), 0 0 0 1px rgba(0,0,0,0.04);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px) scale(0.97);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1001;
            overflow: hidden;
        }

        .profile-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            font-size: 13px;
            color: #475569;
            text-decoration: none;
            transition: all 0.15s;
        }

        .dropdown-item:hover { background: #f8fafc; color: #0f172a; }
        .dropdown-item i { width: 16px; text-align: center; font-size: 14px; }

        .dropdown-divider { height: 1px; background: #f1f5f9; margin: 4px 0; }

        .dropdown-item.danger { color: #dc2626; }
        .dropdown-item.danger:hover { background: #fef2f2; color: #dc2626; }

        /* Content Area */
        .content-area {
            padding: 28px 32px;
            max-width: 1400px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            border-radius: var(--radius);
            padding: 30px 34px;
            color: #fff;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: 20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-banner h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
            position: relative;
        }

        .welcome-banner p {
            font-size: 14px;
            color: #94a3b8;
            position: relative;
        }

        .welcome-banner .date-display {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            text-align: right;
            font-size: 13px;
            color: #94a3b8;
        }

        .welcome-banner .date-display strong {
            display: block;
            font-size: 20px;
            color: #fff;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 22px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid var(--border-color);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: rgba(14, 165, 233, 0.2);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .stat-card:hover .stat-icon { transform: scale(1.1); }

        .stat-icon.blue { background: #eff6ff; color: #2563eb; }
        .stat-icon.green { background: #f0fdf4; color: #16a34a; }
        .stat-icon.purple { background: #faf5ff; color: #9333ea; }
        .stat-icon.amber { background: #fffbeb; color: #d97706; }
        .stat-icon.rose { background: #fff1f2; color: #e11d48; }
        .stat-icon.teal { background: #f0fdfa; color: #0d9488; }
        .stat-icon.indigo { background: #eef2ff; color: #4f46e5; }
        .stat-icon.cyan { background: #ecfeff; color: #0891b2; }
        .stat-icon.slate { background: #f8fafc; color: #475569; }
        .stat-icon.emerald { background: #ecfdf5; color: #059669; }

        .stat-info { flex: 1; min-width: 0; }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin-top: 4px;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            overflow: hidden;
            transition: box-shadow 0.25s ease;
        }

        .card:hover { box-shadow: var(--shadow-md); }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        .card-body { padding: 24px; }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 28px;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .quick-action-btn:hover {
            border-color: var(--bg-accent);
            color: var(--bg-accent);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.12);
            transform: translateY(-2px);
        }

        .quick-action-btn i {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .quick-action-btn .qa-blue { background: #eff6ff; color: #2563eb; }
        .quick-action-btn .qa-green { background: #f0fdf4; color: #16a34a; }
        .quick-action-btn .qa-purple { background: #faf5ff; color: #9333ea; }
        .quick-action-btn .qa-amber { background: #fffbeb; color: #d97706; }

        /* Recent Activity */
        .activity-list { list-style: none; }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }

        .activity-item:last-child { border-bottom: none; }

        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .activity-dot.blue { background: #2563eb; }
        .activity-dot.green { background: #16a34a; }
        .activity-dot.amber { background: #d97706; }
        .activity-dot.red { background: #dc2626; }

        .activity-text {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
        }

        .activity-text strong { color: #0f172a; }

        .activity-time {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        /* 404 */
        .not-found { text-align: center; padding: 80px 20px; }
        .not-found i { font-size: 64px; color: #cbd5e1; margin-bottom: 20px; }
        .not-found h2 { font-size: 20px; color: #334155; margin-bottom: 8px; }
        .not-found p { font-size: 14px; color: #64748b; margin-bottom: 24px; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--bg-accent);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover { background: #0284c7; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.open { display: block; opacity: 1; }

        /* Responsive */
        @media (max-width: 1024px) {
            .grid-2 { grid-template-columns: 1fr; }
            .welcome-banner .date-display { display: none; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); box-shadow: none; }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 30px rgba(0,0,0,0.3); }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: flex; }
            .content-area { padding: 16px 18px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 14px; gap: 10px; flex-direction: column; align-items: center; text-align: center; }
            .stat-icon { width: 42px; height: 42px; font-size: 18px; border-radius: 10px; }
            .stat-value { font-size: 18px; }
            .stat-label { font-size: 11px; }
            .quick-actions { grid-template-columns: 1fr 1fr; gap: 8px; }
            .quick-action-btn { padding: 12px; font-size: 12px; gap: 8px; }
            .quick-action-btn i { width: 32px; height: 32px; font-size: 13px; }
            .topbar { padding: 0 14px; height: 56px; }
            .topbar-title { font-size: 15px; }
            .admin-name-display { display: none; }
            .dropdown-arrow { display: none; }
            .welcome-banner { padding: 18px; margin-bottom: 16px; border-radius: 10px; }
            .welcome-banner h1 { font-size: 17px; }
            .welcome-banner p { font-size: 13px; }
            .card-header { padding: 14px 18px; }
            .card-body { padding: 18px; }
            .grid-2 { gap: 12px; margin-bottom: 16px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stat-card { padding: 12px 8px; }
            .stat-value { font-size: 16px; }
            .quick-actions { grid-template-columns: 1fr 1fr; gap: 8px; }
            .quick-action-btn { padding: 10px; font-size: 11px; }
            .welcome-banner { padding: 14px; }
            .welcome-banner h1 { font-size: 15px; }
            .content-area { padding: 12px 14px; }
            .card-header { padding: 12px 14px; }
            .card-body { padding: 14px; }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-in {
            animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .animate-in:nth-child(2) { animation-delay: 0.05s; }
        .animate-in:nth-child(3) { animation-delay: 0.1s; }
        .animate-in:nth-child(4) { animation-delay: 0.15s; }
        .animate-in:nth-child(5) { animation-delay: 0.2s; }
        .animate-in:nth-child(6) { animation-delay: 0.25s; }
        .animate-in:nth-child(7) { animation-delay: 0.3s; }
        .animate-in:nth-child(8) { animation-delay: 0.35s; }
        .animate-in:nth-child(9) { animation-delay: 0.4s; }
        .animate-in:nth-child(10) { animation-delay: 0.45s; }

        /* Touch-friendly */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover { transform: none; box-shadow: var(--shadow-sm); }
            .quick-action-btn:hover { transform: none; }
        }

        /* Module Styles - Mobile-First */
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: #1e293b;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--bg-accent);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
            outline: none;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            letter-spacing: -0.1px;
        }

        .btn-primary {
            background: var(--bg-accent);
            border-color: var(--bg-accent);
        }

        .btn-primary:hover {
            background: #0284c7;
            border-color: #0284c7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(14, 165, 233, 0.2);
        }

        .badge {
            font-weight: 600;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.2px;
        }

        .table {
            font-size: 13px;
        }

        .table th {
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom-width: 1px;
            padding: 12px 16px;
        }

        .table td {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .table > tbody > tr {
            transition: background 0.15s;
        }

        .pagination .page-link {
            border-radius: 8px;
            margin: 0 2px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s;
        }

        .pagination .page-link:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .pagination .page-item.active .page-link {
            background: var(--bg-accent);
            border-color: var(--bg-accent);
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.25);
        }

        @media (max-width: 768px) {
            .card .d-flex { flex-wrap: wrap; gap: 8px; }
            .card .d-flex h4 { font-size: 15px; }
            .table { font-size: 12px; }
            .table th { font-size: 10px; padding: 10px 8px; }
            .table td { padding: 10px 8px; vertical-align: middle; }
            .btn-group-sm .btn { padding: 5px 10px; font-size: 11px; }
            .pagination { flex-wrap: wrap; justify-content: center; gap: 4px; }
            .pagination .page-link { padding: 6px 10px; font-size: 12px; }
            .form-control, .form-select { min-height: 44px; }
            .modal-content { border-radius: 16px; border: none; box-shadow: var(--shadow-lg); }
            .modal-header { padding: 20px 22px 16px; border-bottom: 1px solid #f1f5f9; }
            .modal-body { padding: 20px 22px; }
            .modal-footer { padding: 14px 22px; border-top: 1px solid #f1f5f9; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="<?= LOGO_URL ?>" alt="Logo" class="sidebar-logo" onerror="this.src='<?= LOGO_FALLBACK ?>'">
            <div class="sidebar-brand">
                <h2>Salem Dominion</h2>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($sidebarGroups as $groupName => $items): ?>
                <div class="nav-group">
                    <div class="nav-group-title"><?= $groupName ?></div>
                    <?php foreach ($items as $item): ?>
                        <a href="?section=<?= $item['section'] ?>"
                           class="nav-item <?= $section === $item['section'] ? 'active' : '' ?>">
                            <i class="<?= $item['icon'] ?>"></i>
                            <span><?= $item['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?= $initials ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= sanitize($admin['name'] ?? 'Admin') ?></div>
                    <div class="sidebar-user-role"><?= sanitize($admin['role'] ?? 'admin') ?></div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="topbar-title"><?= $sectionTitle ?></h1>
            </div>

            <div class="topbar-right">
                <button class="topbar-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge"></span>
                </button>

                <div style="position:relative">
                    <button class="admin-profile-btn" id="profileDropdownBtn">
                        <div class="admin-avatar"><?= $initials ?></div>
                        <div class="admin-name-display">
                            <?= sanitize($admin['name'] ?? 'Admin') ?>
                            <small><?= sanitize($admin['role'] ?? 'admin') ?></small>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown">
                        <a href="?section=profile" class="dropdown-item">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                        <a href="?section=settings" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-area">
            <?php if ($section === 'dashboard'): ?>
                <div class="welcome-banner animate-in">
                    <h1>Welcome back, <?= sanitize($admin['name'] ?? 'Admin') ?>!</h1>
                    <p>Here's what's happening with your church website today.</p>
                    <div class="date-display">
                        <strong><?= date('j') ?></strong>
                        <?= date('M, Y') ?>
                    </div>
                </div>

                <div class="stats-grid">
                    <?php
                    $statCards = [
                        ['key' => 'total_news', 'label' => 'Total News', 'icon' => 'fas fa-newspaper', 'color' => 'blue'],
                        ['key' => 'published_news', 'label' => 'Published News', 'icon' => 'fas fa-check-circle', 'color' => 'green'],
                        ['key' => 'total_events', 'label' => 'Events', 'icon' => 'fas fa-calendar-alt', 'color' => 'purple'],
                        ['key' => 'upcoming_events', 'label' => 'Upcoming Events', 'icon' => 'fas fa-clock', 'color' => 'amber'],
                        ['key' => 'total_sermons', 'label' => 'Sermons', 'icon' => 'fas fa-book-open', 'color' => 'teal'],
                        ['key' => 'total_gallery', 'label' => 'Gallery Items', 'icon' => 'fas fa-images', 'color' => 'rose'],
                        ['key' => 'prayer_requests', 'label' => 'Prayer Requests', 'icon' => 'fas fa-pray', 'color' => 'indigo'],
                        ['key' => 'contact_messages', 'label' => 'Contact Messages', 'icon' => 'fas fa-envelope', 'color' => 'cyan'],
                        ['key' => 'subscribers', 'label' => 'Subscribers', 'icon' => 'fas fa-users', 'color' => 'slate'],
                        ['key' => 'total_donations', 'label' => 'Total Donations', 'icon' => 'fas fa-hand-holding-usd', 'color' => 'emerald', 'prefix' => 'UGX '],
                    ];
                    foreach ($statCards as $idx => $sc):
                        $value = $stats[$sc['key']] ?? 0;
                        $display = ($sc['prefix'] ?? '') . number_format($value);
                    ?>
                        <div class="stat-card animate-in" style="animation-delay: <?= $idx * 0.04 ?>s">
                            <div class="stat-icon <?= $sc['color'] ?>">
                                <i class="<?= $sc['icon'] ?>"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value"><?= $display ?></div>
                                <div class="stat-label"><?= $sc['label'] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="quick-actions">
                    <a href="?section=news&action=create" class="quick-action-btn">
                        <i class="fas fa-plus qa-blue"></i> Add News
                    </a>
                    <a href="?section=events" class="quick-action-btn">
                        <i class="fas fa-calendar-plus qa-green"></i> Add Event
                    </a>
                    <a href="?section=sermons" class="quick-action-btn">
                        <i class="fas fa-microphone qa-purple"></i> Add Sermon
                    </a>
                    <a href="?section=contact-messages" class="quick-action-btn">
                        <i class="fas fa-envelope qa-amber"></i> View Messages
                    </a>
                </div>

                <div class="grid-2">
                    <div class="card animate-in">
                        <div class="card-header">
                            <h3 class="card-title">Recent Activity</h3>
                            <a href="?section=activity-logs" class="btn-primary" style="font-size:12px;padding:6px 14px;">View All</a>
                        </div>
                        <div class="card-body">
                            <ul class="activity-list">
                                <?php
                                try {
                                    $recentLogs = $dbAdmin->fetchAll(
                                        "SELECT al.*, au.full_name FROM activity_logs al LEFT JOIN admin_users au ON al.user_id = au.id ORDER BY al.created_at DESC LIMIT 6"
                                    );
                                    if (empty($recentLogs)) {
                                        echo '<li class="activity-item"><div class="activity-text" style="color:#94a3b8;">No recent activity recorded.</div></li>';
                                    } else {
                                        foreach ($recentLogs as $log) {
                                            $colorMap = ['login' => 'blue', 'logout' => 'blue', 'created' => 'green', 'updated' => 'amber', 'deleted' => 'red'];
                                            $dotColor = $colorMap[$log['action']] ?? 'blue';
                                            echo '<li class="activity-item">';
                                            echo '<div class="activity-dot ' . $dotColor . '"></div>';
                                            echo '<div>';
                                            echo '<div class="activity-text"><strong>' . sanitize($log['full_name'] ?? 'System') . '</strong> ' . sanitize($log['action']) . ' ' . sanitize($log['module'] ?? '') . '</div>';
                                            echo '<div class="activity-time">' . timeAgo($log['created_at']) . '</div>';
                                            echo '</div>';
                                            echo '</li>';
                                        }
                                    }
                                } catch (Exception $e) {
                                    echo '<li class="activity-item"><div class="activity-text" style="color:#94a3b8;">Unable to load activity logs.</div></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>

                    <div class="card animate-in">
                        <div class="card-header">
                            <h3 class="card-title">System Info</h3>
                        </div>
                        <div class="card-body">
                            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                                <tr>
                                    <td style="padding:8px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Church</td>
                                    <td style="padding:8px 0;text-align:right;font-weight:600;border-bottom:1px solid #f1f5f9;"><?= CHURCH_NAME ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Pastor</td>
                                    <td style="padding:8px 0;text-align:right;font-weight:600;border-bottom:1px solid #f1f5f9;"><?= CHURCH_PASTOR ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Admin Role</td>
                                    <td style="padding:8px 0;text-align:right;font-weight:600;text-transform:capitalize;border-bottom:1px solid #f1f5f9;"><?= sanitize($admin['role'] ?? 'admin') ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">PHP Version</td>
                                    <td style="padding:8px 0;text-align:right;font-weight:600;border-bottom:1px solid #f1f5f9;"><?= phpversion() ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:#64748b;border-bottom:1px solid #f1f5f9;">Server</td>
                                    <td style="padding:8px 0;text-align:right;font-weight:600;border-bottom:1px solid #f1f5f9;"><?= php_uname('s') ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0;color:#64748b;">Environment</td>
                                    <td style="padding:8px 0;text-align:right;font-weight:600;text-transform:capitalize;"><?= APP_ENV ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($section === 'not-found'): ?>
                <div class="not-found">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h2>Page Not Found</h2>
                    <p>The section you're looking for doesn't exist.</p>
                    <a href="?section=dashboard" class="btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

            <?php else: ?>
                <?php
                $moduleFile = __DIR__ . '/modules/' . $section . '.php';
                if (file_exists($moduleFile)) {
                    require $moduleFile;
                } else {
                    echo '<div class="not-found">';
                    echo '<i class="fas fa-puzzle-piece"></i>';
                    echo '<h2>Module Not Available</h2>';
                    echo '<p>The "' . sanitize($sectionTitle) . '" module file is missing.</p>';
                    echo '<a href="?section=dashboard" class="btn-primary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>';
                    echo '</div>';
                }
                ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');
            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileDropdown = document.getElementById('profileDropdown');

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }

            toggle.addEventListener('click', function() {
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay.addEventListener('click', closeSidebar);

            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('open');
            });

            document.addEventListener('click', function(e) {
                if (!profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
                    profileDropdown.classList.remove('open');
                }
            });

            document.querySelectorAll('.nav-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });

            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth > 768 && sidebar.classList.contains('open')) {
                        closeSidebar();
                    }
                }, 100);
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                    closeSidebar();
                }
                if (e.key === 'Escape' && profileDropdown.classList.contains('open')) {
                    profileDropdown.classList.remove('open');
                }
            });
        })();
    </script>
</body>
</html>
