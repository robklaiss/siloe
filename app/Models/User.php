<?php

namespace App\Models;

use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = $this->getDbConnection();
    }

    /**
     * Get database connection
     */
    private function getDbConnection() {
        // Include the database config to get the function
        if (!function_exists('getDbConnection')) {
            require_once __DIR__ . '/../../config/database.php';
        }
        return getDbConnection();
    }

    /**
     * Get all users
     */
    public function getAllUsers() {
        $stmt = $this->db->query('SELECT * FROM users ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Check if email already exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = 'SELECT COUNT(*) as count FROM users WHERE email = :email';
        $params = [':email' => $email];
        
        if ($excludeId) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get all employees for a company
     */
    /**
     * Get paginated employees for a company
     * 
     * @param int $companyId The company ID
     * @param int $page The page number (1-based)
     * @param int $perPage Number of items per page
     * @return array Array containing 'data' and 'pagination' info
     */
    public function getEmployeesByCompanyPaginated($companyId, $page = 1, $perPage = 10) {
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // For admin users, get all employees; for HR users, filter by company_id
        $whereClause = $companyId !== null 
            ? 'WHERE company_id = :company_id AND role = "employee"' 
            : 'WHERE role = "employee"';
        
        // Get total count for pagination
        $total = $companyId !== null 
            ? $this->getEmployeeCountByCompany($companyId) 
            : $this->getTotalEmployeeCount();
        
        // Get paginated data
        $sql = "SELECT * FROM users 
                $whereClause
                ORDER BY name ASC 
                LIMIT :limit OFFSET :offset";
                
        $stmt = $this->db->prepare($sql);
        
        if ($companyId !== null) {
            $stmt->bindValue(':company_id', $companyId, PDO::PARAM_INT);
        }
        
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll();
        
        // Calculate pagination info
        $totalPages = $perPage > 0 ? ceil($total / $perPage) : 1;
        
        return [
            'data' => $data,
            'pagination' => [
                'total' => (int)$total,
                'per_page' => (int)$perPage,
                'current_page' => (int)$page,
                'last_page' => max(1, $totalPages),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
    
    /**
     * Get total count of all employees (for admin users)
     */
    public function getTotalEmployeeCount() {
        $stmt = $this->db->query('SELECT COUNT(*) as count FROM users WHERE role = "employee"');
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get all employees for a company (without pagination)
     */
    public function getEmployeesByCompany($companyId) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE company_id = :company_id AND role = "employee" ORDER BY name ASC');
        $stmt->execute([':company_id' => $companyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get employee count for a company
     */
    public function getEmployeeCountByCompany($companyId) {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE company_id = :company_id AND role = "employee"');
        $stmt->execute([':company_id' => $companyId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Get active employee count for a company
     */
    public function getActiveEmployeeCountByCompany($companyId) {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM users WHERE company_id = :company_id AND role = "employee" AND is_active = 1');
        $stmt->execute([':company_id' => $companyId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data including name, email, password, role, company_id, etc.
     * @return int The ID of the newly created user
     */
    public function createUser($data) {
        $sql = 'INSERT INTO users (
                    name, 
                    email, 
                    password, 
                    role, 
                    ' . (isset($data['company_id']) ? 'company_id, ' : '') . '
                    is_active, 
                    created_at, 
                    updated_at
                ) VALUES (
                    :name, 
                    :email, 
                    :password, 
                    :role, 
                    ' . (isset($data['company_id']) ? ':company_id, ' : '') . '
                    :is_active, 
                    datetime("now"), 
                    datetime("now")
                )';
                
        $stmt = $this->db->prepare($sql);
        
        $params = [
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'] ?? 'employee',
            ':is_active' => $data['is_active'] ?? 1
        ];
        
        if (isset($data['company_id'])) {
            $params[':company_id'] = $data['company_id'];
        }
        
        $stmt->execute($params);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Deactivate a user (soft delete)
     * 
     * @param int $id The ID of the user to deactivate
     * @param int|null $companyId Optional company ID for verification
     * @return bool True on success
     * @throws \Exception If user not found or unauthorized
     */
    public function deactivateUser($id, $companyId = null) {
        // If company ID is provided, verify the user belongs to the company
        if ($companyId !== null) {
            $user = $this->getUserById($id);
            if (!$user || $user['company_id'] != $companyId) {
                throw new \Exception('User not found or unauthorized');
            }
        }
        
        $stmt = $this->db->prepare('UPDATE users SET is_active = 0, deactivated_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Reactivate a user
     * 
     * @param int $id The ID of the user to reactivate
     * @param int|null $companyId Optional company ID for verification
     * @return bool True on success
     * @throws \Exception If user not found or unauthorized
     */
    public function reactivateUser($id, $companyId = null) {
        // If company ID is provided, verify the user belongs to the company
        if ($companyId !== null) {
            $user = $this->getUserById($id);
            if (!$user || $user['company_id'] != $companyId) {
                throw new \Exception('User not found or unauthorized');
            }
        }
        
        $stmt = $this->db->prepare('UPDATE users SET is_active = 1, deactivated_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Update a user
     */
    public function updateUser($id, $data) {
        // Start building the SQL query
        $sql = 'UPDATE users SET ';
        $params = [':id' => $id];
        $fields = [];
        
        // Add fields that are present in the data
        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params[':email'] = $data['email'];
        }
        
        if (isset($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role'])) {
            $fields[] = 'role = :role';
            $params[':role'] = $data['role'];
        }
        
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = :is_active';
            $params[':is_active'] = $data['is_active'];
            
            // If deactivating user, set deactivated_at timestamp
            if ($data['is_active'] == 0) {
                $fields[] = 'deactivated_at = CURRENT_TIMESTAMP';
            } else {
                $fields[] = 'deactivated_at = NULL';
            }
        }
        
        // Always update the updated_at timestamp
        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        
        // Complete the SQL query
        $sql .= implode(', ', $fields) . ' WHERE id = :id';
        
        // Execute the query
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a user
     */
    public function deleteUser($id) {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all active employees
     */
    public function getAllActiveEmployees() {
        $stmt = $this->db->prepare('
            SELECT * FROM users 
            WHERE role = :role AND is_active = 1
            ORDER BY name ASC
        ');
        $stmt->execute([':role' => 'employee']);
        return $stmt->fetchAll();
    }

    /**
     * Get all employees (active and inactive)
     */
    public function getAllEmployees() {
        $stmt = $this->db->prepare('
            SELECT * FROM users 
            WHERE role = :role
            ORDER BY is_active DESC, name ASC
        ');
        $stmt->execute([':role' => 'employee']);
        return $stmt->fetchAll();
    }

    /**
     * Verify user credentials
     */
    public function verifyCredentials($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!$user['is_active']) {
            return false;
        }
        
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
}
