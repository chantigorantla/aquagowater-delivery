<?php
// accept_order.php - Update order status with notifications
header('Content-Type: application/json');
require_once 'db.php';
require_once 'send_push_notification.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Also support form POST
if (empty($data)) {
    $data = $_POST;
}

$order_id = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$action = isset($data['action']) ? strtolower($data['action']) : '';
$status = isset($data['status']) ? strtolower($data['status']) : '';

// Validate input
if ($order_id <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid order ID'));
    exit;
}

// Determine new status based on action or direct status
$new_status = '';
if (!empty($action)) {
    switch ($action) {
        case 'accept':
        case 'confirm':
            $new_status = 'confirmed';
            break;
        case 'reject':
        case 'cancel':
            $new_status = 'cancelled';
            break;
        case 'complete':
        case 'deliver':
            $new_status = 'delivered';
            break;
        case 'out_for_delivery':
            $new_status = 'out_for_delivery';
            break;
        default:
            echo json_encode(array('success' => false, 'message' => 'Invalid action: ' . $action));
            exit;
    }
} elseif (!empty($status)) {
    // Direct status update
    $valid_statuses = array('pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled');
    if (in_array($status, $valid_statuses)) {
        $new_status = $status;
    } else {
        echo json_encode(array('success' => false, 'message' => 'Invalid status: ' . $status));
        exit;
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Action or status required'));
    exit;
}

// Get order details for notification
$orderQuery = $conn->prepare("SELECT o.user_id, o.partner_id, o.product_name, o.total, u.name as partner_name, u.shop_name 
                              FROM orders o 
                              LEFT JOIN users u ON o.partner_id = u.id 
                              WHERE o.id = ?");
$orderQuery->bind_param("i", $order_id);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();
$orderInfo = $orderResult->fetch_assoc();

if (!$orderInfo) {
    echo json_encode(array('success' => false, 'message' => 'Order not found'));
    exit;
}

$customerId = $orderInfo['user_id'];
$partnerId = $orderInfo['partner_id'];
$productName = $orderInfo['product_name'];
$total = $orderInfo['total'];
$partnerDisplayName = !empty($orderInfo['shop_name']) ? $orderInfo['shop_name'] : $orderInfo['partner_name'];

// Get payment method to handle COD payment status update
$paymentQuery = $conn->prepare("SELECT payment_method FROM orders WHERE id = ?");
$paymentQuery->bind_param("i", $order_id);
$paymentQuery->execute();
$paymentResult = $paymentQuery->get_result();
$paymentInfo = $paymentResult->fetch_assoc();
$paymentMethod = $paymentInfo['payment_method'] ?? 'COD';
$paymentQuery->close();

// Update order status (and payment_status for COD on delivery)
if ($new_status == 'delivered' && strtoupper($paymentMethod) == 'COD') {
    // For COD orders, mark payment as 'paid' when delivered (cash collected)
    $stmt = $conn->prepare("UPDATE orders SET status = ?, payment_status = 'paid' WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
}
$stmt->bind_param("si", $new_status, $order_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {

        // =============================================
        // SEND NOTIFICATIONS based on status
        // =============================================

        switch ($new_status) {
            case 'confirmed':
                // Notify customer - Order confirmed
                notifyUser(
                    $conn,
                    $customerId,
                    "Order Confirmed! ✅",
                    "Your order #$order_id is being prepared",
                    "Order Confirmed",
                    "Great news! Your order #$order_id has been confirmed by $partnerDisplayName.\n\nItems: $productName\nTotal: ₹$total\n\nEstimated delivery: 30-45 minutes. You'll be notified when it's out for delivery.",
                    ['order_id' => $order_id],
                    'order_update'
                );
                break;

            case 'out_for_delivery':
                // Notify customer - Out for delivery
                notifyUser(
                    $conn,
                    $customerId,
                    "On the way! 🚚",
                    "Your order #$order_id is out for delivery",
                    "Order Out for Delivery",
                    "Your order #$order_id is on its way!\n\nItems: $productName\nTotal: ₹$total\n\nThe delivery partner from $partnerDisplayName is heading to your location. Please be available to receive your order.",
                    ['order_id' => $order_id],
                    'order_update'
                );
                break;

            case 'delivered':
                // Notify customer - Delivered
                notifyUser(
                    $conn,
                    $customerId,
                    "Delivered! 🎉",
                    "Your order #$order_id has been delivered",
                    "Order Delivered Successfully",
                    "Your order #$order_id has been delivered successfully!\n\nItems: $productName\nTotal: ₹$total\n\nThank you for using AquaGo! We hope you enjoy your purchase. Please rate your experience.",
                    ['order_id' => $order_id],
                    'order_update'
                );
                break;

            case 'cancelled':
                // Notify customer - Cancelled
                notifyUser(
                    $conn,
                    $customerId,
                    "Order Cancelled ❌",
                    "Your order #$order_id has been cancelled",
                    "Order Cancelled",
                    "We're sorry, your order #$order_id has been cancelled.\n\nItems: $productName\nTotal: ₹$total\n\nIf you made a payment, it will be refunded within 3-5 business days. Please try ordering again or contact support if you have questions.",
                    ['order_id' => $order_id],
                    'order_update'
                );
                break;
        }

        echo json_encode(array(
            'status' => 'ok',
            'success' => true,
            'message' => 'Order status updated to ' . ucfirst($new_status),
            'order_id' => $order_id,
            'new_status' => $new_status
        ));
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Order not found or status unchanged'
        ));
    }
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Database error: ' . $stmt->error
    ));
}

$stmt->close();
$conn->close();
