<?php
/**
 * Database Verification Script
 * Verifies that the database is properly set up and working
 */

require_once 'db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Verification - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 900px; margin-top: 50px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .table-responsive { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-database me-2'></i>Database Verification</h3>
            </div>
            <div class='card-body'>
                <h4><i class='fas fa-cog fa-spin me-2'></i>Checking Database...</h4>";

// Test database connection
$conn = getConnection();
if (!$conn) {
    echo "<div class='alert alert-danger'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            <strong>Database connection failed!</strong><br>
            Please check your database configuration in db_connection.php
          </div>";
} else {
    echo "<div class='alert alert-success'>
            <i class='fas fa-check-circle me-2'></i>
            <strong>Database connected successfully!</strong>
          </div>";
    
    // Check database name
    $result = $conn->query("SELECT DATABASE() as db_name");
    if ($result) {
        $dbName = $result->fetch_assoc()['db_name'];
        echo "<div class='alert alert-info'>
                <i class='fas fa-info-circle me-2'></i>
                <strong>Current Database:</strong> " . htmlspecialchars($dbName) . "
              </div>";
    }
    
    // Check if admin_users table exists and has data
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        echo "<div class='alert alert-success'>
                <i class='fas fa-check-circle me-2'></i>
                <strong>admin_users table exists</strong>
              </div>";
        
        // Check admin users
        $adminResult = $conn->query("SELECT id, username, full_name, role, is_active FROM admin_users");
        if ($adminResult) {
            echo "<h5><i class='fas fa-users me-2'></i>Admin Users:</h5>
                  <div class='table-responsive'>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            while ($admin = $adminResult->fetch_assoc()) {
                $status = $admin['is_active'] ? '<span class=\"status-ok\">Active</span>' : '<span class=\"status-error\">Inactive</span>';
                echo "<tr>
                        <td>{$admin['id']}</td>
                        <td>{$admin['username']}</td>
                        <td>{$admin['full_name']}</td>
                        <td>" . ucfirst($admin['role']) . "</td>
                        <td>$status</td>
                      </tr>";
            }
            
            echo "</tbody></table></div>";
        }
    } else {
        echo "<div class='alert alert-warning'>
                <i class='fas fa-exclamation-triangle me-2'></i>
                <strong>admin_users table not found!</strong><br>
                Please import the database first.
              </div>";
    }
    
    // Check other important tables
    $importantTables = ['sermons', 'events', 'news', 'users', 'donations', 'testimonials'];
    echo "<h5><i class='fas fa-table me-2'></i>Database Tables Status:</h5>
          <div class='table-responsive'>
            <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Status</th>
                        <th>Records</th>
                    </tr>
                </thead>
                <tbody>";
    
    foreach ($importantTables as $table) {
        $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $countResult = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
            echo "<tr>
                    <td>$table</td>
                    <td><span class='status-ok'>Exists</span></td>
                    <td>$count</td>
                  </tr>";
        } else {
            echo "<tr>
                    <td>$table</td>
                    <td><span class='status-error'>Missing</span></td>
                    <td>-</td>
                  </tr>";
        }
    }
    
    echo "</tbody></table></div>";
    
    $conn->close();
}

echo "
                <div class='text-center mt-4'>
                    <a href='admin/' class='btn btn-primary btn-lg me-2'>
                        <i class='fas fa-sign-in-alt me-2'></i>Go to Admin Panel
                    </a>
                    <a href='import_database.php' class='btn btn-outline-primary btn-lg me-2'>
                        <i class='fas fa-upload me-2'></i>Import Database
                    </a>
                    <a href='index.php' class='btn btn-outline-secondary btn-lg'>
                        <i class='fas fa-home me-2'></i>Visit Website
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
