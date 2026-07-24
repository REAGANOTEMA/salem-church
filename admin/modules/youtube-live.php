<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'view';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

function extractYouTubeLiveEmbed(string $url): string {
    $url = trim($url);
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('/youtube\.com\/live\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'save':
            $youtube_url = trim($_POST['youtube_url'] ?? '');
            $title = trim($_POST['title'] ?? 'Live Stream');
            $is_live = isset($_POST['is_live']) ? 1 : 0;
            $is_enabled = isset($_POST['is_enabled']) ? 1 : 0;

            if (empty($youtube_url)) {
                if ($isAjax) jsonError('YouTube URL is required');
                setFlash('error', 'YouTube URL is required');
                redirect(BASE_URL . '/admin/dashboard.php?section=youtube-live');
            }

            $embed_url = extractYouTubeLiveEmbed($youtube_url);
            $existing = $db->fetch("SELECT id FROM youtube_live ORDER BY id DESC LIMIT 1");

            if ($existing) {
                $db->update('youtube_live', [
                    'youtube_url' => $youtube_url,
                    'embed_url' => $embed_url,
                    'title' => $title,
                    'is_live' => $is_live,
                    'is_enabled' => $is_enabled,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$existing['id']]);
                $id = $existing['id'];
            } else {
                $id = $db->insert('youtube_live', [
                    'youtube_url' => $youtube_url,
                    'embed_url' => $embed_url,
                    'title' => $title,
                    'is_live' => $is_live,
                    'is_enabled' => $is_enabled,
                    'created_by' => $_SESSION['admin_id'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            logActivity($db, 'updated', 'youtube_live', $_SESSION['admin_id'], "Updated YouTube Live: {$title}");
            if ($isAjax) jsonSuccess(['id' => $id], 'YouTube Live stream saved');
            setFlash('success', 'YouTube Live stream saved successfully');
            redirect(BASE_URL . '/admin/dashboard.php?section=youtube-live');
            break;

        case 'toggle_live':
            $stream = $db->fetch("SELECT id, is_live FROM youtube_live ORDER BY id DESC LIMIT 1");
            if ($stream) {
                $newVal = $stream['is_live'] ? 0 : 1;
                $db->update('youtube_live', ['is_live' => $newVal, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$stream['id']]);
                logActivity($db, 'updated', 'youtube_live', $_SESSION['admin_id'], "Toggled live status to " . ($newVal ? 'LIVE' : 'OFF'));
            }
            if ($isAjax) jsonSuccess([], 'Live status toggled');
            setFlash('success', 'Live status updated');
            redirect(BASE_URL . '/admin/dashboard.php?section=youtube-live');
            break;

        case 'toggle_enabled':
            $stream = $db->fetch("SELECT id, is_enabled FROM youtube_live ORDER BY id DESC LIMIT 1");
            if ($stream) {
                $newVal = $stream['is_enabled'] ? 0 : 1;
                $db->update('youtube_live', ['is_enabled' => $newVal, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$stream['id']]);
                logActivity($db, 'updated', 'youtube_live', $_SESSION['admin_id'], "Toggled enabled to " . ($newVal ? 'ON' : 'OFF'));
            }
            if ($isAjax) jsonSuccess([], 'Enabled status toggled');
            setFlash('success', 'Enabled status updated');
            redirect(BASE_URL . '/admin/dashboard.php?section=youtube-live');
            break;

        case 'disable':
            $stream = $db->fetch("SELECT id FROM youtube_live ORDER BY id DESC LIMIT 1");
            if ($stream) {
                $db->update('youtube_live', [
                    'is_live' => 0,
                    'is_enabled' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$stream['id']]);
                logActivity($db, 'updated', 'youtube_live', $_SESSION['admin_id'], 'Disabled YouTube Live stream');
            }
            if ($isAjax) jsonSuccess([], 'Stream disabled');
            setFlash('success', 'Live stream disabled');
            redirect(BASE_URL . '/admin/dashboard.php?section=youtube-live');
            break;
    }
}

$stream = $db->fetch("SELECT * FROM youtube_live ORDER BY id DESC LIMIT 1");

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fab fa-youtube me-2 text-danger"></i>YouTube Live Management</h4>
</div>

<?php if ($stream): ?>
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <span class="badge bg-<?= $stream['is_live'] ? 'danger' : 'secondary' ?> fs-6 p-2">
                        <i class="fas fa-circle <?= $stream['is_live'] ? 'fa-beat' : '' ?> me-1"></i>
                        <?= $stream['is_live'] ? 'LIVE NOW' : 'OFF AIR' ?>
                    </span>
                </div>
                <h5 class="mb-1"><?= sanitize($stream['title'] ?: 'No Title') ?></h5>
                <small class="text-muted">Last updated: <?= timeAgo($stream['updated_at']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <span class="badge bg-<?= $stream['is_enabled'] ? 'success' : 'warning' ?> fs-6 p-2">
                        <i class="fas fa-<?= $stream['is_enabled'] ? 'check-circle' : 'pause-circle' ?> me-1"></i>
                        <?= $stream['is_enabled'] ? 'ENABLED' : 'DISABLED' ?>
                    </span>
                </div>
                <div class="d-grid gap-2">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle_enabled">
                        <button type="submit" class="btn btn-<?= $stream['is_enabled'] ? 'outline-warning' : 'outline-success' ?> w-100">
                            <i class="fas fa-<?= $stream['is_enabled'] ? 'pause' : 'play' ?> me-1"></i>
                            <?= $stream['is_enabled'] ? 'Disable Stream' : 'Enable Stream' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-broadcast-tower fa-2x text-<?= $stream['is_live'] ? 'danger' : 'muted' ?>"></i>
                </div>
                <h6>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle_live">
                        <button type="submit" class="btn btn-<?= $stream['is_live'] ? 'outline-secondary' : 'danger' ?> w-100">
                            <i class="fas fa-<?= $stream['is_live'] ? 'stop' : 'circle' ?> me-1"></i>
                            <?= $stream['is_live'] ? 'Go Off Air' : 'Go Live Now' ?>
                        </button>
                    </form>
                    <form method="POST" onsubmit="return confirm('This will disable the stream completely. Continue?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="disable">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-power-off me-1"></i> Disable Stream
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-cog me-2"></i>Stream Settings</h5></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="save">
                    <div class="mb-3">
                        <label class="form-label">YouTube Live URL *</label>
                        <input type="url" name="youtube_url" class="form-control" required
                               value="<?= sanitize($stream['youtube_url'] ?? '') ?>"
                               placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/...">
                        <div class="form-text">Paste the YouTube live stream URL. It will auto-generate the embed URL.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stream Title</label>
                        <input type="text" name="title" class="form-control"
                               value="<?= sanitize($stream['title'] ?? '') ?>"
                               placeholder="e.g. Sunday Worship Service">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_live" class="form-check-input" id="is_live" <?= ($stream['is_live'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_live">Currently Live</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_enabled" class="form-check-input" id="is_enabled" <?= ($stream['is_enabled'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_enabled">Enable on Website</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Stream Settings</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview</h5></div>
            <div class="card-body p-0">
                <?php if (!empty($stream['embed_url'])): ?>
                    <div class="ratio ratio-16x9">
                        <iframe src="<?= sanitize($stream['embed_url']) ?>" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" style="border:0;"></iframe>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fab fa-youtube fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No stream URL configured</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h6><i class="fas fa-info-circle me-1"></i> Instructions</h6>
                <ol class="small mb-0">
                    <li>Set up your live stream on YouTube</li>
                    <li>Copy the stream URL</li>
                    <li>Paste it in the URL field above</li>
                    <li>Enable the stream to show on the website</li>
                    <li>Toggle "Currently Live" when you start streaming</li>
                </ol>
            </div>
        </div>
    </div>
</div>
