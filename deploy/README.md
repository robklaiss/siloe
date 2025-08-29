# Deploy

This directory contains deployment-related configs and scripts for the Siloe PHP app.

## Requirements
- PHP 8.x with `PDO` and `pdo_sqlite`
- Web server (Apache or Nginx) pointing the site root to `public/`
- File permissions: web user must write to `storage/logs/`, `database/` (and `database/siloe.db`), `public/uploads/`
- SSH access to the server (preferably with key authentication)

## cPanel quick start (production)
Environment: SSH user `siloecom` at host `192.185.143.154`.
Preferred deployment path is `~/siloe` with document root set to `~/siloe/public` in cPanel.

### First-time setup

1) Set up SSH key authentication (recommended)
   ```bash
   # Run the improved SSH key setup script
   bash deploy/scripts/setup_ssh_key_improved.sh
   ```
   This script will:
   - Create an SSH key if it doesn't exist
   - Copy the key to the server
   - Configure your SSH config file
   - Update deployment scripts to use the key

2) First-time setup in cPanel
   - In Domains → Document Root: set the domain/subdomain to `~/siloe/public`.
   - Ensure PHP 8.x is enabled for the domain.

### Using the unified deployment script (recommended)

```bash
# Perform a dry run first
bash deploy/deploy.sh --dry-run

# Deploy with full functionality
bash deploy/deploy.sh
```

The unified script handles:
- SSH connection verification
- Automatic backup creation
- File deployment with rsync
- Database migrations
- Permission setting
- Deployment verification

Options:
- `--dry-run`: Simulate deployment without making changes
- `--verbose`: Show detailed output
- `--skip-backup`: Skip backup creation
- `--skip-migrations`: Skip running migrations
- `--skip-permissions`: Skip setting permissions
- `--rollback [backup]`: Rollback to specified backup or latest if not specified

### Legacy deployment method

1) Upload code with rsync (safe to dry-run first)
   ```bash
   # optional: export to avoid host key prompt for the first connection
   export SSH_OPTS='-o StrictHostKeyChecking=accept-new'
   bash deploy/scripts/deploy_rsync.sh --dry-run
   bash deploy/scripts/deploy_rsync.sh
   ```
   Notes:
   - The script excludes `storage/*`, `public/uploads/*`, and `database/*.db` so server data is preserved.
   - Defaults: `REMOTE=siloecom@192.185.143.154`, `REMOTE_PATH=~/siloe` (override via env if needed).

2) Set permissions on server
   ```bash
   ssh $SSH_OPTS siloecom@192.185.143.154 "cd ~/siloe && bash deploy/scripts/set_permissions.sh"
   ```

3) Run migrations on server
   ```bash
   ssh $SSH_OPTS siloecom@192.185.143.154 "cd ~/siloe && PHP_BIN=php bash deploy/scripts/run_migrations.sh"
   ```

5) Verify
   - Browse the domain; if errors, check `storage/logs/` and the server error log in cPanel.
   - Ensure HTTPS is configured before keeping HSTS enabled in `public/.htaccess`.

## Quick start
1) Upload code with rsync:
   ```bash
   REMOTE=user@server REMOTE_PATH=/var/www/siloe \
   bash deploy/scripts/deploy_rsync.sh
   ```
2) Set permissions on server:
   ```bash
   ssh user@server "cd /var/www/siloe && bash deploy/scripts/set_permissions.sh"
   ```
3) Configure your web server using one of the examples in `deploy/configs/`:
   - `apache-vhost.conf`
   - `nginx-server.conf`
4) Run migrations on server:
   ```bash
   ssh user@server "cd /var/www/siloe && PHP_BIN=php bash deploy/scripts/run_migrations.sh"
   ```
5) Production hardening:
   - In `app/config/config.php` set `APP_ENV` to `'production'` (and adjust `APP_DEBUG` if added).
   - In `public/index.php` consider `ini_set('display_errors', 0);` while keeping logging enabled.

## Included

### New unified deployment system
- `deploy.sh` – Main unified deployment script with backup/rollback functionality
- `config.sh` – Centralized configuration for all deployment settings
- `scripts/setup_ssh_key_improved.sh` – Improved SSH key setup script

### Legacy scripts
- `scripts/deploy_rsync.sh` – sync project to server (excludes git, node_modules, logs)
- `scripts/set_permissions.sh` – ensures writable directories/files
- `scripts/run_migrations.sh` – executes `database/run-migrations.php`
- `configs/apache-vhost.conf` – Apache vhost sample (DocumentRoot `public/`)
- `configs/nginx-server.conf` – Nginx server block sample (try_files + PHP-FPM)
- `checklist.md` – concise end-to-end steps

## Notes
- Do NOT commit secrets. Use environment variables or server-level config.
- HSTS in `public/.htaccess` assumes HTTPS. Only enable it if TLS is configured.
- Always use SSH key authentication instead of passwords for better security.
- The backup/rollback system allows for safe deployments with easy recovery.
- All deployment logs are stored in `/tmp/siloe_deploy_*.log` for troubleshooting.
