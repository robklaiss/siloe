<?php

/** @var \App\Core\Router $router */

// Menu Selection
$router->get('/menu/select', 'EmployeeMenuController', 'showSelectionForm');
$router->post('/menu/select', 'EmployeeMenuController', 'processSelection');

// View selection history (for employees to see their own history)
$router->get('/menu/my-selections', 'EmployeeMenuController', 'mySelections');
