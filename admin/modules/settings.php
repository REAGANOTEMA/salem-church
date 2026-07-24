<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    $section = $_POST['section'] ?? 'general';

    switch ($section) {
        case 'general':
            $settings = [
                'church_name' => trim($_POST['church_name'] ?? ''),
                'church_address' => trim($_POST['church_address'] ?? ''),
                'church_phone' => trim($_POST['church_phone'] ?? ''),
                'church_email' => trim($_POST['church_email'] ?? ''),
                'church_website' => trim($_POST['church_website'] ?? ''),
                'church_pastor' => trim($_POST['church_pastor'] ?? ''),
            ];
            foreach ($settings as $key => $value) {
                setSetting($key, $value);
            }
            logActivity($db, 'updated', 'settings', $_SESSION['admin_id'], 'Updated general settings');
            break;

        case 'uploads':
            if (!empty($_FILES['logo']['name'])) {
                $uploaded = uploadFile($_FILES['logo'], 'settings', ALLOWED_IMAGE_TYPES);
                if ($uploaded) setSetting('church_logo', $uploaded);
            }
            if (!empty($_FILES['favicon']['name'])) {
                $uploaded = uploadFile($_FILES['favicon'], 'settings', ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml']);
                if ($uploaded) setSetting('church_favicon', $uploaded);
            }
            logActivity($db, 'updated', 'settings', $_SESSION['admin_id'], 'Updated logo/favicon');
            break;

        case 'social':
            $socials = [
                'facebook_url' => trim($_POST['facebook_url'] ?? ''),
                'youtube_url' => trim($_POST['youtube_url'] ?? ''),
                'tiktok_url' => trim($_POST['tiktok_url'] ?? ''),
                'whatsapp_url' => trim($_POST['whatsapp_url'] ?? ''),
                'instagram_url' => trim($_POST['instagram_url'] ?? ''),
                'twitter_url' => trim($_POST['twitter_url'] ?? ''),
            ];
            foreach ($socials as $key => $value) {
                setSetting($key, $value);
            }
            logActivity($db, 'updated', 'settings', $_SESSION['admin_id'], 'Updated social media links');
            break;

        case 'services':
            $serviceTimes = trim($_POST['service_times'] ?? '');
            setSetting('service_times', $serviceTimes);
            logActivity($db, 'updated', 'settings', $_SESSION['admin_id'], 'Updated service times');
            break;

        case 'theme':
            $theme = [
                'primary_color' => trim($_POST['primary_color'] ?? '#0d6efd'),
                'secondary_color' => trim($_POST['secondary_color'] ?? '#6c757d'),
                'accent_color' => trim($_POST['accent_color'] ?? '#198754'),
            ];
            foreach ($theme as $key => $value) {
                setSetting($key, $value);
            }
            logActivity($db, 'updated', 'settings', $_SESSION['admin_id'], 'Updated theme colors');
            break;

        case 'seo':
            $seo = [
                'meta_title' => trim($_POST['meta_title'] ?? ''),
                'meta_description' => trim($_POST['meta_description'] ?? ''),
                'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
            ];
            foreach ($seo as $key => $value) {
                setSetting($key, $value);
            }
            logActivity($db, 'updated', 'settings', $_SESSION['admin_id'], 'Updated SEO settings');
            break;
    }

    setFlash('success', ucfirst($section) . ' settings saved successfully');
    redirect(BASE_URL . '/admin/modules/settings.php?section=' . $section);
}

$currentSection = $_GET['section'] ?? 'general';
$sections = [
    'general' => ['icon' => 'church', 'label' => 'Church Info'],
    'uploads' => ['icon' => 'upload', 'label' => 'Logo & Favicon'],
    'social' => ['icon' => 'share-alt', 'label' => 'Social Media'],
    'services' => ['icon' => 'clock', 'label' => 'Service Times'],
    'theme' => ['icon' => 'palette', 'label' => 'Theme Colors'],
    'seo' => ['icon' => 'search', 'label' => 'SEO Settings'],
];

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Site Settings</h4>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="list-group list-group-flush">
                <?php foreach ($sections as $key => $sec): ?>
                <a href="?section=<?= $key ?>" class="list-group-item list-group-item-action <?= $currentSection === $key ? 'active' : '' ?>">
                    <i class="fas fa-<?= $sec['icon'] ?> me-2"></i> <?= $sec['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">

                <?php if ($currentSection === 'general'): ?>
                <h5><i class="fas fa-church me-2"></i>Church Information</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="general">
                    <div class="mb-3">
                        <label class="form-label">Church Name</label>
                        <input type="text" name="church_name" class="form-control" value="<?= sanitize(getSetting('church_name', CHURCH_NAME)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pastor</label>
                        <input type="text" name="church_pastor" class="form-control" value="<?= sanitize(getSetting('church_pasteur', CHURCH_PASTOR)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="church_address" class="form-control" rows="2"><?= sanitize(getSetting('church_address', CHURCH_ADDRESS)) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="church_phone" class="form-control" value="<?= sanitize(getSetting('church_phone', CHURCH_PHONE)) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="church_email" class="form-control" value="<?= sanitize(getSetting('church_email', CHURCH_EMAIL)) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website</label>
                        <input type="url" name="church_website" class="form-control" value="<?= sanitize(getSetting('church_website', CHURCH_WEBSITE)) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>

                <?php elseif ($currentSection === 'uploads'): ?>
                <h5><i class="fas fa-image me-2"></i>Logo & Favicon</h5>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="uploads">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Church Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <?php $logo = getSetting('church_logo'); if ($logo): ?>
                                <div class="mt-2">
                                    <img src="<?= BASE_URL . '/' . $logo ?>" class="img-thumbnail" style="max-height:100px">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept="image/*">
                            <?php $favicon = getSetting('church_favicon'); if ($favicon): ?>
                                <div class="mt-2">
                                    <img src="<?= BASE_URL . '/' . $favicon ?>" class="img-thumbnail" style="max-height:50px">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>

                <?php elseif ($currentSection === 'social'): ?>
                <h5><i class="fas fa-share-alt me-2"></i>Social Media Links</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="social">
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-facebook me-1"></i> Facebook URL</label>
                        <input type="url" name="facebook_url" class="form-control" value="<?= sanitize(getSetting('facebook_url', FACEBOOK_URL)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-youtube me-1"></i> YouTube URL</label>
                        <input type="url" name="youtube_url" class="form-control" value="<?= sanitize(getSetting('youtube_url', YOUTUBE_URL)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-tiktok me-1"></i> TikTok URL</label>
                        <input type="url" name="tiktok_url" class="form-control" value="<?= sanitize(getSetting('tiktok_url', TIKTOK_URL)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-whatsapp me-1"></i> WhatsApp URL</label>
                        <input type="url" name="whatsapp_url" class="form-control" value="<?= sanitize(getSetting('whatsapp_url', WHATSAPP_URL)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-instagram me-1"></i> Instagram URL</label>
                        <input type="url" name="instagram_url" class="form-control" value="<?= sanitize(getSetting('instagram_url', '')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-twitter me-1"></i> Twitter/X URL</label>
                        <input type="url" name="twitter_url" class="form-control" value="<?= sanitize(getSetting('twitter_url', '')) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>

                <?php elseif ($currentSection === 'services'): ?>
                <h5><i class="fas fa-clock me-2"></i>Service Times</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="services">
                    <div class="mb-3">
                        <label class="form-label">Service Times (one per line)</label>
                        <textarea name="service_times" class="form-control" rows="6" placeholder="Sunday Worship: 10:00 AM&#10;Wednesday Bible Study: 7:00 PM&#10;Friday Prayer Meeting: 6:00 PM"><?= sanitize(getSetting('service_times')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>

                <?php elseif ($currentSection === 'theme'): ?>
                <h5><i class="fas fa-palette me-2"></i>Theme Colors</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="theme">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Primary Color</label>
                            <input type="color" name="primary_color" class="form-control form-control-color" value="<?= sanitize(getSetting('primary_color', '#0d6efd')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Secondary Color</label>
                            <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= sanitize(getSetting('secondary_color', '#6c757d')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Accent Color</label>
                            <input type="color" name="accent_color" class="form-control form-control-color" value="<?= sanitize(getSetting('accent_color', '#198754')) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>

                <?php elseif ($currentSection === 'seo'): ?>
                <h5><i class="fas fa-search me-2"></i>SEO Settings</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="seo">
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" value="<?= sanitize(getSetting('meta_title')) ?>" maxlength="70">
                        <div class="form-text">Recommended: 50-60 characters</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3" maxlength="160"><?= sanitize(getSetting('meta_description')) ?></textarea>
                        <div class="form-text">Recommended: 150-160 characters</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="<?= sanitize(getSetting('meta_keywords')) ?>" placeholder="church, worship, prayer, bible">
                        <div class="form-text">Separate keywords with commas</div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                </form>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
