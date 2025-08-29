<?php
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Key Session Values:</h3>";
echo "<ul>";
echo "<li><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</li>";
echo "<li><strong>User Role:</strong> " . ($_SESSION['user_role'] ?? 'Not set') . "</li>";
echo "<li><strong>User Name:</strong> " . ($_SESSION['user_name'] ?? 'Not set') . "</li>";
echo "<li><strong>Company ID:</strong> " . ($_SESSION['company_id'] ?? 'Not set') . "</li>";
echo "<li><strong>Session ID:</strong> " . session_id() . "</li>";
echo "</ul>";

echo "<h3>HR Access Check:</h3>";
$hasAccess = isset($_SESSION['user_id']) && 
             isset($_SESSION['user_role']) && 
             in_array($_SESSION['user_role'], ['company_admin', 'admin']);

echo "<p><strong>Can access HR routes:</strong> " . ($hasAccess ? "✅ YES" : "❌ NO") . "</p>";

if (!$hasAccess) {
    echo "<h3>Why no access:</h3>";
    echo "<ul>";
    if (!isset($_SESSION['user_id'])) {
        echo "<li>❌ Not logged in (no user_id)</li>";
    } else {
        echo "<li>✅ Logged in (user_id: " . $_SESSION['user_id'] . ")</li>";
    }
    
    if (!isset($_SESSION['user_role'])) {
        echo "<li>❌ No user role set</li>";
    } elseif (!in_array($_SESSION['user_role'], ['company_admin', 'admin'])) {
        echo "<li>❌ User role '" . $_SESSION['user_role'] . "' is not 'company_admin' or 'admin'</li>";
    } else {
        echo "<li>✅ User role is authorized</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='/login'>Go to Login</a> | <a href='/dashboard'>Go to Dashboard</a> | <a href='/hr/employees/create'>Try HR Create Employee</a></p>";
?>
