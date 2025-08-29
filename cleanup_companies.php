<?php
// Load database configuration
require_once __DIR__ . '/config/database.php';

// Connect to the database
try {
    $db = getDbConnection();
    
    // Start transaction
    $db->beginTransaction();
    
    // First, check if company with ID 10 exists
    $stmt = $db->query('SELECT id, name FROM companies WHERE id = 10');
    $siloe = $stmt->fetch();
    
    if (!$siloe) {
        throw new Exception('Company with ID 10 (Siloe) not found!');
    }
    
    echo "Found company: " . htmlspecialchars($siloe['name']) . " (ID: " . $siloe['id'] . ")\n";
    
    // Get count of all companies before deletion
    $countStmt = $db->query('SELECT COUNT(*) as count FROM companies');
    $countBefore = $countStmt->fetch()['count'];
    
    echo "Total companies before cleanup: $countBefore\n";
    
    // Delete all companies except ID 10
    $deleteStmt = $db->prepare('DELETE FROM companies WHERE id != 10');
    $deleteStmt->execute();
    $deletedCount = $deleteStmt->rowCount();
    
    // Get count after deletion
    $countAfter = $countBefore - $deletedCount;
    
    // Verify only one company remains
    $verifyStmt = $db->query('SELECT id, name FROM companies');
    $remainingCompanies = $verifyStmt->fetchAll();
    
    // Commit the transaction
    $db->commit();
    
    echo "\nCleanup completed successfully!\n";
    echo "Companies deleted: $deletedCount\n";
    echo "Companies remaining: $countAfter\n";
    
    if ($countAfter > 0) {
        echo "\nRemaining company:\n";
        foreach ($remainingCompanies as $company) {
            echo "- ID: " . $company['id'] . ", Name: " . $company['name'] . "\n";
        }
    }
    
} catch (Exception $e) {
    // Rollback in case of error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
