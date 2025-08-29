<?php

namespace App\Models;

use App\Core\Model;

class DeleteRequest extends Model {
    /**
     * Crear una nueva solicitud de eliminación
     */
    public function createRequest($entityType, $entityId, $entityName, $requestedBy, $reason = null) {
        $sql = "INSERT INTO delete_requests 
                (entity_type, entity_id, entity_name, requested_by, requested_at, reason, status) 
                VALUES (?, ?, ?, ?, datetime('now'), ?, 'pending')";
        
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute([$entityType, $entityId, $entityName, $requestedBy, $reason]);
        
        return self::getDb()->lastInsertId();
    }
    
    /**
     * Obtener todas las solicitudes de eliminación pendientes
     */
    public function getPendingRequests() {
        $sql = "SELECT dr.*, 
                u1.name as requester_name, 
                u1.email as requester_email,
                u2.name as approver_name
                FROM delete_requests dr
                LEFT JOIN users u1 ON dr.requested_by = u1.id
                LEFT JOIN users u2 ON dr.approved_by = u2.id
                WHERE dr.status = 'pending'
                ORDER BY dr.requested_at DESC";
                
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener solicitudes de eliminación por tipo de entidad
     */
    public function getRequestsByEntityType($entityType, $status = null) {
        $sql = "SELECT dr.*, 
                u1.name as requester_name, 
                u1.email as requester_email,
                u2.name as approver_name
                FROM delete_requests dr
                LEFT JOIN users u1 ON dr.requested_by = u1.id
                LEFT JOIN users u2 ON dr.approved_by = u2.id
                WHERE dr.entity_type = ?";
                
        if ($status) {
            $sql .= " AND dr.status = ?";
            $params = [$entityType, $status];
        } else {
            $params = [$entityType];
        }
        
        $sql .= " ORDER BY dr.requested_at DESC";
        
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Aprobar una solicitud de eliminación
     */
    public function approveRequest($requestId, $approvedBy) {
        $sql = "UPDATE delete_requests 
                SET status = 'approved', 
                    approved_by = ?, 
                    approved_at = datetime('now')
                WHERE id = ?";
                
        $stmt = self::getDb()->prepare($sql);
        $result = $stmt->execute([$approvedBy, $requestId]);
        
        return $result;
    }
    
    /**
     * Rechazar una solicitud de eliminación
     */
    public function rejectRequest($requestId, $approvedBy) {
        $sql = "UPDATE delete_requests 
                SET status = 'rejected', 
                    approved_by = ?, 
                    approved_at = datetime('now')
                WHERE id = ?";
                
        $stmt = self::getDb()->prepare($sql);
        $result = $stmt->execute([$approvedBy, $requestId]);
        
        return $result;
    }
    
    /**
     * Obtener una solicitud por ID
     */
    public function getRequestById($id) {
        $sql = "SELECT dr.*, 
                u1.name as requester_name, 
                u1.email as requester_email,
                u2.name as approver_name
                FROM delete_requests dr
                LEFT JOIN users u1 ON dr.requested_by = u1.id
                LEFT JOIN users u2 ON dr.approved_by = u2.id
                WHERE dr.id = ?";
                
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch();
    }
    
    /**
     * Verificar si existe una solicitud pendiente para una entidad
     */
    public function hasPendingRequest($entityType, $entityId) {
        $sql = "SELECT COUNT(*) FROM delete_requests 
                WHERE entity_type = ? 
                AND entity_id = ? 
                AND status = 'pending'";
                
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute([$entityType, $entityId]);
        
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Contar solicitudes pendientes
     */
    public function countPendingRequests() {
        $sql = "SELECT COUNT(*) FROM delete_requests WHERE status = 'pending'";
        $stmt = self::getDb()->prepare($sql);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }
}
