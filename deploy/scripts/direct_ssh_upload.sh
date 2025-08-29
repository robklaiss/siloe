#!/bin/bash

# Full System Deployment Script
# This script will deploy the entire local system to production
# It includes both emergency fixes and full application files

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="~/.ssh/siloe_ed25519"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"
LOCAL_APP="$LOCAL_ROOT/app"
MINIMAL_INDEX="$LOCAL_ROOT/minimal_index.php"
ADMIN_ACCESS="$LOCAL_ROOT/admin_access.php"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_PUBLIC="$REMOTE_ROOT/public"
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"

# Check if essential files exist
if [ ! -f "$MINIMAL_INDEX" ]; then
    echo "Error: minimal_index.php not found at $MINIMAL_INDEX"
    exit 1
fi

if [ ! -f "$ADMIN_ACCESS" ]; then
    echo "Error: admin_access.php not found at $ADMIN_ACCESS"
    exit 1
fi

if [ ! -d "$LOCAL_PUBLIC" ]; then
    echo "Error: public directory not found at $LOCAL_PUBLIC"
    exit 1
fi

if [ ! -d "$LOCAL_APP" ]; then
    echo "Error: app directory not found at $LOCAL_APP"
    exit 1
fi

# Create a temporary script to run on the server for setup
cat > /tmp/remote_setup.sh << EOF
#!/bin/bash

# Create necessary directories if they don't exist
mkdir -p $REMOTE_ROOT/public
mkdir -p $REMOTE_ROOT/app
mkdir -p $REMOTE_ROOT/app/Core
mkdir -p $REMOTE_ROOT/app/Controllers
mkdir -p $REMOTE_ROOT/app/Models
mkdir -p $REMOTE_ROOT/app/Middleware

# Backup existing index.php in public_html
if [ -f $REMOTE_PUBLIC_HTML/index.php ]; then
    cp $REMOTE_PUBLIC_HTML/index.php $REMOTE_PUBLIC_HTML/index.php.bak
    echo "✅ Backed up existing index.php in public_html"
fi

# Backup existing index.php in public
if [ -f $REMOTE_PUBLIC/index.php ]; then
    cp $REMOTE_PUBLIC/index.php $REMOTE_PUBLIC/index.php.bak
    echo "✅ Backed up existing index.php in public"
fi

# Ensure public_html symlinks to siloe/public if needed
if [ ! -L $REMOTE_PUBLIC_HTML ] && [ -d $REMOTE_PUBLIC_HTML ]; then
    echo "Setting up symlink from public_html to siloe/public"
    mv $REMOTE_PUBLIC_HTML $REMOTE_PUBLIC_HTML.bak
    ln -s $REMOTE_PUBLIC $REMOTE_PUBLIC_HTML
fi

echo "✅ Server directories prepared"
EOF

chmod +x /tmp/remote_setup.sh

# Create a script for setting permissions
cat > /tmp/remote_permissions.sh << EOF
#!/bin/bash

# Set permissions for key files
chmod 644 $REMOTE_PUBLIC_HTML/index.php
chmod 644 $REMOTE_PUBLIC_HTML/admin_access.php
chmod 644 $REMOTE_PUBLIC/index.php
chmod -R 755 $REMOTE_ROOT/app
chmod -R 755 $REMOTE_PUBLIC

# Verify critical files
echo "✅ Verifying critical files:"
ls -la $REMOTE_PUBLIC_HTML/index.php
ls -la $REMOTE_PUBLIC_HTML/admin_access.php
ls -la $REMOTE_PUBLIC/index.php

echo "✅ DEPLOYMENT COMPLETE!"
echo "The site should now be accessible at:"
echo "- Main site: https://www.siloe.com.py/"
echo "- Admin access: https://www.siloe.com.py/admin_access.php"
EOF

chmod +x /tmp/remote_permissions.sh

echo "========================================"
echo "FULL SYSTEM DEPLOYMENT TO SILOE SERVER"
echo "========================================"

# 1. Setup server directories
echo "1. Setting up server directories..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "bash -s" < /tmp/remote_setup.sh

# 2. Upload emergency fix files
echo "\n2. Uploading emergency fix files..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$MINIMAL_INDEX" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/index.php"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$MINIMAL_INDEX" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC/index.php"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$ADMIN_ACCESS" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/admin_access.php"

# 3. Upload core application files
echo "\n3. Uploading core application files..."

# Upload Core files
echo "   Uploading Core files..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Core/Controller.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_ROOT/app/Core/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Core/Model.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_ROOT/app/Core/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Core/QueryBuilder.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_ROOT/app/Core/"

# Upload Controllers
echo "   Uploading Controllers..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Controllers/AuthController.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_ROOT/app/Controllers/"

# Upload Middleware
echo "   Uploading Middleware..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Middleware/AuthMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_ROOT/app/Middleware/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Middleware/GuestMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_ROOT/app/Middleware/"

# 4. Set permissions
echo "\n4. Setting permissions..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "bash -s" < /tmp/remote_permissions.sh

# Clean up
rm /tmp/remote_setup.sh
rm /tmp/remote_permissions.sh

echo "========================================"
echo "FULL SYSTEM DEPLOYMENT COMPLETE!"
echo "========================================"
echo "The site should now be accessible at:"
echo "- Main site: https://www.siloe.com.py/"
echo "- Admin access: https://www.siloe.com.py/admin_access.php"
echo ""
echo "Admin credentials:"
echo "- Email: admin@example.com"
echo "- Password: Admin123!"
echo ""
echo "Deployed files:"
echo "- Emergency minimal_index.php → index.php"
echo "- Emergency admin_access.php"
echo "- Core system files (Controller, Model, QueryBuilder)"
echo "- AuthController.php for login functionality"
echo "- Middleware files for authentication"
echo ""
echo "Next steps:"
echo "1. Verify the site is working by visiting https://www.siloe.com.py/"
echo "2. Test admin access at https://www.siloe.com.py/admin_access.php"
echo "3. Test regular login functionality"
