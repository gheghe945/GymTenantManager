<?php
/**
 * SMTP Settings Model
 */
class SmtpSetting extends BaseModel {
    /**
     * Nome della tabella
     *
     * @var string
     */
    protected $table = 'smtp_settings';
    
    /**
     * Costruttore
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Ottiene le impostazioni SMTP per un tenant
     *
     * @param int $tenant_id ID del tenant
     * @return array|false Impostazioni SMTP o false se non trovate
     */
    public function getByTenant($tenant_id) {
        try {
            // Prepara la query
            $query = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id";
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
            
            // Esegui la query
            $stmt->execute();
            
            // Ottieni il risultato
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nel recupero delle impostazioni SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salva o aggiorna le impostazioni SMTP per un tenant
     *
     * @param int $tenant_id ID del tenant
     * @param array $data Dati delle impostazioni SMTP
     * @return bool True se l'operazione è riuscita, altrimenti false
     */
    public function saveOrUpdate($tenant_id, $data) {
        try {
            // Verifica se esistono già impostazioni per questo tenant
            $existingSettings = $this->getByTenant($tenant_id);
            
            if ($existingSettings) {
                // Aggiorna le impostazioni esistenti
                $query = "UPDATE {$this->table} SET 
                          smtp_host = :host,
                          smtp_port = :port,
                          smtp_username = :username,
                          smtp_password = :password,
                          smtp_encryption = :encryption,
                          smtp_from_email = :from_email,
                          smtp_from_name = :from_name
                          WHERE tenant_id = :tenant_id";
            } else {
                // Inserisci nuove impostazioni
                $query = "INSERT INTO {$this->table} 
                          (tenant_id, smtp_host, smtp_port, smtp_username, smtp_password, 
                           smtp_encryption, smtp_from_email, smtp_from_name) 
                          VALUES 
                          (:tenant_id, :host, :port, :username, :password, 
                           :encryption, :from_email, :from_name)";
            }
            
            // Prepara lo statement
            $stmt = $this->db->prepare($query);
            
            // Bind dei parametri
            $stmt->bindParam(':tenant_id', $tenant_id, PDO::PARAM_INT);
            $stmt->bindParam(':host', $data['smtp_host']);
            $stmt->bindParam(':port', $data['smtp_port'], PDO::PARAM_INT);
            $stmt->bindParam(':username', $data['smtp_username']);
            $stmt->bindParam(':password', $data['smtp_password']);
            $stmt->bindParam(':encryption', $data['smtp_encryption']);
            $stmt->bindParam(':from_email', $data['smtp_from_email']);
            $stmt->bindParam(':from_name', $data['smtp_from_name']);
            
            // Esegui la query
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log dell'errore
            error_log("Errore nel salvataggio delle impostazioni SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se le impostazioni SMTP sono configurate per un tenant
     *
     * @param int $tenant_id ID del tenant
     * @return bool True se le impostazioni SMTP sono configurate, altrimenti false
     */
    public function isConfigured($tenant_id) {
        $settings = $this->getByTenant($tenant_id);
        
        if (!$settings) {
            return false;
        }
        
        // Verifica che siano impostati i campi obbligatori
        return !empty($settings['smtp_host']) && 
               !empty($settings['smtp_port']) && 
               !empty($settings['smtp_from_email']);
    }
    
    /**
     * Configura PHPMailer con le impostazioni SMTP di un tenant
     *
     * @param PHPMailer $mailer Istanza di PHPMailer
     * @param int $tenant_id ID del tenant
     * @return bool True se la configurazione è riuscita, altrimenti false
     */
    public function configurePHPMailer($mailer, $tenant_id) {
        $settings = $this->getByTenant($tenant_id);
        
        if (!$settings || !$this->isConfigured($tenant_id)) {
            return false;
        }
        
        try {
            // Configura il mailer con SMTP
            $mailer->isSMTP();
            $mailer->Host = $settings['smtp_host'];
            $mailer->Port = $settings['smtp_port'];
            
            // Autenticazione SMTP se sono forniti username e password
            if (!empty($settings['smtp_username'])) {
                $mailer->SMTPAuth = true;
                $mailer->Username = $settings['smtp_username'];
                $mailer->Password = $settings['smtp_password'];
            } else {
                $mailer->SMTPAuth = false;
            }
            
            // Crittografia SMTP
            if (!empty($settings['smtp_encryption'])) {
                $mailer->SMTPSecure = $settings['smtp_encryption'];
            }
            
            // Imposta mittente
            $mailer->setFrom(
                $settings['smtp_from_email'], 
                !empty($settings['smtp_from_name']) ? $settings['smtp_from_name'] : ''
            );
            
            return true;
        } catch (Exception $e) {
            error_log("Errore nella configurazione di PHPMailer: " . $e->getMessage());
            return false;
        }
    }
}