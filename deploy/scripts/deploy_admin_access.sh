#!/bin/bash

# Deploy admin_access.php to the server
# This script will upload the admin_access.php file to the server
# and ensure it's placed in the correct location

# Set variables
SERVER_IP="192.185.143.154"
SERVER_USER="siloecom"
SSH_KEY="~/.ssh/siloe_ed25519"
LOCAL_FILE="/Users/robinklaiss/Dev/siloe/admin_access.php"
REMOTE_PATH="/home1/siloecom/public_html/"

# Display info
echo "Deploying admin_access.php to server..."
echo "Server: $SERVER_USER@$SERVER_IP"
echo "Local file: $LOCAL_FILE"
echo "Remote path: $REMOTE_PATH"

# Upload the file
echo "Uploading file..."
scp -i $SSH_KEY -o IdentitiesOnly=yes "$LOCAL_FILE" "$SERVER_USER@$SERVER_IP:$REMOTE_PATH"

# Check if upload was successful
if [ $? -eq 0 ]; then
    echo "✅ Upload successful!"
    echo "Admin access is now available at: https://www.siloe.com.py/admin_access.php"
else
    echo "❌ Upload failed!"
    exit 1
fi

# Set permissions
echo "Setting permissions..."
ssh -i $SSH_KEY -o IdentitiesOnly=yes "$SERVER_USER@$SERVER_IP" "chmod 644 ${REMOTE_PATH}admin_access.php"

echo "✅ Deployment complete!"
echo "You can now access the admin panel at: https://www.siloe.com.py/admin_access.php"
