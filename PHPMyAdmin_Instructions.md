# PHPMyAdmin Instructions - Admin Login Tables

## Step-by-Step Execution Guide

### 1. Access PHPMyAdmin
- Open your web browser
- Navigate to: `http://localhost/phpmyadmin` (or your PHPMyAdmin URL)
- Login with your database credentials

### 2. Select Database
- In the left panel, find and click on `salem_dominion_ministries`
- If the database doesn't exist, create it first:
  - Click "New" in the left panel
  - Enter database name: `salem_dominion_ministries`
  - Select "utf8mb4_unicode_ci" collation
  - Click "Create"

### 3. Execute SQL Script
- Click on the `salem_dominion_ministries` database
- Click the "SQL" tab in the top navigation
- Copy the entire content from: `database_tables/admin_login_table.sql`
- Paste the SQL code into the SQL query box
- Click "Go" or "Execute" to run the script

### 4. Verify Table Creation
- After execution, you should see these tables in the left panel:
  - `admin_users`
  - `admin_sessions`
  - `admin_login_logs`
  - `admin_settings`

### 5. Verify Admin User
- Click on the `admin_users` table
- Click "Browse" to view the data
- Confirm Pastor Faty Musasizi account exists:
  - Username: `MusasiziFaty`
  - Full Name: `Pastor Faty Musasizi`
  - Role: `pastor`

### 6. Test Login
- Navigate to your admin login page
- Use these credentials:
  - Username: `MusasiziFaty`
  - Password: `123456`

## Database Schema Overview

### admin_users Table
- Stores admin user credentials and information
- Includes Pastor Faty Musasizi account
- Password is securely hashed
- Supports multiple admin roles

### admin_sessions Table
- Manages active admin sessions
- Tracks login time and activity
- Includes IP address and user agent for security

### admin_login_logs Table
- Logs all login attempts (success/failure)
- Security auditing and monitoring
- Helps detect suspicious activity

### admin_settings Table
- Stores admin panel configuration
- Security settings and preferences
- Easy to customize behavior

## Security Features Implemented

1. **Password Hashing**: Secure password storage
2. **Account Lockout**: Prevents brute force attacks
3. **Session Management**: Secure session handling
4. **Login Logging**: Complete audit trail
5. **Role-Based Access**: Different permission levels

## Troubleshooting

### If SQL Execution Fails:
1. Check if database exists
2. Verify SQL syntax is complete
3. Check for existing tables with same names
4. Ensure proper database permissions

### If Login Doesn't Work:
1. Verify admin_users table has the correct data
2. Check PHP error logs
3. Ensure database connection is working
4. Verify session configuration

## Next Steps

After successfully creating these tables:
1. Test the admin login functionality
2. Review the admin_dashboard.php page
3. Configure additional admin settings as needed
4. Set up proper backup procedures

## File Location

The SQL script is located at:
`database_tables/admin_login_table.sql`

Keep this file for reference and future database setups.
