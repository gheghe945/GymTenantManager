<?php
/**
 * Attendance Model
 */
class Attendance extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'attendance';
    
    /**
     * Get attendance by ID with tenant check
     *
     * @param int $id Attendance ID
     * @param int|null $tenantId Tenant ID for filtering
     * @return array|false
     */
    public function getAttendanceById($id, $tenantId = null) {
        return $this->getById($id, $tenantId);
    }
    
    /**
     * Get attendance by tenant ID
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getAttendanceByTenantId($tenantId) {
        $sql = "SELECT a.*, u.name as user_name, c.name as course_name 
                FROM {$this->table} a 
                JOIN users u ON a.user_id = u.id 
                LEFT JOIN courses c ON a.course_id = c.id 
                WHERE a.tenant_id = :tenant_id 
                ORDER BY a.date DESC, a.time_in DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get attendance by user ID
     *
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getAttendanceByUserId($userId, $tenantId) {
        $sql = "SELECT a.*, c.name as course_name 
                FROM {$this->table} a 
                LEFT JOIN courses c ON a.course_id = c.id 
                WHERE a.user_id = :user_id AND a.tenant_id = :tenant_id 
                ORDER BY a.date DESC, a.time_in DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent attendance by user ID
     *
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @param int $limit Maximum number of records to return
     * @return array
     */
    public function getRecentAttendanceByUserId($userId, $tenantId, $limit = 10) {
        $sql = "SELECT a.*, c.name as course_name 
                FROM {$this->table} a 
                LEFT JOIN courses c ON a.course_id = c.id 
                WHERE a.user_id = :user_id AND a.tenant_id = :tenant_id 
                ORDER BY a.date DESC, a.time_in DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count attendance with optional date range
     *
     * @param int $tenantId Tenant ID
     * @param string|null $startDate Start date (YYYY-MM-DD)
     * @param string|null $endDate End date (YYYY-MM-DD)
     * @return int
     */
    public function countAttendance($tenantId, $startDate = null, $endDate = null) {
        $whereClause = 'tenant_id = :tenant_id';
        $params = ['tenant_id' => $tenantId];
        
        if ($startDate && $endDate) {
            $whereClause .= ' AND date BETWEEN :start_date AND :end_date';
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->count($whereClause, $params);
    }
    
    /**
     * Count attendance for a specific day
     *
     * @param int $tenantId Tenant ID
     * @param string $date Date (YYYY-MM-DD)
     * @return int
     */
    public function countAttendanceForDay($tenantId, $date) {
        $whereClause = 'tenant_id = :tenant_id AND date = :date';
        $params = [
            'tenant_id' => $tenantId,
            'date' => $date
        ];
        
        return $this->count($whereClause, $params);
    }
    
    /**
     * Get daily attendance counts for a date range
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array
     */
    public function getDailyAttendanceCounts($tenantId, $startDate, $endDate) {
        $sql = "SELECT date, COUNT(*) as count 
                FROM {$this->table} 
                WHERE tenant_id = :tenant_id AND date BETWEEN :start_date AND :end_date 
                GROUP BY date 
                ORDER BY date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get attendance counts by day of week
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array
     */
    public function getAttendanceByDayOfWeek($tenantId, $startDate, $endDate) {
        $sql = "SELECT TO_CHAR(date, 'Day') as day_name, COUNT(*) as count 
                FROM {$this->table} 
                WHERE tenant_id = :tenant_id AND date BETWEEN :start_date AND :end_date 
                GROUP BY day_name 
                ORDER BY EXTRACT(DOW FROM date)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get attendance counts by course
     *
     * @param int $tenantId Tenant ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array
     */
    public function getAttendanceCountByCourse($tenantId, $startDate, $endDate) {
        $sql = "SELECT c.name as course_name, COUNT(a.id) as count 
                FROM {$this->table} a 
                JOIN courses c ON a.course_id = c.id 
                WHERE a.tenant_id = :tenant_id AND a.date BETWEEN :start_date AND :end_date 
                GROUP BY c.name 
                ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Ottieni tutte le presenze in un intervallo di date
     *
     * @param int $tenantId ID del tenant
     * @param string $startDate Data di inizio (YYYY-MM-DD)
     * @param string $endDate Data di fine (YYYY-MM-DD)
     * @return array
     */
    public function getAttendanceByDateRange($tenantId, $startDate, $endDate) {
        $sql = "SELECT a.*, u.name as user_name, c.name as course_name 
                FROM {$this->table} a 
                JOIN users u ON a.user_id = u.id 
                LEFT JOIN courses c ON a.course_id = c.id 
                WHERE a.tenant_id = :tenant_id AND a.date BETWEEN :start_date AND :end_date 
                ORDER BY a.date, a.time_in";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if user has already checked in for the day
     *
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @param string $date Date (YYYY-MM-DD)
     * @return bool
     */
    public function hasUserCheckedInToday($userId, $tenantId, $date) {
        $whereClause = 'user_id = :user_id AND tenant_id = :tenant_id AND date = :date';
        $params = [
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'date' => $date
        ];
        
        return $this->count($whereClause, $params) > 0;
    }
}

