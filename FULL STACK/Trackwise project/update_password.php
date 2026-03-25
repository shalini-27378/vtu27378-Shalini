<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Update password for demo_user
$hashed_password = password_hash('password123', PASSWORD_DEFAULT);
$query = "UPDATE users SET password = :password WHERE username = 'demo_user'";
$stmt = $db->prepare($query);
$stmt->bindParam(':password', $hashed_password);
$stmt->execute();

echo "Password updated successfully!";
?>