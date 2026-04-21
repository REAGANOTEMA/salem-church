<?php
/**
 * Database Error Handler for Hosting Platform
 * Provides graceful handling of database connection failures
 */

class DatabaseErrorHandler {
    private static $error_logged = false;
    
    /**
     * Handle database connection failure gracefully
     */
    public static function handleConnectionFailure($error_message = null) {
        // Log error only once per request
        if (!self::$error_logged) {
            error_log("Database Connection Failed: " . ($error_message ?? 'Unknown error'));
            self::$error_logged = true;
        }
        
        // Return user-friendly message or null for graceful degradation
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            return [
                'error' => true,
                'message' => 'Database connection failed: ' . $error_message,
                'debug_info' => self::getDebugInfo()
            ];
        }
        
        return null;
    }
    
    /**
     * Get safe database data with fallback
     */
    public static function safeQuery($query, $fallback_data = []) {
        try {
            $conn = getConnection();
            if (!$conn) {
                return self::handleConnectionFailure();
            }
            
            $result = $conn->query($query);
            if (!$result) {
                error_log("Query failed: " . $conn->error);
                return $fallback_data;
            }
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Database query exception: " . $e->getMessage());
            return $fallback_data;
        }
    }
    
    /**
     * Execute safe prepared statement
     */
    public static function safePreparedStatement($query, $params = [], $fallback_data = []) {
        try {
            $conn = getConnection();
            if (!$conn) {
                return self::handleConnectionFailure();
            }
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                return $fallback_data;
            }
            
            if (!empty($params)) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                $stmt->close();
                return $data;
            }
            
            $stmt->close();
            return $fallback_data;
            
        } catch (Exception $e) {
            error_log("Prepared statement exception: " . $e->getMessage());
            return $fallback_data;
        }
    }
    
    /**
     * Check database status and return appropriate response
     */
    public static function getDatabaseStatus() {
        try {
            $conn = getConnection();
            if (!$conn) {
                return [
                    'status' => 'error',
                    'message' => 'Database connection failed',
                    'connected' => false
                ];
            }
            
            // Test connection
            $test_result = $conn->query("SELECT 1 as test");
            if (!$test_result) {
                return [
                    'status' => 'error',
                    'message' => 'Database query failed',
                    'connected' => false
                ];
            }
            
            // Check tables
            $tables_check = $conn->query("SHOW TABLES");
            $tables = [];
            if ($tables_check) {
                while ($row = $tables_check->fetch_row()) {
                    $tables[] = $row[0];
                }
            }
            
            return [
                'status' => 'success',
                'message' => 'Database connected successfully',
                'connected' => true,
                'tables' => $tables,
                'table_count' => count($tables)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
                'connected' => false
            ];
        }
    }
    
    /**
     * Get debug information for development
     */
    private static function getDebugInfo() {
        return [
            'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
            'php_version' => PHP_VERSION,
            'mysql_version' => mysqli_get_client_info(),
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ];
    }
    
    /**
     * Display database maintenance message
     */
    public static function displayMaintenanceMessage() {
        if (!headers_sent()) {
            header('HTTP/1.0 503 Service Unavailable');
            header('Retry-After: 300'); // 5 minutes
        }
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Maintenance - Salem Dominion Ministries</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                .maintenance-container {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .maintenance-card {
                    background: white;
                    padding: 2rem;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    max-width: 500px;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class="maintenance-container">
                <div class="maintenance-card">
                    <div class="mb-4">
                        <img src="public/logo-icon.jpeg" alt="Salem Dominion Ministries" style="width: 100px; height: 100px; border-radius: 50%;">
                    </div>
                    <h2 class="text-danger mb-3">Database Maintenance</h2>
                    <p class="text-muted mb-4">
                        We're currently performing database maintenance. Please check back in a few minutes.
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This is a temporary issue and will be resolved shortly.
                    </div>
                    <div class="mt-4">
                        <button onclick="location.reload()" class="btn btn-primary">
                            <i class="fas fa-sync"></i> Try Again
                        </button>
                    </div>
                    <div class="mt-3 text-muted small">
                        Last updated: <?php echo date('h:i A'); ?>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Helper functions for global use
function safe_db_query($query, $fallback = []) {
    return DatabaseErrorHandler::safeQuery($query, $fallback);
}

function safe_db_prepare($query, $params = [], $fallback = []) {
    return DatabaseErrorHandler::safePreparedStatement($query, $params, $fallback);
}

function get_db_status() {
    return DatabaseErrorHandler::getDatabaseStatus();
}

function handle_db_error($error = null) {
    return DatabaseErrorHandler::handleConnectionFailure($error);
}

?>
