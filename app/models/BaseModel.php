<?php
require_once __DIR__ . '/../../config/database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    protected function query($sql, $params = []) {
        try {
            error_log("\n=== Database Query ===");
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            
            $stmt = $this->execute($sql, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Query returned " . count($result) . " rows");
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in query(): " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    protected function execute($sql, $params = []) {
        try {
            error_log("\n=== Database Query ===");
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                $error = $this->db->errorInfo();
                error_log("Failed to prepare statement: " . $error[2]);
                throw new PDOException("Failed to prepare statement: " . $error[2]);
            }
            
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                error_log("Binding {$key} = " . var_export($value, true) . " (type: {$type})");
                if (!$stmt->bindValue($key, $value, $type)) {
                    $error = $stmt->errorInfo();
                    error_log("Failed to bind parameter {$key}: " . $error[2]);
                    throw new PDOException("Failed to bind parameter {$key}: " . $error[2]);
                }
            }
            
            $result = $stmt->execute();
            error_log("Query execution result: " . ($result ? "success" : "failed"));
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Query execution failed: " . $error[2]);
                throw new PDOException("Query execution failed: " . $error[2]);
            }
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error in execute(): " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    protected function fetchAll($sql, $params = []) {
        try {
            error_log("\n=== Database Query (fetchAll) ===");
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            
            $stmt = $this->execute($sql, $params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Query returned " . count($result) . " rows");
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in fetchAll(): " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    protected function fetchOne($sql, $params = []) {
        try {
            error_log("\n=== Database Query (fetchOne) ===");
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            
            $stmt = $this->execute($sql, $params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Query returned: " . ($result ? "1 row" : "no rows"));
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in fetchOne(): " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parameters: " . json_encode($params));
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->fetchOne($sql, [':id' => $id]);
    }
    
    public function findBy($column, $value) {
        // Whitelist allowed columns to prevent SQL injection
        $allowedColumns = $this->getAllowedColumns();
        if (!in_array($column, $allowedColumns)) {
            throw new InvalidArgumentException("Invalid column name: " . $column);
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        return $this->fetchOne($sql, [':value' => $value]);
    }
    
    protected function getAllowedColumns() {
        return [];
    }
    
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            return $this->fetchAll($sql, [':limit' => $limit, ':offset' => $offset]);
        }
        
        return $this->fetchAll($sql);
    }
    
    protected function create($data) {
        $allowedColumns = $this->getAllowedColumns();
        $filteredData = array_intersect_key($data, array_flip($allowedColumns));
        
        if (empty($filteredData)) {
            throw new Exception('No valid data provided');
        }
        
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = implode(', ', array_map(function($key) {
            return ":{$key}";
        }, array_keys($filteredData)));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $params = array_combine(
                array_map(function($key) { return ":{$key}"; }, array_keys($filteredData)),
                array_values($filteredData)
            );
            
            $this->execute($sql, $params);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error in create(): " . $e->getMessage());
            throw $e;
        }
    }
    
    protected function update($id, $data) {
        $allowedColumns = $this->getAllowedColumns();
        $filteredData = array_intersect_key($data, array_flip($allowedColumns));
        
        if (empty($filteredData)) {
            throw new Exception('No valid data provided');
        }
        
        $setClause = implode(', ', array_map(function($key) {
            return "{$key} = :{$key}";
        }, array_keys($filteredData)));
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        
        try {
            $params = array_combine(
                array_map(function($key) { return ":{$key}"; }, array_keys($filteredData)),
                array_values($filteredData)
            );
            $params[':id'] = $id;
            
            return $this->execute($sql, $params);
        } catch (PDOException $e) {
            error_log("Database error in update(): " . $e->getMessage());
            throw $e;
        }
    }
    
    protected function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        try {
            return $this->execute($sql, [':id' => $id]);
        } catch (PDOException $e) {
            error_log("Database error in delete(): " . $e->getMessage());
            throw $e;
        }
    }
    
    public function count($where = null, $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        
        $result = $this->fetchOne($sql, $params);
        return $result['count'];
    }
    
    public function paginate($page = 1, $perPage = 20, $where = null, $params = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $queryParams = array_merge($params, [
            ':limit' => $perPage,
            ':offset' => $offset
        ]);
        
        $data = $this->fetchAll($sql, $queryParams);
        $totalCount = $this->count($where, $params);
        $totalPages = ceil($totalCount / $perPage);
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }
    
    protected function beginTransaction() {
        if (!$this->db->inTransaction()) {
            return $this->db->beginTransaction();
        }
        return true;
    }
    
    protected function commit() {
        if ($this->db->inTransaction()) {
            return $this->db->commit();
        }
        return true;
    }
    
    protected function rollback() {
        if ($this->db->inTransaction()) {
            return $this->db->rollBack();
        }
        return true;
    }
}
?>