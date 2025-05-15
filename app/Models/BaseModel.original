<?php
/**
 * Base Model
 */
class BaseModel
{
    protected $db;
    protected $table;
    protected $tenantColumn = 'tenant_id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get a record by ID
     * @param int $id
     * @return array|boolean
     */
    public function getById($id)
    {
        $tenantId = getCurrentTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all records
     * @return array
     */
    public function getAll()
    {
        $tenantId = getCurrentTenantId();
        $sql = "SELECT * FROM {$this->table}";
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " WHERE {$this->tenantColumn} = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a new record
     * @param array $data
     * @return boolean
     */
    public function insert($data)
    {
        $tenantId = getCurrentTenantId();
        if ($tenantId !== null && $this->tenantColumn && !isset($data[$this->tenantColumn])) {
            $data[$this->tenantColumn] = $tenantId;
        }
        
        $fields = array_keys($data);
        $placeholders = array_map(function ($field) {
            return ':' . $field;
        }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind values
        foreach ($data as $key => $value) {
            // Salta i valori non scalari (array, oggetti, ecc.)
            if (!is_scalar($value) && !is_null($value)) {
                error_log("Skipping non-scalar value for key: " . $key);
                continue;
            }
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Update a record
     * @param array $data
     * @return boolean
     */
    public function update($data)
    {
        $id = $data['id'];
        unset($data['id']);
        
        $tenantId = getCurrentTenantId();
        if (isset($data[$this->tenantColumn])) {
            $tenantId = $data[$this->tenantColumn];
        }
        
        $fields = array_keys($data);
        $placeholders = array_map(function ($field) {
            return $field . ' = :' . $field;
        }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $placeholders) . " WHERE id = :id";
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Bind values
        foreach ($data as $key => $value) {
            // Salta i valori non scalari (array, oggetti, ecc.)
            if (!is_scalar($value) && !is_null($value)) {
                error_log("Skipping non-scalar value for key: " . $key);
                continue;
            }
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($tenantId !== null) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }

    /**
     * Delete a record
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        $tenantId = getCurrentTenantId();
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }

    /**
     * Find records by custom field
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public function findByField($field, $value)
    {
        $tenantId = getCurrentTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE $field = :value";
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a single record by custom field
     * @param string $field
     * @param mixed $value
     * @return array|boolean
     */
    public function findOneByField($field, $value)
    {
        $tenantId = getCurrentTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE $field = :value";
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':value', $value);
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find records by custom criteria
     * @param array $criteria
     * @return array
     */
    public function findByCriteria($criteria)
    {
        $tenantId = getCurrentTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        
        foreach ($criteria as $key => $value) {
            $sql .= " AND $key = :$key";
        }
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($criteria as $key => $value) {
            // Salta i valori non scalari (array, oggetti, ecc.)
            if (!is_scalar($value) && !is_null($value)) {
                error_log("Skipping non-scalar value for key: " . $key);
                continue;
            }
            $stmt->bindValue(':' . $key, $value);
        }
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find one record by custom criteria
     * @param array $criteria
     * @return array|boolean
     */
    public function findOneByCriteria($criteria)
    {
        $tenantId = getCurrentTenantId();
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        
        foreach ($criteria as $key => $value) {
            $sql .= " AND $key = :$key";
        }
        
        if ($tenantId !== null && $this->tenantColumn) {
            $sql .= " AND {$this->tenantColumn} = :tenant_id";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($criteria as $key => $value) {
            // Salta i valori non scalari (array, oggetti, ecc.)
            if (!is_scalar($value) && !is_null($value)) {
                error_log("Skipping non-scalar value for key: " . $key);
                continue;
            }
            $stmt->bindValue(':' . $key, $value);
        }
        
        if ($tenantId !== null && $this->tenantColumn) {
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}