<?php
// Test script to verify password functions

// Test with the existing hash from the database
$oldHash = '$2y$10$b5plA/KpnHsZ0Ew7u3oxLu9QWAgW7SlUPNZwLH5FCqvI7Fz0GHKb.';
$password = 'admin123';

echo "<h2>Existing Hash Test</h2>";
echo "Password: $password<br>";
echo "Old Hash: $oldHash<br>";
echo "Verify result: " . (password_verify($password, $oldHash) ? 'true' : 'false') . "<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Password Hash Info: <pre>" . print_r(password_get_info($oldHash), true) . "</pre><br>";

// Generate a new hash for the same password
echo "<h2>New Hash Test</h2>";
$newHash = password_hash($password, PASSWORD_DEFAULT);
echo "New Hash: $newHash<br>";
echo "New Verify result: " . (password_verify($password, $newHash) ? 'true' : 'false') . "<br>";
echo "Password Hash Info: <pre>" . print_r(password_get_info($newHash), true) . "</pre><br>";

// Try with different cost parameters
echo "<h2>Different Cost Parameter Tests</h2>";
for ($cost = 8; $cost <= 12; $cost++) {
    $testHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    echo "Hash with cost $cost: $testHash<br>";
    echo "Verify result: " . (password_verify($password, $testHash) ? 'true' : 'false') . "<br><br>";
}

// Function to manually verify bcrypt hash
function manual_bcrypt_verify($password, $hash) {
    $parts = explode('$', $hash);
    if (count($parts) < 4 || $parts[1] != '2y') {
        return 'Not a bcrypt hash';
    }
    
    $cost = $parts[2];
    $salt = substr($parts[3], 0, 22); // Extract the salt
    
    $testHash = crypt($password, '$2y$' . $cost . '$' . $salt . '$');
    
    return [
        'Original hash' => $hash,
        'Test hash' => $testHash,
        'Match' => $hash === $testHash ? 'Yes' : 'No'
    ];
}

echo "<h2>Manual Verification Test</h2>";
echo "<pre>" . print_r(manual_bcrypt_verify($password, $oldHash), true) . "</pre>";