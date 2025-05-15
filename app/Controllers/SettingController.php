<?php
// Carica l'autoloader di Composer
require_once 'vendor/autoload.php';

// Importa la classe PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Settings Controller
 */
class SettingController extends BaseController {
    /**
     * SmtpSetting model
     *
     * @var SmtpSetting
     */
    private $smtpModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Verifica che l'utente sia autenticato e sia GYM_ADMIN
        if (!isLoggedIn() || !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        // Inizializza il modello SMTP
        $this->smtpModel = new SmtpSetting();
    }
    
    /**
     * Index - Mostra la pagina delle impostazioni
     *
     * @return void
     */
    public function index() {
        // Ottieni il tenant_id dalla sessione
        $tenant_id = $_SESSION['tenant_id'];
        
        // Ottieni le impostazioni SMTP
        $smtp_settings = $this->smtpModel->getByTenant($tenant_id);
        
        // Imposta i dati per la vista
        $data = [
            'smtp_settings' => $smtp_settings
        ];
        
        // Renderizza la vista
        $this->render('settings/index', $data);
    }
    
    /**
     * SaveSmtp - Salva le impostazioni SMTP
     *
     * @return void
     */
    public function saveSmtp() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        // Ottieni il tenant_id dalla sessione
        $tenant_id = $_SESSION['tenant_id'];
        
        // Ottieni i dati dal form
        $data = [
            'smtp_host' => trim($_POST['smtp_host']),
            'smtp_port' => (int)trim($_POST['smtp_port']),
            'smtp_username' => trim($_POST['smtp_username']),
            'smtp_password' => trim($_POST['smtp_password']),
            'smtp_encryption' => trim($_POST['smtp_encryption']),
            'smtp_from_email' => trim($_POST['smtp_from_email']),
            'smtp_from_name' => trim($_POST['smtp_from_name'])
        ];
        
        // Validazione dei dati
        $errors = [];
        
        if (empty($data['smtp_host'])) {
            $errors['smtp_host'] = 'Il campo Host SMTP è obbligatorio';
        }
        
        if (empty($data['smtp_port'])) {
            $errors['smtp_port'] = 'Il campo Porta SMTP è obbligatorio';
        }
        
        if (empty($data['smtp_from_email'])) {
            $errors['smtp_from_email'] = 'Il campo Email mittente è obbligatorio';
        } elseif (!filter_var($data['smtp_from_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['smtp_from_email'] = 'Inserisci un indirizzo email valido';
        }
        
        // Se ci sono errori, torna alla vista con gli errori
        if (!empty($errors)) {
            flash('smtp_error', 'Ci sono errori nel form. Correggi e riprova.', 'alert alert-danger');
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $data;
            redirect('settings');
        }
        
        // Salva le impostazioni SMTP
        if ($this->smtpModel->saveOrUpdate($tenant_id, $data)) {
            flash('smtp_success', 'Impostazioni SMTP salvate con successo!', 'alert alert-success');
        } else {
            flash('smtp_error', 'Impossibile salvare le impostazioni SMTP. Riprova.', 'alert alert-danger');
        }
        
        // Redirect alla pagina delle impostazioni
        redirect('settings');
    }
    
    /**
     * TestSmtp - Testa le impostazioni SMTP
     *
     * @return void
     */
    public function testSmtp() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Metodo non consentito']);
                return;
            }
            redirect('settings');
        }
        
        // Ottieni il tenant_id dalla sessione
        $tenant_id = $_SESSION['tenant_id'];
        
        // Ottieni le impostazioni SMTP
        $smtp_settings = $this->smtpModel->getByTenant($tenant_id);
        
        // Verifica che le impostazioni SMTP siano configurate
        if (!$smtp_settings || !$this->smtpModel->isConfigured($tenant_id)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Le impostazioni SMTP non sono configurate']);
                return;
            }
            
            flash('smtp_error', 'Le impostazioni SMTP non sono configurate. Configura prima le impostazioni.', 'alert alert-danger');
            redirect('settings');
        }
        
        // Indirizzo email di test (quello dell'utente corrente)
        $testEmail = $_SESSION['user_email'];
        
        // Crea una nuova istanza di PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configura PHPMailer con le impostazioni SMTP
            $this->smtpModel->configurePHPMailer($mail, $tenant_id);
            
            // Imposta il destinatario
            $mail->addAddress($testEmail);
            
            // Imposta l'oggetto e il corpo del messaggio
            $mail->Subject = 'Test SMTP da GymManager';
            $mail->Body    = 'Questo è un messaggio di test per verificare le impostazioni SMTP di GymManager.';
            $mail->AltBody = 'Questo è un messaggio di test per verificare le impostazioni SMTP di GymManager.';
            
            // Invia l'email
            $mail->send();
            
            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Email di test inviata con successo!']);
                return;
            }
            
            flash('smtp_success', 'Email di test inviata con successo!', 'alert alert-success');
        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Errore nell\'invio dell\'email: ' . $mail->ErrorInfo]);
                return;
            }
            
            flash('smtp_error', 'Errore nell\'invio dell\'email: ' . $mail->ErrorInfo, 'alert alert-danger');
        }
        
        // Redirect alla pagina delle impostazioni
        redirect('settings');
    }
}