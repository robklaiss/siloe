<?php

/**
 * Migración para crear la tabla de solicitudes de eliminación de empresas
 * 
 * Esta tabla almacenará las solicitudes pendientes de eliminación de empresas
 * que requieren aprobación de un administrador
 */
class CreateDeleteRequestsTable {

    /**
     * Ejecutar la migración
     */
    public function up() {
        $db = require_once __DIR__ . '/../../config/database.php';
        $pdo = $db['connection'];

        // Crear tabla para solicitudes de eliminación
        $pdo->exec("CREATE TABLE IF NOT EXISTS delete_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entity_type VARCHAR(50) NOT NULL, -- 'company', 'user', etc.
            entity_id INTEGER NOT NULL,
            entity_name VARCHAR(100) NOT NULL,
            requested_by INTEGER NOT NULL,
            requested_at DATETIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
            approved_by INTEGER NULL,
            approved_at DATETIME NULL,
            reason TEXT NULL,
            FOREIGN KEY (requested_by) REFERENCES users(id),
            FOREIGN KEY (approved_by) REFERENCES users(id)
        )");

        echo "Tabla delete_requests creada correctamente.\n";
        return true;
    }

    /**
     * Revertir la migración
     */
    public function down() {
        $db = require_once __DIR__ . '/../../config/database.php';
        $pdo = $db['connection'];

        // Eliminar tabla
        $pdo->exec("DROP TABLE IF EXISTS delete_requests");

        echo "Tabla delete_requests eliminada correctamente.\n";
        return true;
    }
}
