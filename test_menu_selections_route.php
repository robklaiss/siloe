<?php
// Test the menu selections route directly
session_start();

// Set up session to simulate logged-in admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_name'] = 'Admin User';

// Include the bootstrap file to set up the application
require_once __DIR__ . '/public/index.php';
?>
