<?php

// Load configuration
require_once __DIR__ . '/../app/config/config.php';

// Get database connection
function getDbConnection() {
    try {
        $dsn = 'sqlite:' . DB_PATH;
        $pdo = new \PDO($dsn);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $pdo;
    } catch (\PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// Reset admin password
function resetAdminPassword($email, $newPassword) {
    $db = getDbConnection();
    
    // Check if user exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "Error: User with email {$email} not found.\n";
        return false;
    }
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the password
    $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
    $result = $stmt->execute([
        ':password' => $hashedPassword,
        ':email' => $email
    ]);
    
    if ($result) {
        echo "Success: Password for {$email} has been reset.\n";
        echo "New password hash: " . $hashedPassword . "\n";
        
        // Verify the new password works
        $verify = password_verify($newPassword, $hashedPassword);
        echo "Password verification test: " . ($verify ? "PASSED" : "FAILED") . "\n";
        return true;
    } else {
        echo "Error: Failed to update password.\n";
        return false;
    }
}

// Email and password to reset
$adminEmail = 'admin@siloe.com';
$newPassword = 'admin123';

// Reset the password
resetAdminPassword($adminEmail, $newPassword);
