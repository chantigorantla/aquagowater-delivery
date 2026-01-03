<?php
include "../db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "error" => "Email and password are required"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Query user with all required fields
$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email=? AND role='customer'");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status" => "error", "error" => "User not found"]);
    exit;
}

$user = $res->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "error" => "Invalid password"]);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(16));

// Save token in DB
$update = $conn->prepare("UPDATE users SET token=? WHERE id=?");
$update->bind_param("si", $token, $user['id']);
$update->execute();

// Return response in format Android expects
echo json_encode([
    "status" => "ok",
    "user" => [
        "id" => (int)$user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role'],
        "token" => $token
    ]
]);
?>
