<?php
$pageTitle = 'Events | Salem Dominion Ministries';
$currentPage = 'events';
$pageDescription = 'Discover upcoming events, services, and gatherings at Salem Dominion Ministries.';

require_once 'config.php';
require_once 'db_connection.php';

$pdo = Database::getInstance()->getPdo();

$tab = $_GET['tab'] ?? 'upcoming';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

$events = [];
$featured_event = null;
$total_events = 0;
$total_pages = 1;
$next_upcoming = null;

try {
    if ($pdo) {
        if ($tab === 'upcoming') {
            $where = "WHERE e.event_date >= CURDATE() AND e.status != 'deleted'";
            $order = "ORDER BY e.event_date ASC, e.event_time ASC";
        } elseif ($tab === 'past') {
            $where = "WHERE e.event_date < CURDATE() AND e.status != 'deleted'";
            $order = "ORDER BY e.event_date DESC, e.event_time DESC";
        } else {
            $where = "WHERE e.status != 'deleted'";
            $order = "ORDER BY e.event_date ASC";
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM events e {$where}");
        if ($countStmt) {
            $countStmt->execute();
            $total_events = $countStmt->fetchColumn();
            $total_pages = max(1, ceil($total_events / $per_page));
            $page = min($page, $total_pages);
        }

        $query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name FROM events e LEFT JOIN users u ON e.created_by = u.id {$where} {$order} LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($query);
        if ($stmt) {
            $stmt->execute([$per_page, $offset]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if ($tab === 'upcoming' && $page === 1) {
            $featStmt = $pdo->prepare("SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name FROM events e LEFT JOIN users u ON e.created_by = u.id WHERE e.event_date >= CURDATE() AND e.status != 'deleted' ORDER BY e.event_date ASC LIMIT 1");
            if ($featStmt) {
                $featStmt->execute();
                $featured_event = $featStmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            if (!$featured_event && !empty($events)) {
                $featured_event = $events[0];
                $events = array_slice($events, 1);
            }
        }

        $nextStmt = $pdo->prepare("SELECT e.event_date, e.event_time, e.title FROM events e WHERE e.event_date >= CURDATE() AND e.status != 'deleted' ORDER BY e.event_date ASC LIMIT 1");
        if ($nextStmt) {
            $nextStmt->execute();
            $next_upcoming = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    }
} catch (Exception $e) {
    error_log("Events page error: " . $e->getMessage());
}

include 'components/header.php';
?>

<style>
.events-hero {
    background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(14,165,233,0.75)), url('assets/hero-community-CDAgPtPb.jpg') center/cover no-repeat;
    padding: 100px 0 60px;
    color: #fff;
    text-align: center;
}
.events-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; }
.events-hero p { font-family: 'Montserrat', sans-serif; font-size: 1.1rem; opacity: 0.9; max-width: 600px; margin: 15px auto 0; }
.events-hero .scripture { font-style: italic; opacity: 0.8; margin-top: 20px; font-size: 0.95rem; }
.events-hero .scripture strong { color: #fbbf24; }

.countdown-bar { background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 16px; padding: 30px 40px; margin-top: -40px; position: relative; z-index: 10; color: #fff; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 40px rgba(0,0,0,0.2); flex-wrap: wrap; gap: 20px; }
.countdown-bar h5 { font-family: 'Playfair Display', serif; margin: 0; font-size: 1.2rem; color: #fbbf24; }
.countdown-timer { display: flex; gap: 15px; }
.countdown-timer .cd-block { text-align: center; }
.countdown-timer .cd-num { font-size: 2rem; font-weight: 800; font-family: 'Montserrat', sans-serif; color: #fff; line-height: 1; }
.countdown-timer .cd-label { font-size: 0.7rem; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; font-family: 'Montserrat', sans-serif; }

.tab-filters { background: #fff; border-radius: 14px; padding: 8px; display: inline-flex; gap: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 30px; }
.tab-filters .tab-btn { border: none; background: transparent; padding: 10px 24px; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 0.9rem; color: #64748b; cursor: pointer; transition: all 0.3s ease; }
.tab-filters .tab-btn.active { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; box-shadow: 0 4px 15px rgba(14,165,233,0.3); }
.tab-filters .tab-btn:hover:not(.active) { color: #0f172a; background: #f1f5f9; }

.featured-event { background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.08); margin-bottom: 50px; }
.featured-event .evt-img { height: 350px; background: linear-gradient(135deg, #0ea5e9, #0f172a); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.3); font-size: 4rem; position: relative; overflow: hidden; }
.featured-event .evt-img img { width: 100%; height: 100%; object-fit: cover; }
.featured-event .evt-info { padding: 30px; }
.featured-event .evt-info h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #0f172a; }
.featured-event .date-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(251,191,36,0.15); color: #d97706; padding: 6px 14px; border-radius: 10px; font-weight: 600; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; }

.event-card { background: #fff; border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.4s cubic-bezier(0.4,0,0.2,1); height: 100%; }
.event-card:hover { transform: translateY(-8px); box-shadow: 0 15px 40px rgba(0,0,0,0.12); }
.event-card .evt-thumb { height: 180px; background: linear-gradient(135deg, #0ea5e9, #0f172a); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.4); font-size: 3rem; position: relative; overflow: hidden; }
.event-card .evt-thumb img { width: 100%; height: 100%; object-fit: cover; }
.event-card .evt-date-float { position: absolute; top: 12px; left: 12px; background: rgba(251,191,36,0.95); color: #0f172a; padding: 8px 12px; border-radius: 10px; text-align: center; min-width: 60px; }
.event-card .evt-date-float .day { font-size: 1.5rem; font-weight: 800; line-height: 1; font-family: 'Montserrat', sans-serif; }
.event-card .evt-date-float .month { font-size: 0.7rem; text-transform: uppercase; font-weight: 600; font-family: 'Montserrat', sans-serif; letter-spacing: 1px; }
.event-card .card-body { padding: 20px; }
.event-card .card-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.event-card .evt-meta { color: #64748b; font-size: 0.8rem; font-family: 'Montserrat', sans-serif; }
.event-card .evt-meta i { color: #0ea5e9; width: 18px; }
.event-card .evt-desc { color: #64748b; font-size: 0.85rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 8px; }

.btn-gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0f172a; font-weight: 600; border: none; border-radius: 10px; padding: 10px 24px; font-family: 'Montserrat', sans-serif; }
.btn-gold:hover { background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; transform: translateY(-2px); box-shadow: 0 5px 20px rgba(251,191,36,0.3); }
.btn-blue { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; font-weight: 600; border: none; border-radius: 10px; padding: 10px 24px; font-family: 'Montserrat', sans-serif; }
.btn-blue:hover { background: linear-gradient(135deg, #0284c7, #0369a1); color: #fff; transform: translateY(-2px); }

.empty-state { text-align: center; padding: 80px 20px; }
.empty-state h3 { font-family: 'Playfair Display', serif; color: #0f172a; }

.pagination .page-link { border-radius: 10px; margin: 0 3px; border: 2px solid #e2e8f0; color: #0f172a; font-family: 'Montserrat', sans-serif; }
.pagination .page-item.active .page-link { background: linear-gradient(135deg, #0ea5e9, #0284c7); border-color: #0ea5e9; color: #fff; }
.pagination .page-link:hover { background: #f1f5f9; border-color: #0ea5e9; color: #0ea5e9; }

.calendar-grid { background: #fff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
.calendar-grid .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.calendar-grid .cal-header h5 { font-family: 'Playfair Display', serif; margin: 0; color: #0f172a; }
.calendar-grid .cal-day { padding: 8px; text-align: center; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; border-radius: 8px; cursor: default; }
.calendar-grid .cal-day.has-event { background: rgba(251,191,36,0.15); color: #d97706; font-weight: 600; cursor: pointer; }
.calendar-grid .cal-day.today { background: #0ea5e9; color: #fff; font-weight: 700; }
.calendar-grid .cal-day-header { font-weight: 700; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
.calendar-grid .cal-day.past { color: #cbd5e1; }

.modal-detail .modal-content { border-radius: 16px; border: none; overflow: hidden; }
.modal-detail .modal-header { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; border: none; }
.modal-detail .modal-body { padding: 30px; }

@media(max-width:768px) { .events-hero h1 { font-size: 2rem; } .countdown-bar { padding: 20px; } .countdown-timer .cd-num { font-size: 1.5rem; } }

.sdm-interactions { border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px; }
.sdm-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sdm-action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 10px;
    background: #fff; color: #475569; font-size: 0.85rem; font-weight: 600;
    font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s;
    text-decoration: none; white-space: nowrap;
}
.sdm-action-btn:hover { border-color: #0ea5e9; color: #0ea5e9; background: rgba(14,165,233,0.05); }
.sdm-action-btn.liked { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }
.sdm-action-btn.liked i { animation: sdmHeartPop 0.3s ease; }
@keyframes sdmHeartPop { 0%{transform:scale(1)} 50%{transform:scale(1.3)} 100%{transform:scale(1)} }
.sdm-action-btn i { font-size: 0.9rem; }
.sdm-share-menu { position: relative; display: inline-block; }
.sdm-share-dropdown {
    display: none; position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%);
    background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    padding: 8px; z-index: 100; min-width: 180px;
}
.sdm-share-dropdown.show { display: block; animation: sdmFadeIn 0.2s ease; }
@keyframes sdmFadeIn { from{opacity:0;transform:translateX(-50%) translateY(4px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
.sdm-share-dropdown a {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    border-radius: 8px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif;
    color: #475569; text-decoration: none; transition: background 0.2s;
}
.sdm-share-dropdown a:hover { background: #f1f5f9; }
.sdm-share-dropdown a i { width: 20px; text-align: center; }
.sdm-share-dropdown .fa-whatsapp { color: #25d366; }
.sdm-share-dropdown .fa-facebook-f { color: #1877f2; }
.sdm-share-dropdown .fa-twitter { color: #1da1f2; }
.sdm-share-dropdown .fa-telegram { color: #0088cc; }
.sdm-share-dropdown .fa-link { color: #64748b; }
.sdm-comments-section { margin-top: 20px; }
.sdm-comments-header { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.sdm-comments-header span { background: #0ea5e9; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-weight: 600; }
.sdm-comment-form { display: flex; gap: 12px; margin-bottom: 20px; }
.sdm-comment-form .avatar {
    width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7);
    display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;
    font-size: 0.85rem; flex-shrink: 0; font-family: 'Montserrat', sans-serif;
}
.sdm-comment-form .form-body { flex: 1; }
.sdm-comment-form textarea {
    width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 12px;
    font-size: 0.9rem; font-family: 'Montserrat', sans-serif; resize: vertical;
    min-height: 60px; transition: border-color 0.3s; outline: none; color: #0f172a;
}
.sdm-comment-form textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.sdm-comment-form .char-count { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 4px; }
.sdm-comment-form .form-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
.sdm-comment-form .btn-post {
    padding: 8px 20px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff;
    border: none; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: 'Montserrat', sans-serif; transition: all 0.3s;
}
.sdm-comment-form .btn-post:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
.sdm-comment-form .btn-post:disabled { opacity: 0.5; cursor: not-allowed; }
.sdm-comment-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; animation: sdmFadeIn 0.3s ease; }
.sdm-comment-item .avatar {
    width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #fbbf24, #f59e0b);
    display: flex; align-items: center; justify-content: center; color: #0f172a; font-weight: 700;
    font-size: 0.75rem; flex-shrink: 0; font-family: 'Montserrat', sans-serif;
}
.sdm-comment-item .comment-body { flex: 1; }
.sdm-comment-item .comment-author { font-weight: 700; color: #0f172a; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; }
.sdm-comment-item .comment-time { color: #94a3b8; font-size: 0.75rem; margin-left: 8px; }
.sdm-comment-item .comment-text { color: #475569; font-size: 0.9rem; line-height: 1.6; margin-top: 4px; word-wrap: break-word; }
.sdm-comment-item .comment-delete { color: #94a3b8; font-size: 0.75rem; cursor: pointer; margin-top: 4px; border: none; background: none; padding: 0; font-family: 'Montserrat', sans-serif; }
.sdm-comment-item .comment-delete:hover { color: #ef4444; }
.sdm-load-more { display: block; width: 100%; padding: 10px; border: 2px dashed #e2e8f0; border-radius: 10px; background: transparent; color: #64748b; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s; text-align: center; }
.sdm-load-more:hover { border-color: #0ea5e9; color: #0ea5e9; }
.sdm-copied-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #0f172a; color: #fff; padding: 10px 24px; border-radius: 10px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; z-index: 9999; animation: sdmToastIn 0.3s ease; }
@keyframes sdmToastIn { from{opacity:0;transform:translateX(-50%) translateY(10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
@media(max-width:480px) { .sdm-actions { gap: 8px; } .sdm-action-btn { padding: 7px 12px; font-size: 0.8rem; } }
</style>

<section class="events-hero" data-aos="fade-in">
    <div class="container">
        <h1 data-aos="fade-up">Events</h1>
        <p data-aos="fade-up" data-delay="100">Join us for powerful gatherings, conferences, and community celebrations</p>
        <div class="scripture" data-aos="fade-up" data-delay="200">
            <i class="fas fa-users me-2"></i>
            "For where two or three gather in my name, there am I with them." &mdash; <strong>Matthew 18:20</strong>
        </div>
    </div>
</section>

<section style="padding: 0 0 60px;">
    <div class="container">

        <?php if ($next_upcoming && $tab === 'upcoming'): ?>
        <div class="countdown-bar" data-aos="fade-up" id="countdownBar" data-event-date="<?= htmlspecialchars($next_upcoming['event_date'] . ' ' . ($next_upcoming['event_time'] ?? '09:00:00')) ?>">
            <div>
                <h5><i class="fas fa-calendar-check me-2"></i><?= htmlspecialchars($next_upcoming['title']) ?></h5>
                <small style="color:#94a3b8;font-family:'Montserrat',sans-serif;">Next event starts in...</small>
            </div>
            <div class="countdown-timer" id="countdownTimer">
                <div class="cd-block"><div class="cd-num" id="cd-days">00</div><div class="cd-label">Days</div></div>
                <div class="cd-block"><div class="cd-num" id="cd-hours">00</div><div class="cd-label">Hours</div></div>
                <div class="cd-block"><div class="cd-num" id="cd-mins">00</div><div class="cd-label">Min</div></div>
                <div class="cd-block"><div class="cd-num" id="cd-secs">00</div><div class="cd-label">Sec</div></div>
            </div>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mt-5 mb-4 flex-wrap gap-3">
            <div class="tab-filters" data-aos="fade-up">
                <a href="?tab=upcoming" class="tab-btn <?= $tab === 'upcoming' ? 'active' : '' ?>"><i class="fas fa-arrow-up me-1"></i> Upcoming</a>
                <a href="?tab=past" class="tab-btn <?= $tab === 'past' ? 'active' : '' ?>"><i class="fas fa-history me-1"></i> Past</a>
                <a href="?tab=all" class="tab-btn <?= $tab === 'all' ? 'active' : '' ?>"><i class="fas fa-list me-1"></i> All</a>
            </div>
            <div data-aos="fade-up">
                <button class="btn btn-sm btn-outline-secondary" onclick="toggleView()" id="viewToggle">
                    <i class="fas fa-calendar me-1"></i> Calendar View
                </button>
            </div>
        </div>

        <div id="gridView">
            <?php if ($featured_event): ?>
            <div class="featured-event" data-aos="fade-up">
                <div class="row g-0">
                    <div class="col-lg-6">
                        <div class="evt-img">
                            <?php if (!empty($featured_event['banner_image'])): ?>
                                <img src="<?= htmlspecialchars($featured_event['banner_image']) ?>" alt="<?= htmlspecialchars($featured_event['title']) ?>">
                            <?php else: ?>
                                <i class="fas fa-calendar-star"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="evt-info d-flex flex-column justify-content-center h-100">
                            <div class="date-badge mb-3">
                                <i class="fas fa-calendar-alt"></i>
                                <?= formatDate($featured_event['event_date'], 'l, F j, Y') ?>
                                <?php if (!empty($featured_event['event_time'])): ?>
                                    at <?= date('g:i A', strtotime($featured_event['event_time'])) ?>
                                <?php endif; ?>
                            </div>
                            <h3><?= htmlspecialchars($featured_event['title']) ?></h3>
                            <?php if (!empty($featured_event['location'])): ?>
                                <p class="mb-2" style="color:#64748b;"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?= htmlspecialchars($featured_event['location']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($featured_event['speaker'])): ?>
                                <p class="mb-2" style="color:#64748b;"><i class="fas fa-user-tie me-2 text-primary"></i><?= htmlspecialchars($featured_event['speaker']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($featured_event['description'])): ?>
                                <p class="mt-2" style="color:#475569;line-height:1.7;font-size:0.95rem;"><?= htmlspecialchars(truncate($featured_event['description'], 300)) ?></p>
                            <?php endif; ?>
                            <div class="mt-3 d-flex gap-2 flex-wrap">
                                <?php if (!empty($featured_event['registration_url'])): ?>
                                    <a href="<?= htmlspecialchars($featured_event['registration_url']) ?>" target="_blank" class="btn btn-gold"><i class="fas fa-user-plus me-2"></i>Register Now</a>
                                <?php endif; ?>
                                <button class="btn btn-blue" onclick="showEventDetail(<?= htmlspecialchars(json_encode([
                                    'id' => $featured_event['id'],
                                    'title' => $featured_event['title'],
                                    'date' => formatDate($featured_event['event_date'], 'l, F j, Y'),
                                    'time' => !empty($featured_event['event_time']) ? date('g:i A', strtotime($featured_event['event_time'])) : '',
                                    'location' => $featured_event['location'] ?? '',
                                    'speaker' => $featured_event['speaker'] ?? '',
                                    'description' => $featured_event['description'] ?? '',
                                    'registration_url' => $featured_event['registration_url'] ?? '',
                                    'max_attendees' => $featured_event['max_attendees'] ?? '',
                                ])) ?>"><i class="fas fa-info-circle me-2"></i>More Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($events)): ?>
            <div class="row g-4">
                <?php foreach ($events as $idx => $event):
                    $evtDate = new DateTime($event['event_date']);
                    $isPast = $evtDate < new DateTime();
                ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-delay="<?= ($idx % 3) * 100 ?>">
                    <div class="event-card">
                        <div class="evt-thumb">
                            <?php if (!empty($event['banner_image'])): ?>
                                <img src="<?= htmlspecialchars($event['banner_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-calendar-alt"></i>
                            <?php endif; ?>
                            <div class="evt-date-float">
                                <div class="day"><?= $evtDate->format('d') ?></div>
                                <div class="month"><?= $evtDate->format('M') ?></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($isPast): ?>
                                <span class="badge bg-secondary mb-2" style="font-family:'Montserrat',sans-serif;font-size:0.7rem;">Past Event</span>
                            <?php endif; ?>
                            <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                            <div class="evt-meta mb-2">
                                <div><i class="fas fa-clock"></i> <?= date('g:i A', strtotime($event['event_time'] ?? '09:00:00')) ?></div>
                                <?php if (!empty($event['location'])): ?>
                                    <div class="mt-1"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($event['speaker'])): ?>
                                    <div class="mt-1"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($event['speaker']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($event['description'])): ?>
                                <p class="evt-desc"><?= htmlspecialchars(truncate($event['description'], 120)) ?></p>
                            <?php endif; ?>
                            <div class="d-flex gap-2 mt-auto">
                                <?php if (!$isPast && !empty($event['registration_url'])): ?>
                                    <a href="<?= htmlspecialchars($event['registration_url']) ?>" target="_blank" class="btn btn-gold btn-sm"><i class="fas fa-user-plus me-1"></i>Register</a>
                                <?php endif; ?>
                                <button class="btn btn-outline-primary btn-sm" onclick='showEventDetail(<?= htmlspecialchars(json_encode([
                                    'id' => $event['id'],
                                    'title' => $event['title'],
                                    'date' => formatDate($event['event_date'], 'l, F j, Y'),
                                    'time' => !empty($event['event_time']) ? date('g:i A', strtotime($event['event_time'])) : '',
                                    'location' => $event['location'] ?? '',
                                    'speaker' => $event['speaker'] ?? '',
                                    'description' => $event['description'] ?? '',
                                    'registration_url' => $event['registration_url'] ?? '',
                                    'max_attendees' => $event['max_attendees'] ?? '',
                                ])) ?>'><i class="fas fa-info-circle me-1"></i>Details</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav class="mt-5 d-flex justify-content-center" data-aos="fade-up">
                <ul class="pagination">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=<?= urlencode($tab) ?>&page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?tab=<?= urlencode($tab) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=<?= urlencode($tab) ?>&page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state" data-aos="fade-up">
                <div style="font-size:5rem;color:#cbd5e1;margin-bottom:20px;"><i class="fas fa-calendar-times"></i></div>
                <h3>No <?= $tab === 'upcoming' ? 'Upcoming' : 'Past' ?> Events</h3>
                <p style="color:#64748b;max-width:400px;margin:10px auto;">
                    <?php if ($tab === 'upcoming'): ?>
                        There are no upcoming events at this time. Our next Sunday service is every Sunday at 9:00 AM. Join us!
                    <?php else: ?>
                        No past events to show yet.
                    <?php endif; ?>
                </p>
                <div class="mt-3" style="color:#64748b;font-size:0.9rem;">
                    <i class="fas fa-church me-2 text-primary"></i> Next Service: Sunday 9:00 AM - 12:00 PM
                </div>
                <?php if ($tab === 'upcoming'): ?>
                <a href="contact.php" class="btn btn-gold mt-3"><i class="fas fa-envelope me-2"></i>Contact Us</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div id="calendarView" style="display:none;">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="calendar-grid" id="calendarGrid" data-aos="fade-up"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade modal-detail" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailTitle" style="font-family:'Playfair Display',serif;"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="eventDetailBody"></div>
                <div class="sdm-interactions">
                    <div class="sdm-actions">
                        <button class="sdm-action-btn sdm-like-btn"><i class="far fa-heart"></i> <span class="like-count sdm-like-count">0</span></button>
                        <button class="sdm-action-btn sdm-toggle-comments"><i class="far fa-comment"></i> <span class="sdm-comment-count">0</span></button>
                        <div class="sdm-share-menu">
                            <button class="sdm-action-btn sdm-share-btn"><i class="fas fa-share-alt"></i> <span class="sdm-share-count">0</span></button>
                            <div class="sdm-share-dropdown">
                                <a href="#" class="sdm-share-link" data-platform="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                <a href="#" class="sdm-share-link" data-platform="facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
                                <a href="#" class="sdm-share-link" data-platform="twitter"><i class="fab fa-twitter"></i> Twitter</a>
                                <a href="#" class="sdm-share-link" data-platform="telegram"><i class="fab fa-telegram"></i> Telegram</a>
                                <a href="#" class="sdm-share-link" data-platform="link"><i class="fas fa-link"></i> Copy Link</a>
                            </div>
                        </div>
                    </div>
                    <div class="sdm-comments-section" style="display:none;">
                        <h6 class="sdm-comments-header"><i class="fas fa-comments"></i> Comments <span>0</span></h6>
                        <div class="sdm-comment-form">
                            <div class="avatar"><?= !empty($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'],0,1)) : 'G' ?></div>
                            <div class="form-body">
                                <form class="sdm-comment-form-form">
                                    <textarea placeholder="<?= !empty($_SESSION['user_logged_in']) ? 'Share your thoughts...' : 'Log in to comment...' ?>" <?= empty($_SESSION['user_logged_in']) ? 'disabled' : '' ?> maxlength="2000"></textarea>
                                    <div class="char-count">0/2000</div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn-post" <?= empty($_SESSION['user_logged_in']) ? 'disabled' : '' ?>>Post Comment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="sdm-comments-list"></div>
                        <button class="sdm-load-more" style="display:none;">Load more comments</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var sdmCurrentEvent = { type: 'event', id: 0, csrf: '<?= $csrfToken ?>', loggedIn: <?= !empty($_SESSION['user_logged_in']) ? 'true' : 'false' ?> };
function sdmHeaders() { return { 'Content-Type': 'application/json', 'X-CSRF-Token': sdmCurrentEvent.csrf }; }
function sdmVisitorHash() { var h = localStorage.getItem('sdm_vh'); if (!h) { h = 'v_' + Math.random().toString(36).substring(2) + Date.now().toString(36); localStorage.setItem('sdm_vh', h); } return h; }
function sdmEscapeHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
function sdmTimeAgo(dt) { var diff = Math.floor(Date.now()/1000) - Math.floor(new Date(dt).getTime()/1000); if (diff < 60) return 'Just now'; if (diff < 3600) return Math.floor(diff/60) + 'm ago'; if (diff < 86400) return Math.floor(diff/3600) + 'h ago'; if (diff < 604800) return Math.floor(diff/86400) + 'd ago'; return new Date(dt).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }

var sdmCommentsPage = 1, sdmLoadingComments = false;
function sdmLoadComments(page) {
    if (sdmLoadingComments) return;
    sdmLoadingComments = true;
    var s = sdmCurrentEvent;
    fetch('api.php?action=get_comments&content_type=' + s.type + '&content_id=' + s.id + '&page=' + page + '&limit=10')
    .then(function(r){return r.json();}).then(function(res) {
        sdmLoadingComments = false;
        var list = document.querySelector('#eventDetailModal .sdm-comments-list');
        var hdr = document.querySelector('#eventDetailModal .sdm-comments-header');
        if (!list) return;
        if (page === 1) list.innerHTML = '';
        if (hdr) hdr.innerHTML = '<i class="fas fa-comments"></i> Comments <span>' + (res.total||0) + '</span>';
        if (!res.data || res.data.length === 0) {
            if (page === 1) list.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:16px;font-size:0.85rem;font-family:Montserrat,sans-serif;">No comments yet. Be the first!</p>';
            var mb = list.parentElement.querySelector('.sdm-load-more'); if(mb) mb.style.display='none';
            return;
        }
        res.data.forEach(function(c) {
            var ini = c.user_name.split(' ').map(function(w){return w[0]}).join('').substring(0,2).toUpperCase();
            var del = (sdmCurrentEvent.loggedIn && c.user_id == <?= $_SESSION['user_id'] ?? 0 ?>) ? '<button class="sdm-comment-delete comment-delete" data-id="'+c.id+'"><i class="fas fa-trash-alt me-1"></i>Delete</button>' : '';
            list.insertAdjacentHTML('beforeend', '<div class="sdm-comment-item"><div class="avatar">'+ini+'</div><div class="comment-body"><div><span class="comment-author">'+sdmEscapeHtml(c.user_name)+'</span><span class="comment-time">'+sdmTimeAgo(c.created_at)+'</span></div><div class="comment-text">'+sdmEscapeHtml(c.comment)+'</div>'+del+'</div></div>');
        });
        var mb = list.parentElement.querySelector('.sdm-load-more');
        if (mb) { mb.style.display = (page >= (res.pagination?res.pagination.total_pages:1)) ? 'none' : 'block'; }
    }).catch(function(){ sdmLoadingComments = false; });
}
function sdmRefreshCounts() {
    var s = sdmCurrentEvent;
    fetch('api.php?action=get_counts&content_type=' + s.type + '&content_id=' + s.id)
    .then(function(r){return r.json();}).then(function(res) {
        if (!res.success) return;
        var wrap = document.querySelector('#eventDetailModal .sdm-interactions');
        if (!wrap) return;
        var lk = wrap.querySelector('.sdm-like-count'); if(lk) lk.textContent = res.likes||0;
        var cm = wrap.querySelector('.sdm-comment-count'); if(cm) cm.textContent = res.comments||0;
        var sh = wrap.querySelector('.sdm-share-count'); if(sh) sh.textContent = res.shares||0;
        if (res.user_liked) { var btn = wrap.querySelector('.sdm-like-btn'); if(btn){btn.classList.add('liked');btn.querySelector('i').className='fas fa-heart';} }
    });
}
function sdmResetInteractions() {
    sdmCommentsPage = 1;
    var wrap = document.querySelector('#eventDetailModal .sdm-interactions');
    if (!wrap) return;
    var lk = wrap.querySelector('.sdm-like-count'); if(lk) lk.textContent='0';
    var cm = wrap.querySelector('.sdm-comment-count'); if(cm) cm.textContent='0';
    var sh = wrap.querySelector('.sdm-share-count'); if(sh) sh.textContent='0';
    var btn = wrap.querySelector('.sdm-like-btn'); if(btn){btn.classList.remove('liked');btn.querySelector('i').className='far fa-heart';}
    var list = wrap.querySelector('.sdm-comments-list'); if(list) list.innerHTML='';
    var sec = wrap.querySelector('.sdm-comments-section'); if(sec) sec.style.display='none';
    var mb = wrap.querySelector('.sdm-load-more'); if(mb) mb.style.display='none';
    var hdr = wrap.querySelector('.sdm-comments-header'); if(hdr) hdr.innerHTML='<i class="fas fa-comments"></i> Comments <span>0</span>';
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.sdm-like-btn') && e.target.closest('#eventDetailModal')) {
        e.preventDefault();
        var s = sdmCurrentEvent;
        fetch('api.php?action=toggle_like', {method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:s.type,content_id:s.id,visitor_hash:sdmVisitorHash()})})
        .then(function(r){return r.json();}).then(function(res){
            if(!res.success){if(res.message)alert(res.message);return;}
            var wrap = document.querySelector('#eventDetailModal .sdm-interactions');
            var ct = wrap.querySelector('.like-count'); if(ct) ct.textContent=res.count||0;
            var btn = wrap.querySelector('.sdm-like-btn');
            btn.classList.toggle('liked',res.liked);
            btn.querySelector('i').className = res.liked ? 'fas fa-heart' : 'far fa-heart';
        });
        return;
    }
    if (e.target.closest('.sdm-toggle-comments') && e.target.closest('#eventDetailModal')) {
        e.preventDefault();
        var sec = document.querySelector('#eventDetailModal .sdm-comments-section');
        if(sec){ sec.style.display = sec.style.display==='none'?'block':'none'; if(sec.style.display!=='none' && sec.dataset.loaded!=='1'){sdmLoadComments(1);sec.dataset.loaded='1';} }
        return;
    }
    if (e.target.closest('.sdm-share-btn') && e.target.closest('#eventDetailModal')) {
        e.preventDefault();
        var dd = e.target.closest('.sdm-share-menu').querySelector('.sdm-share-dropdown');
        document.querySelectorAll('#eventDetailModal .sdm-share-dropdown.show').forEach(function(d){if(d!==dd)d.classList.remove('show');});
        dd.classList.toggle('show');
        return;
    }
    if (!e.target.closest('.sdm-share-dropdown') && !e.target.closest('.sdm-share-btn')) { document.querySelectorAll('#eventDetailModal .sdm-share-dropdown.show').forEach(function(d){d.classList.remove('show');}); }
    if (e.target.closest('.sdm-share-link') && e.target.closest('#eventDetailModal')) {
        e.preventDefault();
        var link = e.target.closest('.sdm-share-link');
        var platform = link.dataset.platform;
        var shareText = document.title, shareUrl = window.location.href, url = '';
        if(platform==='whatsapp') url='https://wa.me/?text='+encodeURIComponent(shareText+' '+shareUrl);
        else if(platform==='facebook') url='https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(shareUrl);
        else if(platform==='twitter') url='https://twitter.com/intent/tweet?url='+encodeURIComponent(shareUrl)+'&text='+encodeURIComponent(shareText);
        else if(platform==='telegram') url='https://t.me/share/url?url='+encodeURIComponent(shareUrl)+'&text='+encodeURIComponent(shareText);
        else if(platform==='link'){navigator.clipboard.writeText(shareUrl).then(function(){sdmShowCopied();});}
        if(url) window.open(url,'_blank','width=600,height=400');
        fetch('api.php?action=record_share',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:sdmCurrentEvent.type,content_id:sdmCurrentEvent.id,platform:platform})})
        .then(function(r){return r.json();}).then(function(res){if(res.success){var sh=document.querySelector('#eventDetailModal .sdm-share-count');if(sh)sh.textContent=res.count||0;}});
        document.querySelectorAll('#eventDetailModal .sdm-share-dropdown.show').forEach(function(d){d.classList.remove('show');});
        return;
    }
    if (e.target.closest('.sdm-comment-delete') && e.target.closest('#eventDetailModal')) {
        e.preventDefault();
        if(!confirm('Delete this comment?'))return;
        var cid = e.target.closest('.sdm-comment-delete').dataset.id;
        fetch('api.php?action=delete_comment',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({comment_id:cid})})
        .then(function(r){return r.json();}).then(function(res){if(res.success){sdmLoadComments(1);sdmRefreshCounts();}else{alert(res.message||'Failed.');}});
        return;
    }
    if (e.target.closest('.sdm-load-more') && e.target.closest('#eventDetailModal')) { e.preventDefault(); sdmCommentsPage++; sdmLoadComments(sdmCommentsPage); return; }
});
document.addEventListener('submit', function(e) {
    var form = e.target.closest('#eventDetailModal .sdm-comment-form-form');
    if (!form) return;
    e.preventDefault();
    if (!sdmCurrentEvent.loggedIn) { alert('Please log in to comment.'); return; }
    var ta = form.querySelector('textarea'), btn = form.querySelector('.btn-post'), text = ta.value.trim();
    if (!text) return;
    btn.disabled = true;
    fetch('api.php?action=add_comment',{method:'POST',headers:sdmHeaders(),body:JSON.stringify({content_type:sdmCurrentEvent.type,content_id:sdmCurrentEvent.id,comment:text})})
    .then(function(r){return r.json();}).then(function(res){btn.disabled=false;if(!res.success){alert(res.message||'Failed.');return;}ta.value='';var ct=form.querySelector('.char-count');if(ct)ct.textContent='0/2000';sdmLoadComments(1);sdmRefreshCounts();}).catch(function(){btn.disabled=false;});
});
document.addEventListener('input', function(e) { if(e.target.closest('#eventDetailModal .sdm-comment-form textarea')){var ct=e.target.closest('.sdm-comment-form').querySelector('.char-count');if(ct)ct.textContent=e.target.value.length+'/2000';} });
function sdmShowCopied(){var t=document.createElement('div');t.className='sdm-copied-toast';t.textContent='Link copied!';document.body.appendChild(t);setTimeout(function(){t.remove();},2000);}

function showEventDetail(data) {
    sdmResetInteractions();
    if (data.id) sdmCurrentEvent.id = data.id;
    document.getElementById('eventDetailTitle').textContent = data.title;
    var html = '<div class="row g-4">';
    html += '<div class="col-md-6"><div class="d-flex align-items-center gap-3 mb-3"><div style="width:48px;height:48px;background:rgba(14,165,233,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-calendar text-primary"></i></div><div><small class="text-muted" style="font-family:Montserrat,sans-serif;">Date</small><div class="fw-semibold">' + data.date + '</div></div></div></div>';
    if (data.time) html += '<div class="col-md-6"><div class="d-flex align-items-center gap-3 mb-3"><div style="width:48px;height:48px;background:rgba(251,191,36,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-clock" style="color:#fbbf24;"></i></div><div><small class="text-muted" style="font-family:Montserrat,sans-serif;">Time</small><div class="fw-semibold">' + data.time + '</div></div></div></div>';
    if (data.location) html += '<div class="col-md-6"><div class="d-flex align-items-center gap-3 mb-3"><div style="width:48px;height:48px;background:rgba(14,165,233,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-map-marker-alt text-primary"></i></div><div><small class="text-muted" style="font-family:Montserrat,sans-serif;">Location</small><div class="fw-semibold">' + data.location + '</div></div></div></div>';
    if (data.speaker) html += '<div class="col-md-6"><div class="d-flex align-items-center gap-3 mb-3"><div style="width:48px;height:48px;background:rgba(251,191,36,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-tie" style="color:#fbbf24;"></i></div><div><small class="text-muted" style="font-family:Montserrat,sans-serif;">Speaker</small><div class="fw-semibold">' + data.speaker + '</div></div></div></div>';
    html += '</div>';
    if (data.description) html += '<hr><div class="mt-3" style="line-height:1.8;color:#475569;">' + data.description + '</div>';
    if (data.registration_url) html += '<div class="mt-4"><a href="' + data.registration_url + '" target="_blank" class="btn btn-gold"><i class="fas fa-user-plus me-2"></i>Register Now</a></div>';
    document.getElementById('eventDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
    sdmRefreshCounts();
}

let calendarActive = false;
function toggleView() {
    calendarActive = !calendarActive;
    document.getElementById('gridView').style.display = calendarActive ? 'none' : 'block';
    document.getElementById('calendarView').style.display = calendarActive ? 'block' : 'none';
    document.getElementById('viewToggle').innerHTML = calendarActive ? '<i class="fas fa-th me-1"></i> Grid View' : '<i class="fas fa-calendar me-1"></i> Calendar View';
    if (calendarActive) renderCalendar();
}

function renderCalendar() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = now.getDate();
    let html = '<div class="cal-header"><h5>' + monthNames[month] + ' ' + year + '</h5></div>';
    html += '<div class="row g-1">';
    dayNames.forEach(d => { html += '<div class="col cal-day cal-day-header">' + d + '</div>'; });
    for (let i = 0; i < firstDay; i++) html += '<div class="col cal-day"></div>';
    for (let d = 1; d <= daysInMonth; d++) {
        let cls = 'col cal-day';
        if (d === today) cls += ' today';
        html += '<div class="' + cls + '">' + d + '</div>';
    }
    html += '</div>';
    document.getElementById('calendarGrid').innerHTML = html;
}

(function() {
    const bar = document.getElementById('countdownBar');
    if (!bar) return;
    const eventDateStr = bar.getAttribute('data-event-date');
    const eventDate = new Date(eventDateStr.replace(' ', 'T'));
    function update() {
        const now = new Date();
        let diff = eventDate - now;
        if (diff <= 0) { document.getElementById('cd-days').textContent = '00'; document.getElementById('cd-hours').textContent = '00'; document.getElementById('cd-mins').textContent = '00'; document.getElementById('cd-secs').textContent = '00'; return; }
        const days = Math.floor(diff / 86400000); diff %= 86400000;
        const hours = Math.floor(diff / 3600000); diff %= 3600000;
        const mins = Math.floor(diff / 60000); diff %= 60000;
        const secs = Math.floor(diff / 1000);
        document.getElementById('cd-days').textContent = String(days).padStart(2, '0');
        document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('cd-mins').textContent = String(mins).padStart(2, '0');
        document.getElementById('cd-secs').textContent = String(secs).padStart(2, '0');
    }
    update();
    setInterval(update, 1000);
})();
</script>

<?php include 'components/footer.php'; ?>
