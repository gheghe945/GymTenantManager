<?php
/**
 * SMTP Settings Model
 */
class SmtpSetting extends BaseModel {
    /**
     * The table name in the database
     *
     * @var string
     */
    protected $table = 'smtp_settings';
    
    /**
     * Ottiene le impostazioni SMTP per un tenant
     *
     * @param int $tenantId ID del tenant
     * @return array|false
     */
    public function getByTenantId($tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea o aggiorna le impostazioni SMTP per un tenant
     *
     * @param array $data Dati delle impostazioni SMTP
     * @return bool
     */
    public function saveSettings($data) {
        // Controlla se esistono già impostazioni per questo tenant
        $existingSettings = $this->getByTenantId($data['tenant_id']);
        
        if ($existingSettings) {
            // Aggiorna le impostazioni esistenti
            return $this->update($data);
        } else {
            // Crea nuove impostazioni
            return $this->create($data) ? true : false;
        }
    }
    
    /**
     * Crea nuove impostazioni SMTP
     *
     * @param array $data Dati delle impostazioni SMTP
     * @return int|false L'ID delle impostazioni create, o false in caso di errore
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (tenant_id, host, port, username, password, sender_name, sender_email, encryption, active, created_at, updated_at) 
                VALUES (:tenant_id, :host, :port, :username, :password, :sender_name, :sender_email, :encryption, :active, NOW(), NOW()) RETURNING id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':tenant_id', $data['tenant_id'], PDO::PARAM_INT);
        $stmt->bindParam(':host', $data['host'], PDO::PARAM_STR);
        $stmt->bindParam(':port', $data['port'], PDO::PARAM_INT);
        $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindParam(':sender_name', $data['sender_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sender_email', $data['sender_email'], PDO::PARAM_STR);
        $stmt->bindParam(':encryption', $data['encryption'], PDO::PARAM_STR);
        $stmt->bindParam(':active', $data['active'], PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            // Return the new ID
            return $stmt->fetchColumn();
        }
        
        return false;
    }
    
    /**
     * Aggiorna le impostazioni SMTP
     *
     * @param array $data Dati delle impostazioni SMTP
     * @return bool
     */
    public function update($data) {
        $sql = "UPDATE {$this->table} SET 
                host = :host, 
                port = :port, 
                username = :username, 
                password = :password, 
                sender_name = :sender_name, 
                sender_email = :sender_email, 
                encryption = :encryption, 
                active = :active, 
                updated_at = NOW() 
                WHERE tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':tenant_id', $data['tenant_id'], PDO::PARAM_INT);
        $stmt->bindParam(':host', $data['host'], PDO::PARAM_STR);
        $stmt->bindParam(':port', $data['port'], PDO::PARAM_INT);
        $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindParam(':sender_name', $data['sender_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sender_email', $data['sender_email'], PDO::PARAM_STR);
        $stmt->bindParam(':encryption', $data['encryption'], PDO::PARAM_STR);
        $stmt->bindParam(':active', $data['active'], PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica se le impostazioni SMTP sono corrette
     *
     * @param array $data Dati delle impostazioni SMTP
     * @return array Risultato della verifica [success: bool, message: string]
     */
    public function testConnection($data) {
        try {
            // Creiamo una nuova istanza di PHPMailer
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $data['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $data['username'];
            $mail->Password = $data['password'];
            $mail->Port = $data['port'];
            
            // Impostazioni di sicurezza
            switch ($data['encryption']) {
                case 'tls':
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    break;
                case 'ssl':
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                    break;
                default:
                    $mail->SMTPSecure = '';
                    $mail->SMTPAutoTLS = false;
            }
            
            // Imposta il timeout
            $mail->Timeout = 10;
            
            // Proviamo a connetterci al server SMTP per verificare le credenziali
            // Cattura l'output di debug
            ob_start();
            $mail->SMTPDebug = 2; // Modalità di debug dettagliata
            
            // Imposta mittente e destinatario (necessari per eseguire il comando MAIL FROM)
            $mail->setFrom($data['sender_email'], $data['sender_name']);
            $mail->addAddress($data['sender_email']); // Invia a sé stesso per il test
            
            // Testa la connessione senza inviare la mail
            $mail->preSend();
            $debug = ob_get_clean();
            
            return ['success' => true, 'message' => 'Connessione SMTP riuscita.'];
        } catch (Exception $e) {
            $debug = ob_get_clean();
            return ['success' => false, 'message' => 'Errore: ' . $e->getMessage()];
        }
    }
}