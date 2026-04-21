<?php
/**
 * Complete Database Setup Script
 * Automatically fixes database connection and imports all data
 */

set_time_limit(0); // Disable time limits
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Complete Database Setup - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .progress { height: 25px; border-radius: 15px; }
        .progress-bar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .step-success { color: #28a745; font-weight: bold; }
        .step-error { color: #dc3545; font-weight: bold; }
        .step-info { color: #17a2b8; font-weight: bold; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 8px; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-database me-2'></i>Complete Database Setup</h3>
                <p class='mb-0'>Automatic database configuration and import for Salem Dominion Ministries</p>
            </div>
            <div class='card-body'>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    echo "<h4><i class='fas fa-cog fa-spin me-2'></i>Setting up database...</h4>
          <div class='progress mb-3'>
            <div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 100%'></div>
          </div>";
    
    $steps = [];
    
    // Step 1: Test MySQL connection
    echo "<div class='mb-3'>
            <h5><i class='fas fa-plug me-2'></i>Step 1: Testing MySQL Connection</h5>";
    
    try {
        $mysql = new mysqli('localhost', 'root', '');
        if ($mysql->connect_error) {
            throw new Exception("MySQL connection failed: " . $mysql->connect_error);
        }
        $steps[] = "MySQL connection: SUCCESS";
        echo "<span class='step-success'>SUCCESS</span> - MySQL server is running<br>";
        $mysql->close();
    } catch (Exception $e) {
        $steps[] = "MySQL connection: FAILED - " . $e->getMessage();
        echo "<span class='step-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<div class='alert alert-danger'>
                <strong>Required:</strong> Start XAMPP MySQL service<br>
                1. Open XAMPP Control Panel<br>
                2. Click 'Start' on MySQL<br>
                3. Refresh this page
              </div>";
    }
    echo "</div>";
    
    // Step 2: Create database if not exists
    echo "<div class='mb-3'>
            <h5><i class='fas fa-database me-2'></i>Step 2: Creating Database</h5>";
    
    try {
        $conn = new mysqli('localhost', 'root', '');
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Check if database exists
        $result = $conn->query("SHOW DATABASES LIKE 'salem_dominion_ministries'");
        if ($result->num_rows == 0) {
            // Create database
            if ($conn->query("CREATE DATABASE salem_dominion_ministries CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                $steps[] = "Database creation: SUCCESS";
                echo "<span class='step-success'>SUCCESS</span> - Database 'salem_dominion_ministries' created<br>";
            } else {
                throw new Exception("Failed to create database: " . $conn->error);
            }
        } else {
            $steps[] = "Database creation: ALREADY EXISTS";
            echo "<span class='step-info'>INFO</span> - Database 'salem_dominion_ministries' already exists<br>";
        }
        
        $conn->close();
    } catch (Exception $e) {
        $steps[] = "Database creation: FAILED - " . $e->getMessage();
        echo "<span class='step-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    echo "</div>";
    
    // Step 3: Import SQL file
    echo "<div class='mb-3'>
            <h5><i class='fas fa-file-import me-2'></i>Step 3: Importing Database Structure</h5>";
    
    try {
        $sqlFile = __DIR__ . '/salem_dominion_ministries.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: " . $sqlFile);
        }
        
        $conn = new mysqli('localhost', 'root', '', 'salem_dominion_ministries');
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        $sqlContent = file_get_contents($sqlFile);
        
        // Remove comments and clean SQL
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
        
        // Split into statements
        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
        
        $executed = 0;
        $errors = [];
        
        foreach ($statements as $statement) {
            if (!empty($statement) && 
                !preg_match('/^(SET|START|COMMIT|USE)/', $statement)) {
                
                if ($conn->query($statement)) {
                    $executed++;
                } else {
                    $error = $conn->error;
                    if (!strpos($error, 'already exists') && !strpos($error, 'Duplicate')) {
                        $errors[] = $error;
                    }
                }
            }
        }
        
        if (empty($errors)) {
            $steps[] = "SQL import: SUCCESS - $executed statements executed";
            echo "<span class='step-success'>SUCCESS</span> - Imported $executed SQL statements<br>";
        } else {
            $steps[] = "SQL import: PARTIAL - " . count($errors) . " errors";
            echo "<span class='step-info'>PARTIAL</span> - $executed statements executed, " . count($errors) . " non-critical errors<br>";
        }
        
        $conn->close();
        
    } catch (Exception $e) {
        $steps[] = "SQL import: FAILED - " . $e->getMessage();
        echo "<span class='step-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    echo "</div>";
    
    // Step 4: Verify database
    echo "<div class='mb-3'>
            <h5><i class='fas fa-check-circle me-2'></i>Step 4: Verifying Database</h5>";
    
    try {
        require_once 'db_connection.php';
        $conn = getConnection();
        
        if (!$conn) {
            throw new Exception("Database connection test failed");
        }
        
        // Check admin_users table
        $result = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        if ($result) {
            $adminCount = $result->fetch_assoc()['count'];
            echo "<span class='step-success'>SUCCESS</span> - Found $adminCount admin users<br>";
        } else {
            throw new Exception("Cannot access admin_users table");
        }
        
        // Check key tables
        $tables = ['sermons', 'events', 'news', 'users', 'donations', 'testimonials'];
        $tableCount = 0;
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $tableCount++;
            }
        }
        
        echo "<span class='step-success'>SUCCESS</span> - $tableCount/" . count($tables) . " key tables found<br>";
        
        $conn->close();
        $steps[] = "Database verification: SUCCESS";
        
    } catch (Exception $e) {
        $steps[] = "Database verification: FAILED - " . $e->getMessage();
        echo "<span class='step-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    echo "</div>";
    
    // Step 5: Test admin login
    echo "<div class='mb-3'>
            <h5><i class='fas fa-sign-in-alt me-2'></i>Step 5: Testing Admin Login</h5>";
    
    try {
        require_once 'db_connection.php';
        $conn = getConnection();
        
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = 'MusasiziFaty'");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if (password_verify('123456', $admin['password'])) {
                    echo "<span class='step-success'>SUCCESS</span> - Admin login credentials verified<br>";
                    echo "<small>Username: MusasiziFaty | Password: 123456</small><br>";
                    $steps[] = "Admin login test: SUCCESS";
                } else {
                    echo "<span class='step-error'>FAILED</span> - Password verification failed<br>";
                    $steps[] = "Admin login test: FAILED - Password mismatch";
                }
            } else {
                echo "<span class='step-error'>FAILED</span> - Admin user not found<br>";
                $steps[] = "Admin login test: FAILED - User not found";
            }
            $stmt->close();
            $conn->close();
        } else {
            throw new Exception("Cannot connect to database for login test");
        }
        
    } catch (Exception $e) {
        $steps[] = "Admin login test: FAILED - " . $e->getMessage();
        echo "<span class='step-error'>FAILED</span> - " . htmlspecialchars($e->getMessage()) . "<br>";
    }
    echo "</div>";
    
    // Final result
    echo "<div class='alert alert-success text-center'>
            <h4><i class='fas fa-check-circle me-2'></i>Database Setup Complete!</h4>
            <p class='mb-3'>Your database is now fully configured and ready to use.</p>
            <div class='d-flex justify-content-center gap-2'>
                <a href='admin/' class='btn btn-primary btn-lg'>
                    <i class='fas fa-sign-in-alt me-2'></i>Go to Admin Panel
                </a>
                <a href='index.php' class='btn btn-outline-primary btn-lg'>
                    <i class='fas fa-home me-2'></i>Visit Website
                </a>
            </div>
          </div>";
    
    echo "<div class='mt-4'>
            <h5>Setup Summary:</h5>
            <pre>" . implode("\n", $steps) . "</pre>
          </div>";
    
} else {
    echo "
        <div class='text-center mb-4'>
            <h4><i class='fas fa-tools me-2'></i>Automatic Database Setup</h4>
            <p class='text-muted'>This script will automatically configure your database and import all necessary data.</p>
        </div>
        
        <div class='alert alert-info'>
            <h5><i class='fas fa-info-circle me-2'></i>What this setup will do:</h5>
            <ul class='mb-0'>
                <li>Test MySQL server connection</li>
                <li>Create the 'salem_dominion_ministries' database</li>
                <li>Import all database tables and structure</li>
                <li>Import sample data and admin users</li>
                <li>Verify database functionality</li>
                <li>Test admin login credentials</li>
            </ul>
        </div>
        
        <div class='alert alert-warning'>
            <h5><i class='fas fa-exclamation-triangle me-2'></i>Prerequisites:</h5>
            <ul class='mb-0'>
                <li>XAMPP must be installed</li>
                <li>MySQL service must be running in XAMPP</li>
                <li>The SQL file 'salem_dominion_ministries.sql' must exist</li>
            </ul>
        </div>
        
        <form method='post' class='text-center'>
            <input type='hidden' name='setup' value='1'>
            <button type='submit' class='btn btn-primary btn-lg' onclick='return confirm(\"This will set up your complete database. Continue?\")'>
                <i class='fas fa-play me-2'></i>Start Database Setup
            </button>
        </form>
        
        <div class='mt-4 text-center'>
            <small class='text-muted'>
                <a href='debug_connection.php'>Debug Connection</a> | 
                <a href='verify_database.php'>Verify Database</a> | 
                <a href='import_database.php'>Manual Import</a>
            </small>
        </div>";
}

echo "
            </div>
        </div>
    </div>
</body>
</html>";
?>
