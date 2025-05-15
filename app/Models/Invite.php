<?php
/**
 * Invite Model
 */
class Invite extends BaseModel {
    /**
     * Nome della tabella
     *
     * @var string
     */
    protected $table = 'invites';
    
    /**
     * Costruttore
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Crea un nuovo invito
     *
     * @param array $data Dati dell'invito (email, tenant_id)
     * @return array|false Dati dell'invito creato o false in caso di errore
     */
    public function create($data) {
        if (isset($data['email']) && isset($data['tenant_id'])) {
            return $this->createInvite($data['email'], $data['tenant_id']);
        }
        return false;
    }
    
    /**
     * Crea un nuovo invito (metodo specifico)
     *
     * @param string $email Email dell'utente da invitare
     * @param int $tenant_id ID del tenant
     * @return array|false Dati dell'invito creato o false in caso di errore
     */
    public function createInvite($email, $tenant_id) {
        try {
            // Genera un token casuale
            $token = bin2hex(random_bytes(16));
            
            // Calcola la data di scadenza (48 ore da ora)
            $expires_at = date('Y-m-d H:i:s', strtotime('+48 hours'));
            
            // Prepara la query
            $query = "INSERT INTO {$this->table} (email, token, tenant_id, status, expires_at) 
                      VALUES (:email, :token, :tenant_id, 'pending', :expires_at) 
                      RETURNING id, email, token, tenant_id, status, expires_at, created_at";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
            $stmt->bindParam(':expires_at', $expires_at);
            
            // Esegui la query
            $stmt->execute();
            
            // Ottieni i dati dell'invito appena creato
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nella creazione dell'invito: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ottiene tutti gli inviti per un tenant specifico
     *
     * @param int $tenant_id ID del tenant
     * @return array Elenco degli inviti
     */
    public function getAllByTenant($tenant_id) {
        try {
            // Prepara la query
            $query = "SELECT * FROM {$this->table} 
                      WHERE tenant_id = :tenant_id 
                      ORDER BY created_at DESC";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
            
            // Esegui la query
            $stmt->execute();
            
            // Ottieni i risultati
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nel recupero degli inviti: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ottiene un invito per token
     *
     * @param string $token Token dell'invito
     * @return array|false Dati dell'invito o false se non trovato
     */
    public function getByToken($token) {
        try {
            // Prepara la query
            $query = "SELECT i.*, t.name as tenant_name 
                      FROM {$this->table} i
                      JOIN tenants t ON i.tenant_id = t.id
                      WHERE i.token = :token";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':token', $token);
            
            // Esegui la query
            $stmt->execute();
            
            // Ottieni il risultato
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nel recupero dell'invito per token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Aggiorna lo stato di un invito
     *
     * @param int $id ID dell'invito
     * @param string $status Nuovo stato dell'invito ('pending', 'used', 'expired')
     * @return bool True se l'aggiornamento è riuscito, altrimenti false
     */
    public function updateStatus($id, $status) {
        try {
            // Prepara la query
            $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);
            
            // Esegui la query
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nell'aggiornamento dello stato dell'invito: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se un invito è valido
     *
     * @param array $invite Dati dell'invito
     * @return bool True se l'invito è valido, altrimenti false
     */
    public function isValid($invite) {
        if (!$invite) {
            return false;
        }
        
        // Verifica lo stato
        if ($invite['status'] !== 'pending') {
            return false;
        }
        
        // Verifica la scadenza
        $expires_at = strtotime($invite['expires_at']);
        $now = time();
        
        if ($now > $expires_at) {
            // Aggiorna lo stato a 'expired'
            $this->updateStatus($invite['id'], 'expired');
            return false;
        }
        
        return true;
    }
}