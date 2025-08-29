#!/bin/bash

# Emergency login fix script
# This script will redeploy admin_access.php and minimal_index.php to restore login functionality

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="~/.ssh/siloe_ed25519"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"

# Remote paths
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"
REMOTE_PUBLIC="/home1/siloecom/siloe/public"

echo "========================================="
echo "EMERGENCY LOGIN FIX"
echo "========================================="

# Check if admin_access.php exists locally
if [ ! -f "$LOCAL_ROOT/admin_access.php" ]; then
    echo "Error: admin_access.php not found in $LOCAL_ROOT"
    exit 1
fi

# Check if minimal_index.php exists locally
if [ ! -f "$LOCAL_ROOT/minimal_index.php" ]; then
    echo "Error: minimal_index.php not found in $LOCAL_ROOT"
    exit 1
fi

# Upload admin_access.php to both locations
echo "1. Uploading admin_access.php..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_ROOT/admin_access.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_ROOT/admin_access.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC/"

# Upload minimal_index.php as index.php to both locations
echo "2. Uploading minimal_index.php as index.php..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_ROOT/minimal_index.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/index.php"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_ROOT/minimal_index.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC/index.php"

# Set permissions
echo "3. Setting permissions..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "chmod 644 $REMOTE_PUBLIC_HTML/admin_access.php $REMOTE_PUBLIC_HTML/index.php $REMOTE_PUBLIC/admin_access.php $REMOTE_PUBLIC/index.php"

# Verify files
echo "4. Verifying files..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "ls -la $REMOTE_PUBLIC_HTML/admin_access.php $REMOTE_PUBLIC_HTML/index.php"

echo "========================================="
echo "LOGIN FIX COMPLETE!"
echo "========================================="
echo "You should now be able to access:"
echo "- Admin access: https://www.siloe.com.py/admin_access.php"
echo "- Emergency login: https://www.siloe.com.py/"
echo ""
echo "Admin credentials: admin@example.com / Admin123!"
