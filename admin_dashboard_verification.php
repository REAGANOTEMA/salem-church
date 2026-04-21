<?php
/**
 * Comprehensive Admin Dashboard Verification Script
 * Tests all admin functionalities and database connections
 */

require_once 'db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard Verification - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-info { color: #17a2b8; font-weight: bold; }
        .progress { height: 8px; }
        .test-item { padding: 10px; margin: 5px 0; border-radius: 10px; }
        .test-pass { background: #d4edda; border-left: 4px solid #28a745; }
        .test-fail { background: #f8d7da; border-left: 4px solid #dc3545; }
        .test-warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 10px; font-size: 12px; overflow-x: auto; }
        .summary-card { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .error-card { background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-shield-alt me-2'></i>Admin Dashboard Verification</h3>
                <p class='mb-0'>Comprehensive testing of all admin functionalities and database connections</p>
            </div>
            <div class='card-body'>";

$tests = [];
$passed = 0;
$failed = 0;
$warnings = 0;

echo "<h4><i class='fas fa-database me-2'></i>Database Connection Tests</h4>";

// Test 1: Database Connection
echo "<div class='test-item'>";
try {
    $conn = getConnection();
    if ($conn) {
        echo "<span class='status-ok'>SUCCESS</span> - Database connected<br>";
        $tests[] = ['Database Connection', 'PASS', 'Successfully connected to MySQL'];
        $passed++;
    } else {
        echo "<span class='status-error'>FAILED</span> - Database connection failed<br>";
        $tests[] = ['Database Connection', 'FAIL', 'Could not connect to MySQL'];
        $failed++;
    }
} catch (Exception $e) {
    echo "<span class='status-error'>FAILED</span> - Database connection error: " . htmlspecialchars($e->getMessage()) . "<br>";
    $tests[] = ['Database Connection', 'FAIL', 'Exception: ' . $e->getMessage()];
    $failed++;
}
echo "</div>";

// Test 2: Database Tables
echo "<div class='test-item'>";
if ($conn) {
    $tables_to_check = ['admin_users', 'sermons', 'events', 'news', 'gallery', 'testimonials', 'users', 'donations'];
    $missing_tables = [];
    $existing_tables = [];
    
    foreach ($tables_to_check as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $existing_tables[] = $table;
        } else {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo "<span class='status-ok'>SUCCESS</span> - All required tables exist<br>";
        echo "<small>Tables: " . implode(', ', $existing_tables) . "</small><br>";
        $tests[] = ['Database Tables', 'PASS', 'All required tables exist'];
        $passed++;
    } else {
        echo "<span class='status-warning'>WARNING</span> - Missing tables: " . implode(', ', $missing_tables) . "<br>";
        echo "<small>Existing: " . implode(', ', $existing_tables) . "</small><br>";
        $tests[] = ['Database Tables', 'WARNING', 'Missing tables: ' . implode(', ', $missing_tables)];
        $warnings++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot check tables without database connection<br>";
    $tests[] = ['Database Tables', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

// Test 3: Admin User Authentication
echo "<div class='test-item'>";
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
        $admin_count = $result->fetch_assoc()['count'];
        
        if ($admin_count > 0) {
            echo "<span class='status-ok'>SUCCESS</span> - Found $admin_count active admin users<br>";
            $tests[] = ['Admin Users', 'PASS', "$admin_count active admin users found"];
            $passed++;
        } else {
            echo "<span class='status-warning'>WARNING</span> - No active admin users found<br>";
            $tests[] = ['Admin Users', 'WARNING', 'No active admin users'];
            $warnings++;
        }
    } catch (Exception $e) {
        echo "<span class='status-error'>FAILED</span> - Error checking admin users: " . htmlspecialchars($e->getMessage()) . "<br>";
        $tests[] = ['Admin Users', 'FAIL', 'Error: ' . $e->getMessage()];
        $failed++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot check admin users without database connection<br>";
    $tests[] = ['Admin Users', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

echo "<h4><i class='fas fa-cogs me-2'></i>Admin Functionality Tests</h4>";

// Test 4: Sermon Management
echo "<div class='test-item'>";
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM sermons");
        $sermon_count = $result->fetch_assoc()['count'];
        echo "<span class='status-info'>INFO</span> - Found $sermon_count sermons in database<br>";
        
        // Test sermon table structure
        $structure = $conn->query("DESCRIBE sermons");
        $required_fields = ['title', 'description', 'sermon_date', 'media_type', 'status'];
        $missing_fields = [];
        
        while ($row = $structure->fetch_assoc()) {
            if (in_array($row['Field'], $required_fields)) {
                unset($required_fields[array_search($row['Field'], $required_fields)]);
            }
        }
        
        if (empty($required_fields)) {
            echo "<span class='status-ok'>SUCCESS</span> - Sermon table structure is correct<br>";
            $tests[] = ['Sermon Management', 'PASS', 'Table structure correct, ' . $sermon_count . ' sermons'];
            $passed++;
        } else {
            echo "<span class='status-warning'>WARNING</span> - Missing sermon fields: " . implode(', ', $required_fields) . "<br>";
            $tests[] = ['Sermon Management', 'WARNING', 'Missing fields: ' . implode(', ', $required_fields)];
            $warnings++;
        }
    } catch (Exception $e) {
        echo "<span class='status-error'>FAILED</span> - Error testing sermon management: " . htmlspecialchars($e->getMessage()) . "<br>";
        $tests[] = ['Sermon Management', 'FAIL', 'Error: ' . $e->getMessage()];
        $failed++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot test sermon management without database connection<br>";
    $tests[] = ['Sermon Management', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

// Test 5: Event Management
echo "<div class='test-item'>";
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM events");
        $event_count = $result->fetch_assoc()['count'];
        echo "<span class='status-info'>INFO</span> - Found $event_count events in database<br>";
        
        // Test event table structure
        $structure = $conn->query("DESCRIBE events");
        $required_fields = ['title', 'description', 'event_date', 'location', 'status'];
        $missing_fields = [];
        
        while ($row = $structure->fetch_assoc()) {
            if (in_array($row['Field'], $required_fields)) {
                unset($required_fields[array_search($row['Field'], $required_fields)]);
            }
        }
        
        if (empty($required_fields)) {
            echo "<span class='status-ok'>SUCCESS</span> - Event table structure is correct<br>";
            $tests[] = ['Event Management', 'PASS', 'Table structure correct, ' . $event_count . ' events'];
            $passed++;
        } else {
            echo "<span class='status-warning'>WARNING</span> - Missing event fields: " . implode(', ', $required_fields) . "<br>";
            $tests[] = ['Event Management', 'WARNING', 'Missing fields: ' . implode(', ', $required_fields)];
            $warnings++;
        }
    } catch (Exception $e) {
        echo "<span class='status-error'>FAILED</span> - Error testing event management: " . htmlspecialchars($e->getMessage()) . "<br>";
        $tests[] = ['Event Management', 'FAIL', 'Error: ' . $e->getMessage()];
        $failed++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot test event management without database connection<br>";
    $tests[] = ['Event Management', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

// Test 6: News Management
echo "<div class='test-item'>";
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM news");
        $news_count = $result->fetch_assoc()['count'];
        echo "<span class='status-info'>INFO</span> - Found $news_count news articles in database<br>";
        
        // Test news table structure
        $structure = $conn->query("DESCRIBE news");
        $required_fields = ['title', 'content', 'status'];
        $missing_fields = [];
        
        while ($row = $structure->fetch_assoc()) {
            if (in_array($row['Field'], $required_fields)) {
                unset($required_fields[array_search($row['Field'], $required_fields)]);
            }
        }
        
        if (empty($required_fields)) {
            echo "<span class='status-ok'>SUCCESS</span> - News table structure is correct<br>";
            $tests[] = ['News Management', 'PASS', 'Table structure correct, ' . $news_count . ' articles'];
            $passed++;
        } else {
            echo "<span class='status-warning'>WARNING</span> - Missing news fields: " . implode(', ', $required_fields) . "<br>";
            $tests[] = ['News Management', 'WARNING', 'Missing fields: ' . implode(', ', $required_fields)];
            $warnings++;
        }
    } catch (Exception $e) {
        echo "<span class='status-error'>FAILED</span> - Error testing news management: " . htmlspecialchars($e->getMessage()) . "<br>";
        $tests[] = ['News Management', 'FAIL', 'Error: ' . $e->getMessage()];
        $failed++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot test news management without database connection<br>";
    $tests[] = ['News Management', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

// Test 7: Gallery Management
echo "<div class='test-item'>";
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM gallery");
        $gallery_count = $result->fetch_assoc()['count'];
        echo "<span class='status-info'>INFO</span> - Found $gallery_count gallery items in database<br>";
        
        // Test gallery table structure
        $structure = $conn->query("DESCRIBE gallery");
        $required_fields = ['title', 'description', 'file_type', 'file_url'];
        $missing_fields = [];
        
        while ($row = $structure->fetch_assoc()) {
            if (in_array($row['Field'], $required_fields)) {
                unset($required_fields[array_search($row['Field'], $required_fields)]);
            }
        }
        
        if (empty($required_fields)) {
            echo "<span class='status-ok'>SUCCESS</span> - Gallery table structure is correct<br>";
            $tests[] = ['Gallery Management', 'PASS', 'Table structure correct, ' . $gallery_count . ' items'];
            $passed++;
        } else {
            echo "<span class='status-warning'>WARNING</span> - Missing gallery fields: " . implode(', ', $required_fields) . "<br>";
            $tests[] = ['Gallery Management', 'WARNING', 'Missing fields: ' . implode(', ', $required_fields)];
            $warnings++;
        }
    } catch (Exception $e) {
        echo "<span class='status-error'>FAILED</span> - Error testing gallery management: " . htmlspecialchars($e->getMessage()) . "<br>";
        $tests[] = ['Gallery Management', 'FAIL', 'Error: ' . $e->getMessage()];
        $failed++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot test gallery management without database connection<br>";
    $tests[] = ['Gallery Management', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

// Test 8: Testimonials Management
echo "<div class='test-item'>";
if ($conn) {
    try {
        $result = $conn->query("SELECT COUNT(*) as count FROM testimonials");
        $testimonial_count = $result->fetch_assoc()['count'];
        echo "<span class='status-info'>INFO</span> - Found $testimonial_count testimonials in database<br>";
        
        // Test testimonials table structure
        $structure = $conn->query("DESCRIBE testimonials");
        $required_fields = ['name', 'content', 'is_approved'];
        $missing_fields = [];
        
        while ($row = $structure->fetch_assoc()) {
            if (in_array($row['Field'], $required_fields)) {
                unset($required_fields[array_search($row['Field'], $required_fields)]);
            }
        }
        
        if (empty($required_fields)) {
            echo "<span class='status-ok'>SUCCESS</span> - Testimonials table structure is correct<br>";
            $tests[] = ['Testimonials Management', 'PASS', 'Table structure correct, ' . $testimonial_count . ' testimonials'];
            $passed++;
        } else {
            echo "<span class='status-warning'>WARNING</span> - Missing testimonial fields: " . implode(', ', $required_fields) . "<br>";
            $tests[] = ['Testimonials Management', 'WARNING', 'Missing fields: ' . implode(', ', $required_fields)];
            $warnings++;
        }
    } catch (Exception $e) {
        echo "<span class='status-error'>FAILED</span> - Error testing testimonials management: " . htmlspecialchars($e->getMessage()) . "<br>";
        $tests[] = ['Testimonials Management', 'FAIL', 'Error: ' . $e->getMessage()];
        $failed++;
    }
} else {
    echo "<span class='status-error'>SKIPPED</span> - Cannot test testimonials management without database connection<br>";
    $tests[] = ['Testimonials Management', 'SKIP', 'No database connection'];
    $warnings++;
}
echo "</div>";

echo "<h4><i class='fas fa-shield-alt me-2'></i>Security Tests</h4>";

// Test 9: Password Hashing
echo "<div class='test-item'>";
try {
    $test_password = 'test123';
    $hashed = password_hash($test_password, PASSWORD_DEFAULT);
    
    if (password_verify($test_password, $hashed)) {
        echo "<span class='status-ok'>SUCCESS</span> - Password hashing/verification works correctly<br>";
        $tests[] = ['Password Security', 'PASS', 'Password hashing and verification functional'];
        $passed++;
    } else {
        echo "<span class='status-error'>FAILED</span> - Password verification failed<br>";
        $tests[] = ['Password Security', 'FAIL', 'Password verification not working'];
        $failed++;
    }
} catch (Exception $e) {
    echo "<span class='status-error'>FAILED</span> - Password hashing error: " . htmlspecialchars($e->getMessage()) . "<br>";
    $tests[] = ['Password Security', 'FAIL', 'Error: ' . $e->getMessage()];
    $failed++;
}
echo "</div>";

// Test 10: File Upload Directories
echo "<div class='test-item'>";
$upload_dirs = ['uploads/sermons/video', 'uploads/sermons/audio', 'uploads/gallery/image', 'uploads/gallery/video', 'uploads/gallery/audio'];
$missing_dirs = [];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        $missing_dirs[] = $dir;
    }
}

if (empty($missing_dirs)) {
    echo "<span class='status-ok'>SUCCESS</span> - All upload directories exist<br>";
    $tests[] = ['Upload Directories', 'PASS', 'All required upload directories exist'];
    $passed++;
} else {
    echo "<span class='status-warning'>WARNING</span> - Missing upload directories: " . implode(', ', $missing_dirs) . "<br>";
    $tests[] = ['Upload Directories', 'WARNING', 'Missing directories: ' . implode(', ', $missing_dirs)];
    $warnings++;
}
echo "</div>";

// Summary
$total_tests = $passed + $failed + $warnings;
$success_rate = $total_tests > 0 ? round(($passed / $total_tests) * 100, 1) : 0;

echo "<div class='mt-4'>";
if ($failed === 0 && $warnings === 0) {
    echo "<div class='card summary-card'>
        <div class='card-body text-center'>
            <h4><i class='fas fa-check-circle me-2'></i>All Tests Passed!</h4>
            <p class='mb-0'>Admin dashboard is fully operational with $passed/$total_tests tests passing ($success_rate%)</p>
        </div>
    </div>";
} elseif ($failed === 0) {
    echo "<div class='card summary-card'>
        <div class='card-body text-center'>
            <h4><i class='fas fa-exclamation-triangle me-2'></i>Tests Passed with Warnings</h4>
            <p class='mb-0'>$passed/$total_tests tests passing ($success_rate%), $warnings warnings</p>
        </div>
    </div>";
} else {
    echo "<div class='card error-card'>
        <div class='card-body text-center'>
            <h4><i class='fas fa-times-circle me-2'></i>Some Tests Failed</h4>
            <p class='mb-0'>$passed/$total_tests tests passing ($success_rate%), $failed failed, $warnings warnings</p>
        </div>
    </div>";
}
echo "</div>";

// Detailed Results
echo "<div class='mt-4'>
    <h5><i class='fas fa-list me-2'></i>Detailed Test Results</h5>
    <div class='table-responsive'>
        <table class='table table-sm'>
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>";

foreach ($tests as $test) {
    $status_class = '';
    $status_icon = '';
    
    switch ($test[1]) {
        case 'PASS':
            $status_class = 'status-ok';
            $status_icon = 'fa-check';
            break;
        case 'FAIL':
            $status_class = 'status-error';
            $status_icon = 'fa-times';
            break;
        case 'WARNING':
            $status_class = 'status-warning';
            $status_icon = 'fa-exclamation-triangle';
            break;
        case 'SKIP':
            $status_class = 'status-info';
            $status_icon = 'fa-forward';
            break;
    }
    
    echo "<tr>
        <td>" . htmlspecialchars($test[0]) . "</td>
        <td><span class='$status_class'><i class='fas $status_icon me-1'></i>" . $test[1] . "</span></td>
        <td>" . htmlspecialchars($test[2]) . "</td>
    </tr>";
}

echo "</tbody>
        </table>
    </div>
</div>";

echo "<div class='text-center mt-4'>
    <a href='admin/' class='btn btn-primary me-2'>
        <i class='fas fa-tachometer-alt me-2'></i>Go to Admin Dashboard
    </a>
    <a href='index.php' class='btn btn-outline-primary'>
        <i class='fas fa-home me-2'></i>Visit Website
    </a>
</div>";

if ($conn) {
    $conn->close();
}

echo "
            </div>
        </div>
    </div>
</body>
</html>";
?>
