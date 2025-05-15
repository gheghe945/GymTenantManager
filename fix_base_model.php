<?php
// Script per correggere il file BaseModel.php

$file = file_get_contents('app/Models/BaseModel.php');

// Modifica il metodo update per aggiungere controllo sui valori scalari
$modified = str_replace(
    '        // Bind values
        foreach ($data as $key => $value) {
            $stmt->bindValue(\':' . $key . '\', $value);
        }',
    '        // Bind values
        foreach ($data as $key => $value) {
            // Skip non-scalar values
            if (!is_scalar($value) && !is_null($value)) {
                error_log("Skipping non-scalar value for key: " . $key);
                continue;
            }
            $stmt->bindValue(\':' . $key . '\', $value);
        }',
    $file
);

// Se la prima sostituzione non ha funzionato, facciamo una modifica manuale
if ($modified === $file) {
    // Apriamo il file e lo leggiamo riga per riga
    $lines = file('app/Models/BaseModel.php');
    $result = '';
    $in_bind_section = false;
    
    foreach ($lines as $line) {
        if (strpos($line, 'foreach ($data as $key => $value)') !== false) {
            $in_bind_section = true;
            $result .= $line;
        } else if ($in_bind_section && strpos($line, '$stmt->bindValue') !== false) {
            $result .= "            // Skip non-scalar values\n";
            $result .= "            if (!is_scalar(\$value) && !is_null(\$value)) {\n";
            $result .= "                error_log(\"Skipping non-scalar value for key: \" . \$key);\n";
            $result .= "                continue;\n";
            $result .= "            }\n";
            $result .= $line;
            $in_bind_section = false;
        } else {
            $result .= $line;
        }
    }
    
    $modified = $result;
}

file_put_contents('app/Models/BaseModel.php', $modified);

echo "Modifiche applicate a BaseModel.php\n";