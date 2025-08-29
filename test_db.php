<?php
try {
    $db = new PDO('sqlite:/Users/robinklaiss/Dev/siloe/database/siloe.db');
    echo "Database connection successful\n";
    
    // Test a simple query
    $stmt = $db->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    echo "User count: " . $result['count'] . "\n";
} catch (Exception $e) {
    echo 'Database connection failed: ' . $e->getMessage() . "\n";
}
