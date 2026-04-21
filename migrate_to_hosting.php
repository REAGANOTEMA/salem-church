<?php
/**
 * Database Migration Script - Local to Hosting
 * This script migrates all your data from localhost to hosting database
 */

// Database configurations
$local_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'ReagaN23#',
    'name' => 'salem_dominion_ministries',
    'port' => 3306
];

$hosting_config = [
    'host' => 'localhost',
    'user' => 'salemdominionmin_db',
    'pass' => 'CtYeTnGktDxy9UvdtZJF',
    'name' => 'salemdominionmin_db',
    'port' => 3306
];

echo "<h2>Salem Dominion Ministries - Database Migration</h2>";
echo "<p>Migrating from localhost to hosting database...</p>";

// Connect to both databases
try {
    $local_conn = new mysqli($local_config['host'], $local_config['user'], $local_config['pass'], $local_config['name'], $local_config['port']);
    $hosting_conn = new mysqli($hosting_config['host'], $hosting_config['user'], $hosting_config['pass'], $hosting_config['name'], $hosting_config['port']);
    
    if ($local_conn->connect_error) {
        throw new Exception("Local database connection failed: " . $local_conn->connect_error);
    }
    
    if ($hosting_conn->connect_error) {
        throw new Exception("Hosting database connection failed: " . $hosting_conn->connect_error);
    }
    
    echo "<p style='color: green;'>Connected to both databases successfully!</p>";
    
    // Get all tables from local database
    $tables = [];
    $result = $local_conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    echo "<p>Found " . count($tables) . " tables to migrate</p>";
    
    // Drop existing tables in hosting database
    echo "<p>Dropping existing tables in hosting database...</p>";
    $hosting_conn->query("SET FOREIGN_KEY_CHECKS=0");
    foreach ($tables as $table) {
        $hosting_conn->query("DROP TABLE IF EXISTS `$table`");
    }
    $hosting_conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    // Migrate each table
    foreach ($tables as $table) {
        echo "<p>Migrating table: <strong>$table</strong>...</p>";
        
        // Get create table statement
        $result = $local_conn->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_row();
        $create_table = $row[1];
        
        // Create table in hosting database
        if ($hosting_conn->query($create_table)) {
            echo "<p style='color: green;'>Table created successfully</p>";
        } else {
            echo "<p style='color: red;'>Error creating table: " . $hosting_conn->error . "</p>";
            continue;
        }
        
        // Get data from local table
        $data = [];
        $result = $local_conn->query("SELECT * FROM `$table`");
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        if (count($data) > 0) {
            // Insert data into hosting table
            $columns = array_keys($data[0]);
            $column_list = "`" . implode("`, `", $columns) . "`";
            
            foreach ($data as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $value = $row[$column];
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $hosting_conn->real_escape_string($value) . "'";
                    }
                }
                
                $sql = "INSERT INTO `$table` ($column_list) VALUES (" . implode(", ", $values) . ")";
                if (!$hosting_conn->query($sql)) {
                    echo "<p style='color: orange;'>Warning: Failed to insert row: " . $hosting_conn->error . "</p>";
                }
            }
            echo "<p style='color: green;'>Migrated " . count($data) . " records</p>";
        } else {
            echo "<p style='color: blue;'>No data to migrate</p>";
        }
    }
    
    // Verify migration
    echo "<h3>Migration Verification</h3>";
    $hosting_conn->query("SET FOREIGN_KEY_CHECKS=0");
    $result = $hosting_conn->query("SHOW TABLES");
    $hosting_tables = [];
    while ($row = $result->fetch_row()) {
        $hosting_tables[] = $row[0];
    }
    $hosting_conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    echo "<p>Hosting database now has " . count($hosting_tables) . " tables</p>";
    
    // Check key tables
    $key_tables = ['users', 'admin_users', 'messages', 'sermons', 'events'];
    foreach ($key_tables as $table) {
        if (in_array($table, $hosting_tables)) {
            $count = $hosting_conn->query("SELECT COUNT(*) FROM `$table`")->fetch_row()[0];
            echo "<p style='color: green;'>$table: $count records</p>";
        } else {
            echo "<p style='color: red;'>$table: NOT FOUND</p>";
        }
    }
    
    echo "<h3 style='color: green;'>Migration Completed Successfully!</h3>";
    echo "<p>Your hosting database is now ready with all data from localhost.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test your website on the hosting platform</li>";
    echo "<li>Verify all features work correctly</li>";
    echo "<li>Delete this migration script for security</li>";
    echo "</ul>";
    
    $local_conn->close();
    $hosting_conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Migration failed: " . $e->getMessage() . "</p>";
}
?>
