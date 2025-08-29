#!/bin/bash

# Deploy Login System Script
# This script will deploy the exact login system from development to production

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"
LOCAL_APP="$LOCAL_ROOT/app"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_PUBLIC="$REMOTE_ROOT/public"
REMOTE_APP="$REMOTE_ROOT/app"
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"

echo "========================================="
echo "DEPLOYING EXACT LOGIN SYSTEM FROM DEVELOPMENT"
echo "========================================="

# Check if login.php exists locally
if [ ! -f "$LOCAL_PUBLIC/login.php" ]; then
    echo "Error: login.php not found in $LOCAL_PUBLIC"
    exit 1
fi

# Check if AuthController.php exists locally
if [ ! -f "$LOCAL_APP/Controllers/AuthController.php" ]; then
    echo "Error: AuthController.php not found in $LOCAL_APP/Controllers"
    exit 1
fi

# Upload login.php to both locations
echo "1. Uploading login.php..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_PUBLIC/login.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_PUBLIC/login.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"

# Upload AuthController.php
echo "2. Uploading AuthController.php..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Controllers"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Controllers/AuthController.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Controllers/"

# Upload middleware files
echo "3. Uploading middleware files..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Middleware"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Middleware/AuthMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Middleware/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Middleware/GuestMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Middleware/"

# Upload core files
echo "4. Uploading core files..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Core"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Core/Controller.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Core/Model.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_APP/Core/QueryBuilder.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"

# Upload admin_access.php for emergency access
echo "5. Uploading admin_access.php for emergency access..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_ROOT/admin_access.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC/"
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_ROOT/admin_access.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"

# Set permissions
echo "6. Setting permissions..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "chmod 644 $REMOTE_PUBLIC/login.php $REMOTE_PUBLIC_HTML/login.php $REMOTE_PUBLIC/admin_access.php $REMOTE_PUBLIC_HTML/admin_access.php"
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "find $REMOTE_APP -type d -exec chmod 755 {} \;"
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "find $REMOTE_APP -type f -exec chmod 644 {} \;"

# Verify files
echo "7. Verifying files..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "ls -la $REMOTE_PUBLIC/login.php $REMOTE_PUBLIC_HTML/login.php"
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_HOST" "ls -la $REMOTE_APP/Controllers/AuthController.php"

echo "========================================="
echo "LOGIN SYSTEM DEPLOYMENT COMPLETE!"
echo "========================================="
echo "You should now be able to access:"
echo "- Login page: https://www.siloe.com.py/login.php"
echo "- Emergency admin access: https://www.siloe.com.py/admin_access.php"
echo ""
echo "Admin credentials: admin@siloe.com / admin123"
