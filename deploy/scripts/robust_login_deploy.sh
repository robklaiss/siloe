#!/bin/bash

# Robust Login System Deployment Script
# This script deploys the exact login system from development to production
# with individual file transfers and error handling

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10 -o BatchMode=yes"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"
LOCAL_APP="$LOCAL_ROOT/app"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_PUBLIC="$REMOTE_ROOT/public"
REMOTE_APP="$REMOTE_ROOT/app"
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
NC='\033[0m' # No Color

# Function to upload a file with error handling
upload_file() {
    local src="$1"
    local dest="$2"
    local desc="$3"
    
    echo -e "${YELLOW}Uploading $desc...${NC}"
    
    # Make sure source file exists
    if [ ! -f "$src" ]; then
        echo -e "${RED}Error: Source file $src does not exist${NC}"
        return 1
    fi
    
    # Create destination directory if needed
    local dest_dir=$(dirname "$dest")
    ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $dest_dir" || {
        echo -e "${RED}Error: Could not create directory $dest_dir${NC}"
        return 1
    }
    
    # Upload file
    scp $SSH_OPTS "$src" "$SERVER_USER@$SERVER_HOST:$dest" || {
        echo -e "${RED}Error: Failed to upload $src to $dest${NC}"
        return 1
    }
    
    echo -e "${GREEN}Successfully uploaded $desc${NC}"
    return 0
}

# Function to run a command on the server
run_remote_cmd() {
    local cmd="$1"
    local desc="$2"
    
    echo -e "${YELLOW}$desc...${NC}"
    
    ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "$cmd" || {
        echo -e "${RED}Error: Failed to $desc${NC}"
        return 1
    }
    
    echo -e "${GREEN}Successfully $desc${NC}"
    return 0
}

echo "========================================="
echo "DEPLOYING EXACT LOGIN SYSTEM FROM DEVELOPMENT"
echo "========================================="

# Test SSH connection first
echo "Testing SSH connection..."
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "echo Connection successful" || {
    echo -e "${RED}Error: Could not establish SSH connection to $SERVER_USER@$SERVER_HOST${NC}"
    exit 1
}
echo -e "${GREEN}SSH connection successful${NC}"

# Upload login.php to both locations
upload_file "$LOCAL_PUBLIC/login.php" "$REMOTE_PUBLIC/login.php" "login.php to siloe/public"
upload_file "$LOCAL_PUBLIC/login.php" "$REMOTE_PUBLIC_HTML/login.php" "login.php to public_html"

# Upload AuthController.php
upload_file "$LOCAL_APP/Controllers/AuthController.php" "$REMOTE_APP/Controllers/AuthController.php" "AuthController.php"

# Upload middleware files
upload_file "$LOCAL_APP/Middleware/AuthMiddleware.php" "$REMOTE_APP/Middleware/AuthMiddleware.php" "AuthMiddleware.php"
upload_file "$LOCAL_APP/Middleware/GuestMiddleware.php" "$REMOTE_APP/Middleware/GuestMiddleware.php" "GuestMiddleware.php"

# Upload core files
upload_file "$LOCAL_APP/Core/Controller.php" "$REMOTE_APP/Core/Controller.php" "Controller.php"
upload_file "$LOCAL_APP/Core/Model.php" "$REMOTE_APP/Core/Model.php" "Model.php"
upload_file "$LOCAL_APP/Core/QueryBuilder.php" "$REMOTE_APP/Core/QueryBuilder.php" "QueryBuilder.php"

# Upload admin_access.php for emergency access
upload_file "$LOCAL_ROOT/admin_access.php" "$REMOTE_PUBLIC/admin_access.php" "admin_access.php to siloe/public"
upload_file "$LOCAL_ROOT/admin_access.php" "$REMOTE_PUBLIC_HTML/admin_access.php" "admin_access.php to public_html"

# Set permissions
run_remote_cmd "chmod 644 $REMOTE_PUBLIC/login.php $REMOTE_PUBLIC_HTML/login.php $REMOTE_PUBLIC/admin_access.php $REMOTE_PUBLIC_HTML/admin_access.php" "setting permissions for public files"
run_remote_cmd "find $REMOTE_APP -type d -exec chmod 755 {} \;" "setting permissions for app directories"
run_remote_cmd "find $REMOTE_APP -type f -exec chmod 644 {} \;" "setting permissions for app files"

# Verify files
run_remote_cmd "ls -la $REMOTE_PUBLIC/login.php $REMOTE_PUBLIC_HTML/login.php" "verifying login.php files"
run_remote_cmd "ls -la $REMOTE_APP/Controllers/AuthController.php" "verifying AuthController.php"

echo "========================================="
echo "LOGIN SYSTEM DEPLOYMENT COMPLETE!"
echo "========================================="
echo "You should now be able to access:"
echo "- Login page: https://www.siloe.com.py/login.php"
echo "- Emergency admin access: https://www.siloe.com.py/admin_access.php"
echo ""
echo "Admin credentials: admin@siloe.com / admin123"
