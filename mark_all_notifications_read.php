<?php

/**
 * Mark all notifications as read for a user
 * Expects: user_id
 */
require_once 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required']);
    exit;
}

// Update all notifications as read for this user
$stmt = $conn->prepare("UPDATE notifications SET read_flag = 1 WHERE user_id = ? AND read_flag = 0");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $affectedRows = $stmt->affected_rows;
    echo json_encode([
        'status' => 'ok',
        'message' => $affectedRows > 0 ? "$affectedRows notifications marked as read" : "No unread notifications",
        'updated_count' => $affectedRows
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update notifications']);
}

$stmt->close();
$conn->close();
