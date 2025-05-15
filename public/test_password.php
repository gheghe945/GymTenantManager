<?php
// Test script to verify password functions

$hash = '$2y$10$b5plA/KpnHsZ0Ew7u3oxLu9QWAgW7SlUPNZwLH5FCqvI7Fz0GHKb.';
$password = 'admin123';

echo "Password: $password<br>";
echo "Hash: $hash<br>";
echo "Verify result: " . (password_verify($password, $hash) ? 'true' : 'false') . "<br>";

// Generate a new hash for the same password
$newHash = password_hash($password, PASSWORD_DEFAULT);
echo "New hash: $newHash<br>";
echo "New verify result: " . (password_verify($password, $newHash) ? 'true' : 'false') . "<br>";