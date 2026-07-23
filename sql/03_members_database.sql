-- ============================================================
-- SALEM DOMINION MINISTRIES
-- STEP 3: Members Database (salemdominionmin_members)
-- ============================================================
-- Run this in phpMyAdmin or MySQL CLI
-- Contains: User/member accounts
-- ============================================================

CREATE DATABASE IF NOT EXISTS `salemdominionmin_members`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `salemdominionmin_members`;

-- Verify
SELECT 'Members database created successfully!' AS status;
SELECT DATABASE() AS current_database;

-- ============================================================
-- 1. users
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
-- ============================================================
-- SAMPLE DATA
-- ============================================================
-- ============================================================

-- ============================================================
-- Users (3 sample users, password = "password" for all)
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================
INSERT IGNORE INTO `users` (`id`, `first_name`, `last_name`, `email`, `username`, `password`, `role`, `phone`, `country`, `is_active`, `email_verified`)
VALUES
  (1, 'Apostle', 'Faty', 'apostle.faty@salem-dominion-ministries.com', 'apostlefaty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pastor', '+256753244480', 'Uganda', 1, 1),
  (2, 'John', 'Doe', 'john.doe@example.com', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', '+256700123456', 'Uganda', 1, 1),
  (3, 'Jane', 'Smith', 'jane.smith@example.com', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+256751234567', 'Kenya', 1, 1);

-- ============================================================
-- ============================================================
--
-- MEMBERS DATABASE SETUP COMPLETE!
--
-- ============================================================
-- ============================================================
--
-- DATABASE: salemdominionmin_members
--
-- Total Tables: 1
--
-- GENERAL USER LOGIN CREDENTIALS:
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
-- TABLE SUMMARY:
-- ============================================================
--
--  1.  users    (3 sample users)
--
-- ============================================================
-- NOTE: All password hashes are for the word "password"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ============================================================
