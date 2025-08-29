# Deployment checklist

- [ ] PHP 8.x with `pdo_sqlite` enabled
- [ ] Web root points to `public/`
- [ ] Apache: `mod_rewrite` enabled, or Nginx: `try_files` configured
- [ ] Writable: `storage/logs/`, `database/` (+ `database/siloe.db`), `public/uploads/`
- [ ] Code uploaded (rsync or git)
- [ ] Migrations executed: `php database/run-migrations.php`
- [ ] Error display disabled in production (keep logging)

## Steps
1) Upload code:
   ```bash
   REMOTE=user@server REMOTE_PATH=/var/www/siloe \
   bash deploy/scripts/deploy_rsync.sh
   ```
2) Set permissions:
   ```bash
   ssh user@server "cd /var/www/siloe && bash deploy/scripts/set_permissions.sh"
   ```
3) Configure webserver using one of:
   - `deploy/configs/apache-vhost.conf`
   - `deploy/configs/nginx-server.conf`
4) Run migrations:
   ```bash
   ssh user@server "cd /var/www/siloe && PHP_BIN=php bash deploy/scripts/run_migrations.sh"
   ```
5) Switch to production settings:
   - In `app/config/config.php`: set `APP_ENV` to `'production'` and consider turning off `APP_DEBUG`.
   - In `public/index.php`: set `ini_set('display_errors', 0);` (logging remains enabled).
