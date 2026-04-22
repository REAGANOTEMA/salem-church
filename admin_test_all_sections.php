<?php
// ADMIN SECTIONS TEST - Verify all admin sections work correctly
require_once 'db_connection.php';

// Start session
session_start();

// Simulate admin login for testing
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_name'] = 'Test Admin';

// Create database connection
$conn = createDatabaseConnection();
$GLOBALS['admin_db_connection'] = $conn;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Sections Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Admin Sections Test</h1>
    <p>Testing all admin sections for proper database connectivity and data retrieval...</p>";

// Test database connection
echo "<div class='test-section'>";
echo "<h2>Database Connection Test</h2>";
if ($conn) {
    echo "<p class='success'>Database connection: SUCCESS</p>";
    try {
        $test_query = $conn->query("SELECT 1");
        echo "<p class='success'>Database query test: SUCCESS</p>";
    } catch (Exception $e) {
        echo "<p class='error'>Database query test: FAILED - " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='error'>Database connection: FAILED</p>";
}
echo "</div>";

// Test News Section
echo "<div class='test-section'>";
echo "<h2>News Section Test</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM news");
    $count = $result->fetch_assoc()['count'];
    echo "<p class='success'>News table records: " . $count . "</p>";
    
    $result = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
    $news = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p class='info'>Latest 3 news articles:</p>";
    echo "<pre>";
    foreach ($news as $item) {
        echo "ID: " . $item['id'] . " - " . $item['title'] . " (" . $item['status'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>News section test: FAILED - " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Events Section
echo "<div class='test-section'>";
echo "<h2>Events Section Test</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM events");
    $count = $result->fetch_assoc()['count'];
    echo "<p class='success'>Events table records: " . $count . "</p>";
    
    $result = $conn->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 3");
    $events = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p class='info'>Latest 3 events:</p>";
    echo "<pre>";
    foreach ($events as $item) {
        echo "ID: " . $item['id'] . " - " . $item['title'] . " (" . $item['event_date'] . " - " . $item['status'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>Events section test: FAILED - " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Sermons Section
echo "<div class='test-section'>";
echo "<h2>Sermons Section Test</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM sermons");
    $count = $result->fetch_assoc()['count'];
    echo "<p class='success'>Sermons table records: " . $count . "</p>";
    
    $result = $conn->query("SELECT * FROM sermons ORDER BY created_at DESC LIMIT 3");
    $sermons = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p class='info'>Latest 3 sermons:</p>";
    echo "<pre>";
    foreach ($sermons as $item) {
        echo "ID: " . $item['id'] . " - " . $item['title'] . " (" . $item['status'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>Sermons section test: FAILED - " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Gallery Section
echo "<div class='test-section'>";
echo "<h2>Gallery Section Test</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM gallery");
    $count = $result->fetch_assoc()['count'];
    echo "<p class='success'>Gallery table records: " . $count . "</p>";
    
    $result = $conn->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 3");
    $gallery = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p class='info'>Latest 3 gallery items:</p>";
    echo "<pre>";
    foreach ($gallery as $item) {
        echo "ID: " . $item['id'] . " - " . $item['title'] . " (" . $item['file_type'] . " - " . $item['status'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>Gallery section test: FAILED - " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Testimonials Section
echo "<div class='test-section'>";
echo "<h2>Testimonials Section Test</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM testimonials");
    $count = $result->fetch_assoc()['count'];
    echo "<p class='success'>Testimonials table records: " . $count . "</p>";
    
    $result = $conn->query("SELECT * FROM testimonials ORDER BY submitted_at DESC LIMIT 3");
    $testimonials = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p class='info'>Latest 3 testimonials:</p>";
    echo "<pre>";
    foreach ($testimonials as $item) {
        echo "ID: " . $item['id'] . " - " . $item['name'] . " (" . $item['status'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>Testimonials section test: FAILED - " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Messages Section
echo "<div class='test-section'>";
echo "<h2>Messages Section Test</h2>";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM messages");
    $count = $result->fetch_assoc()['count'];
    echo "<p class='success'>Messages table records: " . $count . "</p>";
    
    $result = $conn->query("SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id ORDER BY m.created_at DESC LIMIT 3");
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    echo "<p class='info'>Latest 3 messages:</p>";
    echo "<pre>";
    foreach ($messages as $item) {
        echo "ID: " . $item['id'] . " - " . substr($item['subject'], 0, 30) . "... (" . $item['message_type'] . " - " . $item['status'] . ")\n";
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>Messages section test: FAILED - " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Admin Sections Include Test
echo "<div class='test-section'>";
echo "<h2>Admin Sections Include Test</h2>";

// Test each admin section include
$sections = ['news', 'events', 'sermons', 'gallery', 'testimonials', 'messages'];

foreach ($sections as $section) {
    echo "<h3>Testing $section section...</h3>";
    try {
        // Capture output
        ob_start();
        include "admin_sections/$section.php";
        $output = ob_get_clean();
        
        if (strpos($output, 'Failed to fetch') !== false) {
            echo "<p class='error'>$section section: FAILED - Data fetch error</p>";
        } elseif (strpos($output, 'error') !== false) {
            echo "<p class='error'>$section section: FAILED - Contains error</p>";
        } else {
            echo "<p class='success'>$section section: SUCCESS</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>$section section: FAILED - " . $e->getMessage() . "</p>";
    }
}

echo "</div>";

echo "<div class='test-section'>";
echo "<h2>Test Summary</h2>";
echo "<p>All admin sections have been tested. Check the results above for any issues.</p>";
echo "<p><a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
echo "</div>";

echo "</body></html>";

// Close connection
if ($conn) {
    $conn->close();
}
?>
