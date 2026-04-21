<?php
// EDIT EVENT PAGE - Salem Dominion Ministries
// Edit existing events

session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get event ID
$event_id = intval($_GET['id'] ?? 0);
if ($event_id <= 0) {
    header('Location: admin_dashboard.php?section=events');
    exit;
}

// Initialize variables
$success = '';
$error = '';
$event = null;

// Get event data
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        $stmt->close();
        
        if (!$event) {
            $error = "Event not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading event: " . $e->getMessage();
    }
} else {
    $error = "Database connection failed.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_event') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $location = trim($_POST['location']);
        $status = $_POST['status'];
        $category = $_POST['category'] ?? 'service';
        
        if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
            $error = 'Please fill in all required fields.';
        } else {
            if ($conn) {
                try {
                    $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ?, status = ?, category = ? WHERE id = ?");
                    $stmt->bind_param("sssssssi", $title, $description, $event_date, $event_time, $location, $status, $category, $event_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $success = 'Event updated successfully!';
                    
                    // Refresh event data
                    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
                    $stmt->bind_param("i", $event_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $event = $result->fetch_assoc();
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $error = 'Failed to update event: ' . $e->getMessage();
                }
            } else {
                $error = 'Database connection required.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Salem Dominion Ministries</title>
    <link rel="icon" href="public/logo-icon.jpeg">
    <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --midnight-blue: #0f172a;
            --ocean-blue: #0ea5e9;
            --heavenly-gold: #fbbf24;
            --snow-white: #ffffff;
            --pearl-white: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--midnight-blue) 0%, #1e293b 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .edit-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--snow-white);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--heavenly-gold);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: var(--snow-white);
        }

        .form-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-label {
            color: var(--snow-white);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--snow-white);
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--heavenly-gold);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.25);
            color: var(--snow-white);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--heavenly-gold), #f59e0b);
            color: var(--midnight-blue);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(251, 191, 36, 0.3);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 1rem;
                margin: 0;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <a href="admin_dashboard.php?section=events" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Events
        </a>

        <div class="page-header">
            <h1 class="page-title">Edit Event</h1>
            <p>Update event details and scheduling</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($event): ?>
            <form method="POST">
                <input type="hidden" name="action" value="update_event">
                
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="title">Event Title *</label>
                                <input type="text" id="title" name="title" class="form-control" 
                                       value="<?php echo htmlspecialchars($event['title']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="event_date">Event Date *</label>
                                <input type="date" id="event_date" name="event_date" class="form-control" 
                                       value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="event_time">Event Time *</label>
                                <input type="time" id="event_time" name="event_time" class="form-control" 
                                       value="<?php echo htmlspecialchars($event['event_time'] ?? '09:00'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="location">Location *</label>
                                <input type="text" id="location" name="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($event['location']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="upcoming" <?php echo $event['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="ongoing" <?php echo $event['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                                    <option value="completed" <?php echo $event['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $event['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="category">Category</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="service" <?php echo $event['category'] === 'service' ? 'selected' : ''; ?>>Service</option>
                                    <option value="conference" <?php echo $event['category'] === 'conference' ? 'selected' : ''; ?>>Conference</option>
                                    <option value="workshop" <?php echo $event['category'] === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                                    <option value="outreach" <?php echo $event['category'] === 'outreach' ? 'selected' : ''; ?>>Outreach</option>
                                    <option value="fellowship" <?php echo $event['category'] === 'fellowship' ? 'selected' : ''; ?>>Fellowship</option>
                                    <option value="youth" <?php echo $event['category'] === 'youth' ? 'selected' : ''; ?>>Youth</option>
                                    <option value="children" <?php echo $event['category'] === 'children' ? 'selected' : ''; ?>>Children</option>
                                    <option value="special" <?php echo $event['category'] === 'special' ? 'selected' : ''; ?>>Special Event</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>
                    Update Event
                </button>
            </form>
        <?php else: ?>
            <div class="text-center" style="color: var(--snow-white); padding: 3rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Event not found or unable to load.</p>
                <a href="admin_dashboard.php?section=events" class="btn-back">Back to Events</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
