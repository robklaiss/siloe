#!/bin/bash

# Deploy Core and Middleware Script
# This script focuses on deploying the Core and Middleware files

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
echo "DEPLOYING CORE AND MIDDLEWARE FILES"
echo "========================================="

# Deploy Core files
echo "1. Setting up Core directory..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Core"
if [ $? -eq 0 ]; then
    echo "✅ Successfully created Core directory"
else
    echo "❌ Failed to create Core directory"
    exit 1
fi

echo "2. Uploading Controller.php..."
scp $SSH_OPTS "$LOCAL_APP/Core/Controller.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded Controller.php"
else
    echo "❌ Failed to upload Controller.php"
fi

echo "3. Uploading Model.php..."
scp $SSH_OPTS "$LOCAL_APP/Core/Model.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded Model.php"
else
    echo "❌ Failed to upload Model.php"
fi

echo "4. Uploading QueryBuilder.php..."
scp $SSH_OPTS "$LOCAL_APP/Core/QueryBuilder.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded QueryBuilder.php"
else
    echo "❌ Failed to upload QueryBuilder.php"
fi

# Deploy Middleware files
echo "5. Setting up Middleware directory..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Middleware"
if [ $? -eq 0 ]; then
    echo "✅ Successfully created Middleware directory"
else
    echo "❌ Failed to create Middleware directory"
    exit 1
fi

echo "6. Uploading AuthMiddleware.php..."
scp $SSH_OPTS "$LOCAL_APP/Middleware/AuthMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Middleware/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded AuthMiddleware.php"
else
    echo "❌ Failed to upload AuthMiddleware.php"
fi

echo "7. Uploading GuestMiddleware.php..."
scp $SSH_OPTS "$LOCAL_APP/Middleware/GuestMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Middleware/"
if [ $? -eq 0 ]; then
    echo "✅ Successfully uploaded GuestMiddleware.php"
else
    echo "❌ Failed to upload GuestMiddleware.php"
fi

# Set permissions
echo "8. Setting permissions..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "find $REMOTE_APP/Core -type f -exec chmod 644 {} \; && find $REMOTE_APP/Middleware -type f -exec chmod 644 {} \;"
if [ $? -eq 0 ]; then
    echo "✅ Successfully set permissions"
else
    echo "❌ Failed to set permissions"
fi

echo "========================================="
echo "CORE AND MIDDLEWARE DEPLOYMENT COMPLETE!"
echo "========================================="
