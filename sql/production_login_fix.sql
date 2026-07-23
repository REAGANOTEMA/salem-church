-- ============================================================
-- SALEM DOMINION MINISTRIES - PRODUCTION LOGIN FIX
-- Run this SQL in phpMyAdmin on the salemdominionmin_admin database
-- ============================================================

-- Step 1: Ensure admin_users table has all required columns
-- (Skip if table already exists from SQL import)

-- Step 2: Create or reset the SalemChurch admin user
INSERT INTO admin_users (username, password, full_name, email, role, is_active, login_attempts, locked_until)
VALUES (
    'SalemChurch',
    '$2y$10$42jRDNrpbbPilzY/nLDAdul9G0hJRh0fpNYA.cX5SuJBbc0bZkm.m',
    'Pastor Faty Musasizi',
    'admin@salemdominionministries.com',
    'super_admin',
    1,
    0,
    NULL
)
ON DUPLICATE KEY UPDATE
    password = '$2y$10$42jRDNrpbbPilzY/nLDAdul9G0hJRh0fpNYA.cX5SuJBbc0bZkm.m',
    full_name = 'Pastor Faty Musasizi',
    is_active = 1,
    login_attempts = 0,
    locked_until = NULL;

-- Step 3: Verify the user exists and login will work
SELECT id, username, full_name, email, role, is_active, login_attempts, locked_until
FROM admin_users
WHERE username = 'SalemChurch';
