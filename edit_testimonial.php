<?php
// EDIT TESTIMONIAL PAGE - Salem Dominion Ministries
// Edit existing testimonials

session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get testimonial ID
$testimonial_id = intval($_GET['id'] ?? 0);
if ($testimonial_id <= 0) {
    header('Location: admin_dashboard.php?section=testimonials');
    exit;
}

// Initialize variables
$success = '';
$error = '';
$testimonial = null;

// Get testimonial data
if ($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $testimonial_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $testimonial = $result->fetch_assoc();
        $stmt->close();
        
        if (!$testimonial) {
            $error = "Testimonial not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading testimonial: " . $e->getMessage();
    }
} else {
    $error = "Database connection failed.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $testimonial) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_testimonial') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');
        $testimonial_text = trim($_POST['testimonial']);
        $rating = intval($_POST['rating'] ?? 0);
        $is_approved = $_POST['is_approved'] ?? 0;
        
        if (empty($name) || empty($testimonial_text)) {
            $error = 'Please fill in all required fields.';
        } elseif ($rating < 1 || $rating > 5) {
            $error = 'Rating must be between 1 and 5.';
        } else {
            if ($conn) {
                try {
                    $stmt = $conn->prepare("UPDATE testimonials SET name = ?, email = ?, occupation = ?, testimonial = ?, rating = ?, is_approved = ? WHERE id = ?");
                    $stmt->bind_param("ssssiii", $name, $email, $occupation, $testimonial_text, $rating, $is_approved, $testimonial_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $success = 'Testimonial updated successfully!';
                    
                    // Refresh testimonial data
                    $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
                    $stmt->bind_param("i", $testimonial_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $testimonial = $result->fetch_assoc();
                    $stmt->close();
                    
                } catch (Exception $e) {
                    $error = 'Failed to update testimonial: ' . $e->getMessage();
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
    <title>Edit Testimonial - Salem Dominion Ministries</title>
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

        .rating-input {
            display: flex;
            gap: 10px;
            font-size: 1.5rem;
        }

        .rating-input label {
            color: var(--heavenly-gold);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rating-input input[type="radio"] {
            display: none;
        }

        .rating-input input[type="radio"]:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: var(--heavenly-gold);
        }

        .rating-input label {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-check {
            color: var(--snow-white);
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
        <a href="admin_dashboard.php?section=testimonials" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Testimonials
        </a>

        <div class="page-header">
            <h1 class="page-title">Edit Testimonial</h1>
            <p>Update testimonial content and information</p>
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

        <?php if ($testimonial): ?>
            <form method="POST">
                <input type="hidden" name="action" value="update_testimonial">
                
                <div class="form-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="name">Name *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($testimonial['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($testimonial['email'] ?? ''); ?>" 
                                       placeholder="Email address (optional)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="occupation">Occupation</label>
                                <input type="text" id="occupation" name="occupation" class="form-control" 
                                       value="<?php echo htmlspecialchars($testimonial['occupation'] ?? ''); ?>" 
                                       placeholder="Occupation or title (optional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rating *</label>
                                <div class="rating-input">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" 
                                               <?php echo $testimonial['rating'] == $i ? 'checked' : ''; ?>>
                                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="testimonial">Testimonial *</label>
                        <textarea id="testimonial" name="testimonial" class="form-control" rows="6" required><?php echo htmlspecialchars($testimonial['testimonial']); ?></textarea>
                        <small class="text-muted">The testimonial text as submitted by the person</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1" 
                                   <?php echo $testimonial['is_approved'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_approved">
                                <strong>Approved</strong> - Show this testimonial on the website
                            </label>
                        </div>
                        <small class="text-muted">Uncheck to hide this testimonial from public view</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-2"></i>
                    Update Testimonial
                </button>
            </form>
        <?php else: ?>
            <div class="text-center" style="color: var(--snow-white); padding: 3rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Testimonial not found or unable to load.</p>
                <a href="admin_dashboard.php?section=testimonials" class="btn-back">Back to Testimonials</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
