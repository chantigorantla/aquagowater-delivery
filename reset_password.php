<?php

/**
 * Reset Password
 * Changes password after OTP verification
 * Expects: email, reset_token, new_password
 */
require_once 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['reset_token']) || !isset($data['new_password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email, reset token, and new password are required']);
    exit;
}

$email = trim($data['email']);
$resetToken = trim($data['reset_token']);
$newPassword = $data['new_password'];

// Validate password length
if (strlen($newPassword) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Find the reset record with the token
$stmt = $conn->prepare("SELECT id, user_id, used, expires_at FROM password_resets WHERE email = ? AND otp = ?");
$stmt->bind_param("ss", $email, $resetToken);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired reset token']);
    exit;
}

$reset = $result->fetch_assoc();
$stmt->close();

// Check if already used
if ($reset['used'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'This reset link has already been used']);
    exit;
}

// Check if expired (give extra 5 minutes after OTP verification)
$expiresAt = strtotime($reset['expires_at']);
if (time() > $expiresAt + 300) { // 5 minutes extra
    echo json_encode(['status' => 'error', 'message' => 'Reset session expired. Please start again']);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update user's password
$updateUserStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$updateUserStmt->bind_param("si", $hashedPassword, $reset['user_id']);

if (!$updateUserStmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
    exit;
}
$updateUserStmt->close();

// Mark reset as used
$markUsedStmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
$markUsedStmt->bind_param("i", $reset['id']);
$markUsedStmt->execute();
$markUsedStmt->close();

// Delete all reset records for this email (cleanup)
$cleanupStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$cleanupStmt->bind_param("s", $email);
$cleanupStmt->execute();
$cleanupStmt->close();

echo json_encode([
    'status' => 'ok',
    'message' => 'Password reset successfully. You can now login with your new password.'
]);

$conn->close();
