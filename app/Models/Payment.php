<?php
/**
 * Payment Model
 */
class Payment extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'payments';
    
    /**
     * Get payment by ID with tenant check
     *
     * @param int $id Payment ID
     * @param int|null $tenantId Tenant ID for filtering
     * @return array|false
     */
    public function getPaymentById($id, $tenantId = null) {
        return $this->getById($id, $tenantId);
    }
    
    /**
     * Get payments by tenant ID
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getPaymentsByTenantId($tenantId) {
        $sql = "SELECT p.*, u.name as user_name, 
                COALESCE(m.type, 'N/A') as membership_type 
                FROM {$this->table} p 
                JOIN users u ON p.user_id = u.id 
                LEFT JOIN memberships m ON p.membership_id = m.id 
                WHERE p.tenant_id = :tenant_id 
                ORDER BY p.payment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments by user ID
     *
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getPaymentsByUserId($userId, $tenantId) {
        $sql = "SELECT p.*, COALESCE(m.type, 'N/A') as membership_type 
                FROM {$this->table} p 
                LEFT JOIN memberships m ON p.membership_id = m.id 
                WHERE p.user_id = :user_id AND p.tenant_id = :tenant_id 
                ORDER BY p.payment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent payments
     *
     * @param int $tenantId Tenant ID
     * @param int $limit Maximum number of payments to return
     * @return array
     */
    public function getRecentPayments($tenantId, $limit = 5) {
        $sql = "SELECT p.*, u.name as user_name, 
                COALESCE(m.type, 'N/A') as membership_type 
                FROM {$this->table} p 
                JOIN users u ON p.user_id = u.id 
                LEFT JOIN memberships m ON p.membership_id = m.id 
                WHERE p.tenant_id = :tenant_id 
                ORDER BY p.payment_date DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total revenue for a specific month
     *
     * @param int $tenantId Tenant ID
     * @param int $month Month (1-12)
     * @param int $year Year
     * @return float
     */
    public function getTotalRevenueForMonth($tenantId, $month, $year) {
        $sql = "SELECT SUM(amount) FROM {$this->table} 
                WHERE tenant_id = :tenant_id 
                AND EXTRACT(MONTH FROM payment_date) = :month 
                AND EXTRACT(YEAR FROM payment_date) = :year";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':month', $month, PDO::PARAM_INT);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        
        return (float) $stmt->fetchColumn() ?: 0;
    }
    
    /**
     * Get total revenue within a date range
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return float
     */
    public function getTotalRevenue($tenantId, $startDate, $endDate) {
        $sql = "SELECT SUM(amount) FROM {$this->table} 
                WHERE tenant_id = :tenant_id 
                AND payment_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return (float) $stmt->fetchColumn() ?: 0;
    }
    
    /**
     * Get monthly revenue within a date range
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array
     */
    public function getMonthlyRevenue($tenantId, $startDate, $endDate) {
        $sql = "SELECT 
                TO_CHAR(payment_date, 'YYYY-MM') as month,
                SUM(amount) as total 
                FROM {$this->table} 
                WHERE tenant_id = :tenant_id 
                AND payment_date BETWEEN :start_date AND :end_date 
                GROUP BY month 
                ORDER BY month";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get revenue by payment method
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array
     */
    public function getRevenueByPaymentMethod($tenantId, $startDate, $endDate) {
        $sql = "SELECT 
                payment_method,
                SUM(amount) as total 
                FROM {$this->table} 
                WHERE tenant_id = :tenant_id 
                AND payment_date BETWEEN :start_date AND :end_date 
                GROUP BY payment_method 
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get revenue by membership type
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array
     */
    public function getRevenueByMembershipType($tenantId, $startDate, $endDate) {
        $sql = "SELECT 
                COALESCE(m.type, 'Other') as membership_type,
                SUM(p.amount) as total 
                FROM {$this->table} p 
                LEFT JOIN memberships m ON p.membership_id = m.id 
                WHERE p.tenant_id = :tenant_id 
                AND p.payment_date BETWEEN :start_date AND :end_date 
                GROUP BY membership_type 
                ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

