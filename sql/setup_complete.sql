-- ============================================================
-- SALEM DOMINION MINISTRIES - COMPLETE DATABASE SETUP
-- Run this SQL in phpMyAdmin on each database
-- ============================================================

-- ============================================================
-- PART 1: ADMIN DATABASE (run on salemdominionmin_admin)
-- ============================================================

-- activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin_login_logs
CREATE TABLE IF NOT EXISTS `admin_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('success','failed','blocked') NOT NULL DEFAULT 'failed',
  `failure_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_username` (`username`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin_users
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin_sessions
CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `logout_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin_settings
CREATE TABLE IF NOT EXISTS `admin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- backups
CREATE TABLE IF NOT EXISTS `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('sermon','news','event','gallery') NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL DEFAULT 'Guest',
  `user_email` varchar(255) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` enum('approved','pending','spam','deleted') NOT NULL DEFAULT 'approved',
  `parent_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_content` (`content_type`,`content_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- likes
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('sermon','news','event','gallery') NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `visitor_hash` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_content` (`content_type`,`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- media_library
CREATE TABLE IF NOT EXISTS `media_library` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- shares
CREATE TABLE IF NOT EXISTS `shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` enum('sermon','news','event','gallery') NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `share_platform` varchar(50) DEFAULT 'link',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_content` (`content_type`,`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- system_logs
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` enum('info','warning','error','critical') NOT NULL DEFAULT 'info',
  `message` text NOT NULL,
  `context` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_level` (`level`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ADMIN USERS (passwords are verified below)
-- ============================================================

-- 'admin' user with password: password
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `role`, `is_active`, `login_attempts`, `locked_until`)
VALUES (
    'admin',
    '$2y$10$jZ2/BU6HBH1gg2tM7shAMe191b60ZUfUGpB1VpqBD8Q.BXYz183WK',
    'Super Admin',
    'admin@salem-dominion-ministries.com',
    'super_admin',
    1, 0, NULL
)
ON DUPLICATE KEY UPDATE
    `password` = '$2y$10$jZ2/BU6HBH1gg2tM7shAMe191b60ZUfUGpB1VpqBD8Q.BXYz183WK',
    `full_name` = 'Super Admin',
    `is_active` = 1,
    `login_attempts` = 0,
    `locked_until` = NULL;

-- 'SalemChurch' user with password: Lovely2God
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `role`, `is_active`, `login_attempts`, `locked_until`)
VALUES (
    'SalemChurch',
    '$2y$12$CwR2W2wZcqMBJRpfsBlItOVfBXbQ.UcDgpd94y0p4AwsTe5v2zWFK',
    'Pastor Faty Musasizi',
    'admin@salem-dominion-ministries.com',
    'super_admin',
    1, 0, NULL
)
ON DUPLICATE KEY UPDATE
    `password` = '$2y$12$CwR2W2wZcqMBJRpfsBlItOVfBXbQ.UcDgpd94y0p4AwsTe5v2zWFK',
    `full_name` = 'Pastor Faty Musasizi',
    `is_active` = 1,
    `login_attempts` = 0,
    `locked_until` = NULL;

-- 'MusasiziFaty' user with password: password
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `role`, `is_active`, `login_attempts`, `locked_until`)
VALUES (
    'MusasiziFaty',
    '$2y$10$jZ2/BU6HBH1gg2tM7shAMe191b60ZUfUGpB1VpqBD8Q.BXYz183WK',
    'Apostle Faty Musasizi',
    'pastor@salem-dominion-ministries.com',
    'pastor',
    1, 0, NULL
)
ON DUPLICATE KEY UPDATE
    `password` = '$2y$10$jZ2/BU6HBH1gg2tM7shAMe191b60ZUfUGpB1VpqBD8Q.BXYz183WK',
    `full_name` = 'Apostle Faty Musasizi',
    `is_active` = 1,
    `login_attempts` = 0,
    `locked_until` = NULL;

-- Admin settings
INSERT INTO `admin_settings` (`setting_key`, `setting_value`, `description`)
VALUES
    ('max_login_attempts', '5', 'Maximum number of failed login attempts before account lockout'),
    ('account_lockout_duration', '1800', 'Account lockout duration in seconds (30 minutes)'),
    ('session_timeout', '3600', 'Session timeout duration in seconds (1 hour)'),
    ('maintenance_mode', '0', 'Maintenance mode toggle')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ============================================================
-- VERIFY: Run this to confirm admin users exist
-- ============================================================
-- SELECT id, username, full_name, role, is_active FROM admin_users;
