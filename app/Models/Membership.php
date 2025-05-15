<?php
/**
 * Membership Model
 */
class Membership extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'memberships';
    
    /**
     * Get membership by ID with tenant check
     *
     * @param int $id Membership ID
     * @param int|null $tenantId Tenant ID for filtering
     * @return array|false
     */
    public function getMembershipById($id, $tenantId = null) {
        return $this->getById($id, $tenantId);
    }
    
    /**
     * Get memberships by tenant ID
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getMembershipsByTenantId($tenantId) {
        $sql = "SELECT m.*, u.name as user_name 
                FROM {$this->table} m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.tenant_id = :tenant_id 
                ORDER BY m.end_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get memberships by user ID
     *
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getMembershipsByUserId($userId, $tenantId) {
        $sql = "SELECT m.* 
                FROM {$this->table} m 
                WHERE m.user_id = :user_id AND m.tenant_id = :tenant_id 
                ORDER BY m.end_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update membership status
     *
     * @param int $id Membership ID
     * @param string $status New status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * Count active memberships by tenant
     *
     * @param int $tenantId Tenant ID
     * @return int
     */
    public function countActiveMemberships($tenantId) {
        $whereClause = 'tenant_id = :tenant_id AND status = :status AND end_date >= CURRENT_DATE';
        $params = [
            'tenant_id' => $tenantId,
            'status' => 'active'
        ];
        
        return $this->count($whereClause, $params);
    }
    
    /**
     * Count expired memberships by tenant
     *
     * @param int $tenantId Tenant ID
     * @return int
     */
    public function countExpiredMemberships($tenantId) {
        $whereClause = 'tenant_id = :tenant_id AND (status = :status OR end_date < CURRENT_DATE)';
        $params = [
            'tenant_id' => $tenantId,
            'status' => 'expired'
        ];
        
        return $this->count($whereClause, $params);
    }
    
    /**
     * Count memberships by type
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function countMembershipsByType($tenantId) {
        $sql = "SELECT type, COUNT(*) as count 
                FROM {$this->table} 
                WHERE tenant_id = :tenant_id 
                GROUP BY type 
                ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
