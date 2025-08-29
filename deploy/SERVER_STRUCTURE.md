# Siloe Server Structure Documentation

This document provides a detailed overview of the Siloe server directory structure based on the emergency deployment completed on August 26, 2025.

## Server Details
- **SSH User**: siloecom
- **Server IP**: 192.185.143.154
- **Web Root**: /home1/siloecom/siloe/public
- **Public HTML**: /home1/siloecom/public_html (symlinked to siloe/public)
- **Admin Credentials**: admin@example.com / Admin123!

## Directory Structure

### Root Directory: /home1/siloecom
Contains user home directory files, configuration files, and main application directories:
- Standard cPanel user files (.bash_history, .ssh, etc.)
- `public_html` - Web accessible directory (symlinked to siloe/public)
- `siloe` - Main application directory
- Backup files (siloe_backup_*.tar.gz)

### Web Root: /home1/siloecom/public_html
This is a symlink to `/home1/siloecom/siloe/public` and contains:
- `.htaccess` - Apache configuration
- `index.php` - Main entry point (deployed from minimal_index.php)
- `admin_access.php` - Emergency admin access script
- Various PHP files for testing and debugging
- Asset directories (css, js, images)
- Configuration files

### Application Root: /home1/siloecom/siloe
Contains the main application structure:
- `app/` - Application code
  - `Core/` - Core framework files
  - `Controllers/` - Controller classes
  - `Models/` - Data models
  - `Middleware/` - Authentication middleware
- `public/` - Publicly accessible files (same content as public_html)
- `database/` - Database files and migrations
- `backups/` - Backup directory
- `storage/` - File storage

## Deployment Notes

1. The `public_html` directory is symlinked to `/home1/siloecom/siloe/public` to maintain proper MVC structure while working with cPanel's default web root.

2. Critical files are deployed to both locations to ensure functionality even if the symlink is broken:
   - `index.php` (from minimal_index.php)
   - `admin_access.php`

3. Core application files are deployed to their proper locations in the MVC structure:
   - Core framework files in `/home1/siloecom/siloe/app/Core/`
   - Controllers in `/home1/siloecom/siloe/app/Controllers/`
   - Middleware in `/home1/siloecom/siloe/app/Middleware/`

## Verification

The deployment was verified by:
1. Successful admin login
2. Proper session data
3. Directory structure verification
4. File permission checks

## Future Deployments

For future deployments, use the `direct_ssh_upload.sh` script in the `deploy/scripts/` directory, which:
1. Sets up proper directory structure
2. Backs up existing files
3. Deploys application files to the correct locations
4. Sets proper file permissions
