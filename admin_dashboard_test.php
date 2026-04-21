<?php
session_start();
require_once './db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/welcome.php');
    exit;
}

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// Initialize database connection
$conn = getConnection();
if (!$conn) {
    $error = "Database connection failed. Please try again later.";
}

// Simple test page
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard Test</title>
    <style>
        body { font-family: Arial; background: #0f172a; color: white; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .status { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 10px 0; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard Test</h1>
            <p>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</p>
        </div>
        
        <div class="status">
            <h3>Database Connection Status:</h3>
            <?php if ($conn): ?>
                <p class="success">Database connected successfully!</p>
                <?php
                // Test basic query
                $result = $conn->query("SELECT COUNT(*) as count FROM users");
                if ($result) {
                    $count = $result->fetch_assoc()['count'];
                    echo "<p>Users table: $count records</p>";
                }
                $conn->close();
                ?>
            <?php else: ?>
                <p class="error">Database connection failed</p>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="status">
            <h3>Session Status:</h3>
            <p>Admin logged in: <?php echo isset($_SESSION['admin_logged_in']) ? 'Yes' : 'No'; ?></p>
            <p>Username: <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Not set'); ?></p>
        </div>
        
        <div class="status">
            <p><a href="admin_sections/dashboard.php">Go to Full Dashboard</a></p>
            <p><a href="admin/welcome.php">Logout</a></p>
        </div>
    </div>
</body>
</html>
