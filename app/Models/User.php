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
        $sql = "SELECT id, tenant_id, name, email, password, role::text as role, created_at, updated_at 
                FROM {$this->table} WHERE email = :email";
        
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
    
    /**
     * Create a new user
     *
     * @param array $data User data
     * @return int|false The ID of the newly created user, or false on failure
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (tenant_id, name, email, password, role, created_at, updated_at) 
                VALUES (:tenant_id, :name, :email, :password, :role::user_role, NOW(), NOW()) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':tenant_id', $data['tenant_id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindParam(':role', $data['role'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Return the new user ID
            return $stmt->fetchColumn();
        }
        
        return false;
    }
    
    /**
     * Get users without a tenant assigned
     * 
     * @return array
     */
    public function getUsersWithoutTenant() {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id IS NULL ORDER BY name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Assign user to tenant with role
     * 
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @param string $role Role to assign
     * @return bool
     */
    public function assignUserToTenant($userId, $tenantId, $role = 'GYM_ADMIN') {
        $sql = "UPDATE {$this->table} SET tenant_id = :tenant_id, role = :role::user_role 
                WHERE id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Remove user from tenant
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function removeUserFromTenant($userId) {
        $sql = "UPDATE {$this->table} SET tenant_id = NULL, role = 'MEMBER'::user_role 
                WHERE id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Disabilita un utente
     * 
     * @param int $userId ID dell'utente
     * @return bool
     */
    public function disableUser($userId) {
        $sql = "UPDATE {$this->table} SET is_active = false 
                WHERE id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Abilita un utente
     * 
     * @param int $userId ID dell'utente
     * @return bool
     */
    public function enableUser($userId) {
        $sql = "UPDATE {$this->table} SET is_active = true 
                WHERE id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica se un utente è attivo
     * 
     * @param int $userId ID dell'utente
     * @return bool
     */
    public function isUserActive($userId) {
        $sql = "SELECT is_active FROM {$this->table} WHERE id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return (bool)$stmt->fetchColumn();
    }
    
    /**
     * Reimposta la password di un utente
     * 
     * @param int $userId ID dell'utente
     * @param string $newPassword Nuova password (già hashata)
     * @return bool
     */
    public function resetPassword($userId, $newPassword) {
        $sql = "UPDATE {$this->table} SET password = :password 
                WHERE id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':password', $newPassword, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
