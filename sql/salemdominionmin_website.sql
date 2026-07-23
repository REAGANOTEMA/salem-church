-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 23, 2026 at 09:11 AM
-- Server version: 10.6.23-MariaDB-cll-lve
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `salemdominionmin_website`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `name`, `description`, `cover_image`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Sunday Services', 'Photos from our regular Sunday worship services', 'uploads/albums/sunday_services_cover.jpg', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Youth Conference 2025', 'Memorable moments from the Youth Conference 2025', 'uploads/albums/youth_conf_cover.jpg', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Community Outreach', 'Our community outreach and charitable activities', 'uploads/albums/outreach_cover.jpg', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','inactive','archived','expired') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `is_pinned`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'New Building Project - Phase 1 Begins', 'We are thrilled to announce that Phase 1 of our new church building project has officially begun. We encourage every member to contribute towards this vision. See the finance team for details.', 1, '2026-06-23', '2027-07-23', 'active', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Easter Sunday Service Special', 'Join us for a powerful Easter Sunday celebration. We will have a special sunrise service at 6:00 AM followed by the main service at 9:00 AM. Invite your friends and family!', 0, '2026-07-09', '2026-07-30', 'active', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Church Registration Drive', 'All members are encouraged to register with the church database for better communication and pastoral care. Please see the secretary after any service to complete your registration.', 0, '2026-05-24', '2026-08-22', 'inactive', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `bible_verses`
--

CREATE TABLE `bible_verses` (
  `id` int(11) NOT NULL,
  `verse_text` text NOT NULL,
  `reference` varchar(255) NOT NULL,
  `book` varchar(100) DEFAULT NULL,
  `chapter` int(11) DEFAULT NULL,
  `verse_number` int(11) DEFAULT NULL,
  `is_daily` tinyint(1) NOT NULL DEFAULT 0,
  `is_weekly` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bible_verses`
--

INSERT INTO `bible_verses` (`id`, `verse_text`, `reference`, `book`, `chapter`, `verse_number`, `is_daily`, `is_weekly`, `created_at`) VALUES
(1, 'For I know the plans I have for you, declares the LORD, plans to prosper you and not to harm you, plans to give you hope and a future.', 'Jeremiah 29:11', 'Jeremiah', 29, 11, 1, 0, '2026-07-23 05:07:34'),
(2, 'Trust in the LORD with all your heart and lean not on your own understanding; in all your ways submit to him, and he will make your paths straight.', 'Proverbs 3:5-6', 'Proverbs', 3, 5, 0, 1, '2026-07-23 05:07:34'),
(3, 'The LORD is my shepherd, I lack nothing. He makes me lie down in green pastures, he leads me beside quiet waters, he refreshes my soul.', 'Psalm 23:1-3', 'Psalms', 23, 1, 1, 0, '2026-07-23 05:07:34'),
(4, 'But those who hope in the LORD will renew their strength. They will soar on wings like eagles; they will run and not grow weary, they will walk and not be faint.', 'Isaiah 40:31', 'Isaiah', 40, 31, 0, 1, '2026-07-23 05:07:34'),
(5, 'Be strong and courageous. Do not be afraid; do not be discouraged, for the LORD your God will be with you wherever you go.', 'Joshua 1:9', 'Joshua', 1, 9, 1, 0, '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `pastor_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `city`, `phone`, `email`, `pastor_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Salem Dominion - Head Quarters', 'Plot 45, Kampala Road', 'Kampala', '+256753244480', 'info@salem-dominion-ministries.com', 'Apostle Faty Musasizi', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied','archived') NOT NULL DEFAULT 'unread',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `is_read`, `read_at`, `replied_at`, `reply_message`, `replied_by`, `created_at`) VALUES
(1, 'Grace N.', 'grace.n@example.com', '+256733344555', 'Service Times Inquiry', 'Good morning. I would like to know the service times for Sunday. I am new in the area and looking for a church to attend. Thank you.', 'unread', 0, NULL, NULL, NULL, NULL, '2026-07-23 05:07:34'),
(2, 'Peter M.', 'peter.m@example.com', '+256744455666', 'Volunteer Registration', 'I would like to volunteer in the children ministry. I have experience working with kids and would love to serve. Please get back to me.', 'read', 1, NULL, NULL, NULL, NULL, '2026-07-23 05:07:34'),
(3, 'Linda A.', 'linda.a@example.com', '+256755566777', 'Booking Request', 'I would like to book a meeting with Apostle Faty for spiritual counseling. Please let me know the available dates. God bless.', 'unread', 0, NULL, NULL, NULL, NULL, '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `head_name` varchar(255) DEFAULT NULL,
  `head_email` varchar(255) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `head_name`, `head_email`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Worship & Praise', 'Leading worship and praise sessions during all services and special events.', 'Apostle Faty Musasizi', 'worship@salem-dominion-ministries.com', NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Media & Communications', 'Managing all media production, social media, and church communications.', 'Tech Team', 'media@salem-dominion-ministries.com', NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Finance & Administration', 'Overseeing church finances, budgeting, and administrative operations.', 'Finance Team', 'finance@salem-dominion-ministries.com', NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'Hospitality & Ushering', 'Welcoming and serving all visitors and members during services and events.', 'Hospitality Team', 'hospitality@salem-dominion-ministries.com', NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `donor_phone` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_type` enum('tithe','offering','building_fund','missions','children_ministry','special','general','benevolence','other') NOT NULL DEFAULT 'general',
  `payment_method` enum('mobile_money','bank_transfer','cash','online','card') NOT NULL DEFAULT 'mobile_money',
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','failed','cancelled','rejected') NOT NULL DEFAULT 'pending',
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `confirmation_code` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_name`, `donor_email`, `donor_phone`, `amount`, `donation_type`, `payment_method`, `transaction_id`, `status`, `confirmed_by`, `confirmed_at`, `payment_reference`, `notes`, `is_anonymous`, `confirmation_code`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, NULL, NULL, 0, 'DON-SDM-2026-001', NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, NULL, NULL, 0, 'DON-SDM-2026-002', NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, NULL, NULL, 0, 'DON-SDM-2026-003', NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `donation_campaigns`
--

CREATE TABLE `donation_campaigns` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `goal` decimal(10,2) NOT NULL,
  `raised` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_campaigns`
--

INSERT INTO `donation_campaigns` (`id`, `title`, `description`, `goal`, `raised`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'New Church Building Fund', 'Help us build a new church to accommodate our growing congregation. Every donation brings us closer to our dream of having a permanent worship center that seats 2,000 people.', 50000000.00, 12500000.00, '2026-01-23', '2028-01-23', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `slug`, `description`, `event_date`, `event_time`, `end_time`, `location`, `venue`, `speaker`, `banner_image`, `max_attendees`, `registration_url`, `is_featured`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Sunday Morning Worship Service', NULL, 'Join us for a powerful time of worship and the Word. Every Sunday we gather to lift the name of Jesus and receive fresh anointing for the week ahead. Come expecting a miracle!', '2026-07-30', '09:00:00', '12:00:00', 'Main Sanctuary', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', NULL, 500, NULL, 1, 'upcoming', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Midweek Prayer Meeting', NULL, 'Experience the presence of God through corporate prayer and intercession. Join us every Wednesday evening as we pray for the church, the nation, and one another. Prayer changes things!', '2026-07-25', '18:30:00', '20:30:00', 'Prayer Hall', 'Salem Dominion Ministries HQ', 'Pastor Faty Musasizi', NULL, 200, NULL, 0, 'upcoming', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Youth Conference 2026', NULL, 'A special conference designed to empower and equip the next generation for kingdom impact. Three days of intense worship, teaching, and ministry. Register now to secure your spot!', '2026-08-22', '10:00:00', '18:00:00', 'Main Sanctuary', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', NULL, 800, NULL, 1, 'upcoming', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'Easter Sunday Celebration', NULL, 'Celebrate the resurrection of our Lord Jesus Christ with us! A special Easter service filled with praise, worship, and a powerful Easter message of hope and new beginnings.', '2026-04-24', '08:00:00', '12:00:00', 'Main Sanctuary', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', NULL, 500, NULL, 1, 'completed', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'Leadership Summit 2025', NULL, 'An equipping session for all ministry leaders and department heads. Topics include effective leadership, team management, and spiritual growth for leaders.', '2026-06-08', '09:00:00', '16:00:00', 'Conference Room', 'Salem Dominion Ministries HQ', 'Apostle Faty Musasizi', NULL, 100, NULL, 0, 'completed', 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_url` text NOT NULL,
  `file_path` text DEFAULT NULL,
  `file_type` enum('image','video','audio') NOT NULL,
  `album_id` int(11) DEFAULT NULL,
  `album` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `dimensions` varchar(50) DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `description`, `file_url`, `file_path`, `file_type`, `album_id`, `album`, `category`, `file_size`, `dimensions`, `status`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'uploads/gallery/worship_1.jpg', 'uploads/gallery/worship_1.jpg', 'image', 1, 'Sunday Services', 'worship', 2048000, NULL, 'published', 1, '2026-07-23 05:07:34', '2026-07-23 06:11:19'),
(2, 'Youth Conference 2025 Opening', 'The opening ceremony of our Youth Conference filled with praise and expectation.', 'uploads/gallery/youth_conference_2025.jpg', 'uploads/gallery/youth_conference_2025.jpg', 'image', 2, 'Youth Conference 2025', 'events', 3145728, NULL, 'published', 1, '2026-07-23 05:07:34', '2026-07-23 06:11:19'),
(3, 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation shared during service.', 'uploads/gallery/testimony_1.mp4', 'uploads/gallery/testimony_1.mp4', 'video', NULL, NULL, 'testimonies', 15728640, NULL, 'published', 1, '2026-07-23 05:07:34', '2026-07-23 06:11:19');

-- --------------------------------------------------------

--
-- Table structure for table `homepage_sections`
--

CREATE TABLE `homepage_sections` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `section_type` varchar(50) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_sections`
--

INSERT INTO `homepage_sections` (`id`, `title`, `content`, `section_type`, `image_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Hero Section', 'Welcome to Salem Dominion Ministries. A place of divine encounter, spiritual growth, and kingdom impact. Join us and experience the transformative power of God.', 'hero', NULL, 1, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'About Us', 'Salem Dominion Ministries is a Bible-believing, Spirit-filled church committed to raising a generation of believers who are on fire for God. Founded by Apostle Faty Musasizi, our ministry has grown to become a beacon of hope and transformation in our community and beyond.', 'about', NULL, 2, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Our Mission', 'To reach the lost, disciple the found, and equip believers for kingdom service through the power of the Holy Spirit and the uncompromised Word of God.', 'mission', NULL, 3, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'Our Vision', 'To be a global ministry that raises sons and daughters of God who are impact-makers in their families, communities, and nations for the glory of God.', 'vision', NULL, 4, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'Service Times', 'Sunday Morning Service: 9:00 AM - 12:00 PM | Midweek Prayer: Wednesday 6:30 PM - 8:30 PM | Youth Service: Friday 6:00 PM - 8:00 PM', 'services', NULL, 5, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(6, 'Call to Action', 'Are you looking for a church home? Salem Dominion Ministries welcomes you with open arms. Join us this Sunday and experience God like never before!', 'cta', NULL, 6, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(7, 'Contact Info', 'Visit us at Plot 45, Kampala Road, Kampala, Uganda. Call us at +256753244480 or email info@salem-dominion-ministries.com. We would love to hear from you!', 'contact', NULL, 7, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `leadership`
--

CREATE TABLE `leadership` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `order_position` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leadership`
--

INSERT INTO `leadership` (`id`, `name`, `title`, `bio`, `email`, `phone`, `image_url`, `order_position`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Apostle Faty Musasizi', 'Senior Pastor & Founder', 'Apostle Faty Musasizi is the founder and senior pastor of Salem Dominion Ministries. With over 20 years of ministry experience, he has a passion for empowering believers and spreading the Gospel across nations. He is a prophetic voice to this generation, leading with wisdom, compassion, and uncompromising devotion to the Word of God.', 'apostle@salem-dominion-ministries.com', '+256753244480', NULL, 1, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Sarah Johnson', 'Children Ministry Director', 'Sarah has a heart for children and has been leading our children ministry for over 10 years, creating engaging programs that help kids grow in their faith. She holds a degree in Early Childhood Education and brings creativity and dedication to every program.', 'children@salem-dominion-ministries.com', '+256751234567', NULL, 2, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Michael Williams', 'Youth Ministry Leader', 'Michael is passionate about reaching the next generation and leads our youth ministry with creative programs and relevant teaching. His energetic leadership has grown the youth ministry from 30 to over 200 members in just three years.', 'youth@salem-dominion-ministries.com', '+256702345678', NULL, 3, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `content_type` enum('sermon','news','event','gallery') NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `visitor_hash` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `target` varchar(20) DEFAULT '_self',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `label`, `url`, `icon`, `parent_id`, `sort_order`, `is_active`, `target`, `created_at`) VALUES
(1, 'Home', '/', 'fas fa-home', NULL, 1, 1, '_self', '2026-07-23 05:07:34'),
(2, 'About', '/about.php', 'fas fa-church', NULL, 2, 1, '_self', '2026-07-23 05:07:34'),
(3, 'Sermons', '/sermons.php', 'fas fa-book-open', NULL, 3, 1, '_self', '2026-07-23 05:07:34'),
(4, 'Events', '/events.php', 'fas fa-calendar-alt', NULL, 4, 1, '_self', '2026-07-23 05:07:34'),
(5, 'Ministries', '/ministries.php', 'fas fa-hands-helping', NULL, 5, 1, '_self', '2026-07-23 05:07:34'),
(6, 'Gallery', '/gallery.php', 'fas fa-images', NULL, 6, 1, '_self', '2026-07-23 05:07:34'),
(7, 'Contact', '/contact.php', 'fas fa-envelope', NULL, 7, 1, '_self', '2026-07-23 05:07:34'),
(8, 'Donate', '/donate.php', 'fas fa-hand-holding-heart', NULL, 8, 1, '_self', '2026-07-23 05:07:34'),
(9, 'Give', '/donate.php', 'fas fa-donate', NULL, 9, 1, '_self', '2026-07-23 05:07:34'),
(10, 'Live', '/live.php', 'fas fa-broadcast-tower', NULL, 10, 1, '_self', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `ministries`
--

CREATE TABLE `ministries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ministries`
--

INSERT INTO `ministries` (`id`, `name`, `slug`, `description`, `leader_name`, `leader_email`, `leader_phone`, `meeting_day`, `meeting_time`, `meeting_location`, `category`, `image_url`, `is_active`, `sort_order`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Children Ministry', NULL, 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching, worship, and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.com', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Youth Ministry', NULL, 'Empowering young people to discover their purpose and develop a strong foundation in faith through dynamic programs.', 'Michael Williams', 'youth@salem-dominion-ministries.com', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Women Ministry', NULL, 'Supporting and encouraging women in their spiritual journey through fellowship, discipleship, and mentorship.', 'Grace Brown', 'women@salem-dominion-ministries.com', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'Men Ministry', NULL, 'Building strong men of faith who lead their families and communities with biblical principles and integrity.', 'David Davis', 'men@salem-dominion-ministries.com', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'Worship Team', NULL, 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence to dwell.', 'Apostle Faty Musasizi', 'worship@salem-dominion-ministries.com', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `excerpt`, `category`, `tags`, `featured_image`, `views`, `status`, `is_featured`, `scheduled_at`, `author_id`, `created_at`, `updated_at`) VALUES
(1, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach. The project, which has been in the planning phase for over a year, will feature a modern auditorium with seating for 2,000 people, a state-of-the-art sound system, and dedicated spaces for children and youth ministry. We invite every member to be part of this historic milestone through their generous contributions and prayers.', 'Exciting news about our expansion plans to better serve our community and accommodate our growing family.', 'Announcements', 'building,expansion,project', NULL, 0, 'published', 1, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved Apostle and Founder, Faty Musasizi, was honored with the prestigious Community Service Award at the annual Gospel Ministers Summit. The award recognizes his outstanding contributions to community development through spiritual leadership, education support, and humanitarian aid. Pastor Faty dedicated the award to the entire Salem Dominion Ministries family, stating that none of it would have been possible without the support of the congregation.', 'Recognition for our pastor\'s tireless dedication to community service and transformation.', 'Announcements', 'award,pastor,recognition', NULL, 0, 'published', 0, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'Children\'s Ministry Expansion Program', 'We are expanding our children\'s ministry with new programs and facilities designed to better serve our young ones. The expansion includes a new curriculum based on interactive storytelling, worship sessions tailored for different age groups, and an outdoor play area. We are also looking for volunteer teachers who have a heart for children.', 'New programs and facilities to nurture our children in faith and love.', 'Ministry News', 'children,expansion,ministry', NULL, 0, 'draft', 0, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'Annual Prayer and Fasting Week Announced', 'Join us for our annual week of prayer and fasting scheduled for next month. This spiritual exercise has been a cornerstone of our ministry for years, and we have seen countless breakthroughs as a result. The theme for this year is \"Breaking Every Chain\" and will include nightly prayer sessions, worship, and prophetic declarations.', 'Our annual spiritual retreat to seek God\'s face for breakthroughs and transformation.', 'Announcements', 'prayer,fasting,spiritual', NULL, 0, 'draft', 0, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'Youth Conference 2025 Recap', 'Our Youth Conference 2025 was a tremendous success with over 500 young people in attendance. The three-day event featured powerful worship, insightful teachings, and transformative workshops. Guest speakers included Apostle Faty Musasizi and several other anointed men of God. Many young people made commitments to serve God and their communities.', 'A look back at the impactful Youth Conference that empowered the next generation.', 'Events Recap', 'youth,conference,2025', NULL, 0, 'archived', 0, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('active','unsubscribed','bounced') NOT NULL DEFAULT 'active',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `name`, `phone`, `is_active`, `status`, `subscribed_at`, `unsubscribed_at`, `created_at`) VALUES
(1, 'subscriber1@example.com', 'Mary W.', NULL, 1, 'active', '2026-07-23 05:07:34', NULL, '2026-07-23 05:07:34'),
(2, 'subscriber2@example.com', 'Joseph K.', NULL, 1, 'active', '2026-07-23 05:07:34', NULL, '2026-07-23 05:07:34'),
(3, 'subscriber3@example.com', 'Ruth N.', NULL, 1, 'active', '2026-07-23 05:07:34', NULL, '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `news_categories`
--

CREATE TABLE `news_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news_categories`
--

INSERT INTO `news_categories` (`id`, `name`, `slug`, `description`, `parent_id`, `sort_order`, `is_active`) VALUES
(1, 'Announcements', 'announcements', 'Official church announcements and updates', NULL, 1, 1),
(2, 'Ministry News', 'ministry-news', 'News from various church ministries', NULL, 2, 1),
(3, 'Testimonies', 'testimonies', 'Stories of faith and transformation', NULL, 3, 1),
(4, 'Events Recap', 'events-recap', 'Recaps of past church events', NULL, 4, 1),
(5, 'Community', 'community', 'Community outreach and social impact', NULL, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE `page_content` (
  `id` int(11) NOT NULL,
  `page_slug` varchar(255) NOT NULL,
  `section_key` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `content_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_content`
--

INSERT INTO `page_content` (`id`, `page_slug`, `section_key`, `content`, `content_type`, `created_at`, `updated_at`) VALUES
(1, 'about', 'history', 'Salem Dominion Ministries was founded by Apostle Faty Musasizi with a vision to raise a generation of believers who are on fire for God. What started as a small fellowship has grown into a vibrant church community impacting lives across Uganda and beyond.', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'about', 'mission', 'To reach the lost, disciple the found, and equip believers for kingdom service through the power of the Holy Spirit and the uncompromised Word of God.', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'about', 'vision', 'To be a global ministry that raises sons and daughters of God who are impact-makers in their families, communities, and nations for the glory of God.', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'about', 'core_values', 'Prayer, Holiness, Excellence, Integrity, Unity, Discipleship, Evangelism', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'home', 'welcome_message', 'Welcome to Salem Dominion Ministries! We are a Bible-believing, Spirit-filled church committed to raising a generation of believers who are on fire for God. Whether you are visiting for the first time or looking for a church home, you belong here.', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(6, 'home', 'service_announcement', 'Join us every Sunday from 9:00 AM to 12:00 PM for our main worship service. Midweek services every Wednesday at 6:30 PM.', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(7, 'home', 'cta_text', 'Experience God like never before. Visit us this Sunday!', 'text', '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `pastor_bookings`
--

CREATE TABLE `pastor_bookings` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pastor_booking_availability`
--

CREATE TABLE `pastor_booking_availability` (
  `id` int(11) NOT NULL,
  `pastor_id` int(11) NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `booking_duration_minutes` int(11) NOT NULL DEFAULT 30,
  `max_bookings_per_day` int(11) NOT NULL DEFAULT 8,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pastor_booking_availability`
--

INSERT INTO `pastor_booking_availability` (`id`, `pastor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `booking_duration_minutes`, `max_bookings_per_day`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'monday', '09:00:00', '18:00:00', 1, 30, 8, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 2, 'tuesday', '09:00:00', '18:00:00', 1, 30, 8, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 2, 'wednesday', '09:00:00', '15:00:00', 1, 30, 8, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 2, 'thursday', '09:00:00', '18:00:00', 1, 30, 8, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 2, 'friday', '09:00:00', '15:00:00', 1, 30, 8, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `prayer_requests`
--

CREATE TABLE `prayer_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `request_text` text NOT NULL,
  `is_urgent` tinyint(1) NOT NULL DEFAULT 0,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','answered','archived','praying') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prayer_requests`
--

INSERT INTO `prayer_requests` (`id`, `name`, `email`, `phone`, `request_text`, `is_urgent`, `is_anonymous`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sarah K.', 'sarah.k@example.com', '+256711122333', 'Please pray for my mother who is in the hospital. She has been diagnosed with a serious illness and we need God\'s healing touch. We believe in the power of prayer.', 1, 0, 'pending', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Anonymous', NULL, NULL, 'Pray for my business. I have been struggling financially for months and need God\'s intervention and divine provision.', 0, 1, 'pending', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'David O.', 'david.o@example.com', '+256722233444', 'Thank you church for your prayers last month. My wife has recovered fully and we give God all the glory! Please continue to pray for our family.', 0, 0, 'answered', '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_applications`
--

CREATE TABLE `prophetic_school_applications` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `program` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo`
--

CREATE TABLE `seo` (
  `id` int(11) NOT NULL,
  `page_slug` varchar(255) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `og_title` varchar(255) DEFAULT NULL,
  `og_description` text DEFAULT NULL,
  `og_image` text DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seo`
--

INSERT INTO `seo` (`id`, `page_slug`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`, `og_image`, `canonical_url`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Salem Dominion Ministries - Home', 'Welcome to Salem Dominion Ministries. A place of divine encounter, spiritual growth, and kingdom impact.', 'church, worship, dominion, salem, ministry, kampala, uganda', 'Salem Dominion Ministries', 'A place of divine encounter and spiritual growth', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'about', 'About Us - Salem Dominion Ministries', 'Learn about Salem Dominion Ministries, our mission, vision, and the story of our ministry.', 'about, mission, vision, church history', 'About Salem Dominion Ministries', 'Our mission and vision', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'sermons', 'Sermons - Salem Dominion Ministries', 'Watch and listen to life-changing sermons from Apostle Faty Musasizi and our ministry team.', 'sermons, preaching, teaching, word of god', 'Our Sermons', 'Life-changing sermons and teachings', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'events', 'Events - Salem Dominion Ministries', 'Discover upcoming events, conferences, and gatherings at Salem Dominion Ministries.', 'events, conferences, gatherings, church events', 'Church Events', 'Upcoming events and conferences', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'donate', 'Donate - Salem Dominion Ministries', 'Support the work of God through your generous giving. Give online via mobile money, bank transfer, or card.', 'donate, giving, tithe, offering, giving to church', 'Give to Salem Dominion Ministries', 'Support our ministry through your generous giving', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(6, 'contact', 'Contact Us - Salem Dominion Ministries', 'Get in touch with Salem Dominion Ministries. Visit us, call us, or send us a message.', 'contact, location, phone, email, address', 'Contact Salem Dominion Ministries', 'Reach out to us', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(7, 'ministries', 'Ministries - Salem Dominion Ministries', 'Explore our various ministries designed to serve and empower every member of the family.', 'ministries, youth, children, women, men, worship', 'Our Ministries', 'Ministries for every member', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(8, 'gallery', 'Gallery - Salem Dominion Ministries', 'Browse photos and videos from our services, events, and ministry activities.', 'gallery, photos, videos, church media', 'Photo Gallery', 'Moments from our services and events', NULL, NULL, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `sermons`
--

CREATE TABLE `sermons` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `preacher` varchar(255) DEFAULT NULL,
  `sermon_date` date NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `series` varchar(255) DEFAULT NULL,
  `media_type` enum('video','audio','youtube','podcast') NOT NULL DEFAULT 'video',
  `media_url` text DEFAULT NULL,
  `audio_url` text DEFAULT NULL,
  `pdf_url` text DEFAULT NULL,
  `scripture` varchar(255) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `thumbnail` text DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'published',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sermons`
--

INSERT INTO `sermons` (`id`, `title`, `description`, `preacher`, `sermon_date`, `category`, `series`, `media_type`, `media_url`, `audio_url`, `pdf_url`, `scripture`, `duration`, `thumbnail`, `status`, `is_featured`, `views`, `uploaded_by`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God. Learn to activate your faith for miracles.', 'Apostle Faty Musasizi', '2026-07-16', 'Faith', 'Walking in Power', 'video', 'https://www.youtube.com/watch?v=example1', NULL, NULL, 'Hebrews 11:1', '2840', NULL, 'published', 1, 1250, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application. Every believer has a purpose - find yours!', 'Apostle Faty Musasizi', '2026-07-09', 'Purpose', 'Walking in Power', 'video', 'https://www.youtube.com/watch?v=example2', NULL, NULL, 'Jeremiah 29:11', '3120', NULL, 'published', 1, 980, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life. Obedience is better than sacrifice.', 'Pastor Faty Musasizi', '2026-07-02', 'Obedience', NULL, 'audio', 'https://audio.salem-dominion-ministries.com/sermon3.mp3', NULL, NULL, 'Deuteronomy 28:1-2', '2640', NULL, 'published', 0, 756, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'Spiritual Warfare: Winning the Battle', 'An in-depth teaching on spiritual warfare and the armor of God. Learn how to stand firm against the enemy\'s tactics and claim your victory in Christ.', 'Apostle Faty Musasizi', '2026-06-25', 'Spiritual Warfare', 'Battle Ready', 'video', 'https://www.youtube.com/watch?v=example4', NULL, NULL, 'Ephesians 6:10-18', '3600', NULL, 'published', 0, 1420, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'The Heart of Worship', 'What does it truly mean to worship God in spirit and truth? This sermon explores the essence of genuine worship and how it transforms our relationship with God.', 'Apostle Faty Musasizi', '2026-06-18', 'Worship', NULL, 'video', 'https://www.youtube.com/watch?v=example5', NULL, NULL, 'John 4:23-24', '2400', NULL, 'published', 0, 890, NULL, 1, '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'church_name', 'Salem Dominion Ministries', 'general', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(2, 'church_phone', '+256753244480', 'contact', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(3, 'church_email', 'info@salem-dominion-ministries.com', 'contact', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(4, 'church_address', 'Plot 45, Kampala Road, Kampala, Uganda', 'contact', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(5, 'church_city', 'Kampala', 'contact', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(6, 'church_country', 'Uganda', 'contact', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(7, 'church_website', 'https://salem-dominion-ministries.com', 'general', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(8, 'church_youtube', 'https://www.youtube.com/@salem-dominion-ministries', 'social', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(9, 'church_facebook', 'https://www.facebook.com/salemdominionministries', 'social', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(10, 'church_instagram', 'https://www.instagram.com/salemdominionministries', 'social', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(11, 'church_twitter', 'https://twitter.com/salemdommin', 'social', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(12, 'church_whatsapp', '+256753244480', 'social', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(13, 'service_sunday_time', '09:00 AM - 12:00 PM', 'services', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(14, 'service_wednesday_time', '06:30 PM - 08:30 PM', 'services', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(15, 'service_friday_time', '06:00 PM - 08:00 PM', 'services', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(16, 'currency', 'UGX', 'donations', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(17, 'mobile_money_number', '+256753244480', 'donations', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(18, 'bank_name', 'Centenary Bank', 'donations', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(19, 'bank_account_name', 'Salem Dominion Ministries', 'donations', '2026-07-23 05:07:34', '2026-07-23 05:07:34'),
(20, 'bank_account_number', '1234567890', 'donations', '2026-07-23 05:07:34', '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE `shares` (
  `id` int(11) NOT NULL,
  `content_type` enum('sermon','news','event','gallery') NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `share_platform` varchar(50) DEFAULT 'link',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `occupation` varchar(255) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('pending','approved','rejected','archived') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `email`, `occupation`, `testimonial`, `rating`, `is_approved`, `is_featured`, `status`, `submitted_at`, `approved_at`, `approved_by`, `created_at`) VALUES
(1, 'John Doe', 'john.doe@example.com', 'Business Owner', 'Salem Dominion Ministries has truly transformed my life. The teachings are powerful, the worship is heavenly, and the community is amazing. I came as a visitor and found my spiritual home. God bless Apostle Faty and the entire leadership.', 5, 1, 1, 'approved', '2026-07-23 05:07:34', NULL, 1, '2026-07-23 05:07:34'),
(2, 'Jane Smith', 'jane.smith@example.com', 'Teacher', 'I found my spiritual home here. The worship is uplifting and the messages are life-changing. Since joining Salem Dominion, my faith has grown immensely and I have experienced God\'s faithfulness in my career and family.', 5, 1, 1, 'approved', '2026-07-23 05:07:34', NULL, 1, '2026-07-23 05:07:34'),
(3, 'Michael Johnson', 'michael.j@example.com', 'Student', 'The youth ministry helped me discover my purpose in God. Through the mentorship and programs, I have grown from a confused young man to a confident servant of God. I am forever grateful to Salem Dominion Ministries.', 4, 1, 0, 'approved', '2026-07-23 05:07:34', NULL, 1, '2026-07-23 05:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `youtube_live`
--

CREATE TABLE `youtube_live` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `youtube_url` text NOT NULL,
  `embed_url` text DEFAULT NULL,
  `is_live` tinyint(1) NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `youtube_live`
--

INSERT INTO `youtube_live` (`id`, `title`, `youtube_url`, `embed_url`, `is_live`, `is_enabled`, `started_at`, `ended_at`, `created_by`, `updated_at`) VALUES
(1, 'Sunday Live Service', 'https://www.youtube.com/@salem-dominion-ministries/live', 'https://www.youtube.com/embed/?listType=live&list=channel', 0, 1, NULL, NULL, 1, '2026-07-23 05:07:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_pinned` (`is_pinned`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `bible_verses`
--
ALTER TABLE `bible_verses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reference` (`reference`),
  ADD KEY `idx_book` (`book`),
  ADD KEY `idx_is_daily` (`is_daily`),
  ADD KEY `idx_is_weekly` (`is_weekly`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_city` (`city`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content` (`content_type`,`content_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_donor_name` (`donor_name`),
  ADD KEY `idx_donor_email` (`donor_email`),
  ADD KEY `idx_donor_phone` (`donor_phone`),
  ADD KEY `idx_amount` (`amount`),
  ADD KEY `idx_donation_type` (`donation_type`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_confirmation_code` (`confirmation_code`);

--
-- Indexes for table `donation_campaigns`
--
ALTER TABLE `donation_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_featured` (`is_featured`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_type` (`file_type`),
  ADD KEY `idx_album_id` (`album_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_section_type` (`section_type`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `leadership`
--
ALTER TABLE `leadership`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_position` (`order_position`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_like` (`content_type`,`content_id`,`user_id`),
  ADD UNIQUE KEY `unique_visitor_like` (`content_type`,`content_id`,`visitor_hash`),
  ADD KEY `idx_content` (`content_type`,`content_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `ministries`
--
ALTER TABLE `ministries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_featured` (`is_featured`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`),
  ADD KEY `idx_author_id` (`author_id`),
  ADD KEY `idx_views` (`views`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_subscribed_at` (`subscribed_at`);

--
-- Indexes for table `news_categories`
--
ALTER TABLE `news_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_sort_order` (`sort_order`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `page_content`
--
ALTER TABLE `page_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_slug` (`page_slug`),
  ADD KEY `idx_section_key` (`section_key`);

--
-- Indexes for table `pastor_bookings`
--
ALTER TABLE `pastor_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pastor_id` (`pastor_id`),
  ADD KEY `idx_client_email` (`client_email`),
  ADD KEY `idx_booking_date` (`booking_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_confirmation_code` (`confirmation_code`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `pastor_booking_availability`
--
ALTER TABLE `pastor_booking_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pastor_id` (`pastor_id`),
  ADD KEY `idx_day_of_week` (`day_of_week`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_urgent` (`is_urgent`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `prophetic_school_applications`
--
ALTER TABLE `prophetic_school_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `seo`
--
ALTER TABLE `seo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_slug` (`page_slug`);

--
-- Indexes for table `sermons`
--
ALTER TABLE `sermons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sermon_date` (`sermon_date`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_series` (`series`),
  ADD KEY `idx_media_type` (`media_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_featured` (`is_featured`),
  ADD KEY `idx_views` (`views`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_group` (`setting_group`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content` (`content_type`,`content_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_platform` (`share_platform`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_approved` (`is_approved`),
  ADD KEY `idx_is_featured` (`is_featured`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `youtube_live`
--
ALTER TABLE `youtube_live`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_live` (`is_live`),
  ADD KEY `idx_is_enabled` (`is_enabled`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bible_verses`
--
ALTER TABLE `bible_verses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donation_campaigns`
--
ALTER TABLE `donation_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leadership`
--
ALTER TABLE `leadership`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ministries`
--
ALTER TABLE `ministries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news_categories`
--
ALTER TABLE `news_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `page_content`
--
ALTER TABLE `page_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pastor_bookings`
--
ALTER TABLE `pastor_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pastor_booking_availability`
--
ALTER TABLE `pastor_booking_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `prophetic_school_applications`
--
ALTER TABLE `prophetic_school_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seo`
--
ALTER TABLE `seo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sermons`
--
ALTER TABLE `sermons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `shares`
--
ALTER TABLE `shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `youtube_live`
--
ALTER TABLE `youtube_live`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
