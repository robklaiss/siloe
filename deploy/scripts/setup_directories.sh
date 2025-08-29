#!/bin/bash

# Script to set up necessary directories on the server and upload minimal index.php

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"

echo "Setting up directories on the server..."

# Create directories on the server
ssh -o PreferredAuthentications=password $SERVER << 'ENDSSH'
# Create necessary directories
mkdir -p /home1/siloecom/siloe/public
mkdir -p /home1/siloecom/siloe/database

# Check if public_html is a symlink and fix it
if [ -L /home1/siloecom/public_html ]; then
    echo "public_html is a symlink, checking target..."
    TARGET=$(readlink /home1/siloecom/public_html)
    echo "Current target: $TARGET"
    
    if [ "$TARGET" != "/home1/siloecom/siloe/public" ]; then
        echo "Fixing symlink..."
        rm /home1/siloecom/public_html
        ln -s /home1/siloecom/siloe/public /home1/siloecom/public_html
    fi
else
    echo "public_html is not a symlink, creating backup and setting up symlink..."
    if [ -d /home1/siloecom/public_html ]; then
        mv /home1/siloecom/public_html /home1/siloecom/public_html_backup_$(date +%s)
    fi
    ln -s /home1/siloecom/siloe/public /home1/siloecom/public_html
fi

# Set proper permissions
chmod -R 755 /home1/siloecom/siloe
chmod -R 755 /home1/siloecom/public_html

echo "Directory setup complete!"
ENDSSH

# Upload minimal index.php
echo "Uploading minimal index.php..."
scp -o PreferredAuthentications=password /Users/robinklaiss/Dev/siloe/minimal_index.php $SERVER:/home1/siloecom/siloe/public/index.php

echo "Done!"
echo "You can now access the site at: http://www.siloe.com.py/"
