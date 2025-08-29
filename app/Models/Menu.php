<?php

namespace App\Models;

use PDO;

class Menu {
    private $db;

    public function __construct() {
        $this->db = $this->getDbConnection();
    }
    
    /**
     * Get all weekly menu items
     */
    public function getWeeklyMenuItems() {
        $stmt = $this->db->query('SELECT * FROM weekly_menu_items WHERE is_available = 1 ORDER BY name ASC');
        return $stmt->fetchAll();
    }
    
    /**
     * Get weekly items for a specific menu
     * 
     * @param int $menuId The ID of the menu
     * @return array Array of weekly item IDs
     */
    public function getWeeklyItemsForMenu($menuId) {
        $stmt = $this->db->prepare('SELECT weekly_item_id FROM menu_weekly_items WHERE menu_id = :menu_id');
        $stmt->execute([':menu_id' => $menuId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'weekly_item_id');
    }
    
    /**
     * Add a weekly menu item to today's menu
     */
    public function addWeeklyItemToMenu($menuId, $weeklyItemId) {
        $weeklyItem = $this->getWeeklyMenuItemById($weeklyItemId);
        if (!$weeklyItem) {
            return false;
        }
        
        // Link the existing weekly item to the menu via join table for consistency
        $stmt = $this->db->prepare('
            INSERT OR IGNORE INTO menu_weekly_items (menu_id, weekly_item_id)
            VALUES (:menu_id, :weekly_item_id)
        ');
        
        return $stmt->execute([
            ':menu_id' => $menuId,
            ':weekly_item_id' => $weeklyItemId
        ]);
    }
    
    /**
     * Get weekly menu item by ID
     */
    public function getWeeklyMenuItemById($id) {
        $stmt = $this->db->prepare('SELECT * FROM weekly_menu_items WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get database connection
     */
    private function getDbConnection() {
        // Use the application-wide connection which enables PRAGMA foreign_keys
        return \getDbConnection();
    }

    /**
     * Get all menus
     */
    public function getAllMenus() {
        $stmt = $this->db->query('SELECT * FROM menus ORDER BY date DESC');
        return $stmt->fetchAll();
    }

    /**
     * Get menu by ID
     */
    public function getMenuById($id) {
        $stmt = $this->db->prepare('SELECT * FROM menus WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get available menu items for a specific date
     */
    public function getAvailableMenuItems($date) {
        error_log("Obteniendo ítems de menú disponibles para la fecha: " . $date);
        
        $results = [
            'daily' => [],
            'regular' => []
        ];
        
        // First, try to find a menu for the exact date (daily specials)
        $stmt = $this->db->prepare('
            SELECT mi.*, 1 as is_daily
            FROM menu_items mi
            JOIN menus m ON mi.menu_id = m.id
            WHERE date(m.date) = date(:date) 
              AND mi.is_available = 1 
              AND m.available = 1
            ORDER BY mi.name ASC
        ');
        $stmt->execute([':date' => $date]);
        $dailyItems = $stmt->fetchAll();
        
        // If no results, try with the date as a string (for backward compatibility)
        if (empty($dailyItems)) {
            $stmt = $this->db->prepare('
                SELECT mi.*, 1 as is_daily
                FROM menu_items mi
                JOIN menus m ON mi.menu_id = m.id
                WHERE m.date = :date_str 
                  AND mi.is_available = 1 
                  AND m.available = 1
                ORDER BY mi.name ASC
            ');
            $stmt->execute([':date_str' => (string)$date]);
            $dailyItems = $stmt->fetchAll();
        }
        
        // Get regular menu items (always available)
        $stmt = $this->db->prepare('
            SELECT mi.*, 0 as is_daily
            FROM menu_items mi
            WHERE mi.is_available = 1 
              AND mi.is_weekly_item = 0
              AND NOT EXISTS (
                  SELECT 1 FROM menus m 
                  WHERE m.id = mi.menu_id 
                  AND m.available = 1 
                  AND (date(m.date) = date(:date) OR m.date = :date_str)
              )
            ORDER BY mi.name ASC
        ');
        $stmt->execute([
            ':date' => $date,
            ':date_str' => (string)$date
        ]);
        $regularItems = $stmt->fetchAll();
        
        $results['daily'] = $dailyItems;
        $results['regular'] = $regularItems;
        
        error_log(sprintf(
            "Se encontraron %d ítems diarios y %d ítems regulares para la fecha: %s",
            count($dailyItems),
            count($regularItems),
            $date
        ));
        
        return $results;
    }

    /**
     * Get menu item by ID
     */
    public function getMenuItemById($id) {
        $stmt = $this->db->prepare('SELECT * FROM menu_items WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create a new menu
     */
    public function createMenu($data) {
        $stmt = $this->db->prepare('
            INSERT INTO menus (name, description, date, price, is_active)
            VALUES (:name, :description, :date, :price, :is_active)
        ');
        
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':date' => $data['date'],
            ':price' => $data['price'],
            ':is_active' => $data['is_active'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Update a menu
     */
    public function updateMenu($id, $data) {
        $stmt = $this->db->prepare('
            UPDATE menus 
            SET name = :name, 
                description = :description, 
                date = :date, 
                price = :price, 
                is_active = :is_active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':date' => $data['date'],
            ':price' => $data['price'],
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }

    /**
     * Delete a menu
     */
    public function deleteMenu($id) {
        $stmt = $this->db->prepare('DELETE FROM menus WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Create a menu item
     */
    public function createMenuItem($data) {
        $stmt = $this->db->prepare('
            INSERT INTO menu_items (
                menu_id, name, description, price, 
                is_vegetarian, has_gluten, has_dairy, is_available
            )
            VALUES (
                :menu_id, :name, :description, :price, 
                :is_vegetarian, :has_gluten, :has_dairy, :is_available
            )
        ');
        
        $stmt->execute([
            ':menu_id' => $data['menu_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':is_vegetarian' => $data['is_vegetarian'] ?? 0,
            ':has_gluten' => $data['has_gluten'] ?? 0,
            ':has_dairy' => $data['has_dairy'] ?? 0,
            ':is_available' => $data['is_available'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Update a menu item
     */
    public function updateMenuItem($id, $data) {
        $stmt = $this->db->prepare('
            UPDATE menu_items 
            SET menu_id = :menu_id,
                name = :name, 
                description = :description, 
                price = :price, 
                is_vegetarian = :is_vegetarian,
                has_gluten = :has_gluten,
                has_dairy = :has_dairy,
                is_available = :is_available,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
        
        return $stmt->execute([
            ':id' => $id,
            ':menu_id' => $data['menu_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':is_vegetarian' => $data['is_vegetarian'] ?? 0,
            ':has_gluten' => $data['has_gluten'] ?? 0,
            ':has_dairy' => $data['has_dairy'] ?? 0,
            ':is_available' => $data['is_available'] ?? 1
        ]);
    }

    /**
     * Delete a menu item
     */
    public function deleteMenuItem($id) {
        $stmt = $this->db->prepare('DELETE FROM menu_items WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get menu items for a specific menu
     */
    public function getMenuItemsByMenuId($menuId) {
        $stmt = $this->db->prepare('SELECT * FROM menu_items WHERE menu_id = :menu_id ORDER BY name ASC');
        $stmt->execute([':menu_id' => $menuId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get the most recent available menu
     */
    public function getMostRecentAvailableMenu() {
        $stmt = $this->db->prepare('
            SELECT * FROM menus 
            WHERE available = 1 
            ORDER BY date DESC 
            LIMIT 1
        ');
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get latest menus (for dashboard)
     */
    public function getLatestMenus($limit = 5) {
        $stmt = $this->db->prepare('
            SELECT * FROM menus 
            WHERE date >= CURRENT_DATE
            ORDER BY date ASC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
