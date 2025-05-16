<?php
// Carica l'autoloader di Composer
require_once APP_ROOT . '/vendor/autoload.php';

// Importa la classe PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Invite Controller
 */
class InviteController extends BaseController {
    /**
     * Invite model
     *
     * @var Invite
     */
    private $inviteModel;
    
    /**
     * SMTP Settings model
     *
     * @var SmtpSetting
     */
    private $smtpModel;
    
    /**
     * User model
     *
     * @var User
     */
    private $userModel;
    
    /**
     * User Profile model
     *
     * @var UserProfile
     */
    private $profileModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Inizializza i modelli
        $this->inviteModel = new Invite();
        $this->smtpModel = new SmtpSetting();
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        
        // Verifica che l'utente sia autenticato e sia GYM_ADMIN per determinate azioni
        $restricted_methods = ['index', 'send'];
        
        $current_method = $_GET['method'] ?? 'index';
        
        if (in_array($current_method, $restricted_methods) && (!isLoggedIn() || !hasRole('GYM_ADMIN'))) {
            redirect('dashboard');
        }
    }
    
    /**
     * Index - Mostra la pagina degli inviti
     *
     * @return void
     */
    public function index() {
        // Ottieni il tenant_id dalla sessione
        $tenant_id = $_SESSION['tenant_id'];
        
        // Verifica se SMTP è configurato
        $smtpConfigured = $this->smtpModel->isConfigured($tenant_id);
        
        // Ottieni tutti gli inviti per il tenant
        $invites = $this->inviteModel->getAllByTenant($tenant_id);
        
        // Imposta i dati per la vista
        $data = [
            'invites' => $invites,
            'smtpConfigured' => $smtpConfigured
        ];
        
        // Renderizza la vista
        $this->render('invites/index', $data);
    }
    
    /**
     * Send - Invia un invito
     *
     * @return void
     */
    public function send() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('invites');
        }
        
        // Ottieni il tenant_id dalla sessione
        $tenant_id = $_SESSION['tenant_id'];
        
        // Ottieni dati dal form
        $email = trim($_POST['email'] ?? '');
        $sendEmail = isset($_POST['send_email']) ? true : false;
        $name = trim($_POST['name'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $role = trim($_POST['role'] ?? 'MEMBER');
        $manualRegistration = isset($_POST['manual_registration']) ? true : false;
        
        // Validazione
        if (empty($email)) {
            flash('invite_message', 'Inserisci un indirizzo email valido', 'alert alert-danger');
            redirect('invites');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('invite_message', 'Inserisci un indirizzo email valido', 'alert alert-danger');
            redirect('invites');
        }
        
        // Verifica se l'utente esiste già
        if ($this->userModel->findUserByEmail($email, $tenant_id)) {
            flash('invite_message', 'Questo indirizzo email è già registrato', 'alert alert-danger');
            redirect('invites');
        }
        
        // Crea l'invito
        $invite = $this->inviteModel->create([
            'email' => $email,
            'tenant_id' => $tenant_id
        ]);
        
        if (!$invite) {
            flash('invite_message', 'Impossibile creare l\'invito. Riprova.', 'alert alert-danger');
            redirect('invites');
        }
        
        // Se è un invito manuale, crea direttamente l'utente
        if ($manualRegistration && !empty($name) && !empty($lastname)) {
            // Genera una password casuale
            $password = $this->generateRandomPassword();
            
            // Crea l'utente
            $user_id = $this->userModel->register([
                'name' => $name,
                'lastname' => $lastname,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'tenant_id' => $tenant_id
            ]);
            
            if ($user_id) {
                // Crea un profilo base
                $profile_data = [
                    'user_id' => $user_id,
                    'phone' => '',
                    'birthdate' => null,
                    'tax_code' => '',
                    'address' => '',
                    'city' => '',
                    'province' => '',
                    'zip' => '',
                    'weight' => 0,
                    'height' => 0
                ];
                
                $profile_id = $this->profileModel->create($profile_data);
                
                // Aggiorna lo stato dell'invito a 'used'
                $this->inviteModel->updateStatus($invite['id'], 'used');
                
                // Genera il QR code per il link di invito
                $this->generateQrCode($invite['token']);
                
                // Se richiesto, invia email con credenziali
                if ($sendEmail) {
                    $smtpConfigured = $this->smtpModel->isConfigured($tenant_id);
                    if ($smtpConfigured) {
                        $this->sendCredentialsEmail($email, $password, $tenant_id);
                    }
                }
                
                // Reindirizza alla pagina di riepilogo
                flash('invite_message', 'Utente creato con successo!', 'alert alert-success');
                redirect('invites/details/' . $invite['token']);
                return;
            } else {
                flash('invite_message', 'Impossibile creare l\'utente. Riprova.', 'alert alert-danger');
                redirect('invites');
                return;
            }
        }
        
        // Genera il QR code per il link di invito
        $this->generateQrCode($invite['token']);
        
        // Se richiesto, invia email di invito
        if ($sendEmail) {
            // Verifica se SMTP è configurato per inviare l'email
            $smtpConfigured = $this->smtpModel->isConfigured($tenant_id);
            
            // Se SMTP è configurato, invia l'email
            if ($smtpConfigured) {
                $this->sendInviteEmail($invite, $tenant_id);
            }
        }
        
        // Messaggio di successo
        flash('invite_message', 'Invito creato con successo!', 'alert alert-success');
        
        // Reindirizza alla pagina di riepilogo
        redirect('invites/details/' . $invite['token']);
    }
    
    /**
     * Details - Mostra i dettagli dell'invito
     *
     * @param string $token Token dell'invito
     * @return void
     */
    public function details($token) {
        // Verifica che l'utente sia autenticato e sia GYM_ADMIN
        if (!isLoggedIn() || !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        // Ottieni l'invito tramite token
        $invite = $this->inviteModel->getByToken($token);
        
        // Verifica se l'invito esiste
        if (!$invite) {
            flash('invite_message', 'Invito non trovato', 'alert alert-danger');
            redirect('invites');
        }
        
        // Verifica che l'invito appartenga al tenant corrente
        if ($invite['tenant_id'] != $_SESSION['tenant_id']) {
            flash('invite_message', 'Non hai i permessi per visualizzare questo invito', 'alert alert-danger');
            redirect('invites');
        }
        
        // Costruisci l'URL completo dell'invito
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        $inviteUrl = $protocol . $domainName . URLROOT . '/register/' . $token;
        
        // Percorso del QR code
        $qrCodePath = '/uploads/qrcodes/' . $token . '.png';
        
        // Genera il QR code se non esiste
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $qrCodePath)) {
            $this->generateQrCode($token);
        }
        
        // Imposta i dati per la vista
        $data = [
            'invite' => $invite,
            'inviteUrl' => $inviteUrl,
            'qrCodePath' => $qrCodePath,
            'smtpConfigured' => $this->smtpModel->isConfigured($_SESSION['tenant_id'])
        ];
        
        // Renderizza la vista
        $this->render('invites/details', $data);
    }
    
    /**
     * SendEmail - Invia l'email di invito
     *
     * @param string $token Token dell'invito
     * @return void
     */
    public function sendEmail($token = '') {
        // Verifica che sia una richiesta POST o AJAX
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            redirect('invites');
        }
        
        // Verifica che l'utente sia autenticato e sia GYM_ADMIN
        if (!isLoggedIn() || !hasRole('GYM_ADMIN')) {
            $this->jsonResponse(false, 'Non sei autorizzato a effettuare questa operazione');
            return;
        }
        
        // Ottieni l'invito tramite token
        $invite = $this->inviteModel->getByToken($token);
        
        // Verifica se l'invito esiste
        if (!$invite) {
            $this->jsonResponse(false, 'Invito non trovato');
            return;
        }
        
        // Verifica che l'invito appartenga al tenant corrente
        if ($invite['tenant_id'] != $_SESSION['tenant_id']) {
            $this->jsonResponse(false, 'Non hai i permessi per inviare questo invito');
            return;
        }
        
        // Verifica se SMTP è configurato
        $smtpConfigured = $this->smtpModel->isConfigured($_SESSION['tenant_id']);
        
        if (!$smtpConfigured) {
            $this->jsonResponse(false, 'Le impostazioni SMTP non sono configurate');
            return;
        }
        
        // Invia l'email
        $emailSent = $this->sendInviteEmail($invite, $_SESSION['tenant_id']);
        
        if (!$emailSent) {
            $this->jsonResponse(false, 'Errore nell\'invio dell\'email');
            return;
        }
        
        // Risposta JSON di successo
        $this->jsonResponse(true, 'Email inviata con successo');
    }
    
    /**
     * Invia una risposta JSON
     *
     * @param bool $success Indica se l'operazione è riuscita
     * @param string $message Messaggio da mostrare
     * @param array $data Dati aggiuntivi (opzionale)
     * @return void
     */
    private function jsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
    
    /**
     * Register - Mostra il modulo di registrazione per un invito
     *
     * @param string $token Token dell'invito
     * @return void
     */
    public function register($token) {
        // Ottieni l'invito tramite token
        $invite = $this->inviteModel->getByToken($token);
        
        // Verifica se l'invito è valido
        if (!$invite || !$this->inviteModel->isValid($invite)) {
            // Renderizza la vista dell'errore
            $this->render('invites/invalid_token');
            return;
        }
        
        // Imposta i dati per la vista
        $data = [
            'token' => $token,
            'email' => $invite['email'],
            'tenant_name' => $invite['tenant_name'],
            'tenant_id' => $invite['tenant_id'],
            'errors' => $_SESSION['errors'] ?? []
        ];
        
        // Pulisci le variabili di sessione
        unset($_SESSION['errors']);
        
        // Renderizza la vista
        $this->render('invites/register', $data);
    }
    
    /**
     * Process - Elabora la registrazione
     *
     * @param string $token Token dell'invito
     * @return void
     */
    public function process($token) {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('register/' . $token);
        }
        
        // Ottieni l'invito tramite token
        $invite = $this->inviteModel->getByToken($token);
        
        // Verifica se l'invito è valido
        if (!$invite || !$this->inviteModel->isValid($invite)) {
            // Renderizza la vista dell'errore
            $this->render('invites/invalid_token');
            return;
        }
        
        // Ottieni i dati dal form
        $data = [
            'name' => trim($_POST['name']),
            'lastname' => trim($_POST['lastname']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'confirm_password' => trim($_POST['confirm_password']),
            'phone' => trim($_POST['phone']),
            'birthdate' => trim($_POST['birthdate']),
            'tax_code' => trim($_POST['tax_code']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'province' => trim($_POST['province']),
            'zip' => trim($_POST['zip']),
            'weight' => trim($_POST['weight']),
            'height' => trim($_POST['height'])
        ];
        
        // Validazione dei dati
        $errors = [];
        
        // Validazione dei campi obbligatori
        $required_fields = ['name', 'lastname', 'email', 'password', 'confirm_password', 'phone',
                           'birthdate', 'tax_code', 'address', 'city', 'province', 'zip', 'weight', 'height'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = 'Questo campo è obbligatorio';
            }
        }
        
        // Verifica che l'email corrisponda a quella dell'invito
        if ($data['email'] !== $invite['email']) {
            $errors['email'] = 'L\'indirizzo email non corrisponde a quello dell\'invito';
        }
        
        // Verifica se l'email esiste già nel sistema
        if ($this->userModel->findOneByField('email', $data['email'])) {
            $errors['email'] = 'L\'indirizzo email è già registrato nel sistema. Si prega di effettuare il login.';
        }
        
        // Verifica la password
        if (strlen($data['password']) < 6) {
            $errors['password'] = 'La password deve contenere almeno 6 caratteri';
        }
        
        // Verifica che le password corrispondano
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Le password non corrispondono';
        }
        
        // Se ci sono errori, torna alla vista con gli errori
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            redirect('register/' . $token);
        }
        
        // Crea il nuovo utente
        $user_id = $this->userModel->register([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'MEMBER',
            'tenant_id' => $invite['tenant_id']
        ]);
        
        if (!$user_id) {
            $errors['general'] = 'Impossibile registrare l\'utente. Riprova.';
            $_SESSION['errors'] = $errors;
            redirect('register/' . $token);
        }
        
        // Crea il profilo utente
        $profile_data = [
            'user_id' => $user_id,
            'phone' => $data['phone'],
            'birthdate' => $data['birthdate'],
            'tax_code' => $data['tax_code'],
            'address' => $data['address'],
            'city' => $data['city'],
            'province' => $data['province'],
            'zip' => $data['zip'],
            'weight' => $data['weight'],
            'height' => $data['height']
        ];
        
        $profile_id = $this->profileModel->create($profile_data);
        
        if (!$profile_id) {
            // In caso di errore nella creazione del profilo, elimina l'utente
            $this->userModel->delete($user_id);
            
            $errors['general'] = 'Impossibile creare il profilo utente. Riprova.';
            $_SESSION['errors'] = $errors;
            redirect('register/' . $token);
        }
        
        // Aggiorna lo stato dell'invito a 'used'
        $this->inviteModel->updateStatus($invite['id'], 'used');
        
        // Messaggio di successo
        flash('login_message', 'Registrazione completata con successo! Ora puoi accedere con le tue credenziali.', 'alert alert-success');
        redirect('login');
    }
    
    /**
     * Invia l'email di invito
     *
     * @param array $invite Dati dell'invito
     * @param int $tenant_id ID del tenant
     * @return bool True se l'invio è riuscito, altrimenti false
     */
    private function sendInviteEmail($invite, $tenant_id) {
        try {
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
            $mail->addAddress($invite['email']);
            
            // Imposta l'oggetto e il corpo del messaggio
            $mail->Subject = 'Invito a registrarsi su ' . $tenant_name;
            
            // Costruisci l'URL completo di registrazione
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $register_url = $protocol . $domainName . URLROOT . '/register/' . $invite['token'];
            
            // Corpo HTML
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Invito a registrarsi su {$tenant_name}</h2>
                <p>Sei stato invitato a registrarti su {$tenant_name}.</p>
                <p>Per completare la registrazione, clicca sul seguente link:</p>
                <p><a href='{$register_url}'>{$register_url}</a></p>
                <p>Questo link scadrà tra 48 ore.</p>
                <p>Se non hai richiesto questo invito, puoi ignorare questa email.</p>
            ";
            
            // Corpo alternativo in testo
            $mail->AltBody = "
                Invito a registrarsi su {$tenant_name}
                
                Sei stato invitato a registrarti su {$tenant_name}.
                Per completare la registrazione, visita il seguente link:
                {$register_url}
                
                Questo link scadrà tra 48 ore.
                Se non hai richiesto questo invito, puoi ignorare questa email.
            ";
            
            // Invia l'email
            if ($mail->send()) {
                return true;
            } else {
                error_log("Errore nell'invio dell'email di invito: " . $mail->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            error_log("Errore nell'invio dell'email di invito: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Invia l'email con le credenziali dell'utente
     *
     * @param string $email Indirizzo email dell'utente
     * @param string $password Password generata
     * @param int $tenant_id ID del tenant
     * @return bool True se l'invio è riuscito, altrimenti false
     */
    private function sendCredentialsEmail($email, $password, $tenant_id) {
        try {
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
            $mail->addAddress($email);
            
            // Imposta l'oggetto e il corpo del messaggio
            $mail->Subject = 'Le tue credenziali di accesso a ' . $tenant_name;
            
            // URL di login
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $login_url = $protocol . $domainName . URLROOT . '/login';
            
            // Corpo HTML
            $mail->isHTML(true);
            $mail->Body = "
                <h2>Le tue credenziali di accesso a {$tenant_name}</h2>
                <p>È stato creato un account per te su {$tenant_name}.</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Password:</strong> {$password}</p>
                <p>Per accedere, visita il seguente link:</p>
                <p><a href='{$login_url}'>{$login_url}</a></p>
                <p>Ti consigliamo di cambiare la password al primo accesso.</p>
            ";
            
            // Corpo alternativo in testo
            $mail->AltBody = "
                Le tue credenziali di accesso a {$tenant_name}
                
                È stato creato un account per te su {$tenant_name}.
                
                Email: {$email}
                Password: {$password}
                
                Per accedere, visita il seguente link:
                {$login_url}
                
                Ti consigliamo di cambiare la password al primo accesso.
            ";
            
            // Invia l'email
            if ($mail->send()) {
                return true;
            } else {
                error_log("Errore nell'invio dell'email con le credenziali: " . $mail->ErrorInfo);
                return false;
            }
        } catch (Exception $e) {
            error_log("Errore nell'invio dell'email con le credenziali: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera una password casuale
     *
     * @param int $length Lunghezza della password
     * @return string Password generata
     */
    private function generateRandomPassword($length = 10) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+;:,.?';
        $password = '';
        
        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }
        
        return $password;
    }
    
    /**
     * Genera un QR code per il link di invito
     *
     * @param string $token Token dell'invito
     * @return bool True se la generazione è riuscita, altrimenti false
     */
    private function generateQrCode($token) {
        try {
            // Costruisci l'URL completo dell'invito
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $registerUrl = $protocol . $domainName . URLROOT . '/register/' . $token;
            
            // Directory per i QR code
            $qrCodeDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/qrcodes';
            
            // Crea la directory se non esiste
            if (!is_dir($qrCodeDir)) {
                mkdir($qrCodeDir, 0755, true);
            }
            
            // Percorso del file QR code
            $qrCodePath = $qrCodeDir . '/' . $token . '.png';
            
            // Utilizziamo un metodo alternativo per creare un semplice QR code usando Google Charts API
            $googleChartUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($registerUrl);
            $qrImageContent = file_get_contents($googleChartUrl);
            
            if ($qrImageContent) {
                // Scrivi l'immagine su file
                file_put_contents($qrCodePath, $qrImageContent);
                return true;
            } else {
                error_log("Impossibile generare QR code da Google Charts API");
                return false;
            }
        } catch (Exception $e) {
            error_log("Errore nella generazione del QR code: " . $e->getMessage());
            return false;
        }
    }
}