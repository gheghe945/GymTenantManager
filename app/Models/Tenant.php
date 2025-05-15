<?php
/**
 * Tenant Model
 */
class Tenant extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'tenants';
    
    /**
     * Find tenant by subdomain
     *
     * @param string $subdomain Tenant subdomain
     * @return array|false
     */
    public function findTenantBySubdomain($subdomain) {
        $sql = "SELECT * FROM {$this->table} WHERE subdomain = :subdomain";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':subdomain', $subdomain, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get tenant by ID
     *
     * @param int $id Tenant ID
     * @return array|false
     */
    public function getTenantById($id) {
        return $this->getById($id);
    }
    
    /**
     * Get all tenants
     *
     * @return array
     */
    public function getAllTenants() {
        return $this->getAll();
    }
    
    /**
     * Get recent tenants with limit
     *
     * @param int $limit Maximum number of tenants to return
     * @return array
     */
    public function getRecentTenants($limit = 5) {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count tenants
     *
     * @return int
     */
    public function countTenants() {
        return $this->count();
    }
    
    /**
     * Create new tenant
     *
     * @param array $data Tenant data
     * @return int|false The ID of the newly created tenant, or false on failure
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, subdomain, address, phone, email, is_active, created_at, updated_at) 
                VALUES (:name, :subdomain, :address, :phone, :email, :is_active, NOW(), NOW()) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':subdomain', $data['subdomain'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // Return the new tenant ID
            return $stmt->fetchColumn();
        }
        
        return false;
    }
}
