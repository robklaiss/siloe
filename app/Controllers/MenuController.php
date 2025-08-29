<?php

namespace App\Controllers;

use App\Models\EmployeeMenuSelection;
use App\Models\Menu;
use App\Models\User;
use DateTime;
use PDO;

class MenuController extends Controller {
    private $menuModel;
    private $selectionModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        
        $this->menuModel = new Menu();
        $this->selectionModel = new EmployeeMenuSelection();
        $this->userModel = new User();
    }

    /**
     * Delete a weekly item by ID via JSON DELETE
     */
    public function deleteWeeklyItem($id) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $pdo = $this->getDbConnection();
            $stmt = $pdo->prepare('DELETE FROM weekly_menu_items WHERE id = :id');
            $ok = $stmt->execute([':id' => (int)$id]);
            if (!$ok || $stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Ítem no encontrado']);
                return;
            }
            echo json_encode(['success' => true, 'message' => 'Ítem eliminado correctamente']);
        } catch (\PDOException $e) {
            error_log('Error deleting weekly item: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error en la base de datos']);
        }
    }
    
    /**
     * Get weekly items filtered by category.
     * Supports categories: almuerzo, merienda, bebidas, postres
     * Falls back to heuristics if the 'category' column does not exist.
     */
    public function getWeeklyItemsByCategory() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $rawCat = isset($_GET['category']) ? strtolower(trim((string)$_GET['category'])) : '';
        $map = [
            'almuerzo' => 'almuerzo', 'main' => 'almuerzo', 'mains' => 'almuerzo', 'plato' => 'almuerzo',
            'merienda' => 'merienda', 'snack' => 'merienda', 'snacks' => 'merienda',
            'bebida' => 'bebidas', 'bebidas' => 'bebidas', 'beverage' => 'bebidas', 'beverages' => 'bebidas', 'drinks' => 'bebidas',
            'postre' => 'postres', 'postres' => 'postres', 'dessert' => 'postres', 'desserts' => 'postres'
        ];
        $category = $map[$rawCat] ?? '';
        if ($category === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid or missing category']);
            return;
        }

        try {
            $pdo = $this->getDbConnection();

            // Detect schema
            $hasCategory = false;
            $hasIsBeverage = false;
            try {
                $cols = $pdo->query('PRAGMA table_info(weekly_menu_items)')->fetchAll();
                foreach ($cols as $col) {
                    if (isset($col['name']) && strcasecmp($col['name'], 'category') === 0) {
                        $hasCategory = true;
                    }
                    if (isset($col['name']) && strcasecmp($col['name'], 'is_beverage') === 0) {
                        $hasIsBeverage = true;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            if ($hasCategory) {
                $stmt = $pdo->prepare('
                    SELECT id, name, description, price
                    FROM weekly_menu_items
                    WHERE is_available = 1 AND category = :category
                    ORDER BY name ASC
                ');
                $stmt->execute([':category' => $category]);
            } else {
                // Heuristic fallbacks
                if ($category === 'bebidas') {
                    if ($hasIsBeverage) {
                        $stmt = $pdo->query('
                            SELECT id, name, description, price FROM weekly_menu_items 
                            WHERE is_available = 1 AND is_beverage = 1 ORDER BY name ASC
                        ');
                    } else {
                        $stmt = $pdo->query('
                            SELECT id, name, description, price FROM weekly_menu_items 
                            WHERE is_available = 1 AND (
                                LOWER(name) LIKE "%agua%" OR LOWER(name) LIKE "%refresco%" OR LOWER(name) LIKE "%jugo%" OR
                                LOWER(name) LIKE "%café%" OR LOWER(name) LIKE "%cafe%" OR LOWER(name) LIKE "%té%" OR LOWER(name) LIKE "%te%" OR
                                LOWER(name) LIKE "%mate%" OR LOWER(name) LIKE "%terer%" OR LOWER(name) LIKE "%soda%" OR LOWER(name) LIKE "%gaseosa%" OR
                                LOWER(name) LIKE "%cola%" OR LOWER(name) LIKE "%coca%" OR LOWER(name) LIKE "%pepsi%" OR LOWER(name) LIKE "%sprite%" OR LOWER(name) LIKE "%fanta%" OR
                                LOWER(name) LIKE "%bebida%"
                            )
                            ORDER BY name ASC
                        ');
                    }
                } elseif ($category === 'postres') {
                    $stmt = $pdo->query('
                        SELECT id, name, description, price FROM weekly_menu_items 
                        WHERE is_available = 1 AND (
                            LOWER(name) LIKE "%postre%" OR LOWER(name) LIKE "%dessert%" OR LOWER(name) LIKE "%torta%" OR LOWER(name) LIKE "%pastel%" OR LOWER(name) LIKE "%helado%"
                        )
                        ORDER BY name ASC
                    ');
                } elseif ($category === 'merienda') {
                    $stmt = $pdo->query('
                        SELECT id, name, description, price FROM weekly_menu_items 
                        WHERE is_available = 1 AND (
                            LOWER(name) LIKE "%merienda%" OR LOWER(name) LIKE "%sandwich%" OR LOWER(name) LIKE "%sándwich%" OR LOWER(name) LIKE "%empanada%" OR LOWER(name) LIKE "%chipa%" OR LOWER(name) LIKE "%tostada%"
                        )
                        ORDER BY name ASC
                    ');
                } else { // almuerzo
                    $stmt = $pdo->query('
                        SELECT id, name, description, price FROM weekly_menu_items 
                        WHERE is_available = 1 AND (
                            LOWER(name) LIKE "%plato%" OR LOWER(name) LIKE "%ensalada%" OR LOWER(name) LIKE "%sopa%" OR LOWER(name) LIKE "%pasta%" OR LOWER(name) LIKE "%pollo%" OR LOWER(name) LIKE "%carne%" OR LOWER(name) LIKE "%pescado%" OR LOWER(name) LIKE "%arroz%" OR LOWER(name) LIKE "%milanesa%" OR LOWER(name) LIKE "%estofado%"
                        )
                        ORDER BY name ASC
                    ');
                }
            }

            $items = $stmt->fetchAll();

            // Company-aware filtering: if a company context exists, intersect with
            // the set of menu items actually selected by employees of that company
            // for the requested date (default: today). This aligns visibility with
            // real offerings seen by employees per company.
            $companyId = $_SESSION['company_id'] ?? null;
            $allCompanies = isset($_GET['all_companies']) && ($_GET['all_companies'] === '1' || strtolower((string)$_GET['all_companies']) === 'true');
            $filterEnabled = $companyId && !$allCompanies; // allow bypass via all_companies=1

            if ($filterEnabled) {
                // Determine target date
                $dateParam = isset($_GET['date']) ? trim((string)$_GET['date']) : '';
                $targetDate = $dateParam !== '' ? $dateParam : date('Y-m-d');

                // Get distinct selected names for the company and date
                $selStmt = $pdo->prepare('
                    SELECT DISTINCT LOWER(mi.name) AS lname
                    FROM employee_menu_selections s
                    JOIN menu_items mi ON s.menu_item_id = mi.id
                    WHERE s.company_id = :company_id AND s.selection_date = :selection_date
                ');
                $selStmt->execute([':company_id' => $companyId, ':selection_date' => $targetDate]);
                $nameRows = $selStmt->fetchAll();
                $selectedNames = array_map(function ($r) { return (string)($r['lname'] ?? ''); }, $nameRows);
                $selectedNames = array_values(array_filter($selectedNames, fn($n) => $n !== ''));

                // Fallback to most recent selection date for this company if none today
                if (empty($selectedNames)) {
                    $lastStmt = $pdo->prepare('SELECT selection_date FROM employee_menu_selections WHERE company_id = :company_id ORDER BY selection_date DESC LIMIT 1');
                    $lastStmt->execute([':company_id' => $companyId]);
                    $row = $lastStmt->fetch();
                    if ($row && !empty($row['selection_date'])) {
                        $fallbackDate = (string)$row['selection_date'];
                        $selStmt->execute([':company_id' => $companyId, ':selection_date' => $fallbackDate]);
                        $nameRows = $selStmt->fetchAll();
                        $selectedNames = array_map(function ($r) { return (string)($r['lname'] ?? ''); }, $nameRows);
                        $selectedNames = array_values(array_filter($selectedNames, fn($n) => $n !== ''));
                    }
                }

                if (!empty($selectedNames)) {
                    $selectedSet = array_flip($selectedNames); // for O(1) lookups
                    $items = array_values(array_filter($items, function ($it) use ($selectedSet) {
                        $name = isset($it['name']) ? strtolower((string)$it['name']) : '';
                        return $name !== '' && isset($selectedSet[$name]);
                    }));
                } else {
                    // No selections found -> show empty list to reflect no offerings
                    $items = [];
                }
            }

            echo json_encode(['success' => true, 'items' => $items]);
        } catch (\PDOException $e) {
            error_log('Error getting weekly items by category: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    }
    
    /**
     * Update a weekly item by ID via JSON PUT
     */
    public function updateWeeklyItem($id) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) { $data = []; }
        $name = isset($data['name']) ? trim((string)$data['name']) : '';
        $description = isset($data['description']) ? trim((string)$data['description']) : '';
        $price = $data['price'] ?? null;
        $priceNum = is_numeric($price) ? (float)$price : null;

        if ($name === '' || $priceNum === null || $priceNum < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nombre y precio válido son requeridos']);
            return;
        }

        try {
            $pdo = $this->getDbConnection();
            $stmt = $pdo->prepare('UPDATE weekly_menu_items SET name = :name, description = :description, price = :price WHERE id = :id');
            $ok = $stmt->execute([
                ':id' => (int)$id,
                ':name' => $name,
                ':description' => ($description !== '' ? $description : null),
                ':price' => $priceNum
            ]);

            if (!$ok || $stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Ítem no encontrado o sin cambios']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Ítem actualizado correctamente']);
        } catch (\PDOException $e) {
            error_log('Error updating weekly item: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error en la base de datos']);
        }
    }
    
    public function index() {
        // Render the categories accordion view (Spanish)
        return $this->view('menus/categories', [
            'title' => 'Menús - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'menus'
        ])->layout('layouts/app');
    }
    
    public function create() {
        // Get weekly menu items
        $weeklyItems = $this->menuModel->getWeeklyMenuItems();
        
        // Render the menu creation form
        return $this->view('menus/create', [
            'title' => 'Create Menu - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'weeklyItems' => $weeklyItems,
            'categories' => $this->getCategories(),
            'csrf_token' => $this->generateCsrfToken(),
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'menus'
        ])->layout('layouts/app');
    }
    
    public function store() {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /menus/create');
            exit;
        }
        
        // Get form data
        $weeklyItems = isset($_POST['weekly_items']) ? (array)$_POST['weekly_items'] : [];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $available = isset($_POST['available']) ? 1 : 0;

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if ($price <= 0) $errors[] = 'Price must be greater than zero';
        if (empty($date)) $errors[] = 'Date is required';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'date' => $date,
                'available' => $available
            ];
            header('Location: /menus/create');
            exit;
        }

        // Get database connection
        $db = $this->getDbConnection();
        
        try {
            // Begin transaction for better data integrity
            $db->beginTransaction();
            
            // Create new menu - the unique index will prevent duplicates
            $stmt = $db->prepare('INSERT INTO menus (name, description, price, date, available, is_active) VALUES (:name, :description, :price, :date, :available, :is_active)');
            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':date' => $date,
                ':available' => $available,
                ':is_active' => $available // Using available as is_active
            ]);
            
            // Get the ID of the newly created menu
            $menuId = $db->lastInsertId();
            
            // Add weekly items to the menu within the same transaction for atomicity
            if (!empty($weeklyItems)) {
                $linkStmt = $db->prepare('INSERT OR IGNORE INTO menu_weekly_items (menu_id, weekly_item_id) VALUES (:menu_id, :weekly_item_id)');
                foreach ($weeklyItems as $itemId) {
                    $linkStmt->execute([
                        ':menu_id' => $menuId,
                        ':weekly_item_id' => (int)$itemId
                    ]);
                }
            }

            // Commit the transaction
            $db->commit();

            // Redirect to menu list with success message
            $_SESSION['success'] = 'Menu created successfully';
            header('Location: /menus');
            exit;
        } catch (\PDOException $e) {
            // Rollback the transaction
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            // Check if this is a constraint violation (duplicate entry)
            if ($e->getCode() == '23000' || strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $_SESSION['error'] = 'A menu item with this name and description already exists';
            } else {
                // Log the detailed error for debugging
                error_log("Menu insert - Error: {$e->getMessage()}");
                $_SESSION['error'] = 'Failed to create menu. Please try again.';
            }
            
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'date' => $date,
                'available' => $available
            ];
            header('Location: /menus/create');
            exit;
        }
    }
    
    public function edit($id) {
        $menu = $this->getMenuById($id);
        if (!$menu) {
            $_SESSION['error'] = 'Menu not found';
            header('Location: /menus');
            exit;
        }
        
        // Get weekly menu items
        $weeklyItems = $this->menuModel->getWeeklyMenuItems();
        
        // Get current menu's weekly items using the model's method
        $currentWeeklyItems = $this->menuModel->getWeeklyItemsForMenu($id);
        
        return $this->view('menus/edit', [
            'title' => 'Edit Menu - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'menu' => $menu,
            'categories' => $this->getCategories(),
            'weeklyItems' => $weeklyItems,
            'currentWeeklyItems' => $currentWeeklyItems,
            'csrf_token' => $this->generateCsrfToken(),
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'menus'
        ])->layout('layouts/app');
    }
    
    public function update($id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /menus/' . $id . '/edit');
            exit;
        }

        // Get form data
        $weeklyItems = isset($_POST['weekly_items']) ? (array)$_POST['weekly_items'] : [];
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $available = isset($_POST['available']) ? 1 : 0;

        // Basic validation
        $errors = [];
        if (empty($name)) $errors[] = 'Name is required';
        if ($price <= 0) $errors[] = 'Price must be greater than zero';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'available' => $available
            ];
            header('Location: /menus/' . $id . '/edit');
            exit;
        }

        // Get database connection
        $db = $this->getDbConnection();
        
        try {
            // Begin transaction
            $db->beginTransaction();
            
            // Update the menu
            $stmt = $db->prepare('UPDATE menus SET name = :name, description = :description, price = :price, available = :available, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $result = $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':available' => $available
            ]);
            
            if (!$result) {
                throw new Exception('Failed to update menu');
            }
            
            // Update weekly menu items
            // First, remove all existing weekly items for this menu
            $stmt = $db->prepare('DELETE FROM menu_weekly_items WHERE menu_id = :menu_id');
            $stmt->execute([':menu_id' => $id]);
            
            // Add selected weekly items
            if (!empty($weeklyItems)) {
                $stmt = $db->prepare('INSERT INTO menu_weekly_items (menu_id, weekly_item_id) VALUES (:menu_id, :weekly_item_id)');
                foreach ($weeklyItems as $itemId) {
                    $stmt->execute([
                        ':menu_id' => $id,
                        ':weekly_item_id' => $itemId
                    ]);
                }
            }
            
            // Commit the transaction
            $db->commit();
            
            $_SESSION['success'] = 'Menu updated successfully';
            header('Location: /menus');
            exit;
            
        } catch (Exception $e) {
            // Rollback the transaction on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            $_SESSION['error'] = 'Error updating menu: ' . $e->getMessage();
            $_SESSION['old'] = [
                'name' => $name, 
                'description' => $description, 
                'price' => $price,
                'available' => $available
            ];
            header('Location: /menus/' . $id . '/edit');
            exit;
        }
    }
    
    public function destroy($id) {
        // Verify CSRF token
        if (!$this->verifyCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid CSRF token';
            header('Location: /menus');
            exit;
        }

        // Get menu by ID
        $menu = $this->getMenuById($id);
        
        if (!$menu) {
            $_SESSION['error'] = 'Menu not found';
            header('Location: /menus');
            exit;
        }

        // Check if menu has associated menu items
        $db = $this->getDbConnection();
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM menu_items WHERE menu_id = :menu_id');
        $stmt->execute([':menu_id' => $id]);
        $result = $stmt->fetch();
        
        if ($result && $result['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete menu with associated menu items';
            header('Location: /menus');
            exit;
        }

        // Delete menu
        $stmt = $db->prepare('DELETE FROM menus WHERE id = :id');
        $result = $stmt->execute([':id' => $id]);

        if ($result) {
            $_SESSION['success'] = 'Menu deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete menu';
        }
        
        header('Location: /menus');
        exit;
    }
    
    private function getAllMenus() {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->query('SELECT id, name, description, price, date, available, created_at FROM menus ORDER BY date DESC');
            $menus = $stmt->fetchAll();
            
            // Enhance each menu with bundled items and calculate total price
            foreach ($menus as &$menu) {
                $menu['bundled_items'] = $this->getMenuBundledItems($menu['id']);
                $menu['total_price'] = $this->calculateMenuTotalPrice($menu);
            }
            
            return $menus;
        } catch (\Exception $e) {
            error_log('Error getting menus: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get bundled items for a specific menu
     */
    private function getMenuBundledItems($menuId) {
        try {
            $db = $this->getDbConnection();
            $bundledItems = [];
            
            // Get weekly items associated with this menu
            $stmt = $db->prepare('
                SELECT wmi.id, wmi.name, wmi.description, wmi.price, "weekly" as type
                FROM menu_weekly_items mwi 
                JOIN weekly_menu_items wmi ON mwi.weekly_item_id = wmi.id 
                WHERE mwi.menu_id = :menu_id AND wmi.is_available = 1
            ');
            $stmt->execute([':menu_id' => $menuId]);
            $weeklyItems = $stmt->fetchAll();
            
            // Get custom menu items for this menu
            $stmt = $db->prepare('
                SELECT id, name, description, price, "custom" as type
                FROM menu_items 
                WHERE menu_id = :menu_id AND is_available = 1 AND is_weekly_item = 0
            ');
            $stmt->execute([':menu_id' => $menuId]);
            $customItems = $stmt->fetchAll();
            
            // Combine all items
            $bundledItems = array_merge($weeklyItems, $customItems);
            
            // Categorize items
            $categorized = [
                'main_dishes' => [],
                'beverages' => [],
                'desserts' => [],
                'other' => []
            ];
            
            foreach ($bundledItems as $item) {
                $name = strtolower($item['name']);
                if (stripos($name, 'agua') !== false || stripos($name, 'refresco') !== false || 
                    stripos($name, 'jugo') !== false || stripos($name, 'café') !== false || 
                    stripos($name, 'té') !== false) {
                    $categorized['beverages'][] = $item;
                } elseif (
                    stripos($name, 'postre') !== false ||
                    stripos($name, 'dessert') !== false ||
                    stripos($name, 'torta') !== false ||
                    stripos($name, 'pastel') !== false ||
                    stripos($name, 'helado') !== false
                ) {
                    $categorized['desserts'][] = $item;
                } elseif (in_array($name, ['ensalada', 'sopa', 'pasta', 'pollo', 'carne', 'pescado']) || 
                         stripos($name, 'plato') !== false) {
                    $categorized['main_dishes'][] = $item;
                } else {
                    $categorized['other'][] = $item;
                }
            }
            
            return $categorized;
        } catch (\Exception $e) {
            error_log('Error getting bundled items for menu ' . $menuId . ': ' . $e->getMessage());
            return ['main_dishes' => [], 'beverages' => [], 'desserts' => [], 'other' => []];
        }
    }
    
    /**
     * Calculate total price including all bundled items
     */
    private function calculateMenuTotalPrice($menu) {
        $totalPrice = floatval($menu['price']); // Base menu price
        
        // Add prices from all bundled items
        foreach ($menu['bundled_items'] as $category => $items) {
            foreach ($items as $item) {
                $totalPrice += floatval($item['price']);
            }
        }
        
        return $totalPrice;
    }
    
    private function getMenuById($id) {
        try {
            $db = $this->getDbConnection();
            $stmt = $db->prepare('SELECT id, name, description, price, date, available, created_at FROM menus WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log('Error getting menu: ' . $e->getMessage());
            return null;
        }
    }
    
    protected function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    protected function getDbConnection() {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $dsn = 'sqlite:' . DB_PATH;
                $pdo = new \PDO($dsn);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                // Ensure SQLite enforces foreign key constraints for cascades
                $pdo->exec('PRAGMA foreign_keys = ON');
            } catch (\PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        
        return $pdo;
    }
    
    /**
     * Save a new beverage via AJAX
     */
    public function saveBeverage() {
        // Ensure JSON response
        header('Content-Type: application/json');
        // Check method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        // Parse input: prefer JSON, fallback to $_POST (FormData/urlencoded)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        $input = null;
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
        if ($input === null) {
            // Fallback to POST array (multipart/form-data or application/x-www-form-urlencoded)
            $input = $_POST;
        }
        
        // Normalize and validate required fields
        $name = isset($input['name']) ? trim((string)$input['name']) : '';
        $description = isset($input['description']) ? trim((string)$input['description']) : '';
        $price = $input['price'] ?? null;
        $priceNum = is_numeric($price) ? (float)$price : null;
        if ($name === '' || $priceNum === null || $priceNum <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requieren nombre y un precio positivo válido']);
            return;
        }
        
        // Optional flags
        $menuId = isset($input['menu_id']) && (int)$input['menu_id'] > 0 ? (int)$input['menu_id'] : null;
        $saveToWeekly = !empty($input['save_to_weekly']) && in_array((string)$input['save_to_weekly'], ['1', 'true', 'on'], true);
        
        try {
            $pdo = $this->getDbConnection();
            
            // Detect if is_beverage and category columns exist
            $hasIsBeverage = false;
            $hasCategory = false;
            try {
                $cols = $pdo->query('PRAGMA table_info(weekly_menu_items)')->fetchAll();
                foreach ($cols as $col) {
                    if (isset($col['name']) && strcasecmp($col['name'], 'is_beverage') === 0) {
                        $hasIsBeverage = true;
                    }
                    if (isset($col['name']) && strcasecmp($col['name'], 'category') === 0) {
                        $hasCategory = true;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore schema detection errors and proceed without the flag
            }
            
            // Save beverage to weekly_menu_items
            if ($hasIsBeverage && $hasCategory) {
                $stmt = $pdo->prepare('
                    INSERT INTO weekly_menu_items (name, description, price, is_available, is_beverage, category) 
                    VALUES (:name, :description, :price, 1, 1, :category)
                ');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $priceNum,
                    ':category' => 'bebidas'
                ]);
            } elseif ($hasIsBeverage) {
                $stmt = $pdo->prepare('
                    INSERT INTO weekly_menu_items (name, description, price, is_available, is_beverage) 
                    VALUES (:name, :description, :price, 1, 1)
                ');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $priceNum
                ]);
            } elseif ($hasCategory) {
                $stmt = $pdo->prepare('
                    INSERT INTO weekly_menu_items (name, description, price, is_available, category) 
                    VALUES (:name, :description, :price, 1, :category)
                ');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $priceNum,
                    ':category' => 'bebidas'
                ]);
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO weekly_menu_items (name, description, price, is_available) 
                    VALUES (:name, :description, :price, 1)
                ');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $priceNum
                ]);
            }
            
            $weeklyItemId = $pdo->lastInsertId();
        
        // If menu_id is provided, also link this beverage to the specific menu
        if ($menuId) {
            $linkStmt = $pdo->prepare('
                INSERT OR IGNORE INTO menu_weekly_items (menu_id, weekly_item_id) 
                VALUES (:menu_id, :weekly_item_id)
            ');
            $linkStmt->execute([
                ':menu_id' => $menuId,
                ':weekly_item_id' => $weeklyItemId
            ]);
        }
        
        $message = '¡Bebida guardada correctamente!';
        if ($saveToWeekly) {
            $message .= ' Estará disponible para todos los menús futuros.';
        } else {
            $message .= ' Ahora está disponible para selección en otros menús.';
        }
        
        if ($menuId) {
            $message .= ' También se agregó a este menú.';
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'weekly_item_id' => $weeklyItemId
        ]);
            
        } catch (\PDOException $e) {
            error_log('Error saving beverage: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error en la base de datos']);
        }
    }
    
    /**
     * Save a new dessert via AJAX
     */
    public function saveDessert() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        // Parse input: prefer JSON, fallback to $_POST
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        $input = null;
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
        if ($input === null) {
            $input = $_POST;
        }

        // Validate
        $name = isset($input['name']) ? trim((string)$input['name']) : '';
        $description = isset($input['description']) ? trim((string)$input['description']) : '';
        $price = $input['price'] ?? null;
        $priceNum = is_numeric($price) ? (float)$price : null;
        if ($name === '' || $priceNum === null || $priceNum <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Se requieren nombre y un precio positivo válido']);
            return;
        }

        $menuId = isset($input['menu_id']) && (int)$input['menu_id'] > 0 ? (int)$input['menu_id'] : null;
        $saveToWeekly = !empty($input['save_to_weekly']) && in_array((string)$input['save_to_weekly'], ['1', 'true', 'on'], true);

        try {
            $pdo = $this->getDbConnection();

            // Detect schema columns
            $hasCategory = false;
            try {
                $cols = $pdo->query('PRAGMA table_info(weekly_menu_items)')->fetchAll();
                foreach ($cols as $col) {
                    if (isset($col['name']) && strcasecmp($col['name'], 'category') === 0) {
                        $hasCategory = true;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            // Insert dessert
            if ($hasCategory) {
                $stmt = $pdo->prepare('
                    INSERT INTO weekly_menu_items (name, description, price, is_available, category)
                    VALUES (:name, :description, :price, 1, :category)
                ');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $priceNum,
                    ':category' => 'postres'
                ]);
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO weekly_menu_items (name, description, price, is_available)
                    VALUES (:name, :description, :price, 1)
                ');
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $priceNum
                ]);
            }

            $weeklyItemId = $pdo->lastInsertId();

            // Link to menu if provided
            if ($menuId) {
                $linkStmt = $pdo->prepare('
                    INSERT OR IGNORE INTO menu_weekly_items (menu_id, weekly_item_id)
                    VALUES (:menu_id, :weekly_item_id)
                ');
                $linkStmt->execute([
                    ':menu_id' => $menuId,
                    ':weekly_item_id' => $weeklyItemId
                ]);
            }

            $message = '¡Postre guardado correctamente!';
            if ($saveToWeekly) {
                $message .= ' Estará disponible para todos los menús futuros.';
            } else {
                $message .= ' Ahora está disponible para selección en otros menús.';
            }
            if ($menuId) {
                $message .= ' También se agregó a este menú.';
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'weekly_item_id' => $weeklyItemId
            ]);
        } catch (\PDOException $e) {
            error_log('Error saving dessert: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error en la base de datos']);
        }
    }
    
    /**
     * Get all available beverages via AJAX
     */
    public function getBeverages() {
        // Check if request is GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        try {
            $pdo = $this->getDbConnection();
            
            // Prefer explicit is_beverage flag when available; fallback to name patterns
            $hasIsBeverage = false;
            try {
                $cols = $pdo->query('PRAGMA table_info(weekly_menu_items)')->fetchAll();
                foreach ($cols as $col) {
                    if (isset($col['name']) && strcasecmp($col['name'], 'is_beverage') === 0) {
                        $hasIsBeverage = true;
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore and fallback
            }
            
            if ($hasIsBeverage) {
                $stmt = $pdo->query('
                    SELECT id, name, description, price 
                    FROM weekly_menu_items 
                    WHERE is_available = 1 AND is_beverage = 1
                    ORDER BY name ASC
                ');
            } else {
                $stmt = $pdo->query('
                    SELECT id, name, description, price 
                    FROM weekly_menu_items 
                    WHERE is_available = 1 
                    AND (LOWER(name) LIKE "%agua%" OR LOWER(name) LIKE "%refresco%" OR 
                         LOWER(name) LIKE "%jugo%" OR LOWER(name) LIKE "%café%" OR 
                         LOWER(name) LIKE "%té%" OR LOWER(name) LIKE "%bebida%")
                    ORDER BY name ASC
                ');
            }
            
            $beverages = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'beverages' => $beverages
            ]);
            
        } catch (\PDOException $e) {
            error_log('Error getting beverages: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    }
    
    /**
     * Debug method to test menu data retrieval
     */
    public function debugMenus() {
        header('Content-Type: application/json');
        
        try {
            $menus = $this->getAllMenus();
            
            echo json_encode([
                'success' => true,
                'menu_count' => count($menus),
                'menus' => $menus,
                'sample_menu' => !empty($menus) ? $menus[0] : null
            ], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get menu categories for forms
     */
    private function getCategories() {
        return [
            'almuerzo' => 'Almuerzo',
            'merienda' => 'Merienda', 
            'bebidas' => 'Bebidas',
            'postres' => 'Postres'
        ];
    }
    
    /**
     * Alternative menu listing view using index_new.php template
     */
    public function indexNew() {
        $menus = $this->getAllMenus();
        
        return $this->view('menus/index_new', [
            'title' => 'Administrar Menús - ' . (defined('APP_NAME') ? APP_NAME : 'Siloe'),
            'menus' => $menus,
            'hideNavbar' => true,
            'wrapContainer' => false,
            'sidebarTitle' => 'Siloe empresas',
            'active' => 'menus'
        ])->layout('layouts/app');
    }
}
