<?php

/**
 * reject_order.php - Partner rejects an order
 * Sets order status to 'rejected' and notifies customer via push + in-app
 */

header('Content-Type: application/json');
require_once 'db.php';
require_once 'send_push_notification.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
$orderId = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$partnerId = isset($data['partner_id']) ? (int)$data['partner_id'] : 0;
$reason = isset($data['reason']) ? trim($data['reason']) : 'Partner unavailable';

if ($orderId <= 0) {
    echo json_encode(['status' => 'error', 'error' => 'Order ID is required']);
    exit;
}

try {
    // Get order details for notification
    if ($partnerId > 0) {
        $checkStmt = $conn->prepare(
            "SELECT o.id, o.user_id, o.status, o.product_name, o.total, u.name as partner_name, u.shop_name 
             FROM orders o 
             LEFT JOIN users u ON o.partner_id = u.id 
             WHERE o.id = ? AND o.partner_id = ?"
        );
        $checkStmt->bind_param("ii", $orderId, $partnerId);
    } else {
        $checkStmt = $conn->prepare(
            "SELECT o.id, o.user_id, o.status, o.product_name, o.total, u.name as partner_name, u.shop_name 
             FROM orders o 
             LEFT JOIN users u ON o.partner_id = u.id 
             WHERE o.id = ?"
        );
        $checkStmt->bind_param("i", $orderId);
    }
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(['status' => 'error', 'error' => 'Order not found or access denied']);
        exit;
    }

    $order = $result->fetch_assoc();
    $customerId = (int)$order['user_id'];
    $currentStatus = $order['status'];
    $productName = $order['product_name'];
    $total = $order['total'];
    $partnerDisplayName = !empty($order['shop_name']) ? $order['shop_name'] : $order['partner_name'];

    // Check if order can be rejected (only pending orders)
    if ($currentStatus !== 'pending') {
        echo json_encode([
            'status' => 'error',
            'error' => 'Only pending orders can be rejected. Current status: ' . $currentStatus
        ]);
        exit;
    }

    // Update order status to rejected
    $updateStmt = $conn->prepare("UPDATE orders SET status = 'rejected' WHERE id = ?");
    $updateStmt->bind_param("i", $orderId);

    if ($updateStmt->execute()) {
        // Send notification to customer using the unified notifyUser function
        notifyUser(
            $conn,
            $customerId,
            "Order Rejected ❌",                          // Push title
            "Order #$orderId was declined",              // Push body (brief)
            "Order Rejected",                            // In-app title
            "Unfortunately, your order #$orderId has been declined by $partnerDisplayName.\n\nReason: $reason\n\nItems: $productName\nTotal: ₹$total\n\nPlease try ordering from another partner, or try again later.",  // In-app body (detailed)
            ['order_id' => $orderId],
            'order_update'
        );

        echo json_encode([
            'status' => 'ok',
            'message' => 'Order rejected successfully',
            'order_id' => $orderId
        ]);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Failed to reject order']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'error' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
