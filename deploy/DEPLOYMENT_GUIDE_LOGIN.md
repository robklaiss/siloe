# Siloe Login System Deployment Guide

This guide documents the process of deploying the Siloe login system to production, including the specific files needed, deployment scripts, and troubleshooting steps.

## Server Information

- **SSH User**: siloecom
- **Server IP**: 192.185.143.154
- **Web Root**: /home1/siloecom/siloe/public
- **Public HTML**: /home1/siloecom/public_html (symlinked to siloe/public)
- **Admin Credentials**: admin@siloe.com / admin123

## Critical Login System Files

The following files are essential for the login system to function correctly:

1. **Public Files**:
   - `login.php` - Main login page
   - `admin_access.php` - Emergency admin access page

2. **Controllers**:
   - `app/Controllers/AuthController.php` - Handles authentication logic

3. **Core System Files**:
   - `app/Core/Controller.php` - Base controller class
   - `app/Core/Model.php` - Base model class
   - `app/Core/QueryBuilder.php` - Database query builder

4. **Middleware**:
   - `app/Middleware/AuthMiddleware.php` - Authentication middleware
   - `app/Middleware/GuestMiddleware.php` - Guest access middleware

## Deployment Scripts

We've created several deployment scripts to handle different aspects of the login system deployment:

### 1. Focused Login Files Deployment

`deploy_login_only.sh` - Deploys the essential public login files:
```bash
#!/bin/bash
# Deploy Login Files Only Script
# This script focuses only on the essential login files

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_PUBLIC="$LOCAL_ROOT/public"

# Remote paths
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"

# Upload login.php to public_html
scp $SSH_OPTS "$LOCAL_PUBLIC/login.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"

# Upload admin_access.php to public_html
scp $SSH_OPTS "$LOCAL_ROOT/admin_access.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_PUBLIC_HTML/"

# Set permissions
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "chmod 644 $REMOTE_PUBLIC_HTML/login.php $REMOTE_PUBLIC_HTML/admin_access.php"
```

### 2. AuthController Deployment

`deploy_auth_controller.sh` - Deploys the authentication controller:
```bash
#!/bin/bash
# Deploy AuthController Script

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Local paths
LOCAL_APP="/Users/robinklaiss/Dev/siloe/app"

# Remote paths
REMOTE_APP="/home1/siloecom/siloe/app"

# Create Controllers directory if it doesn't exist
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Controllers"

# Upload AuthController.php
scp $SSH_OPTS "$LOCAL_APP/Controllers/AuthController.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Controllers/"

# Set permissions
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "chmod 644 $REMOTE_APP/Controllers/AuthController.php"
```

### 3. Core and Middleware Deployment

`deploy_core_middleware.sh` - Deploys the core system files and middleware:
```bash
#!/bin/bash
# Deploy Core and Middleware Script

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Local paths
LOCAL_APP="/Users/robinklaiss/Dev/siloe/app"

# Remote paths
REMOTE_APP="/home1/siloecom/siloe/app"

# Deploy Core files
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Core"
scp $SSH_OPTS "$LOCAL_APP/Core/Controller.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
scp $SSH_OPTS "$LOCAL_APP/Core/Model.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"
scp $SSH_OPTS "$LOCAL_APP/Core/QueryBuilder.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Core/"

# Deploy Middleware files
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "mkdir -p $REMOTE_APP/Middleware"
scp $SSH_OPTS "$LOCAL_APP/Middleware/AuthMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Middleware/"
scp $SSH_OPTS "$LOCAL_APP/Middleware/GuestMiddleware.php" "$SERVER_USER@$SERVER_HOST:$REMOTE_APP/Middleware/"

# Set permissions
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "find $REMOTE_APP/Core -type f -exec chmod 644 {} \; && find $REMOTE_APP/Middleware -type f -exec chmod 644 {} \;"
```

### 4. Verification Script

`verify_login_system.sh` - Verifies that all login system files are deployed correctly:
```bash
#!/bin/bash
# Verify Login System Script

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"

# Remote paths
REMOTE_PUBLIC_HTML="/home1/siloecom/public_html"
REMOTE_APP="/home1/siloecom/siloe/app"

# Check if login.php exists
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_PUBLIC_HTML/login.php ]; then echo 'Found'; else echo 'Not found'; fi"

# Check if admin_access.php exists
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_PUBLIC_HTML/admin_access.php ]; then echo 'Found'; else echo 'Not found'; fi"

# Check if AuthController.php exists
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_APP/Controllers/AuthController.php ]; then echo 'Found'; else echo 'Not found'; fi"

# Check if Core files exist
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_APP/Core/Controller.php ] && [ -f $REMOTE_APP/Core/Model.php ] && [ -f $REMOTE_APP/Core/QueryBuilder.php ]; then echo 'All found'; else echo 'Some missing'; fi"

# Check if Middleware files exist
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "if [ -f $REMOTE_APP/Middleware/AuthMiddleware.php ] && [ -f $REMOTE_APP/Middleware/GuestMiddleware.php ]; then echo 'All found'; else echo 'Some missing'; fi"

# Check file permissions
ssh $SSH_OPTS "$SERVER_USER@$SERVER_HOST" "ls -la $REMOTE_PUBLIC_HTML/login.php $REMOTE_PUBLIC_HTML/admin_access.php"
```

## Deployment Process

To deploy the login system to production, follow these steps:

1. **Deploy Public Login Files**:
   ```bash
   ./deploy_login_only.sh
   ```

2. **Deploy AuthController**:
   ```bash
   ./deploy_auth_controller.sh
   ```

3. **Deploy Core and Middleware Files**:
   ```bash
   ./deploy_core_middleware.sh
   ```

4. **Verify Deployment**:
   ```bash
   ./verify_login_system.sh
   ```

## Troubleshooting

### SSH Connection Issues

If you encounter SSH connection issues during deployment:

1. **Split Deployment into Smaller Scripts**:
   - Use the individual component scripts instead of the comprehensive script
   - This helps avoid SSH connection timeouts or drops during large transfers

2. **SSH Connection Debugging**:
   - Add `-v` to SSH options for verbose output: `SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10 -v"`
   - Check server logs: `/var/log/secure` or `/var/log/auth.log`

3. **SSH Key Authentication**:
   - Ensure the SSH key has correct permissions: `chmod 600 ~/.ssh/siloe_ed25519`
   - Verify the key is added to the server's authorized_keys file

### File Permission Issues

If files are not accessible after deployment:

1. **Check File Permissions**:
   ```bash
   ssh siloecom@192.185.143.154 "ls -la /home1/siloecom/public_html/login.php"
   ```

2. **Set Correct Permissions**:
   ```bash
   ssh siloecom@192.185.143.154 "chmod 644 /home1/siloecom/public_html/login.php"
   ```

### Login System Not Working

If the login system is still not working after deployment:

1. **Check PHP Error Logs**:
   ```bash
   ssh siloecom@192.185.143.154 "tail -n 50 /home1/siloecom/logs/error_log"
   ```

2. **Verify File Contents**:
   ```bash
   ssh siloecom@192.185.143.154 "cat /home1/siloecom/public_html/login.php"
   ssh siloecom@192.185.143.154 "cat /home1/siloecom/siloe/app/Controllers/AuthController.php"
   ```

3. **Use Emergency Admin Access**:
   - Access https://www.siloe.com.py/admin_access.php
   - Login with admin credentials: admin@siloe.com / admin123

## Conclusion

This deployment guide provides a comprehensive approach to deploying the Siloe login system to production. By following these steps and using the provided scripts, you can ensure a successful deployment and quickly troubleshoot any issues that may arise.
