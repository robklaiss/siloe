# Manual Authentication Fix Guide for Siloe

Follow these steps to fix the authentication system on your production server.

## Step 1: Fix the AuthController.php File

1. Connect to your server via SSH:
   ```bash
   ssh siloecom@192.185.143.154
   ```

2. Navigate to the Controllers directory:
   ```bash
   cd /home/siloecom/public_html/app/Controllers
   ```

3. Create or edit the AuthController.php file:
   ```bash
   nano AuthController.php
   ```

4. Replace the entire contents with this fixed code:
   ```php
   <?php
   namespace App\Controllers;

   use App\Core\Controller;
   use PDO;
   use PDOException;

   class AuthController extends Controller {
       protected function getDbConnection() {
           static $db = null;
           if ($db === null) {
               try {
                   if (defined('DB_DRIVER') && DB_DRIVER === 'mysql') {
                       $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                       $db = new PDO($dsn, DB_USER, DB_PASS, [
                           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                           PDO::ATTR_EMULATE_PREPARES => false,
                       ]);
                   } else {
                       $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../../database/siloe.db';
                       $db = new PDO('sqlite:' . $dbPath);
                       $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                       $db->exec('PRAGMA foreign_keys = ON;');
                   }
               } catch (PDOException $e) {
                   error_log('Database connection error: ' . $e->getMessage());
                   throw $e;
               }
           }
           return $db;
       }
       
       // Keep the rest of your AuthController.php content below this line
   ```

5. Save the file (Ctrl+O, then Enter, then Ctrl+X in nano)

## Step 2: Create the Database Migration Script

1. Create the migrations directory if it doesn't exist:
   ```bash
   mkdir -p /home/siloecom/public_html/database/migrations
   ```

2. Create the migration script:
   ```bash
   nano /home/siloecom/public_html/database/migrations/restore_auth_tables.php
   ```

3. Add this code:
   ```php
   <?php
   // Define the database path correctly
   $dbPath = __DIR__ . '/../../database/siloe.db';
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
   ```

4. Save the file (Ctrl+O, then Enter, then Ctrl+X in nano)

## Step 3: Create the Restore Script

1. Create the restore script:
   ```bash
   nano /home/siloecom/public_html/restore_auth.php
   ```

2. Add this code:
   ```php
   <?php
   /**
    * Siloe Authentication System Restore Script
    */

   // Set error reporting
   error_reporting(E_ALL);
   ini_set('display_errors', 1);

   echo "<h1>Siloe Authentication System Restore</h1>";

   // Define paths
   $baseDir = __DIR__;
   $dbDir = $baseDir . '/database';
   $dbFile = $dbDir . '/siloe.db';
   $migrationFile = $dbDir . '/migrations/restore_auth_tables.php';

   // Check if migration file exists
   if (!file_exists($migrationFile)) {
       die("<p>Error: Migration file not found at: $migrationFile</p>");
   }

   // Create database directory if it doesn't exist
   if (!is_dir($dbDir)) {
       if (!mkdir($dbDir, 0755, true)) {
           die("<p>Error: Failed to create database directory at: $dbDir</p>");
       }
       echo "<p>Created database directory: $dbDir</p>";
   }

   // Create empty database file if it doesn't exist
   if (!file_exists($dbFile)) {
       if (!touch($dbFile)) {
           die("<p>Error: Failed to create database file at: $dbFile</p>");
       }
       echo "<p>Created empty database file: $dbFile</p>";
   }

   // Set proper permissions
   chmod($dbDir, 0755);
   chmod($dbFile, 0644);

   // Run the migration script
   echo "<p>Running database migration...</p>";
   echo "<pre>";
   include $migrationFile;
   echo "</pre>";

   echo "<h2>Restoration Complete!</h2>";
   echo "<p>The authentication system has been restored successfully.</p>";
   echo "<p>You can now login with:</p>";
   echo "<ul>";
   echo "<li><strong>Email:</strong> admin@example.com</li>";
   echo "<li><strong>Password:</strong> password</li>";
   echo "</ul>";
   echo "<p><strong>IMPORTANT:</strong> Change these credentials immediately after logging in!</p>";
   echo "<p><a href='/'>Go to Homepage</a></p>";
   ```

3. Save the file (Ctrl+O, then Enter, then Ctrl+X in nano)

## Step 4: Run the Restore Script

1. Set proper permissions:
   ```bash
   chmod 644 /home/siloecom/public_html/database/migrations/restore_auth_tables.php
   chmod 644 /home/siloecom/public_html/restore_auth.php
   ```

2. Visit the restore script in your browser:
   ```
   https://your-domain.com/restore_auth.php
   ```

3. The script will:
   - Create the database directory if needed
   - Create the SQLite database file if needed
   - Run the migration to create auth tables
   - Create a default admin user

## Step 5: Login with Default Credentials

1. Navigate to your login page
2. Use these credentials:
   - Email: `admin@example.com`
   - Password: `password`
3. **IMPORTANT**: Change these credentials immediately after logging in!

## Troubleshooting

If you encounter any issues:

1. **Check File Permissions**
   ```bash
   chmod 755 /home/siloecom/public_html/database
   chmod 644 /home/siloecom/public_html/database/siloe.db
   ```

2. **Check PHP Error Logs**
   ```bash
   tail -n 50 /home/siloecom/logs/error_log
   ```

3. **Verify Database File**
   ```bash
   ls -la /home/siloecom/public_html/database/siloe.db
   ```

4. **Test Database Connection**
   Create a test file:
   ```bash
   nano /home/siloecom/public_html/test_db.php
   ```
   
   Add this code:
   ```php
   <?php
   try {
       $db = new PDO('sqlite:/home/siloecom/public_html/database/siloe.db');
       echo "Database connection successful!";
   } catch (PDOException $e) {
       echo "Database error: " . $e->getMessage();
   }
   ```
   
   Visit `https://your-domain.com/test_db.php` in your browser to test the connection.
