<?php
// Definiamo APP_ROOT prima di includere bootstrap.php
define('APP_ROOT', dirname(dirname(__FILE__)));
require_once '../app/bootstrap.php';

// Generiamo un nuovo hash per la password admin123
$password = 'admin123';
$newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Aggiorniamo la password per tutti gli utenti
try {
    $db = getDbConnection();
    $stmt = $db->prepare("UPDATE users SET password = :password");
    $stmt->bindParam(':password', $newHash);
    $result = $stmt->execute();
    
    if ($result) {
        echo "Password reset successful! All users now have password: $password<br>";
        echo "New hash: $newHash<br>";
        echo "Now try to log in again with:<br>";
        echo "Email: admin@example.com<br>";
        echo "Password: $password<br><br>";
        echo "<a href=\"/login\">Go to login page</a>";
    } else {
        echo "Password reset failed!";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}