<?php

/**
 * Update user profile
 * Expects: user_id, name, email
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID required"]);
    exit;
}

$userId = (int)$data['user_id'];
$name = isset($data['name']) ? trim($data['name']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$phone = isset($data['phone']) ? trim($data['phone']) : null;

try {
    // Check if email already exists for another user
    if ($email) {
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $userId);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "error" => "Email already in use"]);
            exit;
        }
    }

    // Build update query dynamically
    $updates = [];
    $params = [];
    $types = "";

    if ($name) {
        $updates[] = "name = ?";
        $params[] = $name;
        $types .= "s";
    }
    if ($email) {
        $updates[] = "email = ?";
        $params[] = $email;
        $types .= "s";
    }
    if ($phone) {
        $updates[] = "phone = ?";
        $params[] = $phone;
        $types .= "s";
    }

    if (empty($updates)) {
        echo json_encode(["status" => "error", "error" => "No fields to update"]);
        exit;
    }

    $params[] = $userId;
    $types .= "i";

    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // Get updated user data
    $userQuery = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $userQuery->bind_param("i", $userId);
    $userQuery->execute();
    $user = $userQuery->get_result()->fetch_assoc();

    echo json_encode([
        "status" => "ok",
        "message" => "Profile updated",
        "user" => $user
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to update profile"
    ]);
}
