#!/bin/bash

# Targeted deployment for Menu and HR fixes
# - MenuController.php
# - CompanyController.php (Admin)
# - Views for menus/create, orders/show, admin/dashboard, hr/menu_selections/today
# - Models: Company.php, User.php (DB connection fix)

set -e

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SSH_KEY="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY -o IdentitiesOnly=yes -o ConnectTimeout=10"
SERVER="$SERVER_USER@$SERVER_HOST"

# Local paths
LOCAL_ROOT="/Users/robinklaiss/Dev/siloe"
LOCAL_APP="$LOCAL_ROOT/app"
LOCAL_CONTROLLERS="$LOCAL_APP/Controllers"
LOCAL_CONTROLLERS_ADMIN="$LOCAL_APP/Controllers/Admin"
LOCAL_MODELS="$LOCAL_APP/Models"
LOCAL_VIEWS="$LOCAL_APP/views"

# Remote paths
REMOTE_ROOT="/home1/siloecom/siloe"
REMOTE_APP="$REMOTE_ROOT/app"
REMOTE_CONTROLLERS="$REMOTE_APP/Controllers"
REMOTE_CONTROLLERS_ADMIN="$REMOTE_APP/Controllers/Admin"
REMOTE_MODELS="$REMOTE_APP/Models"
REMOTE_VIEWS="$REMOTE_APP/views"

printf "\n===========================================\n"
printf "Deploying Menu + HR fixes to production\n"
printf "===========================================\n\n"

# 0) Sanity checks
for f in \
  "$LOCAL_CONTROLLERS/MenuController.php" \
  "$LOCAL_CONTROLLERS_ADMIN/CompanyController.php" \
  "$LOCAL_MODELS/Company.php" \
  "$LOCAL_MODELS/User.php" \
  "$LOCAL_VIEWS/menus/create.php" \
  "$LOCAL_VIEWS/orders/show.php" \
  "$LOCAL_VIEWS/admin/dashboard.php" \
  "$LOCAL_VIEWS/hr/menu_selections/today.php"; do
  if [ ! -f "$f" ]; then
    echo "Missing local file: $f" >&2
    exit 1
  fi
done

echo "1) Ensuring remote directories exist..."
ssh $SSH_OPTS "$SERVER" "mkdir -p \"$REMOTE_CONTROLLERS_ADMIN\" \"$REMOTE_MODELS\" \"$REMOTE_VIEWS/menus\" \"$REMOTE_VIEWS/orders\" \"$REMOTE_VIEWS/admin\" \"$REMOTE_VIEWS/hr/menu_selections\""

# 1) Upload controllers
printf "\n2) Uploading controllers...\n"
scp $SSH_OPTS "$LOCAL_CONTROLLERS/MenuController.php" "$SERVER:$REMOTE_CONTROLLERS/"
scp $SSH_OPTS "$LOCAL_CONTROLLERS_ADMIN/CompanyController.php" "$SERVER:$REMOTE_CONTROLLERS_ADMIN/"

# 2) Upload models
printf "\n3) Uploading models...\n"
scp $SSH_OPTS "$LOCAL_MODELS/Company.php" "$SERVER:$REMOTE_MODELS/"
scp $SSH_OPTS "$LOCAL_MODELS/User.php" "$SERVER:$REMOTE_MODELS/"

# 3) Upload views
printf "\n4) Uploading views...\n"
scp $SSH_OPTS "$LOCAL_VIEWS/menus/create.php" "$SERVER:$REMOTE_VIEWS/menus/"
scp $SSH_OPTS "$LOCAL_VIEWS/orders/show.php" "$SERVER:$REMOTE_VIEWS/orders/"
scp $SSH_OPTS "$LOCAL_VIEWS/admin/dashboard.php" "$SERVER:$REMOTE_VIEWS/admin/"
scp $SSH_OPTS "$LOCAL_VIEWS/hr/menu_selections/today.php" "$SERVER:$REMOTE_VIEWS/hr/menu_selections/"

# 4) Set permissions
printf "\n5) Setting permissions...\n"
ssh $SSH_OPTS "$SERVER" "\
  chmod 644 \"$REMOTE_CONTROLLERS/MenuController.php\" && \
  chmod 644 \"$REMOTE_CONTROLLERS_ADMIN/CompanyController.php\" && \
  chmod 644 \"$REMOTE_MODELS/Company.php\" \"$REMOTE_MODELS/User.php\" && \
  chmod 644 \"$REMOTE_VIEWS/menus/create.php\" \"$REMOTE_VIEWS/orders/show.php\" \"$REMOTE_VIEWS/admin/dashboard.php\" \"$REMOTE_VIEWS/hr/menu_selections/today.php\" \
"

# 5) Verify
printf "\n6) Verifying uploaded files...\n"
ssh $SSH_OPTS "$SERVER" "\
  ls -la \"$REMOTE_CONTROLLERS/MenuController.php\"; \
  ls -la \"$REMOTE_CONTROLLERS_ADMIN/CompanyController.php\"; \
  ls -la \"$REMOTE_MODELS/Company.php\"; \
  ls -la \"$REMOTE_MODELS/User.php\"; \
  ls -la \"$REMOTE_VIEWS/menus/create.php\"; \
  ls -la \"$REMOTE_VIEWS/orders/show.php\"; \
  ls -la \"$REMOTE_VIEWS/admin/dashboard.php\"; \
  ls -la \"$REMOTE_VIEWS/hr/menu_selections/today.php\" \
"

printf "\n===========================================\n"
printf "Deployment completed.\n"
printf "===========================================\n\n"
