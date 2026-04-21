<?php
/**
 * Database Import Script for Salem Dominion Ministries
 * This script will import the SQL file and set up the database
 */

// Disable time limits for large imports
set_time_limit(0);

// Database connection
require_once 'db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Import - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 800px; margin-top: 50px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .progress { height: 25px; border-radius: 15px; }
        .progress-bar { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-database me-2'></i>Database Import</h3>
            </div>
            <div class='card-body'>
";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'import') {
        echo "<h4><i class='fas fa-cog fa-spin me-2'></i>Importing Database...</h4>";
        echo "<div class='progress mb-3'>
                <div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 100%'></div>
              </div>";
        
        // Read SQL file
        $sqlFile = __DIR__ . '/salem_dominion_ministries.sql';
        if (!file_exists($sqlFile)) {
            echo "<div class='alert alert-danger'>
                    <i class='fas fa-exclamation-triangle me-2'></i>
                    <strong>SQL file not found!</strong> Please ensure 'salem_dominion_ministries.sql' exists in the root directory.
                  </div>";
        } else {
            $sqlContent = file_get_contents($sqlFile);
            
            // Connect to database
            $conn = getConnection();
            if (!$conn) {
                echo "<div class='alert alert-danger'>
                        <i class='fas fa-exclamation-triangle me-2'></i>
                        <strong>Database connection failed!</strong> Please check your database configuration.
                      </div>";
            } else {
                try {
                    // Split SQL into individual statements
                    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
                    
                    $executed = 0;
                    $errors = [];
                    
                    foreach ($statements as $statement) {
                        if (!empty($statement) && 
                            !preg_match('/^--/', $statement) && 
                            !preg_match('/^SET/', $statement) &&
                            !preg_match('/^START TRANSACTION/', $statement) &&
                            !preg_match('/^COMMIT/', $statement) &&
                            !preg_match('/^\/\*/', $statement)) {
                            
                            if ($conn->query($statement)) {
                                $executed++;
                            } else {
                                $errors[] = $conn->error;
                            }
                        }
                    }
                    
                    if (empty($errors)) {
                        echo "<div class='alert alert-success'>
                                <i class='fas fa-check-circle me-2'></i>
                                <strong>Database imported successfully!</strong><br>
                                Executed $executed statements successfully.
                              </div>";
                        
                        // Test database connection with new data
                        $testResult = $conn->query("SELECT COUNT(*) as count FROM admin_users");
                        if ($testResult) {
                            $adminCount = $testResult->fetch_assoc()['count'];
                            echo "<div class='alert alert-info'>
                                    <i class='fas fa-info-circle me-2'></i>
                                    <strong>Database verified!</strong><br>
                                    Found $adminCount admin users in database.
                                  </div>";
                        }
                        
                        echo "<div class='text-center mt-4'>
                                <a href='admin/' class='btn btn-primary btn-lg me-2'>
                                    <i class='fas fa-sign-in-alt me-2'></i>Go to Admin Panel
                                </a>
                                <a href='index.php' class='btn btn-outline-primary btn-lg'>
                                    <i class='fas fa-home me-2'></i>Visit Website
                                </a>
                              </div>";
                    } else {
                        echo "<div class='alert alert-warning'>
                                <i class='fas fa-exclamation-triangle me-2'></i>
                                <strong>Import completed with warnings:</strong><br>
                                Executed $executed statements successfully.<br>
                                " . count($errors) . " errors encountered.
                              </div>";
                        echo "<div class='alert alert-secondary'>
                                <strong>Errors:</strong><br>
                                <pre>" . implode("\n", array_slice($errors, 0, 5)) . "</pre>
                              </div>";
                    }
                    
                    $conn->close();
                    
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>
                            <i class='fas fa-exclamation-triangle me-2'></i>
                            <strong>Import failed:</strong> " . htmlspecialchars($e->getMessage()) . "
                          </div>";
                }
            }
        }
    } else {
        echo "<div class='alert alert-info'>
                <i class='fas fa-info-circle me-2'></i>
                <strong>Ready to import database!</strong><br>
                This will set up all tables and sample data for Salem Dominion Ministries.
              </div>";
    }
} else {
    // Show import form
    echo "<h4><i class='fas fa-database me-2'></i>Database Setup</h4>
          <p class='text-muted'>This will import the complete database structure and sample data for Salem Dominion Ministries.</p>
          
          <div class='alert alert-info'>
              <i class='fas fa-info-circle me-2'></i>
              <strong>What will be imported:</strong><br>
              <ul class='mb-0'>
                  <li>Admin users (MusasiziFaty / 123456)</li>
                  <li>All database tables (sermons, events, news, etc.)</li>
                  <li>Sample data for testing</li>
                  <li>Complete church management structure</li>
              </ul>
          </div>
          
          <form method='post' class='text-center'>
              <input type='hidden' name='action' value='import'>
              <button type='submit' class='btn btn-primary btn-lg'>
                  <i class='fas fa-upload me-2'></i>Import Database
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
