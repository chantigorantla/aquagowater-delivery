<?php

/**
 * Update Partner UPI ID
 * Expects: user_id, upi_id
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID is required"]);
    exit;
}

$userId = (int)$data['user_id'];
$upiId = isset($data['upi_id']) ? trim($data['upi_id']) : '';

// Verify user is a partner
$checkUser = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
$checkUser->bind_param("i", $userId);
$checkUser->execute();
$userResult = $checkUser->get_result();

if ($userResult->num_rows == 0) {
    echo json_encode(["status" => "error", "error" => "User not found"]);
    exit;
}

$user = $userResult->fetch_assoc();
if ($user['role'] !== 'partner') {
    echo json_encode(["status" => "error", "error" => "Only partners can update UPI ID"]);
    exit;
}

// Update UPI ID
$stmt = $conn->prepare("UPDATE users SET upi_id = ? WHERE id = ?");
$stmt->bind_param("si", $upiId, $userId);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "ok",
        "message" => "UPI ID updated successfully",
        "upi_id" => $upiId
    ]);
} else {
    echo json_encode(["status" => "error", "error" => "Failed to update UPI ID"]);
}
