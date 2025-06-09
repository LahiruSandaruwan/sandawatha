<?php
abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->connect();
            
            if (!$this->db) {
                error_log("Failed to establish database connection in " . get_class($this));
                throw new Exception("Database connection failed");
            }
            
            // Set error mode to throw exceptions
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch (Exception $e) {
            error_log("Database connection error in " . get_class($this) . ": " . $e->getMessage());
            throw $e;
        }
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
            
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                error_log("Binding {$key} = " . var_export($value, true) . " (type: {$type})");
                $stmt->bindValue($key, $value, $type);
            }
            
            $result = $stmt->execute();
            error_log("Query execution result: " . ($result ? "success" : "failed"));
            
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
        // Override this method in child classes to specify allowed columns
        return ['id', 'created_at', 'updated_at'];
    }
    
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            return $this->fetchAll($sql, [':limit' => $limit, ':offset' => $offset]);
        }
        
        return $this->fetchAll($sql);
    }
    
    public function create($data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) { return ':' . $col; }, $columns);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        $this->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $columns = array_keys($data);
        $setClause = array_map(function($col) { return $col . ' = :' . $col; }, $columns);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }
        
        return $this->execute($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->execute($sql, [':id' => $id]);
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
}
?>