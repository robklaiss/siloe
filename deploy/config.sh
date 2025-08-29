#!/bin/bash
# Siloe deployment configuration file
# This file contains all the configuration settings for the deployment process

# Server details
REMOTE_USER="siloecom"
REMOTE_HOST="192.185.143.154"
REMOTE="${REMOTE_USER}@${REMOTE_HOST}"

# Remote paths
REMOTE_BASE_PATH="/home1/siloecom/siloe"
REMOTE_PUBLIC_PATH="${REMOTE_BASE_PATH}/public"
REMOTE_BACKUP_PATH="${REMOTE_BASE_PATH}/backups"

# SSH configuration
SSH_KEY_FILE="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i ${SSH_KEY_FILE} -o IdentitiesOnly=yes"
SSH_ALIAS="siloe"

# PHP configuration
PHP_BIN="php"

# Rsync options
RSYNC_OPTS="-azv --delete"
RSYNC_EXCLUDES=(
  "--exclude" ".git"
  "--exclude" ".gitignore"
  "--exclude" "node_modules"
  "--exclude" "storage/*"            # keep server-generated storage
  "--exclude" "public/uploads/*"     # keep user uploads on server
  "--exclude" "database/*.db"        # don't overwrite SQLite DB
  "--exclude" "database/*.sqlite"    # don't overwrite SQLite DB
  "--exclude" "storage/logs/*"       # legacy explicit logs exclude
  "--exclude" ".DS_Store"
  "--exclude" "backups"              # don't sync local backups
)

# Backup settings
BACKUP_ENABLED=true
BACKUP_KEEP_COUNT=5                  # Number of backups to keep
BACKUP_TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_NAME="siloe_backup_${BACKUP_TIMESTAMP}"

# Deployment settings
DEPLOYMENT_LOG_FILE="/tmp/siloe_deploy_${BACKUP_TIMESTAMP}.log"
DEPLOYMENT_TIMEOUT=300               # Timeout in seconds

# Migration settings
RUN_MIGRATIONS=true
MIGRATION_SCRIPT="run_latest_migration.php"
FALLBACK_MIGRATION_SCRIPT="database/init_db.php"

# Permissions
SET_PERMISSIONS=true
PERMISSION_SCRIPT="deploy/scripts/set_permissions.sh"
