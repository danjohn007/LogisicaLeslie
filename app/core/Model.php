<?php
/**
 * Clase Base Model
 * Sistema de Logística - Quesos y Productos Leslie
 */

class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get database-compatible date functions based on the driver
     */
    protected function getDateFunction($function, ...$args) {
        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        switch ($function) {
            case 'CURDATE':
                return $driver === 'sqlite' ? "DATE('now')" : 'CURDATE()';
                
            case 'NOW':
                return $driver === 'sqlite' ? 'CURRENT_TIMESTAMP' : 'NOW()';
                
            case 'DATE':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "DATE({$dateField})" : "DATE({$dateField})";
                
            case 'YEAR':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "strftime('%Y', {$dateField})" : "YEAR({$dateField})";
                
            case 'MONTH':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "strftime('%m', {$dateField})" : "MONTH({$dateField})";
                
            case 'YEARWEEK':
                $dateField = $args[0] ?? 'created_at';
                return $driver === 'sqlite' ? "strftime('%Y%W', {$dateField})" : "YEARWEEK({$dateField})";
                
            case 'DATE_SUB':
                $dateField = $args[0] ?? 'CURDATE()';
                $interval = $args[1] ?? 'INTERVAL 7 DAYS';
                if ($driver === 'sqlite') {
                    // Convert MySQL interval to SQLite format
                    if (strpos($interval, 'INTERVAL') !== false) {
                        preg_match('/INTERVAL\s+(\d+)\s+(\w+)/', $interval, $matches);
                        $num = $matches[1] ?? '7';
                        $unit = $matches[2] ?? 'DAYS';
                        $sqliteUnit = strtolower(rtrim($unit, 's')); // Convert DAYS to day
                        return "DATE({$dateField}, '-{$num} {$sqliteUnit}')";
                    }
                }
                return "DATE_SUB({$dateField}, {$interval})";
                
            case 'DATE_ADD':
                $dateField = $args[0] ?? 'CURDATE()';
                $interval = $args[1] ?? 'INTERVAL 7 DAYS';
                if ($driver === 'sqlite') {
                    // Convert MySQL interval to SQLite format
                    if (strpos($interval, 'INTERVAL') !== false) {
                        preg_match('/INTERVAL\s+(\d+)\s+(\w+)/', $interval, $matches);
                        $num = $matches[1] ?? '7';
                        $unit = $matches[2] ?? 'DAYS';
                        $sqliteUnit = strtolower(rtrim($unit, 's')); // Convert DAYS to day
                        return "DATE({$dateField}, '+{$num} {$sqliteUnit}')";
                    }
                }
                return "DATE_ADD({$dateField}, {$interval})";
                
            default:
                return $function;
        }
    }
    
    public function findAll($conditions = [], $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        // Handle conditions
        if (!empty($conditions) && is_array($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        // Handle old-style parameters where $conditions was actually $limit
        if (!is_array($conditions) && $conditions !== null) {
            $limit = $conditions;
            $offset = $limit ?: 0;
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $setPairs = [];
        foreach ($data as $key => $value) {
            $setPairs[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setPairs);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function count($where = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    public function commit() {
        return $this->db->commit();
    }
    
    public function rollback() {
        return $this->db->rollback();
    }
}