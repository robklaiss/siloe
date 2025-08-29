#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Creating direct login script..."

# Create a direct login script that bypasses routing
cat > /tmp/direct_login.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Direct Login System</h1>";

// Process login form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Processing Login</h2>";
    echo "<p>Attempting to login with email: $email</p>";
    
    // Define paths to check
    $possiblePaths = [
        __DIR__ . '/../../database/siloe.db',
        __DIR__ . '/../database/siloe.db',
        __DIR__ . '/database/siloe.db',
        '/home1/siloecom/siloe/public/database/siloe.db',
        '/home1/siloecom/siloe/public/public/database/siloe.db'
    ];
    
    // Find the first existing database
    $dbPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $dbPath = $path;
            echo "<p>Using database at: $dbPath</p>";
            break;
        }
    }
    
    if (!$dbPath) {
        echo "<p class='error'>No database found! Tried paths:</p>";
        echo "<ul>";
        foreach ($possiblePaths as $path) {
            echo "<li>$path</li>";
        }
        echo "</ul>";
    } else {
        try {
            $db = new PDO('sqlite:' . $dbPath);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if users table exists
            $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(PDO::FETCH_COLUMN);
            
            if (!in_array('users', $tables)) {
                echo "<p class='error'>Users table not found in database!</p>";
            } else {
                // Attempt to find user
                $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    echo "<p class='error'>User not found with email: $email</p>";
                } else {
                    echo "<p>User found: ID={$user['id']}, Name={$user['name']}, Role={$user['role']}</p>";
                    
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        echo "<p class='success'>Password verification successful!</p>";
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        echo "<p class='success'>Login successful! Session created.</p>";
                        echo "<p>Session ID: " . session_id() . "</p>";
                        echo "<p>Session data:</p>";
                        echo "<pre>";
                        print_r($_SESSION);
                        echo "</pre>";
                        
                        // Create a session cookie manually
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (86400 * 30); // 30 days
                        
                        $stmt = $db->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
                        $stmt->execute([
                            'token' => $token,
                            'id' => $user['id']
                        ]);
                        
                        setcookie('remember_token', $token, $expiry, '/', '', false, true);
                        echo "<p class='success'>Remember token set: " . substr($token, 0, 10) . "...</p>";
                        
                        // Provide links to protected areas
                        echo "<div class='navigation'>";
                        echo "<h3>Navigation</h3>";
                        echo "<ul>";
                        echo "<li><a href='/dashboard'>Go to Dashboard</a></li>";
                        if ($user['role'] === 'admin') {
                            echo "<li><a href='/admin/dashboard'>Go to Admin Dashboard</a></li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                    } else {
                        echo "<p class='error'>Password verification failed!</p>";
                        echo "<p>Stored password hash: " . substr($user['password'], 0, 10) . "...</p>";
                        echo "<p>Expected password: 'password'</p>";
                        
                        // Create a new password hash for comparison
                        $newHash = password_hash('password', PASSWORD_DEFAULT);
                        echo "<p>New hash for 'password': " . substr($newHash, 0, 10) . "...</p>";
                        
                        // Try to update the password
                        echo "<h3>Attempting to reset password</h3>";
                        $stmt = $db->prepare('UPDATE users SET password = :password WHERE email = :email');
                        $stmt->execute([
                            'password' => $newHash,
                            'email' => $email
                        ]);
                        
                        if ($stmt->rowCount() > 0) {
                            echo "<p class='success'>Password updated successfully! Please try logging in again.</p>";
                        } else {
                            echo "<p class='error'>Failed to update password.</p>";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Database error: " . $e->getMessage() . "</p>";
        }
    }
}

// Display login form
echo "<h2>Login Form</h2>";
echo "<form method='post' action=''>";
echo "<div><label>Email: <input type='email' name='email' value='admin@example.com' required></label></div>";
echo "<div><label>Password: <input type='password' name='password' value='password' required></label></div>";
echo "<div><button type='submit'>Login</button></div>";
echo "</form>";

// Add some CSS for better presentation
echo <<<HTML
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        color: #333;
    }
    h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    h2 {
        color: #2980b9;
        margin-top: 20px;
    }
    p {
        margin: 10px 0;
    }
    .success {
        color: #27ae60;
        font-weight: bold;
    }
    .error {
        color: #e74c3c;
        font-weight: bold;
    }
    form {
        background-color: #f8f9fa;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin: 20px 0;
    }
    form div {
        margin-bottom: 10px;
    }
    input {
        padding: 5px;
        width: 300px;
    }
    button {
        padding: 8px 15px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .navigation {
        background-color: #eaf2f8;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }
    pre {
        background-color: #f8f9fa;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 3px;
        overflow: auto;
    }
</style>
HTML;
EOL

# Upload the direct login script
echo "Uploading direct login script to server..."
scp -o PreferredAuthentications=password /tmp/direct_login.php $SERVER:/home1/siloecom/siloe/public/public/direct_login.php

# Create a script to fix the session directory permissions
cat > /tmp/fix_sessions.php << 'EOL'
<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Session Directory Fix</h1>";

// Get session save path
$sessionPath = session_save_path();
echo "<p>Current session save path: $sessionPath</p>";

// Check if directory exists and is writable
if (!empty($sessionPath)) {
    if (is_dir($sessionPath)) {
        echo "<p>Session directory exists</p>";
        if (is_writable($sessionPath)) {
            echo "<p class='success'>Session directory is writable</p>";
        } else {
            echo "<p class='error'>Session directory is not writable</p>";
            
            // Try to make it writable
            if (@chmod($sessionPath, 0777)) {
                echo "<p class='success'>Successfully made session directory writable</p>";
            } else {
                echo "<p class='error'>Failed to make session directory writable</p>";
            }
        }
    } else {
        echo "<p class='error'>Session directory does not exist</p>";
        
        // Try to create it
        if (@mkdir($sessionPath, 0777, true)) {
            echo "<p class='success'>Successfully created session directory</p>";
        } else {
            echo "<p class='error'>Failed to create session directory</p>";
        }
    }
} else {
    echo "<p>No session save path configured, using default</p>";
}

// Create a custom session directory
$customSessionDir = __DIR__ . '/sessions';
if (!is_dir($customSessionDir)) {
    if (mkdir($customSessionDir, 0777, true)) {
        echo "<p class='success'>Created custom session directory: $customSessionDir</p>";
    } else {
        echo "<p class='error'>Failed to create custom session directory</p>";
    }
} else {
    echo "<p>Custom session directory already exists: $customSessionDir</p>";
    
    // Make sure it's writable
    if (is_writable($customSessionDir)) {
        echo "<p class='success'>Custom session directory is writable</p>";
    } else {
        if (@chmod($customSessionDir, 0777)) {
            echo "<p class='success'>Made custom session directory writable</p>";
        } else {
            echo "<p class='error'>Failed to make custom session directory writable</p>";
        }
    }
}

// Add some CSS for better presentation
echo <<<HTML
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        color: #333;
    }
    h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
    }
    p {
        margin: 10px 0;
    }
    .success {
        color: #27ae60;
        font-weight: bold;
    }
    .error {
        color: #e74c3c;
        font-weight: bold;
    }
</style>
HTML;
EOL

# Upload the session fix script
echo "Uploading session fix script to server..."
scp -o PreferredAuthentications=password /tmp/fix_sessions.php $SERVER:/home1/siloecom/siloe/public/public/fix_sessions.php

echo "Direct login script uploaded. Access it at: http://www.siloe.com.py/direct_login.php"
echo "Session fix script uploaded. Access it at: http://www.siloe.com.py/fix_sessions.php"
