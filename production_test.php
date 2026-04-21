<?php
/**
 * Production Test Script - Tests both localhost and hosting configurations
 * Run this script to verify your hosting setup works correctly
 */

require_once 'db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Production Environment Test - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1000px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-info { color: #17a2b8; font-weight: bold; }
        .env-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .env-localhost { background: #28a745; color: white; }
        .env-production { background: #dc3545; color: white; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 10px; font-size: 12px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-server me-2'></i>Production Environment Test</h3>
                <p class='mb-0'>Comprehensive testing for both localhost and hosting environments</p>
            </div>
            <div class='card-body'>";

// Environment Detection Test
echo "<h4><i class='fas fa-globe me-2'></i>Environment Detection</h4>";
echo "<div class='mb-3'>";

global $environment;
$server_name = $_SERVER['SERVER_NAME'] ?? 'Unknown';
$http_host = $_SERVER['HTTP_HOST'] ?? 'Unknown';
$server_addr = $_SERVER['SERVER_ADDR'] ?? 'Unknown';
$script_name = $_SERVER['SCRIPT_NAME'] ?? 'Unknown';

echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<strong>Detected Environment:</strong> ";
echo "<span class='env-badge env-" . $environment . "'>" . strtoupper($environment) . "</span><br>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<strong>Server Name:</strong> " . htmlspecialchars($server_name) . "<br>";
echo "</div>";
echo "</div>";

echo "<div class='row mt-2'>";
echo "<div class='col-md-6'>";
echo "<strong>HTTP Host:</strong> " . htmlspecialchars($http_host) . "<br>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<strong>Server IP:</strong> " . htmlspecialchars($server_addr) . "<br>";
echo "</div>";
echo "</div>";

echo "<div class='mt-2'>";
echo "<strong>Script Path:</strong> " . htmlspecialchars($script_name) . "<br>";
echo "</div>";

echo "</div>";

// Database Configuration Test
echo "<h4><i class='fas fa-database me-2'></i>Database Configuration</h4>";
echo "<div class='mb-3'>";

echo "<table class='table table-sm'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$db_configs = [
    'DB_HOST' => DB_HOST,
    'DB_USER' => DB_USER,
    'DB_PASS' => str_repeat('*', strlen(DB_PASS)),
    'DB_NAME' => DB_NAME,
    'DB_PORT_PRIMARY' => DB_PORT_PRIMARY,
    'DB_PORT_FALLBACK' => DB_PORT_FALLBACK
];

foreach ($db_configs as $key => $value) {
    $masked_value = $key === 'DB_PASS' ? str_repeat('*', strlen(DB_PASS)) : $value;
    echo "<tr>";
    echo "<td><code>" . $key . "</code></td>";
    echo "<td>" . htmlspecialchars($masked_value) . "</td>";
    echo "<td><span class='status-ok'>SET</span></td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";

// Database Connection Test
echo "<h4><i class='fas fa-plug me-2'></i>Database Connection Test</h4>";
echo "<div class='mb-3'>";

$connection_tests = [];

// Test primary connection
echo "<div class='test-item'>";
try {
    $conn = getConnection();
    if ($conn) {
        echo "<span class='status-ok'>SUCCESS</span> - Database connected successfully<br>";
        $connection_tests['primary'] = 'PASS';
        
        // Get server info
        $server_info = $conn->server_info;
        echo "<small>MySQL Server: " . htmlspecialchars($server_info) . "</small><br>";
        
        // Get database info
        $db_info_result = $conn->query("SELECT DATABASE() as db_name, USER() as current_user, VERSION() as version");
        $db_info = $db_info_result->fetch_assoc();
        echo "<small>Current Database: " . htmlspecialchars($db_info['db_name']) . "</small><br>";
        echo "<small>Current User: " . htmlspecialchars($db_info['current_user']) . "</small><br>";
        echo "<small>MySQL Version: " . htmlspecialchars($db_info['version']) . "</small><br>";
        
        $conn->close();
    } else {
        echo "<span class='status-error'>FAILED</span> - Could not connect to database<br>";
        $connection_tests['primary'] = 'FAIL';
    }
} catch (Exception $e) {
    echo "<span class='status-error'>ERROR</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    $connection_tests['primary'] = 'ERROR';
}
echo "</div>";

// Test database tables
echo "<div class='test-item'>";
try {
    $conn = getConnection();
    if ($conn) {
        $tables_result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $tables_result->fetch_assoc()) {
            $tables[] = array_values($row)[0];
        }
        
        $required_tables = ['admin_users', 'sermons', 'events', 'news', 'gallery', 'testimonials', 'users', 'donations'];
        $missing_tables = array_diff($required_tables, $tables);
        
        if (empty($missing_tables)) {
            echo "<span class='status-ok'>SUCCESS</span> - All required tables exist<br>";
            echo "<small>Found " . count($tables) . " tables</small><br>";
            $connection_tests['tables'] = 'PASS';
        } else {
            echo "<span class='status-warning'>WARNING</span> - Missing tables: " . implode(', ', $missing_tables) . "<br>";
            echo "<small>Found: " . implode(', ', $tables) . "</small><br>";
            $connection_tests['tables'] = 'WARNING';
        }
        
        $conn->close();
    } else {
        echo "<span class='status-error'>SKIPPED</span> - Cannot check tables without database connection<br>";
        $connection_tests['tables'] = 'SKIP';
    }
} catch (Exception $e) {
    echo "<span class='status-error'>ERROR</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    $connection_tests['tables'] = 'ERROR';
}
echo "</div>";

// Test admin user
echo "<div class='test-item'>";
try {
    $conn = getConnection();
    if ($conn) {
        $admin_result = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
        $admin_count = $admin_result->fetch_assoc()['count'];
        
        if ($admin_count > 0) {
            echo "<span class='status-ok'>SUCCESS</span> - Found $admin_count active admin users<br>";
            $connection_tests['admin'] = 'PASS';
        } else {
            echo "<span class='status-warning'>WARNING</span> - No active admin users found<br>";
            $connection_tests['admin'] = 'WARNING';
        }
        
        $conn->close();
    } else {
        echo "<span class='status-error'>SKIPPED</span> - Cannot check admin users without database connection<br>";
        $connection_tests['admin'] = 'SKIP';
    }
} catch (Exception $e) {
    echo "<span class='status-error'>ERROR</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    $connection_tests['admin'] = 'ERROR';
}
echo "</div>";

echo "</div>";

// File System Test
echo "<h4><i class='fas fa-folder me-2'></i>File System Test</h4>";
echo "<div class='mb-3'>";

$upload_dirs = ['uploads/sermons/video', 'uploads/sermons/audio', 'uploads/gallery/image', 'uploads/gallery/video', 'uploads/gallery/audio'];
$missing_dirs = [];
$existing_dirs = [];

foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        $existing_dirs[] = $dir;
    } else {
        $missing_dirs[] = $dir;
    }
}

if (empty($missing_dirs)) {
    echo "<span class='status-ok'>SUCCESS</span> - All upload directories exist<br>";
    echo "<small>Directories: " . implode(', ', $existing_dirs) . "</small><br>";
} else {
    echo "<span class='status-warning'>WARNING</span> - Missing upload directories<br>";
    echo "<small>Missing: " . implode(', ', $missing_dirs) . "</small><br>";
    echo "<small>Existing: " . implode(', ', $existing_dirs) . "</small><br>";
}

echo "</div>";

// PHP Configuration Test
echo "<h4><i class='fas fa-cog me-2'></i>PHP Configuration</h4>";
echo "<div class='mb-3'>";

$php_configs = [
    'PHP Version' => phpversion(),
    'Memory Limit' => ini_get('memory_limit'),
    'Max Upload Size' => ini_get('upload_max_filesize'),
    'Max Post Size' => ini_get('post_max_size'),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'File Uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
    'MySQL Support' => extension_loaded('mysqli') ? 'Enabled' : 'Disabled',
    'Session Support' => extension_loaded('session') ? 'Enabled' : 'Disabled'
];

echo "<table class='table table-sm'>";
foreach ($php_configs as $key => $value) {
    $status = ($key === 'MySQL Support' || $key === 'Session Support' || $key === 'File Uploads') ? 
        ($value === 'Enabled' ? 'status-ok' : 'status-error') : 'status-info';
    
    echo "<tr>";
    echo "<td>" . $key . "</td>";
    echo "<td>" . htmlspecialchars($value) . "</td>";
    echo "<td><span class='$status'>" . $value . "</span></td>";
    echo "</tr>";
}
echo "</table>";

echo "</div>";

// Summary
echo "<h4><i class='fas fa-clipboard-check me-2'></i>Test Summary</h4>";
echo "<div class='mb-3'>";

$all_tests = array_merge($connection_tests, ['php' => 'PASS', 'files' => empty($missing_dirs) ? 'PASS' : 'WARNING']);
$passed = count(array_filter($all_tests, function($v) { return $v === 'PASS'; }));
$failed = count(array_filter($all_tests, function($v) { return $v === 'FAIL' || $v === 'ERROR'; }));
$warnings = count(array_filter($all_tests, function($v) { return $v === 'WARNING' || $v === 'SKIP'; }));
$total = count($all_tests);

$success_rate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "<div class='row'>";
echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='text-success'>$passed</h5>";
echo "<small class='text-muted'>Passed</small>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='text-danger'>$failed</h5>";
echo "<small class='text-muted'>Failed</small>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='text-warning'>$warnings</h5>";
echo "<small class='text-muted'>Warnings</small>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<div class='col-md-3'>";
echo "<div class='card text-center'>";
echo "<div class='card-body'>";
echo "<h5 class='text-info'>$success_rate%</h5>";
echo "<small class='text-muted'>Success Rate</small>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";

// Action Buttons
echo "<div class='text-center mt-4'>";
if ($environment === 'localhost') {
    echo "<div class='alert alert-info'>";
    echo "<i class='fas fa-info-circle me-2'></i>";
    echo "<strong>Localhost Environment Detected</strong><br>";
    echo "This test is running on your local development server. When you deploy to hosting, this will automatically switch to production mode.";
    echo "</div>";
} else {
    echo "<div class='alert alert-success'>";
    echo "<i class='fas fa-check-circle me-2'></i>";
    echo "<strong>Production Environment Detected</strong><br>";
    echo "This test is running on your hosting server. Production configuration is active.";
    echo "</div>";
}

echo "<div class='mt-3'>";
echo "<a href='admin/' class='btn btn-primary me-2'>";
echo "<i class='fas fa-tachometer-alt me-2'></i>Admin Dashboard";
echo "</a>";
echo "<a href='admin_dashboard_verification.php' class='btn btn-info me-2'>";
echo "<i class='fas fa-clipboard-check me-2'></i>Full Verification";
echo "</a>";
echo "<a href='index.php' class='btn btn-outline-primary'>";
echo "<i class='fas fa-home me-2'></i>Visit Website";
echo "</a>";
echo "</div>";
echo "</div>";

echo "</div>
        </div>
    </div>
</body>
</html>";
?>
