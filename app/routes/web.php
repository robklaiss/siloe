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
$router->get('/profile', 'ProfileController', 'index');
$router->get('/profile/edit', 'ProfileController', 'edit');
$router->put('/profile', 'ProfileController', 'update');
$router->put('/profile/password', 'ProfileController', 'updatePassword');
$router->get('/profile/security', 'ProfileController', 'security');

// Admin routes
$router->get('/admin/dashboard', 'Admin\DashboardController', 'index');

// Delete requests management
$router->get('/admin/delete-requests', 'Admin\DeleteRequestController', 'index');
$router->get('/admin/delete-requests/{id}', 'Admin\DeleteRequestController', 'show');
$router->post('/admin/delete-requests/{id}/approve', 'Admin\DeleteRequestController', 'approve');
$router->post('/admin/delete-requests/{id}/reject', 'Admin\DeleteRequestController', 'reject');

// HR routes are loaded later conditionally

// Company Management Routes
$router->get('/admin/companies', 'Admin\CompanyController', 'index');
$router->get('/admin/companies/create', 'Admin\CompanyController', 'create');
$router->post('/admin/companies', 'Admin\CompanyController', 'store');
$router->get('/admin/companies/{id}', 'Admin\CompanyController', 'show');
$router->get('/admin/companies/{id}/edit', 'Admin\CompanyController', 'edit');
$router->put('/admin/companies/{id}', 'Admin\CompanyController', 'update');
// Fallback: accept POST for update in case method override (_method=PUT) is not available
$router->post('/admin/companies/{id}', 'Admin\CompanyController', 'update');
$router->post('/admin/companies/{id}/delete', 'Admin\CompanyController', 'destroy');
// Company HR dashboards
$router->get('/admin/companies/{id}/hr', 'Admin\CompanyController', 'hrDashboard');
$router->get('/admin/companies/{id}/employee', 'Admin\CompanyController', 'employeeDashboard');
// Map reports routes to existing controller actions
 
// Company HR Employee Management Routes
$router->get('/admin/companies/{id}/hr/employees/create', 'Admin\CompanyController', 'createEmployee');
$router->post('/admin/companies/{id}/hr/employees', 'Admin\CompanyController', 'storeEmployee');

// Company HR Menu Selection Routes
$router->get('/admin/companies/{id}/hr/menu-selections/today', 'Admin\CompanyController', 'todaysMenuSelections');
$router->get('/admin/companies/{id}/hr/menu-selections/history', 'Admin\CompanyController', 'selectionsHistory');

// User management routes
$router->get('/admin/users', 'Admin\UserController', 'index');
$router->get('/admin/users/create', 'Admin\UserController', 'create');
$router->post('/admin/users', 'Admin\UserController', 'store');
$router->get('/admin/users/{id}/edit', 'Admin\UserController', 'edit');
$router->put('/admin/users/{id}', 'Admin\UserController', 'update');
$router->delete('/admin/users/{id}', 'Admin\UserController', 'destroy');

// Menu Management
$router->get('/menus', 'MenuController', 'index');
$router->get('/menus/new', 'MenuController', 'indexNew');
$router->get('/menus/create', 'MenuController', 'create');
$router->post('/menus', 'MenuController', 'store');
$router->get('/menus/{id}/edit', 'MenuController', 'edit');
$router->put('/menus/{id}', 'MenuController', 'update');
$router->delete('/menus/{id}', 'MenuController', 'destroy');

    // AJAX routes for menu items
    $router->post('/api/beverages/save', 'MenuController', 'saveBeverage');
    $router->post('/api/desserts/save', 'MenuController', 'saveDessert');
    $router->get('/api/beverages', 'MenuController', 'getBeverages');
    $router->get('/api/weekly-items', 'MenuController', 'getWeeklyItemsByCategory');
    $router->put('/api/weekly-items/{id}', 'MenuController', 'updateWeeklyItem');
    $router->delete('/api/weekly-items/{id}', 'MenuController', 'deleteWeeklyItem');

// Debug route to test menu data
$router->get('/debug/menus', 'MenuController', 'debugMenus');

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

// Debug routes
$router->get('/debug/routes', 'DebugController', 'routes');
$router->get('/debug/orders', 'DebugController', 'debugOrders');

// Fallback is handled by Router::notFound(); no explicit route required
