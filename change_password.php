<?php

/**
 * Change user password
 * Expects: user_id, old_password, new_password
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['old_password']) || !isset($data['new_password'])) {
    echo json_encode(["status" => "error", "error" => "user_id, old_password, and new_password required"]);
    exit;
}

$userId = (int)$data['user_id'];
$oldPassword = $data['old_password'];
$newPassword = $data['new_password'];

if (strlen($newPassword) < 6) {
    echo json_encode(["status" => "error", "error" => "New password must be at least 6 characters"]);
    exit;
}

try {
    // Get current password
    $userQuery = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $userQuery->bind_param("i", $userId);
    $userQuery->execute();
    $result = $userQuery->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "error" => "User not found"]);
        exit;
    }

    $user = $result->fetch_assoc();

    // Verify old password
    if (!password_verify($oldPassword, $user['password'])) {
        echo json_encode(["status" => "error", "error" => "Current password is incorrect"]);
        exit;
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateQuery->bind_param("si", $hashedPassword, $userId);
    $updateQuery->execute();

    echo json_encode([
        "status" => "ok",
        "message" => "Password changed successfully"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to change password"
    ]);
}
