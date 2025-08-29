#!/bin/bash
# Complete fix script for Siloe production system

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

log "INFO" "Starting complete fix process..."

# Step 1: Create necessary directories on the server
log "INFO" "Creating necessary directories on the server..."
ssh $SSH_OPTS "$SERVER" "mkdir -p ~/siloe/database/migrations ~/siloe/storage/logs ~/siloe/public/uploads"

# Step 2: Create a complete database initialization script
TMP_INIT_SCRIPT=$(mktemp)
cat > "$TMP_INIT_SCRIPT" << 'EOF'
<?php
// Complete database initialization script

// Define database path
$dbPath = __DIR__ . '/database/database.sqlite';
$dbDir = dirname($dbPath);

// Create database directory if it doesn't exist
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "Created database directory: $dbDir\n";
}

// Check if database file exists
if (file_exists($dbPath)) {
    // Backup existing database
    $backupPath = $dbPath . '.backup.' . date('YmdHis');
    copy($dbPath, $backupPath);
    echo "Backed up existing database to: $backupPath\n";
}

// Create or recreate database file
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
    
    // Insert sample company data
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM companies');
    $stmt->execute();
    $companyCount = $stmt->fetchColumn();
    
    if ($companyCount == 0) {
        $stmt = $pdo->prepare('INSERT INTO companies (name, description, address, phone, email, website) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute(['Siloe Company', 'A sample company', '123 Main St', '555-1234', 'info@siloe.com', 'https://siloe.com']);
        echo "Created sample company data\n";
    }
    
    // Insert sample menu items
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM weekly_menu_items');
    $stmt->execute();
    $menuCount = $stmt->fetchColumn();
    
    if ($menuCount == 0) {
        $menuItems = [
            ['Pasta Bolognesa', 'Delicious pasta with meat sauce', 12.99, 'pasta_bolognesa.jpg', 'Main', 'Monday'],
            ['Ensalada César', 'Fresh Caesar salad', 8.99, 'ensalada_cesar.jpg', 'Starter', 'Monday'],
            ['Pollo a la Parrilla', 'Grilled chicken with vegetables', 14.99, 'pollo_parrilla.jpg', 'Main', 'Tuesday'],
            ['Tres Leches', 'Traditional dessert', 6.99, 'tres_leches.jpg', 'Dessert', 'Tuesday']
        ];
        
        $stmt = $pdo->prepare('INSERT INTO weekly_menu_items (name, description, price, image, category, day_of_week) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($menuItems as $item) {
            $stmt->execute($item);
        }
        echo "Created sample menu items\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Database initialization complete\n";
EOF

log "INFO" "Uploading complete database initialization script to server..."
scp $SSH_OPTS "$TMP_INIT_SCRIPT" "$SERVER:~/siloe/complete_init_db.php"

# Step 3: Create migration files
TMP_MIGRATION_DIR=$(mktemp -d)

# Migration 1: Add contact and status fields to companies table
cat > "$TMP_MIGRATION_DIR/20250614151505_add_contact_and_status_fields_to_companies_table.php" << 'EOF'
<?php
class AddContactAndStatusFieldsToCompaniesTable {
    public function up() {
        $dbPath = __DIR__ . '/../database.sqlite';
        
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if columns already exist
            $stmt = $pdo->query("PRAGMA table_info(companies)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'name');
            
            $columnsToAdd = [
                'contact_name' => 'TEXT',
                'contact_phone' => 'TEXT',
                'contact_email' => 'TEXT',
                'status' => 'TEXT DEFAULT "active"'
            ];
            
            foreach ($columnsToAdd as $column => $type) {
                if (!in_array($column, $columnNames)) {
                    $pdo->exec("ALTER TABLE companies ADD COLUMN $column $type");
                    echo "Added $column column to companies table\n";
                } else {
                    echo "Column $column already exists in companies table\n";
                }
            }
            
            echo "Migration completed successfully\n";
            return true;
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run the migration
$migration = new AddContactAndStatusFieldsToCompaniesTable();
$migration->up();
EOF

# Migration 2: Add logo to companies table
cat > "$TMP_MIGRATION_DIR/20250614170100_add_logo_to_companies_table.php" << 'EOF'
<?php
class AddLogoToCompaniesTable {
    public function up() {
        $dbPath = __DIR__ . '/../database.sqlite';
        
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if column already exists
            $stmt = $pdo->query("PRAGMA table_info(companies)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'name');
            
            if (!in_array('logo', $columnNames)) {
                $pdo->exec("ALTER TABLE companies ADD COLUMN logo TEXT");
                echo "Added logo column to companies table\n";
            } else {
                echo "Column logo already exists in companies table\n";
            }
            
            echo "Migration completed successfully\n";
            return true;
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run the migration
$migration = new AddLogoToCompaniesTable();
$migration->up();
EOF

# Migration 3: Create weekly menu items table
cat > "$TMP_MIGRATION_DIR/2025_07_15_000000_create_weekly_menu_items_table.php" << 'EOF'
<?php
class CreateWeeklyMenuItemsTable {
    public function up() {
        $dbPath = __DIR__ . '/../database.sqlite';
        
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if table already exists
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='weekly_menu_items'");
            if (!$stmt->fetch()) {
                $pdo->exec('CREATE TABLE weekly_menu_items (
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
            } else {
                echo "Table weekly_menu_items already exists\n";
            }
            
            echo "Migration completed successfully\n";
            return true;
        } catch (PDOException $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run the migration
$migration = new CreateWeeklyMenuItemsTable();
$migration->up();
EOF

log "INFO" "Uploading migration files to server..."
ssh $SSH_OPTS "$SERVER" "mkdir -p ~/siloe/database/migrations"
scp $SSH_OPTS "$TMP_MIGRATION_DIR"/* "$SERVER:~/siloe/database/migrations/"

# Step 4: Create a simple test file to verify the application is working
TMP_TEST_FILE=$(mktemp)
cat > "$TMP_TEST_FILE" << 'EOF'
<?php
// Simple test file to verify the application is working
echo "<h1>Siloe Application Test</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
$dbPath = __DIR__ . '/database/database.sqlite';
if (file_exists($dbPath)) {
    echo "<p style='color:green'>Database file exists at: $dbPath</p>";
    
    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p style='color:green'>Successfully connected to database</p>";
        
        // Check tables
        $tables = ['users', 'companies', 'weekly_menu_items', 'employee_menu_selections', 'delete_requests'];
        echo "<h2>Database Tables:</h2>";
        echo "<ul>";
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                // Count records
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
                $countStmt->execute();
                $count = $countStmt->fetchColumn();
                
                echo "<li style='color:green'>Table $table exists ($count records)</li>";
            } else {
                echo "<li style='color:red'>Table $table does not exist</li>";
            }
        }
        
        echo "</ul>";
        
        // Show users
        $stmt = $pdo->query("SELECT id, name, email, role FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "<h2>Users:</h2>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p style='color:red'>No users found in the database</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red'>Database file does not exist at: $dbPath</p>";
}

// Check directories
$directories = [
    'storage' => __DIR__ . '/storage',
    'storage/logs' => __DIR__ . '/storage/logs',
    'public/uploads' => __DIR__ . '/public/uploads'
];

echo "<h2>Directory Structure:</h2>";
echo "<ul>";

foreach ($directories as $name => $path) {
    if (is_dir($path)) {
        $writable = is_writable($path) ? "writable" : "not writable";
        echo "<li style='color:green'>Directory $name exists ($writable)</li>";
    } else {
        echo "<li style='color:red'>Directory $name does not exist</li>";
    }
}

echo "</ul>";

// Show PHP info
echo "<h2>PHP Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
EOF

log "INFO" "Uploading test file to server..."
scp $SSH_OPTS "$TMP_TEST_FILE" "$SERVER:~/siloe/public/system_test.php"

# Step 5: Run the complete database initialization
log "INFO" "Running complete database initialization on server..."
ssh $SSH_OPTS "$SERVER" "cd ~/siloe && php complete_init_db.php"

# Step 6: Set proper permissions
log "INFO" "Setting proper permissions on server..."
ssh $SSH_OPTS "$SERVER" "chmod -R 755 ~/siloe && chmod -R 777 ~/siloe/database ~/siloe/storage ~/siloe/public/uploads"

# Step 7: Clean up temporary files
log "INFO" "Cleaning up temporary files..."
rm "$TMP_INIT_SCRIPT"
rm -rf "$TMP_MIGRATION_DIR"
rm "$TMP_TEST_FILE"

log "SUCCESS" "Complete fix process finished!"
log "INFO" "You can now access your application at http://siloecom.com"
log "INFO" "To verify the system is working, visit http://siloecom.com/system_test.php"
