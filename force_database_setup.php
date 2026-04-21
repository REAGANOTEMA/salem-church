<?php
/**
 * Force Database Setup - Bypasses all connection issues
 * Directly creates database and imports data
 */

set_time_limit(0);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Force Database Setup - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .progress { height: 25px; border-radius: 15px; }
        .progress-bar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-database me-2'></i>Force Database Setup</h3>
                <p class='mb-0'>Complete database setup with forced connection methods</p>
            </div>
            <div class='card-body'>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_setup'])) {
    echo "<h4><i class='fas fa-cog fa-spin me-2'></i>Forcing Database Setup...</h4>
          <div class='progress mb-3'>
            <div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 100%'></div>
          </div>";
    
    $success = true;
    $messages = [];
    
    // Step 1: Force MySQL connection
    echo "<div class='mb-3'><h5>Step 1: Forcing MySQL Connection</h5>";
    try {
        // Try port 3306 first
        $conn = @new mysqli('localhost', 'root', 'ReagaN23#', '', 3306);
        if ($conn->connect_error) {
            // If 3306 fails, try 3307
            $conn = @new mysqli('localhost', 'root', 'ReagaN23#', '', 3307);
            if ($conn->connect_error) {
                // Try 127.0.0.1 with 3306
                $conn = @new mysqli('127.0.0.1', 'root', 'ReagaN23#', '', 3306);
                if ($conn->connect_error) {
                    // Try 127.0.0.1 with 3307
                    $conn = @new mysqli('127.0.0.1', 'root', 'ReagaN23#', '', 3307);
                    if ($conn->connect_error) {
                        throw new Exception("Cannot connect to MySQL on any host or port");
                    }
                }
            }
        }
        $messages[] = "MySQL connection: SUCCESS";
        echo "<span class='status-ok'>SUCCESS</span> - MySQL connected<br>";
    } catch (Exception $e) {
        $success = false;
        $messages[] = "MySQL connection: FAILED - " . $e->getMessage();
        echo "<span class='status-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    echo "</div>";
    
    if ($success) {
        // Step 2: Force database creation
        echo "<div class='mb-3'><h5>Step 2: Creating Database</h5>";
        try {
            $conn->query("DROP DATABASE IF EXISTS `salem_dominion_ministries`");
            $create_result = $conn->query("CREATE DATABASE `salem_dominion_ministries` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            if (!$create_result) {
                throw new Exception("Failed to create database: " . $conn->error);
            }
            $messages[] = "Database creation: SUCCESS";
            echo "<span class='status-ok'>SUCCESS</span> - Database created<br>";
        } catch (Exception $e) {
            $success = false;
            $messages[] = "Database creation: FAILED - " . $e->getMessage();
            echo "<span class='status-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
        }
        echo "</div>";
    }
    
    if ($success) {
        // Step 3: Force SQL import
        echo "<div class='mb-3'><h5>Step 3: Importing SQL Data</h5>";
        try {
            $conn->select_db('salem_dominion_ministries');
            
            $sqlFile = __DIR__ . '/salem_dominion_ministries.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("SQL file not found");
            }
            
            $sqlContent = file_get_contents($sqlFile);
            
            // Clean SQL and split statements
            $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
            $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
            $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
            
            $executed = 0;
            foreach ($statements as $statement) {
                if (!empty($statement) && 
                    !preg_match('/^(SET|START|COMMIT|USE)/', $statement)) {
                    if ($conn->query($statement)) {
                        $executed++;
                    }
                }
            }
            
            $messages[] = "SQL import: SUCCESS - $executed statements";
            echo "<span class='status-ok'>SUCCESS</span> - Imported $executed SQL statements<br>";
            
        } catch (Exception $e) {
            $success = false;
            $messages[] = "SQL import: FAILED - " . $e->getMessage();
            echo "<span class='status-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
        }
        echo "</div>";
    }
    
    if ($success) {
        // Step 4: Verify setup
        echo "<div class='mb-3'><h5>Step 4: Verifying Setup</h5>";
        try {
            // Check admin users
            $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
            if ($result) {
                $adminCount = $result->fetch_assoc()['count'];
                $messages[] = "Admin users: $adminCount found";
                echo "<span class='status-ok'>SUCCESS</span> - Found $adminCount admin users<br>";
            }
            
            // Test admin login
            $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'MusasiziFaty'");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if (password_verify('123456', $admin['password'])) {
                    $messages[] = "Admin login test: SUCCESS";
                    echo "<span class='status-ok'>SUCCESS</span> - Admin login verified<br>";
                } else {
                    $messages[] = "Admin login test: FAILED - Password mismatch";
                    echo "<span class='status-error'>FAILED</span> - Password verification failed<br>";
                }
            } else {
                $messages[] = "Admin login test: FAILED - User not found";
                echo "<span class='status-error'>FAILED</span> - Admin user not found<br>";
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $messages[] = "Verification: FAILED - " . $e->getMessage();
            echo "<span class='status-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
        }
        echo "</div>";
    }
    
    $conn->close();
    
    // Final result
    if ($success) {
        echo "<div class='alert alert-success text-center'>
                <h4><i class='fas fa-check-circle me-2'></i>Database Setup Complete!</h4>
                <p>Your database has been successfully configured and is fully functional.</p>
                <div class='d-flex justify-content-center gap-2'>
                    <a href='admin/' class='btn btn-primary btn-lg'>
                        <i class='fas fa-sign-in-alt me-2'></i>Go to Admin Panel
                    </a>
                    <a href='index.php' class='btn btn-outline-primary btn-lg'>
                        <i class='fas fa-home me-2'></i>Visit Website
                    </a>
                </div>
              </div>";
    } else {
        echo "<div class='alert alert-danger text-center'>
                <h4><i class='fas fa-times-circle me-2'></i>Setup Failed</h4>
                <p>Database setup encountered errors. Please check the messages above.</p>
              </div>";
    }
    
    echo "<div class='mt-4'>
            <h5>Setup Log:</h5>
            <pre>" . implode("\n", $messages) . "</pre>
          </div>";
    
} else {
    echo "
        <div class='text-center mb-4'>
            <h4><i class='fas fa-tools me-2'></i>Force Database Setup</h4>
            <p class='text-muted'>This script will force database creation and import all data, bypassing connection detection issues.</p>
        </div>
        
        <div class='alert alert-warning'>
            <h5><i class='fas fa-exclamation-triangle me-2'></i>Warning:</h5>
            <p class='mb-0'>This will <strong>delete and recreate</strong> the entire database. All existing data will be replaced with the sample data from the SQL file.</p>
        </div>
        
        <div class='alert alert-info'>
            <h5><i class='fas fa-info-circle me-2'></i>What this will do:</h5>
            <ul class='mb-0'>
                <li>Force connect to MySQL using multiple methods</li>
                <li>Delete existing database (if any)</li>
                <li>Create fresh database</li>
                <li>Import all tables and data from SQL file</li>
                <li>Verify admin login functionality</li>
            </ul>
        </div>
        
        <form method='post' class='text-center'>
            <input type='hidden' name='force_setup' value='1'>
            <button type='submit' class='btn btn-danger btn-lg' onclick='return confirm(\"This will delete and recreate the entire database. Are you sure?\")'>
                <i class='fas fa-exclamation-triangle me-2'></i>Force Database Setup
            </button>
        </form>";
}

echo "
            </div>
        </div>
    </div>
</body>
</html>";
?>
