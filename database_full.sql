-- ============================================================
-- SALEM DOMINION MINISTRIES - COMPLETE DATABASE SCHEMA
-- Version: 2.0
-- Date: 2026-07-22
-- Database: salem_dominion_ministries
-- Charset: utf8mb4 COLLATE utf8mb4_unicode_ci
-- ============================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS `salem_dominion_ministries`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `salem_dominion_ministries`;

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
-- 5. users
-- ============================================================
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
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. news_categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `news_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. news
-- ============================================================
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `featured_image` text DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  KEY `idx_author_id` (`author_id`),
  KEY `idx_views` (`views`),
  FOREIGN KEY (`author_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. events
-- ============================================================
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `speaker` varchar(255) DEFAULT NULL,
  `banner_image` text DEFAULT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `registration_url` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_status` (`status`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. event_registrations
-- ============================================================
CREATE TABLE IF NOT EXISTS `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_id` (`event_id`),
  KEY `idx_email` (`email`),
  FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. sermons
-- ============================================================
CREATE TABLE IF NOT EXISTS `sermons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `preacher` varchar(255) DEFAULT NULL,
  `sermon_date` date NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `series` varchar(255) DEFAULT NULL,
  `media_type` enum('video','audio') NOT NULL DEFAULT 'video',
  `media_url` text DEFAULT NULL,
  `audio_url` text DEFAULT NULL,
  `pdf_url` text DEFAULT NULL,
  `scripture` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `thumbnail` text DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sermon_date` (`sermon_date`),
  KEY `idx_category` (`category`),
  KEY `idx_series` (`series`),
  KEY `idx_media_type` (`media_type`),
  KEY `idx_status` (`status`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_views` (`views`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. albums
-- ============================================================
CREATE TABLE IF NOT EXISTS `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. gallery
-- ============================================================
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_url` text NOT NULL,
  `file_type` enum('image','video','audio') NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_file_type` (`file_type`),
  KEY `idx_album_id` (`album_id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. youtube_live
-- ============================================================
CREATE TABLE IF NOT EXISTS `youtube_live` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `youtube_url` text NOT NULL,
  `embed_url` text DEFAULT NULL,
  `is_live` tinyint(1) NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_live` (`is_live`),
  KEY `idx_is_enabled` (`is_enabled`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. donations
-- ============================================================
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
  `confirmation_code` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
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
  KEY `idx_confirmation_code` (`confirmation_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. donation_campaigns
-- ============================================================
CREATE TABLE IF NOT EXISTS `donation_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `goal` decimal(10,2) NOT NULL,
  `raised` decimal(10,2) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. prayer_requests
-- ============================================================
CREATE TABLE IF NOT EXISTS `prayer_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `request_text` text NOT NULL,
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','answered','archived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_urgent` (`is_urgent`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. contact_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 18. newsletter_subscribers
-- ============================================================
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_subscribed_at` (`subscribed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 19. settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 20. homepage_sections
-- ============================================================
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `section_type` varchar(50) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_section_type` (`section_type`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 21. bible_verses
-- ============================================================
CREATE TABLE IF NOT EXISTS `bible_verses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verse_text` text NOT NULL,
  `reference` varchar(255) NOT NULL,
  `book` varchar(100) DEFAULT NULL,
  `chapter` int(11) DEFAULT NULL,
  `verse_number` int(11) DEFAULT NULL,
  `is_daily` tinyint(1) NOT NULL DEFAULT 0,
  `is_weekly` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reference` (`reference`),
  KEY `idx_book` (`book`),
  KEY `idx_is_daily` (`is_daily`),
  KEY `idx_is_weekly` (`is_weekly`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 22. leadership
-- ============================================================
CREATE TABLE IF NOT EXISTS `leadership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `order_position` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_position` (`order_position`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 23. departments
-- ============================================================
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `head_name` varchar(255) DEFAULT NULL,
  `head_email` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 24. ministries
-- ============================================================
CREATE TABLE IF NOT EXISTS `ministries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
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

-- ============================================================
-- 25. branches
-- ============================================================
CREATE TABLE IF NOT EXISTS `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pastor_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 26. testimonials
-- ============================================================
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` int(11) DEFAULT NULL,
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

-- ============================================================
-- 27. announcements
-- ============================================================
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','archived') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_pinned` (`is_pinned`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 28. notifications
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
-- 29. activity_logs
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
-- 30. system_logs
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
-- 31. backups
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
-- 32. media_library
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
-- 33. seo
-- ============================================================
CREATE TABLE IF NOT EXISTS `seo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_slug` varchar(255) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` text DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_slug` (`page_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 34. menus
-- ============================================================
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `target` varchar(20) DEFAULT '_self',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 35. page_content
-- ============================================================
CREATE TABLE IF NOT EXISTS `page_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_slug` varchar(255) NOT NULL,
  `section_key` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `content_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_page_slug` (`page_slug`),
  KEY `idx_section_key` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 36. prophetic_school_applications
-- ============================================================
CREATE TABLE IF NOT EXISTS `prophetic_school_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 37. pastor_bookings
-- ============================================================
CREATE TABLE IF NOT EXISTS `pastor_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pastor_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `client_phone` varchar(20) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 30,
  `booking_type` enum('general','counseling','prayer','deliverance','healing','prophecy','other') NOT NULL DEFAULT 'general',
  `subject` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed','no_show') NOT NULL DEFAULT 'pending',
  `confirmation_code` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pastor_id` (`pastor_id`),
  KEY `idx_client_email` (`client_email`),
  KEY `idx_booking_date` (`booking_date`),
  KEY `idx_status` (`status`),
  KEY `idx_confirmation_code` (`confirmation_code`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 38. pastor_booking_availability
-- ============================================================
CREATE TABLE IF NOT EXISTS `pastor_booking_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pastor_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `booking_duration_minutes` int(11) NOT NULL DEFAULT 30,
  `max_bookings_per_day` int(11) NOT NULL DEFAULT 8,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pastor_id` (`pastor_id`),
  KEY `idx_day_of_week` (`day_of_week`),
  KEY `idx_is_active` (`is_active`)
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
-- Users (3 sample users)
-- ============================================================
INSERT IGNORE INTO `users` (`id`, `first_name`, `last_name`, `email`, `username`, `password`, `role`, `phone`, `country`, `is_active`, `email_verified`)
VALUES
  (1, 'Apostle', 'Faty', 'apostle.faty@salem-dominion-ministries.com', 'apostlefaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pastor', '+256753244480', 'Uganda', 1, 1),
  (2, 'John', 'Doe', 'john.doe@example.com', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', '+256700123456', 'Uganda', 1, 1),
  (3, 'Jane', 'Smith', 'jane.smith@example.com', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+256751234567', 'Kenya', 1, 1);

-- ============================================================
-- News Categories (5)
-- ============================================================
INSERT IGNORE INTO `news_categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`)
VALUES
  (1, 'Announcements', 'announcements', 'Official church announcements and updates', 1, 1),
  (2, 'Ministry News', 'ministry-news', 'News from various church ministries', 2, 1),
  (3, 'Testimonies', 'testimonies', 'Stories of faith and transformation', 3, 1),
  (4, 'Events Recap', 'events-recap', 'Recaps of past church events', 4, 1),
  (5, 'Community', 'community', 'Community outreach and social impact', 5, 1);

-- ============================================================
-- News (5 articles: published, draft, archived)
-- ============================================================
INSERT IGNORE INTO `news` (`id`, `title`, `content`, `excerpt`, `category`, `tags`, `status`, `is_featured`, `author_id`)
VALUES
  (1,
   'New Church Building Project Announced',
   'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach. The project, which has been in the planning phase for over a year, will feature a modern auditorium with seating for 2,000 people, a state-of-the-art sound system, and dedicated spaces for children and youth ministry. We invite every member to be part of this historic milestone through their generous contributions and prayers.',
   'Exciting news about our expansion plans to better serve our community and accommodate our growing family.',
   'Announcements', 'building,expansion,project', 'published', 1, 1),
  (2,
   'Pastor Faty Musasizi Receives Community Service Award',
   'Our beloved Apostle and Founder, Faty Musasizi, was honored with the prestigious Community Service Award at the annual Gospel Ministers Summit. The award recognizes his outstanding contributions to community development through spiritual leadership, education support, and humanitarian aid. Pastor Faty dedicated the award to the entire Salem Dominion Ministries family, stating that none of it would have been possible without the support of the congregation.',
   'Recognition for our pastor''s tireless dedication to community service and transformation.',
   'Announcements', 'award,pastor,recognition', 'published', 0, 1),
  (3,
   'Children''s Ministry Expansion Program',
   'We are expanding our children''s ministry with new programs and facilities designed to better serve our young ones. The expansion includes a new curriculum based on interactive storytelling, worship sessions tailored for different age groups, and an outdoor play area. We are also looking for volunteer teachers who have a heart for children.',
   'New programs and facilities to nurture our children in faith and love.',
   'Ministry News', 'children,expansion,ministry', 'draft', 0, 1),
  (4,
   'Annual Prayer and Fasting Week Announced',
   'Join us for our annual week of prayer and fasting scheduled for next month. This spiritual exercise has been a cornerstone of our ministry for years, and we have seen countless breakthroughs as a result. The theme for this year is "Breaking Every Chain" and will include nightly prayer sessions, worship, and prophetic declarations.',
   'Our annual spiritual retreat to seek God''s face for breakthroughs and transformation.',
   'Announcements', 'prayer,fasting,spiritual', 'draft', 0, 1),
  (5,
   'Youth Conference 2025 Recap',
   'Our Youth Conference 2025 was a tremendous success with over 500 young people in attendance. The three-day event featured powerful worship, insightful teachings, and transformative workshops. Guest speakers included Apostle Faty Musasizi and several other anointed men of God. Many young people made commitments to serve God and their communities.',
   'A look back at the impactful Youth Conference that empowered the next generation.',
   'Events Recap', 'youth,conference,2025', 'archived', 0, 1);

-- ============================================================
-- Events (5: 3 upcoming, 2 completed)
-- ============================================================
INSERT IGNORE INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `end_time`, `location`, `venue`, `speaker`, `max_attendees`, `is_featured`, `status`, `created_by`)
VALUES
  (1,
   'Sunday Morning Worship Service',
   'Join us for a powerful time of worship and the Word. Every Sunday we gather to lift the name of Jesus and receive fresh anointing for the week ahead. Come expecting a miracle!',
   DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', '12:00:00',
   'Main Sanctuary', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', 500, 1, 'upcoming', 1),
  (2,
   'Midweek Prayer Meeting',
   'Experience the presence of God through corporate prayer and intercession. Join us every Wednesday evening as we pray for the church, the nation, and one another. Prayer changes things!',
   DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:30:00', '20:30:00',
   'Prayer Hall', 'Salem Dominion Ministries HQ', 'Pastor Faty Musasizi', 200, 0, 'upcoming', 1),
  (3,
   'Youth Conference 2026',
   'A special conference designed to empower and equip the next generation for kingdom impact. Three days of intense worship, teaching, and ministry. Register now to secure your spot!',
   DATE_ADD(CURDATE(), INTERVAL 30 DAY), '10:00:00', '18:00:00',
   'Main Sanctuary', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', 800, 1, 'upcoming', 1),
  (4,
   'Easter Sunday Celebration',
   'Celebrate the resurrection of our Lord Jesus Christ with us! A special Easter service filled with praise, worship, and a powerful Easter message of hope and new beginnings.',
   DATE_SUB(CURDATE(), INTERVAL 90 DAY), '08:00:00', '12:00:00',
   'Main Sanctuary', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', 500, 1, 'completed', 1),
  (5,
   'Leadership Summit 2025',
   'An equipping session for all ministry leaders and department heads. Topics include effective leadership, team management, and spiritual growth for leaders.',
   DATE_SUB(CURDATE(), INTERVAL 45 DAY), '09:00:00', '16:00:00',
   'Conference Room', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', 100, 0, 'completed', 1);

-- ============================================================
-- Sermons (5 sermons, different categories)
-- ============================================================
INSERT IGNORE INTO `sermons` (`id`, `title`, `description`, `preacher`, `sermon_date`, `category`, `series`, `media_type`, `media_url`, `scripture`, `duration`, `status`, `is_featured`, `views`, `created_by`)
VALUES
  (1,
   'The Power of Faith',
   'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God. Learn to activate your faith for miracles.',
   'Apostle Faty Musasizi', DATE_SUB(CURDATE(), INTERVAL 7 DAY),
   'Faith', 'Walking in Power', 'video', 'https://www.youtube.com/watch?v=example1',
   'Hebrews 11:1', 2840, 'published', 1, 1250, 1),
  (2,
   'Walking in Divine Purpose',
   'Discovering and fulfilling God''s divine purpose for your life through biblical principles and practical application. Every believer has a purpose - find yours!',
   'Apostle Faty Musasizi', DATE_SUB(CURDATE(), INTERVAL 14 DAY),
   'Purpose', 'Walking in Power', 'video', 'https://www.youtube.com/watch?v=example2',
   'Jeremiah 29:11', 3120, 'published', 1, 980, 1),
  (3,
   'The Blessing of Obedience',
   'Understanding how obedience to God''s word brings blessings and breakthroughs in every area of life. Obedience is better than sacrifice.',
   'Pastor Faty Musasizi', DATE_SUB(CURDATE(), INTERVAL 21 DAY),
   'Obedience', NULL, 'audio', 'https://audio.salem-dominion-ministries.com/sermon3.mp3',
   'Deuteronomy 28:1-2', 2640, 'published', 0, 756, 1),
  (4,
   'Spiritual Warfare: Winning the Battle',
   'An in-depth teaching on spiritual warfare and the armor of God. Learn how to stand firm against the enemy''s tactics and claim your victory in Christ.',
   'Apostle Faty Musasizi', DATE_SUB(CURDATE(), INTERVAL 28 DAY),
   'Spiritual Warfare', 'Battle Ready', 'video', 'https://www.youtube.com/watch?v=example4',
   'Ephesians 6:10-18', 3600, 'published', 0, 1420, 1),
  (5,
   'The Heart of Worship',
   'What does it truly mean to worship God in spirit and truth? This sermon explores the essence of genuine worship and how it transforms our relationship with God.',
   'Apostle Faty Musasizi', DATE_SUB(CURDATE(), INTERVAL 35 DAY),
   'Worship', NULL, 'video', 'https://www.youtube.com/watch?v=example5',
   'John 4:23-24', 2400, 'published', 0, 890, 1);

-- ============================================================
-- Albums (3)
-- ============================================================
INSERT IGNORE INTO `albums` (`id`, `name`, `description`, `cover_image`, `created_by`)
VALUES
  (1, 'Sunday Services', 'Photos from our regular Sunday worship services', 'uploads/albums/sunday_services_cover.jpg', 1),
  (2, 'Youth Conference 2025', 'Memorable moments from the Youth Conference 2025', 'uploads/albums/youth_conf_cover.jpg', 1),
  (3, 'Community Outreach', 'Our community outreach and charitable activities', 'uploads/albums/outreach_cover.jpg', 1);

-- ============================================================
-- Gallery (3 items)
-- ============================================================
INSERT IGNORE INTO `gallery` (`id`, `title`, `description`, `file_url`, `file_type`, `album_id`, `category`, `file_size`, `status`, `uploaded_by`)
VALUES
  (1, 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'uploads/gallery/worship_1.jpg', 'image', 1, 'worship', 2048000, 'published', 1),
  (2, 'Youth Conference 2025 Opening', 'The opening ceremony of our Youth Conference filled with praise and expectation.', 'uploads/gallery/youth_conference_2025.jpg', 'image', 2, 'events', 3145728, 'published', 1),
  (3, 'Life-Changing Testimony', 'A powerful testimony of God''s faithfulness and transformation shared during service.', 'uploads/gallery/testimony_1.mp4', 'video', NULL, 'testimonies', 15728640, 'published', 1);

-- ============================================================
-- YouTube Live
-- ============================================================
INSERT IGNORE INTO `youtube_live` (`id`, `title`, `youtube_url`, `embed_url`, `is_live`, `is_enabled`, `created_by`)
VALUES
  (1, 'Sunday Live Service', 'https://www.youtube.com/@salem-dominion-ministries/live', 'https://www.youtube.com/embed/?listType=live&list=channel', 0, 1, 1);

-- ============================================================
-- Ministries (5)
-- ============================================================
INSERT IGNORE INTO `ministries` (`id`, `name`, `description`, `leader_name`, `leader_email`, `leader_phone`, `meeting_day`, `meeting_time`, `meeting_location`, `category`, `is_active`, `sort_order`, `created_by`)
VALUES
  (1, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching, worship, and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.com', '+256751234567', 'Sunday', '09:00:00', 'Children''s Hall', 'children', 1, 1, 1),
  (2, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith through dynamic programs.', 'Michael Williams', 'youth@salem-dominion-ministries.com', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', 1, 2, 1),
  (3, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship, discipleship, and mentorship.', 'Grace Brown', 'women@salem-dominion-ministries.com', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', 1, 3, 1),
  (4, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles and integrity.', 'David Davis', 'men@salem-dominion-ministries.com', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', 1, 4, 1),
  (5, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God''s presence to dwell.', 'Apostle Faty Musasizi', 'worship@salem-dominion-ministries.com', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', 1, 5, 1);

-- ============================================================
-- Leadership (3 members with real pastor info)
-- ============================================================
INSERT IGNORE INTO `leadership` (`id`, `name`, `title`, `bio`, `email`, `phone`, `order_position`, `is_active`)
VALUES
  (1, 'Apostle Faty Musasizi', 'Senior Pastor & Founder',
   'Apostle Faty Musasizi is the founder and senior pastor of Salem Dominion Ministries. With over 20 years of ministry experience, he has a passion for empowering believers and spreading the Gospel across nations. He is a prophetic voice to this generation, leading with wisdom, compassion, and uncompromising devotion to the Word of God.',
   'apostle@salem-dominion-ministries.com', '+256753244480', 1, 1),
  (2, 'Sarah Johnson', 'Children Ministry Director',
   'Sarah has a heart for children and has been leading our children ministry for over 10 years, creating engaging programs that help kids grow in their faith. She holds a degree in Early Childhood Education and brings creativity and dedication to every program.',
   'children@salem-dominion-ministries.com', '+256751234567', 2, 1),
  (3, 'Michael Williams', 'Youth Ministry Leader',
   'Michael is passionate about reaching the next generation and leads our youth ministry with creative programs and relevant teaching. His energetic leadership has grown the youth ministry from 30 to over 200 members in just three years.',
   'youth@salem-dominion-ministries.com', '+256702345678', 3, 1);

-- ============================================================
-- Departments (4)
-- ============================================================
INSERT IGNORE INTO `departments` (`id`, `name`, `description`, `head_name`, `head_email`, `is_active`)
VALUES
  (1, 'Worship & Praise', 'Leading worship and praise sessions during all services and special events.', 'Apostle Faty Musasizi', 'worship@salem-dominion-ministries.com', 1),
  (2, 'Media & Communications', 'Managing all media production, social media, and church communications.', 'Tech Team', 'media@salem-dominion-ministries.com', 1),
  (3, 'Finance & Administration', 'Overseeing church finances, budgeting, and administrative operations.', 'Finance Team', 'finance@salem-dominion-ministries.com', 1),
  (4, 'Hospitality & Ushering', 'Welcoming and serving all visitors and members during services and events.', 'Hospitality Team', 'hospitality@salem-dominion-ministries.com', 1);

-- ============================================================
-- Branches (1 - HQ)
-- ============================================================
INSERT IGNORE INTO `branches` (`id`, `name`, `address`, `city`, `phone`, `email`, `pastor_name`, `is_active`)
VALUES
  (1, 'Salem Dominion - Head Quarters', 'Plot 45, Kampala Road', 'Kampala', '+256753244480', 'info@salem-dominion-ministries.com', 'Apostle Faty Musasizi', 1);

-- ============================================================
-- Testimonials (3)
-- ============================================================
INSERT IGNORE INTO `testimonials` (`id`, `name`, `email`, `occupation`, `testimonial`, `rating`, `is_approved`, `is_featured`, `status`, `approved_by`)
VALUES
  (1, 'John Doe', 'john.doe@example.com', 'Business Owner',
   'Salem Dominion Ministries has truly transformed my life. The teachings are powerful, the worship is heavenly, and the community is amazing. I came as a visitor and found my spiritual home. God bless Apostle Faty and the entire leadership.',
   5, 1, 1, 'approved', 1),
  (2, 'Jane Smith', 'jane.smith@example.com', 'Teacher',
   'I found my spiritual home here. The worship is uplifting and the messages are life-changing. Since joining Salem Dominion, my faith has grown immensely and I have experienced God''s faithfulness in my career and family.',
   5, 1, 1, 'approved', 1),
  (3, 'Michael Johnson', 'michael.j@example.com', 'Student',
   'The youth ministry helped me discover my purpose in God. Through the mentorship and programs, I have grown from a confused young man to a confident servant of God. I am forever grateful to Salem Dominion Ministries.',
   4, 1, 0, 'approved', 1);

-- ============================================================
-- Donations (3)
-- ============================================================
INSERT IGNORE INTO `donations` (`id`, `donor_name`, `donor_email`, `donor_phone`, `amount`, `donation_type`, `payment_method`, `status`, `confirmation_code`)
VALUES
  (1, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', 'completed', 'DON-SDM-2026-001'),
  (2, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', 'completed', 'DON-SDM-2026-002'),
  (3, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', 'confirmed', 'DON-SDM-2026-003');

-- ============================================================
-- Donation Campaigns (1 default campaign)
-- ============================================================
INSERT IGNORE INTO `donation_campaigns` (`id`, `title`, `description`, `goal`, `raised`, `start_date`, `end_date`, `is_active`)
VALUES
  (1, 'New Church Building Fund', 'Help us build a new church to accommodate our growing congregation. Every donation brings us closer to our dream of having a permanent worship center that seats 2,000 people.', 50000000.00, 12500000.00, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), DATE_ADD(CURDATE(), INTERVAL 18 MONTH), 1);

-- ============================================================
-- Prayer Requests (3)
-- ============================================================
INSERT IGNORE INTO `prayer_requests` (`id`, `name`, `email`, `phone`, `request_text`, `is_urgent`, `is_anonymous`, `status`)
VALUES
  (1, 'Sarah K.', 'sarah.k@example.com', '+256711122333', 'Please pray for my mother who is in the hospital. She has been diagnosed with a serious illness and we need God''s healing touch. We believe in the power of prayer.', 1, 0, 'pending'),
  (2, 'Anonymous', NULL, NULL, 'Pray for my business. I have been struggling financially for months and need God''s intervention and divine provision.', 0, 1, 'pending'),
  (3, 'David O.', 'david.o@example.com', '+256722233444', 'Thank you church for your prayers last month. My wife has recovered fully and we give God all the glory! Please continue to pray for our family.', 0, 0, 'answered');

-- ============================================================
-- Contact Messages (3)
-- ============================================================
INSERT IGNORE INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`)
VALUES
  (1, 'Grace N.', 'grace.n@example.com', '+256733344555', 'Service Times Inquiry', 'Good morning. I would like to know the service times for Sunday. I am new in the area and looking for a church to attend. Thank you.', 0),
  (2, 'Peter M.', 'peter.m@example.com', '+256744455666', 'Volunteer Registration', 'I would like to volunteer in the children ministry. I have experience working with kids and would love to serve. Please get back to me.', 1),
  (3, 'Linda A.', 'linda.a@example.com', '+256755566777', 'Booking Request', 'I would like to book a meeting with Apostle Faty for spiritual counseling. Please let me know the available dates. God bless.', 0);

-- ============================================================
-- Newsletter Subscribers (3)
-- ============================================================
INSERT IGNORE INTO `newsletter_subscribers` (`email`, `name`, `is_active`)
VALUES
  ('subscriber1@example.com', 'Mary W.', 1),
  ('subscriber2@example.com', 'Joseph K.', 1),
  ('subscriber3@example.com', 'Ruth N.', 1);

-- ============================================================
-- Settings (default church settings)
-- ============================================================
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`)
VALUES
  ('church_name', 'Salem Dominion Ministries', 'general'),
  ('church_phone', '+256753244480', 'contact'),
  ('church_email', 'info@salem-dominion-ministries.com', 'contact'),
  ('church_address', 'Plot 45, Kampala Road, Kampala, Uganda', 'contact'),
  ('church_city', 'Kampala', 'contact'),
  ('church_country', 'Uganda', 'contact'),
  ('church_website', 'https://salem-dominion-ministries.com', 'general'),
  ('church_youtube', 'https://www.youtube.com/@salem-dominion-ministries', 'social'),
  ('church_facebook', 'https://www.facebook.com/salemdominionministries', 'social'),
  ('church_instagram', 'https://www.instagram.com/salemdominionministries', 'social'),
  ('church_twitter', 'https://twitter.com/salemdommin', 'social'),
  ('church_whatsapp', '+256753244480', 'social'),
  ('service_sunday_time', '09:00 AM - 12:00 PM', 'services'),
  ('service_wednesday_time', '06:30 PM - 08:30 PM', 'services'),
  ('service_friday_time', '06:00 PM - 08:00 PM', 'services'),
  ('currency', 'UGX', 'donations'),
  ('mobile_money_number', '+256753244480', 'donations'),
  ('bank_name', 'Centenary Bank', 'donations'),
  ('bank_account_name', 'Salem Dominion Ministries', 'donations'),
  ('bank_account_number', '1234567890', 'donations');

-- ============================================================
-- Homepage Sections (7)
-- ============================================================
INSERT IGNORE INTO `homepage_sections` (`id`, `title`, `content`, `section_type`, `sort_order`, `is_active`)
VALUES
  (1, 'Hero Section', 'Welcome to Salem Dominion Ministries. A place of divine encounter, spiritual growth, and kingdom impact. Join us and experience the transformative power of God.', 'hero', 1, 1),
  (2, 'About Us', 'Salem Dominion Ministries is a Bible-believing, Spirit-filled church committed to raising a generation of believers who are on fire for God. Founded by Apostle Faty Musasizi, our ministry has grown to become a beacon of hope and transformation in our community and beyond.', 'about', 2, 1),
  (3, 'Our Mission', 'To reach the lost, disciple the found, and equip believers for kingdom service through the power of the Holy Spirit and the uncompromised Word of God.', 'mission', 3, 1),
  (4, 'Our Vision', 'To be a global ministry that raises sons and daughters of God who are impact-makers in their families, communities, and nations for the glory of God.', 'vision', 4, 1),
  (5, 'Service Times', 'Sunday Morning Service: 9:00 AM - 12:00 PM | Midweek Prayer: Wednesday 6:30 PM - 8:30 PM | Youth Service: Friday 6:00 PM - 8:00 PM', 'services', 5, 1),
  (6, 'Call to Action', 'Are you looking for a church home? Salem Dominion Ministries welcomes you with open arms. Join us this Sunday and experience God like never before!', 'cta', 6, 1),
  (7, 'Contact Info', 'Visit us at Plot 45, Kampala Road, Kampala, Uganda. Call us at +256753244480 or email info@salem-dominion-ministries.com. We would love to hear from you!', 'contact', 7, 1);

-- ============================================================
-- Bible Verses (5)
-- ============================================================
INSERT IGNORE INTO `bible_verses` (`id`, `verse_text`, `reference`, `book`, `chapter`, `verse_number`, `is_daily`, `is_weekly`)
VALUES
  (1, 'For I know the plans I have for you, declares the LORD, plans to prosper you and not to harm you, plans to give you hope and a future.', 'Jeremiah 29:11', 'Jeremiah', 29, 11, 1, 0),
  (2, 'Trust in the LORD with all your heart and lean not on your own understanding; in all your ways submit to him, and he will make your paths straight.', 'Proverbs 3:5-6', 'Proverbs', 3, 5, 0, 1),
  (3, 'The LORD is my shepherd, I lack nothing. He makes me lie down in green pastures, he leads me beside quiet waters, he refreshes my soul.', 'Psalm 23:1-3', 'Psalms', 23, 1, 1, 0),
  (4, 'But those who hope in the LORD will renew their strength. They will soar on wings like eagles; they will run and not grow weary, they will walk and not be faint.', 'Isaiah 40:31', 'Isaiah', 40, 31, 0, 1),
  (5, 'Be strong and courageous. Do not be afraid; do not be discouraged, for the LORD your God will be with you wherever you go.', 'Joshua 1:9', 'Joshua', 1, 9, 1, 0);

-- ============================================================
-- Announcements (3)
-- ============================================================
INSERT IGNORE INTO `announcements` (`id`, `title`, `content`, `is_pinned`, `start_date`, `end_date`, `status`, `created_by`)
VALUES
  (1, 'New Building Project - Phase 1 Begins',
   'We are thrilled to announce that Phase 1 of our new church building project has officially begun. We encourage every member to contribute towards this vision. See the finance team for details.',
   1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'active', 1),
  (2, 'Easter Sunday Service Special',
   'Join us for a powerful Easter Sunday celebration. We will have a special sunrise service at 6:00 AM followed by the main service at 9:00 AM. Invite your friends and family!',
   0, DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'active', 1),
  (3, 'Church Registration Drive',
   'All members are encouraged to register with the church database for better communication and pastoral care. Please see the secretary after any service to complete your registration.',
   0, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'inactive', 1);

-- ============================================================
-- Menus (10 default navigation items)
-- ============================================================
INSERT IGNORE INTO `menus` (`id`, `label`, `url`, `icon`, `parent_id`, `sort_order`, `is_active`, `target`)
VALUES
  (1, 'Home', '/', 'fas fa-home', NULL, 1, 1, '_self'),
  (2, 'About', '/about.php', 'fas fa-church', NULL, 2, 1, '_self'),
  (3, 'Sermons', '/sermons.php', 'fas fa-book-open', NULL, 3, 1, '_self'),
  (4, 'Events', '/events.php', 'fas fa-calendar-alt', NULL, 4, 1, '_self'),
  (5, 'Ministries', '/ministries.php', 'fas fa-hands-helping', NULL, 5, 1, '_self'),
  (6, 'Gallery', '/gallery.php', 'fas fa-images', NULL, 6, 1, '_self'),
  (7, 'Contact', '/contact.php', 'fas fa-envelope', NULL, 7, 1, '_self'),
  (8, 'Donate', '/donate.php', 'fas fa-hand-holding-heart', NULL, 8, 1, '_self'),
  (9, 'Give', '/donate.php', 'fas fa-donate', NULL, 9, 1, '_self'),
  (10, 'Live', '/live.php', 'fas fa-broadcast-tower', NULL, 10, 1, '_self');

-- ============================================================
-- Pastor Booking Availability (for Pastor Faty - admin_id=2)
-- ============================================================
INSERT IGNORE INTO `pastor_booking_availability` (`pastor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `booking_duration_minutes`, `max_bookings_per_day`, `is_active`)
VALUES
  (2, 'monday', '09:00:00', '18:00:00', 1, 30, 8, 1),
  (2, 'tuesday', '09:00:00', '18:00:00', 1, 30, 8, 1),
  (2, 'wednesday', '09:00:00', '15:00:00', 1, 30, 8, 1),
  (2, 'thursday', '09:00:00', '18:00:00', 1, 30, 8, 1),
  (2, 'friday', '09:00:00', '15:00:00', 1, 30, 8, 1);

-- ============================================================
-- SEO defaults for key pages
-- ============================================================
INSERT IGNORE INTO `seo` (`page_slug`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`)
VALUES
  ('home', 'Salem Dominion Ministries - Home', 'Welcome to Salem Dominion Ministries. A place of divine encounter, spiritual growth, and kingdom impact.', 'church, worship, dominion, salem, ministry, kampala, uganda', 'Salem Dominion Ministries', 'A place of divine encounter and spiritual growth'),
  ('about', 'About Us - Salem Dominion Ministries', 'Learn about Salem Dominion Ministries, our mission, vision, and the story of our ministry.', 'about, mission, vision, church history', 'About Salem Dominion Ministries', 'Our mission and vision'),
  ('sermons', 'Sermons - Salem Dominion Ministries', 'Watch and listen to life-changing sermons from Apostle Faty Musasizi and our ministry team.', 'sermons, preaching, teaching, word of god', 'Our Sermons', 'Life-changing sermons and teachings'),
  ('events', 'Events - Salem Dominion Ministries', 'Discover upcoming events, conferences, and gatherings at Salem Dominion Ministries.', 'events, conferences, gatherings, church events', 'Church Events', 'Upcoming events and conferences'),
  ('donate', 'Donate - Salem Dominion Ministries', 'Support the work of God through your generous giving. Give online via mobile money, bank transfer, or card.', 'donate, giving, tithe, offering, giving to church', 'Give to Salem Dominion Ministries', 'Support our ministry through your generous giving'),
  ('contact', 'Contact Us - Salem Dominion Ministries', 'Get in touch with Salem Dominion Ministries. Visit us, call us, or send us a message.', 'contact, location, phone, email, address', 'Contact Salem Dominion Ministries', 'Reach out to us'),
  ('ministries', 'Ministries - Salem Dominion Ministries', 'Explore our various ministries designed to serve and empower every member of the family.', 'ministries, youth, children, women, men, worship', 'Our Ministries', 'Ministries for every member'),
  ('gallery', 'Gallery - Salem Dominion Ministries', 'Browse photos and videos from our services, events, and ministry activities.', 'gallery, photos, videos, church media', 'Photo Gallery', 'Moments from our services and events');

-- ============================================================
-- Page Content defaults
-- ============================================================
INSERT IGNORE INTO `page_content` (`page_slug`, `section_key`, `content`, `content_type`)
VALUES
  ('about', 'history', 'Salem Dominion Ministries was founded by Apostle Faty Musasizi with a vision to raise a generation of believers who are on fire for God. What started as a small fellowship has grown into a vibrant church community impacting lives across Uganda and beyond.', 'text'),
  ('about', 'mission', 'To reach the lost, disciple the found, and equip believers for kingdom service through the power of the Holy Spirit and the uncompromised Word of God.', 'text'),
  ('about', 'vision', 'To be a global ministry that raises sons and daughters of God who are impact-makers in their families, communities, and nations for the glory of God.', 'text'),
  ('about', 'core_values', 'Prayer, Holiness, Excellence, Integrity, Unity, Discipleship, Evangelism', 'text'),
  ('home', 'welcome_message', 'Welcome to Salem Dominion Ministries! We are a Bible-believing, Spirit-filled church committed to raising a generation of believers who are on fire for God. Whether you are visiting for the first time or looking for a church home, you belong here.', 'text'),
  ('home', 'service_announcement', 'Join us every Sunday from 9:00 AM to 12:00 PM for our main worship service. Midweek services every Wednesday at 6:30 PM.', 'text'),
  ('home', 'cta_text', 'Experience God like never before. Visit us this Sunday!', 'text');

-- ============================================================
-- Sample system log entry
-- ============================================================
INSERT IGNORE INTO `system_logs` (`level`, `message`, `context`)
VALUES
  ('info', 'Database schema created successfully', 'database_setup'),
  ('info', 'Default admin accounts created', 'database_setup'),
  ('info', 'Sample data inserted successfully', 'database_setup');

-- ============================================================
-- Sample activity log
-- ============================================================
INSERT IGNORE INTO `activity_logs` (`user_id`, `action`, `module`, `details`, `ip_address`)
VALUES
  (1, 'Database Setup', 'system', 'Complete database schema created with all 38 tables and sample data', '127.0.0.1');

-- ============================================================
-- ============================================================
--
-- DATABASE SETUP COMPLETE!
--
-- Total Tables: 38
-- Total Sample Items: 50+
--
-- ============================================================
-- ============================================================
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
-- General User Login Credentials:
-- ============================================================
--
-- User Account 1 (Pastor):
--   Username: apostlefaty
--   Password: password
--   Email:    apostle.faty@salem-dominion-ministries.com
--
-- User Account 2 (Member):
--   Username: johndoe
--   Password: password
--   Email:    john.doe@example.com
--
-- User Account 3 (User):
--   Username: janesmith
--   Password: password
--   Email:    jane.smith@example.com
--
-- ============================================================
-- DEFAULT CHURCH SETTINGS:
-- ============================================================
--
--   Church Name:    Salem Dominion Ministries
--   Phone:          +256753244480
--   Email:          info@salem-dominion-ministries.com
--   Address:        Plot 45, Kampala Road, Kampala, Uganda
--   Currency:       UGX
--
-- ============================================================
-- TABLE SUMMARY:
-- ============================================================
--
--  1.  admin_users                      (2 admins)
--  2.  admin_sessions                   (session management)
--  3.  admin_login_logs                 (security audit)
--  4.  admin_settings                   (9 settings)
--  5.  users                            (3 users)
--  6.  news_categories                  (5 categories)
--  7.  news                             (5 articles)
--  8.  events                           (5 events)
--  9.  event_registrations              (registration data)
-- 10.  sermons                          (5 sermons)
-- 11.  albums                           (3 albums)
-- 12.  gallery                          (3 items)
-- 13.  youtube_live                     (1 live config)
-- 14.  donations                        (3 donations)
-- 15.  donation_campaigns               (1 campaign)
-- 16.  prayer_requests                  (3 requests)
-- 17.  contact_messages                 (3 messages)
-- 18.  newsletter_subscribers           (3 subscribers)
-- 19.  settings                         (20 settings)
-- 20.  homepage_sections                (7 sections)
-- 21.  bible_verses                     (5 verses)
-- 22.  leadership                       (3 members)
-- 23.  departments                      (4 departments)
-- 24.  ministries                       (5 ministries)
-- 25.  branches                         (1 branch)
-- 26.  testimonials                     (3 testimonials)
-- 27.  announcements                    (3 announcements)
-- 28.  notifications                    (notification system)
-- 29.  activity_logs                    (activity tracking)
-- 30.  system_logs                      (system logging)
-- 31.  backups                          (backup management)
-- 32.  media_library                    (media management)
-- 33.  seo                              (8 page SEO configs)
-- 34.  menus                            (10 nav items)
-- 35.  page_content                     (7 page sections)
-- 36.  prophetic_school_applications    (application system)
-- 37.  pastor_bookings                  (booking system)
-- 38.  pastor_booking_availability      (5 schedules)
--
-- ============================================================
-- NOTE: All password hashes are for the word "password"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================
