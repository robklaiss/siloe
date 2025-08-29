<?php
// Database initialization script
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to create SQLite database
function create_sqlite_database($db_path) {
    try {
        // Create directory if it doesn't exist
        $dir = dirname($db_path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return "Failed to create directory: $dir";
            }
        }
        
        // Check if database file exists
        $db_exists = file_exists($db_path);
        
        // Create or open the database
        $db = new PDO("sqlite:$db_path");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // If database didn't exist, create tables
        if (!$db_exists) {
            // Create users table
            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT 'user',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Create admin user
            $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['Administrator', 'admin@example.com', $password_hash, 'admin']);
            
            return "Database created and initialized with admin user";
        } else {
            // Check if admin user exists
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(['admin@example.com']);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin) {
                // Create admin user
                $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute(['Administrator', 'admin@example.com', $password_hash, 'admin']);
                return "Admin user created in existing database";
            } else {
                // Update admin password
                $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$password_hash, 'admin@example.com']);
                return "Admin user updated in existing database";
            }
        }
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

// Possible database paths
$db_paths = [
    '/home1/siloecom/siloe/database/database.sqlite',
    '/home1/siloecom/siloe/public/database/database.sqlite',
    '/home1/siloecom/public_html/database/database.sqlite',
    '/home1/siloecom/public_html/database.sqlite'
];

$results = [];

// Try to create/initialize database at each path
foreach ($db_paths as $db_path) {
    $results[$db_path] = create_sqlite_database($db_path);
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Initialization - Siloe</title>
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
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Database Initialization</h1>
    
    <div class="success">
        <h2>Database Initialization Results</h2>
        <?php foreach ($results as $path => $result): ?>
            <p><strong><?php echo $path; ?>:</strong> <?php echo $result; ?></p>
        <?php endforeach; ?>
    </div>
    
    <div class="info">
        <h2>Admin User Credentials</h2>
        <p>Email: admin@example.com</p>
        <p>Password: Admin123!</p>
    </div>
    
    <p>
        <a href="/" class="button">Go to Home</a>
    </p>
    
    <h2>File System Check</h2>
    <?php
    $dirs = [
        '/home1/siloecom/siloe',
        '/home1/siloecom/siloe/database',
        '/home1/siloecom/siloe/public',
        '/home1/siloecom/public_html'
    ];
    
    foreach ($dirs as $dir) {
        echo "<p><strong>Directory:</strong> $dir</p>";
        if (is_dir($dir)) {
            echo "<p class='success'>Directory exists</p>";
            echo "<p>Readable: " . (is_readable($dir) ? "Yes" : "No") . "</p>";
            echo "<p>Writable: " . (is_writable($dir) ? "Yes" : "No") . "</p>";
        } else {
            echo "<p class='error'>Directory does not exist</p>";
        }
    }
    ?>
</body>
</html>
