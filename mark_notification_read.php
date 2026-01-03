<?php
require_once 'db.php';
header('Content-Type: application/json');

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$notification_id = isset($data['notification_id']) ? intval($data['notification_id']) : 0;

if ($user_id <= 0 || $notification_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID and Notification ID required']);
    exit;
}

// Update notification as read
$stmt = $conn->prepare("UPDATE notifications SET read_flag = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'ok', 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['status' => 'ok', 'message' => 'Notification already read or not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update notification']);
}

$stmt->close();
$conn->close();
