<?php

namespace App\Services;

use App\Config\Database;

class AuditService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function log($userId, $action, $entityType, $entityId = null, $oldValues = null, $newValues = null) {
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
            VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $oldValuesJson = $oldValues ? json_encode($oldValues) : null;
        $newValuesJson = $newValues ? json_encode($newValues) : null;
        
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':action', $action, \PDO::PARAM_STR);
        $stmt->bindParam(':entity_type', $entityType, \PDO::PARAM_STR);
        $stmt->bindParam(':entity_id', $entityId, \PDO::PARAM_INT);
        $stmt->bindParam(':old_values', $oldValuesJson, \PDO::PARAM_STR);
        $stmt->bindParam(':new_values', $newValuesJson, \PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ipAddress, \PDO::PARAM_STR);
        $stmt->bindParam(':user_agent', $userAgent, \PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    public function getAuditLogs($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT al.*, u.username
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND al.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['entity_type'])) {
            $query .= " AND al.entity_type = :entity_type";
            $params[':entity_type'] = $filters['entity_type'];
        }
        
        if (!empty($filters['entity_id'])) {
            $query .= " AND al.entity_id = :entity_id";
            $params[':entity_id'] = $filters['entity_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function countAuditLogs($filters = []) {
        $query = "
            SELECT COUNT(*) FROM audit_logs al
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $query .= " AND al.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $query .= " AND al.action = :action";
            $params[':action'] = $filters['action'];
        }
        
        if (!empty($filters['entity_type'])) {
            $query .= " AND al.entity_type = :entity_type";
            $params[':entity_type'] = $filters['entity_type'];
        }
        
        if (!empty($filters['entity_id'])) {
            $query .= " AND al.entity_id = :entity_id";
            $params[':entity_id'] = $filters['entity_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND DATE(al.created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND DATE(al.created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
} 