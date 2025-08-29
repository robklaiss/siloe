#!/bin/bash

# Full Application Deployment Script for Siloe using SCP
# This script will deploy the complete application to the server

echo "===== FULL APPLICATION DEPLOYMENT SCRIPT ====="

# Server details
SERVER_USER="siloecom"
SERVER_HOST="192.185.143.154"
SERVER="$SERVER_USER@$SERVER_HOST"
SERVER_APP_ROOT="/home1/siloecom/siloe"
SERVER_WEB_ROOT="/home1/siloecom/siloe/public"
LOCAL_APP_ROOT="/Users/robinklaiss/Dev/siloe"

# Create a temporary directory for any files we need to modify
TEMP_DIR=$(mktemp -d)
echo "Created temporary directory: $TEMP_DIR"

# Step 1: Create a backup of the current server files
echo "Creating backup of current server files..."
ssh -o PreferredAuthentications=password $SERVER << 'ENDSSH'
BACKUP_DIR="/home1/siloecom/backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r /home1/siloecom/siloe/* "$BACKUP_DIR/" 2>/dev/null || true
echo "Backup created at $BACKUP_DIR"
ENDSSH

# Step 2: Prepare local files for upload
echo "Preparing local files for upload..."

# Create a modified .htaccess file for the server
cat > "$TEMP_DIR/.htaccess" << 'EOL'
# Enable rewrite engine
RewriteEngine On

# Set base directory
RewriteBase /

# Handle front controller
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

# Set default index file
DirectoryIndex index.php

# PHP settings
php_flag display_errors on
php_value error_reporting E_ALL

# Allow access to all files
<FilesMatch ".*">
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOL

# Create a modified database config file
cat > "$TEMP_DIR/database.php" << 'EOL'
<?php

return [
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../database/database.sqlite',
    'prefix' => '',
    'foreign_key_constraints' => true,
];
EOL

# Step 3: Create directories on server
echo "Creating directories on server..."
ssh -o PreferredAuthentications=password $SERVER << 'ENDSSH'
mkdir -p /home1/siloecom/siloe/app/Controllers
mkdir -p /home1/siloecom/siloe/app/Core
mkdir -p /home1/siloecom/siloe/app/Middleware
mkdir -p /home1/siloecom/siloe/app/Models
mkdir -p /home1/siloecom/siloe/config
mkdir -p /home1/siloecom/siloe/database/migrations
mkdir -p /home1/siloecom/siloe/database/seeders
mkdir -p /home1/siloecom/siloe/public/assets/css
mkdir -p /home1/siloecom/siloe/public/assets/js
mkdir -p /home1/siloecom/siloe/public/assets/images
ENDSSH

# Step 4: Upload core files
echo "Uploading core files..."

# Upload AuthController.php
echo "Uploading AuthController.php..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Controllers/AuthController.php" "$SERVER:$SERVER_APP_ROOT/app/Controllers/"

# Upload Controller.php
echo "Uploading Controller.php..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Core/Controller.php" "$SERVER:$SERVER_APP_ROOT/app/Core/"

# Upload Model.php
echo "Uploading Model.php..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Core/Model.php" "$SERVER:$SERVER_APP_ROOT/app/Core/"

# Upload QueryBuilder.php
echo "Uploading QueryBuilder.php..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Core/QueryBuilder.php" "$SERVER:$SERVER_APP_ROOT/app/Core/"

# Upload middleware files
echo "Uploading middleware files..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Middleware/AuthMiddleware.php" "$SERVER:$SERVER_APP_ROOT/app/Middleware/"
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Middleware/GuestMiddleware.php" "$SERVER:$SERVER_APP_ROOT/app/Middleware/"

# Upload model files
echo "Uploading model files..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Models/Company.php" "$SERVER:$SERVER_APP_ROOT/app/Models/"
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/app/Models/EmployeeMenuSelection.php" "$SERVER:$SERVER_APP_ROOT/app/Models/"

# Upload config files
echo "Uploading config files..."
scp -o PreferredAuthentications=password "$TEMP_DIR/database.php" "$SERVER:$SERVER_APP_ROOT/config/"

# Upload migration files
echo "Uploading migration files..."
for file in "$LOCAL_APP_ROOT/database/migrations"/*.php; do
  echo "Uploading $(basename "$file")..."
  scp -o PreferredAuthentications=password "$file" "$SERVER:$SERVER_APP_ROOT/database/migrations/"
done

# Upload public files
echo "Uploading public files..."
scp -o PreferredAuthentications=password "$LOCAL_APP_ROOT/public/index.php" "$SERVER:$SERVER_WEB_ROOT/"
scp -o PreferredAuthentications=password "$TEMP_DIR/.htaccess" "$SERVER:$SERVER_WEB_ROOT/.htaccess"

# Step 5: Create migration runner script
echo "Creating migration runner script..."
cat > "$TEMP_DIR/run_migrations.php" << 'EOL'
<?php
// Database migration runner
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
$db_path = '/home1/siloecom/siloe/database/database.sqlite';
$migrations_dir = '/home1/siloecom/siloe/database/migrations';

// Function to run migrations
function run_migrations($db_path, $migrations_dir) {
    try {
        // Create directory if it doesn't exist
        $db_dir = dirname($db_path);
        if (!is_dir($db_dir)) {
            if (!mkdir($db_dir, 0755, true)) {
                return "Failed to create directory: $db_dir";
            }
        }
        
        // Connect to database
        $db = new PDO("sqlite:$db_path");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create migrations table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration TEXT NOT NULL,
            batch INTEGER NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Get list of executed migrations
        $stmt = $db->query("SELECT migration FROM migrations");
        $executed_migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get list of migration files
        $migration_files = [];
        if (is_dir($migrations_dir)) {
            $files = scandir($migrations_dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $migration_files[] = $file;
                }
            }
            sort($migration_files);
        } else {
            return "Migrations directory not found: $migrations_dir";
        }
        
        // Determine next batch number
        $stmt = $db->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_batch = ($result['max_batch'] ?? 0) + 1;
        
        // Run pending migrations
        $executed_count = 0;
        foreach ($migration_files as $file) {
            $migration_name = pathinfo($file, PATHINFO_FILENAME);
            if (!in_array($migration_name, $executed_migrations)) {
                // Include migration file
                $migration_path = $migrations_dir . '/' . $file;
                if (file_exists($migration_path)) {
                    require_once $migration_path;
                    
                    // Extract class name from filename
                    $class_name = '';
                    if (preg_match('/^\d+_(\w+)$/', $migration_name, $matches)) {
                        $name_parts = explode('_', $matches[1]);
                        $class_name = '';
                        foreach ($name_parts as $part) {
                            $class_name .= ucfirst($part);
                        }
                    }
                    
                    if (class_exists($class_name)) {
                        // Create migration instance and run up method
                        $migration = new $class_name();
                        if (method_exists($migration, 'up')) {
                            $migration->up($db);
                            
                            // Record migration
                            $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                            $stmt->execute([$migration_name, $next_batch]);
                            
                            $executed_count++;
                            echo "Migrated: $migration_name<br>";
                        }
                    } else {
                        echo "Migration class not found: $class_name<br>";
                    }
                }
            }
        }
        
        return "Executed $executed_count migrations";
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

// Run migrations
$result = run_migrations($db_path, $migrations_dir);

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrations - Siloe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px 0;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>Database Migrations</h1>
    
    <div class="success">
        <h2>Migration Results</h2>
        <p><?php echo $result; ?></p>
    </div>
    
    <p>
        <a href="/" class="button">Go to Home</a>
        <a href="/admin_access.php" class="button">Admin Access</a>
    </p>
</body>
</html>
EOL

# Upload migration runner
echo "Uploading migration runner..."
scp -o PreferredAuthentications=password "$TEMP_DIR/run_migrations.php" "$SERVER:$SERVER_WEB_ROOT/run_migrations.php"

# Step 6: Set proper permissions on server
echo "Setting proper permissions on server..."
ssh -o PreferredAuthentications=password $SERVER << 'ENDSSH'
chmod -R 755 /home1/siloecom/siloe
chmod -R 755 /home1/siloecom/siloe/public
chmod 644 /home1/siloecom/siloe/public/.htaccess
find /home1/siloecom/siloe -type f -name "*.php" -exec chmod 644 {} \;
ENDSSH

# Clean up temporary directory
echo "Cleaning up temporary directory..."
rm -rf "$TEMP_DIR"

echo "===== DEPLOYMENT COMPLETE ====="
echo "You can now access the following URLs:"
echo "- Home page: http://www.siloe.com.py/"
echo "- Admin access: http://www.siloe.com.py/admin_access.php"
echo "- Run migrations: http://www.siloe.com.py/run_migrations.php"
