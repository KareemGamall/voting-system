<?php

abstract class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find a record by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all records
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all records (alias for all())
     */
    public function getAll() {
        return $this->all();
    }
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return int|false Returns the last inserted ID on success, false on failure
     */
    public function create($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute($data)) {
                return $this->db->lastInsertId();
            }
            
            // Get error info if execution failed
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error in Model::create for table {$this->table}: " . print_r($errorInfo, true));
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));
            
            return false;
        } catch (PDOException $e) {
            error_log("PDO Exception in Model::create: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update a record
     */
    public function update($id, $data) {
        $setClause = [];
        foreach (array_keys($data) as $key) {
            $setClause[] = "$key = :$key";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$this->table} SET $setClause WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    /**
     * Delete a record
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Find records by custom condition
     */
    public function where($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE $conditions";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find a single record by custom condition
     */
    public function findWhere($conditions, $params = []) {
        $sql = "SELECT * FROM {$this->table} WHERE $conditions LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute raw query
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get the last inserted ID
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
    
    /**
     * Count records
     * 
     * @param array $conditions Optional conditions array (e.g., ['is_voter' => 1])
     * @return int
     */
    public function count($conditions = []) {
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            $whereClause = [];
            foreach (array_keys($conditions) as $key) {
                $whereClause[] = "$key = :$key";
            }
            $whereClause = implode(' AND ', $whereClause);
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $whereClause";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }
}
