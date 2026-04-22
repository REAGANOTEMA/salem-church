<?php
/**
 * Universal Compatibility Test for Salem Dominion Ministries
 * Tests all pages and functionality across different hosting platforms
 */

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once 'db_connection.php';
require_once 'config.php';

// Test results
$test_results = [
    'database_connection' => false,
    'pages_tested' => [],
    'admin_functions' => [],
    'user_functions' => [],
    'errors' => [],
    'success_count' => 0,
    'total_tests' => 0
];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Universal Compatibility Test - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .status-success { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .test-item { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 5px; }
        .progress-bar { height: 20px; background: #e9ecef; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class='container mt-4'>
        <h1 class='text-center mb-4'>Universal Compatibility Test</h1>
        <p class='text-center text-muted'>Testing all pages and functionality for cross-platform compatibility</p>";

// Test 1: Database Connection
echo "<div class='test-section'>
        <h3>Database Connection Test</h3>";

try {
    $conn = createDatabaseConnection();
    if ($conn) {
        $test_results['database_connection'] = true;
        $test_results['success_count']++;
        echo "<div class='test-item status-success'>SUCCESS: Database connection established</div>";
        
        // Test basic query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            $test_results['success_count']++;
            echo "<div class='test-item status-success'>SUCCESS: Basic database query works</div>";
        }
        
        $conn->close();
    } else {
        echo "<div class='test-item status-error'>FAILED: Database connection failed</div>";
        $test_results['errors'][] = 'Database connection failed';
    }
} catch (Exception $e) {
    echo "<div class='test-item status-error'>FAILED: Database exception - " . $e->getMessage() . "</div>";
    $test_results['errors'][] = 'Database exception: ' . $e->getMessage();
}

$test_results['total_tests']++;

// Test 2: Main Pages
echo "</div><div class='test-section'>
        <h3>Main Pages Test</h3>";

$main_pages = [
    'index.php' => 'Homepage',
    'about.php' => 'About Page',
    'news.php' => 'News Page',
    'events.php' => 'Events Page',
    'sermons.php' => 'Sermons Page',
    'gallery.php' => 'Gallery Page',
    'leadership.php' => 'Leadership Page',
    'testimonials.php' => 'Testimonials Page',
    'contact.php' => 'Contact Page',
    'register.php' => 'Registration Page',
    'login.php' => 'Login Page',
    'children_ministry.php' => 'Children Ministry',
    'prophetic-school.php' => 'Prophetic School',
    'book_pastor_call.php' => 'Pastor Booking'
];

foreach ($main_pages as $page => $description) {
    $test_results['total_tests']++;
    
    if (file_exists($page)) {
        echo "<div class='test-item status-success'>SUCCESS: $description - File exists</div>";
        $test_results['success_count']++;
        $test_results['pages_tested'][] = $page;
        
        // Check if file has proper database connection
        $content = file_get_contents($page);
        if (strpos($content, 'createDatabaseConnection') !== false) {
            echo "<div class='test-item status-success'>SUCCESS: $description - Uses correct database connection</div>";
            $test_results['success_count']++;
        } elseif (strpos($content, 'getConnection()') !== false) {
            echo "<div class='test-item status-warning'>WARNING: $description - May have database connection issues</div>";
            $test_results['errors'][] = "$page may have database connection issues";
        }
    } else {
        echo "<div class='test-item status-error'>FAILED: $description - File not found</div>";
        $test_results['errors'][] = "$page file not found";
    }
    
    $test_results['total_tests']++;
}

// Test 3: Admin Sections
echo "</div><div class='test-section'>
        <h3>Admin Sections Test</h3>";

$admin_pages = [
    'admin_sections/dashboard.php' => 'Admin Dashboard',
    'admin_sections/news.php' => 'News Management',
    'admin_sections/sermons.php' => 'Sermons Management',
    'admin_sections/events.php' => 'Events Management',
    'admin_sections/gallery.php' => 'Gallery Management',
    'admin_sections/testimonials.php' => 'Testimonials Management',
    'admin_sections/messages.php' => 'Messages Management',
    'admin_sections/users.php' => 'Users Management'
];

foreach ($admin_pages as $page => $description) {
    $test_results['total_tests']++;
    
    if (file_exists($page)) {
        echo "<div class='test-item status-success'>SUCCESS: $description - File exists</div>";
        $test_results['success_count']++;
        $test_results['admin_functions'][] = $page;
        
        // Check if admin section sets proper status for posts
        $content = file_get_contents($page);
        if (strpos($content, "'published'") !== false || strpos($content, "'upcoming'") !== false) {
            echo "<div class='test-item status-success'>SUCCESS: $description - Sets proper post status</div>";
            $test_results['success_count']++;
        }
    } else {
        echo "<div class='test-item status-error'>FAILED: $description - File not found</div>";
        $test_results['errors'][] = "$page file not found";
    }
    
    $test_results['total_tests']++;
}

// Test 4: Configuration Files
echo "</div><div class='test-section'>
        <h3>Configuration Files Test</h3>";

$config_files = [
    'config.php' => 'Main Configuration',
    'db_connection.php' => 'Database Connection',
    'public/site.webmanifest' => 'PWA Manifest',
    'public/sw.js' => 'Service Worker',
    'public/favicon.ico' => 'Favicon',
    'robots.txt' => 'Robots.txt',
    'browserconfig.xml' => 'Windows Configuration'
];

foreach ($config_files as $file => $description) {
    $test_results['total_tests']++;
    
    if (file_exists($file)) {
        echo "<div class='test-item status-success'>SUCCESS: $description - File exists</div>";
        $test_results['success_count']++;
    } else {
        echo "<div class='test-item status-error'>FAILED: $description - File not found</div>";
        $test_results['errors'][] = "$file file not found";
    }
}

// Test 5: Hosting Platform Detection
echo "</div><div class='test-section'>
        <h3>Hosting Platform Detection</h3>";

$hosting_info = [
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'php_version' => PHP_VERSION,
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'https_status' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS' : 'HTTP',
    'port' => $_SERVER['SERVER_PORT'] ?? 'Unknown'
];

foreach ($hosting_info as $key => $value) {
    echo "<div class='test-item'><strong>" . ucwords(str_replace('_', ' ', $key)) . ":</strong> $value</div>";
}

// Test 6: Cross-Platform Compatibility
echo "</div><div class='test-section'>
        <h3>Cross-Platform Compatibility</h3>";

$compatibility_tests = [
    'mysqli_support' => extension_loaded('mysqli'),
    'json_support' => extension_loaded('json'),
    'gd_support' => extension_loaded('gd'),
    'curl_support' => extension_loaded('curl'),
    'file_uploads' => ini_get('file_uploads'),
    'max_upload_size' => ini_get('upload_max_filesize'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

foreach ($compatibility_tests as $test => $result) {
    $test_results['total_tests']++;
    
    if ($result) {
        $status = is_bool($result) ? 'ENABLED' : $result;
        echo "<div class='test-item status-success'>SUCCESS: $test - $status</div>";
        $test_results['success_count']++;
    } else {
        echo "<div class='test-item status-error'>FAILED: $test - DISABLED</div>";
        $test_results['errors'][] = "$test is disabled";
    }
}

// Calculate success percentage
$success_percentage = $test_results['total_tests'] > 0 ? 
    round(($test_results['success_count'] / $test_results['total_tests']) * 100, 2) : 0;

// Final Results
echo "</div><div class='test-section'>
        <h3>Test Results Summary</h3>
        <div class='progress-bar mb-3'>
            <div class='progress-fill' style='width: $success_percentage%'></div>
        </div>
        <div class='row'>
            <div class='col-md-6'>
                <h5>Success Rate: <span class='status-success'>$success_percentage%</span></h5>
                <p>Tests Passed: {$test_results['success_count']} / {$test_results['total_tests']}</p>
            </div>
            <div class='col-md-6'>
                <h5>Platform Information</h5>
                <p>Server: {$hosting_info['server_software']}</p>
                <p>PHP: {$hosting_info['php_version']}</p>
            </div>
        </div>";

if (!empty($test_results['errors'])) {
    echo "<div class='alert alert-warning'>
            <h5>Issues Found:</h5>
            <ul>";
    foreach ($test_results['errors'] as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>
        </div>";
} else {
    echo "<div class='alert alert-success'>
            <h5>All Systems Operational!</h5>
            <p>All tests passed successfully. The website is ready for deployment on this hosting platform.</p>
        </div>";
}

echo "</div>
        
        <div class='test-section text-center'>
            <h4>Next Steps</h4>
            <div class='row'>
                <div class='col-md-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>Test Live Pages</h5>
                            <p class='card-text'>Visit each page to ensure they load correctly</p>
                            <a href='index.php' class='btn btn-primary'>Test Homepage</a>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>Test Admin Functions</h5>
                            <p class='card-text'>Test admin panel and content management</p>
                            <a href='admin_dashboard.php' class='btn btn-primary'>Test Admin</a>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>Test User Functions</h5>
                            <p class='card-text'>Test registration and user features</p>
                            <a href='register.php' class='btn btn-primary'>Test Registration</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
