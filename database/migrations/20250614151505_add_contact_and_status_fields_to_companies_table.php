<?php

class AddContactFieldsToCompaniesTable
{
    private $db;

    public function __construct()
    {
        // Establish SQLite connection directly
        try {
            $dbPath = __DIR__ . '/../../database/siloe.db';
            // echo "Connecting to SQLite: " . $dbPath . "\n"; // Debug line
            $this->db = new \PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            // echo "Connection successful.\n"; // Debug line
        } catch (\PDOException $e) {
            echo "Failed to connect to database: " . $e->getMessage() . "\n";
            exit(1); // Exit if connection fails
        }
    }

    public function up()
    {
        $sql = "ALTER TABLE companies ADD COLUMN contact_email VARCHAR(255) NULL;"; // Or wherever it fits best
        try {
            $this->db->exec($sql);
            echo "Column 'contact_email' added to 'companies' table successfully.\n";
        } catch (\PDOException $e) {
            echo "Error adding column 'contact_email': " . $e->getMessage() . "\n";
        }

        $sqlPhone = "ALTER TABLE companies ADD COLUMN contact_phone VARCHAR(50) NULL;";
        try {
            $this->db->exec($sqlPhone);
            echo "Column 'contact_phone' added to 'companies' table successfully.\n";
        } catch (\PDOException $e) {
            echo "Error adding column 'contact_phone': " . $e->getMessage() . "\n";
        }

        $sqlIsActive = "ALTER TABLE companies ADD COLUMN is_active INTEGER DEFAULT 1;"; // SQLite uses INTEGER for BOOLEAN
        try {
            $this->db->exec($sqlIsActive);
            echo "Column 'is_active' added to 'companies' table successfully.\n";
        } catch (\PDOException $e) {
            echo "Error adding column 'is_active': " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        // Optional: SQL to remove the columns if needed for rollback
        $sql = "ALTER TABLE companies DROP COLUMN contact_phone;";
        try {
            $this->db->exec($sql);
            echo "Column 'contact_phone' dropped from 'companies' table successfully.\n";
        } catch (\PDOException $e) {
            echo "Error dropping column 'contact_phone': " . $e->getMessage() . "\n";
        }

        $sqlEmail = "ALTER TABLE companies DROP COLUMN contact_email;";
        try {
            $this->db->exec($sqlEmail);
            echo "Column 'contact_email' dropped from 'companies' table successfully.\n";
        } catch (\PDOException $e) {
            echo "Error dropping column 'contact_email': " . $e->getMessage() . "\n";
        }
    }
}

// To run this migration directly (for projects without a migration runner):
// $migration = new AddContactFieldsToCompaniesTable();
// $migration->up();
// To rollback:
// $migration->down();

?>
