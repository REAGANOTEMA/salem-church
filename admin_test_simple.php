<?php
session_start();
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Admin Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 10px; margin: 5px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #d1ecf1; color: #0c5460; }
        .nav-link { display: block; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; margin: 5px 0; border-radius: 4px; }
        .nav-link:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Admin Dashboard Test</h1>
    
    <div class='test-section'>
        <h2>Session Status</h2>
        <div class='status info'>
            <strong>Session Active:</strong> <?php echo isset($_SESSION['admin_logged_in']) ? 'YES' : 'NO'; ?><br>
            <strong>Admin Username:</strong> <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Not Set'); ?><br>
            <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
    
    <div class='test-section'>
        <h2>File Access Test</h2>
        <?php
        $admin_files = [
            'admin_dashboard.php',
            'admin_sections/dashboard.php',
            'admin_sections/events.php',
            'admin_sections/gallery.php',
            'admin_sections/sermons.php',
            'admin_sections/news.php',
            'admin_sections/testimonials.php',
            'admin_sections/messages.php'
        ];
        
        echo "<table>";
        echo "<tr><th>File</th><th>Exists</th><th>Readable</th><th>Test</th></tr>";
        
        foreach ($admin_files as $file) {
            $exists = file_exists($file);
            $readable = is_readable($file);
            $test_link = $exists ? "<a href='$file'>Test File</a>" : 'N/A';
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file) . "</td>";
            echo "<td>" . ($exists ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>" . ($readable ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>$test_link</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        ?>
    </div>
    
    <div class='test-section'>
        <h2>Navigation Test</h2>
        <div class='status info'>
            <strong>Current URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?><br>
            <strong>Request Method:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'Unknown'); ?><br>
            <strong>GET Parameters:</strong> <?php echo htmlspecialchars(json_encode($_GET)); ?>
        </div>
        
        <h3>Quick Navigation</h3>
        <a href='admin_dashboard.php' class='nav-link'>🏠 Admin Dashboard</a><br>
        <a href='admin_dashboard.php?section=dashboard' class='nav-link'>📊 Dashboard Section</a><br>
        <a href='admin_dashboard.php?section=events' class='nav-link'>📅 Events Section</a><br>
        <a href='admin_dashboard.php?section=gallery' class='nav-link'>🖼️ Gallery Section</a><br>
        <a href='admin_dashboard.php?section=sermons' class='nav-link'>🎤️ Sermons Section</a><br>
        <a href='admin_dashboard.php?section=news' class='nav-link'>📰 News Section</a><br>
        <a href='admin_dashboard.php?section=testimonials' class='nav-link'>💬 Testimonials Section</a><br>
        <a href='admin_dashboard.php?section=messages' class='nav-link'>✉️ Messages Section</a><br>
        <a href='admin_dashboard.php?section=users' class='nav-link'>👥 Users Section</a><br>
    </div>
    
    <div class='test-section'>
        <h2>Database Test</h2>
        <?php
        require_once 'db_connection.php';
        $conn = getConnection();
        
        if ($conn) {
            echo "<div class='status success'>";
            echo "<strong>✅ Database Connected</strong><br>";
            echo "Host: " . $conn->host_info . "<br>";
            echo "Server: " . $conn->server_info . "<br>";
            
            // Test basic query
            $result = $conn->query("SELECT 1 as test");
            if ($result) {
                echo "<div class='status success'>Basic Query: ✅ PASSED</div>";
            } else {
                echo "<div class='status error'>Basic Query: ❌ FAILED</div>";
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
    
    <div class='test-section'>
        <h2>Actions</h2>
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <a href='admin/welcome.php' class='nav-link'>🔐 Login to Admin</a>
        <?php else: ?>
            <a href='logout.php' class='nav-link'>🚪 Logout</a>
        <?php endif; ?>
    </div>
    
    <div class='test-section'>
        <h2>Next Steps</h2>
        <div class='status info'>
            <p><strong>If all tests pass:</strong></p>
            <p>1. Visit <a href='admin_dashboard.php'>Admin Dashboard</a></p>
            <p>2. If still showing blank, check browser console for errors</p>
            <p>3. Try <a href='admin_dashboard_debug.php'>Debug Tool</a> for detailed diagnostics</p>
        </div>
    </div>
</body>
</html>";
?>
