<?php

/**
 * Delete user account
 * Expects: user_id, password (for confirmation)
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['password'])) {
    echo json_encode(["status" => "error", "error" => "user_id and password required"]);
    exit;
}

$userId = (int)$data['user_id'];
$password = $data['password'];

try {
    // Verify password
    $userQuery = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $userQuery->bind_param("i", $userId);
    $userQuery->execute();
    $result = $userQuery->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "error" => "User not found"]);
        exit;
    }

    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        echo json_encode(["status" => "error", "error" => "Incorrect password"]);
        exit;
    }

    // Delete related data first
    $conn->query("DELETE FROM cart_items WHERE user_id = $userId");
    $conn->query("DELETE FROM addresses WHERE user_id = $userId");
    // Note: Keep orders for records, just anonymize
    $conn->query("UPDATE orders SET user_id = 0 WHERE user_id = $userId");

    // Delete user
    $deleteQuery = $conn->prepare("DELETE FROM users WHERE id = ?");
    $deleteQuery->bind_param("i", $userId);
    $deleteQuery->execute();

    echo json_encode([
        "status" => "ok",
        "message" => "Account deleted successfully"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to delete account"
    ]);
}
