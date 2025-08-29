#!/usr/bin/env bash
set -euo pipefail

# Run database migrations for Siloe
# Usage: bash deploy/scripts/run_migrations.sh

ROOT_DIR=${ROOT_DIR:-"$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"}
PHP_BIN=${PHP_BIN:-php}

cd "$ROOT_DIR"

if [[ ! -f database/run-migrations.php ]]; then
  echo "[migrate] database/run-migrations.php not found" >&2
  exit 1
fi

echo "[migrate] Running migrations with $PHP_BIN"
$PHP_BIN database/run-migrations.php

echo "[migrate] Done."
