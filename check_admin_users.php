<?php
require_once 'db_connection.php';

$conn = getConnection();
if ($conn) {
    $result = $conn->query('SELECT username, full_name, role FROM admin_users');
    echo "Admin Users in Database:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Username: " . $row['username'] . " | Name: " . $row['full_name'] . " | Role: " . $row['role'] . "\n";
    }
    $conn->close();
} else {
    echo "Database connection failed\n";
}
?>
