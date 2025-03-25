<?php
$admin_password = "admin123";
$user_password = "user123";

$admin_hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
$user_hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

echo "Admin hashed password: " . $admin_hashed_password . "\n";
echo "User hashed password: " . $user_hashed_password . "\n";
?>