<?php
/**
 * Invite Model
 */
class Invite extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'invites';
    
    /**
     * Crea un nuovo invito
     *
     * @param array $data Dati dell'invito
     * @return int|false L'ID dell'invito creato, o false in caso di errore
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (token, tenant_id, email, expires_at, created_at) 
                VALUES (:token, :tenant_id, :email, :expires_at, NOW()) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':token', $data['token'], PDO::PARAM_STR);
        $stmt->bindParam(':tenant_id', $data['tenant_id'], PDO::PARAM_INT);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':expires_at', $data['expires_at'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Return the new ID
            return $stmt->fetchColumn();
        }
        
        return false;
    }
    
    /**
     * Ottiene un invito tramite token
     *
     * @param string $token Token dell'invito
     * @return array|false
     */
    public function getByToken($token) {
        $sql = "SELECT i.*, t.name as tenant_name, t.subdomain 
                FROM {$this->table} i 
                JOIN tenants t ON i.tenant_id = t.id 
                WHERE i.token = :token";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verifica se un invito è valido
     *
     * @param string $token Token dell'invito
     * @return bool
     */
    public function isValidToken($token) {
        $invite = $this->getByToken($token);
        
        if (!$invite) {
            return false;
        }
        
        // Controlla lo stato e la scadenza
        if ($invite['status'] !== 'pending') {
            return false;
        }
        
        $now = new DateTime();
        $expiry = new DateTime($invite['expires_at']);
        
        return $now < $expiry;
    }
    
    /**
     * Segna un invito come utilizzato
     *
     * @param string $token Token dell'invito
     * @return bool
     */
    public function markAsUsed($token) {
        $sql = "UPDATE {$this->table} SET status = 'used' WHERE token = :token";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica se un invito è già stato inviato all'email
     *
     * @param string $email Email dell'utente
     * @param int $tenantId ID del tenant
     * @return bool
     */
    public function isEmailInvited($email, $tenantId) {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE email = :email AND tenant_id = :tenant_id AND status = 'pending'
                AND expires_at > NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Ottiene tutti gli inviti per un tenant
     *
     * @param int $tenantId ID del tenant
     * @return array
     */
    public function getByTenantId($tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Genera un token univoco
     *
     * @return string
     */
    public function generateToken() {
        return bin2hex(random_bytes(32));
    }
}