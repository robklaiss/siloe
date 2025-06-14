<?php

/** @var \App\Core\Router $router */

// HR Dashboard
$router->get('/hr/dashboard', 'HRController', 'dashboard');

// Employee Management
$router->get('/hr/employees', 'HRController', 'index');
$router->get('/hr/employees/create', 'HRController', 'create');
$router->post('/hr/employees', 'HRController', 'store');
$router->get('/hr/employees/{id}/deactivate', 'HRController', 'confirmDeactivate');
$router->post('/hr/employees/{id}/deactivate', 'HRController', 'deactivate');
$router->post('/hr/employees/{id}/reactivate', 'HRController', 'reactivate');

// Employee Menu Selections
$router->get('/hr/employees/{id}/selections', 'EmployeeMenuController', 'getEmployeeSelections');
