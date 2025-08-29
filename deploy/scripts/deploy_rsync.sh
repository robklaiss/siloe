#!/usr/bin/env bash
set -euo pipefail

# Rsync deploy script for Siloe
# Usage:
#   ./deploy/scripts/deploy_rsync.sh [--dry-run]
# Configure via env vars or edit defaults below.

# --- Config ---
: "${REMOTE:=siloecom@192.185.143.154}"  # SSH endpoint (cPanel)
: "${REMOTE_PATH:=/home/siloecom/public_html}"    # Remote project path (docroot -> public_html)
: "${SSH_OPTS:=-i ~/.ssh/siloe_ed25519}"          # SSH key for passwordless auth
: "${PHP_BIN:=php}"                       # Remote PHP binary (e.g., php8.2)

# --- Derived ---
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
EXCLUDES=(
  "--exclude" ".git"
  "--exclude" ".gitignore"
  "--exclude" "node_modules"
  "--exclude" "storage/*"            # keep server-generated storage
  "--exclude" "public/uploads/*"     # keep user uploads on server
  "--exclude" "database/*.db"       # don't overwrite SQLite DB
  "--exclude" "database/*.sqlite"  # don't overwrite SQLite DB
  "--exclude" "storage/logs/*"     # legacy explicit logs exclude
  "--exclude" ".DS_Store"
)

DRY_RUN=""
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN="--dry-run"
fi

echo "[deploy] Syncing $ROOT_DIR -> $REMOTE:$REMOTE_PATH"
rsync -azv $DRY_RUN --delete \
  -e "ssh $SSH_OPTS" \
  "${EXCLUDES[@]}" \
  "$ROOT_DIR/" "$REMOTE:$REMOTE_PATH/"

echo "[deploy] Sync complete. (dry-run=${DRY_RUN:+true})"

# Run post-deploy tasks remotely
echo "[deploy] Running migrations on remote"
ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_PATH' && $PHP_BIN run_latest_migration.php || $PHP_BIN database/init_db.php"
echo "[deploy] Setting permissions on remote"
ssh $SSH_OPTS "$REMOTE" "cd '$REMOTE_PATH' && bash deploy/scripts/set_permissions.sh"
