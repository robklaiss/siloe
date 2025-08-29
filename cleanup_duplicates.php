<?php

// Use shared DB connection to ensure PRAGMA foreign_keys is ON
require_once __DIR__ . '/config/database.php';
$db = getDbConnection();

// Get all menu items grouped by name and description
$query = "SELECT name, description, MIN(id) as keep_id, GROUP_CONCAT(id) as all_ids 
          FROM menus 
          GROUP BY name, description 
          HAVING COUNT(*) > 1";
$stmt = $db->query($query);
$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($duplicates) . " sets of duplicate menu items.\n";

// Begin transaction
$db->beginTransaction();

try {
    // For each set of duplicates
    foreach ($duplicates as $duplicate) {
        $name = $duplicate['name'];
        $description = $duplicate['description'];
        $keep_id = $duplicate['keep_id'];
        $all_ids = explode(',', $duplicate['all_ids']);
        
        // Remove the ID we want to keep
        $all_ids = array_filter($all_ids, function($id) use ($keep_id) {
            return $id != $keep_id;
        });
        
        if (!empty($all_ids)) {
            // Delete the duplicates
            $delete_ids = implode(',', $all_ids);
            $delete_query = "DELETE FROM menus WHERE id IN ($delete_ids)";
            $db->exec($delete_query);
            
            echo "Kept menu item ID $keep_id ($name - $description) and deleted IDs: $delete_ids\n";
        }
    }
    
    // Add a unique constraint to prevent future duplicates
    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_menus_unique ON menus(name, description)");
    echo "Added unique constraint on name and description columns.\n";
    
    // Commit the transaction
    $db->commit();
    echo "All duplicates have been cleaned up successfully.\n";
} catch (Exception $e) {
    // Rollback the transaction on error
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
