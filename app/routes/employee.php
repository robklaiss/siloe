<?php

/** @var \App\Core\Router $router */

// Menu Selection
$router->get('/menu/select', 'EmployeeMenuController', 'showSelectionForm');
$router->post('/menu/select', 'EmployeeMenuController', 'processSelection');

// Other menu items selection
$router->get('/menu/other-items', 'EmployeeMenuController', 'showOtherItems');

// View selection history (for employees to see their own history)
$router->get('/menu/my-selections', 'EmployeeMenuController', 'mySelections');
