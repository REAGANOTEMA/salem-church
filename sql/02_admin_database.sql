-- ============================================================
-- SALEM DOMINION MINISTRIES
-- STEP 2: Admin Database (salemdominionmin_admin)
-- ============================================================
-- Run this in phpMyAdmin or MySQL CLI
-- Contains: Admin system, sessions, logs, media, backups
-- ============================================================

CREATE DATABASE IF NOT EXISTS `salemdominionmin_admin`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `salemdominionmin_admin`;

-- Verify
SELECT 'Admin database created successfully!' AS status;
SELECT DATABASE() AS current_database;

-- Disable foreign key checks for clean creation
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. admin_users
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `role` enum('super_admin','admin','editor','media_team','pastor','secretary','finance','volunteer') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. admin_sessions
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `logout_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_login_time` (`login_time`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_is_active` (`is_active`),
  FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. admin_login_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('success','failed','blocked') NOT NULL DEFAULT 'failed',
  `failure_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_username` (`username`),
  KEY `idx_login_time` (`login_time`),
  KEY `idx_status` (`status`),
  KEY `idx_ip_address` (`ip_address`),
  FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. admin_settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. activity_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_module` (`module`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. system_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` enum('info','warning','error','critical') NOT NULL DEFAULT 'info',
  `message` text NOT NULL,
  `context` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. backups
-- ============================================================
CREATE TABLE IF NOT EXISTS `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. media_library
-- ============================================================
CREATE TABLE IF NOT EXISTS `media_library` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_file_type` (`file_type`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Re-enable foreign key checks
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- ============================================================
-- SAMPLE DATA
-- ============================================================
-- ============================================================

-- ============================================================
-- Admin Users (2 admins, password = "password" for both)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================
INSERT IGNORE INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `is_active`)
VALUES
  (1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'admin@salem-dominion-ministries.com', '+256700000000', 'super_admin', 1),
  (2, 'MusasiziFaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Apostle Faty Musasizi', 'pastor@salem-dominion-ministries.com', '+256753244480', 'pastor', 1);

-- ============================================================
-- Admin Settings
-- ============================================================
INSERT IGNORE INTO `admin_settings` (`setting_key`, `setting_value`, `description`)
VALUES
  ('max_login_attempts', '5', 'Maximum number of failed login attempts before account lockout'),
  ('account_lockout_duration', '1800', 'Account lockout duration in seconds (30 minutes)'),
  ('session_timeout', '3600', 'Session timeout duration in seconds (1 hour)'),
  ('require_password_change', '0', 'Require password change on first login'),
  ('min_password_length', '8', 'Minimum password length requirement'),
  ('enable_two_factor', '0', 'Enable two-factor authentication'),
  ('maintenance_mode', '0', 'Maintenance mode toggle'),
  ('site_title', 'Salem Dominion Ministries Admin', 'Site title for admin panel'),
  ('admin_email', 'admin@salem-dominion-ministries.com', 'Admin contact email');

-- ============================================================
-- Sample system log entry
-- ============================================================
INSERT IGNORE INTO `system_logs` (`level`, `message`, `context`)
VALUES
  ('info', 'Admin database schema created successfully', 'database_setup'),
  ('info', 'Default admin accounts created', 'database_setup'),
  ('info', 'Sample data inserted successfully', 'database_setup');

-- ============================================================
-- Sample activity log
-- ============================================================
INSERT IGNORE INTO `activity_logs` (`user_id`, `action`, `module`, `details`, `ip_address`)
VALUES
  (1, 'Database Setup', 'system', 'Admin database schema created with all tables and sample data', '127.0.0.1');

-- ============================================================
-- ============================================================
--
-- ADMIN DATABASE SETUP COMPLETE!
--
-- ============================================================
-- ============================================================
--
-- DATABASE: salemdominionmin_admin
--
-- Total Tables: 9
--
-- DEFAULT ADMIN LOGIN CREDENTIALS:
-- ============================================================
--
-- Admin Account 1:
--   Username: admin
--   Password: password
--   Role:     super_admin
--   Email:    admin@salem-dominion-ministries.com
--
-- Admin Account 2:
--   Username: MusasiziFaty
--   Password: password
--   Role:     pastor
--   Email:    pastor@salem-dominion-ministries.com
--
-- ============================================================
-- TABLE SUMMARY:
-- ============================================================
--
--  1.  admin_users           (2 admins)
--  2.  admin_sessions        (session management)
--  3.  admin_login_logs      (security audit)
--  4.  admin_settings        (9 settings)
--  5.  notifications         (notification system)
--  6.  activity_logs         (activity tracking)
--  7.  system_logs           (system logging)
--  8.  backups               (backup management)
--  9.  media_library         (media management)
--
-- ============================================================
-- NOTE: All password hashes are for the word "password"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================
