<?php
/**
 * Modello per le impostazioni della palestra
 * Gestisce le informazioni del profilo della palestra come logo, nome, indirizzo, ecc.
 */
class GymSetting extends BaseModel
{
    protected $table = 'gym_settings';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tenant_id', 
        'logo_path', 
        'gym_name', 
        'address', 
        'city', 
        'phone', 
        'email'
    ];

    /**
     * Ottiene le impostazioni per un tenant specifico
     * 
     * @param int $tenantId ID del tenant
     * @return array|null Impostazioni della palestra o null se non trovate
     */
    public static function getByTenantId($tenantId)
    {
        $model = new self();
        $sql = "SELECT * FROM {$model->table} WHERE tenant_id = :tenant_id LIMIT 1";
        $stmt = $model->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea o aggiorna le impostazioni della palestra per un tenant
     * 
     * @param array $data Dati da salvare
     * @return bool Risultato dell'operazione
     */
    public function saveSettings($data)
    {
        // Verifica se esistono già impostazioni per questo tenant
        $existing = $this->getByTenantId($data['tenant_id']);

        if ($existing) {
            // Aggiorna le impostazioni esistenti
            $sql = "UPDATE {$this->table} SET 
                    logo_path = :logo_path,
                    gym_name = :gym_name,
                    address = :address,
                    city = :city,
                    phone = :phone,
                    email = :email,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE tenant_id = :tenant_id";
        } else {
            // Crea nuove impostazioni
            $sql = "INSERT INTO {$this->table} 
                    (tenant_id, logo_path, gym_name, address, city, phone, email)
                    VALUES 
                    (:tenant_id, :logo_path, :gym_name, :address, :city, :phone, :email)";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $data['tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':logo_path', $data['logo_path'], PDO::PARAM_STR);
        $stmt->bindValue(':gym_name', $data['gym_name'], PDO::PARAM_STR);
        $stmt->bindValue(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindValue(':city', $data['city'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Carica un'immagine del logo
     * 
     * @param array $file File caricato ($_FILES)
     * @param int $tenantId ID del tenant
     * @return string|false Path del logo o false in caso di errore
     */
    public function uploadLogo($file, $tenantId)
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        // Verifica il tipo di file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Crea directory se non esiste
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Genera un nome univoco per il file
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . $tenantId . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Sposta il file caricato
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/uploads/logos/' . $filename;
        }

        return false;
    }
}