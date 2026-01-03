<?php

/**
 * Get user notifications with metadata
 * Expects: user_id
 * Returns: notifications with type and order_id from meta field
 */
include "db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "error" => "User ID required"]);
    exit;
}

$userId = (int)$data['user_id'];

try {
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");

    if ($tableCheck->num_rows == 0) {
        // Table doesn't exist, return empty array
        echo json_encode([
            "status" => "ok",
            "notifications" => []
        ]);
        exit;
    }

    // Include meta field for order_id and type
    $query = $conn->prepare(
        "SELECT id, title, body, meta, read_flag, created_at 
         FROM notifications 
         WHERE user_id = ? 
         ORDER BY created_at DESC 
         LIMIT 50"
    );
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        // Parse meta JSON for order_id and type
        $orderId = 0;
        $type = "general";

        if (!empty($row['meta'])) {
            $meta = json_decode($row['meta'], true);
            if (is_array($meta)) {
                $orderId = isset($meta['order_id']) ? (int)$meta['order_id'] : 0;
                $type = isset($meta['type']) ? $meta['type'] : "general";
            }
        }

        $notifications[] = [
            "id" => (int)$row['id'],
            "title" => $row['title'],
            "message" => $row['body'],  // Map 'body' to 'message' for Android compatibility
            "is_read" => (bool)$row['read_flag'],  // Map 'read_flag' to 'is_read' for Android
            "created_at" => $row['created_at'],
            "type" => $type,
            "order_id" => $orderId
        ];
    }

    echo json_encode([
        "status" => "ok",
        "notifications" => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to load notifications"
    ]);
}
