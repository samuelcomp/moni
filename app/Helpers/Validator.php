<?php

namespace App\Helpers;

class Validator {
    private $data;
    private $errors = [];
    
    /**
     * Constructor
     * 
     * @param array $data
     */
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Check if fields are required
     * 
     * @param array $fields
     * @return $this
     */
    public function required($fields) {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
                $this->errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        return $this;
    }
    
    /**
     * Check if field is a valid email
     * 
     * @param string $field
     * @return $this
     */
    public function email($field) {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = 'Invalid email format';
            }
        }
        
        return $this;
    }
    
    /**
     * Check if field has minimum length
     * 
     * @param string $field
     * @param int $length
     * @return $this
     */
    public function minLength($field, $length) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = ucfirst($field) . ' must be at least ' . $length . ' characters';
        }
        
        return $this;
    }
    
    /**
     * Check if field has maximum length
     * 
     * @param string $field
     * @param int $length
     * @return $this
     */
    public function maxLength($field, $length) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = ucfirst($field) . ' must not exceed ' . $length . ' characters';
        }
        
        return $this;
    }
    
    /**
     * Check if field matches another field
     * 
     * @param string $field
     * @param string $matchField
     * @param string $label
     * @return $this
     */
    public function match($field, $matchField, $label = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField])) {
            if ($this->data[$field] !== $this->data[$matchField]) {
                $this->errors[$field] = ucfirst($field) . ' must match ' . ($label ?: $matchField);
            }
        }
        
        return $this;
    }
    
    /**
     * Check if field is numeric
     * 
     * @param string $field
     * @return $this
     */
    public function numeric($field) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = ucfirst($field) . ' must be a number';
        }
        
        return $this;
    }
    
    /**
     * Check if field is a valid date
     * 
     * @param string $field
     * @param string $format
     * @return $this
     */
    public function date($field, $format = 'Y-m-d') {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $date = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = ucfirst($field) . ' must be a valid date in format ' . $format;
            }
        }
        
        return $this;
    }
    
    /**
     * Check if field is a valid time
     * 
     * @param string $field
     * @param string $format
     * @return $this
     */
    public function time($field, $format = 'H:i:s') {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            $time = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$time || $time->format($format) !== $this->data[$field]) {
                $this->errors[$field] = ucfirst($field) . ' must be a valid time in format ' . $format;
            }
        }
        
        return $this;
    }
    
    /**
     * Check if validation passes
     * 
     * @return bool
     */
    public function isValid() {
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     * 
     * @return string|null
     */
    public function getFirstError() {
        return reset($this->errors) ?: null;
    }
} 