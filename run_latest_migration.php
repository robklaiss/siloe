<?php



// Define the path to the migrations directory
$migrationsPath = __DIR__ . '/database/migrations/';

// Get all PHP files from the migrations directory
$allFiles = scandir($migrationsPath);

// Filter for files that match the timestamped migration format (e.g., 20230101120000_create_users_table.php)
$migrationFiles = array_filter($allFiles, function($file) {
    return preg_match('/^\d{14}_.*\.php$/', $file);
});

if (empty($migrationFiles)) {
    echo "No timestamped migration files found.\n";
    exit;
}

// Sort files in descending order to get the latest one first
rsort($migrationFiles);
$latestMigrationFile = $migrationFiles[0];
$fullPath = $migrationsPath . $latestMigrationFile;

echo "Attempting to run latest migration: " . $latestMigrationFile . "\n";

if (file_exists($fullPath)) {
    require_once $fullPath;

    // Derive class name from filename
    $className = pathinfo($latestMigrationFile, PATHINFO_FILENAME);
    $className = preg_replace('/^\d{14}_/', '', $className);
    $className = str_replace('_', ' ', $className);
    $className = str_replace(' ', '', ucwords($className));

    if (class_exists($className)) {
        echo "Migration class $className found.\n";
        try {
            $migration = new $className();
            if (method_exists($migration, 'up')) {
                $migration->up();
            } else {
                echo "Error: Method 'up()' not found in class $className.\n";
            }
        } catch (\Exception $e) {
            echo "Error during migration execution: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Error: Class '$className' not found in " . $latestMigrationFile . ".\n";
        echo "Please ensure the class name in the migration file is correct and follows the convention (e.g., 'add_logo_to_companies' becomes 'AddLogoToCompanies').\n";
    }
} else {
    echo "Error: Migration file not found at " . $fullPath . "\n";
}

?>
