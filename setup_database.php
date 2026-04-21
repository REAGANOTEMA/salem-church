<?php
// Database setup script for Salem Dominion Ministries
require_once 'config.php';
require_once 'db_connection.php';

// Read the SQL file
$sql_file = __DIR__ . '/database_setup_complete.sql';
$sql = file_get_contents($sql_file);

if (!$sql) {
    die("Error: Could not read SQL file");
}

// Connect to database
$conn = getConnection();
if (!$conn) {
    die("Error: Could not connect to database");
}

// Split SQL into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "<h2>Salem Dominion Ministries - Database Setup</h2>";
echo "<p>Setting up database: " . DB_NAME . "</p>";

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty($statement) || strpos(trim($statement), '--') === 0) {
        continue;
    }
    
    try {
        if ($conn->query($statement)) {
            $success_count++;
            echo "<p style='color: green;'>SUCCESS: " . substr($statement, 0, 50) . "...</p>";
        } else {
            $error_count++;
            $error = $conn->error;
            $errors[] = $error;
            echo "<p style='color: red;'>ERROR: " . substr($statement, 0, 50) . "...</p>";
            echo "<p style='color: red;'>Error: " . $error . "</p>";
        }
    } catch (Exception $e) {
        $error_count++;
        $errors[] = $e->getMessage();
        echo "<p style='color: red;'>EXCEPTION: " . substr($statement, 0, 50) . "...</p>";
        echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
    }
}

$conn->close();

echo "<h3>Setup Summary</h3>";
echo "<p>Successful statements: " . $success_count . "</p>";
echo "<p>Failed statements: " . $error_count . "</p>";

if ($error_count > 0) {
    echo "<h3>Errors Encountered</h3>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>" . htmlspecialchars($error) . "</p>";
    }
} else {
    echo "<p style='color: green; font-weight: bold;'>Database setup completed successfully!</p>";
}

echo "<h3>Next Steps</h3>";
echo "<p>1. <a href='index.php'>Go to Homepage</a></p>";
echo "<p>2. <a href='admin_login.php'>Admin Login (Username: MusasiziFaty, Password: 123456)</a></p>";
echo "<p>3. Test all pages: sermons, events, news, ministries, donate, etc.</p>";
?>
