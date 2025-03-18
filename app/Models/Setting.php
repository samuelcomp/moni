<?php

namespace App\Models;

use App\Config\Database;

class Setting
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT * FROM settings";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Get a specific setting by key
     *
     * @param string $key
     * @return mixed
     */
    public function getSetting($key)
    {
        $sql = "SELECT value FROM settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result ? $result->value : null;
    }

    /**
     * Update a setting
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function updateSetting($key, $value)
    {
        // Check if setting exists
        $sql = "SELECT COUNT(*) FROM settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            // Update existing setting
            $sql = "UPDATE settings SET value = :value WHERE setting_key = :key";
        } else {
            // Insert new setting
            $sql = "INSERT INTO settings (setting_key, value) VALUES (:key, :value)";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Delete a setting
     *
     * @param string $key
     * @return bool
     */
    public function deleteSetting($key)
    {
        $sql = "DELETE FROM settings WHERE setting_key = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        
        return $stmt->execute();
    }
} 