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

// Admin Routes
$router->get('/admin/dashboard', 'Admin\DashboardController', 'index');
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
$router->delete('/orders/{id}', 'OrderController', 'destroy');

// Fallback route - must be the last route
$router->any('{any}', 'ErrorController', 'notFound');
