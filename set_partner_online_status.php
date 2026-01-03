<?php

/**
 * set_partner_online_status.php - Update partner online/offline status
 */

header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$partner_id = isset($data['partner_id']) ? intval($data['partner_id']) : 0;
$is_online = isset($data['is_online']) ? ($data['is_online'] ? 1 : 0) : 0;

if ($partner_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Partner ID required']);
    exit;
}

try {
    // Update the is_online column in users table
    $stmt = $conn->prepare("UPDATE users SET is_online = ? WHERE id = ? AND role = 'partner'");
    $stmt->bind_param("ii", $is_online, $partner_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0 || $is_online == $is_online) {
            echo json_encode([
                'status' => 'ok',
                'is_online' => (bool)$is_online,
                'message' => $is_online ? 'Now Online' : 'Now Offline'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Partner not found']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update status']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
