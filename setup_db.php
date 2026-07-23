<?php
/**
 * Database Setup Script - Salem Dominion Ministries
 * Run this once to create the database and import schema.
 * DELETE THIS FILE AFTER RUNNING for security.
 */

echo "<h2>Salem Dominion Ministries - Database Setup</h2>";

// Step 1: Connect to MySQL (no database) and create the database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "<p style='color:green'>Connected to MySQL server successfully.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Failed to connect to MySQL: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit(1);
}

// Step 2: Create database
$dbName = 'salem_dominion_ministries';
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "<p style='color:green'>Database '<b>{$dbName}</b>' created/verified.</p>";

// Step 3: Select database
$pdo->exec("USE `{$dbName}`");
echo "<p style='color:green'>Selected database.</p>";

// Step 4: Check existing tables
$result = $pdo->query("SHOW TABLES");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
echo "<p>Existing tables: " . count($tables) . "</p>";

// Step 5: Read and execute the SQL schema
$sqlFile = __DIR__ . '/database_full.sql';
if (!file_exists($sqlFile)) {
    echo "<p style='color:orange'>database_full.sql not found. Trying database_setup_complete.sql...</p>";
    $sqlFile = __DIR__ . '/database_setup_complete.sql';
}

if (file_exists($sqlFile)) {
    echo "<p>Importing from: " . basename($sqlFile) . "</p>";
    $sql = file_get_contents($sqlFile);
    
    // Remove BOM if present
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
    
    // Split by semicolons, but be careful with semicolons inside strings
    // Simple approach: split on lines that are just a semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $errors = 0;
    $skipped = 0;
    
    foreach ($statements as $stmt) {
        // Skip empty, comments-only, or SET statements
        $cleaned = preg_replace('/--.*$/m', '', $stmt);
        $cleaned = preg_replace('/\/\*.*?\*\//s', '', $cleaned);
        $cleaned = trim($cleaned);
        
        if ($cleaned === '' || stripos($cleaned, 'SET ') === 0) {
            $skipped++;
            continue;
        }
        
        // Check if it's a SELECT statement (skip)
        if (stripos($cleaned, 'SELECT ') === 0) {
            $skipped++;
            continue;
        }
        
        try {
            $pdo->exec($stmt);
            $success++;
        } catch (PDOException $e) {
            $errMsg = $e->getMessage();
            // Ignore duplicate key errors and table exists errors
            if (strpos($errMsg, '1062') !== false || 
                strpos($errMsg, 'Duplicate') !== false ||
                strpos($errMsg, '1050') !== false ||
                strpos($errMsg, 'already exists') !== false) {
                $skipped++;
            } else {
                $errors++;
                echo "<p style='color:orange'>Warning: " . htmlspecialchars(substr($errMsg, 0, 200)) . "</p>";
                echo "<p style='color:gray;font-size:11px'>Statement: " . htmlspecialchars(substr($cleaned, 0, 150)) . "</p>";
            }
        }
    }
    
    echo "<p style='color:green'>SQL Import complete: <b>{$success}</b> succeeded, <b>{$skipped}</b> skipped/ignored, <b>{$errors}</b> errors.</p>";
} else {
    echo "<p style='color:red'>No SQL file found! Please place database_full.sql in the project root.</p>";
}

// Step 6: Verify tables exist
echo "<h3>Database Status:</h3>";
$result = $pdo->query("SHOW TABLES");
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
echo "<p>Total tables: <b>" . count($tables) . "</b></p>";

if (count($tables) > 0) {
    echo "<ul>";
    foreach ($tables as $table) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        echo "<li>{$table} ({$cnt} rows)</li>";
    }
    echo "</ul>";
}

// Step 7: Verify admin user exists
$admin = $pdo->query("SELECT id, username, full_name, role FROM admin_users LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
if ($admin) {
    echo "<h3>Admin Users:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th></tr>";
    foreach ($admin as $a) {
        echo "<tr><td>{$a['id']}</td><td>" . htmlspecialchars($a['username']) . "</td><td>" . htmlspecialchars($a['full_name']) . "</td><td>" . htmlspecialchars($a['role']) . "</td></tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p style='color:green;font-weight:bold'>Setup complete! You can now access:</p>";
echo "<p><a href='index.php'>Website Homepage</a></p>";
echo "<p><a href='admin/login.php'>Admin Login</a> (username: <b>admin</b> or <b>MusasiziFaty</b>, password: <b>password</b>)</p>";
echo "<hr>";
echo "<p style='color:red;font-weight:bold'>IMPORTANT: Delete this setup_db.php file for security!</p>";
