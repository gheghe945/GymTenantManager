<?php
// Script per correggere il file UserController.php

$file = file_get_contents('app/Controllers/UserController.php');

// Sezione di update nell'UserController
$pattern = '/\/\/ Rimuovi i campi di errore e altri campi non del database prima dell\'aggiornamento\s+\$updateData = \$data;\s+unset\(\$updateData\[\'name_err\'\]\);\s+unset\(\$updateData\[\'email_err\'\]\);\s+unset\(\$updateData\[\'password_err\'\]\);\s+unset\(\$updateData\[\'confirm_password_err\'\]\);\s+unset\(\$updateData\[\'role_err\'\]\);\s+unset\(\$updateData\[\'tenant_id_err\'\]\);\s+unset\(\$updateData\[\'tenants\'\]\);\s+unset\(\$updateData\[\'isSuperAdmin\'\]\);/';

$replacement = '// Rimuovi i campi di errore e altri campi non del database prima dell\'aggiornamento
            $updateData = [
                \'id\' => $data[\'id\'],
                \'name\' => $data[\'name\'],
                \'email\' => $data[\'email\'],
                \'role\' => $data[\'role\'],
                \'tenant_id\' => $data[\'tenant_id\']
            ];
            
            // Aggiungi la password solo se è stata impostata
            if (!empty($data[\'password\'])) {
                $updateData[\'password\'] = $data[\'password\'];
            }';

$modified = preg_replace($pattern, $replacement, $file);

// Se la regex non ha funzionato, proviamo un approccio più diretto
if ($modified === $file) {
    $lines = file('app/Controllers/UserController.php');
    $result = '';
    $in_update_section = false;
    $skip_lines = 0;
    
    foreach ($lines as $i => $line) {
        if (strpos($line, '// Rimuovi i campi di errore') !== false) {
            $in_update_section = true;
            $result .= "            // Rimuovi i campi di errore e altri campi non del database prima dell'aggiornamento\n";
            $result .= "            \$updateData = [\n";
            $result .= "                'id' => \$data['id'],\n";
            $result .= "                'name' => \$data['name'],\n";
            $result .= "                'email' => \$data['email'],\n";
            $result .= "                'role' => \$data['role'],\n";
            $result .= "                'tenant_id' => \$data['tenant_id']\n";
            $result .= "            ];\n";
            $result .= "            \n";
            $result .= "            // Aggiungi la password solo se è stata impostata\n";
            $result .= "            if (!empty(\$data['password'])) {\n";
            $result .= "                \$updateData['password'] = \$data['password'];\n";
            $result .= "            }\n";
            $skip_lines = 9; // Salta le prossime linee (le unset che abbiamo sostituito)
        } else if ($skip_lines > 0) {
            $skip_lines--;
        } else {
            $result .= $line;
        }
    }
    
    $modified = $result;
}

file_put_contents('app/Controllers/UserController.php', $modified);

echo "Modifiche applicate a UserController.php\n";