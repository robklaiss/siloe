<?php
require_once __DIR__ . '/app/config/config.php';

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare('SELECT id, email, role FROM users WHERE role = ? LIMIT 1');
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin user found:\n";
        print_r($admin);
    } else {
        echo "No admin user found\n";
        
        // Check all users
        $stmt = $pdo->query('SELECT id, email, role FROM users LIMIT 10');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "All users:\n";
        foreach ($users as $user) {
            echo "ID: {$user['id']}, Email: {$user['email']}, Role: {$user['role']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
