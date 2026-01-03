<?php

/**
 * update_subscription.php - Pause/Resume/Cancel subscription
 */

header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$subscription_id = isset($data['subscription_id']) ? intval($data['subscription_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required']);
    exit;
}

if (!in_array($action, ['pause', 'resume', 'cancel'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action. Use: pause, resume, or cancel']);
    exit;
}

try {
    $newStatus = '';
    switch ($action) {
        case 'pause':
            $newStatus = 'paused';
            break;
        case 'resume':
            $newStatus = 'active';
            break;
        case 'cancel':
            $newStatus = 'cancelled';
            break;
    }

    $stmt = $conn->prepare("UPDATE subscriptions SET status = ? WHERE user_id = ? AND id = ?");
    $stmt->bind_param("sii", $newStatus, $user_id, $subscription_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'ok',
            'message' => 'Subscription ' . $action . 'd successfully',
            'new_status' => $newStatus
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Subscription not found or no change']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
