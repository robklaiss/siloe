<?php
/**
 * Fix Database Configuration
 * 
 * This script updates the database configuration to use the correct database path
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$server = '192.185.143.154';
$user = 'siloecom';
$ssh_key = '~/.ssh/siloe_ed25519';
$remote_path = '/home1/siloecom/siloe/public/config/database.php';

// Create the updated database configuration
$updated_config = <<<'EOT'
<?php

/**
 * Database connection configuration
 */

/**
 * Get a PDO database connection
 * 
 * @return PDO
 */
function getDbConnection() {
    // Try multiple possible database paths
    $possible_paths = [
        __DIR__ . '/../database/siloe.db',
        __DIR__ . '/../database/database.sqlite',
        '/home1/siloecom/siloe/public/database/siloe.db',
        '/home1/siloecom/siloe/public/database/database.sqlite'
    ];
    
    $db_path = null;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $db_path = $path;
            error_log("Using database at: $path");
            break;
        }
    }
    
    if (!$db_path) {
        error_log("Database file not found!");
        throw new Exception("Database file not found!");
    }
    
    try {
        $db = new PDO('sqlite:' . $db_path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Ensure SQLite foreign key constraints are enforced (required for cascades)
        $db->exec('PRAGMA foreign_keys = ON');
        return $db;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw $e;
    }
}
EOT;

// Create a temporary file with the updated configuration
$temp_file = tempnam(sys_get_temp_dir(), 'db_config');
file_put_contents($temp_file, $updated_config);

// Upload the updated configuration to the server
$cmd = "scp -i $ssh_key -o IdentitiesOnly=yes $temp_file $user@$server:$remote_path";
echo "Executing: $cmd\n";
exec($cmd, $output, $return_var);

// Display the result
if ($return_var === 0) {
    echo "✅ Database configuration updated successfully!\n";
} else {
    echo "❌ Failed to update database configuration.\n";
    echo "Error output: " . implode("\n", $output) . "\n";
}

// Clean up the temporary file
unlink($temp_file);

echo "Done.\n";
