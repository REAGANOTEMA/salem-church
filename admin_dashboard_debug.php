<?php
session_start();
require_once 'db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Admin Dashboard Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .debug-section { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 10px; margin: 5px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #d1ecf1; color: #0c5460; }
        h2 { color: #333; margin: 0 0 15px 0; }
        .nav-item { display: block; padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 4px; text-decoration: none; color: #333; }
        .nav-item:hover { background: #e9ecef; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Admin Dashboard Debug</h1>
    
    <div class='debug-section'>
        <h2>Session Status</h2>
        <div class='status info'>
            <strong>Session Active:</strong> <?php echo isset($_SESSION['admin_logged_in']) ? 'Yes' : 'No'; ?><br>
            <strong>Admin Username:</strong> <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Not set'); ?><br>
            <strong>Admin Name:</strong> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Not set'); ?>
        </div>
    </div>
    
    <div class='debug-section'>
        <h2>Database Connection</h2>
        <?php
        $conn = getConnection();
        if ($conn) {
            echo "<div class='status success'>";
            echo "<strong>✅ Database Connected</strong><br>";
            echo "Host: " . $conn->host_info . "<br>";
            echo "Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";
            echo "Server Info: " . $conn->server_info . "<br>";
            echo "</div>";
            
            // Test tables
            echo "<h3>Database Tables:</h3>";
            $tables_result = $conn->query("SHOW TABLES");
            if ($tables_result && $tables_result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Table Name</th></tr>";
                while ($table = $tables_result->fetch_row()) {
                    echo "<tr><td>" . htmlspecialchars($table[0]) . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='status warning'>No tables found</div>";
            }
            
            // Test admin users
            echo "<h3>Admin Users:</h3>";
            $admin_result = $conn->query("SELECT username, email, full_name FROM admin_users LIMIT 5");
            if ($admin_result && $admin_result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Username</th><th>Email</th><th>Full Name</th></tr>";
                while ($admin = $admin_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($admin['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($admin['full_name']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='status error'>No admin users found</div>";
            }
            
            $conn->close();
        } else {
            echo "<div class='status error'>";
            echo "<strong>❌ Database Connection Failed</strong><br>";
            echo "Please check:<br>";
            echo "1. XAMPP MySQL service is running<br>";
            echo "2. Database credentials are correct<br>";
            echo "3. Database exists<br>";
            echo "</div>";
        }
        ?>
    </div>
    
    <div class='debug-section'>
        <h2>File System Check</h2>
        <?php
        $files_to_check = [
            'admin_sections/dashboard.php',
            'admin_sections/events.php',
            'admin_sections/gallery.php',
            'admin_sections/sermons.php',
            'admin_sections/news.php',
            'admin_sections/testimonials.php',
            'admin_sections/messages.php'
        ];
        
        echo "<table>";
        echo "<tr><th>File</th><th>Status</th></tr>";
        
        foreach ($files_to_check as $file) {
            $exists = file_exists($file);
            $readable = is_readable($file);
            $status = $exists && $readable ? '✅ OK' : '❌ Missing';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file) . "</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>
    
    <div class='debug-section'>
        <h2>Navigation Test</h2>
        <div class='status info'>
            <p><strong>Current URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?></p>
            <p><strong>Active Section:</strong> <?php echo htmlspecialchars($_GET['section'] ?? 'dashboard'); ?></p>
            <p><strong>Request Method:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'Unknown'); ?></p>
        </div>
    </div>
    
    <div class='debug-section'>
        <h2>Quick Actions</h2>
        <a href='admin_dashboard.php?section=dashboard' class='nav-item'>📊 Dashboard</a><br>
        <a href='admin_dashboard.php?section=events' class='nav-item'>📅 Events</a><br>
        <a href='admin_dashboard.php?section=gallery' class='nav-item'>🖼️ Gallery</a><br>
        <a href='admin_dashboard.php?section=sermons' class='nav-item'>🎤️ Sermons</a><br>
        <a href='admin_dashboard.php?section=news' class='nav-item'>📰 News</a><br>
        <a href='admin_dashboard.php?section=testimonials' class='nav-item'>💬 Testimonials</a><br>
        <a href='admin_dashboard.php?section=messages' class='nav-item'>✉️ Messages</a><br>
        <a href='admin_dashboard.php?section=users' class='nav-item'>👥 Users</a><br>
    </div>
    
    <div class='debug-section'>
        <h2>Login Test</h2>
        <a href='admin/welcome.php' class='btn'>🔐 Test Admin Login</a>
    </div>
    
    <div class='debug-section'>
        <h2>Database Test</h2>
        <a href='db_connection_test.php' class='btn'>🔗 Test Database Connection</a>
    </div>
    
    <div class='debug-section'>
        <h2>Mobile Test</h2>
        <p><strong>Device:</strong> 
        <?php 
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent)) {
            echo '📱 Mobile Device';
        } elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
            echo '📱 Tablet Device';
        } else {
            echo '🖥️ Desktop Device';
        }
        ?>
        </p>
        <p><strong>Screen Width:</strong> <span id='screen-width'>Detecting...</span></p>
    </div>
    
    <script>
        // Detect screen width
        function updateScreenSize() {
            document.getElementById('screen-width').textContent = window.innerWidth + 'px';
        }
        
        updateScreenSize();
        window.addEventListener('resize', updateScreenSize);
    </script>
</body>
</html>";
?>
