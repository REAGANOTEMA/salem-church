<?php
/**
 * PRODUCTION LOGIN DIAGNOSTIC
 * Upload this to your production server root (alongside index.php)
 * Visit: https://salemdominionministries.com/diagnose_login.php
 * DELETE THIS FILE AFTER DIAGNOSIS
 */

header('Content-Type: text/plain');
echo "========================================\n";
echo "  SALEM CHURCH - LOGIN DIAGNOSTIC\n";
echo "  " . date('Y-m-d d H:i:s') . "\n";
echo "========================================\n\n";

// Step 1: .env and config
echo "=== STEP 1: .env FILE ===\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo ".env file: EXISTS\n";
    $envContent = file_get_contents($envPath);
    // Show DB keys only, mask passwords
    foreach (['DB_HOST','DB_USER','DB_PASSWORD','DB_NAME','ADMIN_DB_HOST','ADMIN_DB_USER','ADMIN_DB_PASSWORD','ADMIN_DB_NAME'] as $key) {
        if (preg_match("/^{$key}=(.+)$/m", $envContent, $m)) {
            $val = $m[1];
            if (strpos($key, 'PASS') !== false) {
                echo "  {$key} = " . (empty($val) ? '[EMPTY!]' : '[SET len=' . strlen($val) . ']') . "\n";
            } else {
                echo "  {$key} = {$val}\n";
            }
        } else {
            echo "  {$key} = [NOT FOUND IN .env!]\n";
        }
    }
} else {
    echo ".env file: MISSING! Using config.php defaults\n";
}
echo "\n";

// Step 2: Config constants
echo "=== STEP 2: CONFIG CONSTANTS ===\n";
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/config.php';
echo "DB_HOST = " . DB_HOST . "\n";
echo "DB_USER = " . DB_USER . "\n";
echo "DB_NAME = " . DB_NAME . "\n";
echo "DB_PASS = " . (DB_PASS ? '[SET]' : '[EMPTY!]') . "\n";
echo "ADMIN_DB_HOST = " . ADMIN_DB_HOST . "\n";
echo "ADMIN_DB_USER = " . ADMIN_DB_USER . "\n";
echo "ADMIN_DB_NAME = " . ADMIN_DB_NAME . "\n";
echo "ADMIN_DB_PASS = " . (ADMIN_DB_PASS ? '[SET]' : '[EMPTY!]') . "\n";
echo "HASH_ALGO = " . (defined('HASH_ALGO') ? HASH_ALGO : 'NOT DEFINED') . "\n";
echo "PHP Version = " . phpversion() . "\n";
echo "\n";

// Step 3: Database connection
echo "=== STEP 3: DATABASE CONNECTION ===\n";
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

try {
    $adminDb = Database::getNamed('admin');
    $pdo = $adminDb->getPdo();
    echo "Admin DB connection: SUCCESS\n";
    echo "Database name: " . $adminDb->getDbName() . "\n";
    echo "Server version: " . $pdo->query("SELECT VERSION()")->fetchColumn() . "\n";
    echo "Current user: " . $pdo->query("SELECT CURRENT_USER()")->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Admin DB connection: FAILED\n";
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\n*** ROOT CAUSE: Cannot connect to admin database ***\n";
    echo "Check .env credentials match what cPanel created.\n";
    exit;
}
echo "\n";

// Step 4: Check table exists
echo "=== STEP 4: TABLE CHECK ===\n";
try {
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    echo "Tables in " . ADMIN_DB_NAME . ": " . count($tables) . "\n";
    foreach ($tables as $t) {
        echo "  - {$t}\n";
    }
    
    if (!in_array('admin_users', $tables)) {
        echo "\n*** ROOT CAUSE: admin_users table DOES NOT EXIST ***\n";
        echo "You need to import sql/salemdominionmin_admin.sql\n";
        exit;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit;
}
echo "\n";

// Step 5: Check admin_users columns
echo "=== STEP 5: TABLE STRUCTURE ===\n";
try {
    $cols = [];
    $result = $pdo->query("DESCRIBE admin_users");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $cols[$row['Field']] = $row['Type'];
    }
    echo "Columns: " . implode(', ', array_keys($cols)) . "\n";
    
    $required = ['id','username','password','full_name','email','role','is_active','login_attempts','locked_until'];
    $missing = array_diff($required, array_keys($cols));
    if (!empty($missing)) {
        echo "*** MISSING COLUMNS: " . implode(', ', $missing) . " ***\n";
    } else {
        echo "All required columns: PRESENT\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 6: List ALL admin users
echo "=== STEP 6: ALL ADMIN USERS ===\n";
try {
    $users = $pdo->query("SELECT id, username, LEFT(password,40) as pw, full_name, email, role, is_active, login_attempts, locked_until FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);
    echo "Total users: " . count($users) . "\n\n";
    foreach ($users as $u) {
        echo "  ID={$u['id']}\n";
        echo "  username={$u['username']}\n";
        echo "  name={$u['full_name']}\n";
        echo "  email={$u['email']}\n";
        echo "  role={$u['role']}\n";
        echo "  active={$u['is_active']}\n";
        echo "  attempts={$u['login_attempts']}\n";
        echo "  locked=" . ($u['locked_until'] ?: 'no') . "\n";
        echo "  pw_start={$u['pw']}...\n\n";
    }
    
    if (empty($users)) {
        echo "*** ROOT CAUSE: admin_users table is EMPTY — no users exist ***\n";
        echo "Run the INSERT SQL to create SalemChurch user.\n";
        exit;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 7: Test login for SalemChurch specifically
echo "=== STEP 7: LOGIN SIMULATION (SalemChurch / Lovely2God) ===\n";
try {
    // Exact query from auth.php
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1");
    $stmt->execute(['SalemChurch', 'SalemChurch']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Try just username
        $stmt2 = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt2->execute(['SalemChurch']);
        $user2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($user2) {
            echo "User 'SalemChurch' EXISTS but is_active = {$user2['is_active']}\n";
            if ($user2['is_active'] == 0) {
                echo "*** ROOT CAUSE: Account is DISABLED (is_active=0) ***\n";
            }
        } else {
            echo "User 'SalemChurch' NOT FOUND in admin_users table\n";
            echo "Available users: ";
            $all = $pdo->query("SELECT username FROM admin_users")->fetchAll(PDO::FETCH_COLUMN);
            echo implode(', ', $all) . "\n";
            echo "*** ROOT CAUSE: SalemChurch user does not exist. Run the INSERT SQL. ***\n";
        }
    } else {
        echo "User found: YES (id={$user['id']})\n";
        echo "is_active: {$user['is_active']}\n";
        
        $locked = $user['locked_until'] && strtotime($user['locked_until']) > time();
        echo "locked: " . ($locked ? 'YES' : 'no') . "\n";
        
        $pwOk = password_verify('Lovely2God', $user['password']);
        echo "password_verify('Lovely2God'): " . ($pwOk ? 'PASS' : 'FAIL') . "\n";
        
        $hashInfo = password_get_info($user['password']);
        echo "hash algorithm: {$hashInfo['algoName']}\n";
        echo "hash length: " . strlen($user['password']) . "\n";
        
        if ($pwOk && !$locked && $user['is_active']) {
            echo "\n*** LOGIN WOULD SUCCEED — No issue found locally ***\n";
            echo "If production still fails, the code on production is outdated.\n";
            echo "Deploy the latest code from GitHub: git pull origin main\n";
        } elseif (!$pwOk) {
            echo "\n*** ROOT CAUSE: password_verify FAILED — hash does not match 'Lovely2God' ***\n";
            echo "The password hash in the database does not match this password.\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 8: Check if auth.php functions work
echo "=== STEP 8: AUTH CLASS TEST ===\n";
try {
    require_once __DIR__ . '/includes/auth.php';
    $auth = auth();
    echo "auth() singleton: OK\n";
    
    // Simulate full login
    $result = $auth->adminLogin('SalemChurch', 'Lovely2God');
    echo "auth()->adminLogin() result:\n";
    echo "  success: " . ($result['success'] ? 'TRUE' : 'FALSE') . "\n";
    echo "  message: {$result['message']}\n";
    
    if ($result['success']) {
        echo "\n*** FULL LOGIN FLOW WORKS — Session vars set correctly ***\n";
        echo "admin_logged_in: " . ($_SESSION['admin_logged_in'] ? 'true' : 'false') . "\n";
        echo "admin_id: " . ($_SESSION['admin_id'] ?? 'not set') . "\n";
        echo "admin_username: " . ($_SESSION['admin_username'] ?? 'not set') . "\n";
        echo "admin_role: " . ($_SESSION['admin_role'] ?? 'not set') . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 9: Check error log
echo "=== STEP 9: RECENT ERROR LOG ===\n";
$logPath = __DIR__ . '/uploads/error.log';
if (file_exists($logPath)) {
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recent = array_slice($lines, -10);
    echo "Last 10 log entries:\n";
    foreach ($recent as $l) {
        echo "  {$l}\n";
    }
} else {
    echo "No error log found\n";
}

echo "\n========================================\n";
echo "  DIAGNOSIS COMPLETE\n";
echo "========================================\n";
echo "DELETE THIS FILE AFTER DIAGNOSIS!\n";
