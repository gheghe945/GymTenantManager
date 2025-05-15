<?php
// Script per correggere il file UserController.php

$file = file_get_contents('app/Controllers/UserController.php');

// Rimuovi FILTER_SANITIZE_STRING deprecato
$fixed = str_replace(
    'filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING)',
    'filter_input_array(INPUT_POST)',
    $file
);

file_put_contents('app/Controllers/UserController.php', $fixed);

echo "Corretto FILTER_SANITIZE_STRING nel file UserController.php\n";