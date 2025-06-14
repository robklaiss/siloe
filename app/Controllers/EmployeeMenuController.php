<?php

namespace App\Controllers;

use App\Models\Menu;
use App\Models\EmployeeMenuSelection;
use App\Models\User;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use DateTime;

class EmployeeMenuController extends Controller {
    private $menuModel;
    private $selectionModel;
    private $userModel;

    public function __construct(Router $router, Request $request = null, Response $response = null, Session $session = null) {
        parent::__construct($router, $request, $response, $session);
        $this->menuModel = new Menu();
        $this->selectionModel = new EmployeeMenuSelection();
        $this->userModel = new User();
        
        // Check if user is logged in
        if (!Session::get('user_id')) {
            Session::setFlash('error', 'You must be logged in to access this page.');
            Response::redirect('/login');
        }
    }

    /**
     * Show the menu selection page for employees
     */
    public function showSelectionForm(Request $request, Response $response)
    {
        // Check if user is an employee
        $userRole = Session::get('user_role');
        if ($userRole !== 'employee') {
            Session::setFlash('error', 'You must be logged in as an employee to access this page.');
            return $response->redirect('/dashboard');
        }

        $userId = Session::get('user_id');
        $today = date('Y-m-d');
        $selectionDeadline = $this->getSelectionDeadline();
        $selectionDeadlinePassed = $this->isSelectionDeadlinePassed();
        
        // Get today's menu items
        $menuItems = $this->menuModel->getAvailableMenuItems($today);
        
        // Check if user has already made a selection for today
        $currentSelection = $this->selectionModel->getSelectionByUserAndDate($userId, $today);
        $hasSelectedForToday = !empty($currentSelection);
        
        // Get upcoming menus (next 7 days)
        $upcomingMenus = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $upcomingMenus[$date] = [
                'items' => $this->menuModel->getAvailableMenuItems($date)
            ];
        }

        return $this->render('employee/menu_selection', [
            'menuItems' => $menuItems,
            'currentSelection' => $currentSelection,
            'hasSelectedForToday' => $hasSelectedForToday,
            'selectionDeadline' => $selectionDeadline,
            'selectionDeadlinePassed' => $selectionDeadlinePassed,
            'upcomingMenus' => $upcomingMenus,
            'csrf_token' => Session::generateCsrfToken()
        ]);
    }

    /**
     * Handle menu selection form submission
     */
    public function processSelection(Request $request, Response $response)
    {
        // Verify CSRF token
        if (!Session::verifyCsrfToken($request->get('_token'))) {
            Session::setFlash('error', 'Invalid CSRF token. Please try again.');
            return $response->redirect('/menu/select');
        }

        $userId = Session::get('user_id');
        $userRole = Session::get('user_role');
        
        if ($userRole !== 'employee') {
            Session::setFlash('error', 'You must be logged in as an employee to make a selection.');
            return $response->redirect('/dashboard');
        }

        if ($this->isSelectionDeadlinePassed()) {
            Session::setFlash('error', 'The selection deadline for today has passed.');
            return $response->redirect('/menu/select');
        }

        $menuItemId = $request->get('menu_item_id');
        $notes = $request->get('notes', '');
        $selectionDate = date('Y-m-d');
        
        if (empty($menuItemId)) {
            Session::setFlash('error', 'Please select a menu item.');
            return $response->redirect('/menu/select');
        }

        // Validate menu item exists and is available
        $menuItem = $this->menuModel->getMenuItemById($menuItemId);
        if (!$menuItem || !$menuItem['is_available']) {
            Session::setFlash('error', 'Selected menu item is not available.');
            return $response->redirect('/menu/select');
        }

        // Save or update the selection
        $selectionData = [
            'user_id' => $userId,
            'menu_item_id' => $menuItemId,
            'selection_date' => $selectionDate,
            'notes' => $notes,
            'status' => 'pending',
            'company_id' => Session::get('company_id')
        ];

        $existingSelection = $this->selectionModel->getSelectionByUserAndDate($userId, $selectionDate);
        
        try {
            if ($existingSelection) {
                // Update existing selection
                $this->selectionModel->updateSelection($existingSelection['id'], [
                    'menu_item_id' => $menuItemId,
                    'notes' => $notes,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $message = 'Your menu selection has been updated successfully.';
            } else {
                // Create new selection
                $this->selectionModel->createSelection($selectionData);
                $message = 'Your menu selection has been saved successfully.';
            }
            
            Session::setFlash('success', $message);
        } catch (\Exception $e) {
            error_log('Error saving menu selection: ' . $e->getMessage());
            Session::setFlash('error', 'An error occurred while saving your selection. Please try again.');
        }

        return $response->redirect('/menu/select');
    }

    /**
     * Get the selection deadline time
     */
    private function getSelectionDeadline()
    {
        // Default to 10:00 AM
        return strtotime('today 10:00 AM');
    }

    /**
     * Check if the selection deadline has passed
     */
    private function isSelectionDeadlinePassed()
    {
        return time() > $this->getSelectionDeadline();
    }

    /**
     * Get menu selection history for an employee (HR view)
     */
    public function getEmployeeSelections(Request $request, Response $response, $employeeId = null)
    {
        // Check if user is HR or admin
        $userRole = Session::get('user_role');
        $companyId = Session::get('company_id');
        
        if (!in_array($userRole, ['admin', 'company_admin'])) {
            Session::setFlash('error', 'You do not have permission to view this page.');
            return $response->redirect('/dashboard');
        }

        // If employeeId is not provided, redirect to HR dashboard
        if (!$employeeId) {
            return $response->redirect('/hr/dashboard');
        }

        // Get employee details
        $employee = $this->userModel->findById($employeeId);
        if (!$employee || $employee['company_id'] != $companyId) {
            Session::setFlash('error', 'Employee not found.');
            return $response->redirect('/hr/dashboard');
        }

        // Get date range from query params
        $startDate = $request->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 15;

        // Get selections with pagination
        $selectionsResult = $this->selectionModel->getSelectionsByEmployee(
            $employeeId, 
            $startDate, 
            $endDate,
            $page,
            $perPage
        );

        return $this->render('hr/employees/selections', [
            'employee' => $employee,
            'selections' => $selectionsResult['data'],
            'pagination' => $selectionsResult['pagination'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'csrf_token' => Session::generateCsrfToken()
        ]);
    }
}
