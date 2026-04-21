<?php
// Simple test to verify logo accessibility
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logo Test</title>
</head>
<body>
    <h1>Logo Accessibility Test</h1>
    
    <h2>Test 1: Direct path from root</h2>
    <img src="public/logo-icon.jpeg" alt="Logo Test 1" style="width: 100px; height: 100px; border: 2px solid red;">
    
    <h2>Test 2: Absolute path</h2>
    <img src="/public/logo-icon.jpeg" alt="Logo Test 2" style="width: 100px; height: 100px; border: 2px solid blue;">
    
    <h2>Test 3: Relative path from admin</h2>
    <img src="../public/logo-icon.jpeg" alt="Logo Test 3" style="width: 100px; height: 100px; border: 2px solid green;">
    
    <h2>File Information</h2>
    <?php
    $logo_path = 'public/logo-icon.jpeg';
    if (file_exists($logo_path)) {
        echo "<p style='color: green;'>Logo file exists at: $logo_path</p>";
        echo "<p>File size: " . filesize($logo_path) . " bytes</p>";
        echo "<p>File type: " . mime_content_type($logo_path) . "</p>";
    } else {
        echo "<p style='color: red;'>Logo file NOT found at: $logo_path</p>";
    }
    
    // Check current directory
    echo "<p>Current directory: " . getcwd() . "</p>";
    echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    ?>
</body>
</html>
