#!/bin/bash

# Configuration
REMOTE_USER="siloecom"
REMOTE_HOST="192.185.143.154"
REMOTE_DIR="~/siloe"
LOCAL_DIR="/Users/robinklaiss/Dev/siloe"

# Create temporary directory for the package
PACKAGE_DIR="/tmp/siloe_auth_restore_$(date +%s)"
mkdir -p "$PACKAGE_DIR"

# Copy necessary files
echo "Preparing files for deployment..."

# 1. AuthController
mkdir -p "$PACKAGE_DIR/app/Controllers"
cp "$LOCAL_DIR/app/Controllers/AuthController.php" "$PACKAGE_DIR/app/Controllers/"

# 2. Database migration
mkdir -p "$PACKAGE_DIR/database/migrations"
cp "$LOCAL_DIR/database/migrations/2025_08_25_restore_auth_tables.php" "$PACKAGE_DIR/database/migrations/"

# 3. Restore script
cp "$LOCAL_DIR/deploy/scripts/restore_auth_system.sh" "$PACKAGE_DIR/"
chmod +x "$PACKAGE_DIR/restore_auth_system.sh"

# Create deployment script
cat > "$PACKAGE_DIR/deploy.sh" << 'EOL'
#!/bin/bash

# Set variables
REMOTE_DIR="$1"

echo "Deploying authentication system restore..."

# 1. Create necessary directories
ssh -t $REMOTE_USER@$REMOTE_HOST "
    echo 'Creating directories...';
    mkdir -p $REMOTE_DIR/app/Controllers $REMOTE_DIR/database/migrations /home/siloecom/backups;
"

# 2. Copy files
echo "Uploading files..."
rsync -avz -e "ssh -o StrictHostKeyChecking=no" \
    app/Controllers/AuthController.php \
    $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/app/Controllers/

rsync -avz -e "ssh -o StrictHostKeyChecking=no" \
    database/migrations/2025_08_25_restore_auth_tables.php \
    $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/database/migrations/

rsync -avz -e "ssh -o StrictHostKeyChecking=no" \
    restore_auth_system.sh \
    $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/

# 3. Run restore script
echo "Running restore script on remote server..."
ssh -t $REMOTE_USER@$REMOTE_HOST "
    cd $REMOTE_DIR;
    chmod +x restore_auth_system.sh;
    ./restore_auth_system.sh;
    echo 'Restore complete!';
"
EOL

chmod +x "$PACKAGE_DIR/deploy.sh"

# Create README
cat > "$PACKAGE_DIR/README.md" << 'EOL'
# Siloe Auth System Restore

This package contains the necessary files to restore the authentication system.

## Instructions

1. Review the files in this package
2. Run the deployment script:
   ```bash
   ./deploy.sh
   ```

## Files Included

- `app/Controllers/AuthController.php` - Updated authentication controller
- `database/migrations/2025_08_25_restore_auth_tables.php` - Database migration
- `restore_auth_system.sh` - Restoration script

## Default Admin Credentials

- Email: admin@example.com
- Password: password

**IMPORTANT**: Change these credentials after first login!
EOL

echo "Restore package created at: $PACKAGE_DIR"
echo "To deploy, run: cd $PACKAGE_DIR && ./deploy.sh"
