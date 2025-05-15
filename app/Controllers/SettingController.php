<?php
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
        // Ottieni le impostazioni SMTP per il tenant corrente
        $smtpSettings = $this->smtpModel->getByTenantId(getCurrentTenantId());
        
        // Prepara i dati per la view
        $data = [
            'smtp_settings' => $smtpSettings ?: [
                'host' => '',
                'port' => 587,
                'username' => '',
                'password' => '',
                'sender_name' => '',
                'sender_email' => '',
                'encryption' => 'tls',
                'active' => true
            ]
        ];
        
        // Renderizza la view
        $this->render('settings/index', $data);
    }
    
    /**
     * Save SMTP Settings
     *
     * @return void
     */
    public function saveSmtp() {
        // Verifica il metodo di richiesta
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        // Processa i dati del form
        $data = [
            'tenant_id' => getCurrentTenantId(),
            'host' => trim($_POST['host']),
            'port' => intval($_POST['port']),
            'username' => trim($_POST['username']),
            'password' => trim($_POST['password']),
            'sender_name' => trim($_POST['sender_name']),
            'sender_email' => trim($_POST['sender_email']),
            'encryption' => trim($_POST['encryption']),
            'active' => isset($_POST['active']) ? true : false
        ];
        
        // Valida i dati
        $errors = [];
        
        if (empty($data['host'])) {
            $errors['host'] = 'Il campo Host è obbligatorio';
        }
        
        if (empty($data['port'])) {
            $errors['port'] = 'Il campo Porta è obbligatorio';
        }
        
        if (empty($data['username'])) {
            $errors['username'] = 'Il campo Username è obbligatorio';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Il campo Password è obbligatorio';
        }
        
        if (empty($data['sender_name'])) {
            $errors['sender_name'] = 'Il campo Nome Mittente è obbligatorio';
        }
        
        if (empty($data['sender_email'])) {
            $errors['sender_email'] = 'Il campo Email Mittente è obbligatorio';
        } elseif (!filter_var($data['sender_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['sender_email'] = 'Inserisci un indirizzo email valido';
        }
        
        // Se ci sono errori, mostra il form con gli errori
        if (!empty($errors)) {
            $data['errors'] = $errors;
            $this->render('settings/index', ['smtp_settings' => $data, 'errors' => $errors]);
            return;
        }
        
        // Salva le impostazioni
        if ($this->smtpModel->saveSettings($data)) {
            flash('settings_message', 'Impostazioni SMTP salvate con successo');
        } else {
            flash('settings_message', 'Errore durante il salvataggio delle impostazioni SMTP', 'alert alert-danger');
        }
        
        redirect('settings');
    }
    
    /**
     * Test SMTP Connection
     *
     * @return void
     */
    public function testSmtp() {
        // Verifica il metodo di richiesta
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        // Processa i dati del form
        $data = [
            'host' => trim($_POST['host']),
            'port' => intval($_POST['port']),
            'username' => trim($_POST['username']),
            'password' => trim($_POST['password']),
            'sender_name' => trim($_POST['sender_name']),
            'sender_email' => trim($_POST['sender_email']),
            'encryption' => trim($_POST['encryption'])
        ];
        
        // Esegui il test di connessione
        $result = $this->smtpModel->testConnection($data);
        
        // Restituisci il risultato in formato JSON
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}