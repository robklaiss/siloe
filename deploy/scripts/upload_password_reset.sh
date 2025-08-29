#!/bin/bash

# Upload password reset script to the server
# This script uploads the admin_password_reset.php file to the production server

echo "Uploading password reset script to server..."

# Check if the password reset file exists
if [ ! -f "/Users/robinklaiss/Dev/siloe/admin_password_reset.php" ]; then
    echo "Error: Password reset file not found!"
    exit 1
fi

# Upload the file using scp
scp /Users/robinklaiss/Dev/siloe/admin_password_reset.php siloecom@192.185.143.154:/home1/siloecom/public_html/

# Check if upload was successful
if [ $? -eq 0 ]; then
    echo "✅ Password reset script uploaded successfully!"
    echo "Access it at: http://www.siloe.com.py/admin_password_reset.php"
    echo "After using it, make sure to delete it from the server for security."
else
    echo "❌ Failed to upload password reset script."
    echo "You may need to manually upload the file using FTP or cPanel."
    echo "Upload /Users/robinklaiss/Dev/siloe/admin_password_reset.php to /home1/siloecom/public_html/"
fi
