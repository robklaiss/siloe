#!/bin/bash

# Set variables
DB_FILE="/home/siloecom/public_html/database/siloe.db"
BACKUP_DIR="/home/siloecom/backups"
TEMP_DIR="/tmp/siloe_restore_$(date +%s)"

# Create temp directory
mkdir -p "$TEMP_DIR"

# Check for existing database backup
if [ -f "$BACKUP_DIR/siloe.db.backup" ]; then
    echo "Restoring database from backup..."
    cp "$BACKUP_DIR/siloe.db.backup" "$DB_FILE"
    chmod 644 "$DB_FILE"
else
    echo "No backup found, creating new database..."
    # Create database directory if it doesn't exist
    mkdir -p "$(dirname "$DB_FILE")"
    
    # Create empty database file
    touch "$DB_FILE"
    chmod 644 "$DB_FILE"
    
    # Run migrations
    php /home/siloecom/public_html/database/migrations/2025_08_25_restore_auth_tables.php
fi

# Set proper permissions
chown -R siloecom:siloecom "$(dirname "$DB_FILE")"
chmod 755 "$(dirname "$DB_FILE")"

# Verify database is accessible
if [ -f "$DB_FILE" ]; then
    echo "Database restored successfully to: $DB_FILE"
    ls -la "$DB_FILE"
else
    echo "Error: Failed to restore database"
    exit 1
fi

echo "Auth system restoration complete!"

# Cleanup
rm -rf "$TEMP_DIR"
