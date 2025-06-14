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
        $db = new PDO('sqlite:' . __DIR__ . '/../../database/siloe.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
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
     * Create a new user
     */
    public function createUser($data) {
        $stmt = $this->db->prepare('
            INSERT INTO users (name, email, password, role, is_active)
            VALUES (:name, :email, :password, :role, :is_active)
        ');
        
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'] ?? 'employee',
            ':is_active' => $data['is_active'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
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
     * Deactivate a user (soft delete)
     */
    public function deactivateUser($id) {
        $stmt = $this->db->prepare('
            UPDATE users 
            SET is_active = 0, 
                deactivated_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Reactivate a user
     */
    public function reactivateUser($id) {
        $stmt = $this->db->prepare('
            UPDATE users 
            SET is_active = 1, 
                deactivated_at = NULL,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');
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
