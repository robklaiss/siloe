#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"
REMOTE_DIR="/home1/siloecom/siloe/public/public"  # Correct public directory
LOCAL_FILE="/Users/robinklaiss/Dev/siloe/fix_auth_system.php"

echo "Uploading auth fix to correct public directory..."

# Upload the fix file
scp -o PreferredAuthentications=password $LOCAL_FILE $SERVER:$REMOTE_DIR/

# Set proper permissions
ssh -o PreferredAuthentications=password $SERVER "chmod 644 $REMOTE_DIR/fix_auth_system.php"

echo "Upload complete!"
echo "Access the fix script at: http://www.siloe.com.py/fix_auth_system.php"
