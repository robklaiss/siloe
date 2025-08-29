#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"
REMOTE_DIR="/home/siloecom/public_html"
LOCAL_DIR="/Users/robinklaiss/Dev/siloe"

echo "Starting simple authentication fix deployment..."

# Step 1: Create a temporary directory for our files
TMP_DIR=$(mktemp -d)
echo "Created temporary directory: $TMP_DIR"

# Step 2: Copy the fixed AuthController.php to the temp directory
mkdir -p $TMP_DIR/app/Controllers
cp $LOCAL_DIR/app/Controllers/AuthController.php $TMP_DIR/app/Controllers/

# Step 3: Create the database migration script
mkdir -p $TMP_DIR/database/migrations
cat > $TMP_DIR/database/migrations/restore_auth_tables.php << 'EOL'
<?php
// Define the database path correctly
$dbPath = __DIR__ . '/../../siloe.db';
echo "Using database at: $dbPath\n";

// Create database directory if it doesn't exist
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
    echo "Created database directory: $dbDir\n";
}

// Create users table if it doesn't exist
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    company_id INTEGER,
    remember_token TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL;

// Create password_resets table
$sql .= <<<SQL

CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL;

// Create sessions table
$sql .= <<<SQL

CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    payload TEXT,
    last_activity INTEGER
);
SQL;

// Create an admin user if none exists
$sql .= <<<SQL

INSERT OR IGNORE INTO users (name, email, password, role) 
VALUES (
    'Admin User', 
    'admin@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password is 'password'
    'admin'
);
SQL;

// Execute the SQL
try {
    // Create or open the database
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->exec($sql);
    
    echo "Database tables created successfully!\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
EOL

# Step 4: Create the restore script
cat > $TMP_DIR/restore.sh << 'EOL'
#!/bin/bash

# Set variables
APP_DIR="/home/siloecom/public_html"
DB_DIR="$APP_DIR/database"
DB_FILE="$DB_DIR/siloe.db"

echo "Starting authentication system restoration..."

# Create database directory if it doesn't exist
if [ ! -d "$DB_DIR" ]; then
    mkdir -p "$DB_DIR"
    echo "Created database directory: $DB_DIR"
fi

# Create empty database file if it doesn't exist
if [ ! -f "$DB_FILE" ]; then
    touch "$DB_FILE"
    chmod 644 "$DB_FILE"
    echo "Created empty database file: $DB_FILE"
fi

# Run migration script
echo "Running database migration..."
cd "$APP_DIR"
php "$APP_DIR/database/migrations/restore_auth_tables.php"

# Set proper permissions
chown -R siloecom:siloecom "$DB_DIR"
chmod 755 "$DB_DIR"
chmod 644 "$DB_FILE"

echo "Checking database file..."
ls -la "$DB_FILE"

echo "Auth system restoration complete!"
EOL

chmod +x $TMP_DIR/restore.sh

# Step 5: Deploy to server using scp
echo "Deploying files to server..."

# Create directories on server
ssh $SERVER "mkdir -p $REMOTE_DIR/app/Controllers $REMOTE_DIR/database/migrations"

# Copy files to server
scp $TMP_DIR/app/Controllers/AuthController.php $SERVER:$REMOTE_DIR/app/Controllers/
scp $TMP_DIR/database/migrations/restore_auth_tables.php $SERVER:$REMOTE_DIR/database/migrations/
scp $TMP_DIR/restore.sh $SERVER:$REMOTE_DIR/

# Step 6: Run the restore script on the server
echo "Running restore script on server..."
ssh $SERVER "cd $REMOTE_DIR && chmod +x restore.sh && ./restore.sh"

# Step 7: Clean up
rm -rf $TMP_DIR
echo "Temporary files cleaned up"

echo "Deployment complete! You should now be able to log in with:"
echo "Email: admin@example.com"
echo "Password: password"
echo "IMPORTANT: Change these credentials immediately after logging in!"
