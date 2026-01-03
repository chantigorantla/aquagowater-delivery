<?php

/**
 * Get unread notification count for a user
 * Expects: user_id
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
        // Table doesn't exist, return 0
        echo json_encode([
            "status" => "ok",
            "unread_count" => 0
        ]);
        exit;
    }

    $query = $conn->prepare(
        "SELECT COUNT(*) as unread_count 
         FROM notifications 
         WHERE user_id = ? AND read_flag = 0"
    );
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    echo json_encode([
        "status" => "ok",
        "unread_count" => (int)$row['unread_count']
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "error" => "Failed to get unread count"
    ]);
}
