#!/bin/bash

# Upload direct login fix script to the server
# This script uses a simple approach to avoid SSH issues

echo "Uploading direct login fix script..."

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SERVER_WEB_ROOT="/home1/siloecom/public_html"

# Check if the direct login fix script exists
if [ ! -f "/Users/robinklaiss/Dev/siloe/direct_login_fix.php" ]; then
    echo "Error: Direct login fix script not found!"
    exit 1
fi

# Upload the script using scp with password authentication
echo "Uploading direct_login_fix.php to server..."
scp -o PreferredAuthentications=password "/Users/robinklaiss/Dev/siloe/direct_login_fix.php" "$SERVER:$SERVER_WEB_ROOT/"

echo "Done!"
echo "Access the direct login fix script at: http://www.siloe.com.py/direct_login_fix.php"
echo "After using it, make sure to delete it from the server for security."
