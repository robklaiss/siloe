<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\Company;
use App\Models\EmployeeMenuSelection;

class HRController extends Controller {
    private $userModel;
    private $companyModel;
    private $selectionModel;

    public function __construct(
        Router $router,
        ?Request $request = null,
        ?Response $response = null,
        ?Session $session = null
    ) {
        parent::__construct($router, $request, $response, $session);
        
        // Check if user is logged in and is an HR or admin
        if (!$this->session->get('user_id') || !in_array($this->session->get('user_role'), ['company_admin', 'admin'])) {
            $this->session->setFlash('error', 'You do not have permission to access this page.');
            $this->response->redirect('/login');
            exit;
        }
        
        $this->userModel = new User();
        $this->companyModel = new Company();
        $this->selectionModel = new EmployeeMenuSelection();
    }
    
    /**
     * Display the HR dashboard
     */
    public function dashboard() {
        $companyId = $this->session->get('company_id');
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        // Get employee count
        $employeeCount = $this->userModel->getEmployeeCountByCompany($companyId);
        
        // Get active employee count
        $activeEmployeeCount = $this->userModel->getActiveEmployeeCountByCompany($companyId);
        
        // Get today's menu selections
        $todaySelections = $this->selectionModel->getSelectionsByCompanyAndDate($companyId, date('Y-m-d'));
        
        // Render the HR dashboard view
        return $this->view('admin/companies/hr_dashboard', [
            'title' => 'HR Dashboard - ' . $company['name'],
            'company' => $company,
            'employeeCount' => $employeeCount,
            'activeEmployeeCount' => $activeEmployeeCount,
            'todaySelections' => $todaySelections,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Display a paginated list of employees
     */
    public function index($company_id = null) {
        $userRole = $this->session->get('user_role');
        $sessionCompanyId = $this->session->get('company_id');
        
        // If no company ID is provided in the URL, use the one from session (for backward compatibility)
        $companyId = $company_id ?? $sessionCompanyId;
        
        // If still no company ID, redirect to dashboard
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not specified.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect($userRole === 'admin' ? '/admin/companies' : '/dashboard');
            return;
        }
        
        // Check if HR user has access to this company
        if ($userRole === 'company_admin' && $companyId != $sessionCompanyId) {
            $this->session->setFlash('error', 'You do not have permission to view employees for this company.');
            $this->response->redirect("/hr/{$sessionCompanyId}/employees");
            return;
        }
        
        // Get current page from query string, default to 1
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10; // Number of items per page
        
        // Get employees for the specified company
        $result = $this->userModel->getEmployeesByCompanyPaginated($companyId, $page, $perPage);
        error_log('Fetching employees for company_id: ' . $companyId . ', Found ' . $result['pagination']['total'] . ' employees');
        
        return $this->view('hr/employees/index', [
            'title' => 'Employee Management - ' . $company['name'],
            'employees' => $result['data'],
            'pagination' => $result['pagination'],
            'company' => $company,
            'company_id' => $companyId,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Display the specified employee
     * 
     * @param string $company_id Company ID from URL (first parameter in route)
     * @param string $id Employee ID from URL (second parameter in route)
     */
    public function show($company_id, $id) {
        // Get employee by ID first
        $employee = $this->userModel->getUserById($id);
        
        if (!$employee || $employee['role'] !== 'employee') {
            $this->session->setFlash('error', 'Employee not found.');
            $this->response->redirect($company_id ? "/hr/{$company_id}/employees" : '/hr/employees');
            return;
        }
        
        // If company ID is not provided in URL, redirect to include it
        if ($company_id === null) {
            $this->response->redirect("/hr/{$employee['company_id']}/employees/{$id}");
            return;
        }
        
        // Verify company ID matches the employee's company
        if ($employee['company_id'] != $company_id) {
            $this->session->setFlash('error', 'Employee not found in the specified company.');
            $this->response->redirect("/hr/{$company_id}/employees");
            return;
        }
        
        // Check permissions
        $userRole = $this->session->get('user_role');
        $userCompanyId = $this->session->get('company_id');
        
        if ($userRole !== 'admin' && $employee['company_id'] != $userCompanyId) {
            $this->session->setFlash('error', 'You do not have permission to view this employee.');
            $this->response->redirect($userCompanyId ? "/hr/{$userCompanyId}/employees" : '/hr/employees');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($company_id);
        
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect($userRole === 'admin' ? '/admin/companies' : '/dashboard');
            return;
        }
        
        return $this->view('hr/employees/show', [
            'title' => 'Employee Details - ' . $employee['name'],
            'employee' => $employee,
            'company' => $company,
            'company_id' => $company_id,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Show the form to edit an employee
     * 
     * @param string $id Employee ID from URL (first parameter from router)
     * @param string $company_id Company ID from URL (second parameter from router)
     */
    public function edit($id, $company_id = null) {
        // Get employee by ID first
        $employee = $this->userModel->getUserById($id);
        
        if (!$employee || $employee['role'] !== 'employee') {
            $this->session->setFlash('error', 'Employee not found.');
            $this->response->redirect($company_id ? "/hr/{$company_id}/employees" : '/hr/employees');
            return;
        }
        
        // If company ID is not provided in URL, redirect to include it
        if ($company_id === null) {
            $this->response->redirect("/hr/{$employee['company_id']}/employees/{$id}/edit");
            return;
        }
        
        // Verify company ID matches the employee's company
        if ($employee['company_id'] != $company_id) {
            $this->session->setFlash('error', 'Employee not found in the specified company.');
            $this->response->redirect("/hr/{$company_id}/employees");
            return;
        }
        
        // Check permissions
        $userRole = $this->session->get('user_role');
        $userCompanyId = $this->session->get('company_id');
        
        if ($userRole !== 'admin' && $employee['company_id'] != $userCompanyId) {
            $this->session->setFlash('error', 'You do not have permission to edit this employee.');
            $this->response->redirect($userCompanyId ? "/hr/{$userCompanyId}/employees" : '/hr/employees');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($company_id);
        
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect($userRole === 'admin' ? '/admin/companies' : '/dashboard');
            return;
        }
        
        return $this->view('hr/employees/edit', [
            'title' => 'Edit Employee - ' . $employee['name'],
            'employee' => $employee,
            'company' => $company,
            'company_id' => $company_id,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Update an existing employee
     * 
     * @param string $id Employee ID from URL (first parameter from router)
     * @param string $company_id Company ID from URL (second parameter from router)
     */
    public function update($id, $company_id = null) {
        // Get employee by ID first
        $employee = $this->userModel->getUserById($id);
        
        if (!$employee || $employee['role'] !== 'employee') {
            $this->session->setFlash('error', 'Employee not found.');
            $this->response->redirect($company_id ? "/hr/{$company_id}/employees" : '/hr/employees');
            return;
        }
        
        // If company ID is not provided in URL, redirect to include it
        if ($company_id === null) {
            $this->response->redirect("/hr/{$employee['company_id']}/employees/{$id}/edit");
            return;
        }
        
        // Verify company ID matches the employee's company
        if ($employee['company_id'] != $company_id) {
            $this->session->setFlash('error', 'Employee not found in the specified company.');
            $this->response->redirect("/hr/{$company_id}/employees");
            return;
        }
        
        // Check permissions
        $userRole = $this->session->get('user_role');
        $userCompanyId = $this->session->get('company_id');
        
        if ($userRole !== 'admin' && $employee['company_id'] != $userCompanyId) {
            $this->session->setFlash('error', 'You do not have permission to update this employee.');
            $this->response->redirect($userCompanyId ? "/hr/{$userCompanyId}/employees" : '/hr/employees');
            return;
        }
        
        // Verify CSRF token
        $token = $this->request->get('_token');
        if (!$this->session->verifyCsrfToken($token)) {
            $this->session->setFlash('error', 'Invalid CSRF token. Please try again.');
            $this->response->redirect("/hr/{$company_id}/employees/{$id}/edit");
            return;
        }
        
        // Prepare data for validation
        $data = [
            'id' => $id,
            'name' => $this->request->get('name'),
            'email' => $this->request->get('email'),
            'is_active' => $this->request->get('is_active', 0) ? 1 : 0,
            'role' => 'employee',
            'company_id' => $employee['company_id'] // Preserve the original company_id
        ];
        
        // Only update password if provided
        $password = $this->request->get('password');
        if (!empty($password)) {
            $data['password'] = $password;
            $data['password_confirm'] = $this->request->get('password_confirm');
        }
        
        // Validate data
        $errors = $this->validateEmployeeData($data, true);
        
        if (!empty($errors)) {
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlash('error', $errorMessage);
            $this->session->set('old', $data);
            $this->response->redirect("/hr/{$company_id}/employees/{$id}/edit");
            return;
        }
        
        // Update the employee
        $result = $this->userModel->updateUser($data);
        
        if ($result) {
            $this->session->setFlash('success', 'Employee updated successfully.');
            $this->response->redirect("/hr/{$company_id}/employees/{$id}");
        } else {
            $this->session->setFlash('error', 'Failed to update employee. Please try again.');
            $this->session->set('old', $data);
            $this->response->redirect("/hr/{$company_id}/employees/{$id}/edit");
        }
    }
    
    /**
     * Show the form to create a new employee
     * 
     * @param string $company_id Company ID from URL
     */
    public function create($company_id = null) {
        if ($company_id) {
            $company = $this->companyModel->getCompanyById($company_id);
            if (!$company) {
                $this->session->setFlash('error', 'Company not found.');
                $this->response->redirect('/admin/companies');
                return;
            }
            
            return $this->view('hr/employees/create', [
                'title' => 'Add New Employee - ' . $company['name'],
                'company' => $company,
                'company_id' => $company_id,
                'csrf_token' => $this->session->generateCsrfToken()
            ]);
            return;
        }
        return $this->view('hr/employees/create', $this->withCsrfToken([
            'title' => 'Add New Employee'
        ]));
    }
    
    /**
     * Store a new employee
     */
    public function store() {
        // Start debug logging
        error_log('=== START EMPLOYEE STORE ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Session ID: ' . session_id());
        
        // Verify CSRF token
        $token = $this->request->get('_token');
        error_log('CSRF Token from form: ' . $token);
        
        if (!$this->verifyCsrfToken($token)) {
            $error = 'Invalid CSRF token. Please refresh the page and try again.';
            error_log('CSRF Validation Failed: ' . $error);
            error_log('Posted token: ' . $token);
            
            $this->session->setFlash('error', $error);
            $this->session->set('old', $this->request->all());
            
            // Ensure session is written before redirect
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            $this->response->redirect('/hr/employees/create');
            return;
        }
        
        // Get form data
        $companyId = $this->session->get('company_id');
        error_log('Current session company_id: ' . ($companyId ?: 'NULL'));
        
        $data = [
            'name' => trim($this->request->get('name')),
            'email' => trim($this->request->get('email')),
            'password' => $this->request->get('password'),
            'password_confirm' => $this->request->get('password_confirm'),
            'company_id' => $companyId,
            'role' => 'employee',
            'is_active' => 1
        ];
        
        error_log('Processing employee data: ' . print_r($data, true));
        
        // Validate data
        $errors = $this->validateEmployeeData($data);
        
        if (!empty($errors)) {
            $errorMessage = implode('<br>', $errors);
            error_log('Validation failed: ' . $errorMessage);
            
            $this->session->setFlash('error', $errorMessage);
            $this->session->set('old', $data);
            
            // Ensure session is written before redirect
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            $this->response->redirect('/hr/employees/create');
            return;
        }
        
        // Create the employee
        try {
            error_log('Attempting to create user with data: ' . print_r($data, true));
            
            $userId = $this->userModel->createUser($data);
            
            if (!$userId) {
                throw new \Exception('Failed to create user. No user ID returned.');
            }
            
            error_log('User created successfully with ID: ' . $userId);
            
            // Set success message
            $this->session->setFlash('success', 'Employee created successfully.');
            
            // Ensure session is written before redirect
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            $this->response->redirect('/hr/employees');
            
        } catch (\Exception $e) {
            $error = 'Failed to create employee: ' . $e->getMessage();
            error_log('Error creating user: ' . $error);
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $this->session->setFlash('error', $error);
            $this->session->set('old', $data);
            
            // Ensure session is written before redirect
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            $this->response->redirect('/hr/employees/create');
        } finally {
            error_log('=== END EMPLOYEE STORE ===');
        }
    }
    
    /**
     * Confirm deactivation of an employee
     */
    public function confirmDeactivate($id) {
        $employee = $this->userModel->getUserById($id);
        $companyId = $this->session->get('company_id');
        
        if (!$employee || $employee['company_id'] != $companyId) {
            $this->session->setFlash('error', 'Employee not found.');
            $this->response->redirect('/hr/employees');
            return;
        }
        
        return $this->view('hr/employees/confirm_deactivate', [
            'title' => 'Confirm Deactivation',
            'employee' => $employee,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Deactivate an employee
     */
    public function deactivate($id) {
        if (!$this->session->verifyCsrfToken($this->request->get('_token'))) {
            $this->session->setFlash('error', 'Invalid CSRF token. Please try again.');
            $this->response->redirect('/hr/employees');
            return;
        }
        
        try {
            $companyId = $this->session->get('company_id');
            $this->userModel->deactivateUser($id, $companyId);
            $this->session->setFlash('success', 'Employee deactivated successfully.');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to deactivate employee: ' . $e->getMessage());
        }
        
        $this->response->redirect('/hr/employees');
    }
    
    /**
     * Reactivate an employee
     */
    public function reactivate($id) {
        if (!$this->session->verifyCsrfToken($this->request->get('_token'))) {
            $this->session->setFlash('error', 'Invalid CSRF token. Please try again.');
            $this->response->redirect('/hr/employees');
            return;
        }
        
        try {
            $companyId = $this->session->get('company_id');
            $this->userModel->reactivateUser($id, $companyId);
            $this->session->setFlash('success', 'Employee reactivated successfully.');
        } catch (\Exception $e) {
            $this->session->setFlash('error', 'Failed to reactivate employee: ' . $e->getMessage());
        }
        
        $this->response->redirect('/hr/employees');
    }
    
    /**
     * Display today's menu selections for all employees
     * 
     * @param string $company_id Company ID from route parameter
     */
    public function todaysSelections($company_id = null) {
        $userRole = $this->session->get('user_role');
        $companyId = $company_id ?? $this->session->get('company_id');
        
        // For admin users, get the first active company if no company is selected
        if ($userRole === 'admin' && !$companyId) {
            $companies = $this->companyModel->getAllCompanies(1); // Get active companies only
            if (!empty($companies)) {
                $companyId = $companies[0]['id'];
                $this->session->set('company_id', $companyId);
            } else {
                $this->session->setFlash('error', 'No active companies found.');
                $this->response->redirect('/admin/companies');
                return;
            }
        } 
        // For non-admin users, require a company ID
        elseif (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/hr/dashboard');
            return;
        }
        
        // Get today's date
        $today = date('Y-m-d');
        
        // Get today's menu selections for the company
        $selections = $this->selectionModel->getSelectionsByCompanyAndDate($companyId, $today);
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect($userRole === 'admin' ? '/admin/companies' : '/hr/dashboard');
            return;
        }
        
        return $this->view('hr/menu_selections/today', [
            'title' => "Today's Menu Selections - " . ($company['name'] ?? 'Company'),
            'selections' => $selections,
            'date' => $today,
            'company' => $company,
            'isAdmin' => $userRole === 'admin',
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Legacy redirect for /hr/menu-selections/today (backward compatibility)
     */
    public function legacyTodaysSelections() {
        $userRole = $this->session->get('user_role');
        $companyId = $this->session->get('company_id');
        
        // For admin users, get the first active company if no company is selected
        if ($userRole === 'admin' && !$companyId) {
            $companies = $this->companyModel->getAllCompanies(true); // Get active companies only
            if (!empty($companies)) {
                $companyId = $companies[0]['id'];
                $this->session->set('company_id', $companyId);
            } else {
                $this->session->setFlash('error', 'No active companies found.');
                $this->response->redirect('/admin/companies');
                return;
            }
        } 
        // For non-admin users, require a company ID
        elseif (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/hr/dashboard');
            return;
        }
        
        // Redirect to the new company-specific route
        $this->response->redirect("/hr/{$companyId}/menu-selections/today");
    }
    
    /**
     * Legacy redirect for /hr/menu-selections/history (backward compatibility)
     */
    public function legacySelectionsHistory() {
        $companyId = $this->session->get('company_id');
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/hr/dashboard');
            return;
        }
        
        // Redirect to the new company-specific route
        $this->response->redirect("/hr/{$companyId}/menu-selections/history");
    }
    
    /**
     * Legacy redirect for /hr/employees (backward compatibility)
     */
    public function legacyEmployees() {
        $companyId = $this->session->get('company_id');
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/hr/dashboard');
            return;
        }
        
        // Redirect to the new company-specific route
        $this->response->redirect("/hr/{$companyId}/employees");
    }
    
    /**
     * Legacy redirect for /hr/dashboard (backward compatibility)
     */
    public function legacyDashboard() {
        $companyId = $this->session->get('company_id');
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/login');
            return;
        }
        
        // Redirect to the new company-specific route
        $this->response->redirect("/hr/{$companyId}/dashboard");
    }
    
    /**
     * Display menu selections history
     * 
     * @param string $company_id Company ID from route parameter
     */
    public function selectionsHistory($company_id = null) {
        $companyId = $company_id ?? $this->session->get('company_id');
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/hr/dashboard');
            return;
        }
        
        // Get date range from request or default to last 30 days
        $startDate = $this->request->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $this->request->get('end_date', date('Y-m-d'));
        
        // Get selections for the date range
        $selections = $this->selectionModel->getSelectionsByCompanyAndDateRange($companyId, $startDate, $endDate);
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        
        return $this->view('hr/menu_selections/history', [
            'title' => 'Menu Selections History - ' . ($company['name'] ?? 'Company'),
            'selections' => $selections,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'company' => $company,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Display notification settings for HR
     * 
     * @param string $company_id Company ID from route parameter
     */
    public function notificationSettings($company_id = null) {
        // Check if user is HR or admin
        $userRole = $this->session->get('user_role');
        $userCompanyId = $this->session->get('company_id');
        
        if (!in_array($userRole, ['admin', 'company_admin'])) {
            $this->session->setFlash('error', 'You do not have permission to access this page.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        // For admin users, use the company_id from URL; for HR users, use their company
        $companyId = ($userRole === 'admin') ? $company_id : $userCompanyId;
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        return $this->view('hr/settings/notifications', [
            'title' => 'Notification Settings - ' . $company['name'],
            'company' => $company,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Display HR preferences
     * 
     * @param string $company_id Company ID from route parameter
     */
    public function hrPreferences($company_id = null) {
        // Check if user is HR or admin
        $userRole = $this->session->get('user_role');
        $userCompanyId = $this->session->get('company_id');
        
        if (!in_array($userRole, ['admin', 'company_admin'])) {
            $this->session->setFlash('error', 'You do not have permission to access this page.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        // For admin users, use the company_id from URL; for HR users, use their company
        $companyId = ($userRole === 'admin') ? $company_id : $userCompanyId;
        
        if (!$companyId) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        // Get company details
        $company = $this->companyModel->getCompanyById($companyId);
        if (!$company) {
            $this->session->setFlash('error', 'Company not found.');
            $this->response->redirect('/dashboard');
            return;
        }
        
        return $this->view('hr/settings/preferences', [
            'title' => 'HR Preferences - ' . $company['name'],
            'company' => $company,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Validate employee data
     */
    private function validateEmployeeData($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Name is required.';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Email is already in use.';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Passwords do not match.';
        }
        
        return $errors;
    }
}
