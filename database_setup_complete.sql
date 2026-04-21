-- SALEM DOMINION MINISTRIES - COMPLETE DATABASE SETUP
-- Database: salem_dominion_ministries
-- Purpose: Complete database structure with sample data

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

-- USERS TABLE - General user accounts for sermon interactions
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

-- Insert sample users for testing
INSERT INTO `users` (`first_name`, `last_name`, `email`, `username`, `password`, `phone`, `country`, `role`, `is_active`, `email_verified`) VALUES
('Apostle', 'Faty', 'apostle.faty@salem-dominion-ministries.org', 'apostlefaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256753244480', 'Uganda', 'pastor', 1, 1),
('John', 'Doe', 'john.doe@example.com', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256700123456', 'Uganda', 'member', 1, 1),
('Jane', 'Smith', 'jane.smith@example.com', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+256751234567', 'Kenya', 'user', 1, 1)
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
    `dimensions` varchar(50) DEFAULT NULL,
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

-- LEADERSHIP TABLE - Store leadership team information
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

-- PASTOR_BOOKINGS TABLE - For book_pastor_call.php functionality
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
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pastor_id` (`pastor_id`),
    KEY `idx_client_email` (`client_email`),
    KEY `idx_booking_date` (`booking_date`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_confirmation_code` (`confirmation_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASTOR_BOOKING_AVAILABILITY TABLE - Store pastor availability schedule
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

INSERT INTO `ministries` (`name`, `description`, `leader_name`, `leader_email`, `leader_phone`, `meeting_day`, `meeting_time`, `meeting_location`, `category`, `is_active`, `sort_order`, `created_by`) VALUES
('Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', 1, 1, 1),
('Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', 1, 2, 1),
('Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', 1, 3, 1),
('Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', 1, 4, 1),
('Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', 1, 5, 1);

INSERT INTO `leadership` (`name`, `title`, `bio`, `email`, `phone`, `order_position`, `is_active`) VALUES
('Apostle Faty Musasizi', 'Senior Pastor & Founder', 'Apostle Faty Musasizi is the founder and senior pastor of Salem Dominion Ministries. With over 20 years of ministry experience, he has a passion for empowering believers and spreading the Gospel.', 'apostle@salem-dominion-ministries.org', '+256753244480', 1, 1),
('Sarah Johnson', 'Children Ministry Director', 'Sarah has a heart for children and has been leading our children ministry for over 10 years, creating engaging programs that help kids grow in their faith.', 'children@salem-dominion-ministries.org', '+256751234567', 2, 1),
('Michael Williams', 'Youth Ministry Leader', 'Michael is passionate about reaching the next generation and leads our youth ministry with creative programs and relevant teaching.', 'youth@salem-dominion-ministries.org', '+256702345678', 3, 1);

INSERT INTO `testimonials` (`name`, `email`, `occupation`, `testimonial`, `rating`, `is_approved`, `status`, `approved_by`) VALUES
('John Doe', 'john.doe@example.com', 'Business Owner', 'Salem Dominion Ministries has transformed my life. The teachings are powerful and the community is amazing!', 5, 1, 'approved', 1),
('Jane Smith', 'jane.smith@example.com', 'Teacher', 'I found my spiritual home here. The worship is uplifting and the messages are life-changing.', 5, 1, 'approved', 1),
('Michael Johnson', 'michael.j@example.com', 'Student', 'The youth ministry helped me discover my purpose in God. I am forever grateful!', 4, 1, 'approved', 1);

INSERT INTO `gallery` (`uploaded_by`, `file_url`, `title`, `description`, `file_type`, `category`, `status`) VALUES
(1, 'uploads/gallery/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', 'published'),
(1, 'uploads/gallery/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', 'published'),
(1, 'uploads/gallery/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', 'published');

INSERT INTO `donations` (`donor_name`, `donor_email`, `donor_phone`, `amount`, `donation_type`, `payment_method`, `status`, `whatsapp_sent`, `confirmation_code`) VALUES
('John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', 'completed', 1, 'DON2024001'),
('Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', 'completed', 1, 'DON2024002'),
('Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', 'confirmed', 0, 'DON2024003');

-- Insert pastor availability schedule
INSERT INTO `pastor_booking_availability` (`pastor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `booking_duration_minutes`, `max_bookings_per_day`, `is_active`) VALUES
(1, 'monday', '09:00:00', '18:00:00', 1, 30, 8, 1),
(1, 'tuesday', '09:00:00', '18:00:00', 1, 30, 8, 1),
(1, 'wednesday', '09:00:00', '15:00:00', 1, 30, 8, 1),
(1, 'wednesday', '21:00:00', '23:59:59', 1, 30, 8, 1),
(1, 'thursday', '09:00:00', '18:00:00', 1, 30, 8, 1),
(1, 'friday', '09:00:00', '15:00:00', 1, 30, 8, 1),
(1, 'friday', '21:00:00', '23:59:59', 1, 30, 8, 1);

-- Default Login Credentials:
-- Username: MusasiziFaty
-- Password: 123456
