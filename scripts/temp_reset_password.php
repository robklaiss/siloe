<?php
// Simple password reset script for cPanel environments
// This avoids any issues with multi-line commands

// Database path
$db_path = __DIR__ . '/../database/siloe.db';

try {
    // Connect to the database
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Admin email to reset
    $admin_email = 'admin@siloe.com';
    
    // New password and its hash
    $new_password = 'admin123';
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update the password
    $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
    $result = $stmt->execute([
        ':password' => $password_hash,
        ':email' => $admin_email
    ]);
    
    if ($result) {
        echo "Password for {$admin_email} has been reset to '{$new_password}'.\n";
        echo "Affected rows: " . $stmt->rowCount() . "\n";
        
        // Verify user exists
        $check = $db->prepare('SELECT id, role FROM users WHERE email = :email');
        $check->execute([':email' => $admin_email]);
        $user = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "Found user ID: {$user['id']}, role: {$user['role']}\n";
            echo "Password reset SUCCESSFUL.\n";
        } else {
            echo "WARNING: No user found with email {$admin_email}!\n";
        }
    } else {
        echo "Failed to reset password for {$admin_email}.\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
