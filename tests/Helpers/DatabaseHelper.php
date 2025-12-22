<?php

namespace Tests\Helpers;

use Database;
use PDO;

/**
 * Database Helper for Tests
 * Handles test database operations
 */
class DatabaseHelper
{
    private $db;
    private $pdo;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    /**
     * Start transaction
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
    
    /**
     * Check if record exists
     */
    public function hasRecord(string $table, array $data): bool
    {
        $conditions = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "SELECT COUNT(*) FROM $table WHERE " . implode(' AND ', $conditions);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Count records
     */
    public function countRecords(string $table, array $where = []): int
    {
        if (empty($where)) {
            $sql = "SELECT COUNT(*) FROM $table";
            $stmt = $this->pdo->query($sql);
        } else {
            $conditions = [];
            $params = [];
            
            foreach ($where as $key => $value) {
                $conditions[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $sql = "SELECT COUNT(*) FROM $table WHERE " . implode(' AND ', $conditions);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Clean table
     */
    public function cleanTable(string $table): void
    {
        $this->pdo->exec("DELETE FROM $table");
    }
    
    /**
     * Get last inserted ID
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
