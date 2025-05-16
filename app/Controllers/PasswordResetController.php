<?php

/**
 * Controller per il recupero password
 */
class PasswordResetController extends BaseController {
    
    private $userModel;
    private $resetModel;
    private $smtpModel;
    
    /**
     * Costruttore
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        // Connessione al database
        $this->db = Database::getInstance();
        // Carica i modelli
        $this->userModel = new User();
        $this->resetModel = new PasswordReset();
        $this->smtpModel = new SmtpSetting();
    }
    
    /**
     * Index - Mostra il form per richiedere il reset della password
     *
     * @return void
     */
    public function index() {
        // Se l'utente è già loggato, reindirizza alla dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        $data = [];
        
        $this->render('password_reset/request', $data);
    }
    
    /**
     * Request - Gestisce la richiesta di reset della password
     *
     * @return void
     */
    public function request() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login');
            return;
        }
        
        // Ottieni l'email dal form
        $email = trim($_POST['email'] ?? '');
        
        // Validazione
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('password_reset_message', 'Inserisci un indirizzo email valido', 'alert alert-danger');
            redirect('password/reset');
            return;
        }
        
        // Identifica il tenant dell'utente
        $tenant_id = $this->findUserTenant($email);
        
        if (!$tenant_id) {
            // Per sicurezza, non rivelare se l'email esiste o meno
            flash('password_reset_message', 'Se l\'indirizzo email esiste nel sistema, riceverai istruzioni per reimpostare la password.', 'alert alert-info');
            redirect('password/reset');
            return;
        }
        
        // Crea un token di reset password
        $resetToken = $this->resetModel->createToken($email, $tenant_id);
        
        if (!$resetToken) {
            error_log('Errore nel creare il token per l\'utente: ' . $email . ' (tenant_id: ' . $tenant_id . ')');
            flash('password_reset_message', 'Si è verificato un errore tecnico. I nostri amministratori sono stati informati.', 'alert alert-danger');
            redirect('password/reset');
            return;
        }
        
        // Verifica se SMTP è configurato per il tenant dell'utente
        $smtpConfigured = $this->smtpModel->isConfigured($tenant_id);
        
        // Se SMTP è configurato per il tenant, invia l'email
        if ($smtpConfigured) {
            $emailSent = $this->sendResetEmail($resetToken, $tenant_id);
            
            if (!$emailSent) {
                error_log('Errore nell\'invio dell\'email di reset per ' . $email . ' (tenant_id: ' . $tenant_id . ')');
                flash('password_reset_message', 'Si è verificato un errore nell\'invio dell\'email. Controlla con l\'amministratore la configurazione SMTP.', 'alert alert-danger');
                redirect('password/reset');
                return;
            }
        } else {
            // Verifica se esiste una configurazione SMTP globale (SUPER_ADMIN)
            $globalSmtpConfigured = $this->smtpModel->isConfigured(1); // Assumiamo che 1 sia il tenant del SUPER_ADMIN
            
            if ($globalSmtpConfigured) {
                // Prova ad inviare usando la configurazione SMTP globale
                $emailSent = $this->sendResetEmail($resetToken, 1);
                
                if (!$emailSent) {
                    error_log('Errore nell\'invio dell\'email di reset usando SMTP globale per ' . $email);
                    flash('password_reset_message', 'Si è verificato un errore nell\'invio dell\'email. Contatta l\'amministratore di sistema.', 'alert alert-danger');
                    redirect('password/reset');
                    return;
                }
            } else {
                // Se nessuna configurazione SMTP è disponibile, mostra un link diretto (solo in ambiente di sviluppo)
                error_log('Nessuna configurazione SMTP disponibile per ' . $email . ' (tenant_id: ' . $tenant_id . ')');
                flash('password_reset_message', 'Il sistema di email non è configurato correttamente. È stato creato un link di recupero temporaneo: <a href="' . URLROOT . '/password/reset/confirm/' . $resetToken['token'] . '">' . URLROOT . '/password/reset/confirm/' . $resetToken['token'] . '</a>', 'alert alert-warning');
                redirect('password/reset');
                return;
            }
        }
        
        // Messaggio di successo
        flash('password_reset_message', 'Abbiamo inviato un\'email con le istruzioni per reimpostare la password. Controlla la tua casella di posta.', 'alert alert-success');
        redirect('password/reset');
    }
    
    /**
     * Identifica il tenant_id associato all'utente con una determinata email
     *
     * @param string $email Email dell'utente
     * @return int|bool ID del tenant o false se l'utente non esiste
     */
    private function findUserTenant($email) {
        try {
            // Query per trovare il tenant_id dell'utente
            $sql = "SELECT tenant_id FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['tenant_id'])) {
                return $result['tenant_id'];
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Errore nel trovare il tenant per l\'utente ' . $email . ': ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Confirm - Mostra il form per reimpostare la password
     *
     * @param string $token Token di reset
     * @return void
     */
    public function confirm($token = '') {
        // Se l'utente è già loggato, reindirizza alla dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        // Verifica se il token esiste
        if (empty($token)) {
            flash('login_message', 'Token di reset non valido o scaduto', 'alert alert-danger');
            redirect('login');
            return;
        }
        
        // Verifica se il token è valido
        $resetData = $this->resetModel->verifyToken($token);
        
        if (!$resetData) {
            flash('login_message', 'Token di reset non valido o scaduto', 'alert alert-danger');
            redirect('login');
            return;
        }
        
        $data = [
            'token' => $token,
            'email' => $resetData['email']
        ];
        
        $this->render('password_reset/reset', $data);
    }
    
    /**
     * Reset - Reimpostazione della password
     *
     * @return void
     */
    public function reset() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login');
            return;
        }
        
        // Ottieni i dati dal form
        $token = trim($_POST['token'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validazione
        if (empty($token) || empty($email) || empty($password) || empty($confirm_password)) {
            flash('reset_message', 'Tutti i campi sono obbligatori', 'alert alert-danger');
            redirect('password/reset/confirm/' . $token);
            return;
        }
        
        if ($password !== $confirm_password) {
            flash('reset_message', 'Le password non corrispondono', 'alert alert-danger');
            redirect('password/reset/confirm/' . $token);
            return;
        }
        
        if (strlen($password) < 6) {
            flash('reset_message', 'La password deve essere lunga almeno 6 caratteri', 'alert alert-danger');
            redirect('password/reset/confirm/' . $token);
            return;
        }
        
        // Verifica se il token è valido
        $resetData = $this->resetModel->verifyToken($token);
        
        if (!$resetData) {
            flash('login_message', 'Token di reset non valido o scaduto', 'alert alert-danger');
            redirect('login');
            return;
        }
        
        // Verifica che l'email corrisponda
        if ($resetData['email'] !== $email) {
            flash('login_message', 'Email non valida', 'alert alert-danger');
            redirect('login');
            return;
        }
        
        // Identifica il tenant dal token
        $tenant_id = $resetData['tenant_id'];
        
        // Aggiorna la password dell'utente
        $updated = $this->userModel->updatePassword($email, $password, $tenant_id);
        
        if (!$updated) {
            flash('reset_message', 'Si è verificato un errore nell\'aggiornamento della password. Riprova.', 'alert alert-danger');
            redirect('password/reset/confirm/' . $token);
            return;
        }
        
        // Segna il token come utilizzato
        $this->resetModel->markAsUsed($token);
        
        // Pulisci i token scaduti
        $this->resetModel->removeExpiredTokens();
        
        // Messaggio di successo
        flash('login_message', 'Password reimpostata con successo! Ora puoi accedere con la tua nuova password.', 'alert alert-success');
        redirect('login');
    }
    
    /**
     * Invia l'email di reset password
     *
     * @param array $resetToken Dati del token di reset
     * @param int $tenant_id ID del tenant
     * @return bool True se l'invio è riuscito, altrimenti false
     */
    private function sendResetEmail($resetToken, $tenant_id) {
        try {
            // Carica la classe PHPMailer
            require_once APP_ROOT . '/vendor/autoload.php';
            
            // Crea una nuova istanza di PHPMailer
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configura PHPMailer con le impostazioni SMTP
            if (!$this->smtpModel->configurePHPMailer($mail, $tenant_id)) {
                return false;
            }
            
            // Ottieni il nome del tenant
            $tenant = $this->db->query("SELECT name FROM tenants WHERE id = " . $tenant_id)->fetch(PDO::FETCH_ASSOC);
            $tenant_name = $tenant ? $tenant['name'] : 'Gestione Palestre';
            
            // Imposta il destinatario
            $mail->addAddress($resetToken['email']);
            
            // Imposta l'oggetto e il corpo del messaggio
            $mail->Subject = 'Reimposta la tua password su ' . $tenant_name;
            
            // Costruisci l'URL completo di reset
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $reset_url = $protocol . $domainName . URLROOT . '/password/reset/confirm/' . $resetToken['token'];
            
            // Corpo HTML
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Reimposta la tua password su {$tenant_name}</h2>
                <p>Abbiamo ricevuto una richiesta per reimpostare la tua password. Se non hai richiesto questo, puoi ignorare questa email.</p>
                <p>Per reimpostare la tua password, clicca sul seguente link:</p>
                <p><a href='{$reset_url}'>{$reset_url}</a></p>
                <p>Questo link scadrà tra 24 ore.</p>
            ";
            
            // Corpo alternativo in testo
            $mail->AltBody = "
                Reimposta la tua password su {$tenant_name}
                
                Abbiamo ricevuto una richiesta per reimpostare la tua password. Se non hai richiesto questo, puoi ignorare questa email.
                
                Per reimpostare la tua password, visita il seguente link:
                {$reset_url}
                
                Questo link scadrà tra 24 ore.
            ";
            
            // Invia l'email
            if ($mail->send()) {
                return true;
            } else {
                error_log("Errore nell'invio dell'email di reset password: " . $mail->ErrorInfo);
                return false;
            }
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("Errore nell'invio dell'email di reset password: " . $e->getMessage());
            return false;
        }
    }
}