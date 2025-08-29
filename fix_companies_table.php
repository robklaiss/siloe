<?php
/**
 * Fix Companies Table Schema
 * 
 * This script updates the companies table schema to match the model
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages
function log_message($message, $type = 'info') {
    $color = 'black';
    if ($type == 'success') $color = 'green';
    if ($type == 'error') $color = 'red';
    if ($type == 'warning') $color = 'orange';
    
    echo "<p style='color:$color'>$message</p>";
}

// Try multiple possible database paths
$possible_paths = [
    __DIR__ . '/database/siloe.db',
    __DIR__ . '/database/database.sqlite',
    '/home1/siloecom/siloe/public/database/siloe.db',
    '/home1/siloecom/siloe/public/database/database.sqlite'
];

$db_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $db_path = $path;
        log_message("Found database at: $path");
        break;
    }
}

if (!$db_path) {
    log_message("Database file not found!", 'error');
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    log_message("Successfully connected to the database", 'success');
    
    // Check current companies table structure
    $stmt = $pdo->query("PRAGMA table_info(companies)");
    $columns = $stmt->fetchAll();
    
    log_message("Current companies table structure:");
    foreach ($columns as $column) {
        log_message("- {$column['name']} ({$column['type']})");
    }
    
    // Check if we need to add missing columns
    $missing_columns = [];
    $expected_columns = [
        'id', 'name', 'address', 'phone', 'contact_email', 'contact_phone', 'is_active', 'logo', 'created_at', 'updated_at'
    ];
    
    $existing_columns = array_column($columns, 'name');
    foreach ($expected_columns as $column) {
        if (!in_array($column, $existing_columns)) {
            $missing_columns[] = $column;
        }
    }
    
    if (count($missing_columns) > 0) {
        log_message("Missing columns: " . implode(', ', $missing_columns), 'warning');
        
        // Add missing columns
        foreach ($missing_columns as $column) {
            $sql = "";
            switch ($column) {
                case 'contact_email':
                    $sql = "ALTER TABLE companies ADD COLUMN contact_email TEXT";
                    break;
                case 'contact_phone':
                    $sql = "ALTER TABLE companies ADD COLUMN contact_phone TEXT";
                    break;
                case 'is_active':
                    $sql = "ALTER TABLE companies ADD COLUMN is_active INTEGER DEFAULT 1";
                    break;
                case 'logo':
                    $sql = "ALTER TABLE companies ADD COLUMN logo TEXT";
                    break;
                case 'phone':
                    $sql = "ALTER TABLE companies ADD COLUMN phone TEXT";
                    break;
            }
            
            if (!empty($sql)) {
                try {
                    $pdo->exec($sql);
                    log_message("Added column: $column", 'success');
                } catch (PDOException $e) {
                    log_message("Error adding column $column: " . $e->getMessage(), 'error');
                }
            }
        }
    } else {
        log_message("All expected columns exist", 'success');
    }
    
    // Create a sample company if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM companies");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        log_message("No companies found. Creating a sample company...");
        
        $stmt = $pdo->prepare("
            INSERT INTO companies (name, address, contact_email, contact_phone, is_active, logo)
            VALUES (:name, :address, :contact_email, :contact_phone, :is_active, :logo)
        ");
        
        $stmt->execute([
            ':name' => 'Siloe Demo Company',
            ':address' => '123 Main St, Asunción, Paraguay',
            ':contact_email' => 'contact@siloe.com.py',
            ':contact_phone' => '+595 21 123 456',
            ':is_active' => 1,
            ':logo' => null
        ]);
        
        log_message("Created sample company", 'success');
    } else {
        log_message("Found $count companies", 'success');
    }
    
    log_message("Companies table fix completed", 'success');
    
} catch (PDOException $e) {
    log_message("Database error: " . $e->getMessage(), 'error');
}
