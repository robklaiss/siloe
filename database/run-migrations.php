<?php

require_once __DIR__ . '/../config/database.php';

// Get database connection
$db = getDbConnection();

try {
    // Create migrations table if it doesn't exist
    $db->exec('CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');

    // Get all migration files
    $migrationFiles = glob(__DIR__ . '/migrations/*.php');
    
    // Get already run migrations
    $stmt = $db->query('SELECT migration FROM migrations');
    $ranMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Filter out already run migrations
    $pendingMigrations = array_filter($migrationFiles, function($file) use ($ranMigrations) {
        return !in_array(basename($file), $ranMigrations);
    });
    
    if (empty($pendingMigrations)) {
        echo "No hay migraciones pendientes para ejecutar.\n";
        exit(0);
    }
    
    // Sort migrations by filename
    sort($pendingMigrations);
    
    // Get next batch number
    $batch = $db->query('SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations')->fetchColumn();
    
    // Run pending migrations
    foreach ($pendingMigrations as $migrationFile) {
        $migrationName = basename($migrationFile);
        echo "Ejecutando migración: $migrationName\n";
        
        // Check if this is a legacy migration (class-based)
        $migrationContent = file_get_contents($migrationFile);
        $isLegacy = (strpos($migrationContent, 'class ') !== false);
        
        try {
            if ($isLegacy) {
                // Create a temporary file to run the legacy migration
                $tempFile = tempnam(sys_get_temp_dir(), 'migration_');
                $tempFilePhp = $tempFile . '.php';
                rename($tempFile, $tempFilePhp);
                
                // Extract the class name from the migration file
                $migrationContent = file_get_contents($migrationFile);
                if (!preg_match('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)/', $migrationContent, $matches)) {
                    throw new Exception("No se pudo encontrar el nombre de la clase en el archivo de migración heredado");
                }
                $className = $matches[1];
                
                // Get the absolute path to the database config
                $dbConfigPath = realpath(__DIR__ . '/../config/database.php');
                if ($dbConfigPath === false) {
                    throw new Exception("No se pudo encontrar el archivo de configuración de la base de datos");
                }
                
                // Create the migration runner script
                $script = '<?php' . "\n";
                $script .= 'require_once ' . var_export($dbConfigPath, true) . ';' . "\n";
                $script .= 'require_once ' . var_export($migrationFile, true) . ';' . "\n";
                $script .= '$migration = new ' . $className . '();' . "\n";
                $script .= '$migration->up();' . "\n";
                $script .= 'echo "Migración completada exitosamente.\n";' . "\n";
                
                file_put_contents($tempFilePhp, $script);
                
                // Execute the migration
                $output = [];
                $returnVar = 0;
                exec('php ' . escapeshellarg($tempFilePhp) . ' 2>&1', $output, $returnVar);
                
                // Clean up
                @unlink($tempFilePhp);
                
                if ($returnVar !== 0) {
                    throw new Exception("La migración heredada falló: " . implode("\n", $output));
                }
                
                // Output the result
                echo implode("\n", $output) . "\n";
            } else {
                // This is a new-style migration
                $migration = require $migrationFile;

                // If the included file executed procedural code and did not return a value,
                // PHP's require returns 1. Treat that as a successful, procedural migration.
                if ($migration === 1) {
                    // Assume the migration handled its own transactions/output.
                    // No action needed here.
                } else {
                    $db->beginTransaction();

                    if (is_array($migration) && isset($migration['up']) && is_callable($migration['up'])) {
                        $migration['up']($db);
                    }
                    // Direct callable
                    elseif (is_callable($migration)) {
                        $migration($db);
                    } 
                    else {
                        throw new Exception("Formato de migración inválido en $migrationName");
                    }

                    $db->commit();
                }
            }
            
            // Record the migration
            $stmt = $db->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
            $stmt->execute([$migrationName, $batch]);
            
            echo "La migración $migrationName se completó exitosamente.\n";
            
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            echo "Error al ejecutar la migración $migrationName: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
    
    echo "Todas las migraciones se completaron exitosamente.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
