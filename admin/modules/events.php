<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdminAuth();

$db = Database::getInstance();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();

    switch ($action) {
        case 'create':
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $event_time = $_POST['event_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $venue = trim($_POST['venue'] ?? '');
            $speaker = trim($_POST['speaker'] ?? '');
            $max_attendees = (int)($_POST['max_attendees'] ?? 0);
            $registration_url = trim($_POST['registration_url'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $status = in_array($_POST['status'] ?? '', ['upcoming', 'ongoing', 'completed', 'cancelled']) ? $_POST['status'] : 'upcoming';

            if (empty($title) || empty($event_date)) {
                if ($isAjax) jsonError('Title and event date are required');
                setFlash('error', 'Title and event date are required');
                redirect(BASE_URL . '/admin/modules/events.php?action=create');
            }

            $banner_image = '';
            if (!empty($_FILES['banner_image']['name'])) {
                $uploaded = uploadFile($_FILES['banner_image'], 'events', ALLOWED_IMAGE_TYPES);
                if ($uploaded) $banner_image = $uploaded;
            }

            $id = $db->insert('events', [
                'title' => $title,
                'slug' => slugify($title),
                'description' => $description,
                'event_date' => $event_date,
                'event_time' => $event_time,
                'end_time' => $end_time,
                'location' => $location,
                'venue' => $venue,
                'speaker' => $speaker,
                'banner_image' => $banner_image,
                'max_attendees' => $max_attendees,
                'registration_url' => $registration_url,
                'is_featured' => $is_featured,
                'status' => $status,
                'created_by' => $_SESSION['admin_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            logActivity($db, 'created', 'events', $_SESSION['admin_id'], "Created event: {$title}");
            if ($isAjax) jsonSuccess(['id' => $id], 'Event created successfully');
            setFlash('success', 'Event created successfully');
            redirect(BASE_URL . '/admin/modules/events.php');
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $event_date = $_POST['event_date'] ?? '';
            $event_time = $_POST['event_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $venue = trim($_POST['venue'] ?? '');
            $speaker = trim($_POST['speaker'] ?? '');
            $max_attendees = (int)($_POST['max_attendees'] ?? 0);
            $registration_url = trim($_POST['registration_url'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $status = in_array($_POST['status'] ?? '', ['upcoming', 'ongoing', 'completed', 'cancelled']) ? $_POST['status'] : 'upcoming';

            if (empty($id) || empty($title) || empty($event_date)) {
                if ($isAjax) jsonError('All required fields must be filled');
                setFlash('error', 'All required fields must be filled');
                redirect(BASE_URL . '/admin/modules/events.php');
            }

            $updateData = [
                'title' => $title,
                'slug' => slugify($title),
                'description' => $description,
                'event_date' => $event_date,
                'event_time' => $event_time,
                'end_time' => $end_time,
                'location' => $location,
                'venue' => $venue,
                'speaker' => $speaker,
                'max_attendees' => $max_attendees,
                'registration_url' => $registration_url,
                'is_featured' => $is_featured,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($_FILES['banner_image']['name'])) {
                $uploaded = uploadFile($_FILES['banner_image'], 'events', ALLOWED_IMAGE_TYPES);
                if ($uploaded) {
                    $old = $db->fetch("SELECT banner_image FROM events WHERE id = ?", [$id]);
                    if ($old && $old['banner_image']) deleteFile($old['banner_image']);
                    $updateData['banner_image'] = $uploaded;
                }
            }

            $db->update('events', $updateData, 'id = ?', [$id]);
            logActivity($db, 'updated', 'events', $_SESSION['admin_id'], "Updated event ID: {$id}");
            if ($isAjax) jsonSuccess([], 'Event updated successfully');
            setFlash('success', 'Event updated successfully');
            redirect(BASE_URL . '/admin/modules/events.php');
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $old = $db->fetch("SELECT banner_image FROM events WHERE id = ?", [$id]);
                if ($old && $old['banner_image']) deleteFile($old['banner_image']);
                $db->delete('events', 'id = ?', [$id]);
                logActivity($db, 'deleted', 'events', $_SESSION['admin_id'], "Deleted event ID: {$id}");
            }
            if ($isAjax) jsonSuccess([], 'Event deleted successfully');
            setFlash('success', 'Event deleted successfully');
            redirect(BASE_URL . '/admin/modules/events.php');
            break;
    }
}

$filterStatus = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = ADMIN_ITEMS_PER_PAGE;

$where = '1=1';
$params = [];

if ($filterStatus === 'upcoming') {
    $where .= " AND event_date >= ? AND status != 'cancelled'";
    $params[] = date('Y-m-d');
} elseif ($filterStatus === 'past') {
    $where .= " AND event_date < ?";
    $params[] = date('Y-m-d');
} elseif ($filterStatus && in_array($filterStatus, ['upcoming', 'ongoing', 'completed', 'cancelled'])) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagination = paginate('events', $db, $perPage, $page, $where, $params);
$events = $pagination['items'];
$total = $pagination['total'];
$totalPages = $pagination['total_pages'];

$editEvent = null;
if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editEvent = $db->fetch("SELECT * FROM events WHERE id = ?", [(int)$_GET['id']]);
}

displayFlash();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Event Management</h4>
    <button class="btn btn-primary" onclick="showCreateEventModal()"><i class="fas fa-plus me-1"></i> Add Event</button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="action" value="list">
            <div class="col-md-4">
                <label class="form-label">Filter by Time</label>
                <select name="status" class="form-select">
                    <option value="">All Events</option>
                    <option value="upcoming" <?= $filterStatus === 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                    <option value="past" <?= $filterStatus === 'past' ? 'selected' : '' ?>>Past</option>
                    <option value="ongoing" <?= $filterStatus === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                    <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/modules/events.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($editEvent): ?>
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Event</h5></div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $editEvent['id'] ?>">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required value="<?= sanitize($editEvent['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="6"><?= sanitize($editEvent['description']) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Event Date *</label>
                            <input type="date" name="event_date" class="form-control" required value="<?= $editEvent['event_date'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="event_time" class="form-control" value="<?= $editEvent['event_time'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="<?= $editEvent['end_time'] ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?= sanitize($editEvent['location']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Venue</label>
                            <input type="text" name="venue" class="form-control" value="<?= sanitize($editEvent['venue']) ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Speaker</label>
                        <input type="text" name="speaker" class="form-control" value="<?= sanitize($editEvent['speaker']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['upcoming', 'ongoing', 'completed', 'cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $editEvent['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Attendees</label>
                        <input type="number" name="max_attendees" class="form-control" value="<?= $editEvent['max_attendees'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Registration URL</label>
                        <input type="url" name="registration_url" class="form-control" value="<?= sanitize($editEvent['registration_url']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Banner Image</label>
                        <input type="file" name="banner_image" class="form-control" accept="image/*">
                        <?php if ($editEvent['banner_image']): ?>
                            <div class="mt-2">
                                <img src="<?= BASE_URL . '/' . $editEvent['banner_image'] ?>" class="img-thumbnail" style="max-height:100px">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" id="edit_featured" <?= $editEvent['is_featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="edit_featured">Featured Event</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Update Event</button>
                        <a href="<?= BASE_URL ?>/admin/modules/events.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($events)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">No events found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Event</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Speaker</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $item): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize(truncate($item['title'], 40)) ?></strong>
                                <?php if ($item['is_featured']): ?>
                                    <span class="badge bg-warning ms-1"><i class="fas fa-star"></i></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><i class="fas fa-calendar me-1"></i><?= formatDate($item['event_date']) ?></small>
                                <?php if ($item['event_time']): ?>
                                    <br><small><i class="fas fa-clock me-1"></i><?= date('g:i A', strtotime($item['event_time'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small><?= sanitize(truncate($item['location'] ?: $item['venue'] ?: 'N/A', 30)) ?></small></td>
                            <td><small><?= sanitize($item['speaker'] ?: 'N/A') ?></small></td>
                            <td>
                                <?php
                                $statusClasses = ['upcoming' => 'primary', 'ongoing' => 'success', 'completed' => 'secondary', 'cancelled' => 'danger'];
                                $cls = $statusClasses[$item['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $cls ?>"><?= ucfirst($item['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $item['id'] ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($filterStatus) ?>">Previous</a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filterStatus) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($filterStatus) ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <small class="text-muted">Showing <?= count($events) ?> of <?= $total ?> events</small>
        <?php endif; ?>
    </div>
</div>

<script>
function showCreateEventModal() {
    const existing = document.getElementById('createEventModal');
    if (existing) { new bootstrap.Modal(existing).show(); return; }
    const html = `
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Create Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Event Date *</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="event_time" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Speaker</label>
                                <input type="text" name="speaker" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Attendees</label>
                                <input type="number" name="max_attendees" class="form-control" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Registration URL</label>
                            <input type="url" name="registration_url" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Banner Image</label>
                            <input type="file" name="banner_image" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="c_feat">
                            <label class="form-check-label" for="c_feat">Featured Event</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>`;
    document.body.insertAdjacentHTML('beforeend', html);
    new bootstrap.Modal(document.getElementById('createEventModal')).show();
}
</script>
