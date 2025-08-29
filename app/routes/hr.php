<?php

/** @var \App\Core\Router $router */

// HR Dashboard
$router->get('/hr/{company_id}/dashboard', 'HRController', 'dashboard');

// Employee Authentication
$router->get('/hr/{company_id}/login', 'AuthController', 'showEmployeeLoginForm');
$router->post('/hr/{company_id}/login', 'AuthController', 'employeeLogin');

// Employee Management
$router->get('/hr/{company_id}/employees', 'HRController', 'index');
$router->get('/hr/{company_id}/employees/create', 'HRController', 'create');
$router->post('/hr/{company_id}/employees', 'HRController', 'store');

// Employee specific routes - most specific first
$router->get('/hr/{company_id}/employees/{id}/selections', 'EmployeeMenuController', 'getEmployeeSelections');
$router->get('/hr/{company_id}/employees/{id}/{name}/edit', 'HRController', 'edit');
// Allow edit without name slug
$router->get('/hr/{company_id}/employees/{id}/edit', 'HRController', 'edit');
$router->post('/hr/{company_id}/employees/{id}/{name}', 'HRController', 'update');
// Support RESTful update via PUT without name slug (matches edit form action)
$router->put('/hr/{company_id}/employees/{id}', 'HRController', 'update');
$router->get('/hr/{company_id}/employees/{id}/{name}/deactivate', 'HRController', 'confirmDeactivate');
$router->post('/hr/{company_id}/employees/{id}/{name}/deactivate', 'HRController', 'deactivate');
// Allow reactivate without name slug (matches index view form)
$router->post('/hr/{company_id}/employees/{id}/reactivate', 'HRController', 'reactivate');
$router->post('/hr/{company_id}/employees/{id}/{name}/reactivate', 'HRController', 'reactivate');

// General employee show route - must be last
// Router doesn't support optional segments; provide both variants
$router->get('/hr/{company_id}/employees/{id}', 'HRController', 'show');
$router->get('/hr/{company_id}/employees/{id}/{name}', 'HRController', 'show');

// Employee Menu Selections
$router->get('/hr/{company_id}/menu-selections/today', 'HRController', 'todaysSelections');
$router->get('/hr/{company_id}/menu-selections/history', 'HRController', 'selectionsHistory');

// Reports
$router->get('/hr/{company_id}/reports/selections', 'HRController', 'selectionsHistory');
$router->get('/hr/{company_id}/reports/employees', 'HRController', 'index');

// Settings
$router->get('/hr/{company_id}/settings/notifications', 'HRController', 'notificationSettings');
$router->get('/hr/{company_id}/settings/preferences', 'HRController', 'hrPreferences');

// Legacy routes for backward compatibility (redirect to company-specific routes)
$router->get('/hr/dashboard', 'HRController', 'legacyDashboard');
$router->get('/hr/employees', 'HRController', 'legacyEmployees');
$router->get('/hr/menu-selections/today', 'HRController', 'legacyTodaysSelections');
$router->get('/hr/menu-selections/history', 'HRController', 'legacySelectionsHistory');
