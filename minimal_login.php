<?php
// Minimal login script that should work regardless of server configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to log in as admin
function login_as_admin() {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_email'] = 'admin@example.com';
    $_SESSION['user_role'] = 'admin';
    $_SESSION['authenticated'] = true;
    return true;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login_admin') {
            login_as_admin();
        }
    }
}

// Display page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal Login - Siloe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: red;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        button, .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px 0;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .button:hover {
            background-color: #45a049;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Minimal Login Solution</h1>
    
    <?php if (is_logged_in()): ?>
        <div class="success">
            <h2>You are logged in!</h2>
            <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
            <p>Email: <?php echo $_SESSION['user_email']; ?></p>
            <p>Role: <?php echo $_SESSION['user_role']; ?></p>
        </div>
        
        <h2>Session Information</h2>
        <pre><?php print_r($_SESSION); ?></pre>
        
        <h2>Navigation</h2>
        <p>
            <a href="/" class="button">Home</a>
            <a href="/dashboard" class="button">Dashboard</a>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="/admin/dashboard" class="button">Admin Dashboard</a>
            <?php endif; ?>
        </p>
        
        <form method="post" action="">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Logout</button>
        </form>
    <?php else: ?>
        <div class="info">
            <p>You are not logged in. Click the button below to log in as admin.</p>
        </div>
        
        <form method="post" action="">
            <input type="hidden" name="action" value="login_admin">
            <button type="submit">Login as Admin</button>
        </form>
    <?php endif; ?>
    
    <h2>Server Information</h2>
    <div class="info">
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
        <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></p>
        <p>Current Script: <?php echo $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown'; ?></p>
        <p>Session ID: <?php echo session_id(); ?></p>
        <p>Session Save Path: <?php echo session_save_path(); ?></p>
    </div>
    
    <h2>File System Check</h2>
    <?php
    $current_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    echo "<p>Current directory: $current_dir</p>";
    
    // Check if we can write to the current directory
    if (is_writable($current_dir)) {
        echo "<p class='success'>Current directory is writable</p>";
    } else {
        echo "<p class='error'>Current directory is not writable</p>";
    }
    
    // Create a test file
    $test_file = $current_dir . '/test_write.txt';
    if (file_put_contents($test_file, 'Test write at ' . date('Y-m-d H:i:s'))) {
        echo "<p class='success'>Successfully wrote to test file</p>";
        unlink($test_file);
        echo "<p>Test file removed</p>";
    } else {
        echo "<p class='error'>Failed to write to test file</p>";
    }
    ?>
</body>
</html>
