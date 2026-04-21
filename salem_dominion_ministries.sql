-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 04:18 AM
-- Server version: 8.0.45
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `salem_dominion_ministries`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('success','failed','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'failed',
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'max_login_attempts', '5', 'Maximum number of failed login attempts before account lockout', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(2, 'account_lockout_duration', '1800', 'Account lockout duration in seconds (30 minutes)', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(3, 'session_timeout', '3600', 'Session timeout duration in seconds (1 hour)', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(4, 'require_password_change', '0', 'Require password change on first login (0 = no, 1 = yes)', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(5, 'min_password_length', '8', 'Minimum password length requirement', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(6, 'enable_two_factor', '0', 'Enable two-factor authentication (0 = no, 1 = yes)', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(7, 'maintenance_mode', '0', 'Maintenance mode (0 = off, 1 = on)', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(8, 'site_title', 'Salem Dominion Ministries Admin', 'Site title for admin panel', '2026-04-10 13:34:08', '2026-04-10 15:34:42'),
(9, 'admin_email', 'pastor@salem-dominion-ministries.org', 'Admin contact email', '2026-04-10 13:34:08', '2026-04-10 15:34:42');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `email`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$pDivjdJhKTHGLNN59.AdtODwVHPkRpPcd8i9SFnT3IUfPRJLMuZcK', 'admin@salem-dominion-ministries.org', 'Administrator', 'admin', 1, NULL, '2026-04-20 02:08:49'),
(2, 'MusasiziFaty', '$2y$10$jN4.4BvehImr/.qJdxSFBuOcx12JAW51H3Kr11E4g4sIivzdVD.J.', 'pastor@salem-dominion-ministries.com', 'Pastor Faty Musasizi', 'admin', 1, '2026-04-20 03:41:13', '2026-04-20 02:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users_backup`
--

CREATE TABLE `admin_users_backup` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','pastor','super_admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users_backup`
--

INSERT INTO `admin_users_backup` (`id`, `username`, `password_hash`, `password`, `full_name`, `email`, `role`, `is_active`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'MusasiziFaty', 'Musasizi123', 'Musasizi123', 'Pastor Faty Musasizi', 'pastor@salem-dominion-ministries.org', 'pastor', 1, '2026-04-18 22:14:32', 0, NULL, '2026-04-10 13:34:06', '2026-04-19 06:34:04'),
(13, 'admin', '$2y$10$KkWHytnEvKH6ewaKiBJMi.bkW..B7yS6.OdXi8TQlpjm3Bf209kHe', '', 'Administrator', 'admin@salem-dominion-ministries.com', 'admin', 1, NULL, 0, NULL, '2026-04-11 16:07:19', '2026-04-11 16:07:19');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `content_type` enum('sermon','news','gallery') COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `author_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_type` enum('sermon','news','gallery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sermon',
  `post_id` int NOT NULL DEFAULT '0',
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content_type`, `content_id`, `user_id`, `parent_id`, `author_name`, `author_email`, `comment_text`, `post_type`, `post_id`, `is_approved`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'sermon', 1, 2, NULL, NULL, NULL, 'This sermon really blessed my heart! Thank you Pastor.', 'sermon', 0, 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(2, 'sermon', 1, 3, NULL, NULL, NULL, 'Powerful message that I needed to hear today.', 'sermon', 0, 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(3, 'sermon', 2, 4, NULL, NULL, NULL, 'The teaching on purpose was exactly what I was looking for.', 'sermon', 0, 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(4, 'news', 1, 2, NULL, NULL, NULL, 'So excited about the new building project! God is good.', 'sermon', 0, 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(5, 'gallery', 1, 3, NULL, NULL, NULL, 'Beautiful worship moments captured here.', 'sermon', 0, 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(6, 'sermon', 1, 2, NULL, NULL, NULL, 'This sermon really blessed my heart! Thank you Pastor.', 'sermon', 0, 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(7, 'sermon', 1, 3, NULL, NULL, NULL, 'Powerful message that I needed to hear today.', 'sermon', 0, 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(8, 'sermon', 2, 4, NULL, NULL, NULL, 'The teaching on purpose was exactly what I was looking for.', 'sermon', 0, 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(9, 'news', 1, 2, NULL, NULL, NULL, 'So excited about the new building project! God is good.', 'sermon', 0, 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(10, 'gallery', 1, 3, NULL, NULL, NULL, 'Beautiful worship moments captured here.', 'sermon', 0, 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(11, 'sermon', 1, 2, NULL, NULL, NULL, 'This sermon really blessed my heart! Thank you Pastor.', 'sermon', 0, 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(12, 'sermon', 1, 3, NULL, NULL, NULL, 'Powerful message that I needed to hear today.', 'sermon', 0, 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(13, 'sermon', 2, 4, NULL, NULL, NULL, 'The teaching on purpose was exactly what I was looking for.', 'sermon', 0, 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(14, 'news', 1, 2, NULL, NULL, NULL, 'So excited about the new building project! God is good.', 'sermon', 0, 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(15, 'gallery', 1, 3, NULL, NULL, NULL, 'Beautiful worship moments captured here.', 'sermon', 0, 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(16, 'sermon', 1, 2, NULL, NULL, NULL, 'This sermon really blessed my heart! Thank you Pastor.', 'sermon', 0, 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(17, 'sermon', 1, 3, NULL, NULL, NULL, 'Powerful message that I needed to hear today.', 'sermon', 0, 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(18, 'sermon', 2, 4, NULL, NULL, NULL, 'The teaching on purpose was exactly what I was looking for.', 'sermon', 0, 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(19, 'news', 1, 2, NULL, NULL, NULL, 'So excited about the new building project! God is good.', 'sermon', 0, 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(20, 'gallery', 1, 3, NULL, NULL, NULL, 'Beautiful worship moments captured here.', 'sermon', 0, 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(21, 'sermon', 1, 2, NULL, NULL, NULL, 'This sermon really blessed my heart! Thank you Pastor.', 'sermon', 0, 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(22, 'sermon', 1, 3, NULL, NULL, NULL, 'Powerful message that I needed to hear today.', 'sermon', 0, 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(23, 'sermon', 2, 4, NULL, NULL, NULL, 'The teaching on purpose was exactly what I was looking for.', 'sermon', 0, 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(24, 'news', 1, 2, NULL, NULL, NULL, 'So excited about the new building project! God is good.', 'sermon', 0, 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(25, 'gallery', 1, 3, NULL, NULL, NULL, 'Beautiful worship moments captured here.', 'sermon', 0, 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int NOT NULL,
  `donor_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `donor_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `donor_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `donation_type` enum('tithe','offering','building_fund','missions','children_ministry','special','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `payment_method` enum('mobile_money','bank_transfer','cash','online','card') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mobile_money',
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','confirmed','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_sent` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_message_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmation_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_name`, `donor_email`, `donor_phone`, `amount`, `donation_type`, `payment_method`, `transaction_id`, `status`, `payment_reference`, `notes`, `is_anonymous`, `whatsapp_sent`, `whatsapp_message_id`, `confirmation_code`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(2, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(3, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(4, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(5, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(6, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(7, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(8, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(9, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(10, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(11, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(12, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(13, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(14, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(15, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(16, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(17, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(18, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(19, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(20, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(21, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(22, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(23, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(24, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(25, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(26, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(27, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(28, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(29, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(30, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(31, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(32, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(33, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(34, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(35, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(36, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(37, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(38, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(39, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(40, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(41, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(42, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(43, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(44, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(45, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(46, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(47, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(48, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(49, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(50, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(51, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(52, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(53, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(54, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(55, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(56, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(57, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(58, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(59, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(60, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(61, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(62, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(63, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(64, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(65, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(66, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(67, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(68, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(69, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(70, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(71, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(72, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(73, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(74, 'Sarah Williams', 'sarah.w@example.com', '+256753456789', 15000.00, 'missions', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024004', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(75, 'David Brown', 'david.brown@example.com', '+256704567890', 75000.00, 'children_ministry', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024005', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(76, 'Grace Wilson', 'grace.wilson@example.com', '+256705678901', 30000.00, 'offering', 'cash', NULL, 'completed', NULL, NULL, 0, 0, NULL, 'DON2024006', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(77, 'Robert Taylor', 'robert.taylor@example.com', '+256706789012', 20000.00, 'tithe', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, 'DON2024007', NULL, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(78, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 14:56:26', '2026-04-10 14:56:26'),
(79, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 14:56:26', '2026-04-10 14:56:26'),
(80, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 14:56:26', '2026-04-10 14:56:26'),
(81, 'John Doe', 'john.doe@example.com', '+256700123456', 50000.00, 'tithe', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024001', NULL, NULL, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(82, 'Jane Smith', 'jane.smith@example.com', '+256751234567', 25000.00, 'offering', 'mobile_money', NULL, 'completed', NULL, NULL, 0, 1, NULL, 'DON2024002', NULL, NULL, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(83, 'Michael Johnson', 'michael.j@example.com', '+256702345678', 100000.00, 'building_fund', 'bank_transfer', NULL, 'confirmed', NULL, NULL, 0, 0, NULL, 'DON2024003', NULL, NULL, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(84, 'Reagan Otema', 'reaganotema2022@gmail.com', '+256772514889', 1000.00, 'special', 'mobile_money', NULL, 'pending', NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, '2026-04-11 12:25:47', '2026-04-11 12:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `donation_campaigns`
--

CREATE TABLE `donation_campaigns` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `campaign_type` enum('general','building','missions','special','emergency') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `goal_amount` decimal(10,2) DEFAULT NULL,
  `current_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `featured_image` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_campaigns`
--

INSERT INTO `donation_campaigns` (`id`, `title`, `description`, `campaign_type`, `goal_amount`, `current_amount`, `start_date`, `end_date`, `is_active`, `featured_image`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(2, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(3, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:34:14', '2026-04-10 13:34:14'),
(4, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(5, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(6, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(7, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(8, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(9, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(10, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(11, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(12, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:45:07', '2026-04-10 13:45:07'),
(13, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(14, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(15, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(16, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(17, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(18, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(19, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(20, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(21, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:54:09', '2026-04-10 13:54:09'),
(22, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(23, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(24, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(25, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(26, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(27, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(28, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(29, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(30, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 14:03:24', '2026-04-10 14:03:24'),
(31, 'Church Building Fund', 'Help us build our new sanctuary to accommodate our growing congregation.', 'building', 50000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(32, 'Missions Support', 'Support our missionary work and outreach programs.', 'missions', 10000000.00, 0.00, '2024-01-01', '2024-12-31', 1, NULL, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(33, 'Youth Ministry', 'Support our youth programs and conferences.', 'general', 5000000.00, 0.00, '2024-01-01', '2024-06-30', 1, NULL, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled','deleted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(2, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(4, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(5, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(6, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(7, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(8, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(9, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(10, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(11, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(12, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(13, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(14, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(16, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(17, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(18, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(19, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(20, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(21, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(22, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(23, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(24, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(25, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(26, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(27, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(28, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(29, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(30, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(31, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(32, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(33, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(34, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 14:56:05', '2026-04-10 14:56:05'),
(35, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 14:56:05', '2026-04-10 14:56:05'),
(36, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 14:56:05', '2026-04-10 14:56:05'),
(37, 'Sunday Morning Service', 'Join us for powerful worship and life-changing messages every Sunday morning.', '2024-02-04', '09:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(38, 'Midweek Prayer Meeting', 'Experience the presence of God through corporate prayer and intercession.', '2024-02-07', '18:30:00', 'Prayer Hall', 'upcoming', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(39, 'Youth Conference 2024', 'A special conference designed to empower and equip the next generation for kingdom impact.', '2024-02-10', '10:00:00', 'Main Sanctuary', 'upcoming', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(40, 'praisennote', 'love of people', '2026-04-12', '17:16:00', 'iganga', 'upcoming', 1, '2026-04-11 10:14:47', '2026-04-11 10:14:47');

-- --------------------------------------------------------

--
-- Table structure for table `event_attendees`
--

CREATE TABLE `event_attendees` (
  `id` int NOT NULL,
  `registration_id` int DEFAULT NULL,
  `event_id` int NOT NULL,
  `attendee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attendee_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendee_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_feedback`
--

CREATE TABLE `event_feedback` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `registration_id` int DEFAULT NULL,
  `attendee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attendee_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `feedback_text` text COLLATE utf8mb4_unicode_ci,
  `suggestions` text COLLATE utf8mb4_unicode_ci,
  `would_recommend` tinyint(1) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age_group` enum('child','youth','young_adult','adult','senior') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `church_affiliation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `special_needs` text COLLATE utf8mb4_unicode_ci,
  `registration_type` enum('individual','family','group') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'individual',
  `group_size` int DEFAULT '1',
  `status` enum('registered','confirmed','cancelled','attended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `confirmation_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `first_name`, `last_name`, `email`, `phone`, `age_group`, `church_affiliation`, `special_needs`, `registration_type`, `group_size`, `status`, `confirmation_code`, `notes`, `ip_address`, `registered_at`, `updated_at`) VALUES
(1, 1, 'John', 'Doe', 'john.doe@example.com', '+256700123456', 'adult', NULL, NULL, 'individual', 1, 'registered', 'REG2024001', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 14:08:57'),
(2, 1, 'Jane', 'Smith', 'jane.smith@example.com', '+256751234567', 'young_adult', NULL, NULL, 'individual', 1, 'registered', 'REG2024002', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 14:08:57'),
(3, 2, 'Michael', 'Johnson', 'michael.j@example.com', '+256702345678', 'youth', NULL, NULL, 'group', 1, 'registered', 'REG2024003', NULL, NULL, '2026-04-10 13:34:14', '2026-04-10 14:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `event_reminders`
--

CREATE TABLE `event_reminders` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `registration_id` int NOT NULL,
  `reminder_type` enum('confirmation','reminder','follow_up','cancellation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reminder',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `send_via` enum('email','sms','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int NOT NULL,
  `uploaded_by` int NOT NULL,
  `file_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` enum('image','video','audio') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `dimensions` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('published','draft','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `uploaded_by`, `file_url`, `title`, `description`, `file_type`, `category`, `file_size`, `dimensions`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(2, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(3, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(4, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(5, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(6, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(7, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(8, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(9, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(10, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(11, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(12, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(13, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(14, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(15, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(16, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(17, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(18, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(19, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(20, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(21, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(22, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(23, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(24, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(25, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(26, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(27, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(28, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(29, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(30, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(31, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(32, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(33, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(34, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(35, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(36, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(37, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(38, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(39, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(40, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(41, 1, 'uploads/gallery/image/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(42, 1, 'uploads/gallery/image/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(43, 1, 'uploads/gallery/video/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(44, 1, 'uploads/gallery/audio/worship_audio_1.mp3', 'Sunday Worship Audio', 'Audio recording of our Sunday morning worship service.', 'audio', 'worship', NULL, NULL, 'published', '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(45, 1, 'uploads/gallery/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 14:56:26', '2026-04-10 14:56:26'),
(46, 1, 'uploads/gallery/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 14:56:26', '2026-04-10 14:56:26'),
(47, 1, 'uploads/gallery/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 14:56:26', '2026-04-10 14:56:26'),
(48, 1, 'uploads/gallery/worship_1.jpg', 'Powerful Worship Session', 'An amazing time of worship as we lift our voices in praise to God.', 'image', 'worship', NULL, NULL, 'published', '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(49, 1, 'uploads/gallery/conference_1.jpg', 'Youth Conference 2024', 'Our youth gathering for spiritual growth and fellowship.', 'image', 'events', NULL, NULL, 'published', '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(50, 1, 'uploads/gallery/testimony_1.mp4', 'Life-Changing Testimony', 'A powerful testimony of God\'s faithfulness and transformation.', 'video', 'testimonies', NULL, NULL, 'published', '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(51, 1, 'uploads/gallery/image/69da1f52ee426_Screenshot 2026-03-31 040038.png', 'power', 'testing', 'image', 'general', NULL, NULL, 'published', '2026-04-11 10:15:46', '2026-04-11 10:15:46');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_bookmarks`
--

CREATE TABLE `gallery_bookmarks` (
  `id` int NOT NULL,
  `gallery_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_comments`
--

CREATE TABLE `gallery_comments` (
  `id` int NOT NULL,
  `gallery_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `author_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery_comments`
--

INSERT INTO `gallery_comments` (`id`, `gallery_id`, `user_id`, `parent_id`, `author_name`, `author_email`, `comment`, `is_approved`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(2, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(3, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(4, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(5, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(6, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(7, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(8, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(9, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(10, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(11, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(12, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(13, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(14, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(15, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(16, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(17, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(18, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(19, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(20, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(21, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(22, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(23, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(24, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(25, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(26, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(27, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(28, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(29, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(30, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(31, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(32, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(33, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(34, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(35, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 13:59:00', '2026-04-10 13:59:00'),
(36, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(37, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(38, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(39, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(40, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(41, 1, 2, NULL, NULL, NULL, 'Amazing worship session! The presence of God was so strong.', 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(42, 1, 4, NULL, NULL, NULL, 'I was there and it was truly blessed!', 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(43, 2, 3, NULL, NULL, NULL, 'Our youth are on fire for God!', 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(44, 2, 5, NULL, NULL, NULL, 'Great conference, looking forward to the next one.', 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(45, 3, 2, NULL, NULL, NULL, 'This testimony encouraged me so much!', 0, NULL, '2026-04-10 14:08:57', '2026-04-10 14:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_reactions`
--

CREATE TABLE `gallery_reactions` (
  `id` int NOT NULL,
  `gallery_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `reaction_type` enum('like','love','blessed','inspired','pray','worship') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'like',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gallery_reactions`
--

INSERT INTO `gallery_reactions` (`id`, `gallery_id`, `user_id`, `reaction_type`, `ip_address`, `created_at`) VALUES
(46, 1, 2, 'love', NULL, '2026-04-10 14:08:57'),
(47, 1, 3, 'blessed', NULL, '2026-04-10 14:08:57'),
(48, 1, 4, 'inspired', NULL, '2026-04-10 14:08:57'),
(49, 2, 2, 'like', NULL, '2026-04-10 14:08:57'),
(50, 2, 5, 'pray', NULL, '2026-04-10 14:08:57'),
(51, 2, 3, 'blessed', NULL, '2026-04-10 14:08:57'),
(52, 3, 2, 'inspired', NULL, '2026-04-10 14:08:57'),
(53, 3, 4, 'worship', NULL, '2026-04-10 14:08:57'),
(54, 3, 5, 'love', NULL, '2026-04-10 14:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_shares`
--

CREATE TABLE `gallery_shares` (
  `id` int NOT NULL,
  `gallery_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `share_type` enum('facebook','twitter','whatsapp','email','link') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leadership`
--

CREATE TABLE `leadership` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` text COLLATE utf8mb4_unicode_ci,
  `order_position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leadership`
--

INSERT INTO `leadership` (`id`, `name`, `title`, `bio`, `email`, `phone`, `image_url`, `order_position`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Apostle Faty Musasizi', 'Senior Pastor & Founder', 'Apostle Faty Musasizi is the founder and senior pastor of Salem Dominion Ministries. With over 20 years of ministry experience, he has a passion for empowering believers and spreading the Gospel.', 'apostle@salem-dominion-ministries.org', '+256753244480', NULL, 1, 1, '2026-04-10 14:56:56', '2026-04-10 14:56:56'),
(2, 'Sarah Johnson', 'Children Ministry Director', 'Sarah has a heart for children and has been leading our children ministry for over 10 years, creating engaging programs that help kids grow in their faith.', 'children@salem-dominion-ministries.org', '+256751234567', NULL, 2, 1, '2026-04-10 14:56:56', '2026-04-10 14:56:56'),
(3, 'Michael Williams', 'Youth Ministry Leader', 'Michael is passionate about reaching the next generation and leads our youth ministry with creative programs and relevant teaching.', 'youth@salem-dominion-ministries.org', '+256702345678', NULL, 3, 1, '2026-04-10 14:56:56', '2026-04-10 14:56:56'),
(4, 'Apostle Faty Musasizi', 'Senior Pastor & Founder', 'Apostle Faty Musasizi is the founder and senior pastor of Salem Dominion Ministries. With over 20 years of ministry experience, he has a passion for empowering believers and spreading the Gospel.', 'apostle@salem-dominion-ministries.org', '+256753244480', NULL, 1, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(5, 'Sarah Johnson', 'Children Ministry Director', 'Sarah has a heart for children and has been leading our children ministry for over 10 years, creating engaging programs that help kids grow in their faith.', 'children@salem-dominion-ministries.org', '+256751234567', NULL, 2, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(6, 'Michael Williams', 'Youth Ministry Leader', 'Michael is passionate about reaching the next generation and leads our youth ministry with creative programs and relevant teaching.', 'youth@salem-dominion-ministries.org', '+256702345678', NULL, 3, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `sender_id` int NOT NULL,
  `recipient_id` int DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_type` enum('user_to_admin','admin_to_user','user_to_user') COLLATE utf8mb4_unicode_ci DEFAULT 'user_to_admin',
  `status` enum('unread','read','replied') COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT 'normal',
  `parent_message_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int NOT NULL,
  `message_id` int NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ministries`
--

CREATE TABLE `ministries` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leader_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leader_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leader_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_day` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_time` time DEFAULT NULL,
  `meeting_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('children','youth','men','women','outreach','worship','prayer','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `image_url` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ministries`
--

INSERT INTO `ministries` (`id`, `name`, `description`, `leader_name`, `leader_email`, `leader_phone`, `meeting_day`, `meeting_time`, `meeting_location`, `category`, `image_url`, `is_active`, `sort_order`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(2, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(3, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(4, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(5, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(6, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(7, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(8, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(9, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(10, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(11, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(12, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(13, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(14, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(15, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(16, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(17, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(18, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(19, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(20, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(21, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(22, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(23, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(24, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(25, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(26, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(27, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(28, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(29, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(30, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(31, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(32, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(33, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(34, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(35, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(36, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(37, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(38, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(39, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(40, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(41, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(42, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(43, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(44, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(45, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(46, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(47, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(48, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(49, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(50, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(51, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(52, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(53, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(54, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(55, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(56, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 14:56:17', '2026-04-10 14:56:17'),
(57, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 14:56:17', '2026-04-10 14:56:17'),
(58, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 14:56:17', '2026-04-10 14:56:17'),
(59, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 14:56:17', '2026-04-10 14:56:17'),
(60, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 14:56:17', '2026-04-10 14:56:17'),
(61, 'Children Ministry', 'Nurturing the next generation in the knowledge and love of God through age-appropriate teaching and activities.', 'Sarah Johnson', 'children@salem-dominion-ministries.org', '+256751234567', 'Sunday', '09:00:00', 'Children\'s Hall', 'children', NULL, 1, 1, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(62, 'Youth Ministry', 'Empowering young people to discover their purpose and develop a strong foundation in faith.', 'Michael Williams', 'youth@salem-dominion-ministries.org', '+256702345678', 'Friday', '18:00:00', 'Youth Center', 'youth', NULL, 1, 2, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(63, 'Women Ministry', 'Supporting and encouraging women in their spiritual journey through fellowship and discipleship.', 'Grace Brown', 'women@salem-dominion-ministries.org', '+256753456789', 'Tuesday', '10:00:00', 'Main Hall', 'women', NULL, 1, 3, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(64, 'Men Ministry', 'Building strong men of faith who lead their families and communities with biblical principles.', 'David Davis', 'men@salem-dominion-ministries.org', '+256704567890', 'Saturday', '07:00:00', 'Prayer Room', 'men', NULL, 1, 4, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(65, 'Worship Team', 'Leading the congregation in powerful worship that creates an atmosphere for God\'s presence.', 'Pastor Faty Musasizi', 'worship@salem-dominion-ministries.org', '+256753244480', 'Thursday', '19:00:00', 'Main Sanctuary', 'worship', NULL, 1, 5, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_image` text COLLATE utf8mb4_unicode_ci,
  `views` int NOT NULL DEFAULT '0',
  `status` enum('published','draft','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `excerpt`, `category`, `featured_image`, `views`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(2, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(3, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(4, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(5, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(6, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(7, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(8, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(9, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(10, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(11, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(12, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(13, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(14, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(15, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(16, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(17, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(18, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(19, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(20, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(21, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(22, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(23, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(24, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(25, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(26, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(27, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(28, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(29, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(30, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(31, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(32, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(33, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(34, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 14:56:16', '2026-04-10 14:56:16'),
(35, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 14:56:16', '2026-04-10 14:56:16'),
(36, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 14:56:16', '2026-04-10 14:56:16'),
(37, 'New Church Building Project Announced', 'We are excited to announce the beginning of our new church building project that will accommodate our growing congregation and expand our ministry outreach.', 'Exciting news about our expansion plans to better serve our community.', 'Announcements', NULL, 0, 'published', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(38, 'Pastor Faty Musasizi Receives Community Service Award', 'Our beloved pastor was honored with the Community Service Award for his outstanding contributions to the community.', 'Recognition for our pastor\'s community impact.', 'Awards', NULL, 0, 'published', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(39, 'Children\'s Ministry Expansion', 'We are expanding our children\'s ministry with new programs and facilities to better serve our young ones.', 'New programs and facilities for our children.', 'Ministry', NULL, 0, 'published', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_type` enum('general','events','sermons','news','all') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unsubscribe_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` datetime DEFAULT NULL,
  `last_sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `newsletter_subscriptions`
--

INSERT INTO `newsletter_subscriptions` (`id`, `email`, `first_name`, `last_name`, `phone`, `subscription_type`, `is_active`, `is_verified`, `verification_token`, `unsubscribe_token`, `preferences`, `ip_address`, `subscribed_at`, `unsubscribed_at`, `last_sent_at`) VALUES
(1, 'john.doe@example.com', 'John', 'Doe', NULL, 'all', 1, 1, NULL, NULL, NULL, NULL, '2026-04-10 13:34:14', NULL, NULL),
(2, 'jane.smith@example.com', 'Jane', 'Smith', NULL, 'events', 1, 1, NULL, NULL, NULL, NULL, '2026-04-10 13:34:14', NULL, NULL),
(3, 'michael.j@example.com', 'Michael', 'Johnson', NULL, 'sermons', 1, 0, NULL, NULL, NULL, NULL, '2026-04-10 13:34:14', NULL, NULL),
(4, 'sarah.w@example.com', 'Sarah', 'Williams', NULL, 'all', 1, 1, NULL, NULL, NULL, NULL, '2026-04-10 13:34:14', NULL, NULL),
(5, 'david.brown@example.com', 'David', 'Brown', NULL, 'news', 1, 1, NULL, NULL, NULL, NULL, '2026-04-10 13:34:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pastor_bookings`
--

CREATE TABLE `pastor_bookings` (
  `id` int NOT NULL,
  `pastor_id` int NOT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_minutes` int NOT NULL DEFAULT '30',
  `booking_type` enum('general','counseling','prayer','deliverance','healing','prophecy','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','cancelled','completed','no_show') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `confirmation_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pastor_booking_availability`
--

CREATE TABLE `pastor_booking_availability` (
  `id` int NOT NULL,
  `pastor_id` int NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `booking_duration_minutes` int NOT NULL DEFAULT '30',
  `max_bookings_per_day` int NOT NULL DEFAULT '8',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pastor_booking_availability`
--

INSERT INTO `pastor_booking_availability` (`id`, `pastor_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `booking_duration_minutes`, `max_bookings_per_day`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'monday', '09:00:00', '18:00:00', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(2, 1, 'tuesday', '09:00:00', '18:00:00', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(3, 1, 'wednesday', '09:00:00', '15:00:00', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(4, 1, 'wednesday', '21:00:00', '23:59:59', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(5, 1, 'thursday', '09:00:00', '18:00:00', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(6, 1, 'friday', '09:00:00', '15:00:00', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(7, 1, 'friday', '21:00:00', '23:59:59', 1, 30, 8, 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_applications`
--

CREATE TABLE `prophetic_school_applications` (
  `id` int NOT NULL,
  `application_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int NOT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nationality` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ministry_background` text COLLATE utf8mb4_unicode_ci,
  `prophetic_experience` text COLLATE utf8mb4_unicode_ci,
  `calling` text COLLATE utf8mb4_unicode_ci,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `payment_method` enum('mobile_money','bank_transfer','cash') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL DEFAULT '100.00',
  `payment_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `payment_status` enum('pending','verified','confirmed','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `application_status` enum('pending','under_review','accepted','rejected','enrolled','graduated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `whatsapp_sent` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_message_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prophetic_school_applications`
--

INSERT INTO `prophetic_school_applications` (`id`, `application_id`, `first_name`, `last_name`, `email`, `phone`, `age`, `gender`, `nationality`, `address`, `ministry_background`, `prophetic_experience`, `calling`, `reason`, `payment_method`, `transaction_id`, `payment_amount`, `payment_currency`, `payment_status`, `application_status`, `whatsapp_sent`, `whatsapp_message_id`, `notes`, `admin_notes`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'PROPH-2024001', 'John', 'Doe', 'john.doe@example.com', '+256700123456', 28, 'male', 'Ugandan', 'Kampala, Uganda', 'Youth ministry leader for 3 years', 'Prophetic dreams and visions', 'Called to prophetic ministry', 'To develop my prophetic gift', 'mobile_money', 'TXN123456789', 100.00, 'USD', 'verified', 'accepted', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(2, 'PROPH-2024002', 'Sarah', 'Johnson', 'sarah.j@example.com', '+256751234567', 32, 'female', 'Kenyan', 'Nairobi, Kenya', 'Worship leader and prayer warrior', 'Words of knowledge and prophecy', 'Prophetic worship ministry', 'To be equipped for ministry', 'bank_transfer', 'BANK987654321', 100.00, 'USD', 'confirmed', 'under_review', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(3, 'PROPH-2024003', 'Michael', 'Williams', 'michael.w@example.com', '+256702345678', 25, 'male', 'Tanzanian', 'Dar es Salaam, Tanzania', 'New believer with passion for God', 'Growing prophetic sensitivity', 'Feel called to prophetic ministry', 'To learn and grow in the gift', 'mobile_money', 'TXN567890123', 100.00, 'USD', 'pending', 'pending', 0, NULL, NULL, NULL, NULL, NULL, '2026-04-10 14:08:59', '2026-04-10 14:08:59');

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_assessments`
--

CREATE TABLE `prophetic_school_assessments` (
  `id` int NOT NULL,
  `enrollment_id` int NOT NULL,
  `course_id` int NOT NULL,
  `assessment_type` enum('assignment','quiz','midterm','final','practical','project') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_score` decimal(5,2) NOT NULL DEFAULT '100.00',
  `score_obtained` decimal(5,2) DEFAULT NULL,
  `grade` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assessment_date` date DEFAULT NULL,
  `status` enum('pending','submitted','graded','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_by` int DEFAULT NULL,
  `graded_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_certificates`
--

CREATE TABLE `prophetic_school_certificates` (
  `id` int NOT NULL,
  `enrollment_id` int NOT NULL,
  `certificate_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate_type` enum('completion','excellence','honor','distinction') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completion',
  `issue_date` date NOT NULL,
  `graduation_date` date NOT NULL,
  `program_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_point_average` decimal(3,2) DEFAULT NULL,
  `special_achievements` text COLLATE utf8mb4_unicode_ci,
  `issued_by` int NOT NULL,
  `certificate_file_path` text COLLATE utf8mb4_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '1',
  `verification_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_courses`
--

CREATE TABLE `prophetic_school_courses` (
  `id` int NOT NULL,
  `program_id` int NOT NULL,
  `course_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_weeks` int DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prophetic_school_courses`
--

INSERT INTO `prophetic_school_courses` (`id`, `program_id`, `course_name`, `course_code`, `description`, `duration_weeks`, `credits`, `is_required`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Introduction to Prophecy', 'PROP-101', 'Biblical foundation of prophetic ministry', 4, 3, 1, 1, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(2, 1, 'Discerning God\'s Voice', 'PROP-102', 'Learning to distinguish God\'s voice from other voices', 4, 3, 1, 1, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(3, 1, 'Prophetic Ethics', 'PROP-103', 'Ethical guidelines for prophetic ministry', 3, 2, 1, 1, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(4, 2, 'Advanced Prophetic Operations', 'PROP-201', 'Deep prophetic ministry techniques', 6, 4, 1, 1, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(5, 2, 'Church and Prophetic Ministry', 'PROP-202', 'Integrating prophecy in church context', 5, 3, 1, 1, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59');

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_documents`
--

CREATE TABLE `prophetic_school_documents` (
  `id` int NOT NULL,
  `application_id` int NOT NULL,
  `document_type` enum('passport_photo','national_id','passport','cv','recommendation_letter','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint DEFAULT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_enrollments`
--

CREATE TABLE `prophetic_school_enrollments` (
  `id` int NOT NULL,
  `application_id` int NOT NULL,
  `program_id` int NOT NULL,
  `enrollment_date` date NOT NULL,
  `graduation_date` date DEFAULT NULL,
  `status` enum('enrolled','active','suspended','graduated','dropped') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'enrolled',
  `grade_point_average` decimal(3,2) DEFAULT NULL,
  `attendance_rate` decimal(5,2) DEFAULT NULL,
  `completion_certificate_issued` tinyint(1) NOT NULL DEFAULT '0',
  `certificate_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prophetic_school_programs`
--

CREATE TABLE `prophetic_school_programs` (
  `id` int NOT NULL,
  `program_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `program_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_months` int DEFAULT NULL,
  `fee_amount` decimal(10,2) NOT NULL DEFAULT '100.00',
  `fee_currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `max_students` int DEFAULT NULL,
  `current_enrollment` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prophetic_school_programs`
--

INSERT INTO `prophetic_school_programs` (`id`, `program_name`, `program_code`, `description`, `duration_months`, `fee_amount`, `fee_currency`, `start_date`, `end_date`, `is_active`, `max_students`, `current_enrollment`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Basic Prophetic Training', 'PROPH-001', 'Introduction to prophetic ministry and spiritual gifts', 3, 100.00, 'USD', NULL, NULL, 1, NULL, 0, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(2, 'Advanced Prophetic Ministry', 'PROPH-002', 'Deep dive into prophetic operations and church ministry', 6, 200.00, 'USD', NULL, NULL, 1, NULL, 0, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59'),
(3, 'Prophetic Mentorship Program', 'PROPH-003', 'One-on-one mentorship with experienced prophetic ministers', 12, 500.00, 'USD', NULL, NULL, 1, NULL, 0, 1, '2026-04-10 14:08:59', '2026-04-10 14:08:59');

-- --------------------------------------------------------

--
-- Table structure for table `sermons`
--

CREATE TABLE `sermons` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sermon_date` date NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_type` enum('video','audio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'video',
  `media_url` text COLLATE utf8mb4_unicode_ci,
  `sermon_series` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preacher_id` int DEFAULT NULL,
  `views` int NOT NULL DEFAULT '0',
  `status` enum('published','draft','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sermons`
--

INSERT INTO `sermons` (`id`, `title`, `description`, `sermon_date`, `category`, `media_type`, `media_url`, `sermon_series`, `preacher_id`, `views`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(2, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(3, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(4, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(5, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(6, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(7, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(8, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(9, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(10, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(11, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(12, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(13, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(14, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(15, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(16, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(17, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(18, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(19, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(20, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(21, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(22, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(23, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(24, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(25, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(26, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(27, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(28, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(29, 'Walking in Divine Purpose', 'Discovering and fulfilling God\'s divine purpose for your life through biblical principles and practical application.', '2024-01-22', 'Purpose', 'audio', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(30, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(31, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 14:08:57', '2026-04-10 14:08:57'),
(34, 'The Power of Faith', 'A powerful message about the importance of faith in our daily lives and how it can transform our relationship with God.', '2024-01-15', 'Faith', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(36, 'The Blessing of Obedience', 'Understanding how obedience to God\'s word brings blessings and breakthroughs in every area of life.', '2024-01-29', 'Blessing', 'video', NULL, NULL, 1, 0, 'published', 1, '2026-04-10 15:34:44', '2026-04-10 15:34:44'),
(39, 'God of Hope and Restorations.', 'May the God of Hope fill your hearts will all peace and understanding.', '2026-04-12', 'faith', 'video', 'uploads/sermons/video/69db46a6bf04a_worship-vid.mp4', '', NULL, 0, 'published', 1, '2026-04-12 07:15:50', '2026-04-12 07:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `sermon_bookmarks`
--

CREATE TABLE `sermon_bookmarks` (
  `id` int NOT NULL,
  `sermon_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sermon_notes`
--

CREATE TABLE `sermon_notes` (
  `id` int NOT NULL,
  `sermon_id` int NOT NULL,
  `user_id` int NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` int DEFAULT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sermon_reactions`
--

CREATE TABLE `sermon_reactions` (
  `id` int NOT NULL,
  `sermon_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `reaction_type` enum('like','love','amen','blessed','inspired','pray','worship') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'like',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sermon_reactions`
--

INSERT INTO `sermon_reactions` (`id`, `sermon_id`, `user_id`, `reaction_type`, `ip_address`, `created_at`) VALUES
(1, 1, 2, 'like', NULL, '2026-04-10 14:08:57'),
(2, 1, 3, 'amen', NULL, '2026-04-10 13:47:55'),
(3, 1, 4, 'blessed', NULL, '2026-04-10 14:08:57'),
(4, 2, 2, 'love', NULL, '2026-04-10 14:08:57'),
(5, 2, 3, 'inspired', NULL, '2026-04-10 14:08:57'),
(6, 2, 5, 'amen', NULL, '2026-04-10 13:34:12'),
(7, 3, 2, 'blessed', NULL, '2026-04-10 13:34:12'),
(8, 3, 4, 'like', NULL, '2026-04-10 14:08:57'),
(9, 3, 5, 'inspired', NULL, '2026-04-10 14:08:57'),
(37, 2, 5, 'pray', NULL, '2026-04-10 14:08:57'),
(38, 3, 2, 'worship', NULL, '2026-04-10 14:08:57'),
(46, 1, 3, 'like', NULL, '2026-04-10 14:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `sermon_shares`
--

CREATE TABLE `sermon_shares` (
  `id` int NOT NULL,
  `sermon_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `share_type` enum('facebook','twitter','whatsapp','email','link') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sermon_views`
--

CREATE TABLE `sermon_views` (
  `id` int NOT NULL,
  `sermon_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testimonial` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('pending','approved','rejected','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int DEFAULT NULL
) ;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `email`, `occupation`, `testimonial`, `rating`, `is_approved`, `is_featured`, `status`, `submitted_at`, `approved_at`, `approved_by`) VALUES
(1, 'John Doe', 'john.doe@example.com', 'Business Owner', 'Salem Dominion Ministries has transformed my life. The teachings are powerful and the community is amazing!', 5, 1, 0, 'approved', '2026-04-10 14:56:26', NULL, 1),
(2, 'Jane Smith', 'jane.smith@example.com', 'Teacher', 'I found my spiritual home here. The worship is uplifting and the messages are life-changing.', 5, 1, 0, 'approved', '2026-04-10 14:56:26', NULL, 1),
(3, 'Michael Johnson', 'michael.j@example.com', 'Student', 'The youth ministry helped me discover my purpose in God. I am forever grateful!', 4, 1, 0, 'approved', '2026-04-10 14:56:26', NULL, 1),
(4, 'John Doe', 'john.doe@example.com', 'Business Owner', 'Salem Dominion Ministries has transformed my life. The teachings are powerful and the community is amazing!', 5, 1, 0, 'approved', '2026-04-10 15:34:44', NULL, 1),
(5, 'Jane Smith', 'jane.smith@example.com', 'Teacher', 'I found my spiritual home here. The worship is uplifting and the messages are life-changing.', 5, 1, 0, 'approved', '2026-04-10 15:34:44', NULL, 1),
(6, 'Michael Johnson', 'michael.j@example.com', 'Student', 'The youth ministry helped me discover my purpose in God. I am forever grateful!', 4, 1, 0, 'approved', '2026-04-10 15:34:44', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','member','admin','pastor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` text COLLATE utf8mb4_unicode_ci,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `username`, `password`, `role`, `phone`, `profile_image`, `address`, `city`, `country`, `avatar`, `bio`, `is_active`, `email_verified`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Apostle', 'Faty', 'apostle.faty@salem-dominion-ministries.org', 'apostlefaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pastor', '+256753244480', NULL, NULL, NULL, 'Uganda', NULL, NULL, 1, 1, NULL, '2026-04-10 13:34:11', '2026-04-10 15:34:42'),
(2, 'John', 'Doe', 'john.doe@example.com', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', '+256700123456', NULL, NULL, NULL, 'Uganda', NULL, NULL, 1, 1, NULL, '2026-04-10 13:34:11', '2026-04-10 15:34:42'),
(3, 'Jane', 'Smith', 'jane.smith@example.com', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+256751234567', NULL, NULL, NULL, 'Kenya', NULL, NULL, 1, 1, NULL, '2026-04-10 13:34:11', '2026-04-10 15:34:42'),
(4, 'Michael', 'Johnson', 'michael.johnson@example.com', 'michaelj', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', '+256702345678', NULL, NULL, NULL, 'Tanzania', NULL, NULL, 1, 1, NULL, '2026-04-10 13:34:11', '2026-04-10 14:08:57'),
(5, 'Sarah', 'Williams', 'sarah.williams@example.com', 'sarahw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+256753456789', NULL, NULL, NULL, 'Rwanda', NULL, NULL, 1, 1, NULL, '2026-04-10 13:34:11', '2026-04-10 14:08:57'),
(59, 'Otema', 'Reagan', 'reaganotema2022@gmail.com', 'reaganotema2022gmailcom618', '$2y$10$5pDWhazpLpPI9qcybT2AZOS/VZms/wDHEgDqKbTDWqBq8gU/nq.tq', 'user', '0772514889', NULL, NULL, NULL, 'Uganda', NULL, NULL, 1, 0, '2026-04-20 18:39:01', '2026-04-11 08:20:34', '2026-04-21 01:39:01');

-- --------------------------------------------------------

--
-- Table structure for table `user_login_logs`
--

CREATE TABLE `user_login_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_status` enum('success','failed','blocked') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_registration_settings`
--

CREATE TABLE `user_registration_settings` (
  `id` int NOT NULL,
  `registration_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `require_email_verification` tinyint(1) NOT NULL DEFAULT '0',
  `require_admin_approval` tinyint(1) NOT NULL DEFAULT '0',
  `default_role` enum('user','member') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `allowed_countries` json DEFAULT NULL,
  `welcome_message` text COLLATE utf8mb4_unicode_ci,
  `auto_login_after_registration` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_registration_settings`
--

INSERT INTO `user_registration_settings` (`id`, `registration_enabled`, `require_email_verification`, `require_admin_approval`, `default_role`, `allowed_countries`, `welcome_message`, `auto_login_after_registration`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:34:12', '2026-04-10 13:34:12'),
(2, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:40:44', '2026-04-10 13:40:44'),
(3, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:43:00', '2026-04-10 13:43:00'),
(4, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:45:06', '2026-04-10 13:45:06'),
(5, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:47:55', '2026-04-10 13:47:55'),
(6, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:52:03', '2026-04-10 13:52:03'),
(7, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:54:08', '2026-04-10 13:54:08'),
(8, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:56:51', '2026-04-10 13:56:51'),
(9, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 13:58:59', '2026-04-10 13:58:59'),
(10, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 14:03:23', '2026-04-10 14:03:23'),
(11, 1, 0, 0, 'user', '[\"Uganda\", \"Kenya\", \"Tanzania\", \"Rwanda\", \"Burundi\", \"South Sudan\"]', 'Welcome to Salem Dominion Ministries! Your account has been created successfully. You can now login and access all our spiritual resources.', 0, '2026-04-10 14:08:57', '2026-04-10 14:08:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_login_time` (`login_time`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_login_time` (`login_time`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_users_backup`
--
ALTER TABLE `admin_users_backup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_content` (`content_type`,`content_id`),
  ADD KEY `idx_post` (`post_type`,`post_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_is_approved` (`is_approved`),
  ADD KEY `idx_created_at` (`created_at`);

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
  ADD KEY `idx_confirmation_code` (`confirmation_code`),
  ADD KEY `idx_whatsapp_sent` (`whatsapp_sent`);

--
-- Indexes for table `donation_campaigns`
--
ALTER TABLE `donation_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaign_type` (`campaign_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_end_date` (`end_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_registration_id` (`registration_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_attendee_email` (`attendee_email`),
  ADD KEY `idx_check_in_time` (`check_in_time`);

--
-- Indexes for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_registration_id` (`registration_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `confirmation_code` (`confirmation_code`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_registered_at` (`registered_at`);

--
-- Indexes for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_registration_id` (`registration_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_file_type` (`file_type`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `gallery_bookmarks`
--
ALTER TABLE `gallery_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_gallery_bookmark` (`gallery_id`,`user_id`),
  ADD KEY `idx_gallery_id` (`gallery_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `gallery_comments`
--
ALTER TABLE `gallery_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gallery_id` (`gallery_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_is_approved` (`is_approved`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `gallery_reactions`
--
ALTER TABLE `gallery_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_gallery_reaction` (`gallery_id`,`user_id`,`reaction_type`),
  ADD KEY `idx_gallery_id` (`gallery_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reaction_type` (`reaction_type`);

--
-- Indexes for table `gallery_shares`
--
ALTER TABLE `gallery_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gallery_id` (`gallery_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_share_type` (`share_type`);

--
-- Indexes for table `leadership`
--
ALTER TABLE `leadership`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_position` (`order_position`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_recipient` (`recipient_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message` (`message_id`);

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
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_views` (`views`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `verification_token` (`verification_token`),
  ADD UNIQUE KEY `unsubscribe_token` (`unsubscribe_token`),
  ADD KEY `idx_subscription_type` (`subscription_type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_is_verified` (`is_verified`),
  ADD KEY `idx_subscribed_at` (`subscribed_at`);

--
-- Indexes for table `pastor_bookings`
--
ALTER TABLE `pastor_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pastor_id` (`pastor_id`),
  ADD KEY `idx_client_email` (`client_email`),
  ADD KEY `idx_booking_date` (`booking_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_confirmation_code` (`confirmation_code`);

--
-- Indexes for table `pastor_booking_availability`
--
ALTER TABLE `pastor_booking_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pastor_id` (`pastor_id`),
  ADD KEY `idx_day_of_week` (`day_of_week`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `prophetic_school_applications`
--
ALTER TABLE `prophetic_school_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_application_status` (`application_status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_transaction_id` (`transaction_id`);

--
-- Indexes for table `prophetic_school_assessments`
--
ALTER TABLE `prophetic_school_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_course_id` (`course_id`),
  ADD KEY `idx_assessment_type` (`assessment_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assessment_date` (`assessment_date`),
  ADD KEY `graded_by` (`graded_by`);

--
-- Indexes for table `prophetic_school_certificates`
--
ALTER TABLE `prophetic_school_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD KEY `idx_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_certificate_type` (`certificate_type`),
  ADD KEY `idx_issue_date` (`issue_date`),
  ADD KEY `idx_is_verified` (`is_verified`),
  ADD KEY `issued_by` (`issued_by`);

--
-- Indexes for table `prophetic_school_courses`
--
ALTER TABLE `prophetic_school_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `idx_program_id` (`program_id`),
  ADD KEY `idx_is_required` (`is_required`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `prophetic_school_documents`
--
ALTER TABLE `prophetic_school_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_is_verified` (`is_verified`);

--
-- Indexes for table `prophetic_school_enrollments`
--
ALTER TABLE `prophetic_school_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_program` (`application_id`,`program_id`),
  ADD KEY `idx_program_id` (`program_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_enrollment_date` (`enrollment_date`);

--
-- Indexes for table `prophetic_school_programs`
--
ALTER TABLE `prophetic_school_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_code` (`program_code`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `sermons`
--
ALTER TABLE `sermons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sermon_date` (`sermon_date`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_views` (`views`),
  ADD KEY `preacher_id` (`preacher_id`);

--
-- Indexes for table `sermon_bookmarks`
--
ALTER TABLE `sermon_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`sermon_id`,`user_id`),
  ADD KEY `idx_sermon_id` (`sermon_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `sermon_notes`
--
ALTER TABLE `sermon_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sermon_id` (`sermon_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `sermon_reactions`
--
ALTER TABLE `sermon_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`sermon_id`,`user_id`,`reaction_type`),
  ADD KEY `idx_sermon_id` (`sermon_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reaction_type` (`reaction_type`);

--
-- Indexes for table `sermon_shares`
--
ALTER TABLE `sermon_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sermon_id` (`sermon_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_share_type` (`share_type`);

--
-- Indexes for table `sermon_views`
--
ALTER TABLE `sermon_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sermon_id` (`sermon_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_viewed_at` (`viewed_at`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_approved` (`is_approved`),
  ADD KEY `idx_is_featured` (`is_featured`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_submitted_at` (`submitted_at`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_last_login` (`last_login`);

--
-- Indexes for table `user_login_logs`
--
ALTER TABLE `user_login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_login_status` (`login_status`),
  ADD KEY `idx_login_time` (`login_time`);

--
-- Indexes for table `user_registration_settings`
--
ALTER TABLE `user_registration_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_users_backup`
--
ALTER TABLE `admin_users_backup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `donation_campaigns`
--
ALTER TABLE `donation_campaigns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `event_attendees`
--
ALTER TABLE `event_attendees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_feedback`
--
ALTER TABLE `event_feedback`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `event_reminders`
--
ALTER TABLE `event_reminders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `gallery_bookmarks`
--
ALTER TABLE `gallery_bookmarks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery_comments`
--
ALTER TABLE `gallery_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `gallery_reactions`
--
ALTER TABLE `gallery_reactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `gallery_shares`
--
ALTER TABLE `gallery_shares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leadership`
--
ALTER TABLE `leadership`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ministries`
--
ALTER TABLE `ministries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `pastor_bookings`
--
ALTER TABLE `pastor_bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pastor_booking_availability`
--
ALTER TABLE `pastor_booking_availability`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `prophetic_school_applications`
--
ALTER TABLE `prophetic_school_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `prophetic_school_assessments`
--
ALTER TABLE `prophetic_school_assessments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prophetic_school_certificates`
--
ALTER TABLE `prophetic_school_certificates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prophetic_school_courses`
--
ALTER TABLE `prophetic_school_courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prophetic_school_documents`
--
ALTER TABLE `prophetic_school_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prophetic_school_enrollments`
--
ALTER TABLE `prophetic_school_enrollments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prophetic_school_programs`
--
ALTER TABLE `prophetic_school_programs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sermons`
--
ALTER TABLE `sermons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `sermon_bookmarks`
--
ALTER TABLE `sermon_bookmarks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sermon_notes`
--
ALTER TABLE `sermon_notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sermon_reactions`
--
ALTER TABLE `sermon_reactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `sermon_shares`
--
ALTER TABLE `sermon_shares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sermon_views`
--
ALTER TABLE `sermon_views`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `user_login_logs`
--
ALTER TABLE `user_login_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_registration_settings`
--
ALTER TABLE `user_registration_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD CONSTRAINT `admin_login_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users_backup` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donation_campaigns`
--
ALTER TABLE `donation_campaigns`
  ADD CONSTRAINT `donation_campaigns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD CONSTRAINT `event_attendees_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `event_attendees_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_feedback`
--
ALTER TABLE `event_feedback`
  ADD CONSTRAINT `event_feedback_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_feedback_ibfk_2` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reminders`
--
ALTER TABLE `event_reminders`
  ADD CONSTRAINT `event_reminders_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_reminders_ibfk_2` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_bookmarks`
--
ALTER TABLE `gallery_bookmarks`
  ADD CONSTRAINT `gallery_bookmarks_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gallery_bookmarks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_comments`
--
ALTER TABLE `gallery_comments`
  ADD CONSTRAINT `gallery_comments_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gallery_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gallery_comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `gallery_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_reactions`
--
ALTER TABLE `gallery_reactions`
  ADD CONSTRAINT `gallery_reactions_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery_shares`
--
ALTER TABLE `gallery_shares`
  ADD CONSTRAINT `gallery_shares_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `gallery` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gallery_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ministries`
--
ALTER TABLE `ministries`
  ADD CONSTRAINT `ministries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prophetic_school_assessments`
--
ALTER TABLE `prophetic_school_assessments`
  ADD CONSTRAINT `prophetic_school_assessments_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `prophetic_school_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prophetic_school_assessments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `prophetic_school_courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prophetic_school_assessments_ibfk_3` FOREIGN KEY (`graded_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prophetic_school_certificates`
--
ALTER TABLE `prophetic_school_certificates`
  ADD CONSTRAINT `prophetic_school_certificates_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `prophetic_school_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prophetic_school_certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prophetic_school_courses`
--
ALTER TABLE `prophetic_school_courses`
  ADD CONSTRAINT `prophetic_school_courses_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `prophetic_school_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prophetic_school_courses_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prophetic_school_documents`
--
ALTER TABLE `prophetic_school_documents`
  ADD CONSTRAINT `prophetic_school_documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `prophetic_school_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prophetic_school_enrollments`
--
ALTER TABLE `prophetic_school_enrollments`
  ADD CONSTRAINT `prophetic_school_enrollments_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `prophetic_school_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prophetic_school_enrollments_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `prophetic_school_programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prophetic_school_programs`
--
ALTER TABLE `prophetic_school_programs`
  ADD CONSTRAINT `prophetic_school_programs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sermons`
--
ALTER TABLE `sermons`
  ADD CONSTRAINT `sermons_ibfk_1` FOREIGN KEY (`preacher_id`) REFERENCES `admin_users_backup` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sermons_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sermon_bookmarks`
--
ALTER TABLE `sermon_bookmarks`
  ADD CONSTRAINT `sermon_bookmarks_ibfk_1` FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sermon_bookmarks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sermon_notes`
--
ALTER TABLE `sermon_notes`
  ADD CONSTRAINT `sermon_notes_ibfk_1` FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sermon_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sermon_reactions`
--
ALTER TABLE `sermon_reactions`
  ADD CONSTRAINT `sermon_reactions_ibfk_1` FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sermon_shares`
--
ALTER TABLE `sermon_shares`
  ADD CONSTRAINT `sermon_shares_ibfk_1` FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sermon_shares_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sermon_views`
--
ALTER TABLE `sermon_views`
  ADD CONSTRAINT `sermon_views_ibfk_1` FOREIGN KEY (`sermon_id`) REFERENCES `sermons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sermon_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `admin_users_backup` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_login_logs`
--
ALTER TABLE `user_login_logs`
  ADD CONSTRAINT `user_login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
