<?php

namespace App\Models;

use PDO;
use PDOException;

class EmployeeMenuSelection
{
    private $db;

    public function __construct()
    {
        $this->db = $this->getDbConnection();
    }

    /**
     * Get database connection
     */
    private function getDbConnection()
    {
        static $pdo = null;
        
        if ($pdo === null) {
            try {
                $dsn = 'sqlite:' . DB_PATH;
                $pdo = new PDO($dsn);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection failed');
            }
        }
        
        return $pdo;
    }

    /**
     * Create a new menu selection
     */
    public function createSelection(array $data): bool
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO employee_menu_selections 
                (user_id, menu_item_id, selection_date, notes, status, company_id, created_at, updated_at)
                VALUES (:user_id, :menu_item_id, :selection_date, :notes, :status, :company_id, :created_at, :updated_at)');

            $now = date('Y-m-d H:i:s');
            return $stmt->execute([
                ':user_id' => $data['user_id'],
                ':menu_item_id' => $data['menu_item_id'],
                ':selection_date' => $data['selection_date'],
                ':notes' => $data['notes'],
                ':status' => $data['status'],
                ':company_id' => $data['company_id'],
                ':created_at' => $now,
                ':updated_at' => $now
            ]);
        } catch (PDOException $e) {
            error_log('Error creating menu selection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing menu selection
     */
    public function updateSelection(int $id, array $data): bool
    {
        try {
            $sql = 'UPDATE employee_menu_selections SET ';
            $updates = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                $updates[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }

            $sql .= implode(', ', $updates);
            $sql .= ' WHERE id = :id';

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Error updating menu selection: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a selection by user and date
     */
    public function getSelectionByUserAndDate(int $userId, string $date): ?array
    {
        try {
            $stmt = $this->db->prepare('SELECT s.*, m.name as menu_name, m.description as menu_description, m.price as menu_price
                FROM employee_menu_selections s
                JOIN menu_items m ON s.menu_item_id = m.id
                WHERE s.user_id = :user_id AND s.selection_date = :selection_date
                LIMIT 1');
                
            $stmt->execute([
                ':user_id' => $userId,
                ':selection_date' => $date
            ]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Error getting menu selection: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all selections for an employee with pagination
     */
    public function getSelectionsByEmployee(
        int $employeeId, 
        string $startDate, 
        string $endDate, 
        int $page = 1, 
        int $perPage = 15
    ): array {
        try {
            $offset = ($page - 1) * $perPage;
            
            // Get total count for pagination
            $countStmt = $this->db->prepare('SELECT COUNT(*) as total 
                FROM employee_menu_selections 
                WHERE user_id = :user_id 
                AND selection_date BETWEEN :start_date AND :end_date');
                
            $countStmt->execute([
                ':user_id' => $employeeId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            
            $total = $countStmt->fetch()['total'];
            
            // Get paginated results
            $stmt = $this->db->prepare('SELECT s.*, m.name as menu_name, m.description as menu_description, m.price as menu_price
                FROM employee_menu_selections s
                JOIN menu_items m ON s.menu_item_id = m.id
                WHERE s.user_id = :user_id 
                AND s.selection_date BETWEEN :start_date AND :end_date
                ORDER BY s.selection_date DESC
                LIMIT :limit OFFSET :offset');
                
            $stmt->bindValue(':user_id', $employeeId, PDO::PARAM_INT);
            $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
            $stmt->bindValue(':end_date', $endDate, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $selections = $stmt->fetchAll();
            
            return [
                'data' => $selections,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ];
        } catch (PDOException $e) {
            error_log('Error getting employee selections: ' . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0
                ]
            ];
        }
    }

    /**
     * Get selection statistics for HR dashboard
     */
    public function getSelectionStats(int $companyId, string $date): array
    {
        try {
            // Get total employees
            $stmt = $this->db->prepare('SELECT COUNT(*) as total_employees 
                FROM users 
                WHERE company_id = :company_id 
                AND role = "employee" 
                AND is_active = 1');
            $stmt->execute([':company_id' => $companyId]);
            $result = $stmt->fetch();
            $totalEmployees = $result['total_employees'];

            // Get selections for today
            $stmt = $this->db->prepare('SELECT COUNT(DISTINCT s.user_id) as selected_count
                FROM employee_menu_selections s
                JOIN users u ON s.user_id = u.id
                WHERE u.company_id = :company_id 
                AND s.selection_date = :selection_date');
            $stmt->execute([
                ':company_id' => $companyId,
                ':selection_date' => $date
            ]);
            $result = $stmt->fetch();
            $selectedCount = $result['selected_count'];

            // Get most popular menu items
            $stmt = $this->db->prepare('SELECT m.name, COUNT(*) as selection_count
                FROM employee_menu_selections s
                JOIN menu_items m ON s.menu_item_id = m.id
                JOIN users u ON s.user_id = u.id
                WHERE u.company_id = :company_id 
                AND s.selection_date = :selection_date
                GROUP BY s.menu_item_id
                ORDER BY selection_count DESC
                LIMIT 5');
            $stmt->execute([
                ':company_id' => $companyId,
                ':selection_date' => $date
            ]);
            $popularItems = $stmt->fetchAll();

            return [
                'total_employees' => $totalEmployees,
                'selected_count' => $selectedCount,
                'not_selected_count' => max(0, $totalEmployees - $selectedCount),
                'selection_rate' => $totalEmployees > 0 ? round(($selectedCount / $totalEmployees) * 100) : 0,
                'popular_items' => $popularItems
            ];
        } catch (PDOException $e) {
            error_log('Error getting selection stats: ' . $e->getMessage());
            return [
                'total_employees' => 0,
                'selected_count' => 0,
                'not_selected_count' => 0,
                'selection_rate' => 0,
                'popular_items' => []
            ];
        }
    }
}
