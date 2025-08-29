<?php
// Direct admin access script
session_start();

// Set session cookie parameters for better persistence
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400); // 24 hours

// Set admin session variables
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@example.com';
$_SESSION['user_role'] = 'admin';
$_SESSION['authenticated'] = true;

// Display success message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access - Siloe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            color: green;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button {
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
    </style>
</head>
<body>
    <h1>Admin Access</h1>
    
    <div class="success">
        <h2>Admin Access Granted</h2>
        <p>You have been logged in as an administrator.</p>
        <p>Session ID: <?php echo session_id(); ?></p>
    </div>
    
    <p>
        <a href="/" class="button">Go to Home</a>
        <a href="/dashboard" class="button">Go to Dashboard</a>
        <a href="/admin/dashboard" class="button">Go to Admin Dashboard</a>
    </p>
    
    <h2>Session Information</h2>
    <pre><?php print_r($_SESSION); ?></pre>
</body>
</html>
