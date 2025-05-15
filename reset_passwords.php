<?php
/**
 * Script to reset passwords for specific users
 * 
 * This will create a password reset for:
 * - admin@example.com (password: admin123)
 */

// Include bootstrap file
define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/app/bootstrap.php';
require_once APP_ROOT . '/app/Models/BaseModel.php';
require_once APP_ROOT . '/app/Models/User.php';

// Reset admin password
$userModel = new User();

// Find admin
$adminUser = $userModel->findUserByEmail('admin@example.com');

if ($adminUser) {
    echo "Found user admin@example.com with ID: " . $adminUser['id'] . "\n";
    
    // Hash new password
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Update user with new password
    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':id', $adminUser['id'], PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "Password updated successfully for admin@example.com. New password: admin123\n";
    } else {
        echo "Failed to update password for admin@example.com\n";
    }
} else {
    echo "No user found with email admin@example.com\n";
    
    // Check if any users exist
    $allUsers = $userModel->getAll();
    if (count($allUsers) > 0) {
        echo "Found " . count($allUsers) . " users in the database:\n";
        foreach ($allUsers as $user) {
            echo "ID: " . $user['id'] . ", Email: " . $user['email'] . ", Role: " . $user['role'] . "\n";
        }
        
        // Reset first user's password
        $firstUser = $allUsers[0];
        $password = 'admin123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':id', $firstUser['id'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo "Password updated successfully for " . $firstUser['email'] . ". New password: admin123\n";
        } else {
            echo "Failed to update password for " . $firstUser['email'] . "\n";
        }
    } else {
        echo "No users found in the database\n";
    }
}

echo "\nDone! You can now login with the new credentials.\n";