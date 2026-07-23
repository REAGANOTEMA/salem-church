-- ============================================================
-- SALEM DOMINION MINISTRIES
-- STEP 1: Create Database
-- ============================================================
-- Run this FIRST in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS `salem_dominion_ministries`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `salem_dominion_ministries`;

-- Verify
SELECT 'Database created successfully!' AS status;
SELECT DATABASE() AS current_database;
