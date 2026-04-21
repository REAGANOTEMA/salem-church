<?php
// Test admin section logo loading
require_once 'config.php';

// Simulate admin section environment
$GLOBALS['admin_db_connection'] = null; // Simulate no DB connection for testing
$admin_name = 'Test Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Section Logo Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { border: 1px solid #ccc; margin: 20px 0; padding: 15px; }
        .logo-test { border: 1px solid #ddd; margin: 10px 0; padding: 10px; }
        .status { padding: 5px 10px; border-radius: 3px; color: white; display: inline-block; margin: 5px; }
        .success { background-color: #28a745; }
        .error { background-color: #dc3545; }
        .warning { background-color: #ffc107; color: #000; }
    </style>
</head>
<body>
    <h1>Admin Section Logo Test</h1>
    
    <div class="test-section">
        <h2>Configuration Status</h2>
        <p>Config file loaded: <span class="status success">YES</span></p>
        <p>Logo functions available: <span class="status <?php echo function_exists('getSafeLogoUrl') ? 'success' : 'error'; ?>">
            <?php echo function_exists('getSafeLogoUrl') ? 'YES' : 'NO'; ?>
        </span></p>
        <p>Safe Logo URL: <code><?php echo getSafeLogoUrl(); ?></code></p>
    </div>
    
    <div class="test-section">
        <h2>Admin Section Simulations</h2>
        
        <h3>Sermons Management</h3>
        <div class="logo-test">
            <h1 class="page-title"><?php echo getLogoImg(30, 30, 'margin-right: 10px'); ?>Sermon Management</h1>
            <p>This simulates the sermons.php page header.</p>
        </div>
        
        <h3>Events Management</h3>
        <div class="logo-test">
            <h1 class="page-title"><?php echo getLogoImg(30, 30, 'margin-right: 10px'); ?>Event Management</h1>
            <p>This simulates the events.php page header.</p>
        </div>
        
        <h3>News Management</h3>
        <div class="logo-test">
            <h1 class="page-title"><?php echo getLogoImg(30, 30, 'margin-right: 10px'); ?>News Management</h1>
            <p>This simulates the news.php page header.</p>
        </div>
        
        <h3>Gallery Management</h3>
        <div class="logo-test">
            <h1 class="page-title"><?php echo getLogoImg(30, 30, 'margin-right: 10px'); ?>Gallery Management</h1>
            <p>This simulates the gallery.php page header.</p>
        </div>
        
        <h3>Dashboard Statistics</h3>
        <div class="logo-test">
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                <div class="stat-card" style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                    <div class="stat-icon"><?php echo getLogoImg(40, 40); ?></div>
                    <div class="stat-number">123</div>
                    <div class="stat-label">Total Sermons</div>
                </div>
                <div class="stat-card" style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                    <div class="stat-icon"><?php echo getLogoImg(40, 40); ?></div>
                    <div class="stat-number">456</div>
                    <div class="stat-label">Total Events</div>
                </div>
                <div class="stat-card" style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                    <div class="stat-icon"><?php echo getLogoImg(40, 40); ?></div>
                    <div class="stat-number">789</div>
                    <div class="stat-label">Total News</div>
                </div>
                <div class="stat-card" style="border: 1px solid #ddd; padding: 10px; text-align: center;">
                    <div class="stat-icon"><?php echo getLogoImg(40, 40); ?></div>
                    <div class="stat-number">321</div>
                    <div class="stat-label">Total Gallery</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Fallback Mechanism Test</h2>
        <p>The logo loading system uses multiple fallbacks:</p>
        <ol>
            <li>Direct file path: <code><?php echo LOGO_PATH; ?></code></li>
            <li>Serve script: <code><?php echo LOGO_SERVE_URL; ?></code></li>
            <li>Base64 fallback: <code>data:image/jpeg;base64,...</code></li>
        </ol>
        
        <h3>Current Active Method:</h3>
        <div class="logo-test">
            <img src="<?php echo getSafeLogoUrl(); ?>" alt="Active Logo Method" style="width: 100px; height: 100px; border: 2px solid green;">
            <br>
            <small>URL: <?php echo getSafeLogoUrl(); ?></small>
        </div>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="admin_dashboard.php" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Test in Admin Dashboard
        </a>
    </div>
</body>
</html>
