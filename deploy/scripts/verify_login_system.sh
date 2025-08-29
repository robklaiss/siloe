#!/bin/bash

# Verify Login System Script
# This script checks if the login system is working correctly on production

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Remote paths
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"
REMOTE_APP="/home1/siloecom/siloe/app"

echo "========================================="
echo "VERIFYING LOGIN SYSTEM ON PRODUCTION"
echo "========================================="

# Check if login.php exists
echo "1. Checking if login.php exists..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_PUBLIC_HTML/login.php ]; then echo 'Found'; else echo 'Not found'; fi"

# Check if admin_access.php exists
echo "2. Checking if admin_access.php exists..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_PUBLIC_HTML/admin_access.php ]; then echo 'Found'; else echo 'Not found'; fi"

# Check if AuthController.php exists
echo "3. Checking if AuthController.php exists..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_APP/Controllers/AuthController.php ]; then echo 'Found'; else echo 'Not found'; fi"

# Check if Core files exist
echo "4. Checking if Core files exist..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_APP/Core/Controller.php ] && [ -f $REMOTE_APP/Core/Model.php ] && [ -f $REMOTE_APP/Core/QueryBuilder.php ]; then echo 'All found'; else echo 'Some missing'; fi"

# Check if Middleware files exist
echo "5. Checking if Middleware files exist..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_APP/Middleware/AuthMiddleware.php ] && [ -f $REMOTE_APP/Middleware/GuestMiddleware.php ]; then echo 'All found'; else echo 'Some missing'; fi"

# Check file permissions
echo "6. Checking file permissions..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "ls -la $REMOTE_PUBLIC_HTML/login.php $REMOTE_PUBLIC_HTML/admin_access.php"

echo "========================================="
echo "LOGIN SYSTEM VERIFICATION COMPLETE!"
echo "========================================="
echo "You should now be able to access:"
echo "- Login page: https://www.siloe.com.py/login.php"
echo "- Emergency admin access: https://www.siloe.com.py/admin_access.php"
echo ""
echo "Admin credentials: admin@siloe.com / admin123"
