-- ============================================================
-- SALEM DOMINION MINISTRIES
-- STEP 6: Comprehensive Schema Alignment
-- Adds missing columns for handler.php and api.php compatibility
-- ============================================================

USE `salemdominionmin_website`;

SET FOREIGN_KEY_CHECKS = 0;

-- ── events: add missing columns for handler.php ──
ALTER TABLE `events`
  ADD COLUMN IF NOT EXISTS `category` varchar(100) DEFAULT 'general' AFTER `location`,
  ADD COLUMN IF NOT EXISTS `image_url` text DEFAULT NULL AFTER `banner_image`,
  ADD COLUMN IF NOT EXISTS `is_recurring` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_featured`;

-- ── donations: add currency column ──
ALTER TABLE `donations`
  ADD COLUMN IF NOT EXISTS `currency` varchar(3) NOT NULL DEFAULT 'UGX' AFTER `amount`;

-- ── contact_messages: add read_by column ──
ALTER TABLE `contact_messages`
  ADD COLUMN IF NOT EXISTS `read_by` int(11) DEFAULT NULL AFTER `read_at`;

-- ── announcements: add priority column ──
ALTER TABLE `announcements`
  ADD COLUMN IF NOT EXISTS `priority` varchar(20) NOT NULL DEFAULT 'normal' AFTER `content`;

-- ── pastor_bookings: add confirmation/cancellation columns ──
ALTER TABLE `pastor_bookings`
  ADD COLUMN IF NOT EXISTS `confirmed_by` int(11) DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `confirmed_at` datetime DEFAULT NULL AFTER `confirmed_by`,
  ADD COLUMN IF NOT EXISTS `cancel_reason` text DEFAULT NULL AFTER `confirmation_code`,
  ADD COLUMN IF NOT EXISTS `cancelled_by` int(11) DEFAULT NULL AFTER `cancel_reason`,
  ADD COLUMN IF NOT EXISTS `cancelled_at` datetime DEFAULT NULL AFTER `cancelled_by`;

-- ── prophetic_school_applications: add updated_at ──
ALTER TABLE `prophetic_school_applications`
  ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- ── news: add published_at for handler.php compat ──
ALTER TABLE `news`
  ADD COLUMN IF NOT EXISTS `published_at` datetime DEFAULT NULL AFTER `scheduled_at`;

SET FOREIGN_KEY_CHECKS = 1;
