#!/bin/bash

# Full Site Deployment Script using SCP
# This script will deploy the complete development version to production

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="~/.ssh/siloe_ed25519"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"
LOCAL_APP="$LOCAL_ROOT/app"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_PUBLIC="$REMOTE_ROOT/public"
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"

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
mkdir -p $REMOTE_ROOT/backups

# Create a timestamped backup of the current site
TIMESTAMP=\$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$REMOTE_ROOT/backups/siloe_backup_\${TIMESTAMP}.tar.gz"

echo "Creating backup of current site..."
tar -czf \$BACKUP_FILE -C $REMOTE_PUBLIC .

# Keep only the 5 most recent backups
echo "Cleaning up old backups..."
ls -t $REMOTE_ROOT/backups/siloe_backup_*.tar.gz | tail -n +6 | xargs rm -f 2>/dev/null || true

echo "Backup created: \$BACKUP_FILE"
EOF

chmod +x /tmp/remote_backup.sh

# Run backup on server
echo "1. Creating backup of current site..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "bash -s" < /tmp/remote_backup.sh

# Create a temporary directory for files to upload
TEMP_DIR=$(mktemp -d)
echo "2. Preparing files for upload..."

# Copy public files
echo "   Preparing public files..."
cp -r "$LOCAL_PUBLIC"/* "$TEMP_DIR/"

# Create a tar archive of the app directory
echo "   Preparing app directory..."
tar -czf "$TEMP_DIR/app.tar.gz" -C "$LOCAL_ROOT" app

# Create a tar archive of the config directory
echo "   Preparing config directory..."
tar -czf "$TEMP_DIR/config.tar.gz" -C "$LOCAL_ROOT" config

# Upload files
echo "3. Uploading files to server..."
scp -i $SSH_KEY -o IdentitiesOnly=yes -r "$TEMP_DIR"/* "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC/"

# Create extraction and setup script
cat > /tmp/remote_setup.sh << EOF
#!/bin/bash

# Extract app directory
echo "Extracting app directory..."
tar -xzf $REMOTE_PUBLIC/app.tar.gz -C $REMOTE_ROOT

# Extract config directory
echo "Extracting config directory..."
tar -xzf $REMOTE_PUBLIC/config.tar.gz -C $REMOTE_ROOT

# Clean up tar files
rm $REMOTE_PUBLIC/app.tar.gz
rm $REMOTE_PUBLIC/config.tar.gz

# Set permissions
echo "Setting permissions..."
find $REMOTE_ROOT/app -type d -exec chmod 755 {} \;
find $REMOTE_ROOT/app -type f -exec chmod 644 {} \;
find $REMOTE_PUBLIC -type d -exec chmod 755 {} \;
find $REMOTE_PUBLIC -type f -exec chmod 644 {} \;

# Verify deployment
echo "✅ Verifying deployment..."
ls -la $REMOTE_PUBLIC/index.php

echo "✅ FULL SITE DEPLOYMENT COMPLETE!"
EOF

chmod +x /tmp/remote_setup.sh

# Run setup on server
echo "4. Setting up files on server..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "bash -s" < /tmp/remote_setup.sh

# Clean up
rm -rf "$TEMP_DIR"
rm /tmp/remote_backup.sh
rm /tmp/remote_setup.sh

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
