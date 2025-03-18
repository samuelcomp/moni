<?php

namespace App\Models;

use App\Config\Database;

class Shift {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllShifts() {
        $stmt = $this->db->query("SELECT * FROM shifts ORDER BY name ASC");
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
    
    public function getShiftById($id) {
        $stmt = $this->db->prepare("SELECT * FROM shifts WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO shifts (name, start_time, end_time)
            VALUES (:name, :start_time, :end_time)
        ");
        
        $stmt->bindParam(':name', $data['name'], \PDO::PARAM_STR);
        $stmt->bindParam(':start_time', $data['start_time'], \PDO::PARAM_STR);
        $stmt->bindParam(':end_time', $data['end_time'], \PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $query = "UPDATE shifts SET ";
        $params = [];
        
        if (isset($data['name'])) {
            $query .= "name = :name, ";
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['start_time'])) {
            $query .= "start_time = :start_time, ";
            $params[':start_time'] = $data['start_time'];
        }
        
        if (isset($data['end_time'])) {
            $query .= "end_time = :end_time, ";
            $params[':end_time'] = $data['end_time'];
        }
        
        // Remove trailing comma and space
        $query = rtrim($query, ", ");
        
        $query .= " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM shifts WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function countShifts() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM shifts");
        return $stmt->fetchColumn();
    }
} 