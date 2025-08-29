<?php

namespace App\Controllers;

use App\Core\Controller as BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Router;
use App\Models\Menu;
use App\Models\EmployeeMenuSelection;
use App\Models\User;
use DateTime;

class EmployeeMenuController extends BaseController {
    private $menuModel;
    private $selectionModel;
    private $userModel;

    public function __construct(Router $router, ?Request $request = null, ?Response $response = null, ?Session $session = null) {
        parent::__construct($router, $request, $response, $session);

        $this->menuModel = new Menu();
        $this->selectionModel = new EmployeeMenuSelection();
        $this->userModel = new User();

        // Check if user is logged in
        if (!Session::get('user_id')) {
            Session::setFlash('error', 'Debes iniciar sesión para acceder a esta página.');
            $this->response->redirect('/login');
        }
    }

    /**
     * Show the menu selection page for employees
     */
    public function showSelectionForm(Request $request, Response $response)
    {
        // Debug: Log session data and server info
        error_log('=== Menu Selection Access Debug ===');
        error_log('Session data: ' . print_r($_SESSION, true));
        error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
        error_log('User ID: ' . (Session::get('user_id') ?? 'not set'));
        error_log('User Role: ' . (Session::get('user_role') ?? 'not set'));
        
        // Check if user is logged in
        if (!Session::get('user_id')) {
            error_log('User not logged in, redirecting to login');
            Session::setFlash('error', 'Debes iniciar sesión para acceder a esta página.');
            return $response->redirect('/login');
        }
        
        // Temporarily bypassing role check for testing
        error_log('Bypassing role check for testing');
        /*
        // Check if user is an employee
        $userRole = Session::get('user_role');
        if ($userRole !== 'employee') {
            error_log('User role is not employee, redirecting to dashboard. Role: ' . ($userRole ?? 'not set'));
            Session::setFlash('error', 'You must be logged in as an employee to access this page.');
            return $response->redirect('/dashboard');
        }
        */

        $userId = Session::get('user_id');
        // Check if viewing a specific date's menu
        $viewingDate = $request->get('date', date('Y-m-d'));
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $viewingDate)) {
            $viewingDate = date('Y-m-d');
        }
        
        $isViewingToday = ($viewingDate === date('Y-m-d'));
        
        // Get menu items for the selected date
        $menuItems = $this->menuModel->getAvailableMenuItems($viewingDate);
        $isDefaultingToPreviousMenu = false;
        
        // If no daily menu items found for the selected date, try to find the most recent menu
        if (empty($menuItems['daily']) && $isViewingToday) {
            $mostRecentMenu = $this->menuModel->getMostRecentAvailableMenu();
            if ($mostRecentMenu) {
                $viewingDate = $mostRecentMenu['date'];
                $menuItems = $this->menuModel->getAvailableMenuItems($viewingDate);
                $isViewingToday = ($viewingDate === date('Y-m-d'));
                $isDefaultingToPreviousMenu = true;
            }
        }
        
        // Get current selection for the viewed date if it exists
        $currentSelection = $this->selectionModel->getSelectionByUserAndDate($userId, $viewingDate);
        $viewingSelection = $isViewingToday ? $currentSelection : [];
        if (!$isViewingToday) {
            $viewingSelection = $this->selectionModel->getSelectionByUserAndDate($userId, $viewingDate);
        }
        $hasSelectedForDate = !empty($viewingSelection);
        
        // Get selection deadline info for the viewed date
        $selectionDeadline = $this->getSelectionDeadline($viewingDate);
        $selectionDeadlinePassed = $this->isSelectionDeadlinePassed($viewingDate);
        
        // If we're defaulting to a previous menu, we need to adjust the deadline info
        if ($isDefaultingToPreviousMenu) {
            $selectionDeadlinePassed = false; // Allow selection for fallback menus
        }
        
        // Get upcoming menus (next 7 days, including today)
        $upcomingMenus = [];
        for ($i = 0; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            // Skip today since we handle it separately
            if ($i === 0 && !$isViewingToday) {
                continue;
            }
            
            $upcomingMenus[$date] = [
                'items' => $this->menuModel->getAvailableMenuItems($date),
                'deadline_passed' => $this->isSelectionDeadlinePassed($date),
                'deadline' => strtotime($date . ' 10:00 AM')
            ];
        }

        // Prepare view data
        $viewData = [
            'title' => 'Weekly Menu Selection',
            'active' => 'select',
            'menuItems' => $menuItems,
            'currentSelection' => $currentSelection,
            'viewingSelection' => $viewingSelection,
            'hasSelectedForDate' => $hasSelectedForDate,
            'hasSelectedForToday' => $hasSelectedForDate && $isViewingToday,
            'selectionDeadline' => $selectionDeadline,
            'selectionDeadlinePassed' => $selectionDeadlinePassed,
            'upcomingMenus' => $upcomingMenus,
            'viewingDate' => $viewingDate,
            'isViewingToday' => $isViewingToday,
            'isViewingPast' => strtotime($viewingDate) < strtotime('today'),
            'isViewingFuture' => strtotime($viewingDate) > strtotime('today'),
            'isDefaultingToPreviousMenu' => $isDefaultingToPreviousMenu,
            'originalDate' => $isDefaultingToPreviousMenu ? date('Y-m-d') : $viewingDate,
            'csrf_token' => Session::generateCsrfToken(),
            'viewingDeadline' => $selectionDeadline,
            'viewingDeadlinePassed' => $selectionDeadlinePassed,
            'menuModel' => $this->menuModel,
            'selectionModel' => $this->selectionModel,
            'currentUser' => [
                'id' => Session::get('user_id')
            ]
        ];
        
        // Add debug info if needed
        if (isset($_GET['debug'])) {
            echo '<pre>';
            print_r($viewData);
            echo '</pre>';
            exit;
        }
        
        return $this->view('employee/menu_selection', $viewData);
    }

    /**
     * Handle menu selection form submission
     */
    /**
     * Save an order to the database
     */
    private function saveOrder(array $orderData)
    {
        $db = $this->getDbConnection();
        
        try {
            // Start a transaction
            $db->beginTransaction();
            
            // First, create the order
            $orderStmt = $db->prepare('INSERT INTO orders 
                (user_id, company_id, order_date, status, special_requests, created_at, updated_at)
                VALUES (:user_id, :company_id, :order_date, :status, :special_requests, :created_at, :updated_at)');

            $now = date('Y-m-d H:i:s');
            $orderParams = [
                ':user_id' => $orderData['user_id'],
                ':company_id' => $orderData['company_id'] ?? 1, // Default to company ID 1 if not provided
                ':order_date' => $orderData['order_date'],
                ':status' => $orderData['status'] ?? 'pending',
                ':special_requests' => $orderData['notes'] ?? null,
                ':created_at' => $now,
                ':updated_at' => $now
            ];

            $orderStmt->execute($orderParams);
            $orderId = $db->lastInsertId();
            
            // Then, create the order item
            $itemStmt = $db->prepare('INSERT INTO order_items 
                (order_id, menu_item_id, quantity, special_requests, created_at, updated_at)
                VALUES (:order_id, :menu_item_id, :quantity, :special_requests, :created_at, :updated_at)');
                
            $itemParams = [
                ':order_id' => $orderId,
                ':menu_item_id' => $orderData['menu_item_id'],
                ':quantity' => $orderData['quantity'] ?? 1,
                ':special_requests' => $orderData['notes'] ?? null,
                ':created_at' => $now,
                ':updated_at' => $now
            ];
            
            $itemStmt->execute($itemParams);
            
            // Commit the transaction
            $db->commit();
            
            return $orderId;
        } catch (\PDOException $e) {
            // Rollback the transaction on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Error saving order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get database connection
     */
    private function getDbConnection()
    {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $pdo = new \PDO('sqlite:' . DB_PATH);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection failed');
            }
        }
        
        return $pdo;
    }

    /**
     * Handle menu selection form submission
     */
    public function processSelection(Request $request, Response $response)
    {
        // Check if this is an AJAX request
        $isAjax = $request->isAjax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        
        // Set content type to JSON for AJAX requests
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        
        // Debug: Log incoming request data
        error_log('=== Menu Selection Form Submission ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Session data: ' . print_r($_SESSION, true));
        error_log('Is AJAX: ' . ($isAjax ? 'Yes' : 'No'));
        
        // Helper function to send JSON response
        $sendJsonResponse = function($success, $message, $statusCode = 200, $additionalData = []) use ($response, $isAjax) {
            $data = array_merge([
                'success' => $success,
                'message' => $message
            ], $additionalData);
            
            if ($isAjax) {
                http_response_code($statusCode);
                echo json_encode($data);
                exit;
            } else {
                Session::setFlash($success ? 'success' : 'error', $message);
                return $response->redirect("/menu/select" . (isset($data['selection_date']) ? '?date=' . $data['selection_date'] : ''));
            }
        };
        
        // Get the selection date from the request
        $selectionDate = $request->get('selection_date');
        
        // Check if user is logged in
        if (!Session::has('user_id')) {
            return $sendJsonResponse(false, 'Debes iniciar sesión para hacer una selección.', 401);
        }
        
        // Verify CSRF token
        $csrfToken = $request->get('_token');
        error_log('CSRF Token from form: ' . $csrfToken);
        error_log('Session CSRF Token: ' . ($_SESSION['csrf_token'] ?? 'Not set'));
        
        if (!Session::verifyCsrfToken($csrfToken)) {
            error_log('CSRF token validation failed');
            return $sendJsonResponse(
                false, 
                'Falló la validación del token CSRF. Por favor actualiza la página e inténtalo de nuevo.',
                403
            );
        }

        $userId = Session::get('user_id');
        $userRole = Session::get('user_role');
            
        if ($userRole !== 'employee') {
            return $sendJsonResponse(false, 'Debes iniciar sesión como empleado para hacer una selección.', 403);
        }

    // Get and validate selection date
    $selectionDate = $request->get('selection_date', date('Y-m-d'));
    $isDefaultingToPreviousMenu = $request->get('is_defaulting_to_previous', false);
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectionDate)) {
            return $sendJsonResponse(
                false,
                'Formato de fecha inválido.',
                400,
                ['selection_date' => $selectionDate]
            );
        }
        
        // Check if the date is in the past
        $today = date('Y-m-d');
        if ($selectionDate < $today && !$isDefaultingToPreviousMenu) {
            return $sendJsonResponse(
                false,
                'No puedes realizar selecciones para fechas pasadas.',
                400,
                ['selection_date' => $selectionDate]
            );
        }
        
        // Check if deadline has passed for the selected date
        if ($this->isSelectionDeadlinePassed($selectionDate) && !$isDefaultingToPreviousMenu) {
            return $sendJsonResponse(
                false,
                'El plazo de selección para la fecha indicada ha pasado.',
                400,
                ['selection_date' => $selectionDate]
            );
        }

        $menuItemId = $request->get('menu_item_id');
        $notes = $request->get('notes', '');
        
        if (empty($menuItemId)) {
            return $sendJsonResponse(
                false,
                'Por favor selecciona un ítem del menú.',
                400,
                ['selection_date' => $selectionDate]
            );
        }

        // Get menu item details for the order
        $menuItem = $this->menuModel->getMenuItemById($menuItemId);
        if (!$menuItem || !$menuItem['is_available']) {
            return $sendJsonResponse(
                false,
                'El ítem de menú seleccionado no está disponible.',
                400,
                ['selection_date' => $selectionDate, 'menu_item_id' => $menuItemId]
            );
        }

        // Create order data
        $orderData = [
            'user_id' => $userId,
            'company_id' => Session::get('company_id', 1), // Default to company ID 1 if not set
            'menu_item_id' => $menuItemId,
            'order_date' => date('Y-m-d H:i:s'),
            'delivery_date' => $selectionDate . ' 12:00:00', // Assuming lunch time is at noon
            'quantity' => 1,
            'total_price' => $menuItem['price'],
            'status' => 'pending',
            'notes' => $notes
        ];

        // Save the order
        try {
            $orderId = $this->saveOrder($orderData);
            if (!$orderId) {
                throw new \Exception('Failed to save order');
            }
            
            // Continue with the original menu selection process
            $selectionData = [
                'user_id' => $userId,
                'menu_item_id' => $menuItemId,
                'order_id' => $orderId, // Link the selection to the order
                'selection_date' => $selectionDate,
                'notes' => $notes,
                'status' => 'confirmed',
                'company_id' => Session::get('company_id', 1),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Save the selection
            $selectionSaved = $this->selectionModel->createSelection($selectionData);

            if ($selectionSaved) {
                return $sendJsonResponse(
                    true,
                    'Tu selección de menú ha sido guardada y el pedido se ha realizado correctamente.',
                    200,
                    [
                        'selection_date' => $selectionDate,
                        'redirect' => "/menu/select" . ($selectionDate === date('Y-m-d') ? '' : "?date=$selectionDate")
                    ]
                );
            } else {
                throw new \Exception('No se pudo guardar la selección de menú');
            }
        } catch (\Exception $e) {
            error_log('Error processing order/selection: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            
            return $sendJsonResponse(
                false,
                'Ocurrió un error al procesar tu selección. Por favor inténtalo de nuevo.',
                500,
                [
                    'selection_date' => $selectionDate,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * Get the selection deadline time for a specific date
     * Returns the deadline (10:00 AM of the specified date)
     * 
     * @param string $date Date in Y-m-d format (optional, defaults to today)
     * @return int Unix timestamp of the deadline
     */
    private function getSelectionDeadline($date = null)
    {
        $date = $date ?? date('Y-m-d');
        return strtotime($date . ' 10:00:00');
    }

    /**
     * Check if the selection deadline has passed for the given date (or today if not specified)
     * 
     * @param string $date Date in Y-m-d format (optional)
     * @return bool True if deadline has passed, false otherwise
     */
    private function isSelectionDeadlinePassed($date = null)
    {
        $now = time();
        $deadline = $this->getSelectionDeadline($date);
        return $now >= $deadline;
        return false;
    }

    /**
     * Display the current user's menu selections
     */
    public function mySelections(Request $request, Response $response) {
        // Check if user is logged in
        if (!Session::get('user_id')) {
            Session::setFlash('error', 'Debes iniciar sesión para ver tus selecciones de menú.');
            return $response->redirect('/login');
        }

        $userId = Session::get('user_id');
        
        try {
            // Get all selections for the current user, ordered by date (newest first)
            $selections = $this->selectionModel->getSelectionsByUser($userId);
            
            // Group selections by date
            $groupedSelections = [];
            foreach ($selections as $selection) {
                $date = $selection['selection_date'];
                if (!isset($groupedSelections[$date])) {
                    $groupedSelections[$date] = [];
                }
                $groupedSelections[$date][] = $selection;
            }
            
            // Get the current date for comparison
            $today = date('Y-m-d');
            
            // Prepare view data
            $viewData = [
                'title' => 'Mis Selecciones de Menú',
                'selections' => $groupedSelections,
                'today' => $today,
                'csrf_token' => Session::generateCsrfToken()
            ];
            
            // Render the view
            return $this->view('employee/my_selections', $viewData);
            
        } catch (\Exception $e) {
            error_log('Error in mySelections: ' . $e->getMessage());
            Session::setFlash('error', 'Ocurrió un error al cargar tus selecciones de menú. Por favor inténtalo de nuevo.');
            return $response->redirect('/dashboard');
        }
    }

    /**
     * Get menu selection history for an employee (HR view)
     */
    /**
     * Show other menu items that are not part of the daily selection
     */
    public function showOtherItems(Request $request, Response $response)
    {
        // Check if user is logged in
        if (!Session::get('user_id')) {
            Session::setFlash('error', 'Debes iniciar sesión para acceder a esta página.');
            return $response->redirect('/login');
        }

        // Get the date from the query parameter or use today's date
        $date = $request->get('date', date('Y-m-d'));
        
        // Get other menu items (items not in today's menu)
        $otherItems = [];
        try {
            // Get today's menu items
            $todayMenu = $this->menuModel->getAvailableMenuItems($date);
            $todayItemIds = array_column($todayMenu, 'id');
            
            // Get all weekly menu items
            $allItems = $this->menuModel->getWeeklyMenuItems();
            
            // Filter out items that are already in today's menu
            foreach ($allItems as $item) {
                if (!in_array($item['id'], $todayItemIds)) {
                    $otherItems[] = [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'description' => $item['description'] ?? '',
                        'price' => $item['price'] ?? 0,
                        'category' => $item['category'] ?? 'other',
                        'image_url' => $item['image_url'] ?? ''
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log('Error in showOtherItems: ' . $e->getMessage());
            Session::setFlash('error', 'Ocurrió un error al cargar otros ítems del menú.');
            return $response->redirect('/menu/select');
        }
        
        // Render the view with other items
        return $this->view('employee/other_items', [
            'title' => 'Otros Ítems del Menú',
            'otherItems' => $otherItems,
            'selectedDate' => $date,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
    
    /**
     * Get menu selection history for an employee (HR view)
     */
    /**
     * Get menu selections for a specific employee
     * 
     * @param string $companyId Company ID from route
     * @param string $id Employee ID from route
     */
    public function getEmployeeSelections($companyId, $id = null)
    {
        // Check if user is HR or admin
        $userRole = Session::get('user_role');
        $userCompanyId = Session::get('company_id');
        
        // Verify user has permission to view this page
        if (!in_array($userRole, ['admin', 'company_admin'])) {
            Session::setFlash('error', 'No tienes permiso para ver esta página.');
            $this->response->redirect('/dashboard');
            return;
        }

        // If employee ID is not provided, redirect to HR dashboard with company ID
        if (!$id) {
            Session::setFlash('error', 'Se requiere el ID del empleado.');
            $this->response->redirect("/hr/{$companyId}/employees");
            return;
        }

        // Get employee details
        $employee = $this->userModel->getUserById($id);
        if (!$employee || $employee['role'] !== 'employee') {
            Session::setFlash('error', 'Empleado no encontrado.');
            $this->response->redirect("/hr/{$companyId}/employees");
            return;
        }
        
        // Check permissions: admin can view any employee, HR can only view employees from their company
        if ($userRole !== 'admin' && $employee['company_id'] != $companyId) {
            Session::setFlash('error', 'No tienes permiso para ver este empleado.');
            $this->response->redirect("/hr/{$userCompanyId}/employees");
            return;
        }

        // Get date range from query params (use defaults if not provided)
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        // Get selections with pagination
        $selectionsResult = $this->selectionModel->getSelectionsByEmployee(
            $id, 
            $startDate, 
            $endDate,
            $page,
            $perPage
        );

        return $this->view('hr/employees/selections', [
            'employee' => $employee,
            'selections' => $selectionsResult['data'],
            'pagination' => $selectionsResult['pagination'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'csrf_token' => $this->session->generateCsrfToken()
        ]);
    }
}
