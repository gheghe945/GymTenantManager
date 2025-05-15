<?php
/**
 * User Profile Model
 */
class UserProfile extends BaseModel {
    /**
     * Nome della tabella
     *
     * @var string
     */
    protected $table = 'user_profiles';
    
    /**
     * Costruttore
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Crea un nuovo profilo utente
     *
     * @param array $data Dati del profilo utente
     * @return int|false ID del profilo creato o false in caso di errore
     */
    public function create($data) {
        try {
            // Prepara la query
            $query = "INSERT INTO {$this->table} 
                      (user_id, phone, birthdate, tax_code, address, city, province, zip, weight, height) 
                      VALUES 
                      (:user_id, :phone, :birthdate, :tax_code, :address, :city, :province, :zip, :weight, :height) 
                      RETURNING id";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':birthdate', $data['birthdate']);
            $stmt->bindParam(':tax_code', $data['tax_code']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':city', $data['city']);
            $stmt->bindParam(':province', $data['province']);
            $stmt->bindParam(':zip', $data['zip']);
            $stmt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
            $stmt->bindParam(':height', $data['height'], PDO::PARAM_STR);
            
            // Esegui la query
            $stmt->execute();
            
            // Ottieni l'ID del profilo appena creato
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nella creazione del profilo utente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ottiene il profilo di un utente per ID utente
     *
     * @param int $user_id ID dell'utente
     * @return array|false Dati del profilo o false se non trovato
     */
    public function getByUserId($user_id) {
        try {
            // Prepara la query
            $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            // Esegui la query
            $stmt->execute();
            
            // Ottieni il risultato
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nel recupero del profilo utente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Aggiorna il profilo di un utente
     *
     * @param array $data Dati del profilo da aggiornare (deve includere 'id')
     * @param int|null $tenantId ID del tenant (non utilizzato per i profili utente)
     * @return bool True se l'aggiornamento è riuscito, altrimenti false
     */
    public function update($data, $tenantId = null) {
        // Verifica che sia presente l'ID
        if (!isset($data['id'])) {
            return false;
        }
        
        try {
            // Recupera l'ID
            $id = $data['id'];
            unset($data['id']);
            
            // Costruisci la parte SET della query
            $set = '';
            foreach ($data as $key => $value) {
                $set .= "{$key} = :{$key}, ";
            }
            $set = rtrim($set, ', ');
            
            // Prepara la query
            $query = "UPDATE {$this->table} SET {$set} WHERE id = :id";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            // Esegui la query
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nell'aggiornamento del profilo utente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Aggiorna il profilo di un utente tramite user_id
     *
     * @param int $user_id ID dell'utente
     * @param array $data Dati del profilo da aggiornare
     * @return bool True se l'aggiornamento è riuscito, altrimenti false
     */
    public function updateByUserId($user_id, $data) {
        try {
            // Prepara la query
            $query = "UPDATE {$this->table} SET 
                      phone = :phone,
                      birthdate = :birthdate,
                      tax_code = :tax_code,
                      address = :address,
                      city = :city,
                      province = :province,
                      zip = :zip,
                      weight = :weight,
                      height = :height
                      WHERE user_id = :user_id";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':birthdate', $data['birthdate']);
            $stmt->bindParam(':tax_code', $data['tax_code']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':city', $data['city']);
            $stmt->bindParam(':province', $data['province']);
            $stmt->bindParam(':zip', $data['zip']);
            $stmt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
            $stmt->bindParam(':height', $data['height'], PDO::PARAM_STR);
            
            // Esegui la query
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nell'aggiornamento del profilo utente: " . $e->getMessage());
            return false;
        }
    }
}