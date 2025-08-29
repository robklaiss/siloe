#!/bin/bash

# Configuration
SERVER="siloecom@192.185.143.154"

echo "Executing direct fix on server..."

# Create a temporary fix script
cat > /tmp/fix_auth.sh << 'EOL'
#!/bin/bash

# Define paths
CONTROLLERS_DIR="/home1/siloecom/siloe/public/app/Controllers"
DB_DIR="/home1/siloecom/siloe/public/database"
DB_FILE="$DB_DIR/siloe.db"

echo "Starting auth system fix..."

# Step 1: Fix AuthController.php
echo "Fixing AuthController.php..."
mkdir -p "$CONTROLLERS_DIR"

cat > "$CONTROLLERS_DIR/AuthController.php" << 'EOF'
<?php
namespace App\Controllers;

use App\Core\Controller;
use PDO;
use PDOException;

class AuthController extends Controller {
    protected function getDbConnection() {
        static $db = null;
        if ($db === null) {
            try {
                if (defined('DB_DRIVER') && DB_DRIVER === 'mysql') {
                    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                    $db = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } else {
                    $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../../database/siloe.db';
                    $db = new PDO('sqlite:' . $dbPath);
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $db->exec('PRAGMA foreign_keys = ON;');
                }
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                throw $e;
            }
        }
        return $db;
    }

    public function showLoginForm() {
        $this->view('auth/login', [
            'title' => 'Login',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Verify CSRF token
        if (!$this->session->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->session->setFlash('error', 'Invalid security token. Please try again.');
            header('Location: /login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $this->session->setFlash('error', 'Invalid email or password.');
                header('Location: /login');
                exit;
            }

            // Login successful
            $this->session->set('user_id', $user['id']);
            $this->session->set('user_name', $user['name']);
            $this->session->set('user_email', $user['email']);
            $this->session->set('user_role', $user['role']);

            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30); // 30 days

                $stmt = $db->prepare('UPDATE users SET remember_token = :token WHERE id = :id');
                $stmt->execute([
                    'token' => $token,
                    'id' => $user['id']
                ]);

                setcookie('remember_token', $token, $expiry, '/', '', false, true);
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /dashboard');
            }
            exit;
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $this->session->setFlash('error', 'An error occurred. Please try again later.');
            header('Location: /login');
            exit;
        }
    }

    public function logout() {
        $this->session->destroy();
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        header('Location: /login');
        exit;
    }
}
EOF

chmod 644 "$CONTROLLERS_DIR/AuthController.php"
echo "AuthController.php fixed successfully!"

# Step 2: Create database directory and file
echo "Setting up database..."
mkdir -p "$DB_DIR"
touch "$DB_FILE"
chmod 644 "$DB_FILE"

# Step 3: Create auth tables
echo "Creating auth tables..."
cat > /tmp/create_tables.php << 'EOF'
<?php
try {
    $dbFile = '/home1/siloecom/siloe/public/database/siloe.db';
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Create users table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    company_id INTEGER,
    remember_token TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL);
    
    // Create password_resets table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL);
    
    // Create sessions table
    $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    payload TEXT,
    last_activity INTEGER
);
SQL);
    
    // Check if admin user already exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => 'admin@example.com']);
    if (!$stmt->fetch()) {
        // Create admin user
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
        $stmt->execute([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password is 'password'
            'role' => 'admin'
        ]);
        echo "Created default admin user\n";
    } else {
        echo "Admin user already exists\n";
    }
    
    echo "Database tables created successfully!\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
EOF

php /tmp/create_tables.php
echo "Auth tables created successfully!"

echo "Auth system fix complete!"
echo "You can now login with:"
echo "Email: admin@example.com"
echo "Password: password"
echo "IMPORTANT: Change these credentials immediately after logging in!"
EOL

# Make the fix script executable
chmod +x /tmp/fix_auth.sh

# Upload and execute the fix script on the server
scp -o PreferredAuthentications=password /tmp/fix_auth.sh $SERVER:/home/siloecom/
ssh -o PreferredAuthentications=password $SERVER "bash /home/siloecom/fix_auth.sh"

echo "Direct fix completed!"
