<?php

/**
 * Verify Password Reset OTP
 * Checks if OTP is valid and not expired
 * Expects: email, otp
 */
require_once 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['otp'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required']);
    exit;
}

$email = trim($data['email']);
$otp = trim($data['otp']);

// Find the OTP record
$stmt = $conn->prepare("SELECT id, user_id, expires_at, used FROM password_resets WHERE email = ? AND otp = ?");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
    exit;
}

$reset = $result->fetch_assoc();
$stmt->close();

// Check if already used
if ($reset['used'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'OTP has already been used']);
    exit;
}

// Check if expired
$expiresAt = strtotime($reset['expires_at']);
if (time() > $expiresAt) {
    echo json_encode(['status' => 'error', 'message' => 'OTP has expired. Please request a new one']);
    exit;
}

// Generate a reset token for the password reset step
$resetToken = bin2hex(random_bytes(32));

// Update the record with the token (mark as verified but not yet used)
$updateStmt = $conn->prepare("UPDATE password_resets SET otp = ? WHERE id = ?");
$updateStmt->bind_param("si", $resetToken, $reset['id']);
$updateStmt->execute();
$updateStmt->close();

echo json_encode([
    'status' => 'ok',
    'message' => 'OTP verified successfully',
    'reset_token' => $resetToken
]);

$conn->close();
