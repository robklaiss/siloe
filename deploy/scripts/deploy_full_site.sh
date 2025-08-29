#!/bin/bash

# Full Site Deployment Script using rsync
# This script will deploy the complete development version to production

# Source configuration
source "$(dirname "$0")/../config.sh"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"

# Banner
echo "========================================"
echo "FULL SITE DEPLOYMENT TO SILOE SERVER"
echo "========================================"
echo "This will deploy your complete development version to production"
echo ""

# Confirm deployment
read -p "Are you sure you want to deploy the full site? (y/n): " confirm
if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
    echo "Deployment cancelled."
    exit 0
fi

# Create backup script
cat > /tmp/remote_backup.sh << EOF
#!/bin/bash

# Create backup directory if it doesn't exist
mkdir -p ${REMOTE_BACKUP_PATH}

# Create a timestamped backup of the current site
TIMESTAMP=\$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${REMOTE_BACKUP_PATH}/siloe_backup_\${TIMESTAMP}.tar.gz"

echo "Creating backup of current site..."
tar -czf \$BACKUP_FILE -C ${REMOTE_BASE_PATH} .

# Keep only the 5 most recent backups
echo "Cleaning up old backups..."
ls -t ${REMOTE_BACKUP_PATH}/siloe_backup_*.tar.gz | tail -n +6 | xargs rm -f 2>/dev/null || true

echo "Backup created: \$BACKUP_FILE"
EOF

chmod +x /tmp/remote_backup.sh

# Run backup on server
echo "1. Creating backup of current site..."
ssh ${SSH_OPTS} ${REMOTE} "bash -s" < /tmp/remote_backup.sh

# Create exclude file for rsync
cat > /tmp/rsync_exclude.txt << EOF
.git/
.gitignore
.DS_Store
node_modules/
vendor/
*.log
storage/logs/*
database/*.sqlite
database/*.db
backups/
EOF

# Deploy using rsync
echo "2. Deploying full site using rsync..."

# Deploy app directory
echo "   Deploying app directory..."
rsync ${RSYNC_OPTS} "${RSYNC_EXCLUDES[@]}" --exclude-from=/tmp/rsync_exclude.txt \
    "$LOCAL_ROOT/app/" "${REMOTE}:${REMOTE_BASE_PATH}/app/"

# Deploy public directory
echo "   Deploying public directory..."
rsync ${RSYNC_OPTS} "${RSYNC_EXCLUDES[@]}" --exclude-from=/tmp/rsync_exclude.txt \
    "$LOCAL_PUBLIC/" "${REMOTE}:${REMOTE_PUBLIC_PATH}/"

# Deploy config directory
echo "   Deploying config directory..."
rsync ${RSYNC_OPTS} "${RSYNC_EXCLUDES[@]}" --exclude-from=/tmp/rsync_exclude.txt \
    "$LOCAL_ROOT/config/" "${REMOTE}:${REMOTE_BASE_PATH}/config/"

# Create permissions script
cat > /tmp/remote_permissions.sh << EOF
#!/bin/bash

# Set permissions
echo "Setting permissions..."
find ${REMOTE_BASE_PATH}/app -type d -exec chmod 755 {} \;
find ${REMOTE_BASE_PATH}/app -type f -exec chmod 644 {} \;
find ${REMOTE_PUBLIC_PATH} -type d -exec chmod 755 {} \;
find ${REMOTE_PUBLIC_PATH} -type f -exec chmod 644 {} \;

# Make sure index.php is accessible
chmod 644 ${REMOTE_PUBLIC_PATH}/index.php

# Verify deployment
echo "✅ Verifying deployment..."
ls -la ${REMOTE_PUBLIC_PATH}/index.php

echo "✅ FULL SITE DEPLOYMENT COMPLETE!"
EOF

chmod +x /tmp/remote_permissions.sh

# Set permissions
echo "3. Setting permissions..."
ssh ${SSH_OPTS} ${REMOTE} "bash -s" < /tmp/remote_permissions.sh

# Clean up
rm /tmp/remote_backup.sh
rm /tmp/remote_permissions.sh
rm /tmp/rsync_exclude.txt

echo "========================================"
echo "FULL SITE DEPLOYMENT COMPLETE!"
echo "========================================"
echo "Your complete development version has been deployed to production."
echo ""
echo "The site should now be accessible at:"
echo "- Main site: https://www.siloe.com.py/"
echo ""
echo "Next steps:"
echo "1. Verify the site is working by visiting https://www.siloe.com.py/"
echo "2. Test all functionality to ensure everything is working as expected"
