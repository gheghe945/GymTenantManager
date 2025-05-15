<?php
/**
 * Profilo Utente Model
 */
class UserProfile extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'user_profiles';
    
    /**
     * Ottiene un profilo utente tramite ID utente
     *
     * @param int $userId ID dell'utente
     * @return array|false
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea un nuovo profilo utente
     *
     * @param array $data Dati del profilo
     * @return int|false L'ID del profilo creato, o false in caso di errore
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, lastname, phone, birthdate, tax_code, address, city, zip, province, weight, height, created_at, updated_at) 
                VALUES (:user_id, :lastname, :phone, :birthdate, :tax_code, :address, :city, :zip, :province, :weight, :height, NOW(), NOW()) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':lastname', $data['lastname'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':birthdate', $data['birthdate'], PDO::PARAM_STR);
        $stmt->bindParam(':tax_code', $data['tax_code'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':city', $data['city'], PDO::PARAM_STR);
        $stmt->bindParam(':zip', $data['zip'], PDO::PARAM_STR);
        $stmt->bindParam(':province', $data['province'], PDO::PARAM_STR);
        $stmt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
        $stmt->bindParam(':height', $data['height'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // Return the new ID
            return $stmt->fetchColumn();
        }
        
        return false;
    }
    
    /**
     * Aggiorna un profilo utente
     *
     * @param array $data Dati del profilo
     * @return bool
     */
    public function update($data) {
        $sql = "UPDATE {$this->table} SET 
                lastname = :lastname, 
                phone = :phone, 
                birthdate = :birthdate, 
                tax_code = :tax_code, 
                address = :address, 
                city = :city, 
                zip = :zip, 
                province = :province, 
                weight = :weight, 
                height = :height, 
                updated_at = NOW() 
                WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':lastname', $data['lastname'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':birthdate', $data['birthdate'], PDO::PARAM_STR);
        $stmt->bindParam(':tax_code', $data['tax_code'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':city', $data['city'], PDO::PARAM_STR);
        $stmt->bindParam(':zip', $data['zip'], PDO::PARAM_STR);
        $stmt->bindParam(':province', $data['province'], PDO::PARAM_STR);
        $stmt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
        $stmt->bindParam(':height', $data['height'], PDO::PARAM_STR);
        
        return $stmt->execute();
    }
}