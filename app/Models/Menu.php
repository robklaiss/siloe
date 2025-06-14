<?php

namespace App\Models;

use PDO;

class Menu {
    private $db;

    public function __construct() {
        $this->db = $this->getDbConnection();
    }

    /**
     * Get database connection
     */
    private function getDbConnection() {
        $db = new PDO('sqlite:' . __DIR__ . '/../../database/siloe.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
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
        $stmt = $this->db->prepare('
            SELECT mi.* 
            FROM menu_items mi
            JOIN menus m ON mi.menu_id = m.id
            WHERE m.date = :date AND mi.is_available = 1
            ORDER BY mi.name ASC
        ');
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
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
