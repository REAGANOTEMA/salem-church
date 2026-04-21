<?php
/**
 * Database Connection Debug Script
 * Diagnoses and fixes database connection issues
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Debug - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-info { color: #17a2b8; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-bug me-2'></i>Database Connection Debug</h3>
            </div>
            <div class='card-body'>
                <h4>Step 1: Testing MySQL Server Connection</h4>";

// Test 1: Basic MySQL connection without database
try {
    $conn_basic = new mysqli('localhost', 'root', '');
    if ($conn_basic->connect_error) {
        echo "<div class='alert alert-danger'>
                <i class='fas fa-times me-2'></i>
                <span class='status-error'>FAILED:</span> Cannot connect to MySQL server<br>
                <strong>Error:</strong> " . htmlspecialchars($conn_basic->connect_error) . "
              </div>";
        echo "<div class='alert alert-warning'>
                <i class='fas fa-info-circle me-2'></i>
                <strong>Solution:</strong> Check if MySQL/XAMPP is running<br>
                1. Start XAMPP Control Panel<br>
                2. Start MySQL service<br>
                3. Try again
              </div>";
    } else {
        echo "<div class='alert alert-success'>
                <i class='fas fa-check me-2'></i>
                <span class='status-ok'>SUCCESS:</span> MySQL server connection works
              </div>";
        $conn_basic->close();
        
        // Test 2: Check if database exists
        echo "<h4>Step 2: Checking Database Existence</h4>";
        $conn_check = new mysqli('localhost', 'root', '');
        $databases = $conn_check->query("SHOW DATABASES LIKE 'salem_dominion_ministries'");
        
        if ($databases && $databases->num_rows > 0) {
            echo "<div class='alert alert-success'>
                    <i class='fas fa-check me-2'></i>
                    <span class='status-ok'>SUCCESS:</span> Database 'salem_dominion_ministries' exists
                  </div>";
            
            // Test 3: Try connecting to the specific database
            echo "<h4>Step 3: Testing Full Database Connection</h4>";
            require_once 'db_connection.php';
            $conn = getConnection();
            
            if ($conn) {
                echo "<div class='alert alert-success'>
                        <i class='fas fa-check me-2'></i>
                        <span class='status-ok'>SUCCESS:</span> Full database connection works!
                      </div>";
                
                // Test 4: Check if admin_users table exists
                echo "<h4>Step 4: Checking Required Tables</h4>";
                $tables = $conn->query("SHOW TABLES LIKE 'admin_users'");
                if ($tables && $tables->num_rows > 0) {
                    echo "<div class='alert alert-success'>
                            <i class='fas fa-check me-2'></i>
                            <span class='status-ok'>SUCCESS:</span> admin_users table exists
                          </div>";
                    
                    // Test 5: Check admin users
                    $users = $conn->query("SELECT COUNT(*) as count FROM admin_users");
                    if ($users) {
                        $count = $users->fetch_assoc()['count'];
                        echo "<div class='alert alert-success'>
                                <i class='fas fa-check me-2'></i>
                                <span class='status-ok'>SUCCESS:</span> Found $count admin users
                              </div>";
                    }
                } else {
                    echo "<div class='alert alert-warning'>
                            <i class='fas fa-exclamation-triangle me-2'></i>
                            <span class='status-info'>WARNING:</span> admin_users table missing<br>
                            <strong>Solution:</strong> <a href='import_database.php'>Import the database</a>
                          </div>";
                }
                
                $conn->close();
                
                echo "<div class='alert alert-success text-center'>
                        <h4><i class='fas fa-check-circle me-2'></i>Database is Ready!</h4>
                        <p>Your database connection is working perfectly.</p>
                        <a href='admin/' class='btn btn-primary btn-lg me-2'>
                            <i class='fas fa-sign-in-alt me-2'></i>Go to Admin Panel
                        </a>
                        <a href='index.php' class='btn btn-outline-primary btn-lg'>
                            <i class='fas fa-home me-2'></i>Visit Website
                        </a>
                      </div>";
                
            } else {
                echo "<div class='alert alert-danger'>
                        <i class='fas fa-times me-2'></i>
                        <span class='status-error'>FAILED:</span> Cannot connect to database<br>
                        <strong>Error:</strong> Check database name and permissions
                      </div>";
            }
            
        } else {
            echo "<div class='alert alert-warning'>
                    <i class='fas fa-exclamation-triangle me-2'></i>
                    <span class='status-info'>WARNING:</span> Database 'salem_dominion_ministries' does not exist<br>
                    <strong>Solution:</strong> <a href='import_database.php'>Import the database</a>
                  </div>";
            
            // Show available databases
            $all_dbs = $conn_check->query("SHOW DATABASES");
            echo "<div class='alert alert-info'>
                    <h5>Available Databases:</h5>
                    <ul>";
            while ($db = $all_dbs->fetch_array()) {
                if ($db[0] !== 'information_schema' && $db[0] !== 'mysql' && $db[0] !== 'performance_schema') {
                    echo "<li>" . htmlspecialchars($db[0]) . "</li>";
                }
            }
            echo "</ul>
                  </div>";
        }
        $conn_check->close();
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <i class='fas fa-times me-2'></i>
            <span class='status-error'>ERROR:</span> " . htmlspecialchars($e->getMessage()) . "
          </div>";
}

echo "
                <div class='mt-4'>
                    <h5>Current Configuration:</h5>
                    <pre>
Host: localhost
User: root
Password: (empty)
Database: salem_dominion_ministries
                    </pre>
                </div>
                
                <div class='mt-4'>
                    <h5>Troubleshooting Steps:</h5>
                    <ol>
                        <li>Make sure XAMPP MySQL service is running</li>
                        <li>Check if database 'salem_dominion_ministries' exists</li>
                        <li>Import the SQL file if database is missing</li>
                        <li>Verify MySQL user permissions</li>
                    </ol>
                </div>
                
                <div class='text-center mt-4'>
                    <a href='import_database.php' class='btn btn-warning me-2'>
                        <i class='fas fa-upload me-2'></i>Import Database
                    </a>
                    <a href='verify_database.php' class='btn btn-info me-2'>
                        <i class='fas fa-check me-2'></i>Verify Database
                    </a>
                    <a href='admin/' class='btn btn-primary'>
                        <i class='fas fa-sign-in-alt me-2'></i>Try Admin Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
