#!/bin/bash
# Script to fix database issues on the production server

# Colors for output
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
RED="\033[0;31m"
BLUE="\033[0;34m"
NC="\033[0m" # No Color

# Server details
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

log "INFO" "Starting database fix process..."

# Create a temporary PHP script to initialize the database
TMP_INIT_SCRIPT=$(mktemp)
cat > "$TMP_INIT_SCRIPT" << 'EOF'
<?php
// Database initialization script

// Define database path
$dbPath = __DIR__ . '/database/database.sqlite';
$dbDir = dirname($dbPath);

// Create database directory if it doesn't exist
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "Created database directory: $dbDir\n";
}

// Check if database file exists
if (!file_exists($dbPath)) {
    // Create empty database file
    file_put_contents($dbPath, '');
    echo "Created empty database file: $dbPath\n";
    
    // Connect to the database
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Successfully connected to database\n";
        
        // Create users table
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT "user",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        echo "Created users table\n";
        
        // Create companies table
        $pdo->exec('CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            address TEXT,
            phone TEXT,
            email TEXT,
            website TEXT,
            contact_name TEXT,
            contact_phone TEXT,
            contact_email TEXT,
            status TEXT DEFAULT "active",
            logo TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        echo "Created companies table\n";
        
        // Create weekly_menu_items table
        $pdo->exec('CREATE TABLE IF NOT EXISTS weekly_menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            image TEXT,
            category TEXT,
            day_of_week TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        echo "Created weekly_menu_items table\n";
        
        // Create employee_menu_selections table
        $pdo->exec('CREATE TABLE IF NOT EXISTS employee_menu_selections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            menu_item_id INTEGER NOT NULL,
            selection_date DATE NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        echo "Created employee_menu_selections table\n";
        
        // Create delete_requests table
        $pdo->exec('CREATE TABLE IF NOT EXISTS delete_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            reason TEXT,
            status TEXT DEFAULT "pending",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        echo "Created delete_requests table\n";
        
        // Create admin user if none exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = "admin"');
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount == 0) {
            // Create default admin user
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->execute(['Admin User', 'admin@siloe.com', $password, 'admin']);
            echo "Created default admin user (email: admin@siloe.com, password: admin123)\n";
        } else {
            echo "Admin user already exists\n";
        }
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Database file already exists: $dbPath\n";
    
    // Connect to the database to verify it's working
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Successfully connected to existing database\n";
        
        // Check if tables exist
        $tables = ['users', 'companies', 'weekly_menu_items', 'employee_menu_selections', 'delete_requests'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$table]);
            if (!$stmt->fetch()) {
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            echo "Missing tables: " . implode(', ', $missingTables) . "\n";
            echo "Database structure is incomplete. Please run the full initialization script.\n";
        } else {
            echo "All required tables exist\n";
        }
        
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Database initialization complete\n";
EOF

log "INFO" "Uploading database fix script to server..."
scp $SSH_OPTS "$TMP_INIT_SCRIPT" "$SERVER:~/siloe/init_db.php"

log "INFO" "Running database initialization script on server..."
ssh $SSH_OPTS "$SERVER" "cd ~/siloe && php init_db.php"

log "INFO" "Cleaning up temporary files..."
rm "$TMP_INIT_SCRIPT"

log "SUCCESS" "Database initialization complete!"

# Now run the migrations
log "INFO" "Running migrations on server..."
ssh $SSH_OPTS "$SERVER" "cd ~/siloe && php database/migrations/20250614151505_add_contact_and_status_fields_to_companies_table.php"
ssh $SSH_OPTS "$SERVER" "cd ~/siloe && php database/migrations/20250614170100_add_logo_to_companies_table.php"
ssh $SSH_OPTS "$SERVER" "cd ~/siloe && php database/migrations/2025_07_15_000000_create_weekly_menu_items_table.php"

log "INFO" "Setting proper permissions..."
ssh $SSH_OPTS "$SERVER" "chmod -R 755 ~/siloe && chmod -R 777 ~/siloe/database ~/siloe/storage ~/siloe/public/uploads"

log "SUCCESS" "Database fix process complete!"
log "INFO" "You can now access your application at http://siloecom.com"
