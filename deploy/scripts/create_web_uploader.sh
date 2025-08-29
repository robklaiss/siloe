#!/bin/bash

# Create a web-based file uploader to help deploy the password reset script
# This script creates a PHP file uploader and uploads it to the server

echo "Creating web-based file uploader..."

# Create the PHP uploader script
cat > web_uploader.php << 'EOL'
<?php
// Simple web-based file uploader for Siloe
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set password for security
$upload_password = "siloe2025";
$authenticated = false;

// Check if password is provided and correct
if (isset($_POST['password']) && $_POST['password'] === $upload_password) {
    $authenticated = true;
    // Store in session to maintain authentication
    session_start();
    $_SESSION['authenticated'] = true;
} elseif (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    $authenticated = true;
}

// Handle file upload
$upload_message = '';
if ($authenticated && isset($_FILES['file'])) {
    $target_dir = __DIR__ . '/';
    $target_file = $target_dir . basename($_FILES["file"]["name"]);
    
    // Check if file already exists
    if (file_exists($target_file)) {
        $upload_message = "<div style='color: orange;'>⚠️ File already exists. Overwriting...</div>";
    }
    
    // Try to upload file
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $upload_message = "<div style='color: green;'>✅ The file " . htmlspecialchars(basename($_FILES["file"]["name"])) . " has been uploaded.</div>";
        
        // Set permissions
        chmod($target_file, 0644);
    } else {
        $upload_message = "<div style='color: red;'>❌ Sorry, there was an error uploading your file.</div>";
    }
}

// HTML for the page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Siloe File Uploader</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"], input[type="file"] {
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin: 15px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .file-list {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .file-item {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .file-item:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .security-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Siloe File Uploader</h1>
        
        <?php if (!$authenticated): ?>
            <form method="post">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>
        <?php else: ?>
            <div class="message">
                <p>✅ Authenticated successfully</p>
            </div>
            
            <?php if (!empty($upload_message)): ?>
                <div class="message">
                    <?php echo $upload_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Select file to upload:</label>
                    <input type="file" id="file" name="file" required>
                </div>
                <button type="submit">Upload File</button>
            </form>
            
            <div class="file-list">
                <h2>Files in current directory:</h2>
                <?php
                $files = scandir(__DIR__);
                foreach ($files as $file) {
                    if ($file != "." && $file != ".." && !is_dir($file)) {
                        echo "<div class='file-item'>";
                        echo "<a href='" . htmlspecialchars($file) . "' target='_blank'>" . htmlspecialchars($file) . "</a>";
                        echo " (" . round(filesize($file) / 1024, 2) . " KB)";
                        echo "</div>";
                    }
                }
                ?>
            </div>
            
            <div class="security-warning">
                <strong>⚠️ Security Warning:</strong> For security reasons, please delete this uploader after use.
                <form method="post">
                    <input type="hidden" name="delete_uploader" value="1">
                    <button type="submit" style="background-color: #dc3545; margin-top: 10px;">Delete This Uploader</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// Self-destruct if requested
if ($authenticated && isset($_POST['delete_uploader'])) {
    unlink(__FILE__);
    echo "<script>window.location = '/';</script>";
    exit;
}
?>
EOL

echo "Uploading web uploader to server..."
scp web_uploader.php siloecom@192.185.143.154:/home1/siloecom/public_html/

echo "Cleaning up local files..."
rm web_uploader.php

echo "Done!"
echo "Access the web uploader at: http://www.siloe.com.py/web_uploader.php"
echo "Password: siloe2025"
echo "After using it, make sure to delete it from the server for security."
