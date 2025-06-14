<?php

namespace App\Models;

use PDO;

class Company {
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
     * Get all companies
     */
    public function getAllCompanies() {
        $stmt = $this->db->query('SELECT * FROM companies ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    /**
     * Get company by ID
     */
    public function getCompanyById($id) {
        $stmt = $this->db->prepare('SELECT * FROM companies WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get company by name
     */
    public function getCompanyByName($name) {
        $stmt = $this->db->prepare('SELECT * FROM companies WHERE name = :name');
        $stmt->execute([':name' => $name]);
        return $stmt->fetch();
    }

    /**
     * Create a new company
     */
    public function createCompany($data) {
        $stmt = $this->db->prepare('
            INSERT INTO companies (name, address, contact_email, contact_phone, is_active, logo)
            VALUES (:name, :address, :contact_email, :contact_phone, :is_active, :logo)
        ');
        
        $stmt->execute([
            ':name' => $data['name'],
            ':address' => $data['address'] ?? '',
            ':contact_email' => $data['contact_email'] ?? '',
            ':contact_phone' => $data['contact_phone'] ?? '',
            ':is_active' => $data['is_active'] ?? 1,
            ':logo' => $data['logo'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Update a company
     */
    public function updateCompany($id, $data) {
        // Start building the SQL query
        $sql = 'UPDATE companies SET ';
        $params = [':id' => $id];
        $fields = [];
        
        // Add fields that are present in the data
        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['address'])) {
            $fields[] = 'address = :address';
            $params[':address'] = $data['address'];
        }
        
        if (isset($data['contact_email'])) {
            $fields[] = 'contact_email = :contact_email';
            $params[':contact_email'] = $data['contact_email'];
        }
        
        if (isset($data['contact_phone'])) {
            $fields[] = 'contact_phone = :contact_phone';
            $params[':contact_phone'] = $data['contact_phone'];
        }
        
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = :is_active';
            $params[':is_active'] = $data['is_active'];
        }
        
        if (isset($data['logo'])) {
            $fields[] = 'logo = :logo';
            $params[':logo'] = $data['logo'];
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
     * Delete a company
     */
    public function deleteCompany($id) {
        $stmt = $this->db->prepare('DELETE FROM companies WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get users by company ID
     */
    public function getCompanyUsers($companyId) {
        $stmt = $this->db->prepare('
            SELECT * FROM users 
            WHERE company_id = :company_id
            ORDER BY name ASC
        ');
        $stmt->execute([':company_id' => $companyId]);
        return $stmt->fetchAll();
    }

    /**
     * Get active companies
     */
    public function getActiveCompanies() {
        $stmt = $this->db->query('
            SELECT * FROM companies 
            WHERE is_active = 1
            ORDER BY name ASC
        ');
        return $stmt->fetchAll();
    }
}
