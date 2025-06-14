<?php

class AddLogoToCompaniesTable {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO('sqlite:' . __DIR__ . '/../siloe.db');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
            exit;
        }
    }

    public function up() {
        $sql = "ALTER TABLE companies ADD COLUMN logo VARCHAR(255) NULL DEFAULT NULL";
        
        try {
            $this->db->exec($sql);
            echo "Migration 'AddLogoToCompaniesTable' successful." . PHP_EOL;
            return true;
        } catch (PDOException $e) {
            echo "Error migrating 'AddLogoToCompaniesTable': " . $e->getMessage() . PHP_EOL;
            return false;
        }
    }

    public function down() {
        $sql = "ALTER TABLE companies DROP COLUMN logo";
        
        try {
            $this->db->exec($sql);
            echo "Rollback 'AddLogoToCompaniesTable' successful." . PHP_EOL;
            return true;
        } catch (PDOException $e) {
            echo "Error rolling back 'AddLogoToCompaniesTable': " . $e->getMessage() . PHP_EOL;
            return false;
        }
    }
}
