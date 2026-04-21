<?php
// Get global database connection
$conn = $GLOBALS['admin_db_connection'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_event':
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $event_date = $_POST['event_date'] ?? '';
                $event_time = $_POST['event_time'] ?? '';
                $location = $_POST['location'] ?? '';
                $category = $_POST['category'] ?? '';
                
                if (!empty($title) && !empty($event_date)) {
                    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, location, category, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    if ($stmt) {
                        $stmt->bind_param("ssssss", $title, $description, $event_date, $event_time, $location, $category);
                        $stmt->execute();
                        $success = "Event added successfully!";
                    }
                }
                break;
                
            case 'edit_event':
                $id = $_POST['id'] ?? '';
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $event_date = $_POST['event_date'] ?? '';
                $event_time = $_POST['event_time'] ?? '';
                $location = $_POST['location'] ?? '';
                $category = $_POST['category'] ?? '';
                
                if (!empty($id) && !empty($title)) {
                    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, category=? WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("ssssssi", $title, $description, $event_date, $event_time, $location, $category, $id);
                        $stmt->execute();
                        $success = "Event updated successfully!";
                    }
                }
                break;
                
            case 'delete_event':
                $id = $_POST['id'] ?? '';
                if (!empty($id)) {
                    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $success = "Event deleted successfully!";
                    }
                }
                break;
        }
    }
}

// Get events from database
$events = [];
if ($conn) {
    try {
        $result = $conn->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 20");
        if ($result) {
            $events = $result->fetch_all(MYSQLI_ASSOC);
        }
    } catch (Exception $e) {
        $error = 'Failed to fetch events: ' . $e->getMessage();
    }
}
?>

<?php if (isset($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="content-header">
    <h1 class="page-title"><img src="<?php echo LOGO_PATH; ?>" alt="Salem Dominion Ministries" style="width: 30px; height: 30px; margin-right: 10px;">Event Management</h1>
    <p class="page-subtitle">Create and manage church events with scheduling and details</p>
</div>

<!-- Add New Event Form -->
<div class="form-section">
    <h2 class="form-title">
        <i class="fas fa-plus-circle"></i>
        Add New Event
    </h2>
    
    <form method="POST">
        <input type="hidden" name="action" value="add_event">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="title" class="form-label">Event Title *</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="location" class="form-label">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="event_date" class="form-label">Event Date *</label>
                    <input type="date" id="event_date" name="event_date" class="form-control" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="event_time" class="form-label">Event Time</label>
                    <input type="time" id="event_time" name="event_time" class="form-control">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Event Description *</label>
            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
        </div>
        
        <button type="submit" class="btn-submit">
            <i class="fas fa-plus"></i> Create Event
        </button>
    </form>
</div>

<!-- Existing Events -->
<div class="data-table">
    <div class="table-header">
        <h3 class="section-title">
            <i class="fas fa-list"></i>
            Existing Events
        </h3>
        <span class="badge bg-primary"><?php echo count($events); ?> Total</span>
    </div>
    
    <?php if (empty($events)): ?>
        <div class="empty-state">
            <i class="fas fa-calendar fa-3x mb-3"></i>
            <h4>No Events Found</h4>
            <p>Start by creating your first event using the form above.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($event['location']); ?></small>
                            </td>
                            <td>
                                <i class="fas fa-calendar-day"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                            </td>
                            <td>
                                <i class="fas fa-clock"></i> <?php echo $event['event_time'] ?: 'All Day'; ?>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($event['status']) {
                                        'upcoming' => 'info',
                                        'ongoing' => 'success', 
                                        'completed' => 'secondary',
                                        'cancelled' => 'danger',
                                        default => 'warning'
                                    }; 
                                ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-view" onclick="viewEvent(<?php echo $event['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editEvent(<?php echo $event['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_item">
                                        <input type="hidden" name="item_type" value="event">
                                        <input type="hidden" name="item_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-title {
    color: #0f172a;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-title i {
    color: #fbbf24;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #0f172a;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.btn-submit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-submit:hover {
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
}

.data-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table {
    margin: 0;
}

.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #0f172a;
    border: none;
    padding: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.9rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
    border-color: #f1f5f9;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
}

.btn-view {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #0f172a;
}

.btn-edit {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-action i {
    margin-right: 6px;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #64748b;
}

.empty-state i {
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.bg-primary { background: #0ea5e9; }
.bg-info { background: #3b82f6; }
.bg-success { background: #10b981; }
.bg-warning { background: #f59e0b; }
.bg-danger { background: #ef4444; }
.bg-secondary { background: #64748b; }

.text-muted {
    color: #64748b;
    font-size: 0.875rem;
}

.table-responsive {
    overflow-x: auto;
}

@media (max-width: 768px) {
    .form-section {
        padding: 1rem;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function viewEvent(id) {
    // Implement view event functionality
    alert('View event ID: ' + id);
}

function editEvent(id) {
    // Implement edit event functionality
    alert('Edit event ID: ' + id);
}
</script>
