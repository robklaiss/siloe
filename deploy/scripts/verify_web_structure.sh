#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Verifying web server structure..."

# Create a very simple test file
echo "<?php echo 'PHP is working!'; ?>" > /tmp/test.php

# Try to find the actual document root by checking Apache/cPanel configuration
echo "Checking for document root in server configuration..."
ssh -o PreferredAuthentications=password $SERVER "grep -r 'DocumentRoot' /etc/apache2/sites-enabled/ 2>/dev/null || grep -r 'DocumentRoot' /etc/httpd/conf/httpd.conf 2>/dev/null || echo 'Cannot find DocumentRoot'"

# List the contents of various possible web directories
echo -e "\nListing contents of possible web directories:"
ssh -o PreferredAuthentications=password $SERVER "ls -la /home1/siloecom/siloe/public/ 2>/dev/null || echo 'Cannot access /home1/siloecom/siloe/public/'"

# Upload test file to multiple locations
echo -e "\nUploading test file to multiple locations..."
scp -o PreferredAuthentications=password /tmp/test.php $SERVER:/home1/siloecom/siloe/public/
scp -o PreferredAuthentications=password /tmp/test.php $SERVER:/home1/siloecom/siloe/
scp -o PreferredAuthentications=password /tmp/test.php $SERVER:/home/siloecom/

# Check if our fix_auth_system.php file exists in the expected location
echo -e "\nChecking if fix_auth_system.php exists in the expected location:"
ssh -o PreferredAuthentications=password $SERVER "ls -la /home1/siloecom/siloe/public/fix_auth_system.php 2>/dev/null || echo 'File not found'"

# Check server error logs
echo -e "\nChecking server error logs (last 10 lines):"
ssh -o PreferredAuthentications=password $SERVER "tail -10 /home1/siloecom/siloe/public/error_log 2>/dev/null || tail -10 /home/siloecom/logs/error_log 2>/dev/null || echo 'Cannot access error logs'"

echo -e "\nDone verifying web structure."
