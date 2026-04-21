# Hosting Deployment Guide - Salem Dominion Ministries

## Overview
This guide will help you deploy your website to a hosting platform with full database connectivity for both the website and admin dashboard.

## Pre-Deployment Checklist

### 1. Database Setup on Hosting
- [ ] Create MySQL database on your hosting control panel
- [ ] Create database user with full privileges
- [ ] Note down your database credentials:
  - Database Host (usually `localhost`)
  - Database Name
  - Database Username  
  - Database Password
  - Database Port (usually `3306`)

### 2. File Upload
- [ ] Upload all files from your local `salem-site` folder to your hosting
- [ ] Ensure file permissions are set correctly (755 for directories, 644 for files)
- [ ] Upload the SQL database file (`salem_dominion_ministries.sql`)

### 3. Production Configuration
- [ ] Edit `config.production.php` with your hosting database details
- [ ] Update the following lines in `config.production.php`:

```php
define('PROD_DB_HOST', 'your_db_host');        // Usually 'localhost'
define('PROD_DB_USER', 'your_db_username');  // Your hosting DB username
define('PROD_DB_PASS', 'your_db_password');  // Your hosting DB password
define('PROD_DB_NAME', 'your_db_name');      // Your hosting DB name
define('PROD_SITE_URL', 'https://yourdomain.com'); // Your actual domain
```

## Step-by-Step Deployment

### Step 1: Database Import
1. Access your hosting control panel (cPanel, Plesk, etc.)
2. Go to phpMyAdmin or database manager
3. Create a new database (e.g., `salemdomin_salem`)
4. Import the `salem_dominion_ministries.sql` file
5. Create a database user and grant full privileges

### Step 2: File Upload
1. Upload all files from your local project to your hosting
2. Common upload methods:
   - FTP client (FileZilla, WinSCP)
   - File Manager in hosting control panel
   - Git repository (if supported)

### Step 3: Configuration
1. Edit `config.production.php` with your database details
2. Upload the updated file to your hosting
3. The system will automatically detect and use these settings

### Step 4: Verify Installation
1. Visit your website homepage
2. Try accessing the admin dashboard at `yourdomain.com/admin/`
3. Test login with credentials:
   - Username: `MusasiziFaty`
   - Password: `123456`

## Environment Detection

The system automatically detects the environment:

### Localhost (Development)
- Automatically detected when accessing from:
  - `localhost`
  - `127.0.0.1`
  - `.dev`, `.local`, `.test` domains
- Uses local database configuration
- Database: `salem_dominion_ministries`
- User: `root`
- Password: `ReagaN23#`

### Production (Hosting)
- Automatically detected when accessing from live domain
- Uses production configuration from `config.production.php`
- Uses your hosting database credentials

## Troubleshooting

### Database Connection Issues
1. **Check database credentials** in `config.production.php`
2. **Verify database exists** on hosting server
3. **Check database user permissions**
4. **Confirm database host** (usually `localhost`)

### Admin Login Issues
1. **Run admin verification**: `yourdomain.com/admin_dashboard_verification.php`
2. **Check admin_users table** exists in database
3. **Verify admin user** was created during import

### File Upload Issues
1. **Check upload directory permissions** (755)
2. **Verify upload path** in `config.production.php`
3. **Ensure PHP upload limits** are sufficient

### Common Error Messages

#### "Database connection failed"
- Check database credentials in `config.production.php`
- Verify database server is running
- Confirm database name and user exist

#### "Access denied for user"
- Database username or password incorrect
- User doesn't have database privileges

#### "Unknown database"
- Database name incorrect in configuration
- Database not created on hosting server

## Security Recommendations

1. **Change default admin password** after first login
2. **Use strong database credentials** 
3. **Enable HTTPS** on your hosting
4. **Regular backups** of database and files
5. **Update PHP version** to latest stable release

## Testing Checklist

After deployment, test all functionality:

- [ ] Homepage loads correctly
- [ ] All navigation links work
- [ ] Admin login works (`/admin/`)
- [ ] Admin dashboard loads
- [ ] Can add/edit sermons
- [ ] Can add/edit events
- [ ] Can add/edit news
- [ ] Can upload gallery items
- [ ] User registration works
- [ ] Contact forms work
- [ ] Mobile responsiveness works

## Support

If you encounter issues:
1. Check the error logs in your hosting control panel
2. Run the verification script: `yourdomain.com/admin_dashboard_verification.php`
3. Ensure all database tables were imported correctly
4. Verify file permissions are set correctly

## Quick Start Commands

```bash
# Import database (via SSH/CLI)
mysql -u your_username -p your_database < salem_dominion_ministries.sql

# Set file permissions (via SSH/CLI)
chmod -R 755 /path/to/your/website
chmod -R 644 /path/to/your/website/*.php
```

## Next Steps

Once deployed:
1. Test all functionality thoroughly
2. Update admin credentials
3. Configure email settings if needed
4. Set up regular backups
5. Monitor website performance

Your website and admin dashboard should now work perfectly on both localhost and your hosting platform!
