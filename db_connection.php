<?php
/**
 * Salem Dominion Ministries - Database Connection (Legacy Compatibility)
 * Provides getConnection() function for existing code
 * New code should use Database::getInstance() or db()
 */

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/helpers.php';

function getConnection() {
    return Database::getInstance()->getPdo();
}

function createDatabaseConnection() {
    try {
        return Database::getInstance()->getPdo();
    } catch (Exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

function isLocalhost(): bool {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;
}
