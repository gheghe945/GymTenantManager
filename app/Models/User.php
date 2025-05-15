<?php
/**
 * User Model
 */
class User extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * Find user by email
     *
     * @param string $email User's email
     * @return array|false
     */
    public function findUserByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user by ID
     *
     * @param int $id User ID
     * @return array|false
     */
    public function getUserById($id) {
        return $this->getById($id);
    }
    
    /**
     * Get all users
     *
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT u.*, t.name as tenant_name 
                FROM {$this->table} u 
                LEFT JOIN tenants t ON u.tenant_id = t.id 
                ORDER BY u.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get users by tenant ID
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getUsersByTenantId($tenantId) {
        $sql = "SELECT u.*, t.name as tenant_name 
                FROM {$this->table} u 
                LEFT JOIN tenants t ON u.tenant_id = t.id 
                WHERE u.tenant_id = :tenant_id 
                ORDER BY u.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get users by role and tenant
     *
     * @param string $role User role
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getUsersByRoleAndTenant($role, $tenantId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE role = :role AND tenant_id = :tenant_id 
                ORDER BY name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count users
     *
     * @return int
     */
    public function countUsers() {
        return $this->count();
    }
    
    /**
     * Count users by role
     *
     * @param string $role User role
     * @param int|null $tenantId Tenant ID
     * @return int
     */
    public function countUsersByRole($role, $tenantId = null) {
        $whereClause = 'role = :role';
        $params = ['role' => $role];
        
        if ($tenantId !== null) {
            $whereClause .= ' AND tenant_id = :tenant_id';
            $params['tenant_id'] = $tenantId;
        }
        
        return $this->count($whereClause, $params);
    }
    
    /**
     * Count new members in a date range
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return int
     */
    public function countNewMembers($tenantId, $startDate, $endDate) {
        $whereClause = 'role = :role AND tenant_id = :tenant_id AND created_at BETWEEN :start_date AND :end_date';
        $params = [
            'role' => 'MEMBER',
            'tenant_id' => $tenantId,
            'start_date' => $startDate . ' 00:00:00',
            'end_date' => $endDate . ' 23:59:59'
        ];
        
        return $this->count($whereClause, $params);
    }
}
