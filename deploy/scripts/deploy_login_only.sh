#!/bin/bash

# Deploy Login Files Only Script
# This script focuses only on the essential login files

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"
LOCAL_APP="$LOCAL_ROOT/app"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_PUBLIC="$REMOTE_ROOT/public"
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"

echo "========================================="
echo "DEPLOYING ESSENTIAL LOGIN FILES ONLY"
echo "========================================="

# Upload login.php to public_html
echo "1. Uploading login.php to public_html..."
scp $SSH_OPTS "$LOCAL_PUBLIC/login.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded login.php to public_html"
else
    echo "❌ Failed to upload login.php to public_html"
fi

# Upload admin_access.php to public_html
echo "2. Uploading admin_access.php to public_html..."
scp $SSH_OPTS "$LOCAL_ROOT/admin_access.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded admin_access.php to public_html"
else
    echo "❌ Failed to upload admin_access.php to public_html"
fi

# Set permissions
echo "3. Setting permissions..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "chmod 644 $REMOTE_PUBLIC_HTML/login.php $REMOTE_PUBLIC_HTML/admin_access.php"
if [ $? -eq 0 ]; then
    echo "✅ Successfully set permissions"
else
    echo "❌ Failed to set permissions"
fi

echo "========================================="
echo "LOGIN FILES DEPLOYMENT COMPLETE!"
echo "========================================="
echo "You should now be able to access:"
echo "- Login page: https://www.siloe.com.py/login.php"
echo "- Emergency admin access: https://www.siloe.com.py/admin_access.php"
echo ""
echo "Admin credentials: admin@siloe.com / admin123"
