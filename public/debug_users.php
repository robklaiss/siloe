<?php
// Include the config to get database connection
require_once __DIR__ . '/../app/config/config.php';

echo "<h2>User Debug Information</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all users with their roles
    $stmt = $pdo->query("SELECT id, name, email, role, is_active, company_id, created_at FROM users ORDER BY role, name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>All Users in System:</h3>";
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Company ID</th><th>Created</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        $roleColor = match($user['role']) {
            'admin' => '#ff6b6b',
            'company_admin' => '#4ecdc4', 
            'employee' => '#45b7d1',
            default => '#95a5a6'
        };
        
        $activeStatus = $user['is_active'] ? '✅ Active' : '❌ Inactive';
        
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td style='background-color: {$roleColor}; color: white; font-weight: bold;'>{$user['role']}</td>";
        echo "<td>{$activeStatus}</td>";
        echo "<td>{$user['company_id']}</td>";
        echo "<td>" . date('Y-m-d H:i', strtotime($user['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show role summary
    $roleCount = [];
    foreach ($users as $user) {
        $roleCount[$user['role']] = ($roleCount[$user['role']] ?? 0) + 1;
    }
    
    echo "<h3>Role Summary:</h3>";
    echo "<ul>";
    foreach ($roleCount as $role => $count) {
        $canAccessHR = in_array($role, ['admin', 'company_admin']) ? '✅ Can access HR' : '❌ Cannot access HR';
        echo "<li><strong>{$role}:</strong> {$count} users - {$canAccessHR}</li>";
    }
    echo "</ul>";
    
    echo "<h3>HR Access Requirements:</h3>";
    echo "<p>To access <code>/hr/employees/create</code>, you need to login as a user with role:</p>";
    echo "<ul>";
    echo "<li><strong>admin</strong> - Full system administrator</li>";
    echo "<li><strong>company_admin</strong> - Company HR administrator</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error connecting to database: " . $e->getMessage() . "</p>";
    echo "<p>Make sure your database is running and configured correctly in the config file.</p>";
}

echo "<hr>";
echo "<p><a href='/login'>Go to Login</a> | <a href='/debug_session.php'>Check Session</a> | <a href='/'>Home</a></p>";
?>
