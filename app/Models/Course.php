<?php
/**
 * Course Model
 */
class Course extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'courses';
    
    /**
     * Get course by ID with tenant check
     *
     * @param int $id Course ID
     * @param int|null $tenantId Tenant ID for filtering
     * @return array|false
     */
    public function getCourseById($id, $tenantId = null) {
        return $this->getById($id, $tenantId);
    }
    
    /**
     * Get courses by tenant ID
     *
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getCoursesByTenantId($tenantId) {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM course_users cu WHERE cu.course_id = c.id) AS enrolled_count 
                FROM {$this->table} c 
                WHERE c.tenant_id = :tenant_id 
                ORDER BY c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get courses by user ID
     *
     * @param int $userId User ID
     * @param int $tenantId Tenant ID
     * @return array
     */
    public function getCoursesByUserId($userId, $tenantId) {
        $sql = "SELECT c.* FROM {$this->table} c 
                JOIN course_users cu ON c.id = cu.course_id 
                WHERE cu.user_id = :user_id AND c.tenant_id = :tenant_id 
                ORDER BY c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count courses by tenant
     *
     * @param int $tenantId Tenant ID
     * @return int
     */
    public function countCourses($tenantId) {
        $whereClause = 'tenant_id = :tenant_id';
        $params = ['tenant_id' => $tenantId];
        
        return $this->count($whereClause, $params);
    }
    
    /**
     * Add user to course
     *
     * @param int $courseId Course ID
     * @param int $userId User ID
     * @return bool
     */
    public function addUserToCourse($courseId, $userId) {
        $sql = "INSERT INTO course_users (course_id, user_id) VALUES (:course_id, :user_id)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Remove user from course
     *
     * @param int $courseId Course ID
     * @param int $userId User ID
     * @return bool
     */
    public function removeUserFromCourse($courseId, $userId) {
        $sql = "DELETE FROM course_users WHERE course_id = :course_id AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
