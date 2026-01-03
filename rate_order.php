<?php
// rate_order.php - Rate an order after delivery
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$rating = isset($data['rating']) ? (int)$data['rating'] : 0;
$comment = isset($data['comment']) ? trim($data['comment']) : '';

// Validation
if ($order_id <= 0 || $user_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Order ID and User ID required'));
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(array('status' => 'error', 'message' => 'Rating must be between 1 and 5'));
    exit;
}

// Verify order belongs to user and is delivered
$checkStmt = $conn->prepare("SELECT id, partner_id, status FROM orders WHERE id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $order_id, $user_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($order = $checkResult->fetch_assoc()) {
    if ($order['status'] !== 'delivered' && $order['status'] !== 'completed') {
        echo json_encode(array('status' => 'error', 'message' => 'Can only rate delivered orders'));
        exit;
    }
    $partner_id = (int)$order['partner_id'];
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Order not found'));
    exit;
}
$checkStmt->close();

// Check if already rated
$ratingCheck = $conn->prepare("SELECT id FROM ratings WHERE order_id = ?");
$ratingCheck->bind_param("i", $order_id);
$ratingCheck->execute();
if ($ratingCheck->get_result()->num_rows > 0) {
    echo json_encode(array('status' => 'error', 'message' => 'Order already rated'));
    exit;
}
$ratingCheck->close();

// Insert rating
$sql = "INSERT INTO ratings (order_id, user_id, partner_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiis", $order_id, $user_id, $partner_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode(array(
        'status' => 'ok',
        'message' => 'Thank you for your rating!'
    ));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Failed to save rating'));
}

$stmt->close();
$conn->close();
