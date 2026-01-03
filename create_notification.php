<?php

/**
 * Create a notification for a user
 * Expects: user_id, title, body, meta (optional)
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id']) || !isset($data['title'])) {
    echo json_encode(["status" => "error", "error" => "User ID and title required"]);
    exit;
}

$userId = (int)$data['user_id'];
$title = $data['title'];
$body = isset($data['body']) ? $data['body'] : '';
$meta = isset($data['meta']) ? json_encode($data['meta']) : null;

try {
    $stmt = $conn->prepare(
        "INSERT INTO notifications (user_id, title, body, meta, read_flag, created_at) 
         VALUES (?, ?, ?, ?, 0, NOW())"
    );
    $stmt->bind_param("isss", $userId, $title, $body, $meta);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "ok",
            "notification_id" => $conn->insert_id,
            "message" => "Notification created successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "error" => "Failed to create notification"
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to create notification: " . $e->getMessage()
    ]);
}

$conn->close();
