<?php

/**
 * Modello per le impostazioni SMTP globali (SUPER_ADMIN)
 */
class GlobalSmtpSetting extends BaseModel {
    protected $table = 'global_smtp_settings';
    
    /**
     * Costruttore
     */
    public function __construct() {
        parent::__construct();
        $this->createTableIfNotExists();
    }
    
    /**
     * Crea la tabella se non esiste
     */
    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id SERIAL PRIMARY KEY,
            host VARCHAR(255) NOT NULL,
            port INT NOT NULL,
            username VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            sender_name VARCHAR(255) NOT NULL,
            sender_email VARCHAR(255) NOT NULL,
            encryption VARCHAR(10) DEFAULT 'tls',
            active BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->db->exec($sql);
            
            // Verifica se esiste già una configurazione
            $count = $this->db->query("SELECT COUNT(*) as count FROM {$this->table}")->fetch(PDO::FETCH_ASSOC);
            
            // Se non esiste, crea una configurazione vuota
            if ($count['count'] == 0) {
                $this->db->query("INSERT INTO {$this->table} (host, port, username, password, sender_name, sender_email, encryption, active) 
                              VALUES ('', 587, '', '', 'GymManager', '', 'tls', FALSE)");
            }
        } catch (PDOException $e) {
            error_log('Errore nella creazione della tabella global_smtp_settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Ottiene le impostazioni SMTP globali
     *
     * @return array
     */
    public function get() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC LIMIT 1";
        
        try {
            return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Errore nel recupero delle impostazioni SMTP globali: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salva le impostazioni SMTP globali
     *
     * @param array $data Dati delle impostazioni SMTP
     * @return bool
     */
    public function save($data) {
        $sql = "UPDATE {$this->table} SET 
                host = :host,
                port = :port,
                username = :username,
                password = :password,
                sender_name = :sender_name,
                sender_email = :sender_email,
                encryption = :encryption,
                active = :active,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':host', $data['host'], PDO::PARAM_STR);
            $stmt->bindParam(':port', $data['port'], PDO::PARAM_INT);
            $stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $data['password'], PDO::PARAM_STR);
            $stmt->bindParam(':sender_name', $data['sender_name'], PDO::PARAM_STR);
            $stmt->bindParam(':sender_email', $data['sender_email'], PDO::PARAM_STR);
            $stmt->bindParam(':encryption', $data['encryption'], PDO::PARAM_STR);
            $stmt->bindParam(':active', $data['active'], PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Errore nel salvataggio delle impostazioni SMTP globali: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se le impostazioni SMTP globali sono configurate e attive
     *
     * @return bool
     */
    public function isConfigured() {
        $settings = $this->get();
        
        if (!$settings) {
            return false;
        }
        
        return !empty($settings['host']) && 
               !empty($settings['username']) && 
               !empty($settings['password']) && 
               !empty($settings['sender_email']) &&
               $settings['active'] == true;
    }
    
    /**
     * Configura PHPMailer con le impostazioni SMTP globali
     *
     * @param \PHPMailer\PHPMailer\PHPMailer $mail Istanza di PHPMailer
     * @return bool
     */
    public function configurePHPMailer($mail) {
        $settings = $this->get();
        
        if (!$settings || !$this->isConfigured()) {
            return false;
        }
        
        try {
            // Imposta il sistema per usare SMTP
            $mail->isSMTP();
            
            // Imposta il server SMTP
            $mail->Host = $settings['host'];
            
            // Imposta il numero di porta SMTP
            $mail->Port = $settings['port'];
            
            // Imposta SMTP authentication
            $mail->SMTPAuth = true;
            
            // Username per l'autenticazione
            $mail->Username = $settings['username'];
            
            // Password per l'autenticazione
            $mail->Password = $settings['password'];
            
            // Tipo di crittografia (TLS o SSL)
            $mail->SMTPSecure = $settings['encryption'];
            
            // Imposta mittente
            $mail->setFrom($settings['sender_email'], $settings['sender_name']);
            
            // Imposta la codifica dei caratteri
            $mail->CharSet = 'UTF-8';
            
            return true;
        } catch (Exception $e) {
            error_log('Errore nella configurazione di PHPMailer con le impostazioni SMTP globali: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Testa la connessione SMTP
     *
     * @param array $settings Impostazioni SMTP da testare
     * @return array Stato e messaggio del test
     */
    public function testConnection($settings) {
        // Carica PHPMailer
        require_once APP_ROOT . '/vendor/autoload.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configura PHPMailer
            $mail->isSMTP();
            $mail->Host = $settings['host'];
            $mail->Port = $settings['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $settings['username'];
            $mail->Password = $settings['password'];
            $mail->SMTPSecure = $settings['encryption'];
            
            // Tenta la connessione al server SMTP
            $mail->smtpConnect();
            
            return [
                'success' => true,
                'message' => 'Connessione al server SMTP stabilita con successo!'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Errore di connessione: ' . $e->getMessage()
            ];
        }
    }
}