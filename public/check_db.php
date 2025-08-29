<?php
// Define root path
define('ROOT_PATH', dirname(__DIR__));

require_once __DIR__ . '/../app/config/config.php';

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user count
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "User count: $count\n";
    
    // Get admin users
    $stmt = $pdo->prepare('SELECT id, email, role FROM users WHERE role = ?');
    $stmt->execute(['admin']);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Admin users: " . count($admins) . "\n";
    
    foreach ($admins as $admin) {
        echo "ID: {$admin['id']}, Email: {$admin['email']}, Role: {$admin['role']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
