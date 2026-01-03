<?php
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "error" => "Email and password are required"]);
    exit;
}

$businessName = isset($data['business_name']) ? trim($data['business_name']) : '';
$contactPerson = isset($data['contact_person']) ? trim($data['contact_person']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$upiId = isset($data['upi_id']) ? trim($data['upi_id']) : '';
$email = trim($data['email']);
$password = $data['password'];

// Use contact person as name, or business name if contact person not provided
$name = !empty($contactPerson) ? $contactPerson : $businessName;

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "error" => "Invalid email format"]);
    exit;
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(["status" => "error", "error" => "Email already registered"]);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate token
$token = bin2hex(random_bytes(32));

// Role is always partner for this endpoint
$role = "partner";

// Insert user with shop_name, phone, and upi_id
$stmt = $conn->prepare("INSERT INTO users (name, shop_name, email, phone, upi_id, password, role, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $name, $businessName, $email, $phone, $upiId, $hashedPassword, $role, $token);

if ($stmt->execute()) {
    $userId = $conn->insert_id;

    echo json_encode([
        "status" => "ok",
        "user" => [
            "id" => $userId,
            "name" => $name,
            "shop_name" => $businessName,
            "email" => $email,
            "phone" => $phone,
            "upi_id" => $upiId,
            "role" => $role,
            "token" => $token
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "error" => "Registration failed: " . $conn->error]);
}
