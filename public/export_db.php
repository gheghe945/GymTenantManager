
<?php
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

echo "Esportazione del database in corso...\n";
exec($command, $output, $return_var);

if ($return_var === 0) {
    echo "Database esportato con successo nel file: " . $output_file . "\n";
    echo "Puoi scaricare il file dal browser visitando: /export_db.php";
    
    // Permetti il download del file
    if (file_exists($output_file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($output_file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($output_file));
        readfile($output_file);
        unlink($output_file); // Elimina il file dopo il download
        exit;
    }
} else {
    echo "Errore durante l'esportazione del database\n";
    print_r($output);
}
?>
