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
        
        // Ottieni l'email dal form
        $email = trim($_POST['email']);
        
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
        $invite = $this->inviteModel->create($email, $tenant_id);
        
        if (!$invite) {
            flash('invite_message', 'Impossibile creare l\'invito. Riprova.', 'alert alert-danger');
            redirect('invites');
        }
        
        // Verifica se SMTP è configurato per inviare l'email
        $smtpConfigured = $this->smtpModel->isConfigured($tenant_id);
        
        // Se SMTP è configurato, invia l'email
        if ($smtpConfigured) {
            $this->sendInviteEmail($invite, $tenant_id);
        }
        
        // Messaggio di successo
        flash('invite_message', 'Invito creato con successo!', 'alert alert-success');
        redirect('invites');
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
            
            // URL di registrazione
            $register_url = URLROOT . '/register/' . $invite['token'];
            
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
}