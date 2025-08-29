<?php
/**
 * Test Login Script
 * 
 * This script tests the database connection and user authentication
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Siloe Login Test</h1>";

// Try multiple possible database paths
$possible_paths = [
    __DIR__ . '/database/siloe.db',
    __DIR__ . '/database/database.sqlite',
    __DIR__ . '/../database/siloe.db',
    __DIR__ . '/../database/database.sqlite',
    '/home1/siloecom/siloe/public/database/siloe.db',
    '/home1/siloecom/siloe/public/database/database.sqlite'
];

$db_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $db_path = $path;
        echo "<p>Found database at: $path</p>";
        break;
    }
}

if (!$db_path) {
    echo "<p style='color:red'>Database file not found!</p>";
    exit;
}

try {
    // Connect to the database
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "<p style='color:green'>Successfully connected to the database!</p>";
    
    // Check if users table exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    $table_exists = $stmt->fetch();
    
    if ($table_exists) {
        echo "<p style='color:green'>Users table exists!</p>";
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $user_count = $stmt->fetchColumn();
        echo "<p>Found $user_count users in the database.</p>";
        
        // List users
        $stmt = $pdo->query("SELECT id, name, email, role FROM users");
        $users = $stmt->fetchAll();
        
        echo "<h2>Users in Database:</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test authentication with admin@siloe.com / admin123
        $email = 'admin@siloe.com';
        $password = 'admin123';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<h2>Testing Authentication for $email</h2>";
            echo "<p>Found user with ID: " . $user['id'] . "</p>";
            echo "<p>Password hash: " . substr($user['password'], 0, 20) . "...</p>";
            
            if (password_verify($password, $user['password'])) {
                echo "<p style='color:green'>Password verification successful!</p>";
                echo "<p>You can log in with:</p>";
                echo "<ul>";
                echo "<li>Email: $email</li>";
                echo "<li>Password: $password</li>";
                echo "</ul>";
            } else {
                echo "<p style='color:red'>Password verification failed!</p>";
                
                // Create a new password hash for comparison
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                echo "<p>Generated hash for '$password': " . substr($new_hash, 0, 20) . "...</p>";
                
                // Update the password
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$new_hash, $email]);
                echo "<p style='color:green'>Updated password for $email to '$password'</p>";
            }
        } else {
            echo "<p style='color:red'>User with email $email not found!</p>";
            
            // Create admin user
            $name = 'Admin';
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin';
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password_hash, $role]);
            
            echo "<p style='color:green'>Created new admin user:</p>";
            echo "<ul>";
            echo "<li>Email: $email</li>";
            echo "<li>Password: $password</li>";
            echo "</ul>";
        }
    } else {
        echo "<p style='color:red'>Users table does not exist!</p>";
        
        // Create users table
        $pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        echo "<p style='color:green'>Created users table!</p>";
        
        // Create admin user
        $name = 'Admin';
        $email = 'admin@siloe.com';
        $password = 'admin123';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password_hash, $role]);
        
        echo "<p style='color:green'>Created admin user:</p>";
        echo "<ul>";
        echo "<li>Email: $email</li>";
        echo "<li>Password: $password</li>";
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Database error: " . $e->getMessage() . "</p>";
}
