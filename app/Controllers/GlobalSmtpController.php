<?php

/**
 * Controller per la gestione delle impostazioni SMTP globali
 * Accessibile solo a SUPER_ADMIN
 */
class GlobalSmtpController extends BaseController {
    private $globalSmtpModel;
    
    /**
     * Costruttore
     */
    public function __construct() {
        parent::__construct();
        
        // Verifica che l'utente sia autenticato e sia SUPER_ADMIN
        if (!isLoggedIn() || !hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        $this->globalSmtpModel = new GlobalSmtpSetting();
    }
    
    /**
     * Mostra la pagina delle impostazioni SMTP globali
     */
    public function index() {
        // Recupera le impostazioni SMTP globali
        $smtpSettings = $this->globalSmtpModel->get();
        
        // Imposta i dati per la vista
        $data = [
            'settings' => $smtpSettings
        ];
        
        // Renderizza la vista
        $this->render('global_smtp/index', $data);
    }
    
    /**
     * Salva le impostazioni SMTP globali
     */
    public function save() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('global-smtp');
        }
        
        // Ottieni i dati dal form
        $id = $_POST['id'] ?? 0;
        $host = trim($_POST['host'] ?? '');
        $port = intval($_POST['port'] ?? 587);
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $sender_name = trim($_POST['sender_name'] ?? '');
        $sender_email = trim($_POST['sender_email'] ?? '');
        $encryption = $_POST['encryption'] ?? 'tls';
        $active = isset($_POST['active']) ? true : false;
        
        // Validazione
        $errors = [];
        
        if (empty($host)) {
            $errors[] = 'Il campo Host è obbligatorio';
        }
        
        if (empty($username)) {
            $errors[] = 'Il campo Username è obbligatorio';
        }
        
        if ($active && empty($password)) {
            $errors[] = 'Il campo Password è obbligatorio se l\'opzione Attivo è selezionata';
        }
        
        if (empty($sender_name)) {
            $errors[] = 'Il campo Nome mittente è obbligatorio';
        }
        
        if (empty($sender_email)) {
            $errors[] = 'Il campo Email mittente è obbligatorio';
        } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Il campo Email mittente non è un indirizzo email valido';
        }
        
        // Se ci sono errori, mostra il form con i messaggi di errore
        if (!empty($errors)) {
            $errorMessage = implode('<br>', $errors);
            flash('smtp_message', $errorMessage, 'alert alert-danger');
            redirect('global-smtp');
        }
        
        // Password precedente
        $previousSettings = $this->globalSmtpModel->get();
        
        // Se la password è vuota, mantieni quella precedente
        if (empty($password) && isset($previousSettings['password'])) {
            $password = $previousSettings['password'];
        }
        
        // Prepara i dati per il salvataggio
        $data = [
            'id' => $id,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'sender_name' => $sender_name,
            'sender_email' => $sender_email,
            'encryption' => $encryption,
            'active' => $active
        ];
        
        // Salva le impostazioni
        if ($this->globalSmtpModel->save($data)) {
            flash('smtp_message', 'Impostazioni SMTP globali salvate con successo', 'alert alert-success');
        } else {
            flash('smtp_message', 'Errore nel salvataggio delle impostazioni SMTP globali', 'alert alert-danger');
        }
        
        redirect('global-smtp');
    }
    
    /**
     * Testa la connessione SMTP
     */
    public function test() {
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('global-smtp');
        }
        
        // Ottieni i dati dal form
        $host = trim($_POST['host'] ?? '');
        $port = intval($_POST['port'] ?? 587);
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $encryption = $_POST['encryption'] ?? 'tls';
        
        // Password precedente
        $previousSettings = $this->globalSmtpModel->get();
        
        // Se la password è vuota, usa quella precedente
        if (empty($password) && isset($previousSettings['password'])) {
            $password = $previousSettings['password'];
        }
        
        // Validazione
        if (empty($host) || empty($username) || empty($password)) {
            flash('smtp_message', 'I campi Host, Username e Password sono obbligatori per testare la connessione', 'alert alert-danger');
            redirect('global-smtp');
        }
        
        // Prepara i dati per il test
        $data = [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'encryption' => $encryption
        ];
        
        // Testa la connessione
        $result = $this->globalSmtpModel->testConnection($data);
        
        if ($result['success']) {
            flash('smtp_message', $result['message'], 'alert alert-success');
        } else {
            flash('smtp_message', $result['message'], 'alert alert-danger');
        }
        
        redirect('global-smtp');
    }
}