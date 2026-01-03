<?php

/**
 * Simple Role-Based Registration
 * Creates user with specified role (customer/partner)
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
    echo json_encode(["status" => "error", "error" => "Name, email, password and role are required"]);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];
$role = trim($data['role']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "error" => "Invalid email format"]);
    exit;
}

// Validate role
if ($role !== 'customer' && $role !== 'partner') {
    echo json_encode(["status" => "error", "error" => "Role must be 'customer' or 'partner'"]);
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

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    $userId = $conn->insert_id;

    echo json_encode([
        "status" => "ok",
        "user" => [
            "id" => $userId,
            "name" => $name,
            "email" => $email,
            "role" => $role
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "error" => "Registration failed: " . $conn->error]);
}
