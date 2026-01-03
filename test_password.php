<?php
include 'db.php';

$email = 'customer@gmail.com';
$newPassword = '123456';

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
$stmt->bind_param("ss", $hash, $email);
$stmt->execute();

echo "Password reset done ✅";
