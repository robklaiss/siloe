#!/bin/bash

# Deploy AuthController Script
# This script focuses only on deploying the AuthController.php file

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_APP="$LOCAL_ROOT/app"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_APP="$REMOTE_ROOT/app"

echo "========================================="
echo "DEPLOYING AUTHCONTROLLER.PHP"
echo "========================================="

# Create Controllers directory if it doesn't exist
echo "1. Creating Controllers directory..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Controllers"
if [ $? -eq 0 ]; then
    echo "✅ Successfully created Controllers directory"
else
    echo "❌ Failed to create Controllers directory"
fi

# Upload AuthController.php
echo "2. Uploading AuthController.php..."
scp $SSH_OPTS "$LOCAL_APP/Controllers/AuthController.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Controllers/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded AuthController.php"
else
    echo "❌ Failed to upload AuthController.php"
fi

# Set permissions
echo "3. Setting permissions..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "chmod 644 $REMOTE_APP/Controllers/AuthController.php"
if [ $? -eq 0 ]; then
    echo "✅ Successfully set permissions"
else
    echo "❌ Failed to set permissions"
fi

echo "========================================="
echo "AUTHCONTROLLER DEPLOYMENT COMPLETE!"
echo "========================================="
