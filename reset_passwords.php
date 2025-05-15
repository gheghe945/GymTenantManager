<?php
/**
 * Script per resettare le password degli utenti di test
 */

// Define application root path
define('APP_ROOT', __DIR__);

// Load bootstrap
require_once APP_ROOT . '/app/bootstrap.php';

// Inizializza il model User
$userModel = new User();

// Password da impostare (123456)
$password = password_hash('123456', PASSWORD_DEFAULT);
echo "Password generata: " . $password . "\n";

// IDs degli utenti da aggiornare
$userIds = [1, 2, 3, 5, 7];

// Aggiorna le password
foreach ($userIds as $userId) {
    $success = $userModel->resetPassword($userId, $password);
    echo "Aggiornamento password per l'utente ID $userId: " . ($success ? "Riuscito" : "Fallito") . "\n";
}

echo "Operazione completata!\n";