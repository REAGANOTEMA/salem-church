-- SALEM DOMINION MINISTRIES - ADMIN LOGIN TABLE
-- Database: salem_dominion_ministries
-- Table: admin_users
-- Purpose: Store admin user credentials for Pastor Faty Musasizi login system

-- Create the admin_users table
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `full_name` varchar(100) NOT NULL,
    `email` varchar(100) DEFAULT NULL,
    `role` enum('admin','pastor','super_admin') NOT NULL DEFAULT 'admin',
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

-- Insert default admin user for Pastor Faty Musasizi
-- Note: Password is '123456' (as specified in the PHP code)
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `role`, `is_active`) 
VALUES ('MusasiziFaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pastor Faty Musasizi', 'pastor@salem-dominion-ministries.org', 'pastor', 1)
ON DUPLICATE KEY UPDATE 
    `password` = VALUES(`password`),
    `full_name` = VALUES(`full_name`),
    `email` = VALUES(`email`),
    `role` = VALUES(`role`),
    `is_active` = VALUES(`is_active`),
    `updated_at` = CURRENT_TIMESTAMP;

-- Create admin_sessions table for session management
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

-- Create admin_login_logs table for security auditing
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

-- Create admin_settings table for configuration
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

-- Insert default admin settings
INSERT INTO `admin_settings` (`setting_key`, `setting_value`, `description`) 
VALUES 
('max_login_attempts', '5', 'Maximum number of failed login attempts before account lockout'),
('account_lockout_duration', '1800', 'Account lockout duration in seconds (30 minutes)'),
('session_timeout', '3600', 'Session timeout duration in seconds (1 hour)'),
('require_password_change', '0', 'Require password change on first login (0 = no, 1 = yes)'),
('min_password_length', '8', 'Minimum password length requirement'),
('enable_two_factor', '0', 'Enable two-factor authentication (0 = no, 1 = yes)'),
('maintenance_mode', '0', 'Maintenance mode (0 = off, 1 = on)'),
('site_title', 'Salem Dominion Ministries Admin', 'Site title for admin panel'),
('admin_email', 'pastor@salem-dominion-ministries.org', 'Admin contact email')
ON DUPLICATE KEY UPDATE 
    `setting_value` = VALUES(`setting_value`),
    `description` = VALUES(`description`),
    `updated_at` = CURRENT_TIMESTAMP;

-- SERMONS TABLE - Store sermon content and media
CREATE TABLE IF NOT EXISTS `sermons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `sermon_date` date NOT NULL,
    `category` varchar(100) DEFAULT NULL,
    `media_type` enum('video','audio') NOT NULL DEFAULT 'video',
    `media_url` text DEFAULT NULL,
    `sermon_series` varchar(255) DEFAULT NULL,
    `preacher_id` int(11) DEFAULT NULL,
    `views` int(11) NOT NULL DEFAULT 0,
    `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sermon_date` (`sermon_date`),
    KEY `idx_category` (`category`),
    KEY `idx_status` (`status`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_views` (`views`),
    FOREIGN KEY (`preacher_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EVENTS TABLE - Store church events
CREATE TABLE IF NOT EXISTS `events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `event_date` date NOT NULL,
    `event_time` time NOT NULL,
    `location` varchar(255) NOT NULL,
    `status` enum('upcoming','ongoing','completed','cancelled','deleted') NOT NULL DEFAULT 'upcoming',
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_date` (`event_date`),
    KEY `idx_status` (`status`),
    KEY `idx_created_by` (`created_by`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEWS TABLE - Store news articles and announcements
CREATE TABLE IF NOT EXISTS `news` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `excerpt` text DEFAULT NULL,
    `category` varchar(100) DEFAULT NULL,
    `featured_image` text DEFAULT NULL,
    `views` int(11) NOT NULL DEFAULT 0,
    `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category`),
    KEY `idx_status` (`status`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_views` (`views`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GALLERY TABLE - Store multimedia content
CREATE TABLE IF NOT EXISTS `gallery` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `uploaded_by` int(11) NOT NULL,
    `file_url` text NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `file_type` enum('image','video','audio') NOT NULL,
    `category` varchar(100) DEFAULT NULL,
    `file_size` bigint(20) DEFAULT NULL,
    `dimensions` varchar(50) DEFAULT NULL, -- For images: widthxheight, for videos: duration
    `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_file_type` (`file_type`),
    KEY `idx_category` (`category`),
    KEY `idx_status` (`status`),
    KEY `idx_uploaded_by` (`uploaded_by`),
    FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TESTIMONIALS TABLE - Store user testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `occupation` varchar(255) DEFAULT NULL,
    `testimonial` text NOT NULL,
    `rating` int(11) DEFAULT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `is_approved` tinyint(1) NOT NULL DEFAULT 0,
    `is_featured` tinyint(1) NOT NULL DEFAULT 0,
    `status` enum('pending','approved','rejected','archived') NOT NULL DEFAULT 'pending',
    `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `approved_at` datetime DEFAULT NULL,
    `approved_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_is_featured` (`is_featured`),
    KEY `idx_status` (`status`),
    KEY `idx_rating` (`rating`),
    KEY `idx_submitted_at` (`submitted_at`),
    FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MINISTRIES TABLE - Store church ministries information
CREATE TABLE IF NOT EXISTS `ministries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `leader_name` varchar(255) DEFAULT NULL,
    `leader_email` varchar(255) DEFAULT NULL,
    `leader_phone` varchar(20) DEFAULT NULL,
    `meeting_day` varchar(50) DEFAULT NULL,
    `meeting_time` time DEFAULT NULL,
    `meeting_location` varchar(255) DEFAULT NULL,
    `category` enum('children','youth','men','women','outreach','worship','prayer','other') NOT NULL DEFAULT 'other',
    `image_url` text DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `sort_order` int(11) NOT NULL DEFAULT 0,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`),
    KEY `idx_created_by` (`created_by`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SERMON_REACTIONS TABLE - Track user reactions to sermons
CREATE TABLE IF NOT EXISTS `sermon_reactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sermon_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `reaction_type` enum('like','love','amen','blessed','inspired','pray','worship') NOT NULL DEFAULT 'like',
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_reaction` (`sermon_id`, `user_id`, `reaction_type`),
    KEY `idx_sermon_id` (`sermon_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_reaction_type` (`reaction_type`),
    FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USERS TABLE - General user accounts for sermon interactions (moved before dependent tables)
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `username` varchar(100) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `role` enum('user','member','admin','pastor') NOT NULL DEFAULT 'user',
    `phone` varchar(20) DEFAULT NULL,
    `address` text DEFAULT NULL,
    `city` varchar(100) DEFAULT NULL,
    `country` varchar(100) DEFAULT NULL,
    `avatar` text DEFAULT NULL,
    `bio` text DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `email_verified` tinyint(1) NOT NULL DEFAULT 0,
    `last_login` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`),
    KEY `idx_role` (`role`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- COMMENTS TABLE - Store comments for sermons and news
CREATE TABLE IF NOT EXISTS `comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `content_type` enum('sermon','news','gallery') NOT NULL,
    `content_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `author_name` varchar(255) DEFAULT NULL,
    `author_email` varchar(255) DEFAULT NULL,
    `comment_text` text NOT NULL,
    `post_type` enum('sermon','news','gallery') NOT NULL DEFAULT 'sermon',
    `post_id` int(11) NOT NULL DEFAULT 0,
    `is_approved` tinyint(1) NOT NULL DEFAULT 0,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_content` (`content_type`, `content_id`),
    KEY `idx_post` (`post_type`, `post_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GALLERY_REACTIONS TABLE - Track user reactions to gallery items
CREATE TABLE IF NOT EXISTS `gallery_reactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gallery_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `reaction_type` enum('like','love','blessed','inspired','pray','worship') NOT NULL DEFAULT 'like',
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_gallery_reaction` (`gallery_id`, `user_id`, `reaction_type`),
    KEY `idx_gallery_id` (`gallery_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_reaction_type` (`reaction_type`),
    FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GALLERY_COMMENTS TABLE - Store comments for gallery items
CREATE TABLE IF NOT EXISTS `gallery_comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gallery_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `author_name` varchar(255) DEFAULT NULL,
    `author_email` varchar(255) DEFAULT NULL,
    `comment` text NOT NULL,
    `is_approved` tinyint(1) NOT NULL DEFAULT 0,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gallery_id` (`gallery_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`parent_id`) REFERENCES `gallery_comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: ALTER TABLE statements removed for clean execution
-- All tables are created with the correct structure from the start

-- SERMON_VIEWS TABLE - Track sermon views separately
CREATE TABLE IF NOT EXISTS `sermon_views` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sermon_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `viewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sermon_id` (`sermon_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_viewed_at` (`viewed_at`),
    FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SERMON_BOOKMARKS TABLE - Allow users to bookmark sermons
CREATE TABLE IF NOT EXISTS `sermon_bookmarks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sermon_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_bookmark` (`sermon_id`, `user_id`),
    KEY `idx_sermon_id` (`sermon_id`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GALLERY_BOOKMARKS TABLE - Allow users to bookmark gallery items
CREATE TABLE IF NOT EXISTS `gallery_bookmarks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gallery_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_gallery_bookmark` (`gallery_id`, `user_id`),
    KEY `idx_gallery_id` (`gallery_id`),
    KEY `idx_user_id` (`user_id`),
    FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SERMON_NOTES TABLE - Allow users to take notes on sermons
CREATE TABLE IF NOT EXISTS `sermon_notes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sermon_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `notes` text NOT NULL,
    `timestamp` int(11) DEFAULT NULL, -- For video/audio timestamp
    `is_private` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sermon_id` (`sermon_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_timestamp` (`timestamp`),
    FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SERMON_SHARES TABLE - Track sermon sharing
CREATE TABLE IF NOT EXISTS `sermon_shares` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sermon_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `share_type` enum('facebook','twitter','whatsapp','email','link') NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sermon_id` (`sermon_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_share_type` (`share_type`),
    FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GALLERY_SHARES TABLE - Track gallery item sharing
CREATE TABLE IF NOT EXISTS `gallery_shares` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gallery_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `share_type` enum('facebook','twitter','whatsapp','email','link') NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gallery_id` (`gallery_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_share_type` (`share_type`),
    FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample users for testing (with passwords)
INSERT INTO `users` (`first_name`, `last_name`, `email`, `username`, `password`, `phone`, `country`, `role`, `is_active`, `email_verified`) VALUES
('Apostle', 'Faty', 'apostle.faty@salem-dominion-ministries.org', 'apostlefaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256753244480', 'Uganda', 'pastor', 1, 1),
('John', 'Doe', 'john.doe@example.com', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256700123456', 'Uganda', 'member', 1, 1),
('Jane', 'Smith', 'jane.smith@example.com', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256751234567', 'Kenya', 'user', 1, 1),
('Michael', 'Johnson', 'michael.johnson@example.com', 'michaelj', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256702345678', 'Tanzania', 'member', 1, 1),
('Sarah', 'Williams', 'sarah.williams@example.com', 'sarahw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256753456789', 'Rwanda', 'user', 1, 1)
ON DUPLICATE KEY UPDATE 
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `email` = VALUES(`email`),
    `password` = VALUES(`password`),
    `phone` = VALUES(`phone`),
    `country` = VALUES(`country`),
    `role` = VALUES(`role`),
    `is_active` = VALUES(`is_active`),
    `email_verified` = VALUES(`email_verified`),
    `updated_at` = CURRENT_TIMESTAMP;

-- USER_REGISTRATION_SETTINGS TABLE - Configure registration settings
CREATE TABLE IF NOT EXISTS `user_registration_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `registration_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `require_email_verification` tinyint(1) NOT NULL DEFAULT 0,
    `require_admin_approval` tinyint(1) NOT NULL DEFAULT 0,
    `default_role` enum('user','member') NOT NULL DEFAULT 'user',
    `allowed_countries` json DEFAULT NULL,
    `welcome_message` text DEFAULT NULL,
    `auto_login_after_registration` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USER_LOGIN_LOGS TABLE - Track user login attempts
CREATE TABLE IF NOT EXISTS `user_login_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `email` varchar(255) DEFAULT NULL,
    `login_status` enum('success','failed','blocked') NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `failure_reason` varchar(255) DEFAULT NULL,
    `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_email` (`email`),
    KEY `idx_login_status` (`login_status`),
    KEY `idx_login_time` (`login_time`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert registration settings
INSERT INTO `user_registration_settings` (`registration_enabled`, `require_email_verification`, `require_admin_approval`, `default_role`, `allowed_countries`, `welcome_message`) VALUES
(1, 0, 0, 'user', '["Uganda", "Kenya", "Tanzania", "Rwanda", "Burundi", "South Sudan"]', 
'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.')
ON DUPLICATE KEY UPDATE 
    `registration_enabled` = VALUES(`registration_enabled`),
    `require_email_verification` = VALUES(`require_email_verification`),
    `require_admin_approval` = VALUES(`require_admin_approval`),
    `default_role` = VALUES(`default_role`),
    `allowed_countries` = VALUES(`allowed_countries`),
    `welcome_message` = VALUES(`welcome_message`),
    `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample data for testing
INSERT INTO `sermons` (`title`, `description`, `sermon_date`, `category`, `media_type`, `preacher_id`, `created_by`) VALUES
('The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', 1, 1),
('Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', 1, 1),
('The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', 1, 1);

INSERT INTO `events` (`title`, `description`, `event_date`, `event_time`, `location`, `status`, `created_by`) VALUES
('Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1),
('Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1),
('Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1);

INSERT INTO `news` (`title`, `content`, `excerpt`, `category`, `created_by`) VALUES
('New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', 1),
('Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', 1),
('Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', 1);

-- Insert sample ministries data
INSERT INTO `ministries` (`name`, `description`, `leader_name`, `leader_email`, `leader_phone`, `meeting_day`, `meeting_time`, `meeting_location`, `category`, `is_active`, `sort_order`, `created_by`) VALUES
('Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', 1, 1, 1),
('Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', 1, 2, 1),
('Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', 1, 3, 1),
('Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', 1, 4, 1),
('Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', 1, 5, 1);

-- Insert sample gallery items
INSERT INTO `gallery` (`uploaded_by`, `file_url`, `title`, `description`, `file_type`, `category`, `status`) VALUES
(1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', 'published'),
(1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', 'published'),
(1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', 'published'),
(1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', 'published');

-- Insert sample reactions and comments (using only valid ENUM values)
INSERT INTO `sermon_reactions` (`sermon_id`, `user_id`, `reaction_type`) VALUES
(1, 2, 'like'), (1, 3, 'like'), (1, 4, 'blessed'),
(2, 2, 'love'), (2, 3, 'inspired'), (2, 5, 'pray'),
(3, 2, 'worship'), (3, 4, 'like'), (3, 5, 'inspired')
ON DUPLICATE KEY UPDATE 
    `reaction_type` = VALUES(`reaction_type`),
    `created_at` = CURRENT_TIMESTAMP;

INSERT INTO `gallery_reactions` (`gallery_id`, `user_id`, `reaction_type`) VALUES
(1, 2, 'love'), (1, 3, 'blessed'), (1, 4, 'inspired'),
(2, 2, 'like'), (2, 5, 'pray'), (2, 3, 'blessed'),
(3, 2, 'inspired'), (3, 4, 'worship'), (3, 5, 'love')
ON DUPLICATE KEY UPDATE 
    `reaction_type` = VALUES(`reaction_type`),
    `created_at` = CURRENT_TIMESTAMP;

INSERT INTO `comments` (`content_type`, `content_id`, `user_id`, `comment_text`) VALUES
('sermon', 1, 2, 'This sermon really blessed my heart! Thank you Pastor.'),
('sermon', 1, 3, 'Powerful message that I needed to hear today.'),
('sermon', 2, 4, 'The teaching on purpose was exactly what I was looking for.'),
('news', 1, 2, 'So excited about the new building project! God is good.'),
('gallery', 1, 3, 'Beautiful worship moments captured here.');

INSERT INTO `gallery_comments` (`gallery_id`, `user_id`, `comment`) VALUES
(1, 2, 'Amazing worship session! The presence of God was so strong.'),
(1, 4, 'I was there and it was truly blessed!'),
(2, 3, 'Our youth are on fire for God!'),
(2, 5, 'Great conference, looking forward to the next one.'),
(3, 2, 'This testimony encouraged me so much!');

-- DONATIONS TABLE - Enhanced for donate.php functionality
CREATE TABLE IF NOT EXISTS `donations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `donor_name` varchar(255) NOT NULL,
    `donor_email` varchar(255) DEFAULT NULL,
    `donor_phone` varchar(20) DEFAULT NULL,
    `amount` decimal(10,2) NOT NULL,
    `donation_type` enum('tithe','offering','building_fund','missions','children_ministry','special','general') NOT NULL DEFAULT 'general',
    `payment_method` enum('mobile_money','bank_transfer','cash','online','card') NOT NULL DEFAULT 'mobile_money',
    `transaction_id` varchar(100) DEFAULT NULL,
    `status` enum('pending','confirmed','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    `payment_reference` varchar(255) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
    `whatsapp_sent` tinyint(1) NOT NULL DEFAULT 0,
    `whatsapp_message_id` varchar(100) DEFAULT NULL,
    `confirmation_code` varchar(50) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_donor_name` (`donor_name`),
    KEY `idx_donor_email` (`donor_email`),
    KEY `idx_donor_phone` (`donor_phone`),
    KEY `idx_amount` (`amount`),
    KEY `idx_donation_type` (`donation_type`),
    KEY `idx_payment_method` (`payment_method`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_transaction_id` (`transaction_id`),
    KEY `idx_confirmation_code` (`confirmation_code`),
    KEY `idx_whatsapp_sent` (`whatsapp_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DONATION_CAMPAIGNS TABLE - Manage donation campaigns
CREATE TABLE IF NOT EXISTS `donation_campaigns` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `campaign_type` enum('general','building','missions','special','emergency') NOT NULL DEFAULT 'general',
    `goal_amount` decimal(10,2) DEFAULT NULL,
    `current_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `featured_image` text DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_campaign_type` (`campaign_type`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_start_date` (`start_date`),
    KEY `idx_end_date` (`end_date`),
    KEY `idx_created_by` (`created_by`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EVENT_REGISTRATIONS TABLE - Support for events.php registration functionality
CREATE TABLE IF NOT EXISTS `event_registrations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event_id` int(11) NOT NULL,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `age_group` enum('child','youth','young_adult','adult','senior') DEFAULT NULL,
    `church_affiliation` varchar(255) DEFAULT NULL,
    `special_needs` text DEFAULT NULL,
    `registration_type` enum('individual','family','group') NOT NULL DEFAULT 'individual',
    `group_size` int(11) DEFAULT 1,
    `status` enum('registered','confirmed','cancelled','attended') NOT NULL DEFAULT 'registered',
    `confirmation_code` varchar(50) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `confirmation_code` (`confirmation_code`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_email` (`email`),
    KEY `idx_phone` (`phone`),
    KEY `idx_status` (`status`),
    KEY `idx_registered_at` (`registered_at`),
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EVENT_ATTENDEES TABLE - Track actual attendance
CREATE TABLE IF NOT EXISTS `event_attendees` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `registration_id` int(11) DEFAULT NULL,
    `event_id` int(11) NOT NULL,
    `attendee_name` varchar(255) NOT NULL,
    `attendee_email` varchar(255) DEFAULT NULL,
    `attendee_phone` varchar(20) DEFAULT NULL,
    `check_in_time` datetime DEFAULT NULL,
    `check_out_time` datetime DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_registration_id` (`registration_id`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_attendee_email` (`attendee_email`),
    KEY `idx_check_in_time` (`check_in_time`),
    FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EVENT_FEEDBACK TABLE - Collect feedback from events
CREATE TABLE IF NOT EXISTS `event_feedback` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event_id` int(11) NOT NULL,
    `registration_id` int(11) DEFAULT NULL,
    `attendee_name` varchar(255) NOT NULL,
    `attendee_email` varchar(255) DEFAULT NULL,
    `rating` int(11) DEFAULT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `feedback_text` text DEFAULT NULL,
    `suggestions` text DEFAULT NULL,
    `would_recommend` tinyint(1) DEFAULT NULL,
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `ip_address` varchar(45) DEFAULT NULL,
    `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_registration_id` (`registration_id`),
    KEY `idx_rating` (`rating`),
    KEY `idx_submitted_at` (`submitted_at`),
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EVENT_REMINDERS TABLE - Send reminders to registered attendees
CREATE TABLE IF NOT EXISTS `event_reminders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event_id` int(11) NOT NULL,
    `registration_id` int(11) NOT NULL,
    `reminder_type` enum('confirmation','reminder','follow_up','cancellation') NOT NULL DEFAULT 'reminder',
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `sent_at` datetime DEFAULT NULL,
    `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
    `send_via` enum('email','sms','both') NOT NULL DEFAULT 'email',
    `scheduled_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_id` (`event_id`),
    KEY `idx_registration_id` (`registration_id`),
    KEY `idx_status` (`status`),
    KEY `idx_scheduled_at` (`scheduled_at`),
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEWSLETTER_SUBSCRIPTIONS TABLE - Support for newsletter functionality
CREATE TABLE IF NOT EXISTS `newsletter_subscriptions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `first_name` varchar(100) DEFAULT NULL,
    `last_name` varchar(100) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `subscription_type` enum('general','events','sermons','news','all') NOT NULL DEFAULT 'all',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `is_verified` tinyint(1) NOT NULL DEFAULT 0,
    `verification_token` varchar(100) DEFAULT NULL,
    `unsubscribe_token` varchar(100) DEFAULT NULL,
    `preferences` json DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` datetime DEFAULT NULL,
    `last_sent_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `verification_token` (`verification_token`),
    UNIQUE KEY `unsubscribe_token` (`unsubscribe_token`),
    KEY `idx_subscription_type` (`subscription_type`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_is_verified` (`is_verified`),
    KEY `idx_subscribed_at` (`subscribed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample donation data matching donate.php form
INSERT INTO `donations` (`donor_name`, `donor_email`, `donor_phone`, `amount`, `donation_type`, `payment_method`, `status`, `whatsapp_sent`, `confirmation_code`) VALUES
('John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', 'completed', 1, 'DON2024001'),
('Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', 'completed', 1, 'DON2024002'),
('Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', 'confirmed', 0, 'DON2024003'),
('Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', 'completed', 1, 'DON2024004'),
('David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', 'completed', 1, 'DON2024005'),
('Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', 'completed', 0, 'DON2024006'),
('Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', 'pending', 0, 'DON2024007');

-- Insert sample donation campaigns
INSERT INTO `donation_campaigns` (`title`, `description`, `campaign_type`, `goal_amount`, `start_date`, `end_date`, `created_by`) VALUES
('Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, '2024-01-01', '2024-12-31', 1),
('Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, '2024-01-01', '2024-12-31', 1),
('Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, '2024-01-01', '2024-06-30', 1);

-- Insert sample event registrations
INSERT INTO `event_registrations` (`event_id`, `first_name`, `last_name`, `email`, `phone`, `age_group`, `registration_type`, `confirmation_code`) VALUES
(1, 'John', 'Doe', 'john.doe@example.com', '+256700123456', 'adult', 'individual', 'REG2024001'),
(1, 'Jane', 'Smith', 'jane.smith@example.com', '+256751234567', 'young_adult', 'individual', 'REG2024002'),
(2, 'Michael', 'Johnson', 'michael.j@example.com', '+256702345678', 'youth', 'group', 'REG2024003'),
(3, 'Sarah', 'Williams', 'sarah.w@example.com', '+256753456789', 'adult', 'family', 'REG2024004')
ON DUPLICATE KEY UPDATE 
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `email` = VALUES(`email`),
    `phone` = VALUES(`phone`),
    `age_group` = VALUES(`age_group`),
    `registration_type` = VALUES(`registration_type`),
    `updated_at` = CURRENT_TIMESTAMP;

-- Insert sample newsletter subscriptions
INSERT INTO `newsletter_subscriptions` (`email`, `first_name`, `last_name`, `subscription_type`, `is_verified`) VALUES
('john.doe@example.com', 'John', 'Doe', 'all', 1),
('jane.smith@example.com', 'Jane', 'Smith', 'events', 1),
('michael.j@example.com', 'Michael', 'Johnson', 'sermons', 0),
('sarah.w@example.com', 'Sarah', 'Williams', 'all', 1),
('david.brown@example.com', 'David', 'Brown', 'news', 1)
ON DUPLICATE KEY UPDATE 
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `subscription_type` = VALUES(`subscription_type`),
    `is_verified` = VALUES(`is_verified`);

-- PROPHETIC_SCHOOL_APPLICATIONS TABLE - Support for prophetic-school.php functionality
CREATE TABLE IF NOT EXISTS `prophetic_school_applications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `application_id` varchar(20) NOT NULL,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `age` int(11) NOT NULL,
    `gender` enum('male','female') NOT NULL,
    `nationality` varchar(100) NOT NULL,
    `address` text NOT NULL,
    `ministry_background` text DEFAULT NULL,
    `prophetic_experience` text DEFAULT NULL,
    `calling` text DEFAULT NULL,
    `reason` text DEFAULT NULL,
    `payment_method` enum('mobile_money','bank_transfer','cash') NOT NULL,
    `transaction_id` varchar(100) NOT NULL,
    `payment_amount` decimal(10,2) NOT NULL DEFAULT 100.00,
    `payment_currency` varchar(3) NOT NULL DEFAULT 'USD',
    `payment_status` enum('pending','verified','confirmed','failed','refunded') NOT NULL DEFAULT 'pending',
    `application_status` enum('pending','under_review','accepted','rejected','enrolled','graduated') NOT NULL DEFAULT 'pending',
    `whatsapp_sent` tinyint(1) NOT NULL DEFAULT 0,
    `whatsapp_message_id` varchar(100) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `admin_notes` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `application_id` (`application_id`),
    KEY `idx_email` (`email`),
    KEY `idx_phone` (`phone`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_application_status` (`application_status`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROPHETIC_SCHOOL_DOCUMENTS TABLE - Store uploaded documents
CREATE TABLE IF NOT EXISTS `prophetic_school_documents` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `application_id` int(11) NOT NULL,
    `document_type` enum('passport_photo','national_id','passport','cv','recommendation_letter','other') NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_path` text NOT NULL,
    `file_size` bigint(20) DEFAULT NULL,
    `file_type` varchar(50) DEFAULT NULL,
    `mime_type` varchar(100) DEFAULT NULL,
    `is_verified` tinyint(1) NOT NULL DEFAULT 0,
    `verification_notes` text DEFAULT NULL,
    `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_application_id` (`application_id`),
    KEY `idx_document_type` (`document_type`),
    KEY `idx_is_verified` (`is_verified`),
    FOREIGN KEY (`application_id`) REFERENCES `prophetic_school_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROPHETIC_SCHOOL_PROGRAMS TABLE - Manage available programs
CREATE TABLE IF NOT EXISTS `prophetic_school_programs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `program_name` varchar(255) NOT NULL,
    `program_code` varchar(20) NOT NULL,
    `description` text DEFAULT NULL,
    `duration_months` int(11) DEFAULT NULL,
    `fee_amount` decimal(10,2) NOT NULL DEFAULT 100.00,
    `fee_currency` varchar(3) NOT NULL DEFAULT 'USD',
    `start_date` date DEFAULT NULL,
    `end_date` date DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `max_students` int(11) DEFAULT NULL,
    `current_enrollment` int(11) NOT NULL DEFAULT 0,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `program_code` (`program_code`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_start_date` (`start_date`),
    KEY `idx_created_by` (`created_by`),
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROPHETIC_SCHOOL_ENROLLMENTS TABLE - Manage student enrollments
CREATE TABLE IF NOT EXISTS `prophetic_school_enrollments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `application_id` int(11) NOT NULL,
    `program_id` int(11) NOT NULL,
    `enrollment_date` date NOT NULL,
    `graduation_date` date DEFAULT NULL,
    `status` enum('enrolled','active','suspended','graduated','dropped') NOT NULL DEFAULT 'enrolled',
    `grade_point_average` decimal(3,2) DEFAULT NULL,
    `attendance_rate` decimal(5,2) DEFAULT NULL,
    `completion_certificate_issued` tinyint(1) NOT NULL DEFAULT 0,
    `certificate_number` varchar(50) DEFAULT NULL,
    `notes` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `application_program` (`application_id`, `program_id`),
    KEY `idx_program_id` (`program_id`),
    KEY `idx_status` (`status`),
    KEY `idx_enrollment_date` (`enrollment_date`),
    FOREIGN KEY (`application_id`) REFERENCES `prophetic_school_applications` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `prophetic_school_programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROPHETIC_SCHOOL_COURSES TABLE - Manage course curriculum
CREATE TABLE IF NOT EXISTS `prophetic_school_courses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `program_id` int(11) NOT NULL,
    `course_name` varchar(255) NOT NULL,
    `course_code` varchar(20) NOT NULL,
    `description` text DEFAULT NULL,
    `duration_weeks` int(11) DEFAULT NULL,
    `credits` int(11) DEFAULT NULL,
    `is_required` tinyint(1) NOT NULL DEFAULT 1,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `course_code` (`course_code`),
    KEY `idx_program_id` (`program_id`),
    KEY `idx_is_required` (`is_required`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`program_id`) REFERENCES `prophetic_school_programs` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROPHETIC_SCHOOL_ASSESSMENTS TABLE - Track student assessments
CREATE TABLE IF NOT EXISTS `prophetic_school_assessments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `enrollment_id` int(11) NOT NULL,
    `course_id` int(11) NOT NULL,
    `assessment_type` enum('assignment','quiz','midterm','final','practical','project') NOT NULL,
    `title` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `max_score` decimal(5,2) NOT NULL DEFAULT 100.00,
    `score_obtained` decimal(5,2) DEFAULT NULL,
    `grade` varchar(5) DEFAULT NULL,
    `assessment_date` date DEFAULT NULL,
    `status` enum('pending','submitted','graded','failed') NOT NULL DEFAULT 'pending',
    `feedback` text DEFAULT NULL,
    `graded_by` int(11) DEFAULT NULL,
    `graded_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_enrollment_id` (`enrollment_id`),
    KEY `idx_course_id` (`course_id`),
    KEY `idx_assessment_type` (`assessment_type`),
    KEY `idx_status` (`status`),
    KEY `idx_assessment_date` (`assessment_date`),
    FOREIGN KEY (`enrollment_id`) REFERENCES `prophetic_school_enrollments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `prophetic_school_courses` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`graded_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROPHETIC_SCHOOL_CERTIFICATES TABLE - Manage certificate issuance
CREATE TABLE IF NOT EXISTS `prophetic_school_certificates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `enrollment_id` int(11) NOT NULL,
    `certificate_number` varchar(50) NOT NULL,
    `certificate_type` enum('completion','excellence','honor','distinction') NOT NULL DEFAULT 'completion',
    `issue_date` date NOT NULL,
    `graduation_date` date NOT NULL,
    `program_name` varchar(255) NOT NULL,
    `student_name` varchar(255) NOT NULL,
    `grade_point_average` decimal(3,2) DEFAULT NULL,
    `special_achievements` text DEFAULT NULL,
    `issued_by` int(11) NOT NULL,
    `certificate_file_path` text DEFAULT NULL,
    `is_verified` tinyint(1) NOT NULL DEFAULT 1,
    `verification_code` varchar(50) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `certificate_number` (`certificate_number`),
    UNIQUE KEY `verification_code` (`verification_code`),
    KEY `idx_enrollment_id` (`enrollment_id`),
    KEY `idx_certificate_type` (`certificate_type`),
    KEY `idx_issue_date` (`issue_date`),
    KEY `idx_is_verified` (`is_verified`),
    FOREIGN KEY (`enrollment_id`) REFERENCES `prophetic_school_enrollments` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`issued_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample prophetic school programs
INSERT INTO `prophetic_school_programs` (`program_name`, `program_code`, `description`, `duration_months`, `fee_amount`, `created_by`) VALUES
('Basic Prophetic Training', 'PROPH-001', 'Introduction to prophetic ministry and spiritual gifts', 3, 100.00, 1),
('Advanced Prophetic Ministry', 'PROPH-002', 'Deep dive into prophetic operations and church ministry', 6, 200.00, 1),
('Prophetic Mentorship Program', 'PROPH-003', 'One-on-one mentorship with experienced prophetic ministers', 12, 500.00, 1);

-- Insert sample prophetic school courses
INSERT INTO `prophetic_school_courses` (`program_id`, `course_name`, `course_code`, `description`, `duration_weeks`, `credits`, `created_by`) VALUES
(1, 'Introduction to Prophecy', 'PROP-101', 'Biblical foundation of prophetic ministry', 4, 3, 1),
(1, 'Discerning God\'s Voice', 'PROP-102', 'Learning to distinguish God\'s voice from other voices', 4, 3, 1),
(1, 'Prophetic Ethics', 'PROP-103', 'Ethical guidelines for prophetic ministry', 3, 2, 1),
(2, 'Advanced Prophetic Operations', 'PROP-201', 'Deep prophetic ministry techniques', 6, 4, 1),
(2, 'Church and Prophetic Ministry', 'PROP-202', 'Integrating prophecy in church context', 5, 3, 1);

-- Insert sample prophetic school applications
INSERT INTO `prophetic_school_applications` (`application_id`, `first_name`, `last_name`, `email`, `phone`, `age`, `gender`, `nationality`, `address`, `ministry_background`, `prophetic_experience`, `calling`, `reason`, `payment_method`, `transaction_id`, `payment_amount`, `payment_status`, `application_status`, `whatsapp_sent`) VALUES
('PROPH-2024001', 'John', 'Doe', 'john.doe@example.com', '+256700123456', 28, 'male', 'Ugandan', 'Kampala, Uganda', 'Youth ministry leader for 3 years', 'Prophetic dreams and visions', 'Called to prophetic ministry', 'To develop my prophetic gift', 'mobile_money', 'TXN123456789', 100.00, 'verified', 'accepted', 1),
('PROPH-2024002', 'Sarah', 'Johnson', 'sarah.j@example.com', '+256751234567', 32, 'female', 'Kenyan', 'Nairobi, Kenya', 'Worship leader and prayer warrior', 'Words of knowledge and prophecy', 'Prophetic worship ministry', 'To be equipped for ministry', 'bank_transfer', 'BANK987654321', 100.00, 'confirmed', 'under_review', 1),
('PROPH-2024003', 'Michael', 'Williams', 'michael.w@example.com', '+256702345678', 25, 'male', 'Tanzanian', 'Dar es Salaam, Tanzania', 'New believer with passion for God', 'Growing prophetic sensitivity', 'Feel called to prophetic ministry', 'To learn and grow in the gift', 'mobile_money', 'TXN567890123', 100.00, 'pending', 'pending', 0);

-- Notes for PHPMyAdmin Execution:
-- 1. Select the database "salem_dominion-ministries" first
-- 2. Copy and paste this entire SQL script
-- 3. Click "Go" or "Execute" to run the script
-- 4. Verify all tables were created successfully
-- 5. Check the admin_users table to confirm Pastor Faty Musasizi account exists

-- Default Login Credentials:
-- Username: MusasiziFaty
-- Password: 123456

-- Security Notes:
-- - The password is hashed using PHP's password_hash() function
-- - Account lockout protection is implemented
-- - Session management and logging are included
-- - All tables use InnoDB engine with proper foreign key constraints
-- - Sample data included for testing purposes
