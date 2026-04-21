<?php
/**
 * Admin Login Check - Debug authentication issues
 */

require_once 'db_connection.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Check - Salem Dominion Ministries</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='card-header text-center'>
                <h3><i class='fas fa-user-shield me-2'></i>Admin Login Check</h3>
            </div>
            <div class='card-body'>";

echo "<h4>Checking Admin Login System...</h4>";

// Test 1: Check database connection
echo "<div class='mb-3'>
        <h5>Test 1: Database Connection</h5>";

$conn = getConnection();
if ($conn) {
    echo "<span class='status-ok'>SUCCESS</span> - Database connected<br>";
} else {
    echo "<span class='status-error'>FAILED</span> - Database connection failed<br>";
    echo "</div></div></body></html>";
    exit;
}

echo "</div>";

// Test 2: Check admin_users table
echo "<div class='mb-3'>
        <h5>Test 2: Admin Users Table</h5>";

$table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($table_check && $table_check->num_rows > 0) {
    echo "<span class='status-ok'>SUCCESS</span> - admin_users table exists<br>";
    
    // Check if there are any admin users
    $user_count = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
    $count = $user_count->fetch_assoc()['count'];
    echo "<small>Found $count active admin users</small><br>";
    
    if ($count > 0) {
        // Show admin users (without passwords)
        $users = $conn->query("SELECT id, username, full_name, email, role, is_active, created_at FROM admin_users");
        echo "<div class='mt-3'>
                <h6>Admin Users:</h6>
                <table class='table table-sm'>
                    <tr><th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr>";
        
        while ($user = $users->fetch_assoc()) {
            echo "<tr>
                    <td>{$user['id']}</td>
                    <td>{$user['username']}</td>
                    <td>{$user['full_name']}</td>
                    <td>{$user['email']}</td>
                    <td>{$user['role']}</td>
                    <td>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>
                  </tr>";
        }
        echo "</table>
              </div>";
        
        // Test login credentials
        echo "<div class='mt-3'>
                <h6>Testing Login Credentials:</h6>";
        
        $test_username = 'MusasiziFaty';
        $test_password = '123456';
        
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->bind_param("s", $test_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            echo "<small>Found user: {$admin['username']}</small><br>";
            
            // Check password verification
            if (password_verify($test_password, $admin['password'])) {
                echo "<span class='status-ok'>SUCCESS</span> - Password verification works<br>";
            } else {
                echo "<span class='status-error'>FAILED</span> - Password verification failed<br>";
                echo "<small>Password hash in database: " . substr($admin['password'], 0, 20) . "...</small><br>";
                echo "<small>Test password hash: " . substr(password_hash($test_password, PASSWORD_DEFAULT), 0, 20) . "...</small><br>";
                
                // Fix the password
                $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hash, $admin['id']);
                if ($update_stmt->execute()) {
                    echo "<span class='status-warning'>FIXED</span> - Password has been updated<br>";
                    echo "<small>Try logging in again with: MusasiziFaty / 123456</small><br>";
                }
                $update_stmt->close();
            }
        } else {
            echo "<span class='status-error'>FAILED</span> - User 'MusasiziFaty' not found<br>";
            
            // Create the admin user
            $password_hash = password_hash($test_password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
            $full_name = 'Musasizi Faty';
            $email = 'admin@salemministries.org';
            $role = 'super_admin';
            $insert_stmt->bind_param("sssss", $test_username, $password_hash, $full_name, $email, $role);
            
            if ($insert_stmt->execute()) {
                echo "<span class='status-warning'>CREATED</span> - Admin user has been created<br>";
                echo "<small>Try logging in with: MusasiziFaty / 123456</small><br>";
            } else {
                echo "<span class='status-error'>FAILED</span> - Could not create admin user<br>";
            }
            $insert_stmt->close();
        }
        
        $stmt->close();
        
    } else {
        echo "<span class='status-warning'>WARNING</span> - No active admin users found<br>";
        
        // Create admin user
        $test_username = 'MusasiziFaty';
        $test_password = '123456';
        $password_hash = password_hash($test_password, PASSWORD_DEFAULT);
        
        $insert_stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $full_name = 'Musasizi Faty';
        $email = 'admin@salemministries.org';
        $role = 'super_admin';
        $insert_stmt->bind_param("sssss", $test_username, $password_hash, $full_name, $email, $role);
        
        if ($insert_stmt->execute()) {
            echo "<span class='status-warning'>CREATED</span> - Admin user has been created<br>";
            echo "<small>Try logging in with: MusasiziFaty / 123456</small><br>";
        } else {
            echo "<span class='status-error'>FAILED</span> - Could not create admin user<br>";
        }
        $insert_stmt->close();
    }
    
} else {
    echo "<span class='status-error'>FAILED</span> - admin_users table does not exist<br>";
}

echo "</div>";

$conn->close();

echo "
                <div class='text-center mt-4'>
                    <a href='admin/' class='btn btn-primary me-2'>
                        <i class='fas fa-sign-in-alt me-2'></i>Try Admin Login
                    </a>
                    <a href='index.php' class='btn btn-outline-primary'>
                        <i class='fas fa-home me-2'></i>Visit Website
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
