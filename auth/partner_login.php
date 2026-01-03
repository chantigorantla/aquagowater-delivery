<?php
require "../db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "error" => "Email and password are required"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Query user with all required fields including shop_name
$stmt = $conn->prepare("SELECT id, name, shop_name, email, password, role FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["status" => "error", "error" => "Invalid email"]);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    echo json_encode(["status" => "error", "error" => "Invalid password"]);
    exit;
}

// Check if user is a partner
if ($user['role'] !== 'partner') {
    echo json_encode(["status" => "error", "error" => "This account is not a partner account"]);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(32));

// Save token in DB
$up = $conn->prepare("UPDATE users SET token=? WHERE id=?");
$up->bind_param("si", $token, $user['id']);
$up->execute();

// Return response in format Android expects
echo json_encode([
    "status" => "ok",
    "user" => [
        "id" => (int)$user['id'],
        "name" => $user['name'],
        "shop_name" => $user['shop_name'] ?? "",
        "email" => $user['email'],
        "role" => $user['role'],
        "token" => $token
    ]
]);
