<?php

/**
 * Forgot password - Send reset code
 * Expects: email
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'])) {
    echo json_encode(["status" => "error", "error" => "Email required"]);
    exit;
}

$email = trim($data['email']);

try {
    // Check if email exists
    $userQuery = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $userQuery->bind_param("s", $email);
    $userQuery->execute();
    $result = $userQuery->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["status" => "error", "error" => "Email not found"]);
        exit;
    }

    $user = $result->fetch_assoc();

    // Generate 6-digit reset code
    $resetCode = rand(100000, 999999);

    // Store reset code (in real app, store in DB with expiry)
    // For demo, just return success
    // TODO: Send email with reset code

    echo json_encode([
        "status" => "ok",
        "message" => "Password reset code sent to your email",
        "reset_code" => $resetCode // Remove in production
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to process request"
    ]);
}
