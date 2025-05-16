<?php

class PasswordReset extends BaseModel {
    protected $table = 'password_resets';
    
    /**
     * Crea un nuovo token di reset password
     *
     * @param string $email Email dell'utente
     * @param int $tenant_id ID del tenant
     * @return array|bool Dati del token di reset o false in caso di errore
     */
    public function createToken($email, $tenant_id) {
        try {
            // Genera un token unico
            $token = bin2hex(random_bytes(32));
            
            // Calcola la data di scadenza (24 ore)
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Inserisci il token nel database
            $sql = "INSERT INTO {$this->table} (email, token, tenant_id, expires_at, used) 
                    VALUES (:email, :token, :tenant_id, :expires_at, :used)";
            $this->db->query($sql);
            $this->db->bind(':email', $email);
            $this->db->bind(':token', $token);
            $this->db->bind(':tenant_id', $tenant_id);
            $this->db->bind(':expires_at', $expires_at);
            $this->db->bind(':used', false);
            
            $result = $this->db->execute();
            
            if ($result) {
                return [
                    'email' => $email,
                    'token' => $token,
                    'expires_at' => $expires_at
                ];
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Errore nella creazione del token di reset password: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se un token di reset password è valido
     *
     * @param string $token Token da verificare
     * @return array|bool Dati del token se valido, altrimenti false
     */
    public function verifyToken($token) {
        try {
            // Cerca il token nel database
            $sql = "SELECT * FROM {$this->table} WHERE token = :token AND used = false AND expires_at > NOW()";
            $this->db->query($sql);
            $this->db->bind(':token', $token);
            
            $result = $this->db->single();
            
            return $result ?: false;
        } catch (Exception $e) {
            error_log('Errore nella verifica del token di reset password: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Segna un token come utilizzato
     *
     * @param string $token Token da aggiornare
     * @return bool True se l'aggiornamento è riuscito, altrimenti false
     */
    public function markAsUsed($token) {
        try {
            $sql = "UPDATE {$this->table} SET used = true WHERE token = :token";
            $this->db->query($sql);
            $this->db->bind(':token', $token);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Errore nella marcatura del token di reset password: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rimuove i token scaduti
     *
     * @return bool True se la rimozione è riuscita, altrimenti false
     */
    public function removeExpiredTokens() {
        try {
            $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW() OR used = true";
            $this->db->query($sql);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Errore nella rimozione dei token di reset password scaduti: ' . $e->getMessage());
            return false;
        }
    }
}