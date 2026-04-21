<?php
// Simple Database Connection Test
echo "<h2>Database Connection Test</h2>";

// Test 1: Check if MySQL extension is available
echo "<h3>1. MySQL Extension Check:</h3>";
if (extension_loaded('mysqli')) {
    echo "MySQLi extension is loaded.<br>";
} else {
    echo "ERROR: MySQLi extension is NOT loaded.<br>";
}

// Test 2: Try basic MySQL connection
echo "<h3>2. Basic Connection Test:</h3>";
try {
    $conn = new mysqli('localhost', 'root', 'ReagaN23#');
    if ($conn->connect_error) {
        echo "ERROR: Cannot connect to MySQL server.<br>";
        echo "Error: " . $conn->connect_error . "<br>";
    } else {
        echo "SUCCESS: Connected to MySQL server.<br>";
        
        // Test 3: Check if database exists
        echo "<h3>3. Database Check:</h3>";
        $result = $conn->query("SHOW DATABASES LIKE 'salem_dominion_ministries'");
        if ($result && $result->num_rows > 0) {
            echo "SUCCESS: Database 'salem_dominion_ministries' exists.<br>";
            
            // Test 4: Try to use the database
            echo "<h3>4. Database Usage Test:</h3>";
            if ($conn->select_db('salem_dominion_ministries')) {
                echo "SUCCESS: Can use the database.<br>";
                
                // Test 5: Check if tables exist
                echo "<h3>5. Tables Check:</h3>";
                $tables = $conn->query("SHOW TABLES");
                if ($tables && $tables->num_rows > 0) {
                    echo "SUCCESS: Found " . $tables->num_rows . " tables.<br>";
                    while ($table = $tables->fetch_row()) {
                        echo "- " . $table[0] . "<br>";
                    }
                } else {
                    echo "WARNING: No tables found in database.<br>";
                }
            } else {
                echo "ERROR: Cannot use the database.<br>";
            }
        } else {
            echo "WARNING: Database 'salem_dominion_ministries' does not exist.<br>";
            echo "Trying to create it...<br>";
            if ($conn->query("CREATE DATABASE salem_dominion_ministries")) {
                echo "SUCCESS: Database created.<br>";
            } else {
                echo "ERROR: Cannot create database.<br>";
            }
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "<br>";
}

// Test 6: Alternative connection (try with no password)
echo "<h3>6. Alternative Connection Test (no password):</h3>";
try {
    $conn2 = new mysqli('localhost', 'root', '');
    if ($conn2->connect_error) {
        echo "ERROR: Cannot connect without password.<br>";
    } else {
        echo "SUCCESS: Connected without password.<br>";
        $conn2->close();
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "<br>";
}

echo "<h3>Test Complete</h3>";
echo "<p><a href='admin_dashboard.php'>Back to Admin Dashboard</a></p>";
?>
