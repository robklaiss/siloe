#!/bin/bash

# Direct upload script for minimal_index.php
# This script uploads the minimal_index.php file directly to the server as index.php

# Set variables
SSH_KEY="~/.ssh/siloe_ed25519"
SERVER="siloecom@192.185.143.154"
LOCAL_FILE="/Users/robinklaiss/Dev/siloe/minimal_index.php"
REMOTE_PATH="/home1/siloecom/public_html/index.php"
ADMIN_FILE="/Users/robinklaiss/Dev/siloe/admin_access.php"
REMOTE_ADMIN_PATH="/home1/siloecom/public_html/admin_access.php"

# Display header
echo "========================================"
echo "DIRECT UPLOAD TO SILOE SERVER"
echo "========================================"

# Backup existing index.php
echo "1. Creating backup of existing index.php..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes $SERVER "if [ -f /home1/siloecom/public_html/index.php ]; then cp /home1/siloecom/public_html/index.php /home1/siloecom/public_html/index.php.bak; echo 'Backup created'; else echo 'No index.php found to backup'; fi"

# Upload minimal_index.php as index.php
echo "2. Uploading minimal_index.php as index.php..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_FILE" "$SERVER:$REMOTE_PATH"

# Upload admin_access.php
echo "3. Uploading admin_access.php..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$ADMIN_FILE" "$SERVER:$REMOTE_ADMIN_PATH"

# Set permissions
echo "4. Setting permissions..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes $SERVER "chmod 644 $REMOTE_PATH; chmod 644 $REMOTE_ADMIN_PATH"

# Verify files
echo "5. Verifying files..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes $SERVER "ls -la $REMOTE_PATH; ls -la $REMOTE_ADMIN_PATH"

echo "========================================"
echo "UPLOAD COMPLETE!"
echo "========================================"
echo "The site should now be accessible at:"
echo "- Main site: https://www.siloe.com.py/"
echo "- Admin access: https://www.siloe.com.py/admin_access.php"
echo ""
echo "Admin credentials:"
echo "- Email: admin@example.com"
echo "- Password: Admin123!"
