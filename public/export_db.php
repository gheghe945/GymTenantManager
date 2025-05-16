
<?php
// Previeni qualsiasi output prima degli header
ob_start();

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';

$output_file = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
$command = sprintf(
    'PGPASSWORD="%s" pg_dump -h %s -U %s -d %s -f %s',
    DB_PASS,
    DB_HOST,
    DB_USER,
    DB_NAME,
    $output_file
);

if (exec($command, $output, $return_var) !== false) {
    if (file_exists($output_file)) {
        // Pulisci qualsiasi output in buffer
        ob_clean();
        
        // Imposta gli header per il download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($output_file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($output_file));
        
        // Invia il file
        readfile($output_file);
        
        // Elimina il file temporaneo
        unlink($output_file);
        exit;
    }
} else {
    echo "Errore durante l'esportazione del database\n";
    print_r($output);
}
?>
