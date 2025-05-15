<?php
// Carica l'autoloader di Composer
require_once 'vendor/autoload.php';

// Importa la classe PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        
        // Aggiungi middleware per verificare se l'utente è GYM_ADMIN
        $this->middleware = [
            'auth' => ['*'],
            'gymAdmin' => ['index', 'create', 'send']
        ];
    }
    
    /**
     * Index - Mostra la pagina degli inviti
     *
     * @return void
     */
    public function index() {
        // Verifica che l'utente sia autenticato e sia GYM_ADMIN
        if (!isLoggedIn() || !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        // Ottieni gli inviti per il tenant corrente
        $invites = $this->inviteModel->getByTenantId(getCurrentTenantId());
        
        // Verifica se le impostazioni SMTP sono configurate
        $smtpSettings = $this->smtpModel->getByTenantId(getCurrentTenantId());
        $smtpConfigured = !empty($smtpSettings) && $smtpSettings['active'];
        
        // Renderizza la view
        $this->render('invites/index', [
            'invites' => $invites,
            'smtpConfigured' => $smtpConfigured
        ]);
    }
    
    /**
     * Send - Invia un invito
     *
     * @return void
     */
    public function send() {
        // Verifica che l'utente sia autenticato e sia GYM_ADMIN
        if (!isLoggedIn() || !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        // Verifica il metodo di richiesta
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('invites');
        }
        
        // Processa i dati del form
        $email = trim($_POST['email']);
        $tenantId = getCurrentTenantId();
        
        // Valida l'email
        if (empty($email)) {
            flash('invite_message', 'Inserisci un indirizzo email valido', 'alert alert-danger');
            redirect('invites');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('invite_message', 'Formato email non valido', 'alert alert-danger');
            redirect('invites');
        }
        
        // Verifica se l'email è già presente nel sistema per questo tenant
        $existingUser = $this->userModel->findUserByEmail($email);
        if ($existingUser && $existingUser['tenant_id'] == $tenantId) {
            flash('invite_message', 'Questo utente è già registrato per questa palestra', 'alert alert-danger');
            redirect('invites');
        }
        
        // Verifica se l'email è già stata invitata
        if ($this->inviteModel->isEmailInvited($email, $tenantId)) {
            flash('invite_message', 'È già stato inviato un invito a questo indirizzo email', 'alert alert-danger');
            redirect('invites');
        }
        
        // Genera un token univoco
        $token = $this->inviteModel->generateToken();
        
        // Calcola la data di scadenza (48 ore)
        $expiresAt = (new DateTime())->add(new DateInterval('PT48H'))->format('Y-m-d H:i:s');
        
        // Crea l'invito
        $inviteData = [
            'token' => $token,
            'tenant_id' => $tenantId,
            'email' => $email,
            'expires_at' => $expiresAt
        ];
        
        // Salva l'invito nel database
        $inviteId = $this->inviteModel->create($inviteData);
        
        if (!$inviteId) {
            flash('invite_message', 'Errore durante la creazione dell\'invito', 'alert alert-danger');
            redirect('invites');
        }
        
        // Ottieni le impostazioni SMTP
        $smtpSettings = $this->smtpModel->getByTenantId($tenantId);
        
        if (empty($smtpSettings) || !$smtpSettings['active']) {
            flash('invite_message', 'Le impostazioni SMTP non sono configurate. L\'invito è stato creato ma non è stato inviato via email.', 'alert alert-warning');
            redirect('invites');
        }
        
        // Genera il link di registrazione
        $registerLink = URLROOT . '/register/' . $token;
        
        // Invia l'email
        if ($this->sendInviteEmail($email, $registerLink, $smtpSettings)) {
            flash('invite_message', 'Invito inviato con successo a ' . $email);
        } else {
            flash('invite_message', 'L\'invito è stato creato ma si è verificato un errore durante l\'invio dell\'email', 'alert alert-warning');
        }
        
        redirect('invites');
    }
    
    /**
     * Register Form - Mostra il form di registrazione
     *
     * @param string $token Token di invito
     * @return void
     */
    public function register($token) {
        // Verifica se il token è valido
        if (!$this->inviteModel->isValidToken($token)) {
            // Token non valido o scaduto
            $this->render('invites/invalid_token');
            return;
        }
        
        // Ottieni i dati dell'invito
        $invite = $this->inviteModel->getByToken($token);
        
        // Prepara i dati per la view
        $data = [
            'token' => $token,
            'email' => $invite['email'],
            'tenant_name' => $invite['tenant_name'],
            'errors' => []
        ];
        
        // Renderizza la view
        $this->render('invites/register', $data);
    }
    
    /**
     * Process Registration - Elabora la registrazione
     *
     * @param string $token Token di invito
     * @return void
     */
    public function process($token) {
        // Verifica il metodo di richiesta
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('register/' . $token);
        }
        
        // Verifica se il token è valido
        if (!$this->inviteModel->isValidToken($token)) {
            // Token non valido o scaduto
            $this->render('invites/invalid_token');
            return;
        }
        
        // Ottieni i dati dell'invito
        $invite = $this->inviteModel->getByToken($token);
        
        // Processa i dati del form
        $data = [
            'token' => $token,
            'email' => $invite['email'],
            'tenant_name' => $invite['tenant_name'],
            'name' => trim($_POST['name']),
            'lastname' => trim($_POST['lastname']),
            'password' => trim($_POST['password']),
            'confirm_password' => trim($_POST['confirm_password']),
            'phone' => trim($_POST['phone']),
            'birthdate' => trim($_POST['birthdate']),
            'tax_code' => trim($_POST['tax_code']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'zip' => trim($_POST['zip']),
            'province' => trim($_POST['province']),
            'weight' => trim($_POST['weight']),
            'height' => trim($_POST['height']),
            'errors' => []
        ];
        
        // Valida i dati
        if (empty($data['name'])) {
            $data['errors']['name'] = 'Il campo Nome è obbligatorio';
        }
        
        if (empty($data['lastname'])) {
            $data['errors']['lastname'] = 'Il campo Cognome è obbligatorio';
        }
        
        if (empty($data['password'])) {
            $data['errors']['password'] = 'Il campo Password è obbligatorio';
        } elseif (strlen($data['password']) < 6) {
            $data['errors']['password'] = 'La password deve essere di almeno 6 caratteri';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $data['errors']['confirm_password'] = 'Le password non corrispondono';
        }
        
        if (empty($data['phone'])) {
            $data['errors']['phone'] = 'Il campo Telefono è obbligatorio';
        }
        
        if (empty($data['birthdate'])) {
            $data['errors']['birthdate'] = 'Il campo Data di nascita è obbligatorio';
        }
        
        if (empty($data['tax_code'])) {
            $data['errors']['tax_code'] = 'Il campo Codice fiscale è obbligatorio';
        }
        
        if (empty($data['address'])) {
            $data['errors']['address'] = 'Il campo Indirizzo è obbligatorio';
        }
        
        if (empty($data['city'])) {
            $data['errors']['city'] = 'Il campo Città è obbligatorio';
        }
        
        if (empty($data['zip'])) {
            $data['errors']['zip'] = 'Il campo CAP è obbligatorio';
        }
        
        if (empty($data['province'])) {
            $data['errors']['province'] = 'Il campo Provincia è obbligatorio';
        }
        
        if (empty($data['weight'])) {
            $data['errors']['weight'] = 'Il campo Peso è obbligatorio';
        } elseif (!is_numeric($data['weight']) || $data['weight'] <= 0) {
            $data['errors']['weight'] = 'Inserisci un valore numerico valido per il peso';
        }
        
        if (empty($data['height'])) {
            $data['errors']['height'] = 'Il campo Altezza è obbligatorio';
        } elseif (!is_numeric($data['height']) || $data['height'] <= 0) {
            $data['errors']['height'] = 'Inserisci un valore numerico valido per l\'altezza';
        }
        
        // Se ci sono errori, mostra il form con gli errori
        if (!empty($data['errors'])) {
            $this->render('invites/register', $data);
            return;
        }
        
        // Salva l'utente nel database
        $userData = [
            'tenant_id' => $invite['tenant_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'MEMBER'
        ];
        
        // Inizia una transazione
        $this->db->beginTransaction();
        
        try {
            // Crea l'utente
            $userId = $this->userModel->create($userData);
            
            if (!$userId) {
                throw new Exception('Errore durante la creazione dell\'utente');
            }
            
            // Crea il profilo utente
            $profileData = [
                'user_id' => $userId,
                'lastname' => $data['lastname'],
                'phone' => $data['phone'],
                'birthdate' => $data['birthdate'],
                'tax_code' => $data['tax_code'],
                'address' => $data['address'],
                'city' => $data['city'],
                'zip' => $data['zip'],
                'province' => $data['province'],
                'weight' => $data['weight'],
                'height' => $data['height']
            ];
            
            $profileId = $this->profileModel->create($profileData);
            
            if (!$profileId) {
                throw new Exception('Errore durante la creazione del profilo utente');
            }
            
            // Segna l'invito come utilizzato
            if (!$this->inviteModel->markAsUsed($token)) {
                throw new Exception('Errore durante l\'aggiornamento dello stato dell\'invito');
            }
            
            // Commit della transazione
            $this->db->commit();
            
            // Mostra un messaggio di successo
            flash('login_message', 'Registrazione completata con successo. Ora puoi effettuare il login.');
            redirect('login');
        } catch (Exception $e) {
            // Rollback della transazione in caso di errore
            $this->db->rollBack();
            
            $data['errors']['general'] = 'Si è verificato un errore durante la registrazione: ' . $e->getMessage();
            $this->render('invites/register', $data);
        }
    }
    
    /**
     * Invia l'email di invito
     *
     * @param string $email Email del destinatario
     * @param string $registerLink Link di registrazione
     * @param array $smtpSettings Impostazioni SMTP
     * @return bool
     */
    private function sendInviteEmail($email, $registerLink, $smtpSettings) {
        try {
            // Carica l'autoloader di Composer
            require_once 'vendor/autoload.php';
            
            // Crea una nuova istanza di PHPMailer
            $mail = new PHPMailer(true);
            
            // Impostazioni del server
            $mail->isSMTP();
            $mail->Host = $smtpSettings['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpSettings['username'];
            $mail->Password = $smtpSettings['password'];
            $mail->Port = $smtpSettings['port'];
            
            // Impostazioni di sicurezza
            switch ($smtpSettings['encryption']) {
                case 'tls':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    break;
                case 'ssl':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;
                default:
                    $mail->SMTPSecure = '';
                    $mail->SMTPAutoTLS = false;
            }
            
            // Impostazioni del mittente e del destinatario
            $mail->setFrom($smtpSettings['sender_email'], $smtpSettings['sender_name']);
            $mail->addAddress($email);
            
            // Contenuto dell'email
            $mail->isHTML(true);
            $mail->Subject = 'Invito a registrarsi nella nostra palestra';
            
            // Corpo dell'email
            $mail->Body = '
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h2>Sei stato invitato a registrarti nella nostra palestra</h2>
                        <p>Ciao,</p>
                        <p>Sei stato invitato a registrarti come membro nella nostra palestra. Per completare la registrazione, clicca sul pulsante qui sotto.</p>
                        <p><a class="button" href="' . $registerLink . '">Completa la registrazione</a></p>
                        <p>Oppure copia e incolla il seguente link nel tuo browser:</p>
                        <p>' . $registerLink . '</p>
                        <p>Questo link scadrà tra 48 ore.</p>
                        <p>Cordiali saluti,<br>Il team della palestra</p>
                    </div>
                </body>
                </html>
            ';
            
            // Versione alternativa in testo semplice
            $mail->AltBody = "Sei stato invitato a registrarti nella nostra palestra.\n\n";
            $mail->AltBody .= "Per completare la registrazione, visita il seguente link:\n";
            $mail->AltBody .= $registerLink . "\n\n";
            $mail->AltBody .= "Questo link scadrà tra 48 ore.\n\n";
            $mail->AltBody .= "Cordiali saluti,\nIl team della palestra";
            
            // Invia l'email
            $mail->send();
            
            return true;
        } catch (Exception $e) {
            error_log('Errore nell\'invio dell\'email: ' . $e->getMessage());
            return false;
        }
    }
}