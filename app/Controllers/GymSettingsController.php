<?php
/**
 * Controller per la gestione delle impostazioni della palestra
 */
class GymSettingsController extends BaseController
{
    protected $gymSettingModel;
    protected $middleware = [
        'auth' => ['*']
    ];
    
    /**
     * Costruttore
     */
    public function __construct()
    {
        parent::__construct();
        $this->gymSettingModel = new GymSetting();
    }
    
    /**
     * Mostra il form delle impostazioni della palestra
     */
    public function index()
    {
        // Verifica che l'utente sia un amministratore della palestra
        if (!hasRole('GYM_ADMIN')) {
            $this->redirect('/');
        }
        
        $tenantId = $_SESSION['tenant_id'];
        $gymSettings = GymSetting::getByTenantId($tenantId);
        
        $this->view('gym_settings/index', [
            'gymSettings' => $gymSettings,
            'title' => __('Impostazioni Palestra'),
            'errors' => $this->getValidationErrors()
        ]);
    }
    
    /**
     * Salva le impostazioni della palestra
     */
    public function save()
    {
        // Verifica che l'utente sia un amministratore della palestra
        if (!hasRole('GYM_ADMIN')) {
            $this->redirect('/');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanifica i dati ricevuti
            $tenantId = $_SESSION['tenant_id'];
            $gymName = trim(filter_input(INPUT_POST, 'gym_name', FILTER_SANITIZE_SPECIAL_CHARS));
            $address = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS));
            $city = trim(filter_input(INPUT_POST, 'city', FILTER_SANITIZE_SPECIAL_CHARS));
            $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
            $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
            
            // Recupera le impostazioni esistenti
            $existingSettings = GymSetting::getByTenantId($tenantId);
            $logoPath = $existingSettings ? $existingSettings['logo_path'] : '';
            
            // Gestione upload logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                // Verifica dimensione file (max 2MB)
                if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
                    $errors['logo'] = __('Il file è troppo grande. Dimensione massima: 2MB');
                } else {
                    $newLogoPath = $this->gymSettingModel->uploadLogo($_FILES['logo'], $tenantId);
                    if ($newLogoPath) {
                        // Se esisteva già un logo, elimina il vecchio file
                        if (!empty($logoPath) && file_exists($_SERVER['DOCUMENT_ROOT'] . $logoPath) && $logoPath != $newLogoPath) {
                            @unlink($_SERVER['DOCUMENT_ROOT'] . $logoPath);
                        }
                        $logoPath = $newLogoPath;
                    } else {
                        $errors['logo'] = __('Errore nel caricamento del logo. Assicurati che sia un\'immagine valida.');
                    }
                }
            }
            
            // Validazione
            $errors = [];
            if (empty($gymName)) {
                $errors['gym_name'] = __('Il nome della palestra è obbligatorio');
            }
            
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = __('Email non valida');
            }
            
            if (!empty($errors)) {
                $this->setValidationErrors($errors);
                $this->redirect('/gym-settings');
                return;
            }
            
            // Salva i dati
            $data = [
                'tenant_id' => $tenantId,
                'logo_path' => $logoPath,
                'gym_name' => $gymName,
                'address' => $address,
                'city' => $city,
                'phone' => $phone,
                'email' => $email
            ];
            
            if ($this->gymSettingModel->saveSettings($data)) {
                $this->setFlashMessage('success', __('Impostazioni palestra aggiornate con successo'));
            } else {
                $this->setFlashMessage('error', __('Errore durante l\'aggiornamento delle impostazioni'));
            }
            
            $this->redirect('/gym-settings');
        } else {
            $this->redirect('/gym-settings');
        }
    }
}