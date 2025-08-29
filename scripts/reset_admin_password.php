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
    
    // Check if user exists and get current info
    $stmt = $db->prepare('SELECT id, password, role FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "Error: User with email {$email} not found.\n";
        return false;
    }
    
    echo "Found user ID: " . $user['id'] . " with role: " . $user['role'] . "\n";
    
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
        
        // Check how many rows were actually affected
        $rowCount = $stmt->rowCount();
        echo "Rows updated: {$rowCount}\n";
        
        // Verify the new password works
        $verify = password_verify($newPassword, $hashedPassword);
        echo "Password verification test: " . ($verify ? "PASSED" : "FAILED") . "\n";
        
        // Double-check that the password was actually updated in the database
        $checkStmt = $db->prepare('SELECT password FROM users WHERE email = :email');
        $checkStmt->execute([':email' => $email]);
        $updatedUser = $checkStmt->fetch();
        
        if ($updatedUser && password_verify($newPassword, $updatedUser['password'])) {
            echo "Database verification: PASSED - Password was correctly saved in database\n";
        } else {
            echo "Database verification: FAILED - Password might not have been saved correctly\n";
        }
        
        return true;
    } else {
        echo "Error: Failed to update password.\n";
        return false;
    }
}

// Email and password to reset
$adminEmail = 'admin@siloe.com';

// Check for command line arguments
if ($argc > 1) {
    $adminEmail = $argv[1];
    echo "Using provided email: {$adminEmail}\n";
}

$newPassword = 'admin123';
if ($argc > 2) {
    $newPassword = $argv[2];
    echo "Using provided password (length: " . strlen($newPassword) . ")\n";
} else {
    echo "No password provided via command line. Using default: {$newPassword}\n";
}

// Reset the password
resetAdminPassword($adminEmail, $newPassword);
