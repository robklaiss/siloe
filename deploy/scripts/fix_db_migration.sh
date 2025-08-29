#!/bin/bash

# Configuration
REMOTE_USER="siloecom"
REMOTE_HOST="192.185.143.154"
REMOTE_DIR="/home/siloecom/public_html"
SSH_KEY="~/.ssh/id_ed25519"
SSH_OPTS="-i $SSH_KEY -o StrictHostKeyChecking=no"
SCP_OPTS="-i $SSH_KEY -o StrictHostKeyChecking=no"

echo "Fixing database migration script..."

# Create a fixed migration script
cat > /tmp/fixed_auth_tables.php << 'EOL'
<?php

// Define the database path correctly
$dbPath = __DIR__ . '/../siloe.db';
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

# Copy the fixed migration script to the server
scp $SCP_OPTS /tmp/fixed_auth_tables.php $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/database/migrations/fixed_auth_tables.php

# Create a fixed restore script
cat > /tmp/fixed_restore.sh << 'EOL'
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
php "$APP_DIR/database/migrations/fixed_auth_tables.php"

# Set proper permissions
chown -R siloecom:siloecom "$DB_DIR"
chmod 755 "$DB_DIR"
chmod 644 "$DB_FILE"

echo "Checking database file..."
ls -la "$DB_FILE"

echo "Auth system restoration complete!"
EOL

# Copy the fixed restore script to the server
scp $SCP_OPTS /tmp/fixed_restore.sh $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/fixed_restore.sh

# Run the fixed restore script on the server
echo "Running fixed restore script on server..."
ssh $SSH_OPTS $REMOTE_USER@$REMOTE_HOST "cd $REMOTE_DIR && chmod +x fixed_restore.sh && ./fixed_restore.sh"

echo "Fix completed!"
