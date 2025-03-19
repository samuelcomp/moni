<?php

namespace App\Models;

use PDO;
use PDOException;

class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }

    /**
     * Get all records from the table
     * 
     * @return array Records
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a record by ID
     * 
     * @param int $id Record ID
     * @return object|null Record
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error in getById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new record
     * 
     * @param array $data Record data
     * @return int|bool Last insert ID or false on failure
     */
    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a record
     * 
     * @param int $id Record ID
     * @param array $data Record data
     * @return bool Success
     */
    public function update($id, $data) {
        try {
            $setClause = '';
            foreach (array_keys($data) as $key) {
                $setClause .= "{$key} = :{$key}, ";
            }
            $setClause = rtrim($setClause, ', ');
            
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':id', $id);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a record
     * 
     * @param int $id Record ID
     * @return bool Success
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }
} 