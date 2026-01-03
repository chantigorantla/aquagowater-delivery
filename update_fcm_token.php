<?php

/**
 * Update user's FCM token for push notifications
 * Expects: user_id, fcm_token
 */
require_once 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['fcm_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'User ID and FCM token required']);
    exit;
}

$userId = (int)$data['user_id'];
$fcmToken = $data['fcm_token'];

// Check if fcm_token column exists, if not add it
$columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'fcm_token'");
if ($columnCheck->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) DEFAULT NULL");
}

// Update user's FCM token
$stmt = $conn->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
$stmt->bind_param("si", $fcmToken, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0 || $stmt->affected_rows == 0) {
        echo json_encode([
            'status' => 'ok',
            'message' => 'FCM token updated successfully'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update FCM token'
    ]);
}

$stmt->close();
$conn->close();
