<?php

error_log('=== LOADING ROUTES ===');

// Test route
$router->get('/test', 'HomeController', 'index');

// Homepage
$router->get('/', 'HomeController', 'index');

// Authentication Routes
$router->get('/login', 'AuthController', 'showLoginForm');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'showRegistrationForm');
$router->post('/register', 'AuthController', 'register');
$router->post('/logout', 'AuthController', 'logout');

// Dashboard (protected route)
$router->get('/dashboard', 'DashboardController', 'index');

// Profile
$router->get('/profile', 'ProfileController', 'edit');
$router->put('/profile', 'ProfileController', 'update');
$router->put('/profile/password', 'ProfileController', 'updatePassword');

// Admin routes
$router->get('/admin/dashboard', 'Admin\DashboardController', 'index');

// Company Management Routes
$router->get('/admin/companies', 'Admin\CompanyController', 'index');
$router->get('/admin/companies/create', 'Admin\CompanyController', 'create');
$router->post('/admin/companies', 'Admin\CompanyController', 'store');
$router->get('/admin/companies/{id}', 'Admin\CompanyController', 'show');
$router->get('/admin/companies/{id}/edit', 'Admin\CompanyController', 'edit');
$router->put('/admin/companies/{id}', 'Admin\CompanyController', 'update');
$router->post('/admin/companies/{id}/delete', 'Admin\CompanyController', 'destroy');
$router->get('/admin/companies/{id}/hr', 'Admin\CompanyController', 'hrDashboard');
$router->get('/admin/companies/{id}/employee', 'Admin\CompanyController', 'employeeDashboard');

// User management routes
$router->get('/admin/users', 'Admin\UserController', 'index');
$router->get('/admin/users/create', 'Admin\UserController', 'create');
$router->post('/admin/users', 'Admin\UserController', 'store');
$router->get('/admin/users/{id}/edit', 'Admin\UserController', 'edit');
$router->put('/admin/users/{id}', 'Admin\UserController', 'update');
$router->delete('/admin/users/{id}', 'Admin\UserController', 'destroy');

// Menu Management
$router->get('/menus', 'MenuController', 'index');
$router->get('/menus/create', 'MenuController', 'create');
$router->post('/menus', 'MenuController', 'store');
$router->get('/menus/{id}/edit', 'MenuController', 'edit');
$router->put('/menus/{id}', 'MenuController', 'update');
$router->delete('/menus/{id}', 'MenuController', 'destroy');

// Orders
$router->get('/orders', 'OrderController', 'index');
$router->get('/orders/create', 'OrderController', 'create');
$router->post('/orders', 'OrderController', 'store');
$router->get('/orders/{id}', 'OrderController', 'show');
$router->get('/orders/{id}/edit', 'OrderController', 'edit');
$router->put('/orders/{id}', 'OrderController', 'update');
$router->post('/orders/{id}/status', 'OrderController', 'updateStatus');
$router->delete('/orders/{id}', 'OrderController', 'destroy');

// Load HR routes
if (file_exists(__DIR__ . '/hr.php')) {
    require_once __DIR__ . '/hr.php';
} else {
    error_log('HR routes file not found');
}

// Load Employee routes
if (file_exists(__DIR__ . '/employee.php')) {
    require_once __DIR__ . '/employee.php';
} else {
    error_log('Employee routes file not found');
}

// Debug route to display all registered routes
$router->get('/debug/routes', 'DebugController', 'routes');

// Fallback route - must be the last route
$router->any('{any}', 'ErrorController', 'notFound');
