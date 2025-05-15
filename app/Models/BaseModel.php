<?php
/**
 * Base Model class that all models extend
 */
class BaseModel {
    /**
     * Database connection
     *
     * @var PDO
     */
    protected $db;
    
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    /**
     * Get all records from the table with tenant filtering
     *
     * @param int|null $tenantId Tenant ID for filtering
     * @return array
     */
    public function getAll($tenantId = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        // Add tenant filtering if applicable
        if ($tenantId !== null) {
            $sql .= " WHERE tenant_id = :tenant_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        } else {
            $stmt = $this->db->prepare($sql);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a single record by ID with tenant filtering
     *
     * @param int $id Record ID
     * @param int|null $tenantId Tenant ID for filtering
     * @return array|false
     */
    public function getById($id, $tenantId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        
        // Add tenant filtering if applicable
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($tenantId !== null) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new record
     *
     * @param array $data Data to insert
     * @return bool
     */
    public function create($data) {
        // Extract keys and values
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind values
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Update a record by ID with tenant filtering
     *
     * @param array $data Data to update (including id)
     * @param int|null $tenantId Tenant ID for filtering
     * @return bool
     */
    public function update($data, $tenantId = null) {
        // Check if ID exists in data
        if (!isset($data['id'])) {
            return false;
        }
        
        // Extract ID and remove from data
        $id = $data['id'];
        unset($data['id']);
        
        // Build SET part of the query
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "{$key} = :{$key}, ";
        }
        $set = rtrim($set, ', ');
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE id = :id";
        
        // Add tenant filtering if applicable
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Bind values
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($tenantId !== null) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Delete a record by ID with tenant filtering
     *
     * @param int $id Record ID
     * @param int|null $tenantId Tenant ID for filtering
     * @return bool
     */
    public function delete($id, $tenantId = null) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        // Add tenant filtering if applicable
        if ($tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($tenantId !== null) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Count records with optional filtering
     *
     * @param string $whereClause Where clause (without WHERE keyword)
     * @param array $params Parameters for binding
     * @return int
     */
    public function count($whereClause = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if (!empty($whereClause)) {
            $sql .= " WHERE {$whereClause}";
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    /**
     * Get records with custom query and parameters
     *
     * @param string $sql SQL query
     * @param array $params Parameters for binding
     * @return array
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a single record with custom query and parameters
     *
     * @param string $sql SQL query
     * @param array $params Parameters for binding
     * @return array|false
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a single value from a query
     *
     * @param string $sql SQL query
     * @param array $params Parameters for binding
     * @return mixed
     */
    public function queryValue($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
