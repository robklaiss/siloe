#!/usr/bin/env bash
set -euo pipefail

# Set filesystem permissions for Siloe (safely)
# Usage: bash deploy/scripts/set_permissions.sh
# Optional env: PROJECT_ROOT, CHOWN_USER, CHOWN_GROUP

PROJECT_ROOT=${PROJECT_ROOT:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"}
CHOWN_USER=${CHOWN_USER:-}
CHOWN_GROUP=${CHOWN_GROUP:-}

cd "$PROJECT_ROOT"

echo "[perm] Project root: $PROJECT_ROOT"

mkdir -p storage/logs public/uploads public/uploads/logos database

# Directories writable by web server
chmod 775 storage storage/logs public/uploads public/uploads/logos database || true

# Ensure SQLite file exists with sane perms (if you want to pre-create it)
if [[ ! -f database/siloe.db ]]; then
  echo "[perm] Creating database/siloe.db"
  : > database/siloe.db || true
fi
chmod 664 database/siloe.db || true

# Optional ownership (only if explicitly set)
if [[ -n "$CHOWN_USER" ]]; then
  echo "[perm] Changing owner to $CHOWN_USER:${CHOWN_GROUP:-$CHOWN_USER}"
  chown -R "$CHOWN_USER:${CHOWN_GROUP:-$CHOWN_USER}" storage public/uploads database || true
fi

echo "[perm] Done. Verify web server user can write to storage/, database/, and public/uploads/."
