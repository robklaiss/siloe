#!/bin/bash

# Colors for output
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
RED="\033[0;31m"
BLUE="\033[0;34m"
NC="\033[0m" # No Color

# Configuration
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SSH_KEY_FILE="$HOME/.ssh/siloe_ed25519"
SSH_OPTS="-i $SSH_KEY_FILE -o IdentitiesOnly=yes"

# Log function
log() {
    local level=$1
    local message=$2
    local color=$NC
    
    case $level in
        "INFO") color=$BLUE ;;
        "SUCCESS") color=$GREEN ;;
        "WARNING") color=$YELLOW ;;
        "ERROR") color=$RED ;;
    esac
    
    echo -e "${color}[$level] $message${NC}"
}

log "INFO" "Checking server structure and system status..."

# Check SSH connection
log "INFO" "Testing SSH connection..."
if ssh $SSH_OPTS -o ConnectTimeout=5 $SERVER "echo 'Connection successful'" &>/dev/null; then
    log "SUCCESS" "SSH connection successful"
else
    log "ERROR" "SSH connection failed"
    exit 1
fi

# Check directory structure
log "INFO" "Checking directory structure..."
DIRECTORIES=(
    "/home1/siloecom/siloe"
    "/home1/siloecom/siloe/public"
    "/home1/siloecom/siloe/database"
    "/home1/siloecom/siloe/database/migrations"
    "/home1/siloecom/siloe/storage"
    "/home1/siloecom/siloe/public/uploads"
)

for dir in "${DIRECTORIES[@]}"; do
    if ssh $SSH_OPTS $SERVER "[ -d '$dir' ]"; then
        log "SUCCESS" "Directory exists: $dir"
    else
        log "ERROR" "Directory does not exist: $dir"
    fi
done

# Check database file
log "INFO" "Checking database file..."
if ssh $SSH_OPTS $SERVER "[ -f '/home1/siloecom/siloe/database/database.sqlite' ]"; then
    log "SUCCESS" "Database file exists"
else
    log "ERROR" "Database file does not exist"
fi

# Check migration files
log "INFO" "Checking migration files..."
MIGRATION_FILES=(
    "20250614151505_add_contact_and_status_fields_to_companies_table.php"
    "20250614170100_add_logo_to_companies_table.php"
    "2025_07_15_000000_create_weekly_menu_items_table.php"
)

for file in "${MIGRATION_FILES[@]}"; do
    if ssh $SSH_OPTS $SERVER "[ -f '/home1/siloecom/siloe/database/migrations/$file' ]"; then
        log "SUCCESS" "Migration file exists: $file"
    else
        log "ERROR" "Migration file does not exist: $file"
    fi
done

# Create a PHP test script to check database tables
TMP_TEST_SCRIPT=$(mktemp)
cat > "$TMP_TEST_SCRIPT" << 'EOF'
<?php
// Database test script
$dbPath = __DIR__ . '/database/database.sqlite';

if (file_exists($dbPath)) {
    echo "Database file exists\n";
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Database connection successful\n";
        
        // Check tables
        $tables = ['users', 'companies', 'weekly_menu_items', 'employee_menu_selections', 'delete_requests'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                // Count records
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
                $countStmt->execute();
                $count = $countStmt->fetchColumn();
                
                echo "Table $table exists ($count records)\n";
            } else {
                echo "Table $table does not exist\n";
            }
        }
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Database file does not exist at: $dbPath\n";
}
EOF

# Upload and run the test script
log "INFO" "Uploading and running database test script..."
scp $SSH_OPTS "$TMP_TEST_SCRIPT" "$SERVER:/home1/siloecom/siloe/db_test.php"
ssh $SSH_OPTS $SERVER "cd /home1/siloecom/siloe && php db_test.php"

# Check web server symlink
log "INFO" "Checking web server symlink..."
ssh $SSH_OPTS $SERVER "ls -la /home1/siloecom/public_html"

# Clean up
log "INFO" "Cleaning up..."
rm "$TMP_TEST_SCRIPT"
ssh $SSH_OPTS $SERVER "rm /home1/siloecom/siloe/db_test.php"

log "SUCCESS" "Server structure check complete"
log "INFO" "You can now access your application at http://siloecom.com"
log "INFO" "To verify the system is working, visit http://siloecom.com/system_test.php"
