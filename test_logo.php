<?php
// Simple logo test
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logo Test</title>
</head>
<body>
    <h2>Logo Test Results</h2>
    
    <h3>Using LOGO_PATH constant:</h3>
    <img src="<?php echo LOGO_PATH; ?>" alt="Church Logo" style="border: 1px solid #ccc; margin: 10px;">
    <br>
    <small>Path: <?php echo LOGO_PATH; ?></small>
    
    <h3>Using CHURCH_LOGO constant:</h3>
    <img src="<?php echo CHURCH_LOGO; ?>" alt="Church Logo" style="border: 1px solid #ccc; margin: 10px;">
    <br>
    <small>Path: <?php echo CHURCH_LOGO; ?></small>
    
    <h3>Using direct path:</h3>
    <img src="public/logo-icon.jpeg" alt="Church Logo" style="border: 1px solid #ccc; margin: 10px;">
    <br>
    <small>Path: public/logo-icon.jpeg</small>
    
    <h3>Debug Information:</h3>
    <ul>
        <li>BASE_URL: <?php echo BASE_URL; ?></li>
        <li>CHURCH_LOGO: <?php echo CHURCH_LOGO; ?></li>
        <li>LOGO_PATH: <?php echo LOGO_PATH; ?></li>
        <li>File exists: <?php echo file_exists('public/logo-icon.jpeg') ? 'YES' : 'NO'; ?></li>
        <li>File size: <?php echo filesize('public/logo-icon.jpeg'); ?> bytes</li>
    </ul>
    
    <p><a href="admin_dashboard.php">Go to Admin Dashboard</a></p>
</body>
</html>
